<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ArticleVenue;
use App\Models\Reservation;
use App\Models\ReservationAccessLog;
use App\Models\ReservationSlot;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReservationAdminController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $sort = (string) $request->query('sort', 'created_desc');

        $validated = $request->validate([
            'venue_id'      => ['nullable', 'integer', 'exists:article_venues,id'],
            'article_id'    => ['nullable', 'integer', 'exists:articles,id'],
            'reserved_from' => ['nullable', 'date'],
            'reserved_to'   => ['nullable', 'date', 'after_or_equal:reserved_from'],
        ]);

        $venueId      = $validated['venue_id'] ?? null;
        $articleId    = $validated['article_id'] ?? null;
        $reservedFrom = $validated['reserved_from'] ?? null;
        $reservedTo   = $validated['reserved_to'] ?? null;

        $applyRoleScope = function ($query) use ($user): void {
            if (in_array($user->role, ['staff', 'manager']) && !empty($user->affiliation)) {
                $query->whereHas('article.venue', fn ($q) => $q->where('venue_name', $user->affiliation));
            }

            if ($user->role === 'staff') {
                $today = now()->toDateString();
                $thirtyDaysLater = now()->addDays(30)->toDateString();
                $query->whereDate('reservation_datetime', '>=', $today)
                    ->whereDate('reservation_datetime', '<=', $thirtyDaysLater);
            }
        };

        $query = Reservation::query()->with([
            'article:id,title,venue_id',
            'article.venue:id,venue_name',
            'reservationSlot:id,article_id,date,start_time,end_time',
        ]);

        $applyRoleScope($query);

        if (!empty($venueId)) {
            $query->whereHas('article', fn ($q) => $q->where('venue_id', $venueId));
        }

        if (!empty($articleId)) {
            $query->where('article_id', $articleId);
        }

        if (!empty($reservedFrom)) {
            $query->whereDate('reservation_datetime', '>=', $reservedFrom);
        }

        if (!empty($reservedTo)) {
            $query->whereDate('reservation_datetime', '<=', $reservedTo);
        }

        $this->applySort($query, $sort);

        $reservations = $query
            ->paginate(20)
            ->withQueryString();

        $hasDateRange = !empty($reservedFrom) && !empty($reservedTo);

        if ($hasDateRange) {
            $optionReservationQuery = Reservation::query();
            $applyRoleScope($optionReservationQuery);
            $optionReservationQuery
                ->whereDate('reservation_datetime', '>=', $reservedFrom)
                ->whereDate('reservation_datetime', '<=', $reservedTo);

            $availableArticleIds = (clone $optionReservationQuery)
                ->whereNotNull('article_id')
                ->distinct()
                ->pluck('article_id')
                ->filter()
                ->values();

            $availableVenueIds = Article::query()
                ->whereIn('id', $availableArticleIds)
                ->whereNotNull('venue_id')
                ->distinct()
                ->pluck('venue_id')
                ->filter()
                ->values();

            $venues = ArticleVenue::query()
                ->whereIn('id', $availableVenueIds)
                ->orderBy('venue_name')
                ->get(['id', 'venue_name']);

            $articlesQuery = Article::query()
                ->whereIn('id', $availableArticleIds)
                ->orderBy('title');
        } else {
            $venues = ArticleVenue::orderBy('venue_name')->get(['id', 'venue_name']);
            $articlesQuery = Article::query()->orderBy('title');

            if (in_array($user->role, ['staff', 'manager']) && !empty($user->affiliation)) {
                $articlesQuery->whereHas('venue', fn ($q) => $q->where('venue_name', $user->affiliation));
            }
        }

        if (!empty($venueId)) {
            $articlesQuery->where('venue_id', $venueId);
        }

        $articles = $articlesQuery->get(['id', 'title', 'venue_id']);

        if (!empty($articleId) && !$articles->contains('id', (int) $articleId)) {
            $selectedArticle = Article::query()->find($articleId, ['id', 'title']);
            if ($selectedArticle) {
                $articles->prepend($selectedArticle);
            }
        }

        $filters = [
            'venue_id'      => $venueId,
            'article_id'    => $articleId,
            'reserved_from' => $reservedFrom,
            'reserved_to'   => $reservedTo,
        ];

        if ($request->ajax() || $request->boolean('async')) {
            return response()->json([
                'results_html' => view('dashboard.reservations.partials.list', compact('user', 'reservations'))->render(),
                'venues' => $venues->map(fn ($venue) => [
                    'id' => $venue->id,
                    'venue_name' => $venue->venue_name,
                ])->values(),
                'articles' => $articles->map(fn ($article) => [
                    'id' => $article->id,
                    'title' => $article->title,
                    'venue_id' => $article->venue_id,
                ])->values(),
                'filters' => $filters,
            ]);
        }

        return view('dashboard.reservations.index', compact('user', 'reservations', 'sort', 'venues', 'articles', 'filters'));
    }

    public function show(Request $request, Reservation $reservation)
    {
        $user = auth()->user();

        if (in_array($user->role, ['staff', 'manager'])) {
            ReservationAccessLog::create([
                'reservation_id' => $reservation->id,
                'user_id'        => $user->id,
                'ip_address'     => (string) $request->ip(),
                'user_agent'     => (string) ($request->userAgent() ?? ''),
                'url'            => (string) $request->fullUrl(),
                'method'         => (string) $request->method(),
                'referer'        => (string) ($request->headers->get('referer') ?? ''),
                'accessed_at'    => now(),
            ]);
        }

        $reservation->load([
            'article:id,title',
            'reservationSlot:id,article_id,date,start_time,end_time,capacity,reserved_count',
        ]);

        [$statusLabel, $statusClass] = $this->resolveStatus($reservation);

        return view('dashboard.reservations.show', compact('user', 'reservation', 'statusLabel', 'statusClass'));
    }

    public function updateStaff(Request $request, Reservation $reservation)
    {
        $validated = $request->validate([
            'staff' => ['nullable', 'string', 'max:100'],
        ]);

        $reservation->update(['staff' => $validated['staff'] ?? null]);

        return redirect()->route('reservations.show', $reservation)
            ->with('msg', '担当者を保存しました。');
    }

    public function destroy(Reservation $reservation): RedirectResponse
    {
        $user = auth()->user();

        if (in_array($user->role, ['staff', 'manager'])) {
            return redirect()->route('reservations.index')
                ->with('msg', 'この操作は許可されていません。');
        }

        DB::transaction(function () use ($reservation): void {
            $targetReservation = Reservation::query()
                ->lockForUpdate()
                ->findOrFail($reservation->id);

            if ($targetReservation->reservation_slot_id) {
                $slot = ReservationSlot::withTrashed()
                    ->lockForUpdate()
                    ->find($targetReservation->reservation_slot_id);

                if ($slot && $slot->reserved_count > 0) {
                    $slot->decrement('reserved_count');
                }
            }

            $targetReservation->delete();
        });

        return redirect()->route('reservations.index')
            ->with('msg', '予約を削除しました。');
    }

    public function export(Request $request): StreamedResponse
    {
        $user = auth()->user();
        $sort = (string) $request->query('sort', 'created_desc');

        $validated = $request->validate([
            'venue_id'      => ['nullable', 'integer', 'exists:article_venues,id'],
            'article_id'    => ['nullable', 'integer', 'exists:articles,id'],
            'reserved_from' => ['nullable', 'date'],
            'reserved_to'   => ['nullable', 'date', 'after_or_equal:reserved_from'],
        ]);

        $venueId      = $validated['venue_id'] ?? null;
        $articleId    = $validated['article_id'] ?? null;
        $reservedFrom = $validated['reserved_from'] ?? null;
        $reservedTo   = $validated['reserved_to'] ?? null;

        $query = Reservation::query()->with([
            'article:id,title',
            'reservationSlot:id,article_id,date,start_time,end_time',
        ]);

        if (in_array($user->role, ['staff', 'manager']) && !empty($user->affiliation)) {
            $query->whereHas('article.venue', fn ($q) => $q->where('venue_name', $user->affiliation));
        }

        if ($user->role === 'staff') {
            $today = now()->toDateString();
            $thirtyDaysLater = now()->addDays(30)->toDateString();
            $query->whereDate('reservation_datetime', '>=', $today)
                ->whereDate('reservation_datetime', '<=', $thirtyDaysLater);
        }

        if (!empty($venueId)) {
            $query->whereHas('article', fn ($q) => $q->where('venue_id', $venueId));
        }

        if (!empty($articleId)) {
            $query->where('article_id', $articleId);
        }

        if (!empty($reservedFrom)) {
            $query->whereDate('reservation_datetime', '>=', $reservedFrom);
        }

        if (!empty($reservedTo)) {
            $query->whereDate('reservation_datetime', '<=', $reservedTo);
        }

        $this->applySort($query, $sort);

        $reservations = $query->get();

        $fileName = 'reservations-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($reservations): void {
            $stream = fopen('php://output', 'w');

            $this->writeCsvRow($stream, [
                'ID',
                '受付日時',
                '予約日時',
                'イベント名',
                '氏名',
                '氏名カナ',
                'メールアドレス',
                '電話番号',
                '住所',
                '備考',
            ]);

            foreach ($reservations as $reservation) {
                $this->writeCsvRow($stream, [
                    $reservation->id,
                    optional($reservation->created_at)->format('Y-m-d H:i'),
                    $this->formatReservationDateTime($reservation),
                    $reservation->article?->title ?? '（記事未設定）',
                    trim(($reservation->firstname ?? '') . ' ' . ($reservation->lastname ?? '')),
                    trim(($reservation->firstname_kana ?? '') . ' ' . ($reservation->lastname_kana ?? '')),
                    $reservation->email,
                    $reservation->phone,
                    trim(($reservation->prefecture ?? '') . ($reservation->city ?? '') . ($reservation->address ?? '') . ' ' . ($reservation->building ?? '')),
                    (string) ($reservation->memo ?? ''),
                ]);
            }

            fclose($stream);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=Shift_JIS',
        ]);
    }

    private function applySort($query, string $sort): void
    {
        if ($sort === 'created_asc') {
            $query->orderBy('created_at');
            return;
        }

        if ($sort === 'reserved_asc') {
            $query->orderByRaw('reservation_datetime IS NULL')
                ->orderBy('reservation_datetime')
                ->orderBy('created_at');
            return;
        }

        if ($sort === 'reserved_desc') {
            $query->orderByRaw('reservation_datetime IS NULL')
                ->orderByDesc('reservation_datetime')
                ->orderByDesc('created_at');
            return;
        }

        $query->orderByDesc('created_at');
    }

    private function resolveStatus(Reservation $reservation): array
    {
        $dateTime = $this->reservationDateTime($reservation);

        if ($dateTime === null) {
            return ['日時未設定', 'is-unscheduled'];
        }

        if ($dateTime->lt(now())) {
            return ['来場済み', 'is-finished'];
        }

        return ['予約済み', 'is-booked'];
    }

    private function reservationDateTime(Reservation $reservation): ?\Carbon\Carbon
    {
        if (!empty($reservation->reservation_datetime)) {
            try {
                return now()->parse($reservation->reservation_datetime);
            } catch (\Throwable $e) {
                // Skip and try slot fallback.
            }
        }

        $slot = $reservation->reservationSlot;

        if (!$slot || !$slot->date || !$slot->start_time) {
            return null;
        }

        try {
            return now()->parse($slot->date->format('Y-m-d') . ' ' . substr((string) $slot->start_time, 0, 5));
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function formatReservationDateTime(Reservation $reservation): string
    {
        $dateTime = $this->reservationDateTime($reservation);

        if ($dateTime === null) {
            return '未設定';
        }

        return $dateTime->format('Y-m-d H:i');
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
