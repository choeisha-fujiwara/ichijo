@props([
    'name' => 'body',
    'value' => '',
    'placeholder' => '本文を入力してください',
    'height' => '16rem',
])

<div x-data="richTextEditor({ initialValue: @js($value) })" class="rich-editor">
    <div class="rich-editor-toolbar">
        <button type="button" class="rich-editor-btn" x-on:click="format('bold')">B</button>
        <button type="button" class="rich-editor-btn" x-on:click="format('italic')">I</button>
        <button type="button" class="rich-editor-btn" x-on:click="format('underline')">U</button>
        <button type="button" class="rich-editor-btn" x-on:click.stop.prevent="insertLink()">リンク</button>
        <button type="button" class="rich-editor-btn" x-on:click="format('removeFormat')">書式クリア</button>
    </div>

    <div
        x-ref="editor"
        contenteditable="true"
        :data-placeholder="placeholder"
        class="rich-text-editor rich-editor-surface"
        :style="`min-height: ${height};`"
        x-on:input="sync()"
        x-on:blur="sync()"
    ></div>

    <textarea name="{{ $name }}" x-ref="textarea" class="hidden" required></textarea>
</div>

@once
<script>
function richTextEditor(config = {}) {
    return {
        initialValue: config.initialValue || '',
        placeholder: config.placeholder || '本文を入力してください',
        height: config.height || '16rem',
        linkPromptOpen: false,

        init() {
            const normalizedValue = this.normalizeHtml(this.initialValue);
            this.$refs.editor.innerHTML = normalizedValue;
            this.$refs.textarea.value = normalizedValue;
            this.dispatchChange();
        },

        focusEditor() {
            this.$refs.editor.focus();
        },

        sync() {
            const normalizedValue = this.normalizeHtml(this.$refs.editor.innerHTML);
            // HTML が実際に変わった場合のみ更新してカーソル位置をリセットするのを防ぐ
            if (this.$refs.editor.innerHTML !== normalizedValue) {
                // カーソル位置を保存
                const selection = window.getSelection();
                const range = selection.rangeCount > 0 ? selection.getRangeAt(0) : null;
                
                // innerHTML を更新
                this.$refs.editor.innerHTML = normalizedValue;
                
                // カーソル位置を復元
                if (range) {
                    try {
                        selection.removeAllRanges();
                        selection.addRange(range);
                    } catch (e) {
                        // 範囲が無効な場合はフォーカスのみ
                        this.$refs.editor.focus();
                    }
                }
            }
            this.$refs.textarea.value = normalizedValue;
            this.dispatchChange();
        },

        normalizeHtml(html) {
            const container = document.createElement('div');
            container.innerHTML = html || '';

            // 他サイトからの貼り付けに紛れ込む実行可能なタグを除去する
            // （XSS対策に加え、サーバー側ファイアウォールに弾かれるのを防ぐ）
            container.querySelectorAll('script, style, iframe, object, embed, link, meta, form, base').forEach((element) => {
                element.remove();
            });

            container.querySelectorAll('*').forEach((element) => {
                element.removeAttribute('style');

                [...element.attributes].forEach((attribute) => {
                    const attrName = attribute.name.toLowerCase();

                    if (attrName.startsWith('on')) {
                        element.removeAttribute(attribute.name);
                        return;
                    }

                    if ((attrName === 'href' || attrName === 'src') && /^\s*(javascript|vbscript|data):/i.test(attribute.value)) {
                        element.removeAttribute(attribute.name);
                    }
                });
            });

            return container.innerHTML;
        },

        dispatchChange() {
            this.$root.dispatchEvent(new CustomEvent('rich-editor-change', {
                detail: {
                    name: @js($name),
                    value: this.$refs.textarea.value,
                },
                bubbles: true,
            }));
        },

        format(command, value = null) {
            this.focusEditor();
            document.execCommand(command, false, value);
            this.sync();
        },

        formatBlock(tag) {
            this.focusEditor();
            document.execCommand('formatBlock', false, tag === 'p' ? 'p' : tag);
            this.sync();
        },

        insertLink() {
            if (this.linkPromptOpen || window.__richEditorLinkPromptLock) return;
            this.linkPromptOpen = true;
            window.__richEditorLinkPromptLock = true;
            try {
                const url = window.prompt('リンクURLを入力してください');
                if (!url) return;
                this.focusEditor();
                document.execCommand('createLink', false, url);
                this.sync();
            } finally {
                this.linkPromptOpen = false;
                window.setTimeout(() => {
                    window.__richEditorLinkPromptLock = false;
                }, 100);
            }
        },
    };
}
</script>

<style>
    .rich-text-editor:empty:before {
        content: attr(data-placeholder);
        color: #9ca3af;
    }

    .rich-text-editor h2 {
        margin: 0 0 0.75rem;
        font-size: 1.25rem;
        font-weight: 700;
    }

    .rich-text-editor p {
        margin: 0 0 0.75rem;
    }

    .rich-text-editor blockquote {
        margin: 0 0 0.75rem;
        padding-left: 1rem;
        border-left: 4px solid #d1d5db;
        color: #4b5563;
    }

    .rich-text-editor ul,
    .rich-text-editor ol {
        margin: 0 0 0.75rem 1.5rem;
    }

    .rich-text-editor a {
        color: #2563eb;
        text-decoration: underline;
    }
 </style>
@endonce