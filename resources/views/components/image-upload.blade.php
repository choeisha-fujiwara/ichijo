@props([
    'name'     => 'image',
    'multiple' => false,
    'addable'  => false,
    'accept'   => 'image/*',
    'preview'  => true,
    'savedImages' => [],
    'savedType' => null,
    'selectedName' => null,
])

@php
$savedImagesPayload = collect($savedImages)
    ->map(function ($image) {
        return [
            'id' => $image->id,
            'url' => route('article.image', $image),
            'original_name' => $image->original_name,
            'path' => $image->path,
        ];
    })
    ->values()
    ->all();

$resolvedSavedType = $savedType;
if ($resolvedSavedType === null) {
    if (str_contains($name, 'header')) {
        $resolvedSavedType = 'header';
    } elseif (str_contains($name, 'body')) {
        $resolvedSavedType = 'body';
    }
}
@endphp

@if($addable)
{{-- ===== スロット追加モード ===== --}}
<div
    x-data="imageUploadAddable({
        fieldName: @js($name),
        savedImages: @js($savedImagesPayload),
        savedType: @js($resolvedSavedType),
        selectedName: @js($selectedName),
    })"
    class="image-upload image-upload-addable"
>
    <div>
        <template x-for="(slot, slotIndex) in slots" :key="slot.id">
            <div class="slot-wrapper relative image-upload-slot">
                {{-- Drop zone --}}
                <div
                    x-on:dragover.prevent="slot.dragging = true"
                    x-on:dragleave.prevent="slot.dragging = false"
                    x-on:drop.prevent="onDrop($event, slot)"
                    :class="slot.dragging ? 'border-indigo-500 bg-indigo-50' : 'border-gray-300 bg-white'"
                    class="image-upload-dropzone"
                    style="min-height: 8rem;"
                    x-on:click.self="openPicker($event, $el.querySelector('input[type=file]'))"
                >
                    <input
                        type="file"
                        name="{{ $name }}[]"
                        :data-slot-id="slot.id"
                        accept="{{ $accept }}"
                        class="image-upload-input"
                        style="display: none;"
                        x-on:click.stop
                        x-on:change="onSelect($event, slot)"
                    />
                    {{-- Placeholder --}}
                    <template x-if="!slot.preview">
                        <div class="image-upload-placeholder">
                            <p>画像をドラッグ&amp;ドロップ</p>
                            <p>または</p>
                            <div class="image-upload-actions">
                                <button type="button" class="image-upload-select-btn" x-on:click.stop.prevent="openPicker($event, $el.closest('.image-upload-dropzone').querySelector('input[type=file]'))">ファイルを選択</button>
                                <button type="button" class="image-upload-library-btn" x-on:click.stop.prevent="openSavedImageModal(slot.id)">保存画像から選択</button>
                            </div>
                        </div>
                    </template>
                    {{-- Preview --}}
                    <template x-if="slot.preview">
                        <img
                            :src="slot.preview"
                            class="image-upload-preview-img"
                        />
                    </template>
                </div>
                {{-- キャプション入力欄 --}}
                <input
                    type="text"
                    :name="fieldName + '_captions[' + slotIndex + ']'"
                    placeholder="キャプションを入力（任意）"
                    x-model="slot.caption"
                    class="existing-media-caption-input"
                    style="margin-top: 8px;"
                >
                {{-- 削除/クリアボタン --}}
                <button
                    type="button"
                    x-show="slot.preview || slots.length > 1"
                    class="image-upload-remove"
                    x-on:click.stop="removeOrClear(slot, $el.closest('.slot-wrapper').querySelector('input[type=file]'))"
                ><span class="material-symbols-outlined delete">delete</span></button>

                <template x-if="selectedInputName && slot.selectedSavedImageId">
                    <input type="hidden" :name="selectedInputName" :value="slot.selectedSavedImageId">
                </template>
            </div>
        </template>
    </div>
    {{-- 追加ボタン --}}
    <button
        type="button"
        class="image-upload-add-btn"
        x-on:click.stop.prevent="addSlot()"
    ><span>＋</span> 画像を追加</button>

    <div class="saved-image-modal" x-show="showSavedModal" x-transition.opacity x-on:click.self="closeSavedImageModal()" style="display: none;">
        <div class="saved-image-dialog" x-on:click.stop>
            <div class="saved-image-head">
                <h3>保存画像を選択</h3>
                <button type="button" class="saved-image-close" x-on:click="closeSavedImageModal()">閉じる</button>
            </div>
            <div class="saved-image-grid" x-show="filteredSavedImages().length > 0">
                <template x-for="image in filteredSavedImages()" :key="image.id">
                    <button
                        type="button"
                        class="saved-image-item"
                        :class="{ 'selected': isImageSelectedInSlot(image.id) }"
                        x-on:click="selectSavedImageForSlot(image)"
                    >
                        <img :src="image.url" :alt="image.original_name" class="saved-image-thumb">
                        <p class="saved-image-name" x-text="image.original_name"></p>
                    </button>
                </template>
            </div>
            <p class="saved-image-empty" x-show="filteredSavedImages().length === 0">選択可能な保存画像がありません。</p>
        </div>
    </div>
