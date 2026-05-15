@props([
    'initialSlots' => [],
])

@php
    $extractTimeParts = function ($timeValue): array {
        if ($timeValue instanceof \DateTimeInterface) {
            return [$timeValue->format('H'), $timeValue->format('i')];
        }

        $timeText = trim((string) $timeValue);
        if (preg_match('/^(\d{1,2}):(\d{2})/', $timeText, $matches) === 1) {
            return [str_pad($matches[1], 2, '0', STR_PAD_LEFT), $matches[2]];
        }

        return [null, null];
    };

    $initialSlotsPayload = collect($initialSlots)
        ->map(function ($slot, $index) use ($extractTimeParts) {
            if (is_array($slot)) {
                $rawDates = $slot['dates'] ?? ($slot['date'] ?? null);
                $dates = is_array($rawDates)
                    ? array_values(array_filter($rawDates))
                    : (!empty($rawDates) ? [(string) $rawDates] : []);

                [$startHour, $startMinute] = $extractTimeParts($slot['start_time'] ?? null);
                [$endHour, $endMinute] = $extractTimeParts($slot['end_time'] ?? null);

                return [
                    'id' => $slot['id'] ?? ($index + 1),
                    'dates' => $dates,
                    'start_hour' => isset($slot['start_hour']) ? str_pad((string) $slot['start_hour'], 2, '0', STR_PAD_LEFT) : $startHour,
                    'start_minute' => isset($slot['start_minute']) ? str_pad((string) $slot['start_minute'], 2, '0', STR_PAD_LEFT) : $startMinute,
                    'end_hour' => isset($slot['end_hour']) ? str_pad((string) $slot['end_hour'], 2, '0', STR_PAD_LEFT) : $endHour,
                    'end_minute' => isset($slot['end_minute']) ? str_pad((string) $slot['end_minute'], 2, '0', STR_PAD_LEFT) : $endMinute,
                    'capacity' => max(1, (int) ($slot['capacity'] ?? 1)),
                ];
            }

            [$startHour, $startMinute] = $extractTimeParts($slot->start_time ?? null);
            [$endHour, $endMinute] = $extractTimeParts($slot->end_time ?? null);

            return [
                'id' => $slot->id ?? ($index + 1),
                'dates' => [optional($slot->date)->format('Y-m-d')],
                'start_hour' => $startHour,
                'start_minute' => $startMinute,
                'end_hour' => $endHour,
                'end_minute' => $endMinute,
                'capacity' => max(1, (int) ($slot->capacity ?? 1)),
            ];
        })
        ->values()
        ->all();
@endphp

