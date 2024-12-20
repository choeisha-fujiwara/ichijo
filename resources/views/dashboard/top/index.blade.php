<x-app-layout>
    <x-slot:title>投稿一覧</x-slot:title>
    <x-slot:page>index</x-slot:page>
    <x-slot:name>{{ $user->name }}</x-slot:name>
    <x-slot:role>{{ $user->role }}</x-slot:role>
    <x-slot:keyword>{{ @$keyword }}</x-slot:keyword>
    <x-slot:state>{{ @$state }}</x-slot:state>
    <x-slot:count>{{ @$data->count }}</x-slot:count>
    <x-slot:old>{{ @$old }}</x-slot:old>
    <div class="content">
        <div class="table-box">
            <div class="table-head">
                <ul>
                    <li class="th-filter-btn"><span class="material-symbols-outlined regular">arrow_drop_down</span</li>
                    <li class="th-shop-name">店名</li>
                    <li class="th-age {{ $data->tilde }}">年代</li>
                    <li class="th-gender {{ $data->tilde }}">性別</li>
                    <li class="th-long-long">その他ご意見</li>
                    <li class="paginations">
                        <div class="paginate">
                            {{ $data->links('vendor.pagination.paginate') }}
                        </div>
                    </li>                
                </ul>
                <div class="table-filter">
                    <form action="filter" method="POST">
                    @csrf
                        <div class="filter-labels">
                            <label class="filter-label active {{ @$state == 'active' ? 'selected' : null }}" for="filter-active"><span class="material-symbols-outlined icon">circle</span><span>要対応（コメントが書き込まれ、本部未承認の投稿）</span></label>
                            <label class="filter-label negative {{ @$state == 'negative' ? 'selected' : null }}" for="filter-negative"><span class="material-symbols-outlined icon">circle</span><span>注意（アンケート回答に低評価があった投稿）</span></label>
                            <label class="filter-label unread {{ @$state == 'unread' ? 'selected' : null }}" for="filter-unread"><span class="material-symbols-outlined icon">circle</span><span>店舗未読（店舗アカウントが未読の投稿）</span></label>
                            <label class="filter-label approval {{ @$state == 'approval' ? 'selected' : null }}" for="filter-approval"><span class="material-symbols-outlined icon">circle</span><span>承認済み（対応済み・承認済みの投稿）</span></label>
                            @if ($user->role !== 'shop')
                            <label class="filter-label ng {{ @$state == 'ng' ? 'selected' : null }}" for="filter-ng"><span class="material-symbols-outlined icon">circle</span><span>非公開（店舗に公開するには不適切なワードが含まれている投稿）</span></label>
                            @endif
                            <label class="filter-label myunread {{ @$state == 'myunread' ? 'selected' : null }}" for="filter-myunread"><span class="material-symbols-outlined icon">padding</span><span>本人未読（当ログインアカウントでの未読投稿）</span></label>
                            <a href="top"><span class="material-symbols-outlined icon">close</span><span>フィルター解除</span></a>
                        </div>
                        <input type="hidden" name="keyword" value="{{ @$keyword }}" />
                        <input type="submit" class="filter-btn" name="state" id="filter-active" value="active" />
                        <input type="submit" class="filter-btn" name="state" id="filter-negative" value="negative" />
                        <input type="submit" class="filter-btn" name="state" id="filter-unread" value="unread" />
                        <input type="submit" class="filter-btn" name="state" id="filter-approval" value="approval" />
                        <input type="submit" class="filter-btn" name="state" id="filter-ng" value="ng" />
                        <input type="submit" class="filter-btn" name="state" id="filter-myunread" value="myunread" />
                    </form>
                </div>
            </div>
            <div class="search-msg {{ @$keyword ? 'active' : null }}">
                <span class="material-symbols-outlined">search</span>検索条件に一致する内容は見つかりませんでした。
            </div>
            <div class="table index-table">
                <div class="tbody">
                    @foreach($data as $datum)
                    <div class="tr {{ $datum->read }}">
                        <div class="td read-icon {{ $datum->readby }} {{ $datum->state->post_state }} {{ $datum->state->post_active }} {{ $datum->state->post_ng == 'NG' ? 'ng' : null }}"><span class="material-symbols-outlined">circle</span></div>
                        <div class="td shop-name"><span>{{ $datum->user->name }}</span></div>
                        <div class="td age {{ tildeCheck($datum->age) }}">{{ $datum->age == '答えたくない' ? '不明' : $datum->age }}</div>
                        <div class="td gender">{{ $datum->gender == '答えたくない' ? '不明' : $datum->gender }}</div>
                        <div class="td long-long">
                            <span>{{ !empty($datum->q20) ? 'その他：' . $datum->q20 . '　' : null }}</span>
                            <span>{{ !empty($datum->q05) ? 'ラーメン：' . $datum->q05 . '　' : null }}</span>
                            <span>{{ !empty($datum->q07) ? '炒飯：' . $datum->q07 . '　' : null }}</span>
                        </div>
                        <div class="td date">{{ dateChange($datum->created_at) }}</div>
                        <div class="td link"><a href="{{ route('top.show', $datum->id) }}"></a></div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @if ($user->role == 'admin')
    <x-download />
    @endif
    <ul class="msg">
        @if (@$msg)
        <li>{{ @$msg }}</li>
        @endif
        @if (@$keyword)
        {{ $data->links('vendor.pagination.count') }}
        @endif
    </ul>
</x-app-layout>   