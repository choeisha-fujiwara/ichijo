<x-app-layout>
    <x-slot:title>予約一覧</x-slot:title>
    <x-slot:page>reservation</x-slot:page>
    <x-slot:name>{{ $user->name }}</x-slot:name>
    <x-slot:role>{{ $user->role }}</x-slot:role>
    <x-slot:login>{{ $user->last_login_at?->format('Y.m.d H:i') }}</x-slot:login>
    <x-slot:old>{{ @$old }}</x-slot:old>

    @php
        $venues  = $venues  ?? collect();
        $filters = $filters ?? [];
    @endphp
    <div class="content reservation-admin-page">
        <div class="reservation-admin-shell">
            <div class="reservation-admin-head">
                <div class="reservation-admin-head-main">
                    <div>
                        <h2>予約一覧</h2>
                        <p>予約データの確認、ソート、検索、CSV出力ができます。</p>
                    </div>
                    <div class="reservation-admin-tools">
                        <form action="{{ route('reservations.index') }}" method="GET" class="reservation-sort-form">
                            @foreach ($filters as $key => $val)
                                @if (!empty($val))
                                    <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                                @endif
                            @endforeach
                            <label for="reservation-sort">表示順</label>
                            <select id="reservation-sort" name="sort" onchange="this.form.submit()">
                                <option value="created_desc" @selected($sort === 'created_desc')>受付が新しい順</option>
                                <option value="created_asc" @selected($sort === 'created_asc')>受付が古い順</option>
                                <option value="reserved_desc" @selected($sort === 'reserved_desc')>予約日時が新しい順</option>
                                <option value="reserved_asc" @selected($sort === 'reserved_asc')>予約日時が古い順</option>
                            </select>
                        </form>
                        @if ($user->role !== 'staff')
                        <a href="{{ route('reservations.export', array_filter(array_merge(['sort' => $sort], $filters))) }}" class="reservation-export-link">CSV出力</a>
                        @endif
                    </div>
                </div>

                <form method="GET" action="{{ route('reservations.index') }}" class="reservation-admin-filter" aria-label="予約絞り込み">
                    <input type="hidden" name="sort" value="{{ $sort }}">

                    <div class="reservation-filter-field">
                        <label for="filter-venue-id">会場</label>
                        <select id="filter-venue-id" name="venue_id">
                            <option value="">すべての会場</option>
                            @foreach ($venues as $venue)
                                <option value="{{ $venue->id }}" @selected((string) ($filters['venue_id'] ?? '') === (string) $venue->id)>
                                    {{ $venue->venue_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="reservation-filter-field reservation-filter-field-date">
                        <label for="filter-reserved-from">予約日</label>
                        <div class="reservation-filter-date-range">
                            <input
                                id="filter-reserved-from"
                                type="date"
                                name="reserved_from"
                                value="{{ $filters['reserved_from'] ?? '' }}"
                            >
                            <span>〜</span>
                            <input
                                id="filter-reserved-to"
                                type="date"
                                name="reserved_to"
                                value="{{ $filters['reserved_to'] ?? '' }}"
                            >
                        </div>
                    </div>

                    <div class="reservation-filter-actions">
                        <button type="submit" class="reservation-filter-submit">検索</button>
                        <a href="{{ route('reservations.index', ['sort' => $sort]) }}" class="reservation-filter-reset">リセット</a>
                    </div>
                </form>

                @if ($errors->any())
                    <p class="reservation-filter-error">{{ $errors->first() }}</p>
                @endif
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
                            <div class="reservation-admin-main-p">
                                <p class="reservation-admin-date">予約日時: {{ $slotDateTime ? $slotDateTime->format('Y-m-d H:i') : '未設定' }}</p>
                                <p class="reservation-admin-name">ご予約者様名: {{ $reservation->firstname ?? '未設定' }} 様</p>
                                <p class="reservation-admin-staff">担当者: {{ $reservation->staff ?? '未設定' }}</p>
                            </div>
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
