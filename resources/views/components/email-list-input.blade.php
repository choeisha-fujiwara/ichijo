@props([
    'name' => 'emails',
    'oldValues' => [],
    'options' => [],
])

<div x-data="emailListInput({ initialValues: @js($oldValues), options: @js($options) })" class="email-list">
    <div>
        <template x-for="(email, index) in emails" :key="`email-${index}`">
            <div class="email-row">
                <input
                    type="email"
                    :name="`{{ $name }}[]`"
                    x-model="emails[index]"
                    class="email-input"
                    :list="listId"
                    placeholder="メールアドレスを入力"
                    autocomplete="off"
                />
                <button
                    type="button"
                    x-show="emails.length > 1"
                    x-on:click="remove(index)"
                    class="email-remove"
                >&times;</button>
            </div>
        </template>

        <datalist :id="listId">
            <template x-for="option in optionList" :key="option.value">
                <option :value="option.value" :label="option.label"></option>
            </template>
        </datalist>
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

    const normalizeOptions = (input) => {
        if (Array.isArray(input)) {
            return input
                .map((option) => {
                    if (option && typeof option === 'object') {
                        const value = option.value ?? '';
                        const label = option.label ?? value;

                        return value !== null && String(value).trim() !== ''
                            ? { value: String(value), label: String(label) }
                            : null;
                    }

                    return option !== null && String(option).trim() !== ''
                        ? { value: String(option), label: String(option) }
                        : null;
                })
                .filter(Boolean);
        }

        if (input && typeof input === 'object') {
            return Object.entries(input)
                .map(([value, label]) => ({
                    value,
                    label: label !== null && String(label).trim() !== '' ? String(label) : value,
                }))
                .filter(option => String(option.value).trim() !== '');
        }

        return [];
    };

    const options = normalizeOptions(config.options);

    return {
        options,
        optionList: options,
        listId: `email-options-${Math.random().toString(36).slice(2, 10)}`,
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