</div>

@once
<script>
function imageUploadAddable(config = {}) {
    return {
        slots: [{ id: 1, dragging: false, preview: null, selectedSavedImageId: null, file: null, caption: '' }],
        nextId: 2,
        dialogOpening: false,
        addSlotLock: false,
        fieldName: config.fieldName || null,
        savedImages: Array.isArray(config.savedImages) ? config.savedImages : [],
        savedType: config.savedType || null,
        selectedName: config.selectedName || null,
        showSavedModal: false,
        activeSlotId: null,

        get selectedInputName() {
            return this.selectedName ? `${this.selectedName}[]` : null;
        },

        init() {
            if (!Array.isArray(this.slots) || this.slots.length === 0) {
                this.slots = [{ id: 1, dragging: false, preview: null, selectedSavedImageId: null, file: null, caption: '' }];
                this.nextId = 2;
                return;
            }

            this.slots = [this.slots[0], ...this.slots.slice(1)].map((slot) => ({
                ...slot,
                file: slot.file || null,
                caption: slot.caption || '',
            }));
            this.nextId = Math.max(...this.slots.map(slot => slot.id), 0) + 1;
            this.$nextTick(() => this.syncAllSlotInputs());
        },

        slotInput(slotId) {
            return this.$root.querySelector(`input[type="file"][data-slot-id="${slotId}"]`);
        },

        syncSlotInput(slot) {
            const input = this.slotInput(slot.id);
            if (!input) return;

            if (!slot.file) {
                input.value = '';
                return;
            }

            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(slot.file);
            input.files = dataTransfer.files;
        },

        syncAllSlotInputs() {
            this.slots.forEach((slot) => this.syncSlotInput(slot));
        },

        openPicker(event, input) {
            if (!input) return;
            if (event?.target?.closest('.image-upload-remove, .image-upload-clear-btn')) return;
            if (this.dialogOpening || window.__imageUploadDialogLock) return;
            this.dialogOpening = true;
            window.__imageUploadDialogLock = true;
            if (typeof input.showPicker === 'function') {
                input.showPicker();
            } else {
                input.click();
            }
            window.setTimeout(() => {
                this.dialogOpening = false;
                window.__imageUploadDialogLock = false;
            }, 350);
        },

        addSlot() {
            if (this.addSlotLock) return;
            this.addSlotLock = true;
            this.slots.push({ id: this.nextId++, dragging: false, preview: null, selectedSavedImageId: null, file: null, caption: '' });
            window.setTimeout(() => {
                this.addSlotLock = false;
            }, 120);
            this.$nextTick(() => this.syncAllSlotInputs());
            this.dispatchPreviewChange();
        },

        hasPreview() {
            return this.slots.some(slot => !!slot.preview);
        },

        clearAll() {
            this.slots = [{ id: 1, dragging: false, preview: null, selectedSavedImageId: null, file: null, caption: '' }];
            this.nextId = 2;
            this.$nextTick(() => this.syncAllSlotInputs());
            this.dispatchPreviewChange();
        },

        removeOrClear(slot, input) {
            if (this.slots.length > 1) {
                this.slots = this.slots.filter(s => s.id !== slot.id);
            } else {
                slot.preview = null;
                slot.selectedSavedImageId = null;
                slot.file = null;
                if (input) input.value = '';
            }
            this.$nextTick(() => this.syncAllSlotInputs());
            this.dispatchPreviewChange();
        },

        onSelect(event, slot) {
            slot.selectedSavedImageId = null;
            slot.file = event.target.files[0] || null;
            this.readFile(slot.file, slot);
        },

        onDrop(event, slot) {
            slot.dragging = false;
            const file = event.dataTransfer.files[0];
            if (!file) return;
            slot.selectedSavedImageId = null;
            slot.file = file;
            const input = event.currentTarget.querySelector('input[type="file"]');
            if (input) {
                const dt = new DataTransfer();
                dt.items.add(file);
                input.files = dt.files;
            }
            this.readFile(file, slot);
        },

        readFile(file, slot) {
            if (!file || !file.type.startsWith('image/')) return;
            const reader = new FileReader();
            reader.onload = e => {
                slot.preview = e.target.result;
                this.$nextTick(() => this.syncSlotInput(slot));
                this.dispatchPreviewChange();
            };
            reader.readAsDataURL(file);
        },

        dispatchPreviewChange() {
            if (!this.fieldName) return;
            const visibleSlots = this.slots.filter(
                (slot) => typeof slot.preview === 'string' && slot.preview.length > 0
            );
            const event = new CustomEvent('image-upload-change', {
                detail: {
                    name: this.fieldName,
                    previews: visibleSlots.map((slot) => slot.preview),
                    captions: visibleSlots.map((slot) => String(slot.caption || '')),
                },
                bubbles: true,
            });

            this.$root.dispatchEvent(event);
            window.dispatchEvent(new CustomEvent('image-upload-change', {
                detail: event.detail,
            }));
        },

        hasSavedImages() {
            return this.filteredSavedImages().length > 0;
        },

        filteredSavedImages() {
            if (!this.savedType) return this.savedImages;
            const marker = `/${this.savedType}/`;
            return this.savedImages.filter(image => (image.path || '').includes(marker));
        },

        openSavedImageModal(slotId) {
            this.activeSlotId = slotId;
            this.showSavedModal = true;
        },

        closeSavedImageModal() {
            this.showSavedModal = false;
            this.activeSlotId = null;
        },

        findSlot(slotId) {
            return this.slots.find(slot => slot.id === slotId) || null;
        },

        isImageSelectedInSlot(imageId) {
            const slot = this.findSlot(this.activeSlotId);
            return !!slot && Number(slot.selectedSavedImageId) === Number(imageId);
        },

        selectSavedImageForSlot(image) {
            const slot = this.findSlot(this.activeSlotId);
            if (!slot) return;

            slot.preview = image.url;
            slot.selectedSavedImageId = image.id;
            slot.file = null;

            const input = this.slotInput(slot.id);
            if (input) {
                input.value = '';
            }

            this.$nextTick(() => this.syncSlotInput(slot));
            this.dispatchPreviewChange();
            this.closeSavedImageModal();
        },
    };
}
</script>
@endonce

