<x-app-layout>
    <x-slot:title>予約詳細</x-slot:title>
    <x-slot:page>reservation</x-slot:page>
    <x-slot:name>{{ $user->name }}</x-slot:name>
    <x-slot:role>{{ $user->role }}</x-slot:role>
    <x-slot:login>{{ $user->last_login_at?->format('Y.m.d H:i') }}</x-slot:login>
    <x-slot:old>{{ @$old }}</x-slot:old>

    <div class="content reservation-admin-page">
        <div class="reservation-detail-shell" @if($user->role === 'staff') id="reservation-detail-shell-nocopy" @endif>
            <div class="reservation-detail-head">
                <h2>予約詳細</h2>
                <div class="reservation-detail-actions">
                    <a href="{{ route('reservations.index') }}" class="reservation-back-link">一覧に戻る</a>
                    @if ($user->role !== 'staff' && $user->role !== 'manager')
                        <div
                            class="top-list-delete reservation-detail-delete"
                            data-name="{{ e(trim(($reservation->firstname ?? '') . ' ' . ($reservation->lastname ?? '')) ?: 'この予約') }}"
                            onclick="(function(el){ var n=el.getAttribute('data-name'); var m='「'+n+'」の予約を削除しますか？'; m+='\nこの操作は元に戻せません。'; if(confirm(m)){el.querySelector('form').submit();} })(this)"
                        >
                            <span class="material-symbols-outlined">delete</span>
                            <form
                                action="{{ route('reservations.destroy', $reservation) }}"
                                method="POST"
                                style="display:none"
                            >
                                @csrf
                                @method('DELETE')
                            </form>
                        </div>
                    @endif
                </div>
            </div>

            <div class="reservation-detail-status-row">
                {{-- <span class="reservation-admin-status {{ $statusClass }}">{{ $statusLabel }}</span> --}}
                <p>受付日時: {{ optional($reservation->created_at)->format('Y-m-d H:i') }}</p>
            </div>

            <dl class="reservation-detail-list">
                <div>
                    <dt>イベント名</dt>
                    <dd>{{ $reservation->article?->title ?? '（記事未設定）' }}</dd>
                </div>
                <div>
                    <dt>予約日時</dt>
                    @php
                        $reservationDateText = '未設定';

                        if (!empty($reservation->reservation_datetime)) {
                            $reservationDateText = (string) $reservation->reservation_datetime;
                        } elseif ($reservation->reservationSlot?->date && $reservation->reservationSlot?->start_time) {
                            $reservationDateText = $reservation->reservationSlot->date->format('Y-m-d') . ' ' . substr((string) $reservation->reservationSlot->start_time, 0, 5);
                        }
                    @endphp
                    <dd class="reservation-datetime-text">{{ $reservationDateText }}</dd>
                </div>
                <div>
                    <dt>氏名</dt>
                    <dd>{{ trim(($reservation->firstname ?? '') . ' ' . ($reservation->lastname ?? '')) ?: '未設定' }}</dd>
                </div>
                <div>
                    <dt>氏名カナ</dt>
                    <dd>{{ trim(($reservation->firstname_kana ?? '') . ' ' . ($reservation->lastname_kana ?? '')) ?: '未設定' }}</dd>
                </div>
                <div>
                    <dt>電話番号</dt>
                    <dd>{{ $reservation->phone ?: '未設定' }}</dd>
                </div>
                <div>
                    <dt>メールアドレス</dt>
                    <dd>{{ $reservation->email ?: '未設定' }}</dd>
                </div>
                <div>
                    <dt>住所</dt>
                    <dd>{{ trim(($reservation->prefecture ?? '') . ($reservation->city ?? '') . ($reservation->address ?? '') . ' ' . ($reservation->building ?? '')) ?: '未設定' }}</dd>
                </div>
                <div>
                    <dt>備考</dt>
                    <dd>{{ $reservation->memo ?: '未設定' }}</dd>
                </div>
            </dl>

            <form
                action="{{ route('reservations.updateStaff', $reservation) }}"
                method="POST"
                class="reservation-staff-form"
            >
                @csrf
                @method('PATCH')
                <div class="reservation-staff-field">
                    <label for="reservation-staff">担当者</label>
                    <input
                        id="reservation-staff"
                        type="text"
                        name="staff"
                        value="{{ old('staff', $reservation->staff ?? '') }}"
                        placeholder="担当者名を入力"
                        maxlength="100"
                    >
                </div>
                <button type="submit" class="reservation-staff-submit">保存</button>
            </form>
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

    @if($user->role === 'staff')
    <div id="ss-blackout" style="display:none;position:fixed;inset:0;background:#000;z-index:999999;"></div>
    <script>
    (function () {
        var shell = document.getElementById('reservation-detail-shell-nocopy');
        if (!shell) return;
        var staffField = shell.querySelector('.reservation-staff-field');
        var blackout = document.getElementById('ss-blackout');
        var blackoutTimer = null;

        function showBlackout() {
            blackout.style.display = 'block';
            clearTimeout(blackoutTimer);
            blackoutTimer = setTimeout(function () {
                blackout.style.display = 'none';
            }, 2000);
        }

        function isScreenshotKey(e) {
            // PrintScreen (Windows)
            if (e.key === 'PrintScreen') return true;
            // Win+Shift+S (Windows Snipping Tool)
            if (e.shiftKey && e.metaKey && (e.key === 'S' || e.key === 's')) return true;
            // Mac: Cmd+Shift+3, Cmd+Shift+4, Cmd+Shift+5
            if (e.metaKey && e.shiftKey && ['3','4','5'].includes(e.key)) return true;
            // Mac: Cmd+Ctrl+Shift+3, Cmd+Ctrl+Shift+4
            if (e.metaKey && e.ctrlKey && e.shiftKey && ['3','4'].includes(e.key)) return true;
            return false;
        }

        document.addEventListener('keydown', function (e) {
            if (isScreenshotKey(e)) showBlackout();
        });

        function isInStaffField(target) {
            return staffField && staffField.contains(target);
        }

        shell.addEventListener('contextmenu', function (e) {
            if (!isInStaffField(e.target)) e.preventDefault();
        });

        shell.addEventListener('copy', function (e) {
            if (!isInStaffField(e.target)) e.preventDefault();
        });

        shell.addEventListener('selectstart', function (e) {
            if (!isInStaffField(e.target)) e.preventDefault();
        });
    })();
    </script>
    @endif
</x-app-layout>
