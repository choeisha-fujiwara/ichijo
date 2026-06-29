<div class="reservation-admin-table" role="list">
    @forelse ($reservations as $reservation)
        @php
            $slotDateTime = null;
            $fullName = trim((string) ($reservation->firstname ?? '') . ' ' . (string) ($reservation->lastname ?? ''));
            if (!empty($reservation->reservation_datetime)) {
                try {
                    $slotDateTime = \Carbon\Carbon::parse($reservation->reservation_datetime);
                } catch (\Throwable $e) {
                    $slotDateTime = null;
                }
            }

            if (!$slotDateTime && $reservation->reservationSlot?->date && $reservation->reservationSlot?->start_time) {
                try {
                    $slotDateTime = \Carbon\Carbon::parse($reservation->reservationSlot->date->format('Y-m-d') . ' ' . substr((string) $reservation->reservationSlot->start_time, 0, 5));
                } catch (\Throwable $e) {
                    $slotDateTime = null;
                }
            }

            if (!$slotDateTime) {
                $statusLabel = '日時未設定';
                $statusClass = 'is-unscheduled';
            } elseif ($slotDateTime->lt(now())) {
                $statusLabel = '来場済み';
                $statusClass = 'is-finished';
            } else {
                $statusLabel = '予約済み';
                $statusClass = 'is-booked';
            }
        @endphp
        <div class="reservation-admin-row-wrap" role="listitem">
            <a href="{{ route('reservations.show', $reservation) }}" class="reservation-admin-row">
                <div class="reservation-admin-main">
                    <h3>{{ $reservation->article?->title ?? '（記事未設定）' }}</h3>
                    <h3>{{ $reservation->article?->venue?->venue_name ?? '（会場未設定）' }}</h3>
                    <div class="reservation-admin-main-p">
                        <p class="reservation-admin-created-at">受付日時: {{ $reservation->created_at ? $reservation->created_at->format('Y-m-d H:i') : '未設定' }}</p>
                        <p class="reservation-admin-date">予約日時: {{ $slotDateTime ? $slotDateTime->format('Y-m-d H:i') : '未設定' }}</p>
                        <p class="reservation-admin-name">ご予約者様名: {{ $fullName !== '' ? $fullName : '未設定' }} 様</p>
                        <p class="reservation-admin-staff">担当者: {{ $reservation->staff ?? '未設定' }}</p>
                    </div>
                </div>
                <div class="reservation-admin-side">
                    {{-- <span class="reservation-admin-status {{ $statusClass }}">{{ $statusLabel }}</span> --}}
                    <span class="reservation-admin-arrow">詳細を見る</span>
                </div>
            </a>
            @if ($user->role !== 'staff' && $user->role !== 'manager')
                <div class="top-list-delete reservation-admin-delete"
                    data-name="{{ e($fullName !== '' ? $fullName : 'この予約') }}"
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
    @empty
        <div class="reservation-admin-empty">
            <p>予約データがありません。</p>
        </div>
    @endforelse
</div>

<div class="reservation-admin-pagination">
    {{ $reservations->links('vendor.pagination.count') }}
</div>
