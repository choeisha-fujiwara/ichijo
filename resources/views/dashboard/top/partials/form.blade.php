@php
    $isEdit = $article !== null;
    $prefillHeaderImage = !$isEdit ? ($prefillHeaderImage ?? null) : null;
    $prefillBodyImages = !$isEdit ? collect($prefillBodyImages ?? [])->values() : collect();
    $prefillHeaderSelectedId = $prefillHeaderImage['id'] ?? null;
    $prefillHeaderPreviewUrl = $prefillHeaderImage['url'] ?? null;
    $prefillBodySelections = $prefillBodyImages
        ->map(fn ($image) => [
            'id' => $image['id'] ?? null,
            'url' => $image['url'] ?? null,
            'caption' => '',
        ])
        ->filter(fn ($image) => !empty($image['id']) && !empty($image['url']))
        ->values()
        ->all();
    $currentExistingBodyCaptions = old('existing_body_image_captions', $isEdit ? ($article->body_image_captions ?? []) : []);
    $currentHeaderImage = $isEdit
        ? ($article->images->firstWhere('path', $article->header_image)
            ?? $article->images->first(fn ($image) => str_contains((string) $image->path, '/header/')))
        : null;
    $initialHeaderPreviewUrl = $currentHeaderImage ? route('article.image', $currentHeaderImage) : ($prefillHeaderPreviewUrl ?: null);
    $currentBodyImageItems = $isEdit
        ? collect($article->body_image ?? [])
            ->map(function ($path, $index) use ($article, $currentExistingBodyCaptions) {
                $image = $article->images->firstWhere('path', $path);
                $normalizedPath = ltrim((string) preg_replace('#^/?storage/#', '', (string) $path), '/');
                $imageUrl = $image
                    ? route('article.image', $image)
                    : Illuminate\Support\Facades\Storage::disk('public')->url($normalizedPath);

                if (empty($normalizedPath) && !$image) {
                    return null;
                }

                return [
                    'index' => $index,
                    'image' => $image,
                    'url' => $imageUrl,
                    'caption' => $currentExistingBodyCaptions[$index] ?? null,
                ];
            })
            ->filter()
            ->values()
        : collect();
    if ($isEdit) {
        $initialSlots = $article->reservationSlots
            ->map(function ($slot) {
                $startTimeText = (string) ($slot->start_time ?? '');
                $endTimeText = (string) ($slot->end_time ?? '');

                preg_match('/^(\d{1,2}):(\d{2})/', $startTimeText, $startParts);
                preg_match('/^(\d{1,2}):(\d{2})/', $endTimeText, $endParts);

                return [
                    'id' => $slot->id,
                    'dates' => [optional($slot->date)->format('Y-m-d')],
                    'start_hour' => !empty($startParts[1]) ? str_pad($startParts[1], 2, '0', STR_PAD_LEFT) : null,
                    'start_minute' => !empty($startParts[2]) ? str_pad($startParts[2], 2, '0', STR_PAD_LEFT) : null,
                    'end_hour' => !empty($endParts[1]) ? str_pad($endParts[1], 2, '0', STR_PAD_LEFT) : null,
                    'end_minute' => !empty($endParts[2]) ? str_pad($endParts[2], 2, '0', STR_PAD_LEFT) : null,
                    'capacity' => $slot->capacity,
                ];
            })
            ->values()
            ->all();
    } else {
        $initialSlots = old('slots', []);
    }
    $emailOptions = collect($userEmails ?? [])
        ->filter(fn ($option) => is_array($option) && !empty($option['value']))
        ->mapWithKeys(fn ($option) => [
            $option['value'] => $option['label'] ?? $option['value'],
        ])
        ->all();
    $initialEmails = collect(old('emails', $isEdit ? ($article->emails ?? []) : []))
        ->filter()
        ->values();
    if ((string) (auth()->user()?->role ?? '') !== 'developer') {
        $initialEmails = $initialEmails->intersect(array_keys($emailOptions))->values();
    }
    $currentBodyImageUrls = $isEdit && $currentBodyImageItems->isNotEmpty()
        ? $currentBodyImageItems->pluck('url')->values()->all()
        : [];
    $currentBodyCaptions = $isEdit && $currentBodyImageItems->isNotEmpty()
        ? $currentBodyImageItems
            ->pluck('caption')
            ->map(fn ($caption) => (string) ($caption ?? ''))
            ->values()
            ->all()
        : [];
    $currentBodyImageSourceIndexes = $isEdit
        ? $currentBodyImageItems
            ->pluck('index')
            ->map(fn ($index) => (int) $index)
            ->values()
            ->all()
        : [];
    $venuesPayload = collect($venues)
        ->map(fn ($venue) => [
            'id' => $venue->id,
            'name' => $venue->venue_name,
            'address' => $venue->address,
            'phone' => $venue->phone,
            'fax' => $venue->fax,
            'access' => $venue->access,
            'map_url' => $venue->map_url,
            'image_url' => !empty($venue->image) ? route('venue.image', $venue) : null,
        ])
        ->values()
        ->all();
