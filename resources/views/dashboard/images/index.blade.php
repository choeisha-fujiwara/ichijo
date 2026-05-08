<x-app-layout>
    <x-slot:title>画像管理</x-slot:title>
    <x-slot:page>images</x-slot:page>
    <x-slot:name>{{ $user->name }}</x-slot:name>
    <x-slot:role>{{ $user->role }}</x-slot:role>
    <x-slot:login>{{ $user->last_login_at?->format('Y.m.d H:i') }}</x-slot:login>

    @php
        $imagesPayload = $images->getCollection()
            ->map(function ($image) use ($type) {
                return [
                    'id' => $image->id,
                    'url' => route('article.image', $image),
                    'name' => (string) ($image->original_name ?: basename((string) $image->path)),
                    'path' => (string) $image->path,
                    'type' => $type,
                    'createUrl' => route('article.index', [
                        'prefill_type' => $type,
                        'prefill_image_id' => $image->id,
                    ]),
                    'deleteUrl' => route('images.destroy', $image),
                ];
            })
            ->values()
            ->all();
    @endphp

    <div class="content images-admin-page" x-data="imagesAdminPage({ items: @js($imagesPayload) })">
        <div class="images-admin-shell">
            <div class="images-admin-head">
                <div>
                    <h2>画像管理</h2>
                    <p>保存画像の確認・記事作成への連携・削除ができます。</p>
                </div>
                <div class="images-type-switch" role="tablist" aria-label="画像タイプ切り替え">
                    <a
                        href="{{ route('images.index', ['type' => 'header']) }}"
                        class="images-type-btn {{ $type === 'header' ? 'is-active' : '' }}"
                        role="tab"
                        aria-selected="{{ $type === 'header' ? 'true' : 'false' }}"
                    >ヘッダー画像</a>
                    <a
                        href="{{ route('images.index', ['type' => 'body']) }}"
                        class="images-type-btn {{ $type === 'body' ? 'is-active' : '' }}"
                        role="tab"
                        aria-selected="{{ $type === 'body' ? 'true' : 'false' }}"
                    >本文画像</a>
                </div>
            </div>

            {{-- アップロードフォーム --}}
            <div class="images-upload-wrap" x-data="imagesUpload()">
                <form
                    method="POST"
                    action="{{ route('images.store') }}"
                    enctype="multipart/form-data"
                    x-ref="uploadForm"
                >
                    @csrf
                    <input type="hidden" name="type" value="{{ $type }}">

                    <label
                        class="images-upload-drop"
                        x-bind:class="dragging ? 'is-dragging' : ''"
                        x-on:dragover.prevent="dragging = true"
                        x-on:dragleave.prevent="dragging = false"
                        x-on:drop.prevent="onDrop($event)"
                        for="images-upload-input"
                    >
                        <span class="material-symbols-outlined">upload_file</span>
                        <p>クリックまたはドラッグ＆ドロップで画像を選択</p>
                        <p class="images-upload-hint">JPEG / PNG / GIF / WebP・最大10MB・最大20枚</p>
                        <input
                            id="images-upload-input"
                            type="file"
                            name="images[]"
                            multiple
                            accept="image/jpeg,image/png,image/gif,image/webp"
                            class="images-upload-input"
                            x-ref="fileInput"
                            x-on:change="onFileChange($event)"
                        >
                    </label>

                    <template x-if="previews.length > 0">
                        <div class="images-upload-preview-wrap">
                            <div class="images-upload-preview-grid">
                                <template x-for="(p, i) in previews" :key="i">
                                    <div class="images-upload-preview-item">
                                        <img :src="p.url" :alt="p.name">
                                        <button type="button" class="images-upload-preview-remove" x-on:click="removePreview(i)" aria-label="削除">
                                            <span class="material-symbols-outlined">close</span>
                                        </button>
                                        <p class="images-upload-preview-name" x-text="p.name"></p>
                                    </div>
                                </template>
                            </div>
                            <button type="submit" class="images-upload-submit">
                                <span class="material-symbols-outlined">cloud_upload</span>
                                <span x-text="`${previews.length}枚 アップロード`"></span>
                            </button>
                        </div>
                    </template>
                </form>
            </div>

            <div class="images-admin-grid" role="list">
                @forelse ($images as $image)
                    @php
                        $name = (string) ($image->original_name ?: basename((string) $image->path));
                    @endphp
                    <article class="image-card" role="listitem" @click="openById({{ $image->id }})">
                        <div class="image-card-thumb">
                            <img src="{{ route('article.image', $image) }}" alt="{{ $name }}">
                        </div>
                        <div class="image-card-body">
                            <p class="image-card-name">{{ $name }}</p>
                        </div>
                    </article>
                @empty
                    <div class="images-admin-empty">
                        <p>画像がありません。</p>
                    </div>
                @endforelse
            </div>

            <div class="images-admin-pagination">
                {{ $images->links('vendor.pagination.count') }}
            </div>
        </div>

        <div class="image-modal" x-show="openedItem" x-transition.opacity x-on:click.self="close()" style="display: none;">
            <div class="image-modal-dialog" x-show="openedItem" x-transition.scale>
                {{-- <div class="image-modal-meta">
                    <p class="image-modal-name" x-text="openedItem?.name"></p>
                </div> --}}
                {{-- <button type="button" class="image-modal-close" @click="close()"><span class="material-symbols-outlined">close</span></button> --}}
                <div class="image-modal-media">
                    <img :src="openedItem?.url || ''" :alt="openedItem?.name || '画像'">
                </div>
                <div class="image-modal-actions">
                    <a :href="openedItem?.createUrl || '#'" class="image-modal-create">この画像から記事を作成</a>
                    <form method="POST" :action="openedItem?.deleteUrl || '#'" @click.stop>
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="type" :value="openedItem?.type || 'header'">
                        <button type="submit" class="image-modal-delete" onclick="return confirm('この画像を削除しますか？');"><span class="material-symbols-outlined">delete</span></button>
                    </form>
                </div>
            </div>
        </div>
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

@once
<script>
function imagesAdminPage(config = {}) {
    return {
        items: Array.isArray(config.items) ? config.items : [],
        openedItem: null,

        openById(id) {
            const found = this.items.find((item) => Number(item.id) === Number(id));
            if (!found) {
                return;
            }

            this.openedItem = found;
        },

        close() {
            this.openedItem = null;
        },
    };
}

function imagesUpload() {
    return {
        dragging: false,
        previews: [],
        files: [],

        onFileChange(event) {
            this.addFiles(Array.from(event.target.files));
        },

        onDrop(event) {
            this.dragging = false;
            const dropped = Array.from(event.dataTransfer.files).filter(f => f.type.startsWith('image/'));
            this.addFiles(dropped);
        },

        addFiles(newFiles) {
            newFiles.forEach(file => {
                if (this.files.length >= 20) return;
                const url = URL.createObjectURL(file);
                this.previews.push({ url, name: file.name });
                this.files.push(file);
            });
            this.syncFileInput();
        },

        removePreview(index) {
            URL.revokeObjectURL(this.previews[index].url);
            this.previews.splice(index, 1);
            this.files.splice(index, 1);
            this.syncFileInput();
        },

        syncFileInput() {
            const dt = new DataTransfer();
            this.files.forEach(f => dt.items.add(f));
            this.$refs.fileInput.files = dt.files;
        },
    };
}
</script>
@endonce
