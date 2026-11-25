<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    /**
     * Display comprehensive financial report and analytics.
     */
    public function index(Request $request)
    {
        $dates = $this->resolveDateRange($request);
        $startDate = $dates['start'];
        $endDate = $dates['end'];
        $period = $dates['period'];

        $summary = $this->reportService->getFinancialSummary($startDate, $endDate);
        $trends = $this->reportService->getDailyTrendAnalytics($startDate, $endDate);
        $ledger = $this->reportService->getUnifiedCashLedger($startDate, $endDate, 150);
        $profile = AppSetting::getExpeditionProfile();

        return view('management.reports.index', compact(
            'summary',
            'trends',
            'ledger',
            'startDate',
            'endDate',
            'period',
            'profile'
        ));
    }

    /**
     * Printable A4 official financial report for basecamp audit and cooperative meetings.
     */
    public function print(Request $request)
    {
        $dates = $this->resolveDateRange($request);
        $startDate = $dates['start'];
        $endDate = $dates['end'];
        $period = $dates['period'];

        $summary = $this->reportService->getFinancialSummary($startDate, $endDate);
        $ledger = $this->reportService->getUnifiedCashLedger($startDate, $endDate, 200);
        $profile = AppSetting::getExpeditionProfile();
        $printedAt = Carbon::now();

        return view('management.reports.print', compact(
            'summary',
            'ledger',
            'startDate',
            'endDate',
            'period',
            'profile',
            'printedAt'
        ));
    }

    /**
     * Resolve start and end dates from user request filters.
     */
    protected function resolveDateRange(Request $request): array
    {
        $period = $request->query('period', '30days');
        $today = Carbon::today();

        switch ($period) {
            case 'today':
                $start = $today->copy();
                $end = $today->copy();
                break;

            case '7days':
                $start = $today->copy()->subDays(6);
                $end = $today->copy();
                break;

            case 'this_month':
                $start = $today->copy()->startOfMonth();
                $end = $today->copy();
                break;

            case 'last_month':
                $start = $today->copy()->subMonth()->startOfMonth();
                $end = $today->copy()->subMonth()->endOfMonth();
                break;

            case 'custom':
                $start = $this->parseDateOrDefault($request->query('start_date'), $today->copy()->subDays(29));
                $end = $this->parseDateOrDefault($request->query('end_date'), $today->copy());
                break;

            case '30days':
            default:
                $period = '30days';
                $start = $today->copy()->subDays(29);
                $end = $today->copy();
                break;
        }

        // Prevent inverted dates
        if ($start->gt($end)) {
            $temp = $start;
            $start = $end;
            $end = $temp;
        }

        return [
            'start' => $start,
            'end' => $end,
            'period' => $period,
        ];
    }

    /**
     * Safely parse a user-supplied date query value without throwing on malformed input.
     */
    protected function parseDateOrDefault(mixed $raw, Carbon $fallback): Carbon
    {
        if (! is_string($raw) || trim($raw) === '') {
            return $fallback;
        }

        try {
            return Carbon::parse($raw);
        } catch (\Throwable) {
            return $fallback;
        }
    }
}