@else
{{-- ===== 通常モード（single/multiple） ===== --}}
<div
    x-data="imageUpload({
        multiple: @js($multiple),
        fieldName: @js($name),
        savedImages: @js($savedImagesPayload),
        savedType: @js($resolvedSavedType),
        selectedName: @js($selectedName),
    })"
    x-on:dragover.prevent="dragging = true"
    x-on:dragleave.prevent="dragging = false"
    x-on:drop.prevent="onDrop($event)"
    :class="dragging ? 'border-indigo-500 bg-indigo-50' : 'border-gray-300 bg-white'"
    class="image-upload image-upload-single"
    x-on:click.self="openPicker($event, $refs.input)"
>
    <input
        x-ref="input"
        type="file"
        name="{{ $name }}{{ $multiple ? '[]' : '' }}"
        accept="{{ $accept }}"
        {{ $multiple ? 'multiple' : '' }}
        class="image-upload-input"
        style="display: none;"
        x-on:click.stop
        x-on:change="onSelect($event)"
    />
    <template x-if="previews.length === 0">
        <div class="image-upload-placeholder">
            {{-- <svg class="w-10 h-10 mb-3 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
            </svg> --}}
            <p>画像をドラッグ&amp;ドロップ</p>
            <p>または</p>
            <div class="image-upload-actions">
                <button type="button" class="image-upload-select-btn" x-on:click.stop.prevent="openPicker($event, $refs.input)">ファイルを選択</button>
                <button type="button" class="image-upload-library-btn" x-on:click.stop.prevent="openSavedImageModal()">保存画像から選択</button>
            </div>
        </div>
    </template>
    @if($preview)
    <div
        x-show="previews.length > 0"
        class="image-upload-preview-list"
        x-on:click.stop
    >
        <img :src="previews[0]" class="image-upload-preview-img" />
    </div>
    <button
        type="button"
        x-show="previews.length > 0"
        class="image-upload-clear-btn"
        x-on:click.stop="clearAll()"
    ><span class="material-symbols-outlined">delete</span></button>
    @endif

    <template x-if="selectedInputName && selectedSavedImageId">
        <input type="hidden" :name="selectedInputName" :value="selectedSavedImageId">
    </template>

    <div class="saved-image-modal" x-show="showSavedModal" x-transition.opacity x-on:click.self="closeSavedImageModal()" style="display: none;">
        <div class="saved-image-dialog" x-on:click.stop>
            <div class="saved-image-head">
                <h3>保存画像を選択</h3>
                <button type="button" class="saved-image-close" x-on:click="closeSavedImageModal()">閉じる</button>
            </div>
            <div class="saved-image-grid" x-show="filteredSavedImages().length > 0">
                <template x-for="image in filteredSavedImages()" :key="image.id">
                    <button
                        type="button"
                        class="saved-image-item"
                        :class="{ 'selected': Number(selectedSavedImageId) === Number(image.id) }"
                        x-on:click="selectSavedImage(image)"
                    >
                        <img :src="image.url" :alt="image.original_name" class="saved-image-thumb">
                        <p class="saved-image-name" x-text="image.original_name"></p>
                    </button>
                </template>
            </div>
            <p class="saved-image-empty" x-show="filteredSavedImages().length === 0">選択可能な保存画像がありません。</p>
        </div>
    </div>
