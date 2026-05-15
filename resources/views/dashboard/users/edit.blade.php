<x-app-layout>
    <x-slot:title>ユーザー編集</x-slot:title>
    <x-slot:page>users</x-slot:page>
    <x-slot:name>{{ $user->name }}</x-slot:name>
    <x-slot:role>{{ $user->role }}</x-slot:role>
    <x-slot:login>{{ $user->last_login_at?->format('Y.m.d H:i') }}</x-slot:login>

    <div class="content users-create-page">
        <div class="users-create-shell">
            <div class="users-create-head">
                <h2>ユーザー編集</h2>
                <p>パスワードと所属を変更できます。</p>
            </div>

            <form method="POST" action="{{ route('users.update', $target) }}" class="users-create-form">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="name">氏名</label>
                    <input
                        id="name"
                        type="text"
                        value="{{ $target->name }}"
                        readonly
                    >
                </div>

                <div class="form-group">
                    <label for="email">メールアドレス</label>
                    <input
                        id="email"
                        type="email"
                        value="{{ $target->email }}"
                        readonly
                    >
                </div>

                <div class="form-group">
                    <label for="password">パスワード</label>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        autocomplete="new-password"
                        placeholder="変更しない場合は空欄"
                    >
                    @error('password')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="affiliation">所属</label>
                    <select
                        id="affiliation"
                        name="affiliation"
                        required
                    >
                        <option value="">選択してください</option>
                        @foreach ($venues as $venue)
                            <option value="{{ $venue->venue_name }}" @selected(old('affiliation', $target->affiliation) === $venue->venue_name)>
                                {{ $venue->venue_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('affiliation')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-actions">
                    <a href="{{ route('users.index') }}" class="form-cancel">キャンセル</a>
                    <button type="submit" class="form-submit">更新</button>
                </div>
            </form>
        </div>
    </div>

    <ul class="msg">
        @if ($errors->any())
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        @endif
    </ul>
</x-app-layout>
