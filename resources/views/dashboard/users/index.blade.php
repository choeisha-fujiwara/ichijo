<x-app-layout>
    <x-slot:title>ユーザー管理</x-slot:title>
    <x-slot:page>users</x-slot:page>
    <x-slot:name>{{ $user->name }}</x-slot:name>
    <x-slot:role>{{ $user->role }}</x-slot:role>
    <x-slot:login>{{ $user->last_login_at?->format('Y.m.d H:i') }}</x-slot:login>

    <div class="content users-admin-page">
        <div class="users-admin-shell">
            <div class="users-admin-head">
                <div class="users-admin-head-main">
                    <h2>ユーザー管理</h2>
                    <p>ユーザー状況の確認、追加・編集・削除ができます。</p>
                </div>
                <div class="users-admin-tools">
                    <a href="{{ route('users.create') }}" class="users-create-link">＋ユーザー追加</a>
                </div>
            </div>

            <div class="users-admin-table-wrap">
                <table class="users-admin-table">
                    <thead>
                        <tr>
                            <th scope="col">所属</th>
                            <th scope="col">氏名</th>
                            <th scope="col" class="center">
                                <span class="users-th-tooltip" data-tooltip="直近30日間の記事作成数です。">
                                    記事作成数<span class="material-symbols-outlined">help</span>
                                </span>
                            </th>
                            <th scope="col" class="center">
                                <span class="users-th-tooltip" data-tooltip="直近30日間の予約件数です。">
                                    予約件数<span class="material-symbols-outlined">help</span>
                                </span>
                            </th>
                            <th scope="col" class="center">
                                <span class="users-th-tooltip" data-tooltip="直近30日間の予約詳細画面（個人情報）にアクセスした回数です。異常値には注意してください。">
                                    閲覧ログ<span class="material-symbols-outlined">help</span>
                                </span>
                            </th>
                            <th scope="col" class="center">最終ログイン日時</th>
                            <th scope="col">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $row)
                            <tr>
                                <td>{{ $row->affiliation }}</td>
                                <td>{{ $row->name }}</td>
                                <td class="center">{{ $row->articles_count ?? 0 }}件</td>
                                <td class="center">{{ $row->recent_reservation_count ?? 0 }}件</td>
                                <td class="center">{{ $row->recent_access_count ?? 0 }}回</td>
                                <td class="center">{{ optional($row->last_login_at)->format('Y-m-d H:i:s') ?: '未ログイン' }}</td>
                                <td>
                                    <div class="users-actions">
                                        @if (
                                            $row->role === 'staff' ||
                                            ($row->role === 'manager' && in_array($user->role, ['admin', 'system', 'developer'], true))
                                        )
                                            <a href="{{ route('users.edit', $row) }}" class="users-edit-link">編集</a>
                                        @endif
                                        <form method="POST" action="{{ route('users.destroy', $row) }}" onsubmit="return confirm('「{{ e($row->name) }}」を削除しますか？');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="users-delete-button">削除</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="users-admin-empty">ユーザーデータがありません。</td>
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
