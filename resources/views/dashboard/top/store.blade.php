<x-app-layout>
    <x-slot:title>イベント作成</x-slot:title>
    <x-slot:page>index</x-slot:page>
    <x-slot:name>{{ $user->name }}</x-slot:name>
    <x-slot:role>{{ $user->role }}</x-slot:role>
    <x-slot:keyword>{{ @$keyword }}</x-slot:keyword>
    <x-slot:state>{{ @$state }}</x-slot:state>
    <x-slot:old>{{ @$old }}</x-slot:old>
    <div class="content">
        <form action="{{ route('top.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
            <div class="input-form">
                <div class="input-item header-image">
                    <label>ヘッダー画像</label>
                    <x-image-upload name="header_image" />
                </div>
                <div class="input-item title-text">
                    <label>タイトル</label>
                    <input type="text" name="title" placeholder="タイトルを入力してください" class="input-title" value="{{ old('title') }}">
                </div>
                <div class="input-item body-text">
                    <label>本文</label>
                    <x-rich-text-editor name="body" placeholder="本文を入力してください" :value="old('body')" />
                </div>
                <div class="input-item">
                    <input type="text" name="freeword_1" placeholder="" class="" value="{{ old('freeword_1') }}">
                </div>
                <div class="input-item">
                    <input type="text" name="freeword_2" placeholder="" class="" value="{{ old('freeword_2') }}">
                </div>
                <div class="input-item body-image">
                    <label>本文画像</label>
                    <x-image-upload name="body_image" :addable="true" />
                </div>
                <div class="input-item reservation-slot">
                    <label>予約枠</label>
                    <x-reservation-slot-input />
                </div>
                <div class="input-item memo">
                    <label>追加質問</label>
                    <input type="text" name="memo" placeholder="例）アンケートに記載する質問など" class="input-memo" value="{{ old('memo') }}">
                </div>
                <div class="input-item emails">
                    <label>送信先メールアドレス</label>
                    <x-email-list-input name="emails" :old-values="old('emails', [])" />
                </div>
                <div class="input-item publish-date">
                    <label>公開期間</label>
                    <div>
                        <div>
                            <span>開始日</span>
                            <input type="date" name="published_at" value="{{ old('published_at') }}">
                        </div>
                        <span>〜</span>
                        <div>
                            <span>終了日</span>
                            <input type="date" name="unpublished_at" value="{{ old('unpublished_at') }}">
                        </div>
                    </div>
                </div>
                <input type="hidden" name="state" value="{{ old('state', 'draft') }}">
                <div class="input-item">
                    <button type="submit">保存</button>
                </div>
            </div>
        </form>
    </div>
    @if ($user->role == 'admin')
    <x-download />
    @endif
    <ul class="msg">
        @if (@$msg)
        <li>{{ @$msg }}</li>
        @endif
        @if (@$keyword)
        {{ @$data->links('vendor.pagination.count') }}
        @endif
    </ul>
</x-app-layout>   