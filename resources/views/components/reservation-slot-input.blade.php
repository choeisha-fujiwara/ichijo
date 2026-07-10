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
    <div class="reservation-slot-toolbar">
        <button
            type="button"
            class="reservation-slot-date-mode-btn"
            x-on:click.stop.prevent="openBuilder()"
        ><span>＋</span> 枠を設定</button>
        <button
            type="button"
            class="reservation-slot-time-add-btn"
            x-on:click.stop.prevent="addManualSlot()"
        >手動で1件追加</button>
        <p class="reservation-slot-summary" x-text="summaryText()"></p>
    </div>

    <div class="reservation-slot-list" x-show="slots.length > 0">
        <template x-for="group in groupedSlots()" :key="group.date || '__undated__'">
            <div class="reservation-slot-date-group" :class="{ 'is-open': isDateOpen(group.date) }">
                <button
                    type="button"
                    class="reservation-slot-date-header"
                    x-on:click="toggleDateGroup(group.date)"
                    :aria-expanded="isDateOpen(group.date) ? 'true' : 'false'"
                >
                    <span class="reservation-slot-date-header-label" x-text="formatGroupDate(group.date)"></span>
                    <span class="reservation-slot-date-header-count" x-text="`${group.items.length}件`"></span>
                    <span class="material-symbols-outlined reservation-slot-date-header-icon">expand_more</span>
                </button>

                <div class="reservation-slot-date-body" x-show="isDateOpen(group.date)" x-collapse>
                    <template x-for="item in group.items" :key="item.slot.id">
                        <div class="reservation-slot-card reservation-slot-card-list">
                            <input type="hidden" :name="`slots[${item.index}][id]`" :value="item.slot.id">
                            <div class="input-item reservation-slot-list-date">
                                <label>日付</label>
                                <input
                                    type="date"
                                    :name="`slots[${item.index}][date]`"
                                    x-model="item.slot.date"
                                />
                            </div>

                            <div class="reservation-slot-times">
                                <div class="input-item">
                                    <label>開始時間</label>
                                    <input type="hidden" :name="`slots[${item.index}][start_hour]`" :value="item.slot.start_hour">
                                    <input type="hidden" :name="`slots[${item.index}][start_minute]`" :value="item.slot.start_minute">
                                    <div class="reservation-slot-time">
                                        <select :value="item.slot.start_hour" x-on:change="item.slot.start_hour = $event.target.value">
                                            <template x-for="h in hours" :key="`list-start-hour-${item.slot.id}-${h}`">
                                                <option :value="h" :selected="item.slot.start_hour === h" x-text="h"></option>
                                            </template>
                                        </select>
                                        <span>:</span>
                                        <select :value="item.slot.start_minute" x-on:change="item.slot.start_minute = $event.target.value">
                                            <template x-for="m in minutes" :key="`list-start-minute-${item.slot.id}-${m}`">
                                                <option :value="m" :selected="item.slot.start_minute === m" x-text="m"></option>
                                            </template>
                                        </select>
                                    </div>
                                </div>

                                <span class="material-symbols-outlined">check_indeterminate_small</span>

                                <div class="input-item">
                                    <label>終了時間</label>
                                    <input type="hidden" :name="`slots[${item.index}][end_hour]`" :value="item.slot.end_hour">
                                    <input type="hidden" :name="`slots[${item.index}][end_minute]`" :value="item.slot.end_minute">
                                    <div class="reservation-slot-time">
                                        <select :value="item.slot.end_hour" x-on:change="item.slot.end_hour = $event.target.value">
                                            <template x-for="h in hours" :key="`list-end-hour-${item.slot.id}-${h}`">
                                                <option :value="h" :selected="item.slot.end_hour === h" x-text="h"></option>
                                            </template>
                                        </select>
                                        <span>:</span>
                                        <select :value="item.slot.end_minute" x-on:change="item.slot.end_minute = $event.target.value">
                                            <template x-for="m in minutes" :key="`list-end-minute-${item.slot.id}-${m}`">
                                                <option :value="m" :selected="item.slot.end_minute === m" x-text="m"></option>
                                            </template>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="input-item reservation-slot-capacity">
                                <label>予約枠数</label>
                                <input
                                    type="number"
                                    :name="`slots[${item.index}][capacity]`"
                                    x-model.number="item.slot.capacity"
                                    x-on:input="item.slot.capacity = normalizeCapacity(item.slot.capacity, 1)"
                                    x-on:blur="item.slot.capacity = normalizeCapacity(item.slot.capacity, 1)"
                                    min="1"
                                    step="1"
                                    inputmode="numeric"
                                    placeholder="1"
                                />
                            </div>

                            <button
                                type="button"
                                class="reservation-slot-remove"
                                x-on:click="removeSlot(item.slot.id)"
                                aria-label="予約枠を削除"
                            ><span class="material-symbols-outlined">delete</span></button>
                        </div>
                    </template>
                </div>
            </div>
        </template>
    </div>

    <p class="reservation-slot-empty" x-show="slots.length === 0">まだ予約枠はありません。「枠を設定」からまとめて作成できます。</p>

    <div class="reservation-slot-modal" x-show="modalOpen" x-cloak>
        <button type="button" class="reservation-slot-modal-backdrop" x-on:click="closeBuilder()" aria-label="モーダルを閉じる"></button>

        <div class="reservation-slot-modal-panel" role="dialog" aria-modal="true" aria-labelledby="reservation-slot-modal-title" x-on:click.stop>
            <div class="reservation-slot-modal-header">
                <h3 id="reservation-slot-modal-title">予約枠を設定</h3>
                <button type="button" class="reservation-slot-modal-close" x-on:click="closeBuilder()" aria-label="閉じる">&times;</button>
            </div>

            <div class="reservation-slot-modal-body">
                <section class="reservation-slot-modal-calendar-wrap">
                    <p class="reservation-slot-modal-label">日付（複数選択）</p>
                    <div class="reservation-slot-modal-calendar" x-ref="modalCalendar"></div>
                    <p class="reservation-slot-date-help">クリックで選択/解除できます。選択中: <span x-text="modalSelectedDates.length"></span> 日</p>
                </section>

                <section class="reservation-slot-modal-times-wrap">
                    <div class="reservation-slot-modal-times-header">
                        <p class="reservation-slot-modal-label">時間枠</p>
                        <button type="button" class="reservation-slot-time-add-btn" x-on:click="addModalTimeRow()"><span>＋</span> 時間枠を追加</button>
                    </div>

                    <div class="reservation-slot-time-row-list">
                        <template x-for="(row, rowIndex) in modalTimeRows" :key="row.id">
                            <div class="reservation-slot-time-row" :data-time-row-id="row.id">
                                <div class="reservation-slot-time">
                                    <select :value="modalTimeRows[rowIndex].start_hour" data-time-field="start_hour" x-on:change="updateModalTimeRow(rowIndex, 'start_hour', $event.target.value)">
                                        <template x-for="h in hours" :key="`modal-start-hour-${row.id}-${h}`">
                                            <option :value="h" :selected="modalTimeRows[rowIndex].start_hour === h" x-text="h"></option>
                                        </template>
                                    </select>
                                    <span>:</span>
                                    <select :value="modalTimeRows[rowIndex].start_minute" data-time-field="start_minute" x-on:change="updateModalTimeRow(rowIndex, 'start_minute', $event.target.value)">
                                        <template x-for="m in minutes" :key="`modal-start-minute-${row.id}-${m}`">
                                            <option :value="m" :selected="modalTimeRows[rowIndex].start_minute === m" x-text="m"></option>
                                        </template>
                                    </select>
                                </div>

                                <span class="reservation-slot-time-sep">〜</span>

                                <div class="reservation-slot-time">
                                    <select :value="modalTimeRows[rowIndex].end_hour" data-time-field="end_hour" x-on:change="updateModalTimeRow(rowIndex, 'end_hour', $event.target.value)">
                                        <template x-for="h in hours" :key="`modal-end-hour-${row.id}-${h}`">
                                            <option :value="h" :selected="modalTimeRows[rowIndex].end_hour === h" x-text="h"></option>
                                        </template>
                                    </select>
                                    <span>:</span>
                                    <select :value="modalTimeRows[rowIndex].end_minute" data-time-field="end_minute" x-on:change="updateModalTimeRow(rowIndex, 'end_minute', $event.target.value)">
                                        <template x-for="m in minutes" :key="`modal-end-minute-${row.id}-${m}`">
                                            <option :value="m" :selected="modalTimeRows[rowIndex].end_minute === m" x-text="m"></option>
                                        </template>
                                    </select>
                                </div>

                                <input
                                    type="number"
                                    :value="modalTimeRows[rowIndex].capacity"
                                    data-time-field="capacity"
                                    x-on:input="updateModalTimeRow(rowIndex, 'capacity', $event.target.value)"
                                    x-on:blur="updateModalTimeRow(rowIndex, 'capacity', $event.target.value)"
                                    min="1"
                                    step="1"
                                    inputmode="numeric"
                                    class="reservation-slot-time-row-capacity"
                                    placeholder="枠数"
                                    aria-label="予約枠数"
                                />

                                <button
                                    type="button"
                                    class="reservation-slot-remove"
                                    x-show="modalTimeRows.length > 1"
                                    x-on:click="removeModalTimeRow(row.id)"
                                    aria-label="時間枠を削除"
                                ><span class="material-symbols-outlined">delete</span></button>
                            </div>
                        </template>
                    </div>
                </section>
            </div>

            <div class="reservation-slot-modal-footer">
                <button type="button" class="reservation-slot-date-clear-btn" x-on:click="closeBuilder()">キャンセル</button>
                <button type="button" class="reservation-slot-date-mode-btn decision" x-on:click="applyBuilderSlots()">決定</button>
            </div>
        </div>
    </div>
