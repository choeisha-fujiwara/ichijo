<x-app-layout>
    <x-slot:title>予約一覧</x-slot:title>
    <x-slot:page>reservation</x-slot:page>
    <x-slot:name>{{ $user->name }}</x-slot:name>
    <x-slot:role>{{ $user->role }}</x-slot:role>
    <x-slot:keyword>{{ @$keyword }}</x-slot:keyword>
    <x-slot:state>{{ @$state }}</x-slot:state>
    <x-slot:old>{{ @$old }}</x-slot:old>

    <div class="content reservation-admin-page">
        <div class="reservation-admin-shell">
            <div class="reservation-admin-head">
                <div>
                    <h2>予約一覧</h2>
                    <p>予約データの確認、ソート、CSV出力ができます。</p>
                </div>
                <div class="reservation-admin-tools">
                    <form action="{{ route('reservations.index') }}" method="GET" class="reservation-sort-form">
                        <label for="reservation-sort">表示順</label>
                        <select id="reservation-sort" name="sort" onchange="this.form.submit()">
                            <option value="created_desc" @selected($sort === 'created_desc')>受付が新しい順</option>
                            <option value="created_asc" @selected($sort === 'created_asc')>受付が古い順</option>
                            <option value="reserved_desc" @selected($sort === 'reserved_desc')>予約日時が新しい順</option>
                            <option value="reserved_asc" @selected($sort === 'reserved_asc')>予約日時が古い順</option>
                        </select>
                    </form>
                    <a href="{{ route('reservations.export', ['sort' => $sort]) }}" class="reservation-export-link">CSV出力</a>
                </div>
            </div>

            <div class="reservation-admin-table" role="list">
                @forelse ($reservations as $reservation)
                    @php
                        $slotDateTime = null;
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
                    <a href="{{ route('reservations.show', $reservation) }}" class="reservation-admin-row" role="listitem">
                        <div class="reservation-admin-main">
                            <h3>{{ $reservation->article?->title ?? '（記事未設定）' }}</h3>
                            <h3>{{ $reservation->article?->venue?->venue_name ?? '（会場未設定）' }}</h3>
                            <p class="reservation-admin-date">予約日時: {{ $slotDateTime ? $slotDateTime->format('Y-m-d H:i') : '未設定' }}</p>
                        </div>
                        <div class="reservation-admin-side">
                            {{-- <span class="reservation-admin-status {{ $statusClass }}">{{ $statusLabel }}</span> --}}
                            <span class="reservation-admin-arrow">詳細を見る</span>
                        </div>
                    </a>
                @empty
                    <div class="reservation-admin-empty">
                        <p>予約データがありません。</p>
                    </div>
                @endforelse
            </div>

            <div class="reservation-admin-pagination">
                {{ $reservations->links('vendor.pagination.count') }}
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
