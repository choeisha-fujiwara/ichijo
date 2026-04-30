<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReservationAdminController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $sort = (string) $request->query('sort', 'created_desc');

        $query = Reservation::query()->with([
            'article:id,title,venue_id',
            'article.venue:id,venue_name',
            'reservationSlot:id,article_id,date,start_time,end_time',
        ]);

        $this->applySort($query, $sort);

        $reservations = $query
            ->paginate(20)
            ->withQueryString();

        return view('dashboard.reservations.index', compact('user', 'reservations', 'sort'));
    }

    public function show(Reservation $reservation)
    {
        $user = auth()->user();

        $reservation->load([
            'article:id,title',
            'reservationSlot:id,article_id,date,start_time,end_time,capacity,reserved_count',
        ]);

        [$statusLabel, $statusClass] = $this->resolveStatus($reservation);

        return view('dashboard.reservations.show', compact('user', 'reservation', 'statusLabel', 'statusClass'));
    }

    public function export(Request $request): StreamedResponse
    {
        $sort = (string) $request->query('sort', 'created_desc');

        $query = Reservation::query()->with([
            'article:id,title',
            'reservationSlot:id,article_id,date,start_time,end_time',
        ]);

        $this->applySort($query, $sort);

        $reservations = $query->get();

        $fileName = 'reservations-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($reservations): void {
            $stream = fopen('php://output', 'w');

            $this->writeCsvRow($stream, [
                'ID',
                '受付日時',
                '予約日時',
                'ステータス',
                'イベント名',
                '氏名',
                '氏名カナ',
                'メールアドレス',
                '電話番号',
                '住所',
                '備考',
            ], true);

            foreach ($reservations as $reservation) {
                [$statusLabel] = $this->resolveStatus($reservation);

                $this->writeCsvRow($stream, [
                    $reservation->id,
                    optional($reservation->created_at)->format('Y-m-d H:i'),
                    $this->formatReservationDateTime($reservation),
                    $statusLabel,
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
            'Content-Type' => 'text/csv; charset=UTF-8',
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

    private function writeCsvRow($stream, array $row, bool $stripUtf8Bom = false): void
    {
        $temp = fopen('php://temp', 'r+');

        if ($temp === false) {
            return;
        }

        fputcsv($temp, $row);
        rewind($temp);
        $line = (string) stream_get_contents($temp);
        fclose($temp);

        if ($stripUtf8Bom) {
            $line = preg_replace('/^\xEF\xBB\xBF/', '', $line) ?? $line;
        }

        fwrite($stream, $line);
    }
}
