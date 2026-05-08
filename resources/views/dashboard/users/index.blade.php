<x-app-layout>
    <x-slot:title>ユーザー管理</x-slot:title>
    <x-slot:page>users</x-slot:page>
    <x-slot:name>{{ $user->name }}</x-slot:name>
    <x-slot:role>{{ $user->role }}</x-slot:role>
    <x-slot:login>{{ $user->last_login_at?->format('Y.m.d H:i') }}</x-slot:login>
    
    <div class="content users-admin-page">
        <div class="users-admin-shell">
            <div class="users-admin-head">
                <h2>ユーザー管理</h2>
                <p>ユーザーの名前、メールアドレス、最終ログイン日時を一覧表示します。</p>
            </div>

            <div class="users-admin-table-wrap">
                <table class="users-admin-table">
                    <thead>
                        <tr>
                            <th scope="col">名前</th>
                            <th scope="col">メールアドレス</th>
                            <th scope="col">最終ログイン日時</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $row)
                            <tr>
                                <td>{{ $row->name }}</td>
                                <td>{{ $row->email }}</td>
                                <td>{{ optional($row->last_login_at)->format('Y-m-d H:i:s') ?: '未ログイン' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="users-admin-empty">ユーザーデータがありません。</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="users-admin-pagination">
                {{ $users->links('vendor.pagination.count') }}
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
