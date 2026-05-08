<x-app-layout>
    <x-slot:title>会場詳細</x-slot:title>
    <x-slot:page>venue</x-slot:page>
    <x-slot:name>{{ $user->name }}</x-slot:name>
    <x-slot:name>{{ $user->name }}</x-slot:name>
    <x-slot:role>{{ $user->role }}</x-slot:role>
    <x-slot:login>{{ $user->last_login_at?->format('Y.m.d H:i') }}</x-slot:login>
    <x-slot:old>{{ @$old }}</x-slot:old>

    <div class="content venue-page">
        <form action="{{ route('venue.update', $venue) }}" method="POST" enctype="multipart/form-data" class="venue-form">
            @csrf
            @method('PUT')
            <div class="input-item venue-image">
                <label>画像</label>
                @if (!empty($venue->image))
                    <img src="{{ route('venue.image', $venue) }}" alt="{{ $venue->venue_name }}" style="max-width: 320px; margin-bottom: 8px;">
                @endif
                <label for="venue-image-input" class="venue-file-trigger">画像を変更する</label>
                <input id="venue-image-input" class="venue-file-input" type="file" name="image" accept="image/*">
            </div>
            <div class="input-item">
                <label>会場名</label>
                <input type="text" name="venue_name" value="{{ old('venue_name', $venue->venue_name) }}" required>
            </div>

            <div class="input-item">
                <label>住所</label>
                <input type="text" name="address" value="{{ old('address', $venue->address) }}" required>
            </div>

            <div class="input-item">
                <label>電話番号</label>
                <input type="text" name="phone" value="{{ old('phone', $venue->phone) }}">
            </div>

            <div class="input-item">
                <label>FAX</label>
                <input type="text" name="fax" value="{{ old('fax', $venue->fax) }}">
            </div>

            <div class="input-item">
                <label>地図URL</label>
                <input type="url" name="map_url" value="{{ old('map_url', $venue->map_url) }}">
            </div>

            <div class="input-item">
                <label>担当者</label>
                <input type="text" name="manager" value="{{ old('manager', $venue->manager) }}">
            </div>

            <div class="input-item">
                <label>アクセス</label>
                <textarea name="access" rows="4">{{ old('access', $venue->access) }}</textarea>
            </div>

            <div class="input-item">
                <label>備考</label>
                <textarea name="notes" rows="4">{{ old('notes', $venue->notes) }}</textarea>
            </div>
            <div class="input-item venue-form-actions is-editing">
                <a href="{{ route('venue.index') }}" class="venue-back-link">一覧に戻る</a>
                <button
                    type="submit"
                    form="venue-delete-form"
                    class="venue-delete-button"
                    onclick="return confirm('この会場を削除しますか？');"
                >削除する</button>
                <button type="submit" class="venue-submit-button">更新する</button>
            </div>
        </form>

        <form action="{{ route('venue.destroy', $venue) }}" method="POST" id="venue-delete-form" style="display: none;">
            @csrf
            @method('DELETE')
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
