@props([
    'name' => 'emails',
    'oldValues' => [],
    'options' => [],
])

<div x-data="emailListInput({ initialValues: @js($oldValues), options: @js($options) })" class="email-list">
    <div>
        <template x-for="(email, index) in emails" :key="`email-${index}`">
            <div class="email-row">
                <select
                    :name="`{{ $name }}[]`"
                    x-model="emails[index]"
                    class="email-input"
                >
                    <option value="">選択してください</option>
                    <template x-for="option in options" :key="option">
                        <option :value="option" x-text="option"></option>
                    </template>
                </select>
                <button
                    type="button"
                    x-show="emails.length > 1"
                    x-on:click="remove(index)"
                    class="email-remove"
                >&times;</button>
            </div>
        </template>
    </div>

    <button
        type="button"
        x-on:click.stop.prevent="add()"
        class="email-add-btn"
    ><span>＋</span> メールを追加</button>
</div>

@once
<script>
function emailListInput(config = {}) {
    const initial = Array.isArray(config.initialValues)
        ? config.initialValues.filter(v => v !== null && String(v).trim() !== '')
        : [];
    const options = Array.isArray(config.options)
        ? config.options.filter(v => v !== null && String(v).trim() !== '')
        : [];

    return {
        options,
        emails: initial.length > 0 ? initial : [''],
        addLock: false,

        init() {
            if (!Array.isArray(this.emails) || this.emails.length === 0) {
                this.emails = [''];
                return;
            }

            this.emails = this.emails.filter(email => email !== null && String(email).trim() !== '');
            if (this.emails.length === 0) {
                this.emails = [''];
                return;
            }
        },

        add() {
            if (this.addLock) return;
            this.addLock = true;
            this.emails.push('');
            window.setTimeout(() => {
                this.addLock = false;
            }, 120);
        },

        remove(index) {
            this.emails.splice(index, 1);
            if (this.emails.length === 0) this.emails = [''];
        },
    };
}
</script>
@endonce