<div x-data="reservationSlotInput({ initialSlots: @js($initialSlotsPayload) })" class="reservation-slots">
    <div>
        <template x-for="(slot, index) in slots" :key="slot.id">
            <div class="reservation-slot-card">

                {{-- 削除ボタン --}}
                <button
                    type="button"
                    x-show="slots.length > 1"
                    class="reservation-slot-remove"
                    x-on:click="removeSlot(slot.id)"
                ><span class="material-symbols-outlined">delete</span></button>


                {{-- 日付 --}}
                <div class="reservation-slot-date input-item">
                    <label>日付</label>
                    <input
                        type="text"
                        :data-slot-id="slot.id"
                        :value="formatSelectedDates(slot.dates)"
                        readonly
                        x-on:click="openDatePicker(slot.id)"
                        placeholder="カレンダーから選択"
                    />
                    <div class="reservation-slot-date-actions">
                        <button
                            type="button"
                            class="reservation-slot-date-mode-btn"
                            :class="{ 'is-active': slot.bulkMode }"
                            x-on:click.stop.prevent="toggleBulkDateMode(slot.id)"
                        >連続日を一括選択</button>
                        <button
                            type="button"
                            class="reservation-slot-date-clear-btn"
                            x-on:click.stop.prevent="clearSlotDates(slot.id)"
                        >選択解除</button>
                    </div>
                    <p class="reservation-slot-date-help" x-show="slot.bulkMode" x-text="slot.rangeStart ? `開始日: ${slot.rangeStart}（次のクリックで範囲追加）` : '一括選択モード: 1日目を選択後、2日目を選ぶと期間をまとめて追加します。'"></p>
                    <template x-for="(date, dateIndex) in slot.dates" :key="`${slot.id}-${date}-${dateIndex}`">
                        <input type="hidden" :name="`slots[${index}][dates][${dateIndex}]`" :value="date">
                    </template>
                </div>

                {{-- 開始時間 --}}
                <div class="reservation-slot-times">
                    <div class="input-item">
                        <label>開始時間</label>
                        <div class="reservation-slot-time">
                            <select
                                :name="`slots[${index}][start_hour]`"
                                x-model="slot.start_hour"
                            >
                                <template x-for="h in hours" :key="h">
                                    <option :value="h" x-text="h"></option>
                                </template>
                            </select>
                            <span>:</span>
                            <select
                                :name="`slots[${index}][start_minute]`"
                                x-model="slot.start_minute"
                            >
                                <template x-for="m in minutes" :key="m">
                                    <option :value="m" x-text="m"></option>
                                </template>
                            </select>
                        </div>
                    </div>

                    {{-- 〜 --}}
                    <span class="material-symbols-outlined">check_indeterminate_small</span>

                    {{-- 終了時間 --}}
                    <div class="input-item">
                        <label>終了時間</label>
                        <div class="reservation-slot-time">
                            <select
                                :name="`slots[${index}][end_hour]`"
                                x-model="slot.end_hour"
                            >
                                <template x-for="h in hours" :key="h">
                                    <option :value="h" x-text="h"></option>
                                </template>
                            </select>
                            <span>:</span>
                            <select
                                :name="`slots[${index}][end_minute]`"
                                x-model="slot.end_minute"
                            >
                                <template x-for="m in minutes" :key="m">
                                    <option :value="m" x-text="m"></option>
                                </template>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- 予約枠数 --}}
                <div class="input-item reservation-slot-capacity">
                    <label>予約枠数</label>
                    <input
                        type="number"
                        :name="`slots[${index}][capacity]`"
                        x-model.number="slot.capacity"
                        x-on:input="slot.capacity = normalizeCapacity(slot.capacity, 1)"
                        x-on:blur="slot.capacity = normalizeCapacity(slot.capacity, 1)"
                        min="1"
                        step="1"
                        inputmode="numeric"
                        class=""
                        placeholder="1"
                    />
                </div>

            </div>
        </template>
    </div>

    {{-- 追加ボタン --}}
    <button
        type="button"
        class="reservation-slot-add-btn"
        x-on:click.stop.prevent="addSlot()"
    ><span>＋</span> 予約枠を追加</button>
</div>

