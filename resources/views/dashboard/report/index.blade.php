<x-app-layout>
    <x-slot:title>レポート</x-slot:title>
    <x-slot:page>report</x-slot:page>
    <x-slot:name>{{ $user->name }}</x-slot:name>
    <x-slot:role>{{ $user->role }}</x-slot:role>
    <x-slot:login>{{ $user->last_login_at?->format('Y.m.d H:i') }}</x-slot:login>

    <div class="content report-page">
        <div class="report-shell">

            {{-- ヘッダー --}}
            <div class="report-head">
                <h2>レポート</h2>
                <p>期間を指定して閲覧数・予約数を確認できます。</p>
            </div>

            {{-- 期間フィルター --}}
            <form method="GET" action="{{ route('report.index') }}" class="report-filter">
                <div class="report-filter-inner">
                    <div class="report-filter-group">
                        <label for="report-from">開始日</label>
                        <input type="date" id="report-from" name="from" value="{{ $filters['from'] }}">
                    </div>
                    <span class="report-filter-sep">〜</span>
                    <div class="report-filter-group">
                        <label for="report-to">終了日</label>
                        <input type="date" id="report-to" name="to" value="{{ $filters['to'] }}">
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
                        <p class="report-summary-value">{{ number_format(array_sum($daily['views'])) }}</p>
                    </div>
                </div>
                <div class="report-summary-card">
                    <span class="material-symbols-outlined">event_available</span>
                    <div>
                        <p class="report-summary-label">期間合計 予約数</p>
                        <p class="report-summary-value">{{ number_format(array_sum($daily['reservations'])) }}</p>
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
                <h3 class="report-section-title">記事別集計</h3>
                @if ($articles->isEmpty())
                    <p class="report-empty">対象期間のデータがありません。</p>
                @else
                    <div class="report-table-scroll">
                        <table class="report-table">
                            <thead>
                                <tr>
                                    <th scope="col">記事タイトル</th>
                                    <th scope="col">閲覧数</th>
                                    <th scope="col">予約数</th>
                                </tr>
                            </thead>
                            <tbody>
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
                @endif
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
            const labels = @json($daily['labels']);
            const views = @json($daily['views']);
            const reservations = @json($daily['reservations']);

            new Chart(document.getElementById('reportChart'), {
            type: 'bar',
            data: {
                labels,
                datasets: [
                    {
                        label: '閲覧数',
                        data: views,
                        backgroundColor: 'rgba(59, 130, 246, 0.5)',
                        borderColor: 'rgba(59, 130, 246, 0.9)',
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
                        tension: 0.3,
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
        });
    </script>
    @endpush
</x-app-layout>
