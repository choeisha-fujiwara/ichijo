<x-app-layout>
    <x-slot:title>店舗一覧</x-slot:title>
    <x-slot:page>shop</x-slot:page>
    <x-slot:name>{{ $user->name }}</x-slot:name>
    <x-slot:role>{{ $user->role }}</x-slot:role>
    <x-slot:keyword>{{ @$keyword }}</x-slot:keyword>
    <x-slot:count>{{ @$data->count }}</x-slot:count>
    <x-slot:old>{{ @$old }}</x-slot:old>
    <div class="content shop">
        <div class="shop table-box">
            <div class="shop-table">
                <div class="thead">
                    <div class="tr">
                        <div class="th shop-name"><span class="material-symbols-outlined thin icon">store</span>店名</div>
                        <div class="th shop-area">エリア名</div>
                        <div class="th shop-block">ブロック名</div>
                        <div class="th">未読</div>
                        <div class="th">未承認</div>
                        <div class="th">承認済</div>
                        <div class="th login-at">最終ログイン</div>
                        <div class="th shop-search">店舗別</div>
                    </div>
                </div>
                <div class="tbody">
                    @foreach ($data as $datum)
                    <div class="tr">
                        <div class="td shop-name"><span class="material-symbols-outlined thin icon">store</span><a href="mailto:{{ $datum->email }}">{{ $datum->name }}</a></div>
                        <div class="td shop-area"><a href="mailto:{{ $datum->amg_mail }}">{{ $datum->area->area_name }}</a></div>
                        <div class="td shop-block"><a href="mailto:{{ $datum->mg_mail }}">{{ $datum->area->block_name }}</a></div>
                        <div class="td {{ $datum->unread !== null ? 'unread' : 'read' }}">
                            <form action="search" method="POST">
                            @csrf
                                <label class="search-label" for="shop-unread-{{ $datum->id }}">{{ $datum->unread !== null ? $datum->unread : 0 }}件</label>
                                <input type="hidden" name="keyword" value="{{ $datum->name }}" />
                                <input type="hidden" name="state" value="unread" />
                                <input type="submit" class="hidden-obj" id="shop-unread-{{ $datum->id }}" />
                            </form>
                        </div>
                        <div class="td {{ $datum->active !== null ? 'active' : 'inactive' }}">
                            <form action="search" method="POST">
                            @csrf
                                <label class="search-label" for="shop-active-{{ $datum->id }}">{{ $datum->active !== null ? $datum->active : 0 }}件</label>
                                <input type="hidden" name="keyword" value="{{ $datum->name }}" />
                                <input type="hidden" name="state" value="active" />
                                <input type="submit" class="hidden-obj" id="shop-active-{{ $datum->id }}" />
                            </form>
                        </div>
                        <div class="td {{ $datum->approval !== null ? 'approval' : 'unapproved' }}">
                            <form action="search" method="POST">
                            @csrf
                                <label class="search-label" for="shop-approval-{{ $datum->id }}">{{ $datum->approval !== null ? $datum->approval : 0 }}件</label>
                                <input type="hidden" name="keyword" value="{{ $datum->name }}" />
                                <input type="hidden" name="state" value="approval" />
                                <input type="submit" class="hidden-obj" id="shop-approval-{{ $datum->id }}" />
                            </form>
                        </div>
                        <div class="td login-at">{{ $datum->last_login_at->isoFormat('YYYY年MM月DD日（ddd） hh:mm') }}</div>
                        <div class="td shop-search">
                            <form action="search" method="POST" class="shop-search">
                            @csrf
                                <label class="search-label" for="shop-search-{{ $datum->id }}"><span class="material-symbols-outlined thin">stacks</span></label>
                                <input type="submit" class="search-btn" name="keyword" id="shop-search-{{ $datum->id }}" value="{{ $datum->name }}" />
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div> 
        </div>
    </div>
    <ul class="msg">
        @if(@$msg)
            <li>{{ @$msg }}</li>
        @endif
    </ul>
</x-app-layout>