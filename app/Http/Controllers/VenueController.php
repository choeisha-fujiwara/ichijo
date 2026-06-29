<?php

namespace App\Http\Controllers;

use App\Models\ArticleVenue;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VenueController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $venues = ArticleVenue::orderBy('updated_at', 'desc')->get();

        return view('dashboard.venue.index', compact('user', 'venues'));
    }

    public function show(ArticleVenue $venue)
    {
        $user = auth()->user();

        return view('dashboard.venue.show', compact('user', 'venue'));
    }

    public function create()
    {
        $user = auth()->user();

        return view('dashboard.venue.create', compact('user'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'venue_name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'fax' => ['nullable', 'string', 'max:255'],
            'map_url' => ['nullable', 'url', 'max:255'],
            'manager' => ['nullable', 'string', 'max:255'],
            'access' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:100000'],
        ]);

        if ($request->hasFile('image')) {
            $storedImage = $this->storeAsWebp($request->file('image'), 'uploads/venue');
            $validated['image'] = $storedImage['path'];
        }

        $venue = ArticleVenue::create($validated);

        return redirect()
            ->route('venue.show', $venue)
            ->with('msg', '会場情報を作成しました');
    }

    public function update(Request $request, ArticleVenue $venue): RedirectResponse
    {
        $validated = $request->validate([
            'venue_name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'fax' => ['nullable', 'string', 'max:255'],
            'map_url' => ['nullable', 'url', 'max:255'],
            'manager' => ['nullable', 'string', 'max:255'],
            'access' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:100000'],
        ]);

        if ($request->hasFile('image')) {
            $storedImage = $this->storeAsWebp($request->file('image'), 'uploads/venue');
            $validated['image'] = $storedImage['path'];

            if (!empty($venue->image)) {
                $oldPath = $this->resolveImagePath((string) $venue->image);

                if ($oldPath !== null && Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }
        }

        $venue->update($validated);

        return redirect()
            ->route('venue.show', $venue)
            ->with('msg', '会場情報を更新しました');
    }

    public function destroy(ArticleVenue $venue): RedirectResponse
    {
        if ($venue->articles()->exists()) {
            return redirect()
                ->route('venue.show', $venue)
                ->with('msg', '利用中の記事があるため削除できません。');
        }

        $venue->delete();

        return redirect()
            ->route('venue.index')
            ->with('msg', '会場情報を削除しました');
    }

    public function image(ArticleVenue $venue)
    {
        if (empty($venue->image)) {
            abort(404);
        }

        $path = $this->resolveImagePath((string) $venue->image);

        if ($path === null || !Storage::disk('public')->exists($path)) {
            abort(404);
        }

        $mimeType = Storage::disk('public')->mimeType($path) ?: 'application/octet-stream';

        return Storage::disk('public')->response(
            $path,
            basename($path),
            ['Content-Type' => $mimeType]
        );
    }

    private function storeAsWebp(UploadedFile $file, string $directory): array
    {
        $originalName = $file->getClientOriginalName();
        $baseName = pathinfo($originalName, PATHINFO_FILENAME);
        $baseName = Str::slug($baseName) ?: 'image';
        $path = trim($directory, '/') . '/' . $baseName . '-' . Str::uuid() . '.webp';

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
            'path' => $path,
            'original_name' => $originalName,
            'size' => Storage::disk('public')->size($path),
        ];
    }

    private function resolveImagePath(string $image): ?string
    {
        $image = trim($image);

        if ($image === '') {
            return null;
        }

        if (str_starts_with($image, 'storage/')) {
            return ltrim(substr($image, 8), '/');
        }

        if (str_contains($image, '/')) {
            return ltrim($image, '/');
        }

        return 'venues/' . $image;
    }
}
