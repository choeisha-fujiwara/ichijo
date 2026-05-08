<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use App\Models\User;
use App\Models\Article;
use App\Models\ArticleVenue;
use App\Models\Image;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Mail\SendMail;
use Illuminate\Support\Facades\Mail;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'prefill_image_id' => ['nullable', 'integer', 'exists:images,id'],
            'prefill_type' => ['nullable', 'in:header,body'],
        ]);

        $data = Article::orderBy('created_at', 'desc')
            ->paginate(100)
            ->withQueryString();
        $images = Image::orderBy('created_at', 'desc')->get();
        $venues = $this->availableVenues();
        $currentRole = Auth::user()->role;
        $excludeRoles = match ($currentRole) {
            'developer' => [],
            'system'    => ['developer'],
            'admin'     => ['developer', 'system'],
            'manager'   => ['developer', 'system', 'admin'],
            default     => ['developer', 'system', 'admin', 'manager'],
        };
        $userEmails = User::query()
            ->when(!empty($excludeRoles), fn($q) => $q->whereNotIn('role', $excludeRoles))
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->orderBy('id')
            ->pluck('email')
            ->unique()
            ->values();

        $prefillHeaderImage = null;
        $prefillBodyImages = [];

        if (!empty($validated['prefill_image_id']) && !empty($validated['prefill_type'])) {
            $prefillImage = Image::find((int) $validated['prefill_image_id']);

            if ($prefillImage) {
                $payload = [
                    'id' => $prefillImage->id,
                    'url' => route('article.image', $prefillImage),
                    'original_name' => $prefillImage->original_name,
                    'path' => $prefillImage->path,
                ];

                if ($validated['prefill_type'] === 'header' && str_contains((string) $prefillImage->path, '/header/')) {
                    $prefillHeaderImage = $payload;
                }

                if ($validated['prefill_type'] === 'body' && str_contains((string) $prefillImage->path, '/body/')) {
                    $prefillBodyImages = [$payload];
                }
            }
        }

        return view('dashboard.top.create', compact('user', 'data', 'images', 'venues', 'prefillHeaderImage', 'prefillBodyImages', 'userEmails'));
    }

    public function edit(Article $article)
    {
        $user = auth()->user();
        $article->load(['images', 'reservationSlots']);
        $images = Image::orderBy('created_at', 'desc')->get();
        $venues = $this->availableVenues($article);
        $currentRole = Auth::user()->role;
        $excludeRoles = match ($currentRole) {
            'developer' => [],
            'system'    => ['developer'],
            'admin'     => ['developer', 'system'],
            'manager'   => ['developer', 'system', 'admin'],
            default     => ['developer', 'system', 'admin', 'manager'],
        };
        $userEmails = User::query()
            ->when(!empty($excludeRoles), fn($q) => $q->whereNotIn('role', $excludeRoles))
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->orderBy('id')
            ->pluck('email')
            ->unique()
            ->values();

        return view('dashboard.top.edit', compact('user', 'article', 'images', 'venues', 'userEmails'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateArticle($request);

        $user = Auth::user();

        DB::transaction(function () use ($request, $validated, $user) {
            $headerImage = $this->resolveHeaderImage($request, $validated);
            $bodyImages = $this->resolveBodyImages($request, $validated);

            $article = Article::create([
                'user_id' => $user->id,
                'title' => $validated['title'],
                'body' => $validated['body'],
                'freeword_1' => $validated['freeword_1'] ?? null,
                'freeword_2' => $validated['freeword_2'] ?? null,
                'header_image' => $headerImage['path'],
                'body_image' => $bodyImages['paths'],
                'body_image_captions' => array_values(array_filter($validated['body_image_captions'] ?? [], fn ($v) => $v !== null)),
                'memo' => $validated['memo'] ?? null,
                'manager' => $validated['manager'] ?? null,
                'venue_id' => $validated['venue_id'] ?? null,
                'emails' => !empty($validated['emails'])
                    ? array_values(array_filter($validated['emails'], fn ($email) => !empty($email)))
                    : null,
                'published_at' => $validated['published_at'] ?? null,
                'unpublished_at' => $validated['unpublished_at'] ?? null,
                'status' => 'draft',
            ]);

            $this->syncArticleImages($article, null, $headerImage['meta'], $bodyImages['meta'], true, false, true, []);
            $this->syncReservationSlots($article, $validated['slots'] ?? []);
        });

        return redirect()->route('top.index')->with('msg', '記事を保存しました');
    }

    public function update(Request $request, Article $article): RedirectResponse
    {
        $validated = $this->validateArticle($request, $article);

        DB::transaction(function () use ($request, $validated, $article) {
            $replaceHeader = $this->hasUploadedFile($request, 'header_image') || !empty($validated['header_selected_image_id']);
            $removeHeader = !$replaceHeader && !empty($validated['remove_header_image']);
            $addBody = $this->hasUploadedFile($request, 'body_image') || !empty($validated['body_selected_image_ids']);
            $originalHeaderImage = $article->header_image;
            $originalBodyImages = $article->body_image ?? [];
            $originalBodyCaptions = $article->body_image_captions ?? [];
            $removeBodyIndexes = collect($validated['remove_body_image_indexes'] ?? [])
                ->map(fn ($index) => (int) $index)
                ->filter(fn ($index) => $index >= 0)
                ->unique()
                ->values();

            $removedBodyPaths = [];
            $keptBodyImages = [];
            $keptBodyCaptions = [];
            $editedExistingCaptions = $validated['existing_body_image_captions'] ?? [];

            foreach ($originalBodyImages as $index => $path) {
                if ($removeBodyIndexes->contains($index)) {
                    $removedBodyPaths[] = $path;
                    continue;
                }

                $keptBodyImages[] = $path;
                $keptBodyCaptions[] = array_key_exists($index, $editedExistingCaptions)
                    ? $editedExistingCaptions[$index]
                    : ($originalBodyCaptions[$index] ?? null);
            }

            $headerImage = $replaceHeader
                ? $this->resolveHeaderImage($request, $validated)
                : ['path' => $removeHeader ? null : $article->header_image, 'meta' => null];

            if ($addBody) {
                $newBodyImages = $this->resolveBodyImages($request, $validated);
                $bodyImages = [
                    'paths' => array_merge($keptBodyImages, $newBodyImages['paths']),
                    'meta' => $newBodyImages['meta'],
                ];
                $newCaptions = array_values(array_filter($validated['body_image_captions'] ?? [], fn ($v) => $v !== null));
                $mergedCaptions = array_merge($keptBodyCaptions, $newCaptions);
            } else {
                $bodyImages = ['paths' => $keptBodyImages, 'meta' => []];
                $mergedCaptions = $keptBodyCaptions;
            }

            $article->update([
                'title' => $validated['title'],
                'body' => $validated['body'],
                'freeword_1' => $validated['freeword_1'] ?? null,
                'freeword_2' => $validated['freeword_2'] ?? null,
                'header_image' => $headerImage['path'],
                'body_image' => $bodyImages['paths'],
                'body_image_captions' => $mergedCaptions,
                'memo' => $validated['memo'] ?? null,
                'manager' => $validated['manager'] ?? null,
                'venue_id' => $validated['venue_id'] ?? null,
                'emails' => !empty($validated['emails'])
                    ? array_values(array_filter($validated['emails'], fn ($email) => !empty($email)))
                    : null,
                'published_at' => $validated['published_at'] ?? null,
                'unpublished_at' => $validated['unpublished_at'] ?? null,
            ]);

            $this->syncArticleImages(
                $article,
                $originalHeaderImage,
                $headerImage['meta'],
                $bodyImages['meta'],
                $replaceHeader,
                $removeHeader,
                $addBody,
                $removedBodyPaths
            );

            $article->reservationSlots()->delete();
            $this->syncReservationSlots($article, $validated['slots'] ?? []);
        });

        return redirect()->route('top.show', $article)->with('msg', '記事を更新しました');
    }

    public function show($id)
    {
        $user = auth()->user();
        $article = Article::with([
                'user',
                'venue',
                'images',
                'reservationSlots' => fn ($query) => $query->orderBy('date')->orderBy('start_time'),
            ])
            ->findOrFail($id);

        return view('dashboard.top.show', compact('user', 'article'));
    }

    public function updateStatus(Request $request, Article $article): RedirectResponse
    {
        $validated = $request->validate([
            'next_status' => ['required', 'string', 'in:publish,draft'],
        ]);

        $today = Carbon::today();
        $publishedDate = $article->published_at?->copy()->startOfDay();
        $unpublishedDate = $article->unpublished_at?->copy()->startOfDay();

        if (!$publishedDate) {
            return back()->with('msg', '公開日が未設定のため変更できません。');
        }

        if ($unpublishedDate && $today->gt($unpublishedDate)) {
            return back()->with('msg', '公開期間が終了しているため変更できません。');
        }

        $allowedNextStatus = null;

        if ($article->status === 'draft') {
            $allowedNextStatus = 'publish';
        } elseif ($article->status === 'publish') {
            $allowedNextStatus = 'draft';
        }

        if (!$allowedNextStatus || $validated['next_status'] !== $allowedNextStatus) {
            return back()->with('msg', '現在の状態ではこの操作はできません。');
        }

        $article->update([
            'status' => $allowedNextStatus,
        ]);

        return back()->with('msg', $allowedNextStatus === 'publish' ? '公開しました。' : '公開を中止しました。');
    }

    public function destroy(Article $article): RedirectResponse
    {
        $article->delete();

        return redirect()->route('top.index')->with('msg', '記事を削除しました');
    }

    public function image(Image $image)
    {
        $path = ltrim((string) preg_replace('#^/?storage/#', '', (string) $image->path), '/');

        if (!Storage::disk('public')->exists($path)) {
            abort(404);
        }

        return Storage::disk('public')->response(
            $path,
            $image->original_name,
            ['Content-Type' => $image->mime_type ?: 'image/webp']
        );
    }

    private function validateArticle(Request $request, ?Article $article = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'freeword_1' => ['nullable', 'string', 'max:255'],
            'freeword_2' => ['nullable', 'string', 'max:255'],
            'memo' => ['nullable', 'string', 'max:255'],
            'header_image' => ['nullable', 'image', 'max:10240'],
            'header_selected_image_id' => ['nullable', 'integer', 'exists:images,id'],
            'remove_header_image' => ['nullable', 'boolean'],
            'body_image' => ['nullable', 'array'],
            'body_image.*' => ['nullable', 'image', 'max:10240'],
            'body_selected_image_ids' => ['nullable', 'array'],
            'body_selected_image_ids.*' => ['nullable', 'integer', 'exists:images,id'],
            'remove_body_image_indexes' => ['nullable', 'array'],
            'remove_body_image_indexes.*' => ['nullable', 'integer', 'min:0'],
            'existing_body_image_captions' => ['nullable', 'array'],
            'existing_body_image_captions.*' => ['nullable', 'string', 'max:255'],
            'body_image_captions' => ['nullable', 'array'],
            'body_image_captions.*' => ['nullable', 'string', 'max:255'],
            'emails' => ['nullable', 'array'],
            'emails.*' => ['nullable', 'email:rfc,dns', 'max:255'],
            'manager' => ['nullable', 'string', 'max:255'],
            'venue_id' => [
                'nullable',
                'integer',
                'exists:article_venues,id',
            ],
            'slots' => ['nullable', 'array'],
            'slots.*.date' => ['nullable', 'date'],
            'slots.*.dates' => ['nullable', 'array'],
            'slots.*.dates.*' => ['nullable', 'date'],
            'slots.*.start_hour' => ['nullable', 'date_format:H'],
            'slots.*.start_minute' => ['nullable', 'in:00,15,30,45'],
            'slots.*.end_hour' => ['nullable', 'date_format:H'],
            'slots.*.end_minute' => ['nullable', 'in:00,15,30,45'],
            'slots.*.capacity' => ['nullable', 'integer', 'min:1'],
            'published_at' => ['nullable', 'date'],
            'unpublished_at' => ['nullable', 'date', 'after_or_equal:published_at'],
        ]);
    }

    private function availableVenues(?Article $article = null)
    {
        return ArticleVenue::query()
            ->orderBy('venue_name')
            ->get();
    }

    private function resolveHeaderImage(Request $request, array $validated): array
    {
        $headerImagePath = null;
        $headerImageMeta = null;
        $uploadedHeader = $this->uploadedFiles($request, 'header_image')[0] ?? null;

        if ($uploadedHeader) {
            $headerImage = $this->storeAsWebp($uploadedHeader, 'uploads/header');
            $headerImagePath = $headerImage['path'];
            $headerImageMeta = [
                'path' => $headerImage['path'],
                'original_name' => $headerImage['original_name'],
                'size' => $headerImage['size'],
                'mime_type' => 'image/webp',
            ];
        } elseif (!empty($validated['header_selected_image_id'])) {
            $selectedHeader = Image::find($validated['header_selected_image_id']);
            if ($selectedHeader && str_contains((string) $selectedHeader->path, '/header/')) {
                $headerImagePath = $selectedHeader->path;
                $headerImageMeta = [
                    'path' => $selectedHeader->path,
                    'original_name' => $selectedHeader->original_name,
                    'size' => $selectedHeader->size,
                    'mime_type' => $selectedHeader->mime_type,
                ];
            }
        }

        return ['path' => $headerImagePath, 'meta' => $headerImageMeta];
    }

    private function resolveBodyImages(Request $request, array $validated): array
    {
        $bodyImagePaths = [];
        $bodyImagesMeta = [];
        $uploadedBodyImages = $this->uploadedFiles($request, 'body_image');
        $selectedBodyImageIds = collect($validated['body_selected_image_ids'] ?? [])
            ->filter(fn ($id) => !empty($id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($selectedBodyImageIds->isNotEmpty()) {
            $selectedBodyImages = Image::whereIn('id', $selectedBodyImageIds)
                ->get()
                ->filter(fn ($image) => str_contains((string) $image->path, '/body/'));

            foreach ($selectedBodyImageIds as $selectedId) {
                $selectedBody = $selectedBodyImages->firstWhere('id', $selectedId);
                if (!$selectedBody) {
                    continue;
                }
                $bodyImagePaths[] = $selectedBody->path;
                $bodyImagesMeta[] = [
                    'path' => $selectedBody->path,
                    'original_name' => $selectedBody->original_name,
                    'size' => $selectedBody->size,
                    'mime_type' => $selectedBody->mime_type,
                ];
            }
        }

        if (!empty($uploadedBodyImages)) {
            foreach ($uploadedBodyImages as $image) {
                $storedBodyImage = $this->storeAsWebp($image, 'uploads/body');
                $bodyImagePaths[] = $storedBodyImage['path'];
                $bodyImagesMeta[] = [
                    'path' => $storedBodyImage['path'],
                    'original_name' => $storedBodyImage['original_name'],
                    'size' => $storedBodyImage['size'],
                    'mime_type' => 'image/webp',
                ];
            }
        }

        return ['paths' => $bodyImagePaths, 'meta' => $bodyImagesMeta];
    }

    private function hasUploadedFile(Request $request, string $key): bool
    {
        return !empty($this->uploadedFiles($request, $key));
    }

    private function uploadedFiles(Request $request, string $key): array
    {
        return collect(Arr::wrap($request->file($key)))
            ->flatten(10)
            ->filter(fn ($file) => $file instanceof UploadedFile && $file->isValid())
            ->values()
            ->all();
    }

    private function syncArticleImages(
        Article $article,
        ?string $originalHeaderImage,
        ?array $headerImageMeta,
        array $bodyImagesMeta,
        bool $replaceHeader,
        bool $removeHeader,
        bool $replaceBody,
        array $removedBodyPaths = []
    ): void
    {
        if (($replaceHeader || $removeHeader) && !empty($originalHeaderImage)) {
            $article->images()->where('path', $originalHeaderImage)->delete();
        }

        if (!empty($removedBodyPaths)) {
            $article->images()->whereIn('path', $removedBodyPaths)->delete();
        }

        $sortOrder = (int) ($article->images()->max('sort_order') ?? -1) + 1;

        if ($replaceHeader && !empty($headerImageMeta)) {
            Image::create([
                'article_id' => $article->id,
                'path' => $headerImageMeta['path'],
                'original_name' => $headerImageMeta['original_name'],
                'size' => $headerImageMeta['size'],
                'mime_type' => $headerImageMeta['mime_type'] ?: 'image/webp',
                'sort_order' => $sortOrder++,
            ]);
        }

        if ($replaceBody) {
            foreach ($bodyImagesMeta as $bodyImageMeta) {
                Image::create([
                    'article_id' => $article->id,
                    'path' => $bodyImageMeta['path'],
                    'original_name' => $bodyImageMeta['original_name'],
                    'size' => $bodyImageMeta['size'],
                    'mime_type' => $bodyImageMeta['mime_type'] ?: 'image/webp',
                    'sort_order' => $sortOrder++,
                ]);
            }
        }
    }

    private function syncReservationSlots(Article $article, array $slots): void
    {
        foreach ($slots as $slot) {
            $dates = [];
            if (!empty($slot['dates']) && is_array($slot['dates'])) {
                $dates = array_values(array_filter($slot['dates']));
            } elseif (!empty($slot['date'])) {
                $dates = [$slot['date']];
            }

            $startHour = $slot['start_hour'] ?? null;
            $startMinute = $slot['start_minute'] ?? null;
            $endHour = $slot['end_hour'] ?? null;
            $endMinute = $slot['end_minute'] ?? null;
            $capacity = isset($slot['capacity']) && $slot['capacity'] !== null && $slot['capacity'] !== ''
                ? (int) $slot['capacity']
                : null;

            if (
                empty($dates)
                || $startHour === null
                || $startMinute === null
                || $endHour === null
                || $endMinute === null
            ) {
                continue;
            }

            $startTime = sprintf('%s:%s:00', $startHour, $startMinute);
            $endTime = sprintf('%s:%s:00', $endHour, $endMinute);

            if ($endTime < $startTime) {
                continue;
            }

            foreach ($dates as $date) {
                $article->reservationSlots()->create([
                    'date' => $date,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'capacity' => $capacity ?? 0,
                ]);
            }
        }
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
            'path' => $path,
            'original_name' => $originalName,
            'size' => Storage::disk('public')->size($path),
        ];
    }
}
