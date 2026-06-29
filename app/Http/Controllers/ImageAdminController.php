<?php

namespace App\Http\Controllers;

use App\Models\Image;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageAdminController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'type' => ['nullable', 'in:header,body'],
        ]);

        $type = (string) ($validated['type'] ?? 'header');

        $images = Image::query()
            ->where('path', 'like', $type === 'body' ? '%/body/%' : '%/header/%')
            ->orderByDesc('created_at')
            ->paginate(32)
            ->withQueryString();

        return view('dashboard.images.index', compact('user', 'images', 'type'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type'   => ['required', 'in:header,body'],
            'images' => ['required', 'array', 'min:1', 'max:20'],
            'images.*' => ['required', 'file', 'mimes:jpeg,png,gif,webp', 'max:100000'],
        ]);

        $type = $validated['type'];
        $directory = "articles/{$type}";

        DB::transaction(function () use ($validated, $directory) {
            foreach ($validated['images'] as $file) {
                /** @var UploadedFile $file */
                $stored = $this->storeAsWebp($file, $directory);
                Image::create([
                    'path'          => $stored['path'],
                    'original_name' => $stored['original_name'],
                    'size'          => $stored['size'],
                    'mime_type'     => 'image/webp',
                ]);
            }
        });

        return redirect()
            ->route('images.index', ['type' => $type])
            ->with('msg', 'アップロードしました。');
    }

    public function getImagesPaginated(Request $request)
    {
        $validated = $request->validate([
            'type' => ['nullable', 'in:header,body'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $type = (string) ($validated['type'] ?? 'header');
        $page = (int) ($validated['page'] ?? 1);

        $query = Image::query()
            ->where('path', 'like', $type === 'body' ? '%/body/%' : '%/header/%')
            ->orderByDesc('created_at');

        $paginated = $query->paginate(5, ['*'], 'page', $page);

        $images = $paginated->getCollection()
            ->map(function ($image) {
                return [
                    'id' => $image->id,
                    'url' => route('article.image', $image),
                    'original_name' => $image->original_name,
                    'path' => $image->path,
                ];
            })
            ->all();

        return response()->json([
            'images' => $images,
            'pagination' => [
                'current_page' => $paginated->currentPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
                'last_page' => $paginated->lastPage(),
                'has_more' => $paginated->hasMorePages(),
            ],
        ]);
    }

    public function destroy(Request $request, Image $image): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['nullable', 'in:header,body'],
        ]);
        $type = (string) ($validated['type'] ?? 'header');

        $article = $image->article;

        if ($article) {
            $bodyImages = $article->body_image ?? [];
            $isHeaderImage = (string) $article->header_image === (string) $image->path;
            $isBodyImage = in_array((string) $image->path, $bodyImages, true);

            if ($isHeaderImage || $isBodyImage) {
                return redirect()
                    ->route('images.index', ['type' => $type])
                    ->with('msg', '記事で利用中の画像は削除できません。');
            }
        }

        $image->delete();

        return redirect()
            ->route('images.index', ['type' => $type])
            ->with('msg', '画像を削除しました。');
    }

    private function storeAsWebp(UploadedFile $file, string $directory): array
    {
        $originalName = $file->getClientOriginalName();
        $baseName = pathinfo($originalName, PATHINFO_FILENAME);
        $baseName = Str::slug($baseName) ?: 'image';
        $path = trim($directory, '/').'/'.$baseName.'-'.Str::uuid().'.webp';

        $rawContent = file_get_contents($file->getRealPath());
        $imageResource = $rawContent !== false ? imagecreatefromstring($rawContent) : false;

        if ($imageResource === false) {
            throw new \RuntimeException('画像の変換に失敗しました。');
        }

        ob_start();
        $encoded = imagewebp($imageResource, null, 80);
        $webpBinary = ob_get_clean();
        imagedestroy($imageResource);

        if ($encoded === false || $webpBinary === false) {
            throw new \RuntimeException('WebP画像の生成に失敗しました。');
        }

        Storage::disk('public')->put($path, $webpBinary);

        return [
            'path'          => $path,
            'original_name' => $originalName,
            'size'          => Storage::disk('public')->size($path),
        ];
    }
}

