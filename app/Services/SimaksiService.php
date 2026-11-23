<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\DailyQuota;
use App\Models\SimaksiRegistration;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class SimaksiService
{
    public function approve(SimaksiRegistration $registration, int $officerId, ?string $notes = null): SimaksiRegistration
    {
        return DB::transaction(function () use ($registration, $officerId, $notes) {
            $locked = SimaksiRegistration::where('id', $registration->id)->lockForUpdate()->firstOrFail();

            if ($locked->status === 'approved') {
                return $locked;
            }

            $locked->update([
                'status' => 'approved',
                'reviewed_by' => $officerId,
                'reviewed_at' => Carbon::now(),
                'revision_notes' => $notes,
            ]);

            // Ensure daily quota record exists (quota was already atomically locked upon initial registration)
            $defaultDailyQuota = (int) AppSetting::get('default_daily_quota', 150);
            $hDate = $locked->hiking_date instanceof Carbon ? $locked->hiking_date->toDateString() : (string) $locked->hiking_date;
            $existingQuota = DailyQuota::whereDate('quota_date', $hDate)->first();
            if (! $existingQuota) {
                try {
                    DailyQuota::create([
                        'quota_date' => $hDate,
                        'max_quota' => $defaultDailyQuota,
                        'booked_quota' => $locked->total_climbers,
                    ]);
                } catch (QueryException $e) {
                    // Row already exists or created concurrently
                }
            }

            AuditService::log(
                action: 'APPROVE_SIMAKSI',
                entityType: 'SimaksiRegistration',
                entityId: $locked->id,
                description: "SIMAKSI {$locked->registration_code} disetujui untuk {$locked->total_climbers} pendaki.",
                reason: $notes,
                payload: ['status' => 'approved', 'quota_date' => $locked->hiking_date->toDateString()]
            );

            return $locked;
        });
    }

    public function reject(SimaksiRegistration $registration, int $officerId, string $reason): SimaksiRegistration
    {
        return DB::transaction(function () use ($registration, $officerId, $reason) {
            $locked = SimaksiRegistration::where('id', $registration->id)->lockForUpdate()->firstOrFail();

            // Idempotency state guard: If already rejected, do NOT decrement quota again!
            if ($locked->status === 'rejected') {
                return $locked;
            }

            $wasActive = in_array($locked->status, ['pending', 'submitted', 'needs_revision', 'revision_needed', 'approved', 'paid']);

            $locked->update([
                'status' => 'rejected',
                'reviewed_by' => $officerId,
                'reviewed_at' => Carbon::now(),
                'rejection_reason' => $reason,
            ]);

            // Release reserved daily quota atomically only if registration was active
            if ($wasActive) {
                $quota = DailyQuota::whereDate('quota_date', $locked->hiking_date)->lockForUpdate()->first();
                if ($quota && $quota->booked_quota > 0) {
                    $decrementAmount = min($quota->booked_quota, $locked->total_climbers);
                    $quota->decrement('booked_quota', $decrementAmount);
                }
            }

            AuditService::log(
                action: 'REJECT_SIMAKSI',
                entityType: 'SimaksiRegistration',
                entityId: $locked->id,
                description: "SIMAKSI {$locked->registration_code} ditolak oleh petugas. Kuota {$locked->total_climbers} pendaki dikembalikan ke kuota harian.",
                reason: $reason,
                payload: ['status' => 'rejected']
            );

            return $locked;
        });
    }

    public function requestRevision(SimaksiRegistration $registration, int $officerId, string $notes): SimaksiRegistration
    {
        return DB::transaction(function () use ($registration, $officerId, $notes) {
            $locked = SimaksiRegistration::where('id', $registration->id)->lockForUpdate()->firstOrFail();

            $locked->update([
                'status' => 'needs_revision',
                'reviewed_by' => $officerId,
                'reviewed_at' => Carbon::now(),
                'revision_notes' => $notes,
            ]);

            AuditService::log(
                action: 'REVISION_SIMAKSI',
                entityType: 'SimaksiRegistration',
                entityId: $locked->id,
                description: "Permintaan revisi berkas/anggota untuk SIMAKSI {$locked->registration_code}.",
                reason: $notes,
                payload: ['status' => 'needs_revision']
            );

            return $locked;
        });
    }

    public function markAsPaid(SimaksiRegistration $registration, string $method, ?int $officerId = null): SimaksiRegistration
    {
        return DB::transaction(function () use ($registration, $method) {
            $locked = SimaksiRegistration::where('id', $registration->id)->lockForUpdate()->firstOrFail();

            $locked->update([
                'payment_status' => 'paid',
                'payment_method' => $method,
                'paid_at' => Carbon::now(),
                'status' => 'paid', // siap check-in di gerbang
            ]);

            AuditService::log(
                action: 'PAID_SIMAKSI',
                entityType: 'SimaksiRegistration',
                entityId: $locked->id,
                description: "Pembayaran SIMAKSI {$locked->registration_code} lunas via {$method} senilai Rp ".number_format($locked->total_amount, 0, ',', '.'),
                payload: ['payment_method' => $method, 'total' => $locked->total_amount]
            );

            return $locked;
        });
    }
}
