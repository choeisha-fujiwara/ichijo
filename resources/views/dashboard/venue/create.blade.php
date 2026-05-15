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
                <div class="input-item">
                    <label>画像</label>
                    <div class="images-upload-wrap" x-data="venueImageUpload()">
                        <label
                            class="images-upload-drop"
                            x-bind:class="dragging ? 'is-dragging' : ''"
                            x-on:dragover.prevent="dragging = true"
                            x-on:dragleave.prevent="dragging = false"
                            x-on:drop.prevent="onDrop($event)"
                            for="venue-image-input"
                        >
                            <template x-if="!previewUrl">
                                <span class="material-symbols-outlined">upload_file</span>
                            </template>
                            <template x-if="!previewUrl">
                                <p>クリックまたはドラッグ＆ドロップで画像を選択</p>
                            </template>
                            <template x-if="!previewUrl">
                                <p class="images-upload-hint">JPEG / PNG / GIF / WebP・最大10MB</p>
                            </template>
                            <template x-if="previewUrl">
                                <img
                                    :src="previewUrl"
                                    alt="選択中の画像プレビュー"
                                    style="display: block; width: 100%; max-height: 240px; object-fit: contain; border-radius: 8px;"
                                >
                            </template>
                            <template x-if="previewName">
                                <p class="images-upload-hint" x-text="previewName"></p>
                            </template>
                            <input
                                id="venue-image-input"
                                type="file"
                                name="image"
                                accept="image/jpeg,image/png,image/gif,image/webp"
                                class="images-upload-input"
                                x-ref="fileInput"
                                x-on:change="onFileChange($event)"
                            >
                        </label>
                    </div>
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

    <script>
    @once
    function venueImageUpload() {
        return {
            dragging: false,
            previewUrl: null,
            previewName: null,

            setPreview(file) {
                if (!file || !file.type.startsWith('image/')) {
                    return;
                }

                if (this.previewUrl) {
                    URL.revokeObjectURL(this.previewUrl);
                }

                this.previewUrl = URL.createObjectURL(file);
                this.previewName = file.name;
            },

            onDrop(e) {
                this.dragging = false;
                const files = e.dataTransfer?.files;
                if (files && files.length > 0) {
                    this.$refs.fileInput.files = files;
                    const event = new Event('change', { bubbles: true });
                    this.$refs.fileInput.dispatchEvent(event);
                }
            },
            onFileChange(e) {
                const file = e.target?.files?.[0] ?? null;
                this.setPreview(file);
            },
        };
    }
    @endonce
    </script>
</x-app-layout>