@endphp

<div
    class="content article-page"
    x-data="articleFormPreview({
        title: @js(old('title', $article->title ?? '')),
        body: @js(old('body', $article->body ?? '')),
        freeword1: @js(old('freeword_1', $article->freeword_1 ?? '')),
        freeword2: @js(old('freeword_2', $article->freeword_2 ?? '')),
        manager: @js(old('manager', $article->manager ?? '')),
        venueId: @js((string) old('venue_id', $article->venue_id ?? '')),
        venues: @js($venuesPayload),
        headerImageUrl: @js($initialHeaderPreviewUrl),
        bodyImageUrls: @js($currentBodyImageUrls),
        bodyCaptions: @js($currentBodyCaptions),
        bodyImageSourceIndexes: @js($currentBodyImageSourceIndexes),
        removeHeaderImage: @js((bool) old('remove_header_image', false)),
        removeBodyIndexes: @js(array_values(array_map('intval', old('remove_body_image_indexes', [])))),
        publishedAt: @js(old('published_at', optional($article->published_at ?? null)->format('Y-m-d'))),
        unpublishedAt: @js(old('unpublished_at', optional($article->unpublished_at ?? null)->format('Y-m-d')))
    })"
    x-on:rich-editor-change="handleRichEditorChange($event)"
    x-on:image-upload-change.window="handleImageUploadChange($event)"
