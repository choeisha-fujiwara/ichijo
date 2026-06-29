<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ArticleVenue;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(private ReportService $reportService) {}

    public function exportArticles(Request $request): StreamedResponse
    {
        [$from, $to, $venueId, $articleId] = $this->resolveFilters($request);
        $articles = $this->reportService->articleSummary($from, $to, $venueId, $articleId);
        $periodLabel = $from->toDateString() . '〜' . $to->toDateString();
        $articleIds = $articles->pluck('article_id')->filter()->values();
        $articleVenueMap = $articleIds->isEmpty()
            ? collect()
            : Article::query()->whereIn('id', $articleIds)->pluck('venue_id', 'id');
        $venueNameMap = $articleVenueMap->isEmpty()
            ? collect()
            : ArticleVenue::query()->whereIn('id', $articleVenueMap->filter()->unique()->values())->pluck('venue_name', 'id');

        $fileName = 'article-summary-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($articles, $periodLabel, $articleVenueMap, $venueNameMap): void {
            $stream = fopen('php://output', 'w');

            $this->writeCsvRow($stream, [
                '期間',
                '会場名',
                '記事タイトル',
                '閲覧数',
                '予約数',
            ]);

            foreach ($articles as $article) {
                $articleVenueId = $articleVenueMap[$article['article_id']] ?? null;
                $articleVenueName = $articleVenueId
                    ? (string) ($venueNameMap[$articleVenueId] ?? '（会場未設定）')
                    : '（会場未設定）';

                $this->writeCsvRow($stream, [
                    $periodLabel,
                    $articleVenueName,
                    (string) ($article['title'] ?? ''),
                    (int) ($article['total_views'] ?? 0),
                    (int) ($article['total_reservations'] ?? 0),
                ]);
            }

            fclose($stream);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=Shift_JIS',
        ]);
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        [$from, $to, $venueId, $articleId] = $this->resolveFilters($request);

        $daily = $this->reportService->dailySummary($from, $to, $venueId, $articleId);
        $articles = $this->reportService->articleSummary($from, $to, $venueId, $articleId);

        $venues = ArticleVenue::orderBy('venue_name')->get(['id', 'venue_name']);
        $periodArticles = $this->reportService->articleSummary($from, $to, null, null);
        $periodArticleIds = $periodArticles->pluck('article_id')->all();
        $periodArticleVenueMap = empty($periodArticleIds)
            ? collect()
            : Article::query()->whereIn('id', $periodArticleIds)->pluck('venue_id', 'id');

        $filterArticlesForJs = $periodArticles
            ->map(fn ($article) => [
                'id' => (int) $article['article_id'],
                'title' => (string) $article['title'],
                'venue_id' => (int) ($periodArticleVenueMap[$article['article_id']] ?? 0),
            ])
            ->values();

        if ($articleId && !$filterArticlesForJs->contains(fn ($article) => $article['id'] === (int) $articleId)) {
            $selectedArticle = Article::query()->find($articleId, ['id', 'title', 'venue_id']);

            if ($selectedArticle) {
                $filterArticlesForJs->prepend([
                    'id' => (int) $selectedArticle->id,
                    'title' => (string) $selectedArticle->title,
                    'venue_id' => (int) $selectedArticle->venue_id,
                ]);
            }
        }

        $filters = [
            'from'     => $from->toDateString(),
            'to'       => $to->toDateString(),
            'venue_id' => $venueId,
            'article_id' => $articleId,
        ];

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'daily' => $daily,
                'articles' => $articles->values(),
                'filters' => $filters,
                'filter_articles' => $filterArticlesForJs,
            ]);
        }

        return view('dashboard.report.index', compact('user', 'daily', 'articles', 'filters', 'venues', 'filterArticlesForJs'));
    }

    private function resolveFilters(Request $request): array
    {
        $validated = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'venue_id' => ['nullable', 'integer', 'exists:article_venues,id'],
            'article_id' => ['nullable', 'integer', 'exists:articles,id'],
        ]);

        $from = isset($validated['from'])
            ? Carbon::parse($validated['from'])->startOfDay()
            : now()->subDays(29)->startOfDay();

        $to = isset($validated['to'])
            ? Carbon::parse($validated['to'])->startOfDay()
            : now()->startOfDay();

        $venueId = $validated['venue_id'] ?? null;
        $articleId = $validated['article_id'] ?? null;

        return [$from, $to, $venueId, $articleId];
    }

    private function writeCsvRow($stream, array $row): void
    {
        $temp = fopen('php://temp', 'r+');

        if ($temp === false) {
            return;
        }

        fputcsv($temp, $row);
        rewind($temp);
        $line = (string) stream_get_contents($temp);
        fclose($temp);

        fwrite($stream, mb_convert_encoding($line, 'SJIS-win', 'UTF-8'));
    }
}
