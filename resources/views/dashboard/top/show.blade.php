<x-app-layout>
    <x-slot:title>記事詳細</x-slot:title>
    <x-slot:page>index</x-slot:page>
    <x-slot:name>{{ $user->name }}</x-slot:name>
    <x-slot:role>{{ $user->role }}</x-slot:role>
    <x-slot:old>{{ @$old }}</x-slot:old>

    @php
        $headerImage = $article->images->firstWhere('path', $article->header_image)
            ?? $article->images->first(fn ($image) => str_contains((string) $image->path, '/header/'));

        $bodyImageCaptions = collect($article->body_image_captions ?? []);
        $bodyImages = collect($article->body_image ?? [])
            ->map(function ($path, $index) use ($article, $bodyImageCaptions) {
                $image = $article->images->firstWhere('path', $path);

                if (!$image) {
                    return null;
                }

                return [
                    'image' => $image,
                    'caption' => (string) ($bodyImageCaptions->get($index) ?? ''),
                ];
            })
            ->filter();

            $rawEmails = $article->emails;
            $notificationEmails = collect(is_array($rawEmails) ? $rawEmails : preg_split('/[,\r\n]+/', (string) $rawEmails))
                ->map(fn ($email) => trim((string) $email))
                ->filter();

            $guestPageUrl = route('show.public', ['token' => $article->public_token]);
            $today = now()->startOfDay();
            $publishedDate = $article->published_at?->copy()->startOfDay();
            $unpublishedDate = $article->unpublished_at?->copy()->startOfDay();
            $isEnded = $unpublishedDate && $today->gt($unpublishedDate);

            $publishBtnLabel = '公開未設定';
            $publishBtnHoverLabel = null;
            $publishBtnClass = 'is-disabled';
            $publishBtnAction = null;

            if ($publishedDate) {
                if ($isEnded) {
                    $publishBtnLabel = '公開終了';
                    $publishBtnClass = 'is-disabled';
                } elseif ($article->status === 'draft') {
                    $publishBtnLabel = '公開する';
                    $publishBtnClass = 'is-ready';
                    $publishBtnAction = 'publish';
                } elseif ($article->status === 'publish' && $today->lt($publishedDate)) {
                    $publishBtnLabel = '公開予約中';
                    $publishBtnHoverLabel = '公開中止';
                    $publishBtnClass = 'is-scheduled';
                    $publishBtnAction = 'draft';
                } elseif ($article->status === 'publish') {
                    $publishBtnLabel = '公開中';
                    $publishBtnHoverLabel = '公開中止';
                    $publishBtnClass = 'is-live';
                    $publishBtnAction = 'draft';
                }
            }
    @endphp

    <div class="content top-detail-page">
        <div class="top-detail-shell">
            <div class="top-detail-head">
                <div>
                    <p class="top-detail-date">{{ optional($article->published_at)->format('Y.m.d') ?: '未設定' }} - {{ optional($article->unpublished_at)->format('Y.m.d') ?: '未設定' }}</p>
                    <h2>{{ $article->title }}</h2>
                </div>
                <div class="top-detail-actions">
                    <a href="{{ route('article.edit', $article) }}" class="top-detail-edit">編集する</a>
                    <a href="{{ route('top.index') }}" class="top-detail-back">一覧へ戻る</a>
                </div>
            </div>

            <div class="top-detail-summary">
                <span class="summary">会場：{{ $article->venue?->venue_name ?: '会場未設定' }}</span>
                <span class="summary">公開日：{{ optional($article->published_at)->format('Y.m.d') ?: '公開日未設定' }}</span>
                <span class="summary">終了日：{{ optional($article->unpublished_at)->format('Y.m.d') ?: '終了日未設定' }}</span>
                <span class="summary">担当者：{{ $article->manager ?: '担当者未設定' }}</span>
                <span class="preview-btn">
                    <a href="{{ $guestPageUrl }}" target="_blank" rel="noopener noreferrer">プレビュー</a>
                </span>
                <span class="publish-btn {{ $publishBtnClass }}">
                    @if ($publishBtnAction)
                        <form action="{{ route('article.status.update', $article) }}" method="POST">
                            @csrf
                            <input type="hidden" name="next_status" value="{{ $publishBtnAction }}">
                            <button type="submit" class="publish-btn-control">
                                <span class="default-label">{{ $publishBtnLabel }}</span>
                                @if ($publishBtnHoverLabel)
                                    <span class="hover-label">{{ $publishBtnHoverLabel }}</span>
                                @endif
                            </button>
                        </form>
                    @else
                        <span class="publish-btn-control is-disabled">{{ $publishBtnLabel }}</span>
                    @endif
                </span>
            </div>

            @if ($headerImage)
                <div class="top-detail-header-image">
                    <img src="{{ route('article.image', $headerImage) }}" alt="{{ $article->title }}">
                </div>
            @endif

            <div class="top-detail-card">
                <h3>本文</h3>
                <div class="top-detail-body">{!! $article->body !!}</div>
            </div>

            @if ($article->freeword_1 || $article->freeword_2)
                <div class="top-detail-card">
                    <div class="top-detail-freewords">
                        @if ($article->freeword_1)
                            <p><span>日程</span><strong>{{ $article->freeword_1 }}</strong></p>
                        @endif
                        @if ($article->freeword_2)
                            <p><span>時間</span><strong>{{ $article->freeword_2 }}</strong></p>
                        @endif
                    </div>
                </div>
            @endif

            @if ($bodyImages->isNotEmpty())
                <div class="top-detail-card">
                    <h3>本文画像</h3>
                    <div class="top-detail-gallery">
                        @foreach ($bodyImages as $bodyImage)
                            <figure>
                                <img src="{{ route('article.image', $bodyImage['image']) }}" alt="{{ $article->title }} 本文画像">
                                @if (!empty($bodyImage['caption']))
                                    <figcaption>{{ $bodyImage['caption'] }}</figcaption>
                                @endif
                            </figure>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="top-detail-grid">
                <div class="top-detail-card">
                    <h3>配信設定</h3>
                    <dl class="top-detail-definition">
                        <div>
                            <dt>作成者</dt>
                            <dd>{{ $article->user?->name ?: '未設定' }}</dd>
                        </div>
                        <div>
                            <dt>通知メールアドレス</dt>
                            <dd>{{ $notificationEmails->isNotEmpty() ? $notificationEmails->implode(' / ') : '未設定' }}</dd>
                        </div>
                        <div>
                            <dt>ステータス</dt>
                            <dd>{{ $article->topListStatusLabel }}</dd>
                        </div>
                        <div>
                            <dt>公開開始日</dt>
                            <dd>{{ $article->published_at?->format('Y.m.d') ?: '未設定' }}</dd>
                        </div>
                        <div>
                            <dt>公開終了日</dt>
                            <dd>{{ $article->unpublished_at?->format('Y.m.d') ?: '未設定' }}</dd>
                        </div>
                        <div>
                            <dt>公開URL</dt>
                            <dd><a href="{{ $guestPageUrl }}" target="_blank" rel="noopener noreferrer">{{ $guestPageUrl }}</a></dd>
                        </div>
                    </dl>
                </div>

                <div class="top-detail-card">
                    <h3>予約枠</h3>
                    @if ($article->reservationSlots->isNotEmpty())
                        <div class="top-detail-slots">
                            @foreach ($article->reservationSlots as $slot)
                                <div class="top-detail-slot">
                                    <p>{{ $slot->date?->format('Y.m.d') }}</p>
                                    <p>{{ \Carbon\Carbon::parse((string) $slot->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse((string) $slot->end_time)->format('H:i') }}</p>
                                    <p>予約枠： {{ $slot->capacity }} </p>
                                    <p class="{{ $slot->reservations_count > 0 ? 'active' : '' }}">予約 {{ $slot->reservations_count ?? 0 }} 件</p>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="top-detail-empty">予約枠は未設定です。</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>