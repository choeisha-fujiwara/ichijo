<x-app-layout>
    <x-slot:title>ユーザー追加</x-slot:title>
    <x-slot:page>users</x-slot:page>
    <x-slot:name>{{ $user->name }}</x-slot:name>
    <x-slot:role>{{ $user->role }}</x-slot:role>
    <x-slot:login>{{ $user->last_login_at?->format('Y.m.d H:i') }}</x-slot:login>

    <div class="content users-create-page">
        <div class="users-create-shell">
            <div class="users-create-head">
                <h2>ユーザー追加</h2>
                <p>新しいユーザーを追加します。</p>
            </div>

            <form method="POST" action="{{ route('users.store') }}" class="users-create-form">
                @csrf

                <div class="form-group">
                    <label for="name">氏名</label>
                    <input
                        id="name"
                        type="text"
                        name="name"
                        value="{{ old('name') }}"
                        required
                        autofocus
                        autocomplete="off"
                    >
                    @error('name')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="email">メールアドレス</label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autocomplete="off"
                    >
                    @error('email')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password">パスワード</label>
                    <input
                        id="password"
                        type="text"
                        pattern="^[a-zA-Z0-9]+$"
                        name="password"
                        placeholder="半角英数字で入力してください"
                        required
                        autocomplete="new-password"
                    >
                    @error('password')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                @if ($user->role !== 'manager')
                <div class="form-group">
                    <label for="role">ロール</label>
                    <select id="role" name="role" required>
                        <option value="">選択してください</option>
                        @foreach ($availableRoles as $role)
                            <option value="{{ $role }}" @selected(old('role') === $role)>
                                {{ $role }}
                            </option>
                        @endforeach
                    </select>
                    @error('role')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
                @else
                <div class="form-group role-select">
                    <label for="role">ロール</label>
                    <input id="role" type="text" value="staff" readonly>
                    <input type="hidden" name="role" value="staff">
                </div>
                @endif

                <div class="form-group">
                    <label for="affiliation">所属</label>
                    <select
                        id="affiliation"
                        name="affiliation"
                        required
                    >
                        <option value="">選択してください</option>
                        @foreach ($venues as $venue)
                            <option value="{{ $venue->venue_name }}" @selected(old('affiliation') === $venue->venue_name)>
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
                    <button type="submit" class="form-submit">追加</button>
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
