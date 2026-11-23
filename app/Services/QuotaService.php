<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\DailyQuota;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class QuotaService
{
    public function getMonthlyGrid(int $year, int $month): array
    {
        $defaultQuota = (int) AppSetting::get('default_daily_quota', 150);
        $start = Carbon::create($year, $month, 1);
        $end = $start->copy()->endOfMonth();
        $period = CarbonPeriod::create($start, $end);

        $quotas = DailyQuota::whereBetween('quota_date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->keyBy(fn ($q) => $q->quota_date->format('Y-m-d'));

        $grid = [];
        foreach ($period as $date) {
            $key = $date->format('Y-m-d');
            $existing = $quotas->get($key);

            $maxQuota = $existing ? $existing->max_quota : $defaultQuota;
            $booked = $existing ? $existing->booked_quota : 0;
            $isClosed = $existing ? $existing->is_closed : false;
            $remaining = $isClosed ? 0 : max(0, $maxQuota - $booked);

            $grid[] = [
                'date' => $key,
                'day' => $date->day,
                'day_name' => $date->translatedFormat('D'),
                'is_weekend' => $date->isWeekend(),
                'max_quota' => $maxQuota,
                'booked' => $booked,
                'remaining' => $remaining,
                'is_closed' => $isClosed,
                'closure_reason' => $existing?->closure_reason,
            ];
        }

        return $grid;
    }

    public function setEmergencyClosure(string $date, bool $close, ?string $reason, int $userId): DailyQuota
    {
        $defaultQuota = (int) AppSetting::get('default_daily_quota', 150);
        $quota = DailyQuota::firstOrCreate(
            ['quota_date' => $date],
            ['max_quota' => $defaultQuota, 'booked_quota' => 0]
        );

        $quota->update([
            'is_closed' => $close,
            'closure_reason' => $close ? $reason : null,
        ]);

        AuditService::log(
            action: $close ? 'CLOSE_DAILY_TRAIL' : 'OPEN_DAILY_TRAIL',
            entityType: 'DailyQuota',
            entityId: $quota->id,
            description: $close ? "Jalur pada tanggal {$date} DITUTUP DARURAT." : "Jalur pada tanggal {$date} DIBUKA KEMBALI.",
            reason: $reason,
            payload: ['date' => $date, 'is_closed' => $close]
        );

        return $quota;
    }
}
