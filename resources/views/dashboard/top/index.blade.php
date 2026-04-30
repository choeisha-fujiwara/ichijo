<x-app-layout>
    <x-slot:title>イベント一覧</x-slot:title>
    <x-slot:page>index</x-slot:page>
    <x-slot:name>{{ $user->name }}</x-slot:name>
    <x-slot:role>{{ $user->role }}</x-slot:role>
    <x-slot:keyword>{{ @$keyword }}</x-slot:keyword>
    <x-slot:state>{{ @$state }}</x-slot:state>
    <x-slot:old>{{ @$old }}</x-slot:old>
    <div class="content top-page">
        <div class="top-list-shell">
            <div class="top-list-head">
                <div>
                    <h2>イベント一覧</h2>
                    <p>公開前のイベントを含め、登録済みのイベントを一覧で確認できます。</p>
                </div>
                <a href="{{ route('article.index') }}" class="top-list-create-link">＋新規作成</a>
            </div>

            <div class="top-list-table" role="list">
                @forelse ($data as $item)
                    <a href="{{ route('top.show', $item) }}" class="top-list-row" role="listitem">
                        <div class="top-list-main">
                            <span class="create-at">作成日：{{ $item->created_at?->format('Y.m.d') }}</span>
                            <p class="top-list-title">{{ $item->title }}</p>
                            <div class="top-list-meta">
                                <span>会場：{{ $item->venue?->venue_name ?: '会場未設定' }}</span>
                                <span>公開期間：{{ optional($item->published_at)->format('Y.m.d') ?: '未設定' }} 〜 {{ optional($item->unpublished_at)->format('Y.m.d') ?: '未設定' }}</span>
                            </div>
                        </div>
                        <div class="top-list-side">
                            <span class="top-list-status {{ $item->top_list_status_class }}">
                                {{ $item->top_list_status_label }}
                            </span>
                            <span>予約枠数：{{ $item->reservation_slots_count ?? 0 }}</span>
                            <span class="{{ $item->reservations_count > 0 ? 'active' : '' }}">予約件数：{{ $item->reservations_count ?? 0 }}</span>
                            {{-- <span class="top-list-arrow">詳細</span> --}}
                        </div>
                    </a>
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