</div>

@once
<script>
function imageUpload(config = {}) {
    return {
        multiple: !!config.multiple,
        dragging: false,
        previews: [],
        files: [],
        dialogOpening: false,
        fieldName: config.fieldName || null,
        savedImages: Array.isArray(config.savedImages) ? config.savedImages : [],
        savedType: config.savedType || null,
        selectedName: config.selectedName || null,
        selectedSavedImageId: null,
        showSavedModal: false,

        init() {
            this.$nextTick(() => this.syncInputFiles());
        },

        syncInputFiles() {
            if (!this.$refs.input) return;

            if (!Array.isArray(this.files) || this.files.length === 0) {
                this.$refs.input.value = '';
                return;
            }

            const dataTransfer = new DataTransfer();
            this.files.forEach((file) => {
                if (file instanceof File) {
                    dataTransfer.items.add(file);
                }
            });
            this.$refs.input.files = dataTransfer.files;
        },

        get selectedInputName() {
            return this.selectedName || null;
        },

        openPicker(event, input) {
            if (!input) return;
            if (event?.target?.closest('.image-upload-remove, .image-upload-clear-btn')) return;
            if (this.dialogOpening || window.__imageUploadDialogLock) return;
            this.dialogOpening = true;
            window.__imageUploadDialogLock = true;
            if (typeof input.showPicker === 'function') {
                input.showPicker();
            } else {
                input.click();
            }
            window.setTimeout(() => {
                this.dialogOpening = false;
                window.__imageUploadDialogLock = false;
            }, 350);
        },

        onSelect(event) {
            this.addFiles(event.target.files);
        },

        onDrop(event) {
            this.dragging = false;
            this.addFiles(event.dataTransfer.files);
        },

        addFiles(fileList) {
            const imageFiles = Array.from(fileList).filter(file => file.type.startsWith('image/'));
            if (imageFiles.length === 0) return;

            this.selectedSavedImageId = null;

            if (!this.multiple) {
                const file = imageFiles[0];
                const reader = new FileReader();
                reader.onload = e => {
                    this.previews = [e.target.result];
                    this.files = [file];
                    this.syncInputFiles();
                    this.dispatchPreviewChange();
                };
                reader.readAsDataURL(file);
                return;
            }

            imageFiles.forEach(file => {
                const reader = new FileReader();
                reader.onload = e => {
                    this.previews.push(e.target.result);
                    this.syncInputFiles();
                    this.dispatchPreviewChange();
                };
                reader.readAsDataURL(file);
                this.files.push(file);
            });

            this.$nextTick(() => this.syncInputFiles());
        },

        remove(index) {
            this.previews.splice(index, 1);
            this.files.splice(index, 1);
            this.syncInputFiles();
            this.dispatchPreviewChange();
        },

        clearAll() {
            this.previews = [];
            this.files = [];
            this.selectedSavedImageId = null;
            this.syncInputFiles();
            this.dispatchPreviewChange();
        },

        dispatchPreviewChange() {
            if (!this.fieldName) return;
            const event = new CustomEvent('image-upload-change', {
                detail: {
                    name: this.fieldName,
                    previews: Array.isArray(this.previews) ? this.previews : [],
                },
                bubbles: true,
            });

            this.$root.dispatchEvent(event);
            window.dispatchEvent(new CustomEvent('image-upload-change', {
                detail: event.detail,
            }));
        },

        hasSavedImages() {
            return this.filteredSavedImages().length > 0;
        },

        filteredSavedImages() {
            if (!this.savedType) return this.savedImages;
            const marker = `/${this.savedType}/`;
            return this.savedImages.filter(image => (image.path || '').includes(marker));
        },

        openSavedImageModal() {
            this.showSavedModal = true;
        },

        closeSavedImageModal() {
            this.showSavedModal = false;
        },

        selectSavedImage(image) {
            this.previews = [image.url];
            this.files = [];
            this.selectedSavedImageId = image.id;
            this.syncInputFiles();
            this.dispatchPreviewChange();
            this.closeSavedImageModal();
        },
    };
}
</script>
@endonce

@endif
