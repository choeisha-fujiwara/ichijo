<?php

namespace App\Http\Controllers;

use App\Models\ArticleVenue;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(private ReportService $reportService) {}

    public function index(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'from'     => ['nullable', 'date'],
            'to'       => ['nullable', 'date', 'after_or_equal:from'],
            'venue_id' => ['nullable', 'integer', 'exists:article_venues,id'],
        ]);

        $from = isset($validated['from'])
            ? Carbon::parse($validated['from'])->startOfDay()
            : now()->subDays(29)->startOfDay();

        $to = isset($validated['to'])
            ? Carbon::parse($validated['to'])->startOfDay()
            : now()->startOfDay();

        $venueId = $validated['venue_id'] ?? null;

        $daily = $this->reportService->dailySummary($from, $to, $venueId);
        $articles = $this->reportService->articleSummary($from, $to, $venueId);

        $venues = ArticleVenue::orderBy('venue_name')->get(['id', 'venue_name']);

        $filters = [
            'from'     => $from->toDateString(),
            'to'       => $to->toDateString(),
            'venue_id' => $venueId,
        ];

        return view('dashboard.report.index', compact('user', 'daily', 'articles', 'filters', 'venues'));
    }
}
