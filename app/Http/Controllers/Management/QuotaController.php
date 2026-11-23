<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\DailyQuota;
use App\Models\TrailCondition;
use App\Services\AuditService;
use App\Services\QuotaService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuotaController extends Controller
{
    public function __construct(
        protected QuotaService $quotaService
    ) {}

    public function index(Request $request)
    {
        $year = (int) $request->input('year', date('Y'));
        $month = (int) $request->input('month', date('n'));

        $grid = $this->quotaService->getMonthlyGrid($year, $month);
        $currentMonthName = Carbon::create($year, $month, 1)->translatedFormat('F Y');

        $trail = TrailCondition::latest()->first();

        return view('management.quota.index', compact('grid', 'year', 'month', 'currentMonthName', 'trail'));
    }

    public function updateDate(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'max_quota' => 'required|integer|min:0|max:500',
        ]);

        $date = $request->input('date');
        $maxQuota = (int) $request->input('max_quota');
        $isClosed = $request->boolean('is_closed');
        $reason = $request->input('closure_reason');

        $defaultDailyQuota = (int) AppSetting::get('default_daily_quota', 150);
        $quota = DailyQuota::firstOrCreate(
            ['quota_date' => $date],
            ['max_quota' => $defaultDailyQuota, 'booked_quota' => 0]
        );

        $quota->update([
            'max_quota' => $maxQuota,
            'is_closed' => $isClosed,
            'closure_reason' => $isClosed ? $reason : null,
        ]);

        AuditService::log(
            action: 'UPDATE_DAILY_QUOTA',
            entityType: 'DailyQuota',
            entityId: $quota->id,
            description: "Pengaturan kuota tanggal {$date} diperbarui: max {$maxQuota}, status tutup: ".($isClosed ? 'YA' : 'TIDAK'),
            reason: $reason,
            payload: ['max_quota' => $maxQuota, 'is_closed' => $isClosed]
        );

        return back()->with('success', "Kuota tanggal {$date} berhasil diperbarui.");
    }

    public function updateTrailCondition(Request $request)
    {
        $request->validate([
            'status' => 'required|string',
            'pos2_water_status' => 'required|string',
            'night_temp_celsius' => 'required|string',
            'weather_condition' => 'required|string',
            'soil_condition' => 'required|string',
        ]);

        $trail = TrailCondition::create([
            'status' => $request->input('status'),
            'pos2_water_status' => $request->input('pos2_water_status'),
            'night_temp_celsius' => $request->input('night_temp_celsius'),
            'weather_condition' => $request->input('weather_condition'),
            'soil_condition' => $request->input('soil_condition'),
            'wind_speed' => $request->input('wind_speed', '14 km/jam'),
            'sar_frequency' => $request->input('sar_frequency', AppSetting::get('sar_frequency', '149.250 MHz')),
            'ranger_notes' => $request->input('ranger_notes'),
            'updated_by' => Auth::id(),
        ]);

        AuditService::log(
            action: 'UPDATE_TRAIL_CONDITION',
            entityType: 'TrailCondition',
            entityId: $trail->id,
            description: "Kondisi telemetri jalur diperbarui: status {$trail->status}, air pos 2: {$trail->pos2_water_status}.",
            payload: $trail->toArray()
        );

        return back()->with('success', 'Telemetri dan kondisi jalur Cikuray berhasil dipublikasikan.');
    }
}
