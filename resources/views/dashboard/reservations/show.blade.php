<x-app-layout>
    <x-slot:title>予約詳細</x-slot:title>
    <x-slot:page>reservation</x-slot:page>
    <x-slot:name>{{ $user->name }}</x-slot:name>
    <x-slot:role>{{ $user->role }}</x-slot:role>
    <x-slot:keyword>{{ @$keyword }}</x-slot:keyword>
    <x-slot:state>{{ @$state }}</x-slot:state>
    <x-slot:old>{{ @$old }}</x-slot:old>

    <div class="content reservation-admin-page">
        <div class="reservation-detail-shell">
            <div class="reservation-detail-head">
                <h2>予約詳細</h2>
                <a href="{{ route('reservations.index') }}" class="reservation-back-link">一覧に戻る</a>
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
