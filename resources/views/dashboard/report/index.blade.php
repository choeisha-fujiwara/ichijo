<x-app-layout>
    <x-slot:title>レポート</x-slot:title>
    <x-slot:page>report</x-slot:page>
    <x-slot:name>{{ $user->name }}</x-slot:name>
    <x-slot:role>{{ $user->role }}</x-slot:role>
    <x-slot:login>{{ $user->last_login_at?->format('Y.m.d H:i') }}</x-slot:login>

    <div class="content report-page">
        <div class="report-shell">
            <div class="report-loading" id="report-loading" aria-hidden="true">
                <div class="report-loading-spinner" aria-hidden="true"></div>
                <p class="report-loading-text">読み込み中...</p>
            </div>

            {{-- ヘッダー --}}
            <div class="report-head">
                <h2>レポート</h2>
                <p>期間を指定して閲覧数・予約数を確認できます。</p>
            </div>

            {{-- 期間フィルター --}}
            <form method="GET" action="{{ route('report.index') }}" class="report-filter" id="report-filter-form">
                <div class="report-filter-inner">
                    <div class="report-filter-group">
                        <label for="report-from">開始日</label>
                        <input type="date" id="report-from" name="from" value="{{ $filters['from'] }}">
                    </div>
                    <span class="report-filter-sep">-</span>
                    <div class="report-filter-group">
                        <label for="report-to">終了日</label>
                        <input type="date" id="report-to" name="to" value="{{ $filters['to'] }}">
                    </div>
                    <div class="report-filter-group">
                        <label for="report-venue">会場</label>
                        <select id="report-venue" name="venue_id">
                            <option value="">すべて</option>
                            @foreach ($venues as $venue)
                                <option value="{{ $venue->id }}" @selected($filters['venue_id'] == $venue->id)>
                                    {{ $venue->venue_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="report-filter-group select-article-group">
                        <label for="report-article">記事</label>
                        <select id="report-article" name="article_id">
                            <option value="">すべて</option>
                            @foreach ($filterArticlesForJs as $filterArticle)
                                <option value="{{ $filterArticle['id'] }}" @selected($filters['article_id'] == $filterArticle['id'])>
                                    {{ $filterArticle['title'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="report-filter-btn">絞り込む</button>
                </div>
            </form>

            {{-- サマリーカード --}}
            <div class="report-summary">
                <div class="report-summary-card">
                    <span class="material-symbols-outlined">visibility</span>
                    <div>
                        <p class="report-summary-label">期間合計 閲覧数</p>
                        <p class="report-summary-value" id="report-total-views">{{ number_format(array_sum($daily['views'])) }}</p>
                    </div>
                </div>
                <div class="report-summary-card">
                    <span class="material-symbols-outlined">event_available</span>
                    <div>
                        <p class="report-summary-label">期間合計 予約数</p>
                        <p class="report-summary-value" id="report-total-reservations">{{ number_format(array_sum($daily['reservations'])) }}</p>
                    </div>
                </div>
            </div>

            {{-- グラフ --}}
            <div class="report-chart-wrap">
                <h3 class="report-section-title">日別推移</h3>
                <div class="report-chart-container">
                    <canvas id="reportChart"></canvas>
                </div>
            </div>

            {{-- ページ別テーブル --}}
            <div class="report-table-wrap">
                <div class="report-table-head">
                    <h3 class="report-section-title">記事別集計</h3>
                    <a
                        id="report-articles-export-link"
                        class="report-export-link"
                        href="{{ route('report.articles.export', array_filter($filters)) }}"
                    >
                        CSV出力
                    </a>
                </div>
                <p class="report-empty" id="report-empty" @if(!$articles->isEmpty()) style="display:none;" @endif>対象期間のデータがありません。</p>
                <div class="report-table-scroll" id="report-table-wrap" @if($articles->isEmpty()) style="display:none;" @endif>
                    <table class="report-table">
                        <thead>
                            <tr>
                                <th scope="col">記事タイトル</th>
                                <th scope="col">閲覧数</th>
                                <th scope="col">予約数</th>
                            </tr>
                        </thead>
                        <tbody id="report-articles-body">
                            @foreach ($articles as $row)
                                <tr>
                                    <td>{{ $row['title'] }}</td>
                                    <td class="report-table-num">{{ number_format($row['total_views']) }}</td>
                                    <td class="report-table-num">{{ number_format($row['total_reservations']) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
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

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const reportShell = document.querySelector('.report-shell');
            const filterForm = document.getElementById('report-filter-form');
            const fromInput = document.getElementById('report-from');
            const toInput = document.getElementById('report-to');
            const venueSelect = document.getElementById('report-venue');
            const articleSelect = document.getElementById('report-article');
            const exportLink = document.getElementById('report-articles-export-link');
            const totalViews = document.getElementById('report-total-views');
            const totalReservations = document.getElementById('report-total-reservations');
            const reportEmpty = document.getElementById('report-empty');
            const reportTableWrap = document.getElementById('report-table-wrap');
            const reportArticlesBody = document.getElementById('report-articles-body');
            const exportBaseUrl = "{{ route('report.articles.export') }}";
            let allArticles = @json($filterArticlesForJs);
            let isLoading = false;

            const setLoading = (loading) => {
                if (!reportShell) {
                    return;
                }

                reportShell.classList.toggle('is-loading', loading);
                reportShell.setAttribute('aria-busy', loading ? 'true' : 'false');
            };

            const renderArticleOptions = (preferredArticleId = null) => {
                if (!venueSelect || !articleSelect) {
                    return;
                }

                const selectedVenueId = String(venueSelect.value || '');
                const previousArticleId = preferredArticleId === null
                    ? String(articleSelect.value || '')
                    : String(preferredArticleId || '');

                articleSelect.innerHTML = '';

                const defaultOption = document.createElement('option');
                defaultOption.value = '';
                defaultOption.textContent = 'すべて';
                articleSelect.appendChild(defaultOption);

                const filteredArticles = allArticles.filter((article) => {
                    return selectedVenueId === '' || String(article.venue_id) === selectedVenueId;
                });

                filteredArticles.forEach((article) => {
                    const option = document.createElement('option');
                    option.value = String(article.id);
                    option.textContent = article.title;
                    articleSelect.appendChild(option);
                });

                const hasPreviousSelection = filteredArticles.some((article) => String(article.id) === previousArticleId);
                articleSelect.value = hasPreviousSelection ? previousArticleId : '';
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

            const labels = @json($daily['labels']);
            const views = @json($daily['views']);
            const reservations = @json($daily['reservations']);

            const reportChart = new Chart(document.getElementById('reportChart'), {
            type: 'bar',
            data: {
                labels,
                datasets: [
                    {
                        label: '閲覧数',
                        data: views,
                        backgroundColor: 'rgba(37, 99, 235, 0.65)',
                        borderColor: 'rgba(29, 78, 216, 1)',
                        borderWidth: 1,
                        yAxisID: 'yViews',
                    },
                    {
                        label: '予約数',
                        data: reservations,
                        type: 'line',
                        backgroundColor: 'rgba(16, 185, 129, 0.2)',
                        borderColor: 'rgba(16, 185, 129, 1)',
                        borderWidth: 2,
                        pointRadius: 3,
                        tension: 0,
                        yAxisID: 'yReservations',
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { position: 'top' },
                    tooltip: { mode: 'index' },
                },
                scales: {
                    x: {
                        grid: { display: false },
                    },
                    yViews: {
                        type: 'linear',
                        position: 'left',
                        beginAtZero: true,
                        ticks: { stepSize: 1 },
                        title: { display: true, text: '閲覧数' },
                    },
                    yReservations: {
                        type: 'linear',
                        position: 'right',
                        beginAtZero: true,
                        ticks: { stepSize: 1 },
                        grid: { drawOnChartArea: false },
                        title: { display: true, text: '予約数' },
                    },
                },
            },
        });

            const formatNumber = (value) => Number(value || 0).toLocaleString('ja-JP');

            const renderArticles = (rows) => {
                if (!reportArticlesBody || !reportEmpty || !reportTableWrap) {
                    return;
                }

                reportArticlesBody.innerHTML = '';

                if (!rows || rows.length === 0) {
                    reportEmpty.style.display = '';
                    reportTableWrap.style.display = 'none';
                    return;
                }

                reportEmpty.style.display = 'none';
                reportTableWrap.style.display = '';

                rows.forEach((row) => {
                    const tr = document.createElement('tr');

                    const titleTd = document.createElement('td');
                    titleTd.textContent = row.title;

                    const viewsTd = document.createElement('td');
                    viewsTd.className = 'report-table-num';
                    viewsTd.textContent = formatNumber(row.total_views);

                    const reservationsTd = document.createElement('td');
                    reservationsTd.className = 'report-table-num';
                    reservationsTd.textContent = formatNumber(row.total_reservations);

                    tr.appendChild(titleTd);
                    tr.appendChild(viewsTd);
                    tr.appendChild(reservationsTd);
                    reportArticlesBody.appendChild(tr);
                });
            };

            const updateFromResponse = (payload) => {
                const nextDaily = payload.daily || { labels: [], views: [], reservations: [] };

                reportChart.data.labels = nextDaily.labels || [];
                reportChart.data.datasets[0].data = nextDaily.views || [];
                reportChart.data.datasets[1].data = nextDaily.reservations || [];
                reportChart.update();

                if (totalViews) {
                    totalViews.textContent = formatNumber((nextDaily.views || []).reduce((sum, value) => sum + Number(value || 0), 0));
                }

                if (totalReservations) {
                    totalReservations.textContent = formatNumber((nextDaily.reservations || []).reduce((sum, value) => sum + Number(value || 0), 0));
                }

                renderArticles(payload.articles || []);
            };

            const fetchReportData = async () => {
                if (!filterForm || isLoading) {
                    return;
                }

                isLoading = true;
                setLoading(true);

                try {
                    const params = new URLSearchParams(new FormData(filterForm));
                    const url = `${filterForm.action}?${params.toString()}`;
                    const response = await fetch(url, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });

                    if (!response.ok) {
                        return;
                    }

                    const payload = await response.json();
                    if (Array.isArray(payload.filter_articles)) {
                        allArticles = payload.filter_articles;
                        renderArticleOptions(payload.filters?.article_id ?? null);
                        updateExportLink();
                    }
                    updateFromResponse(payload);
                } catch (error) {
                    // Keep the existing view when async refresh fails.
                } finally {
                    isLoading = false;
                    setLoading(false);
                }
            };

            if (fromInput) {
                fromInput.addEventListener('change', function () {
                    updateExportLink();
                    fetchReportData();
                });
            }

            if (toInput) {
                toInput.addEventListener('change', function () {
                    updateExportLink();
                    fetchReportData();
                });
            }
        });
    </script>
    @endpush
</x-app-layout>
