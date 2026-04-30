@props([
    'name' => 'emails',
    'oldValues' => [],
])

<div x-data="emailListInput({ initialValues: @js($oldValues) })" class="email-list">
    <div>
        <template x-for="(email, index) in emails" :key="`email-${index}`">
            <div class="email-row">
                <input
                    type="email"
                    :name="`{{ $name }}[]`"
                    x-model="emails[index]"
                    placeholder="mail@example.com"
                    class="email-input"
                />
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

    return {
        emails: initial.length > 0 ? [initial[0]] : [''],
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