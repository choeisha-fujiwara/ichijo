<?php

namespace App\Http\Controllers;

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
            'from' => ['nullable', 'date'],
            'to'   => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $from = isset($validated['from'])
            ? Carbon::parse($validated['from'])->startOfDay()
            : now()->subDays(29)->startOfDay();

        $to = isset($validated['to'])
            ? Carbon::parse($validated['to'])->startOfDay()
            : now()->startOfDay();

        $daily = $this->reportService->dailySummary($from, $to);
        $articles = $this->reportService->articleSummary($from, $to);

        $filters = [
            'from' => $from->toDateString(),
            'to'   => $to->toDateString(),
        ];

        return view('dashboard.report.index', compact('user', 'daily', 'articles', 'filters'));
    }
}
