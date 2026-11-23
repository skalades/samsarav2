<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\AuditLog;
use App\Models\DailyQuota;
use App\Models\PosOrder;
use App\Models\RentalRecord;
use App\Models\SimaksiRegistration;
use App\Models\TrailCondition;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        // Quota Today
        $defaultQuota = (int) AppSetting::get('default_daily_quota', 150);
        $quotaToday = DailyQuota::where('quota_date', $today->toDateString())->first();
        $maxQuota = $quotaToday?->max_quota ?? $defaultQuota;
        $bookedQuota = $quotaToday?->booked_quota ?? 0;
        $remainingQuota = $quotaToday?->remaining_quota ?? $defaultQuota;

        // SIMAKSI Metrics: PENDING vs PAID
        $pendingSimaksiCount = SimaksiRegistration::whereIn('status', ['pending', 'submitted', 'needs_revision', 'approved'])->count();
        $paidSimaksiCount = SimaksiRegistration::where('status', 'paid')->count();
        $activeClimbersOnMountain = SimaksiRegistration::where('status', 'checked_in')->sum('total_climbers');

        // POS Revenue Today
        $posTodayRevenue = PosOrder::whereDate('created_at', $today)
            ->where('status', 'completed')
            ->sum('total_amount');
        $posTransactionsCount = PosOrder::whereDate('created_at', $today)
            ->where('status', 'completed')
            ->count();

        // Rentals
        $activeRentalsCount = RentalRecord::where('status', 'rented')->sum('quantity');

        // Trail & Weather
        $trail = TrailCondition::latest()->first();

        // Recent Audit Logs
        $recentLogs = AuditLog::latest()->take(8)->get();

        // Recent SIMAKSI Registrations
        $recentRegistrations = SimaksiRegistration::latest()->take(6)->get();

        return view('management.dashboard', compact(
            'today',
            'maxQuota',
            'bookedQuota',
            'remainingQuota',
            'quotaToday',
            'pendingSimaksiCount',
            'paidSimaksiCount',
            'activeClimbersOnMountain',
            'posTodayRevenue',
            'posTransactionsCount',
            'activeRentalsCount',
            'trail',
            'recentLogs',
            'recentRegistrations'
        ));
    }
}
