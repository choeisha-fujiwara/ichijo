<x-app-layout>
    <x-slot:title>会場新規作成</x-slot:title>
    <x-slot:page>venue</x-slot:page>
    <x-slot:name>{{ $user->name }}</x-slot:name>
    <x-slot:role>{{ $user->role }}</x-slot:role>
    <x-slot:login>{{ $user->last_login_at?->format('Y.m.d H:i') }}</x-slot:login>
    <x-slot:old>{{ @$old }}</x-slot:old>

    <div class="content venue-page">
        <form action="{{ route('venue.store') }}" method="POST" enctype="multipart/form-data" class="venue-form">
            @csrf
                <div class="input-item venue-image">
                    <label>画像</label>
                    <x-image-upload name="image" />
                </div>
                <div class="input-item">
                    <label>会場名</label>
                    <input type="text" name="venue_name" value="{{ old('venue_name') }}" required>
                </div>
                <div class="input-item">
                    <label>住所</label>
                    <input type="text" name="address" value="{{ old('address') }}" required>
                </div>
                <div class="input-item">
                    <label>電話番号</label>
                    <input type="text" name="phone" value="{{ old('phone') }}">
                </div>
                <div class="input-item">
                    <label>FAX</label>
                    <input type="text" name="fax" value="{{ old('fax') }}">
                </div>
                <div class="input-item">
                    <label>地図URL</label>
                    <input type="url" name="map_url" value="{{ old('map_url') }}">
                </div>
                <div class="input-item">
                    <label>担当者</label>
                    <input type="text" name="manager" value="{{ old('manager') }}">
                </div>
                <div class="input-item">
                    <label>アクセス</label>
                    <textarea name="access" rows="4">{{ old('access') }}</textarea>
                </div>
                <div class="input-item">
                    <label>備考</label>
                    <textarea name="notes" rows="4">{{ old('notes') }}</textarea>
                </div>
            <div class="input-item venue-form-actions">
                <a href="{{ route('venue.index') }}" class="venue-back-link">一覧に戻る</a>
                <button type="submit" class="venue-submit-button">作成する</button>
            </div>
        </form>
    </div>

    <ul class="msg">
        @if (session('msg'))
            <li>{{ session('msg') }}</li>
        @endif
        @if ($errors->any())
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        @endif
    </ul>
</x-app-layout>
