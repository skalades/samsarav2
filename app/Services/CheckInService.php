<?php

namespace App\Services;

use App\Models\ClimberMember;
use App\Models\SimaksiRegistration;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CheckInService
{
    public function checkIn(
        SimaksiRegistration $registration,
        int $officerId,
        ?string $trashBagCode = null,
        array $checkedMemberIds = [],
        array $assignedDriverIds = []
    ): SimaksiRegistration {
        return DB::transaction(function () use ($registration, $officerId, $trashBagCode, $checkedMemberIds, $assignedDriverIds) {
            // Lock registration record exclusively to prevent duplicate concurrent check-ins
            $lockedReg = SimaksiRegistration::where('id', $registration->id)->lockForUpdate()->firstOrFail();

            if ($lockedReg->status === 'checked_in') {
                throw new \RuntimeException("Rombongan SIMAKSI {$lockedReg->registration_code} sudah berstatus Check-In di jalur.");
            }

            if ($lockedReg->status === 'completed') {
                throw new \RuntimeException("Rombongan SIMAKSI {$lockedReg->registration_code} sudah menyelesaikan pendakian (Checked Out).");
            }

            // Update individual climber member attendance
            ClimberMember::where('simaksi_registration_id', $lockedReg->id)
                ->whereIn('id', $checkedMemberIds)
                ->update(['is_checked_in' => true]);

            // Update registration status
            $now = Carbon::now();
            $lockedReg->update([
                'status' => 'checked_in',
                'checked_in_by' => $officerId,
                'checked_in_at' => $now,
                'last_seen_pos' => 'BASECAMP',
                'last_seen_direction' => 'ascending',
                'last_seen_at' => $now,
                'trash_bag_code' => $trashBagCode ?: null,
                'trash_deposit_status' => $lockedReg->trash_deposit_amount > 0 ? 'deposited' : 'none',
            ]);

            // Create initial checkpoint log for gate entry
            \App\Models\SimaksiCheckpoint::create([
                'simaksi_registration_id' => $lockedReg->id,
                'pos_code' => 'BASECAMP',
                'direction' => 'ascending',
                'ranger_id' => $officerId,
                'entry_source' => 'scanner',
                'notes' => 'Check-in gerbang basecamp berhasil dibuka.',
                'scanned_at' => $now,
            ]);

            // Automatically issue ojek trip vouchers if ojek was booked
            if ($lockedReg->ojek_count > 0) {
                $trips = app(OjekService::class)->ensureTripsForRegistration($lockedReg, $officerId);

                // Assign drivers directly if provided at gate check-in
                foreach ($trips as $index => $trip) {
                    $driverId = $assignedDriverIds[$index] ?? null;
                    if ($driverId && ! $trip->driver_id) {
                        app(OjekService::class)->assignDriver($trip, (int) $driverId, $officerId);
                    }
                }
            }

            AuditService::log(
                action: 'GATE_CHECKIN',
                entityType: 'SimaksiRegistration',
                entityId: $lockedReg->id,
                description: "Check-in berhasil untuk {$lockedReg->registration_code}. Kantong sampah #{$trashBagCode} diterbitkan. Hadir: ".count($checkedMemberIds).' pendaki.',
                payload: [
                    'trash_bag_code' => $trashBagCode,
                    'checked_members' => $checkedMemberIds,
                ]
            );

            return $lockedReg;
        });
    }

    public function checkOut(
        SimaksiRegistration $registration,
        int $officerId,
        bool $trashVerified,
        ?string $notes = null
    ): SimaksiRegistration {
        return DB::transaction(function () use ($registration, $officerId, $trashVerified, $notes) {
            // Lock registration row exclusively to prevent concurrent double-checkout
            $lockedReg = SimaksiRegistration::where('id', $registration->id)->lockForUpdate()->firstOrFail();

            if ($lockedReg->status === 'completed') {
                throw new \RuntimeException("Rombongan SIMAKSI {$lockedReg->registration_code} sudah berstatus Check-Out sebelumnya.");
            }

            // Deposit sampah hanya diproses bila memang ada jaminan yang dibayar
            $hasDeposit = (float) $lockedReg->trash_deposit_amount > 0;
            $depositStatus = $hasDeposit
                ? ($trashVerified ? 'refunded' : 'forfeited')
                : ($lockedReg->trash_deposit_status ?: 'waived');
            $alreadyAtGate = $lockedReg->status === 'returned';

            // Perlindungan Data Pribadi: Hapus file fisik KTP secara otomatis setelah pendakian selesai
            $ktpDeleted = false;
            if ($lockedReg->leader_ktp_path) {
                foreach (['public', 'local'] as $diskName) {
                    if (Storage::disk($diskName)->exists($lockedReg->leader_ktp_path)) {
                        Storage::disk($diskName)->delete($lockedReg->leader_ktp_path);
                        $ktpDeleted = true;
                    }
                }
            }

            $now = Carbon::now();
            $lockedReg->update([
                'status' => 'completed',
                'checked_out_at' => $now,
                'last_seen_pos' => 'BASECAMP',
                'last_seen_direction' => 'descending',
                'last_seen_at' => $now,
                'trash_deposit_status' => $depositStatus,
                'leader_ktp_path' => null, // Hapus referensi path KTP permanen
            ]);

            // Kedatangan di gerbang sudah dicatat Gatekeeper saat pemeriksaan turun
            if (! $alreadyAtGate) {
                \App\Models\SimaksiCheckpoint::create([
                    'simaksi_registration_id' => $lockedReg->id,
                    'pos_code' => 'BASECAMP',
                    'direction' => 'descending',
                    'ranger_id' => $officerId,
                    'entry_source' => 'scanner',
                    'notes' => 'Check-out selesai. Sampah diverifikasi: '.($trashVerified ? 'Lengkap' : 'Tidak lengkap'),
                    'scanned_at' => $now,
                ]);
            }

            AuditService::log(
                action: 'GATE_CHECKOUT',
                entityType: 'SimaksiRegistration',
                entityId: $lockedReg->id,
                description: "Pendaki {$lockedReg->registration_code} selesai turun & check-out. Status deposit sampah: {$depositStatus}. File KTP otomatis dimusnahkan demi privasi.",
                reason: $notes,
                payload: [
                    'trash_verified' => $trashVerified,
                    'deposit_status' => $depositStatus,
                    'ktp_purged' => $ktpDeleted,
                ]
            );

            return $lockedReg;
        });
    }
}
