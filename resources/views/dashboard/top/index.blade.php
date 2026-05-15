<x-app-layout>
    <x-slot:title>記事一覧</x-slot:title>
    <x-slot:page>index</x-slot:page>
    <x-slot:name>{{ $user->name }}</x-slot:name>
    <x-slot:role>{{ $user->role }}</x-slot:role>
    <x-slot:login>{{ $user->last_login_at?->format('Y.m.d H:i') }}</x-slot:login>
    <x-slot:old>{{ @$old }}</x-slot:old>
    @php
        $venues = $venues ?? collect();
        $filters = $filters ?? [];
    @endphp
    <div class="content top-page">
        <div class="top-list-shell">
            <div class="top-list-head">
                <div class="top-list-head-main">
                    <div>
                        <h2>記事一覧</h2>
                        <p>公開前の記事を含め、登録済みの記事を一覧で確認できます。</p>
                    </div>
                    <a href="{{ route('article.index') }}" class="top-list-create-link">＋新規作成</a>
                </div>

                <form method="GET" action="{{ route('top.index') }}" class="top-list-filter" aria-label="記事絞り込み">
                    <div class="top-list-filter-field">
                        <label for="venue_id">会場</label>
                        <select id="venue_id" name="venue_id">
                            <option value="">すべての会場</option>
                            @foreach ($venues as $venue)
                                <option value="{{ $venue->id }}" @selected((string) old('venue_id', $filters['venue_id'] ?? '') === (string) $venue->id)>
                                    {{ $venue->venue_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="top-list-filter-field top-list-filter-field-date">
                        <label for="publish_from">公開期間</label>
                        <div class="top-list-filter-date-range">
                            <input
                                id="publish_from"
                                type="date"
                                name="publish_from"
                                value="{{ old('publish_from', $filters['publish_from'] ?? '') }}"
                            >
                            <span>〜</span>
                            <input
                                id="publish_to"
                                type="date"
                                name="publish_to"
                                value="{{ old('publish_to', $filters['publish_to'] ?? '') }}"
                            >
                        </div>
                    </div>

                    <div class="top-list-filter-actions">
                        <button type="submit" class="top-list-filter-submit">検索</button>
                        <a href="{{ route('top.index') }}" class="top-list-filter-reset">リセット</a>
                    </div>
                </form>

                @if ($errors->any())
                    <p class="top-list-filter-error">{{ $errors->first() }}</p>
                @endif
            </div>

            <div class="top-list-table" role="list">
                @forelse ($data as $item)
                    <div class="top-list-row-wrap">
                        <a href="{{ route('top.show', $item) }}" class="top-list-row" role="listitem">
                            <div class="top-list-main">
                                <span class="create-at">作成日：{{ $item->created_at?->format('Y.m.d') }}</span>
                                <p class="top-list-title">{{ $item->title }}</p>
                                <div class="top-list-meta">
                                    <span>会場：{{ $item->venue?->venue_name ?: '会場未設定' }}</span>
                                    <span>期間：{{ optional($item->published_at)->format('Y.m.d') ?: '未設定' }} 〜 {{ optional($item->unpublished_at)->format('Y.m.d') ?: '未設定' }}</span>
                                    <span>担当：{{ $item->manager ?: '未設定' }}</span>
                                </div>
                            </div>
                            <div class="top-list-image">
                                @php
                                    $thumbImage = $item->images->first(fn($img) => str_contains($img->path, '/body/'))
                                        ?? $item->images->first(fn($img) => str_contains($img->path, '/header/'));
                                @endphp
                                @if ($thumbImage)
                                    <img src="{{ route('article.image', $thumbImage) }}" alt="{{ $item->title }}">
                                @else
                                    <span class="no-image">No Image</span>
                                @endif
                            </div>
                            <div class="top-list-side">
                                <span class="top-list-status {{ $item->top_list_status_class }}">
                                    {{ $item->top_list_status_label }}
                                </span>
                                <span>予約枠数：{{ $item->reservation_slots_capacity_sum ?? 0 }}</span>
                                <span class="{{ $item->reservations_count > 0 ? 'active' : '' }}">予約件数：{{ $item->reservations_count ?? 0 }}</span>
                            </div>
                        </a>
                        <div class="top-list-delete"
                            data-count="{{ (int) ($item->reservations_count ?? 0) }}"
                            data-title="{{ e($item->title) }}"
                            onclick="(function(el){ var c=+el.getAttribute('data-count'); var t=el.getAttribute('data-title'); var m='「'+t+'」を削除しますか？'; if(c>0){m+='\nこの記事には'+c+'件の予約があります。予約も全て削除されます。';} m+='\nこの操作は元に戻せません。'; if(confirm(m)){el.querySelector('form').submit();} })(this)"
                        >
                            <span class="material-symbols-outlined">delete</span>
                            <form x-ref="deleteForm"
                                action="{{ route('article.destroy', $item) }}"
                                method="POST"
                                style="display:none"
                            >
                                @csrf
                                @method('DELETE')
                            </form>
                        </div>
                    </div>
                    @empty
                        <div class="top-list-empty">
                            <p>記事データがありません。</p>
                            <a href="{{ route('article.index') }}">記事を作成する</a>
                        </div>
                    @endforelse
            </div>

            @if (method_exists($data, 'links'))
                <div class="top-list-pagination">
                    {{ $data->links('vendor.pagination.count') }}
                </div>
            @endif
        </div>
    </div>
    @if ($user->role == 'admin')
    <x-download />
    @endif
    <ul class="msg">
        @if (@$msg)
        <li>{{ @$msg }}</li>
        @endif
    </ul>
</x-app-layout>   