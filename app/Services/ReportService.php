<?php

namespace App\Services;

use App\Models\Article;
use App\Models\PageView;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * 日別の閲覧数・予約数を集計する
     *
     * @param  Carbon  $from
     * @param  Carbon  $to
     * @param  int|null  $venueId
     * @param  int|null  $articleId
     * @return array{ labels: array, views: array, reservations: array }
     */
    public function dailySummary(Carbon $from, Carbon $to, ?int $venueId = null, ?int $articleId = null): array
    {
        $viewQuery = PageView::whereBetween('date', [$from->toDateString(), $to->toDateString()]);
        $reservationQuery = Reservation::whereBetween('created_at', [$from->startOfDay(), $to->copy()->endOfDay()]);

        if ($venueId) {
            $viewQuery->whereIn('article_id', Article::where('venue_id', $venueId)->pluck('id'));
            $reservationQuery->whereIn('article_id', Article::where('venue_id', $venueId)->pluck('id'));
        }

        if ($articleId) {
            $viewQuery->where('article_id', $articleId);
            $reservationQuery->where('article_id', $articleId);
        }

        $views = $viewQuery
            ->select('date', DB::raw('SUM(view_count) as total'))
            ->groupBy('date')
            ->pluck('total', 'date');

        $reservations = $reservationQuery
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as total'))
            ->groupBy('date')
            ->pluck('total', 'date');

        $labels = [];
        $viewData = [];
        $reservationData = [];

        $cursor = $from->copy()->startOfDay();
        $end = $to->copy()->startOfDay();

        while ($cursor->lte($end)) {
            $key = $cursor->toDateString();
            $labels[] = $cursor->format('m/d');
            $viewData[] = (int) ($views[$key] ?? 0);
            $reservationData[] = (int) ($reservations[$key] ?? 0);
            $cursor->addDay();
        }

        return [
            'labels' => $labels,
            'views' => $viewData,
            'reservations' => $reservationData,
        ];
    }

    /**
     * 記事（ページ）別の閲覧数・予約数を集計する
     *
     * @param  Carbon  $from
     * @param  Carbon  $to
     * @param  int|null  $venueId
     * @param  int|null  $articleId
     * @return Collection
     */
    public function articleSummary(Carbon $from, Carbon $to, ?int $venueId = null, ?int $articleId = null): Collection
    {
        $viewQuery = PageView::whereBetween('date', [$from->toDateString(), $to->toDateString()]);
        $reservationQuery = Reservation::whereBetween('created_at', [$from->startOfDay(), $to->copy()->endOfDay()])
            ->whereNotNull('article_id');

        if ($venueId) {
            $viewQuery->whereIn('article_id', Article::where('venue_id', $venueId)->pluck('id'));
            $reservationQuery->whereIn('article_id', Article::where('venue_id', $venueId)->pluck('id'));
        }

        if ($articleId) {
            $viewQuery->where('article_id', $articleId);
            $reservationQuery->where('article_id', $articleId);
        }

        $views = $viewQuery
            ->select('article_id', DB::raw('SUM(view_count) as total_views'))
            ->groupBy('article_id')
            ->pluck('total_views', 'article_id');

        $reservations = $reservationQuery
            ->select('article_id', DB::raw('COUNT(*) as total_reservations'))
            ->groupBy('article_id')
            ->pluck('total_reservations', 'article_id');

        $articleIds = $views->keys()->merge($reservations->keys())->unique();

        $articles = Article::withTrashed()
            ->whereIn('id', $articleIds)
            ->when($venueId, fn($q) => $q->where('venue_id', $venueId))
            ->pluck('title', 'id');

        return $articleIds->map(function ($id) use ($views, $reservations, $articles) {
            return [
                'article_id' => $id,
                'title' => $articles[$id] ?? '（削除済み）',
                'total_views' => (int) ($views[$id] ?? 0),
                'total_reservations' => (int) ($reservations[$id] ?? 0),
            ];
        })->reject(function ($item) {
            return $item['title'] === '（削除済み）';
        })->sortByDesc('total_views')->values();
    }
}