@once
<script>
function reservationSlotInput(config = {}) {
    return {
        slots: Array.isArray(config.initialSlots) && config.initialSlots.length > 0
            ? config.initialSlots
            : [{ id: 1, dates: [], start_hour: '10', start_minute: '00', end_hour: '11', end_minute: '00', capacity: 1 }],
        nextId: 2,
        addSlotLock: false,
        hours: Array.from({ length: 9 }, (_, i) => String(i + 10).padStart(2, '0')),
        minutes: ['00', '15', '30', '45'],
        defaultEndHour: '11',

        normalizeCapacity(value, fallback = 1) {
            if (value === null || value === undefined || value === '') {
                return fallback;
            }

            const normalized = Number.parseInt(String(value), 10);
            if (Number.isNaN(normalized) || normalized < 1) {
                return fallback;
            }

            return normalized;
        },

        normalizeHour(value, fallback = '10') {
            if (value === null || value === undefined || value === '') {
                return fallback;
            }

            const text = String(value).trim();
            const matched = text.match(/^(\d{1,2})/);
            const normalized = matched ? String(matched[1]).padStart(2, '0') : text.padStart(2, '0');
            return this.hours.includes(normalized) ? normalized : fallback;
        },

        normalizeMinute(value, fallback = '00') {
            if (value === null || value === undefined || value === '') {
                return fallback;
            }

            const text = String(value).trim();
            const fromTime = text.match(/:(\d{2})/);
            const fromPlain = text.match(/^(\d{1,2})$/);
            const normalized = fromTime
                ? String(fromTime[1]).padStart(2, '0')
                : (fromPlain ? String(fromPlain[1]).padStart(2, '0') : text.padStart(2, '0'));
            return this.minutes.includes(normalized) ? normalized : fallback;
        },

        parseTimePart(value, type = 'hour') {
            if (value === null || value === undefined || value === '') {
                return null;
            }

            const text = String(value).trim();
            const match = text.match(/^(\d{1,2}):(\d{2})/);
            if (!match) {
                return null;
            }

            return type === 'hour'
                ? String(match[1]).padStart(2, '0')
                : String(match[2]).padStart(2, '0');
        },

        init() {
            if (!Array.isArray(this.slots) || this.slots.length === 0) {
                this.slots = [{ id: 1, dates: [], start_hour: '10', start_minute: '00', end_hour: '11', end_minute: '00', capacity: 1 }];
                this.nextId = 2;
                return;
            }

            this.slots = this.slots.map((slot, index) => ({
                // start_time/end_time が来るケースもあるため、ここでも再パースして初期値へ反映する
                _startHourFromTime: this.parseTimePart(slot.start_time, 'hour'),
                _startMinuteFromTime: this.parseTimePart(slot.start_time, 'minute'),
                _endHourFromTime: this.parseTimePart(slot.end_time, 'hour'),
                _endMinuteFromTime: this.parseTimePart(slot.end_time, 'minute'),
                id: slot.id ?? (index + 1),
                dates: Array.isArray(slot.dates)
                    ? slot.dates.filter((date) => typeof date === 'string' && date.length > 0)
                    : (slot.date ? [slot.date] : []),
                bulkMode: Boolean(slot.bulkMode),
                rangeStart: null,
                start_hour: this.normalizeHour(slot.start_hour ?? this.parseTimePart(slot.start_time, 'hour'), '10'),
                start_minute: this.normalizeMinute(slot.start_minute ?? this.parseTimePart(slot.start_time, 'minute'), '00'),
                end_hour: this.normalizeHour(slot.end_hour ?? this.parseTimePart(slot.end_time, 'hour'), this.defaultEndHour),
                end_minute: this.normalizeMinute(slot.end_minute ?? this.parseTimePart(slot.end_time, 'minute'), '00'),
                capacity: this.normalizeCapacity(slot.capacity, 1),
            })).map(({ _startHourFromTime, _startMinuteFromTime, _endHourFromTime, _endMinuteFromTime, ...slot }) => slot);
            this.nextId = Math.max(...this.slots.map(slot => slot.id), 0) + 1;

            this.$nextTick(() => {
                this.syncTimeSelectValues();
                this.slots.forEach((slot) => this.initDatePicker(slot.id));
            });
        },

        syncTimeSelectValues() {
            this.slots.forEach((slot, index) => {
                const startHourSelect = this.$root.querySelector(`select[name="slots[${index}][start_hour]"]`);
                const startMinuteSelect = this.$root.querySelector(`select[name="slots[${index}][start_minute]"]`);
                const endHourSelect = this.$root.querySelector(`select[name="slots[${index}][end_hour]"]`);
                const endMinuteSelect = this.$root.querySelector(`select[name="slots[${index}][end_minute]"]`);

                if (startHourSelect) startHourSelect.value = this.normalizeHour(slot.start_hour, '10');
                if (startMinuteSelect) startMinuteSelect.value = this.normalizeMinute(slot.start_minute, '00');
                if (endHourSelect) endHourSelect.value = this.normalizeHour(slot.end_hour, this.defaultEndHour);
                if (endMinuteSelect) endMinuteSelect.value = this.normalizeMinute(slot.end_minute, '00');
            });
        },

        addSlot() {
            if (this.addSlotLock) return;
            this.addSlotLock = true;
            const newSlotId = this.nextId++;
            this.slots.push({ id: newSlotId, dates: [], bulkMode: false, rangeStart: null, start_hour: '10', start_minute: '00', end_hour: this.defaultEndHour, end_minute: '00', capacity: 1 });
            window.setTimeout(() => {
                this.addSlotLock = false;
            }, 120);

            this.$nextTick(() => this.initDatePicker(newSlotId));
        },

        removeSlot(id) {
            this.slots = this.slots.filter(s => s.id !== id);
        },

        findSlot(slotId) {
            return this.slots.find((slot) => Number(slot.id) === Number(slotId)) || null;
        },

        formatSelectedDates(dates) {
            if (!Array.isArray(dates) || dates.length === 0) {
                return '';
            }

            return [...dates]
                .sort((a, b) => a.localeCompare(b))
                .join(', ');
        },

        toggleBulkDateMode(slotId) {
            const slot = this.findSlot(slotId);
            if (!slot) {
                return;
            }

            slot.bulkMode = !slot.bulkMode;
            slot.rangeStart = null;
        },

        clearSlotDates(slotId) {
            const slot = this.findSlot(slotId);
            if (!slot) {
                return;
            }

            slot.dates = [];
            slot.bulkMode = false;
            slot.rangeStart = null;

            const input = this.$root.querySelector(`input[data-slot-id="${slotId}"]`);
            if (input && window.jQuery && jQuery.fn && jQuery.fn.datepicker) {
                const $input = jQuery(input);
                $input.val('');
                this.initDatePicker(slotId);
            }
        },

        buildDateRange(startDateText, endDateText) {
            const start = new Date(`${startDateText}T00:00:00`);
            const end = new Date(`${endDateText}T00:00:00`);
            if (Number.isNaN(start.getTime()) || Number.isNaN(end.getTime())) {
                return [];
            }

            const from = start <= end ? start : end;
            const to = start <= end ? end : start;
            const dates = [];
            const cursor = new Date(from);

            while (cursor <= to) {
                dates.push(cursor.toISOString().slice(0, 10));
                cursor.setDate(cursor.getDate() + 1);
            }

            return dates;
        },

        initDatePicker(slotId) {
            if (!(window.jQuery && jQuery.fn && jQuery.fn.datepicker)) {
                return;
            }

            const input = this.$root.querySelector(`input[data-slot-id="${slotId}"]`);
            const slot = this.findSlot(slotId);
            if (!input || !slot) {
                return;
            }

            const $input = jQuery(input);
            const selectedDates = new Set(slot.dates || []);

            $input.datepicker('destroy');
            $input.datepicker({
                dateFormat: 'yy-mm-dd',
                defaultDate: 0,
                changeMonth: true,
                numberOfMonths: 2,
                beforeShowDay: (date) => {
                    const dateKey = jQuery.datepicker.formatDate('yy-mm-dd', date);
                    return [true, selectedDates.has(dateKey) ? 'ui-state-active' : ''];
                },
                onSelect: (dateText) => {
                    if (slot.bulkMode) {
                        if (!slot.rangeStart) {
                            slot.rangeStart = dateText;
                            selectedDates.add(dateText);
                        } else {
                            const rangeDates = this.buildDateRange(slot.rangeStart, dateText);
                            rangeDates.forEach((date) => selectedDates.add(date));
                            slot.rangeStart = null;
                        }
                    } else {
                        if (selectedDates.has(dateText)) {
                            selectedDates.delete(dateText);
                        } else {
                            selectedDates.add(dateText);
                        }
                    }

                    const normalized = [...selectedDates].sort((a, b) => a.localeCompare(b));
                    slot.dates = normalized;
                    $input.val(this.formatSelectedDates(normalized));
                    window.setTimeout(() => $input.datepicker('refresh'), 0);
                },
                beforeShow: () => {
                    $input.val(this.formatSelectedDates(slot.dates));
                },
            });

            $input.val(this.formatSelectedDates(slot.dates));
        },

        openDatePicker(slotId) {
            if (!(window.jQuery && jQuery.fn && jQuery.fn.datepicker)) {
                return;
            }

            const input = this.$root.querySelector(`input[data-slot-id="${slotId}"]`);
            if (!input) {
                return;
            }

            jQuery(input).datepicker('show');
        },
    };
}
</script>
@endonce