</div>

@once
<script>
function reservationSlotInput(config = {}) {
    const parseTimePart = (value, type = 'hour') => {
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
    };

    const flattenInitialSlots = (initialSlots = []) => {
        if (!Array.isArray(initialSlots)) {
            return [];
        }

        const rows = [];
        initialSlots.forEach((slot, index) => {
            if (!slot || typeof slot !== 'object') {
                return;
            }

            const rawDates = Array.isArray(slot.dates)
                ? slot.dates.filter((date) => typeof date === 'string' && date.trim() !== '')
                : (slot.date ? [slot.date] : []);

            const dates = rawDates.length > 0 ? rawDates : [''];
            dates.forEach((date, dateIndex) => {
                rows.push({
                    id: Number(slot.id ?? `${index + 1}${dateIndex}`),
                    date: typeof date === 'string' ? date : '',
                    start_hour: slot.start_hour ?? parseTimePart(slot.start_time, 'hour'),
                    start_minute: slot.start_minute ?? parseTimePart(slot.start_time, 'minute'),
                    end_hour: slot.end_hour ?? parseTimePart(slot.end_time, 'hour'),
                    end_minute: slot.end_minute ?? parseTimePart(slot.end_time, 'minute'),
                    capacity: slot.capacity,
                });
            });
        });

        return rows;
    };

    return {
        slots: flattenInitialSlots(config.initialSlots),
        nextId: 2,
        hours: Array.from({ length: 9 }, (_, i) => String(i + 10).padStart(2, '0')),
        minutes: ['00', '15', '30', '45'],
        defaultEndHour: '18',
        modalOpen: false,
        modalSelectedDates: [],
        modalTimeRows: [],
        modalNextTimeRowId: 1,
        modalCalendarInitialized: false,
        openDates: [],

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

        normalizeSlotRecord(slot = {}) {
            const sourceDates = Array.isArray(slot.dates)
                ? slot.dates.filter((date) => typeof date === 'string' && date.trim() !== '')
                : [];
            const date = typeof slot.date === 'string' && slot.date.trim() !== ''
                ? slot.date
                : (sourceDates[0] || '');

            return {
                id: Number(slot.id ?? this.nextId++),
                date,
                start_hour: this.normalizeHour(slot.start_hour, '10'),
                start_minute: this.normalizeMinute(slot.start_minute, '00'),
                end_hour: this.normalizeHour(slot.end_hour, '11'),
                end_minute: this.normalizeMinute(slot.end_minute, '00'),
                capacity: this.normalizeCapacity(slot.capacity, 1),
            };
        },

        toMinutes(hour, minute) {
            return Number.parseInt(hour, 10) * 60 + Number.parseInt(minute, 10);
        },

        createSlotPair(date, timeRow) {
            return {
                id: this.nextId++,
                date,
                start_hour: this.normalizeHour(timeRow.start_hour, '10'),
                start_minute: this.normalizeMinute(timeRow.start_minute, '00'),
                end_hour: this.normalizeHour(timeRow.end_hour, '11'),
                end_minute: this.normalizeMinute(timeRow.end_minute, '00'),
                capacity: this.normalizeCapacity(timeRow.capacity, 1),
            };
        },

        updateModalTimeRow(rowIndex, field, value) {
            if (!Array.isArray(this.modalTimeRows) || !Number.isInteger(rowIndex) || rowIndex < 0 || rowIndex >= this.modalTimeRows.length) {
                return;
            }

            const current = this.modalTimeRows[rowIndex] ?? {};
            let nextValue = value;

            if (field === 'start_hour') {
                nextValue = this.normalizeHour(value, '10');
            } else if (field === 'start_minute') {
                nextValue = this.normalizeMinute(value, '00');
            } else if (field === 'end_hour') {
                nextValue = this.normalizeHour(value, '11');
            } else if (field === 'end_minute') {
                nextValue = this.normalizeMinute(value, '00');
            } else if (field === 'capacity') {
                nextValue = this.normalizeCapacity(value, 1);
            }

            this.modalTimeRows.splice(rowIndex, 1, {
                ...current,
                [field]: nextValue,
            });
        },

        isValidTimeRange(startHour, startMinute, endHour, endMinute) {
            const start = this.toMinutes(startHour, startMinute);
            const end = this.toMinutes(endHour, endMinute);
            return end > start;
        },

        createDefaultModalTimeRows() {
            const rows = [];
            for (let hour = 10; hour < 18; hour += 1) {
                rows.push({
                    id: this.modalNextTimeRowId++,
                    start_hour: String(hour).padStart(2, '0'),
                    start_minute: '00',
                    end_hour: String(hour + 1).padStart(2, '0'),
                    end_minute: '00',
                    capacity: 1,
                });
            }

            return rows;
        },

        ensureModalTimeRows() {
            if (!Array.isArray(this.modalTimeRows) || this.modalTimeRows.length === 0) {
                this.modalTimeRows = this.createDefaultModalTimeRows();
            }
        },

        summaryText() {
            const count = this.slots.length;
            return count > 0 ? `作成済み: ${count}件` : '作成済み: 0件';
        },

        groupedSlots() {
            const groups = new Map();

            this.slots.forEach((slot, index) => {
                const key = slot.date || '';
                if (!groups.has(key)) {
                    groups.set(key, []);
                }
                groups.get(key).push({ slot, index });
            });

            return [...groups.entries()]
                .sort(([a], [b]) => {
                    if (!a) return 1;
                    if (!b) return -1;
                    return a.localeCompare(b);
                })
                .map(([date, items]) => ({ date, items }));
        },

        isDateOpen(date) {
            return this.openDates.includes(date || '');
        },

        openDateGroup(date) {
            const key = date || '';
            if (!this.openDates.includes(key)) {
                this.openDates = [...this.openDates, key];
            }
        },

        toggleDateGroup(date) {
            const key = date || '';
            if (this.openDates.includes(key)) {
                this.openDates = this.openDates.filter((d) => d !== key);
            } else {
                this.openDates = [...this.openDates, key];
            }
        },

        formatGroupDate(date) {
            if (!date) {
                return '日付未設定';
            }

            const parsed = new Date(`${date}T00:00:00`);
            if (Number.isNaN(parsed.getTime())) {
                return date;
            }

            const weekday = ['日', '月', '火', '水', '木', '金', '土'][parsed.getDay()];
            return `${date.replace(/-/g, '.')}（${weekday}）`;
        },

        init() {
            this.slots = (Array.isArray(this.slots) ? this.slots : [])
                .map((slot) => this.normalizeSlotRecord(slot));
            this.nextId = Math.max(...this.slots.map(slot => slot.id), 0) + 1;
            this.modalTimeRows = this.createDefaultModalTimeRows();
            this.modalSelectedDates = [];
        },

        addManualSlot() {
            const newSlot = this.normalizeSlotRecord({
                end_hour: '11',
            });
            this.slots.push(newSlot);
            this.openDateGroup(newSlot.date);
        },

        removeSlot(id) {
            this.slots = this.slots.filter((slot) => Number(slot.id) !== Number(id));
        },

        openBuilder() {
            this.modalOpen = true;
            this.modalNextTimeRowId = 1;
            this.modalTimeRows = this.createDefaultModalTimeRows();
            this.$nextTick(() => this.initModalCalendar());
        },

        closeBuilder() {
            this.modalOpen = false;
        },

        initModalCalendar() {
            if (!(window.jQuery && jQuery.fn && jQuery.fn.datepicker) || !this.$refs.modalCalendar) {
                return;
            }

            const selectedDateSet = new Set(this.modalSelectedDates);
            const $calendar = jQuery(this.$refs.modalCalendar);
            $calendar.datepicker('destroy');
            $calendar.datepicker({
                dateFormat: 'yy-mm-dd',
                defaultDate: 0,
                changeMonth: true,
                numberOfMonths: 2,
                beforeShowDay: (date) => {
                    const dateKey = jQuery.datepicker.formatDate('yy-mm-dd', date);
                    const classes = selectedDateSet.has(dateKey) ? 'reservation-slot-selected' : '';
                    return [true, classes];
                },
                onSelect: (dateText) => {
                    if (selectedDateSet.has(dateText)) {
                        selectedDateSet.delete(dateText);
                    } else {
                        selectedDateSet.add(dateText);
                    }

                    this.modalSelectedDates = [...selectedDateSet].sort((a, b) => a.localeCompare(b));
                    window.setTimeout(() => {
                        $calendar.datepicker('refresh');
                    }, 0);
                },
            });
            this.modalCalendarInitialized = true;
        },

        addModalTimeRow() {
            const last = this.modalTimeRows[this.modalTimeRows.length - 1] || null;
            const lastEnd = last ? Number.parseInt(this.normalizeHour(last.end_hour, '11'), 10) : 11;
            const nextStartHourNumber = Math.min(17, Number.isNaN(lastEnd) ? 10 : lastEnd);
            const nextStartHour = String(nextStartHourNumber).padStart(2, '0');
            const nextEndHourNumber = Math.min(18, nextStartHourNumber + 1);

            this.modalTimeRows.push({
                id: this.modalNextTimeRowId++,
                start_hour: nextStartHour,
                start_minute: '00',
                end_hour: String(nextEndHourNumber).padStart(2, '0'),
                end_minute: '00',
                capacity: 1,
            });
        },

        removeModalTimeRow(id) {
            this.modalTimeRows = this.modalTimeRows.filter((row) => Number(row.id) !== Number(id));
            if (this.modalTimeRows.length === 0) {
                this.modalTimeRows = this.createDefaultModalTimeRows();
            }
        },

        applyBuilderSlots() {
            const selectedDates = [...new Set(this.modalSelectedDates)]
                .filter((date) => typeof date === 'string' && date.trim() !== '')
                .sort((a, b) => a.localeCompare(b));

            if (selectedDates.length === 0) {
                window.alert('日付を1日以上選択してください。');
                return;
            }

            const validRows = (Array.isArray(this.modalTimeRows) ? this.modalTimeRows : [])
                .map((row) => ({
                    start_hour: this.normalizeHour(row.start_hour, '10'),
                    start_minute: this.normalizeMinute(row.start_minute, '00'),
                    end_hour: this.normalizeHour(row.end_hour, '11'),
                    end_minute: this.normalizeMinute(row.end_minute, '00'),
                    capacity: this.normalizeCapacity(row.capacity, 1),
                }))
                .filter((row) => this.isValidTimeRange(row.start_hour, row.start_minute, row.end_hour, row.end_minute));

            if (validRows.length === 0) {
                window.alert('有効な時間枠を1件以上入力してください。');
                return;
            }

            const existingKeys = new Set(this.slots.map((slot) => `${slot.date}|${slot.start_hour}:${slot.start_minute}|${slot.end_hour}:${slot.end_minute}`));
            const generatedSlots = [];

            selectedDates.forEach((date) => {
                validRows.forEach((row) => {
                    const key = `${date}|${row.start_hour}:${row.start_minute}|${row.end_hour}:${row.end_minute}`;
                    if (existingKeys.has(key)) {
                        return;
                    }

                    generatedSlots.push(this.createSlotPair(date, row));

                    existingKeys.add(key);
                });
            });

            if (generatedSlots.length > 0) {
                this.slots = [...this.slots, ...generatedSlots].map((slot) => ({
                    ...slot,
                    start_hour: this.normalizeHour(slot.start_hour, '10'),
                    start_minute: this.normalizeMinute(slot.start_minute, '00'),
                    end_hour: this.normalizeHour(slot.end_hour, '11'),
                    end_minute: this.normalizeMinute(slot.end_minute, '00'),
                    capacity: this.normalizeCapacity(slot.capacity, 1),
                }));
                generatedSlots.forEach((slot) => this.openDateGroup(slot.date));
            }

            this.closeBuilder();
        },

        destroy() {
            if (window.jQuery && jQuery.fn && jQuery.fn.datepicker && this.$refs.modalCalendar) {
                jQuery(this.$refs.modalCalendar).datepicker('destroy');
            }
        },
    };
}
</script>
@endonce
