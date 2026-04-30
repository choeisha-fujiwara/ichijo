<x-guest-layout>
    @php
        $currentMonth = \Carbon\Carbon::now();
        $monthStart = $currentMonth->copy()->startOfMonth();
        $monthEnd = $currentMonth->copy()->endOfMonth();
        $calendarStart = $monthStart->copy()->startOfWeek(\Carbon\Carbon::SUNDAY);
        $calendarEnd = $monthEnd->copy()->endOfWeek(\Carbon\Carbon::SATURDAY);

        $reservationByDate = $reservation
            ->sortBy(fn ($slot) => sprintf('%s %s', (string) $slot->date, (string) $slot->start_time))
            ->groupBy(fn ($slot) => \Carbon\Carbon::parse($slot->date)->format('Y-m-d'));

        $reservationPayload = $reservationByDate->map(function ($slots, $date) {
            return [
                'date' => $date,
                'label' => \Carbon\Carbon::parse($date)->format('n月j日'),
                'total_capacity' => (int) $slots->sum(fn ($s) => max(0, $s->capacity - $s->reserved_count)),
                'slots' => $slots->values()->map(function ($slot) {
                    return [
                        'id' => $slot->id,
                        'date' => \Carbon\Carbon::parse($slot->date)->format('Y-m-d'),
                        'date_label' => \Carbon\Carbon::parse($slot->date)->format('Y年n月j日'),
                        'start_time' => \Carbon\Carbon::parse((string) $slot->start_time)->format('H:i'),
                        'end_time' => \Carbon\Carbon::parse((string) $slot->end_time)->format('H:i'),
                        'capacity' => max(0, (int) $slot->capacity - (int) $slot->reserved_count),
                    ];
                })->all(),
            ];
        })->all();
    @endphp
    <div class="content">
        <section class="event-list article-stage">
            <div class="article-header">
                @php
                    $headerImage = $article->images->firstWhere('path', $article->header_image)
                        ?? $article->images->first(fn ($image) => str_contains((string) $image->path, '/header/'));
                @endphp
                @if ($headerImage)
                    <img src="{{ route('article.image', $headerImage) }}" alt="ヘッダー画像">
                @endif
            </div>
            {{-- <p class="article-stage-label">EVENT ARTICLE</p> --}}
            <p class="article-stage-date">{{ optional($article->published_at)->format('Y.m.d') ?: '未設定' }} - {{ optional($article->unpublished_at)->format('Y.m.d') ?: '未設定' }}</p>
            <h1>{{ $article->title }}</h1> 
            <div class="article-body">
                {!! $article->body !!}
            </div>
            <div class="article-freewords">
                @if ($article->freeword_1)
                    <p><span>日程</span>{{ $article->freeword_1 }}</p>
                @endif
                @if ($article->freeword_2)
                    <p><span>時間</span>{{ $article->freeword_2 }}</p>
                @endif
            </div>
            @php
                $bodyImageItems = collect($article->body_image ?? [])
                    ->values()
                    ->map(function ($path, $index) use ($article) {
                        $image = $article->images->firstWhere('path', $path);

                        if (!$image) {
                            return null;
                        }

                        return [
                            'image' => $image,
                            'caption' => $article->body_image_captions[$index] ?? null,
                        ];
                    })
                    ->filter();
            @endphp
            @if ($bodyImageItems->isNotEmpty())
                <div class="article-body-images">
                    @foreach ($bodyImageItems as $item)
                        <figure class="article-body-image-item">
                            <img src="{{ route('article.image', $item['image']) }}" alt="本文画像{{ $loop->iteration }}">
                            @if (!empty($item['caption']))
                                <figcaption class="article-body-image-caption">{{ $item['caption'] }}</figcaption>
                            @endif
                        </figure>
                    @endforeach
                </div>
            @endif
            @if ($article->venue)
                <section class="article-venue" aria-label="会場情報">
                    <h2>イベント会場</h2>
                    @if (!empty($article->venue->image))
                        <div class="article-venue-image">
                            <img src="{{ route('venue.image', $article->venue) }}" alt="{{ $article->venue->venue_name }} の画像">
                        </div>
                    @endif
                    <dl>
                        <div>
                            <dt>会場名</dt>
                            <dd>{{ $article->venue->venue_name }}</dd>
                        </div>
                        <div>
                            <dt>住所</dt>
                            <dd>{{ $article->venue->address }}</dd>
                        </div>
                        @if (!empty($article->venue->phone))
                            <div>
                                <dt>TEL</dt>
                                <dd>{{ $article->venue->phone }}</dd>
                            </div>
                        @endif
                        @if (!empty($article->venue->fax))
                            <div>
                                <dt>FAX</dt>
                                <dd>{{ $article->venue->fax }}</dd>
                            </div>
                        @endif
                        @if (!empty($article->venue->access))
                            <div>
                                <dt>アクセス</dt>
                                <dd>{{ $article->venue->access }}</dd>
                            </div>
                        @endif
                        @if (!empty($article->venue->map_url))
                            <div>
                                <dt>地図</dt>
                                <dd>
                                    <a href="{{ $article->venue->map_url }}" target="_blank" rel="noopener noreferrer"><span class="material-symbols-outlined">location_on</span>Google Map を開く</a>
                                </dd>
                            </div>
                        @endif
                    </dl>
                </section>
            @endif
        </section>
        <section id="reservation-form" class="reservation-form reservation-stage">
            <h2 class="reservation-form-title"><span class="material-symbols-outlined icon" aria-hidden="true">event</span><span class="title-text">イベントご予約フォーム</span></h2>
            @if ($errors->any())
                <div class="reservation-form-errors" role="alert" aria-live="polite">
                    <p>入力内容を確認してください。</p>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form action="{{ \Illuminate\Support\Facades\Route::has('reservation.store') ? route('reservation.store') : '#' }}" method="POST" class="h-adr">
                @csrf
                <span class="p-country-name" style="display:none;">Japan</span>
                <input type="hidden" name="article_id" value="{{ $article->id }}">
                <input type="hidden" name="reservation_slot_id" id="reservation_slot_id" value="{{ old('reservation_slot_id') }}">
                <input type="hidden" name="reservation_datetime" id="reservation_datetime" value="{{ old('reservation_datetime') }}">

                <div class="reservation-calendar-block" id="reservation-calendar" data-reservations='@json($reservationPayload)'>
                    <div class="reservation-calendar-nav">
                        <h3 class="reservation-calendar-title" id="reservation-calendar-title">{{ $currentMonth->format('Y年n月') }}</h3>
                        <button type="button" class="reservation-calendar-nav-btn" id="calendar-prev-month" aria-label="前の月">前月</button>
                        <button type="button" class="reservation-calendar-nav-btn" id="calendar-next-month" aria-label="次の月">次月</button>
                    </div>
                    <div class="reservation-calendar-weekdays">
                        <span>日</span>
                        <span>月</span>
                        <span>火</span>
                        <span>水</span>
                        <span>木</span>
                        <span>金</span>
                        <span>土</span>
                    </div>
                    <div class="reservation-calendar-grid" id="reservation-calendar-grid">
                        @for ($day = $calendarStart->copy(); $day->lte($calendarEnd); $day->addDay())
                            @php
                                $dateKey = $day->format('Y-m-d');
                                $isCurrentMonth = $day->isSameMonth($currentMonth);
                                $hasReservation = $reservationByDate->has($dateKey);
                                $dayCapacity = $hasReservation ? (int) $reservationByDate[$dateKey]->sum(fn ($s) => max(0, $s->capacity - $s->reserved_count)) : 0;
                                $isReservable = $hasReservation && $dayCapacity > 0;
                            @endphp
                            @if ($isReservable)
                                <button type="button" class="calendar-day is-clickable {{ $isCurrentMonth ? '' : 'is-outside' }}" data-date="{{ $dateKey }}">
                                    <span class="calendar-day-number">{{ $day->day }}</span>
                                    <span class="calendar-day-capacity">
                                        <span class="material-symbols-outlined icon">circle</span>
                                        <span>残{{ $dayCapacity }}枠</span>
                                    </span>
                                </button>
                            @elseif ($hasReservation)
                                <div class="calendar-day is-disabled {{ $isCurrentMonth ? '' : 'is-outside' }}" aria-disabled="true">
                                    <span class="calendar-day-number">{{ $day->day }}</span>
                                    <span class="calendar-day-capacity is-disabled">
                                        <span class="material-symbols-outlined icon">circle</span>
                                        <span>残0枠</span>
                                    </span>
                                </div>
                            @else
                                <div class="calendar-day {{ $isCurrentMonth ? '' : 'is-outside' }}">
                                    <span class="calendar-day-number">{{ $day->day }}</span>
                                </div>
                            @endif
                        @endfor
                    </div>
                    <p class="reservation-calendar-empty" id="reservation-calendar-empty" hidden>この月は予約枠がありません</p>
                </div>
                <div class="reservation-selected-slot-block input-item">
                    <p class="input-label single-item"><span class="required">必須</span><span>予約日時</span></p>
                    <p class="reservation-selected-slot" id="reservation-selected-slot">カレンダーから選択してください</p>
                </div> 
                <div class="input-item reservation-slot"></div>
                <div class="reservation-slot-modal" id="reservation-slot-modal" hidden>
                    <div class="reservation-slot-modal__backdrop" data-close-modal></div>
                    <div class="reservation-slot-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="reservation-slot-modal-title">
                        <div class="reservation-slot-modal__head">
                            <h4 id="reservation-slot-modal-title">予約枠を選択してください</h4>
                            <button type="button" class="reservation-slot-modal__close" data-close-modal>&times;</button>
                        </div>
                        <div class="reservation-slot-modal__body">
                            <p class="reservation-slot-modal__date" id="reservation-slot-modal-date"></p>
                            <div class="reservation-slot-list" id="reservation-slot-list"></div>
                        </div>
                    </div>
                </div>
                <div class="input-item name">
                    <p class="input-label"><span class="required">必須</span><span>お名前（漢字）</span></p>
                    <label for="first-name" class="sub-label">姓</label>
                    <input
                        type="text"
                        name="first_name"
                        id="first-name"
                        value="{{ old('first_name') }}"
                        placeholder="例）山田"
                        class=""
                        required
                    />
                    <label for="last-name" class="sub-label">名</label>
                    <input
                        type="text"
                        name="last_name"
                        id="last-name"
                        value="{{ old('last_name') }}"
                        placeholder="例）太郎"
                        class=""
                        required
                    />
                </div>
                <div class="input-item name">
                    <p class="input-label"><span class="required">必須</span><span>お名前（ひらがな）</span></p>
                    <label for="first-name-kana" class="sub-label">姓</label>
                    <input
                        type="text"
                        name="first_name_kana"
                        id="first-name-kana"
                        value="{{ old('first_name_kana') }}"
                        placeholder="例）やまだ"
                        class=""
                        required
                    />
                    <label for="last-name-kana" class="sub-label">名</label>
                    <input
                        type="text"
                        name="last_name_kana"
                        id="last-name-kana"
                        value="{{ old('last_name_kana') }}"
                        placeholder="例）たろう"
                        class=""
                        required
                    />
                </div>
                <div class="input-item address">
                    <p class="input-label"><span class="required">必須</span><span>ご住所</span></p>
                    <label for="postal-code-1" class="sub-label">郵便番号</label>
                    <div class="postal-code-box">
                        <p>〒</p>
                        <input
                            type="text"
                            name="postal_code_1"
                            id="postal-code-1"
                            aria-label="郵便番号（前半3桁）"
                            value="{{ old('postal_code_1') }}"
                            placeholder="例）123"
                            maxlength="3"
                            minlength="3"
                            class=""
                            required
                        />
                        <p>ー</p>
                        <input
                            type="text"
                            name="postal_code_2"
                            id="postal-code-2"
                            aria-label="郵便番号（後半4桁）"
                            value="{{ old('postal_code_2') }}"
                            placeholder="例）4567"
                            maxlength="4"
                            minlength="4"
                            class=""
                            required
                        />
                        <input type="text" class="p-postal-code" value="{{ old('postal_code_1', '') . old('postal_code_2', '') }}" readonly tabindex="-1" aria-hidden="true" style="display:none"/>
                    </div>
                    <label for="address-prefectures" class="sub-label">都道府県</label>
                    <input
                        type="text"
                        name="address_prefectures"
                        id="address-prefectures"
                        value="{{ old('address_prefectures') }}"
                        placeholder="例）新潟県"
                        class="p-region "
                        required
                    />
                    <label for="address-municipalities" class="sub-label">市区町村</label>
                    <input
                        type="text"
                        name="address_municipalities"
                        id="address-municipalities"
                        value="{{ old('address_municipalities') }}"
                        placeholder="例）新発田市大手町"
                        class="p-locality "
                        required
                    />
                    <label for="address-detail" class="sub-label">丁目・番地</label>
                    <input
                        type="text"
                        name="address_detail"
                        id="address-detail"
                        value="{{ old('address_detail') }}"
                        placeholder="例）6丁目4-80"
                        class="p-street-address "
                        required
                    />
                    <label for="address-building" class="sub-label">建物名</label>
                    <input
                        type="text"
                        name="address_building"
                        id="address-building"
                        value="{{ old('address_building') }}"
                        placeholder="例）新潟マンション101号室"
                        class="p-extended-address "
                    />
                </div>
                <div class="input-item phone">
                    <label for="phone-1" class="single-item"><span class="required">必須</span><span>お電話番号</span></label>
                    <div class="phone-number-box">
                        <input
                            type="tel"
                            name="phone-1"
                            id="phone-1"
                            aria-label="電話番号（市外局番または先頭）"
                            value="{{ old('phone-1') }}"
                            placeholder="例）090"
                            minlength="3"
                            maxlength="3"
                            class=""
                            required
                        />
                        <p>ー</p>
                        <input
                            type="tel"
                            name="phone-2"
                            id="phone-2"
                            aria-label="電話番号（中央）"
                            value="{{ old('phone-2') }}"
                            placeholder="例）1234"
                            minlength="4"
                            maxlength="4"
                            class=""
                            required
                        />
                        <p>ー</p>
                        <input
                            type="tel"
                            name="phone-3"
                            id="phone-3"
                            aria-label="電話番号（末尾）"
                            value="{{ old('phone-3') }}"
                            placeholder="例）5678"
                            minlength="4"
                            maxlength="4"
                            class=""
                            required
                        />
                    </div>
                </div>
                <div class="input-item email">
                    <label for="email" class="single-item"><span class="required">必須</span><span>メールアドレス</span></label>
                    <input
                        type="email"
                        name="email"
                        id="email"
                        value="{{ old('email') }}"
                        placeholder="例）example@example.com"
                        class=""
                        required
                    />
                </div>
                <div class="input-item email-confirmation">
                    <label for="email_confirmation" class="single-item"><span class="required">必須</span><span>メールアドレス（確認用）</span></label>
                    <input
                        type="email"
                        name="email_confirmation"
                        id="email_confirmation"
                        value="{{ old('email_confirmation') }}"
                        placeholder="例）example@example.com"
                        class=""
                        required
                    />
                </div>
                <div class="input-item memo">
                    <label for="memo" class="single-item"><span class="any">任意</span><span>ご要望・ご質問</span></label>
                    <textarea
                        name="memo"
                        id="memo"
                        rows="4"
                        placeholder="ご要望やご質問がある場合はご記入ください"
                    ></textarea>
                </div>
                <div class="input-item privacy-policy">
                    <p>■ 個人情報の取り扱いについて</p>
                    <div class="privacy-policy-content">
                        <p>・ご提供いただいた個人情報は、予約の管理およびご連絡のために使用いたします。</p>
                        <p>・個人情報は、適切な安全対策を講じて管理し、第三者に提供することはありません。</p>
                        <p>・ご予約の確認や変更、キャンセルのご連絡をする場合がありますので、正確な情報をご提供ください。</p>
                        <p>・ご予約の際に提供された情報は、予約の完了後、一定期間保存され、その後適切に削除されます。</p>
                        <p><a href="https://www.ichijo.co.jp/privacy/" target="_blank">プライバシーポリシーの詳細はこちら</a></p>
                    </div>
                </div>
                    <label for="privacy_policy" class="privacy-policy-agreement">
                        <input
                            type="checkbox"
                            name="privacy_policy"
                            id="privacy_policy"
                            class="privacy-policy-agreement__input"
                            @checked(old('privacy_policy'))
                            required
                        />
                        <span class="privacy-policy-agreement__check" aria-hidden="true">
                            <span class="material-symbols-outlined">check</span>
                        </span>
                        <span class="privacy-policy-agreement__text">プライバシーポリシーに同意する</span>
                    </label>
                <button type="submit" class="reservation-submit-btn" @disabled(!old('privacy_policy'))>予約する</button> 
            </form>
        </section>
    </div>
    <footer>
        <div class="footer">
            <small>ICHIJO Co.,Ltd.</small>
        </div>
    </footer>
    <script>
        (() => {
            const calendarRoot = document.getElementById('reservation-calendar');
            const modal = document.getElementById('reservation-slot-modal');
            const modalDate = document.getElementById('reservation-slot-modal-date');
            const slotList = document.getElementById('reservation-slot-list');
            const selectedLabel = document.getElementById('reservation-selected-slot');
            const selectedSlotBlock = document.querySelector('.reservation-selected-slot-block');
            const selectedSlotId = document.getElementById('reservation_slot_id');
            const selectedDatetime = document.getElementById('reservation_datetime');
            const postalCodeFirst = document.getElementById('postal-code-1');
            const postalCodeSecond = document.getElementById('postal-code-2');
            const postalCodeHidden = document.querySelector('.p-postal-code');
            const privacyAgreement = document.querySelector('.privacy-policy-agreement');
            const privacyCheckbox = document.getElementById('privacy_policy');
            const submitButton = document.querySelector('.reservation-submit-btn');
            const calendarTitle = document.getElementById('reservation-calendar-title');
            const calendarGrid = document.getElementById('reservation-calendar-grid');
            const prevMonthButton = document.getElementById('calendar-prev-month');
            const nextMonthButton = document.getElementById('calendar-next-month');
            const calendarEmpty = document.getElementById('reservation-calendar-empty');

            const syncPrivacyAgreementState = () => {
                if (!privacyCheckbox || !submitButton) {
                    return;
                }

                const isChecked = privacyCheckbox.checked;
                submitButton.disabled = !isChecked;

                if (privacyAgreement) {
                    privacyAgreement.classList.toggle('is-checked', isChecked);
                }
            };

            syncPrivacyAgreementState();

            if (privacyCheckbox) {
                privacyCheckbox.addEventListener('change', syncPrivacyAgreementState);
            }

            const syncPostalCodeValue = () => {
                if (!postalCodeHidden) {
                    return;
                }

                const first = postalCodeFirst?.value?.trim() ?? '';
                const second = postalCodeSecond?.value?.trim() ?? '';
                const mergedPostalCode = `${first}${second}`;

                postalCodeHidden.value = mergedPostalCode;
                postalCodeHidden.setAttribute('value', mergedPostalCode);
                postalCodeHidden.dispatchEvent(new Event('input', { bubbles: true }));
                postalCodeHidden.dispatchEvent(new Event('change', { bubbles: true }));
                postalCodeHidden.dispatchEvent(new KeyboardEvent('keyup', { bubbles: true }));
            };

            syncPostalCodeValue();

            if (postalCodeFirst) {
                postalCodeFirst.addEventListener('input', syncPostalCodeValue);
            }

            if (postalCodeSecond) {
                postalCodeSecond.addEventListener('input', syncPostalCodeValue);
            }

            if (!calendarRoot || !modal || !slotList || !calendarTitle || !calendarGrid || !calendarEmpty) return;

            const syncSelectedSlotBlockState = () => {
                if (!selectedSlotBlock) {
                    return;
                }

                const hasSelection = Boolean(selectedSlotId?.value) || Boolean(selectedDatetime?.value);
                selectedSlotBlock.classList.toggle('active', hasSelection);
            };

            const payload = JSON.parse(calendarRoot.dataset.reservations || '{}');
            let activeDate = null;
            const today = new Date();
            const currentMonthFloor = new Date(today.getFullYear(), today.getMonth(), 1);
            let activeMonth = new Date(currentMonthFloor);

            const formatMonthKey = (date) => {
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                return `${year}-${month}`;
            };

            const monthKeyToDate = (monthKey) => {
                const [year, month] = monthKey.split('-').map(Number);
                return new Date(year, month - 1, 1);
            };

            const currentMonthKey = formatMonthKey(currentMonthFloor);
            const availableMonthKeys = Array.from(
                new Set(
                    Object.keys(payload)
                        .map((dateKey) => dateKey.slice(0, 7))
                        .filter((monthKey) => monthKey >= currentMonthKey)
                )
            ).sort();

            const formatDateKey = (date) => {
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            };

            const formatMonthTitle = (date) => `${date.getFullYear()}年${date.getMonth() + 1}月`;

            const formatSlotLabel = (slot) => `${slot.date_label} ${slot.start_time}〜${slot.end_time}`;

            const getNextAvailableMonth = () => {
                const activeMonthKey = formatMonthKey(activeMonth);
                const nextMonthKey = availableMonthKeys.find((monthKey) => monthKey > activeMonthKey);
                return nextMonthKey ? monthKeyToDate(nextMonthKey) : null;
            };

            const getPreviousAvailableMonth = () => {
                const activeMonthKey = formatMonthKey(activeMonth);

                if (activeMonthKey <= currentMonthKey) {
                    return null;
                }

                const previousMonthKeys = availableMonthKeys.filter((monthKey) => monthKey < activeMonthKey);
                const previousMonthKey = previousMonthKeys.length > 0
                    ? previousMonthKeys[previousMonthKeys.length - 1]
                    : null;

                if (!previousMonthKey || previousMonthKey < currentMonthKey) {
                    return new Date(currentMonthFloor);
                }

                return monthKeyToDate(previousMonthKey);
            };

            const updateCalendarNav = () => {
                const hasPrevious = formatMonthKey(activeMonth) > currentMonthKey;
                const nextMonth = getNextAvailableMonth();

                if (prevMonthButton) {
                    prevMonthButton.disabled = !hasPrevious;
                    prevMonthButton.hidden = !hasPrevious;
                }

                if (nextMonthButton) {
                    nextMonthButton.disabled = !nextMonth;
                    nextMonthButton.hidden = !nextMonth;
                }
            };

            const getCalendarStart = (date) => {
                const monthStart = new Date(date.getFullYear(), date.getMonth(), 1);
                const start = new Date(monthStart);
                start.setDate(monthStart.getDate() - monthStart.getDay());
                return start;
            };

            const getCalendarEnd = (date) => {
                const monthEnd = new Date(date.getFullYear(), date.getMonth() + 1, 0);
                const end = new Date(monthEnd);
                end.setDate(monthEnd.getDate() + (6 - monthEnd.getDay()));
                return end;
            };

            const renderCalendar = () => {
                calendarTitle.textContent = formatMonthTitle(activeMonth);
                calendarGrid.innerHTML = '';
                const activeMonthKey = formatMonthKey(activeMonth);
                const hasMonthReservation = availableMonthKeys.includes(activeMonthKey);
                calendarEmpty.hidden = hasMonthReservation;

                const start = getCalendarStart(activeMonth);
                const end = getCalendarEnd(activeMonth);

                for (let day = new Date(start); day <= end; day.setDate(day.getDate() + 1)) {
                    const currentDay = new Date(day);
                    const dateKey = formatDateKey(currentDay);
                    const dayData = payload[dateKey];
                    const isCurrentMonth = currentDay.getMonth() === activeMonth.getMonth();
                    const hasReservation = !!dayData && Array.isArray(dayData.slots) && dayData.slots.length > 0;
                    const totalCapacity = hasReservation ? Number(dayData.total_capacity || 0) : 0;
                    const isReservable = hasReservation && totalCapacity > 0;

                    if (isReservable) {
                        const button = document.createElement('button');
                        button.type = 'button';
                        button.className = `calendar-day is-clickable ${isCurrentMonth ? '' : 'is-outside'}`.trim();
                        button.dataset.date = dateKey;
                        button.innerHTML = `
                            <span class="calendar-day-number">${currentDay.getDate()}</span>
                            <span class="calendar-day-capacity">
                                <span class="material-symbols-outlined icon">circle</span>
                                <span>残${totalCapacity}枠</span>
                            </span>
                        `;
                        button.addEventListener('click', () => openModal(dateKey));
                        calendarGrid.appendChild(button);
                    } else if (hasReservation) {
                        const div = document.createElement('div');
                        div.className = `calendar-day is-disabled ${isCurrentMonth ? '' : 'is-outside'}`.trim();
                        div.setAttribute('aria-disabled', 'true');
                        div.innerHTML = `
                            <span class="calendar-day-number">${currentDay.getDate()}</span>
                            <span class="calendar-day-capacity is-disabled">
                                <span class="material-symbols-outlined icon">circle</span>
                                <span>残0枠</span>
                            </span>
                        `;
                        calendarGrid.appendChild(div);
                    } else {
                        const div = document.createElement('div');
                        div.className = `calendar-day ${isCurrentMonth ? '' : 'is-outside'}`.trim();
                        div.innerHTML = `<span class="calendar-day-number">${currentDay.getDate()}</span>`;
                        calendarGrid.appendChild(div);
                    }
                }

                updateCalendarNav();
            };

            const closeModal = () => {
                modal.hidden = true;
                modal.classList.remove('is-open');
                activeDate = null;
            };

            const openModal = (dateKey) => {
                const dayData = payload[dateKey];
                if (!dayData || !Array.isArray(dayData.slots) || dayData.slots.length === 0) {
                    return;
                }

                activeDate = dateKey;
                modalDate.textContent = `${dayData.label} の予約枠`;
                slotList.innerHTML = '';

                dayData.slots.forEach((slot) => {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'reservation-slot-option';
                    button.textContent = formatSlotLabel(slot);
                    button.dataset.slotId = String(slot.id);
                    button.dataset.datetime = `${slot.date} ${slot.start_time}`;

                    button.addEventListener('click', () => {
                        selectedSlotId.value = String(slot.id);
                        selectedDatetime.value = `${slot.date} ${slot.start_time}`;
                        selectedLabel.textContent = `選択中: ${formatSlotLabel(slot)}`;
                        syncSelectedSlotBlockState();
                        closeModal();
                    });

                    slotList.appendChild(button);
                });

                modal.hidden = false;
                modal.classList.add('is-open');
            };

            if (prevMonthButton) {
                prevMonthButton.addEventListener('click', () => {
                    const previousMonth = getPreviousAvailableMonth();
                    if (!previousMonth) {
                        return;
                    }

                    activeMonth = previousMonth;
                    renderCalendar();
                });
            }

            if (nextMonthButton) {
                nextMonthButton.addEventListener('click', () => {
                    const nextMonth = getNextAvailableMonth();
                    if (!nextMonth) {
                        return;
                    }

                    activeMonth = nextMonth;
                    renderCalendar();
                });
            }

            renderCalendar();
            syncSelectedSlotBlockState();

            modal.querySelectorAll('[data-close-modal]').forEach((el) => {
                el.addEventListener('click', closeModal);
            });

            window.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && activeDate) {
                    closeModal();
                }
            });
        })();
    </script>
</x-guest-layout>