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
use App\Models\User;
use App\Models\WhatsappMessage;
use App\Services\WhatsApp\WhatsAppManager;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

/**
 * Beranda per level:
 *  - superadmin → ringkasan menyeluruh
 *  - kasir      → meja kasir (antrean bayar & kiriman Gate)
 *  - gatekeeper → langsung ke layar Gate
 *  - ranger     → langsung ke scanner pos
 */
class DashboardController extends Controller
{
    public function index()
    {
        /** @var User $user */
        $user = Auth::user();

        return match ($user->level()) {
            User::ROLE_GATEKEEPER => redirect()->route('management.gate.index'),
            User::ROLE_RANGER => redirect()->route('management.ranger.index'),
            User::ROLE_KASIR => $this->kasir(),
            default => $this->superadmin(),
        };
    }

    protected function pipeline(): array
    {
        $counts = SimaksiRegistration::selectRaw('status, COUNT(*) as total, SUM(total_climbers) as climbers')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        $pending = collect(SimaksiRegistration::STATUS_PENDING_GROUP)->sum(fn ($s) => (int) ($counts[$s]->total ?? 0));

        return [
            ['key' => 'pending', 'label' => 'Menunggu bayar', 'count' => $pending, 'tone' => 'warning', 'href' => route('management.simaksi.index', ['status' => 'pending'])],
            ['key' => 'paid', 'label' => 'Lunas · ke Gate', 'count' => (int) ($counts['paid']->total ?? 0), 'tone' => 'success', 'href' => route('management.simaksi.index', ['status' => 'paid'])],
            ['key' => 'gate_cleared', 'label' => 'Siap cetak kasir', 'count' => (int) ($counts['gate_cleared']->total ?? 0), 'tone' => 'info', 'href' => route('management.desk.index')],
            ['key' => 'checked_in', 'label' => 'Di jalur', 'count' => (int) ($counts['checked_in']->total ?? 0), 'climbers' => (int) ($counts['checked_in']->climbers ?? 0), 'tone' => 'brand', 'href' => route('management.simaksi.index', ['status' => 'checked_in'])],
            ['key' => 'returned', 'label' => 'Turun · ke kasir', 'count' => (int) ($counts['returned']->total ?? 0), 'tone' => 'violet', 'href' => route('management.desk.index', ['tab' => 'turun'])],
        ];
    }

    protected function superadmin()
    {
        $today = Carbon::today();
        $defaultQuota = (int) AppSetting::get('default_daily_quota', 150);
        $quotaToday = DailyQuota::whereDate('quota_date', $today)->first();
        $maxQuota = $quotaToday?->max_quota ?? $defaultQuota;
        $bookedQuota = $quotaToday?->booked_quota ?? 0;

        $simaksiRevenueToday = (float) SimaksiRegistration::whereDate('paid_at', $today)->sum('total_amount');
        $posRevenueToday = (float) PosOrder::whereDate('created_at', $today)->where('status', 'completed')->sum('total_amount');

        $weekRevenue = collect(range(6, 0))->map(function ($daysAgo) {
            $date = Carbon::today()->subDays($daysAgo);

            return [
                'label' => $date->translatedFormat('D'),
                'date' => $date->toDateString(),
                'simaksi' => (float) SimaksiRegistration::whereDate('paid_at', $date)->sum('total_amount'),
                'pos' => (float) PosOrder::whereDate('created_at', $date)->where('status', 'completed')->sum('total_amount'),
            ];
        });

        $completedToday = SimaksiRegistration::where('status', 'completed')->whereDate('checked_out_at', $today)->count();
        $notClearToday = SimaksiRegistration::where('gate_down_clear', false)->whereDate('gate_down_at', $today)->count();

        $staff = User::all()->groupBy(fn (User $u) => $u->level())->map->count();

        return view('management.dashboards.superadmin', [
            'today' => $today,
            'pipeline' => $this->pipeline(),
            'maxQuota' => $maxQuota,
            'bookedQuota' => $bookedQuota,
            'simaksiRevenueToday' => $simaksiRevenueToday,
            'posRevenueToday' => $posRevenueToday,
            'weekRevenue' => $weekRevenue,
            'completedToday' => $completedToday,
            'notClearToday' => $notClearToday,
            'trail' => TrailCondition::latest()->first(),
            'recentLogs' => AuditLog::latest()->take(8)->get(),
            'staff' => $staff,
            'waStatus' => app(WhatsAppManager::class)->status(),
            'waFailed' => WhatsappMessage::where('status', 'failed')->count(),
            'waSentToday' => WhatsappMessage::where('status', 'sent')->whereDate('sent_at', $today)->count(),
        ]);
    }

    protected function kasir()
    {
        $today = Carbon::today();

        $awaitingPayment = SimaksiRegistration::whereIn('status', SimaksiRegistration::STATUS_PENDING_GROUP)
            ->orderByRaw('CASE WHEN payment_proof_path IS NULL THEN 1 ELSE 0 END')
            ->orderBy('hiking_date')
            ->take(6)
            ->get();

        $readyToRelease = SimaksiRegistration::with('members')->where('status', 'gate_cleared')->orderBy('gate_up_at')->get();
        $readyToFinalize = SimaksiRegistration::where('status', 'returned')->orderBy('gate_down_at')->get();

        $cashDrawer = app(PosController::class)->getCashDrawerSummary();

        return view('management.dashboards.kasir', [
            'today' => $today,
            'pipeline' => $this->pipeline(),
            'awaitingPayment' => $awaitingPayment,
            'awaitingPaymentCount' => SimaksiRegistration::whereIn('status', SimaksiRegistration::STATUS_PENDING_GROUP)->count(),
            'proofUploadedCount' => SimaksiRegistration::whereIn('status', SimaksiRegistration::STATUS_PENDING_GROUP)->whereNotNull('payment_proof_path')->count(),
            'readyToRelease' => $readyToRelease,
            'readyToFinalize' => $readyToFinalize,
            'cashDrawer' => $cashDrawer,
            'posTodayCount' => PosOrder::whereDate('created_at', $today)->where('status', 'completed')->count(),
            'activeRentals' => (int) RentalRecord::where('status', 'rented')->sum('quantity'),
            'waFailed' => WhatsappMessage::with('registration')->where('status', 'failed')->latest()->take(5)->get(),
        ]);
    }
}
