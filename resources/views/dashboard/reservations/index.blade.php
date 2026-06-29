<x-app-layout>
    <x-slot:title>予約一覧</x-slot:title>
    <x-slot:page>reservation</x-slot:page>
    <x-slot:name>{{ $user->name }}</x-slot:name>
    <x-slot:role>{{ $user->role }}</x-slot:role>
    <x-slot:login>{{ $user->last_login_at?->format('Y.m.d H:i') }}</x-slot:login>
    <x-slot:old>{{ @$old }}</x-slot:old>

    @php
        $venues  = $venues  ?? collect();
        $articles = $articles ?? collect();
        $filters = $filters ?? [];
    @endphp
    <div class="content reservation-admin-page">
        <div class="reservation-admin-shell">
            {{-- <div class="reservation-loading" id="reservation-loading" aria-hidden="true">
                <div class="reservation-loading-spinner" aria-hidden="true"></div>
                <p class="reservation-loading-text">読み込み中...</p>
            </div> --}}

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
                        @if ($user->role !== 'staff' && $user->role !== 'manager')
                        <a
                            id="reservations-export-link"
                            href="{{ route('reservations.export', array_filter(array_merge(['sort' => $sort], $filters))) }}"
                            class="reservation-export-link"
                        >CSV出力</a>
                        @endif
                    </div>
                </div>

                <form method="GET" action="{{ route('reservations.index') }}" class="reservation-admin-filter" aria-label="予約絞り込み">
                    <input type="hidden" name="sort" value="{{ $sort }}">

                    <div class="reservation-filter-field reservation-filter-field-date">
                        <label for="filter-reserved-from">予約日</label>
                        <div class="reservation-filter-date-range">
                            <input
                                id="filter-reserved-from"
                                type="date"
                                name="reserved_from"
                                value="{{ $filters['reserved_from'] ?? '' }}"
                            >
                            <span>-</span>
                            <input
                                id="filter-reserved-to"
                                type="date"
                                name="reserved_to"
                                value="{{ $filters['reserved_to'] ?? '' }}"
                            >
                        </div>
                    </div>

                    @if ($user->role !== 'staff' && $user->role !== 'manager')
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
                    @endif

                    <div class="reservation-filter-field">
                        <label for="filter-article-id">記事</label>
                        <select id="filter-article-id" name="article_id">
                            <option value="">すべての記事</option>
                            @foreach ($articles as $article)
                                <option value="{{ $article->id }}" @selected((string) ($filters['article_id'] ?? '') === (string) $article->id)>
                                    {{ $article->title }}
                                </option>
                            @endforeach
                        </select>
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

            <div id="reservation-admin-results">
                @include('dashboard.reservations.partials.list', ['user' => $user, 'reservations' => $reservations])
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

    @php
        $articlesForJs = $articles->map(function ($article) {
            return [
                'id' => $article->id,
                'title' => $article->title,
                'venue_id' => $article->venue_id,
            ];
        })->values();
    @endphp

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const reservationShell = document.querySelector('.reservation-admin-shell');
            const filterForm = document.querySelector('.reservation-admin-filter');
            const venueSelect = document.getElementById('filter-venue-id');
            const articleSelect = document.getElementById('filter-article-id');
            const reservedFromInput = document.getElementById('filter-reserved-from');
            const reservedToInput = document.getElementById('filter-reserved-to');
            const resultsContainer = document.getElementById('reservation-admin-results');
            const exportLink = document.getElementById('reservations-export-link');
            const exportBaseUrl = "{{ route('reservations.export') }}";
            let allArticles = @json($articlesForJs);
            let debounceTimer = null;
            let activeController = null;
            let latestRequestId = 0;

            const setLoading = (loading) => {
                if (!reservationShell) {
                    return;
                }

                reservationShell.classList.toggle('is-loading', loading);
                reservationShell.setAttribute('aria-busy', loading ? 'true' : 'false');
            };

            const renderArticleOptions = () => {
                if (!articleSelect) {
                    return;
                }

                const selectedVenueId = venueSelect ? String(venueSelect.value || '') : '';
                const currentArticleId = String(articleSelect.value || '');

                const filteredArticles = allArticles.filter((article) => {
                    return selectedVenueId === '' || String(article.venue_id) === selectedVenueId;
                });

                articleSelect.innerHTML = '';

                const defaultOption = document.createElement('option');
                defaultOption.value = '';
                defaultOption.textContent = filteredArticles.length > 0 ? 'すべての記事' : '該当記事なし';
                articleSelect.appendChild(defaultOption);

                filteredArticles.forEach((article) => {
                    const option = document.createElement('option');
                    option.value = String(article.id);
                    option.textContent = article.title;
                    articleSelect.appendChild(option);
                });

                const isCurrentArticleAvailable = filteredArticles.some((article) => String(article.id) === currentArticleId);
                articleSelect.value = isCurrentArticleAvailable ? currentArticleId : '';
                articleSelect.disabled = filteredArticles.length === 0;
            };

            const renderVenueOptions = (venues) => {
                if (!venueSelect) {
                    return;
                }

                const currentVenueId = String(venueSelect.value || '');

                venueSelect.innerHTML = '';

                const defaultOption = document.createElement('option');
                defaultOption.value = '';
                defaultOption.textContent = venues.length > 0 ? 'すべての会場' : '該当会場なし';
                venueSelect.appendChild(defaultOption);

                venues.forEach((venue) => {
                    const option = document.createElement('option');
                    option.value = String(venue.id);
                    option.textContent = venue.venue_name;
                    venueSelect.appendChild(option);
                });

                const isCurrentVenueAvailable = venues.some((venue) => String(venue.id) === currentVenueId);
                venueSelect.value = isCurrentVenueAvailable ? currentVenueId : '';
                venueSelect.disabled = venues.length === 0;
            };

            const fetchFilteredResults = async () => {
                if (!filterForm || !reservedFromInput || !reservedToInput || !resultsContainer) {
                    return;
                }

                const reservedFrom = String(reservedFromInput.value || '').trim();
                const reservedTo = String(reservedToInput.value || '').trim();

                if (reservedFrom === '' || reservedTo === '') {
                    return;
                }

                const params = new URLSearchParams(new FormData(filterForm));
                params.set('async', '1');
                params.delete('async');
                const requestId = latestRequestId + 1;
                latestRequestId = requestId;

                if (activeController) {
                    activeController.abort();
                }

                activeController = new AbortController();
                setLoading(true);

                try {
                    const response = await fetch(`${filterForm.action}?${params.toString()}`, {
                        signal: activeController.signal,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                    });

                    if (requestId !== latestRequestId) {
                        return;
                    }

                    if (!response.ok) {
                        return;
                    }

                    const payload = await response.json();

                    if (typeof payload.results_html === 'string') {
                        resultsContainer.innerHTML = payload.results_html;
                    }

                    if (Array.isArray(payload.venues)) {
                        renderVenueOptions(payload.venues);
                    }

                    if (Array.isArray(payload.articles)) {
                        allArticles = payload.articles;
                        renderArticleOptions();
                    }

                    updateExportLink();
                } catch (error) {
                    if (error && error.name === 'AbortError') {
                        return;
                    }

                    // Keep current UI when async filtering fails.
                } finally {
                    if (requestId === latestRequestId) {
                        setLoading(false);
                    }
                }
            };

            const scheduleFetchFilteredResults = () => {
                if (debounceTimer) {
                    window.clearTimeout(debounceTimer);
                }

                debounceTimer = window.setTimeout(fetchFilteredResults, 320);
            };

            const updateExportLink = () => {
                if (!filterForm || !exportLink) {
                    return;
                }

                const params = new URLSearchParams(new FormData(filterForm));
                exportLink.href = `${exportBaseUrl}?${params.toString()}`;
            };

            renderArticleOptions();
            updateExportLink();

            if (venueSelect) {
                venueSelect.addEventListener('change', function () {
                    renderArticleOptions();
                    updateExportLink();
                });
            }

            if (articleSelect) {
                articleSelect.addEventListener('change', updateExportLink);
            }

            if (reservedFromInput) {
                reservedFromInput.addEventListener('change', scheduleFetchFilteredResults);
                reservedFromInput.addEventListener('change', updateExportLink);
            }

            if (reservedToInput) {
                reservedToInput.addEventListener('change', scheduleFetchFilteredResults);
                reservedToInput.addEventListener('change', updateExportLink);
            }
        });
    </script>
    @endpush
</x-app-layout>