>
    <div class="article-shell">
        <div class="article-head">
            <div class="article-head-main">
                <h2>{{ $isEdit ? '記事編集' : '記事作成' }}</h2>
                <p>{{ $isEdit ? '内容を更新して保存してください。' : '必要な項目を入力して公開設定を行ってください。' }}</p>
            </div>
        </div>
        @if ($errors->any())
            <div class="input-item validation-errors">
                <label>入力内容を確認してください</label>
                <ul class="msg">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form action="{{ $isEdit ? route('article.update', $article) : route('article.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @if ($isEdit)
                @method('PUT')
            @endif
            <div class="input-form">
                @if ($isEdit && $currentHeaderImage)
                    <div class="input-item existing-media">
                        <label>現在のヘッダー画像</label>
                        {{-- <p class="input-note">新しい画像を選択したときだけ差し替わります。未選択のまま保存すると現在の画像を維持します。</p> --}}
                        <div class="existing-media-preview single" :class="{ 'will-remove': preview.removeHeaderImage }">
                            <img src="{{ route('article.image', $currentHeaderImage) }}" alt="現在のヘッダー画像">
                        </div>
                        <button
                            type="button"
                            class="existing-media-remove-btn"
                            :class="{ 'is-active': preview.removeHeaderImage }"
                            @click="preview.removeHeaderImage = !preview.removeHeaderImage"
                        ><span class="material-symbols-outlined delete">delete</span>
                            <span x-text="preview.removeHeaderImage ? '削除を取り消す' : '削除する'"></span>
                        </button>
                        <input type="hidden" name="remove_header_image" :value="preview.removeHeaderImage ? 1 : 0">
                    </div>
                @endif
                <div class="input-item header-image">
                    <label>{{ $isEdit ? 'ヘッダー画像を変更' : 'ヘッダー画像' }}</label>
                    @if ($isEdit)
                        {{-- <p class="input-note">ファイルを選択するか、保存画像から選択すると差し替えます。</p> --}}
                    @endif
                    <x-image-upload
                        name="header_image"
                        :saved-images="$images"
                        saved-type="header"
                        selected-name="header_selected_image_id"
                        :initial-selected-id="$prefillHeaderSelectedId"
                        :initial-preview-url="$prefillHeaderPreviewUrl"
                    />
                </div>
                <div class="input-item title-text edit-title">
                    <label>タイトル</label>
                    <textarea name="title" placeholder="タイトルを入力してください" class="input-title" x-model="preview.title" required>{{ old('title', $article->title ?? '') }}</textarea>
                </div>
                <div class="input-item body-text edit-body-text">
                    <label>本文</label>
                    <x-rich-text-editor name="body" placeholder="本文を入力してください" :value="old('body', $article->body ?? '')" />
                </div>
                <div class="input-row">
                    <div class="input-item freeword-item">
                        <label>日程</label>
                        <input type="text" name="freeword_1" placeholder="任意入力" value="{{ old('freeword_1', $article->freeword_1 ?? '') }}" x-model="preview.freeword1">
                    </div>
                    <div class="input-item freeword-item">
                        <label>時間</label>
                        <input type="text" name="freeword_2" placeholder="任意入力" value="{{ old('freeword_2', $article->freeword_2 ?? '') }}" x-model="preview.freeword2">
                    </div>
                </div>
                <div class="input-row">
                    <div class="input-item">
                        <label>担当者</label>
                        <input type="text" name="manager" value="{{ old('manager', $article->manager ?? '') }}" x-model="preview.manager" placeholder="任意入力">
                    </div>
                    <div class="input-item">
                        <label>会場</label>
                        <select name="venue_id" x-model="preview.venueId" required>
                            <option value="">選択してください</option>
                            @foreach ($venues as $venue)
                                <option value="{{ $venue->id }}" @selected((string) old('venue_id', $article->venue_id ?? '') === (string) $venue->id)>
                                    {{ $venue->venue_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('venue_id')
                            <p class="input-note">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                @if ($isEdit && $currentBodyImageItems->isNotEmpty())
                    <div class="input-item existing-media">
                        <label>現在の本文画像</label>
                        {{-- <p class="input-note">各画像のキャプションを編集できます。削除したい画像は削除ボタンを選択してください。</p> --}}
                        <div class="existing-media-preview gallery">
                            @foreach ($currentBodyImageItems as $item)
                                <div class="existing-media-card">
                                    <img src="{{ $item['url'] }}" alt="現在の本文画像{{ $loop->iteration }}">
                                    <textarea
                                        name="existing_body_image_captions[{{ $item['index'] }}]"
                                        rows="4"
                                        placeholder="キャプションを入力（任意）"
                                        class="existing-media-caption-input"
                                        x-model="existingBodyCaptions[{{ $loop->index }}]"
                                        @input="syncBodyImagePreview()"
                                    >{{ old('existing_body_image_captions.' . $item['index'], $item['caption']) }}</textarea>
                                    <button
                                        type="button"
                                        class="existing-media-remove-btn"
                                        :class="{ 'is-active': isBodyImageMarkedForRemoval({{ $item['index'] }}) }"
                                        @click="toggleBodyImageRemoval({{ $item['index'] }})"
                                    >
                                        この画像を削除
                                    </button>
                                </div>
                            @endforeach
                            <template x-for="index in preview.removeBodyIndexes" :key="`remove-${index}`">
                                <input type="hidden" name="remove_body_image_indexes[]" :value="index">
                            </template>
                        </div>
                    </div>
                @endif
                <div class="input-item body-image">
                    <label>{{ $isEdit ? '本文画像を追加' : '本文画像' }}</label>
                    @if ($isEdit)
                        <p class="input-note">画像を選択すると既存の画像に追加されます。</p>
                    @endif
                    <x-image-upload
                        name="body_image"
                        :addable="true"
                        :saved-images="$images"
                        saved-type="body"
                        selected-name="body_selected_image_ids"
                        :initial-selections="$prefillBodySelections"
                    />
                </div>
                <div class="input-item reservation-slot">
                    <label>予約枠</label>
                    <x-reservation-slot-input :initial-slots="$initialSlots" />
                </div>
                <div class="input-item memo">
                    <label>アンケート最終項目変更</label>
                    <input type="text" name="memo" placeholder="例）今回のイベントの感想を教えてください" class="input-memo" value="{{ old('memo', $article->memo ?? '') }}">
                </div>
                <div class="input-item emails">
                    <label>送信先メールアドレス</label>
                    <x-email-list-input name="emails" :old-values="$initialEmails" :options="$emailOptions" />
                </div>
                <div class="input-item publish-date">
                    <label>公開期間</label>
                    <div class="publish-date-range">
                        <div class="publish-date-field">
                            <span class="date-label">開始日</span>
                            <input type="text" id="from" name="published_at" value="{{ old('published_at', optional($article->published_at ?? null)->format('Y-m-d')) }}" class="date-input" x-model="preview.publishedAt" autocomplete="off">
                        </div>
                        <span class="material-symbols-outlined">check_indeterminate_small</span>
                        <div class="publish-date-field">
                            <span class="date-label">終了日</span>
                            <input type="text" id="to" name="unpublished_at" value="{{ old('unpublished_at', optional($article->unpublished_at ?? null)->format('Y-m-d')) }}" class="date-input" x-model="preview.unpublishedAt" autocomplete="off">
                        </div>
                    </div>
                </div>
                <input type="hidden" name="state" value="{{ old('state', $article->status ?? 'draft') }}">
                <div class="input-item action-item article-actions">
                    {{-- @if ($isEdit)
                        <a href="{{ route('top.show', $article) }}" class="article-cancel">戻る</a>
                    @endif --}}
                    @if ($isEdit)
                        <button type="button" class="article-delete" onclick="if(confirm('この記事を削除しますか？')) { document.getElementById('article-delete-form').submit(); }">削除する</button>
                    @endif
                    <button type="submit" class="article-submit">{{ $isEdit ? '更新する' : '保存' }}</button>
                </div>
            </div>
        </form>
    </div>

    @if ($isEdit)
        <form action="{{ route('article.destroy', $article) }}" method="POST" style="display: none;" id="article-delete-form">
            @csrf
            @method('DELETE')
        </form>
    @endif
    <aside class="article-preview" aria-label="プレビュー">
        <div class="article-preview-header">
            <h3>プレビュー</h3>
            <a href="{{ route('top.index') }}" class="top-detail-back">一覧に戻る</a>
        </div>
        <div class="article-preview-device">
            <div class="article-preview-screen">
                <section class="event-list article-stage">
                    <div class="article-header" x-show="preview.headerImageUrl">
                        <img :src="preview.headerImageUrl" alt="プレビュー画像">
                    </div>
                    <p class="article-stage-date" x-text="formatPeriod() || '公開期間未設定'"></p>
                    <h1 x-text="preview.title || 'タイトルがここに表示されます'"></h1>
                    <div class="article-body" x-html="preview.body || '<p>本文がここに表示されます。</p>'"></div>
                    <div class="article-freewords" x-show="preview.freeword1 || preview.freeword2">
                        <p x-show="preview.freeword1"><span>日程</span><strong x-text="preview.freeword1"></strong></p>
                        <p x-show="preview.freeword2"><span>時間</span><strong x-text="preview.freeword2"></strong></p>
                    </div>
                    <div class="article-body-images" x-show="Array.isArray(preview.bodyImageUrls) && preview.bodyImageUrls.length > 0">
                        <template x-for="(imageUrl, index) in preview.bodyImageUrls" :key="`${imageUrl}-${index}`">
                            <figure class="article-body-image-item">
                                <img :src="imageUrl" :alt="'本文画像' + (index + 1)">
                                <figcaption x-show="preview.bodyCaptions[index]" x-text="preview.bodyCaptions[index]"></figcaption>
                            </figure>
                        </template>
                    </div>
                    <template x-if="selectedVenue()">
                        <section class="article-venue" aria-label="お問い合わせ先">
                            <h2>お問い合わせ先</h2>
                            <template x-if="selectedVenue().image_url">
                                <div class="article-venue-image">
                                    <img :src="selectedVenue().image_url" :alt="`${selectedVenue().name} の画像`">
                                </div>
                            </template>
                            <dl>
                                <div>
                                    <dt>会場名</dt>
                                    <dd x-text="selectedVenue().name"></dd>
                                </div>
                                <div x-show="selectedVenue().address">
                                    <dt>住所</dt>
                                    <dd x-text="selectedVenue().address"></dd>
                                </div>
                                <div x-show="selectedVenue().phone">
                                    <dt>TEL</dt>
                                    <dd x-text="selectedVenue().phone"></dd>
                                </div>
                                <div x-show="selectedVenue().fax">
                                    <dt>FAX</dt>
                                    <dd x-text="selectedVenue().fax"></dd>
                                </div>
                                <div x-show="selectedVenue().access">
                                    <dt>アクセス</dt>
                                    <dd x-text="selectedVenue().access"></dd>
                                </div>
                                <div x-show="selectedVenue().map_url">
                                    <dt>地図</dt>
                                    <dd>
                                        <a :href="selectedVenue().map_url" target="_blank" rel="noopener noreferrer"><span class="material-symbols-outlined">location_on</span>Google Map を開く</a>
                                    </dd>
                                </div>
                            </dl>
                        </section>
                    </template>
                    <div class="article-form-note">
                        <p>予約フォーム</p>
                        <span>公開ページではこの下に予約入力フォームが続きます。</span>
                    </div>
                </section>
            </div>
        </div>
    </aside>
</div>

@once
<script>
function articleFormPreview(initialState = {}) {
    return {
        existingBodyImageUrls: Array.isArray(initialState.bodyImageUrls) ? initialState.bodyImageUrls : [],
        existingBodyCaptions: Array.isArray(initialState.bodyCaptions) ? initialState.bodyCaptions.map((value) => String(value || '')) : [],
        existingBodyImageSourceIndexes: Array.isArray(initialState.bodyImageSourceIndexes)
            ? initialState.bodyImageSourceIndexes.map((index) => Number(index))
            : [],
        addedBodyImageUrls: [],
        addedBodyCaptions: [],
        preview: {
            title: initialState.title || '',
            body: initialState.body || '',
            freeword1: initialState.freeword1 || '',
            freeword2: initialState.freeword2 || '',
            manager: initialState.manager || '',
            venueId: initialState.venueId || '',
            venues: Array.isArray(initialState.venues) ? initialState.venues : [],
            headerImageUrl: initialState.headerImageUrl || '',
            bodyImageUrls: Array.isArray(initialState.bodyImageUrls) ? initialState.bodyImageUrls : [],
            bodyCaptions: Array.isArray(initialState.bodyCaptions) ? initialState.bodyCaptions : [],
            removeHeaderImage: Boolean(initialState.removeHeaderImage),
            removeBodyIndexes: Array.isArray(initialState.removeBodyIndexes)
                ? initialState.removeBodyIndexes.map((index) => Number(index)).filter((index) => Number.isInteger(index) && index >= 0)
                : [],
            publishedAt: initialState.publishedAt || '',
            unpublishedAt: initialState.unpublishedAt || '',
        },

        init() {
            this.syncBodyImagePreview();
        },

        syncBodyImagePreview() {
            const existingItems = this.existingBodyImageUrls
                .map((url, index) => ({
                    url,
                    caption: String(this.existingBodyCaptions[index] || ''),
                    sourceIndex: Number.isInteger(this.existingBodyImageSourceIndexes[index])
                        ? this.existingBodyImageSourceIndexes[index]
                        : index,
                }))
                .filter((item) => typeof item.url === 'string' && item.url.length > 0)
                .filter((item) => !this.preview.removeBodyIndexes.includes(item.sourceIndex));

            const addedItems = this.addedBodyImageUrls
                .map((url, index) => ({
                    url,
                    caption: String(this.addedBodyCaptions[index] || ''),
                }))
                .filter((item) => typeof item.url === 'string' && item.url.length > 0);

            const merged = [...existingItems, ...addedItems];
            this.preview.bodyImageUrls = merged.map((item) => item.url);
            this.preview.bodyCaptions = merged.map((item) => item.caption);
        },

        toggleBodyImageRemoval(index) {
            const normalized = Number(index);
            if (!Number.isInteger(normalized) || normalized < 0) {
                return;
            }

            if (this.preview.removeBodyIndexes.includes(normalized)) {
                this.preview.removeBodyIndexes = this.preview.removeBodyIndexes.filter((value) => value !== normalized);
                this.syncBodyImagePreview();
                return;
            }

            this.preview.removeBodyIndexes = [...this.preview.removeBodyIndexes, normalized].sort((a, b) => a - b);
            this.syncBodyImagePreview();
        },

        isBodyImageMarkedForRemoval(index) {
            const normalized = Number(index);
            return this.preview.removeBodyIndexes.includes(normalized);
        },

        handleRichEditorChange(event) {
            if (event?.detail?.name !== 'body') {
                return;
            }

            this.preview.body = event.detail.value || '';
        },

        handleImageUploadChange(event) {
            const name = event?.detail?.name;
            const previews = Array.isArray(event?.detail?.previews)
                ? event.detail.previews.filter((value) => typeof value === 'string' && value.length > 0)
                : [];
            const captions = Array.isArray(event?.detail?.captions)
                ? event.detail.captions.map((value) => String(value || ''))
                : [];

            if (name === 'header_image') {
                if (previews.length === 0) {
                    return;
                }

                this.preview.headerImageUrl = previews[0] || '';
                return;
            }

            if (name === 'body_image') {
                this.addedBodyImageUrls = previews;
                this.addedBodyCaptions = captions.length > 0
                    ? captions.slice(0, previews.length)
                    : Array(previews.length).fill('');
                this.syncBodyImagePreview();
            }
        },

        formatPeriod() {
            if (this.preview.publishedAt && this.preview.unpublishedAt) {
                return `${this.preview.publishedAt} - ${this.preview.unpublishedAt}`;
            }

            if (this.preview.publishedAt) {
                return `${this.preview.publishedAt} から公開`;
            }

            if (this.preview.unpublishedAt) {
                return `${this.preview.unpublishedAt} まで公開`;
            }

            return '';
        },

        selectedVenue() {
            return this.preview.venues.find((venue) => String(venue.id) === String(this.preview.venueId)) || null;
        },
    };
}
</script>
@endonce