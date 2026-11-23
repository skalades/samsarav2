<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\OjekDriver;
use App\Models\OjekSettlement;
use App\Models\OjekTrip;
use App\Models\SimaksiRegistration;
use App\Support\CodeGenerator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class OjekService
{
    /**
     * Ensure trip records/vouchers exist for a SIMAKSI registration with ojek reservation.
     */
    public function ensureTripsForRegistration(SimaksiRegistration $registration, ?int $officerId = null): array
    {
        if ($registration->ojek_count <= 0) {
            return [];
        }

        return DB::transaction(function () use ($registration, $officerId) {
            // Lock and check existing trips under transaction lock to prevent duplicate voucher creation
            $existing = OjekTrip::where('simaksi_registration_id', $registration->id)
                ->lockForUpdate()
                ->get();

            if ($existing->isNotEmpty()) {
                return $existing->all();
            }

            $createdTrips = [];
            $count = $registration->ojek_count;
            $farePerTrip = $count > 0 ? ($registration->ojek_fee / $count) : (float) AppSetting::get('ojek_fee_per_trip', 30000);

            // Komisi kas basecamp/jalur diambil dari konfigurasi resmi
            $basecampShare = (float) AppSetting::get('ojek_basecamp_share', min(5000, $farePerTrip * 0.2));
            $driverShare = max(0, $farePerTrip - $basecampShare);
            $routeName = AppSetting::get('ojek_route_name', 'Basecamp → Pos 1');

            for ($i = 1; $i <= $count; $i++) {
                $tripCode = CodeGenerator::ojekTripCode($registration->registration_code, $i);

                $trip = OjekTrip::create([
                    'trip_code' => $tripCode,
                    'simaksi_registration_id' => $registration->id,
                    'passenger_name' => $registration->leader_name.($count > 1 ? " (Penumpang #{$i})" : ''),
                    'passenger_phone' => $registration->leader_phone,
                    'route' => $routeName,
                    'fare_amount' => $farePerTrip,
                    'driver_share' => $driverShare,
                    'basecamp_share' => $basecampShare,
                    'payment_status' => 'paid',
                    'trip_status' => 'voucher_issued',
                    'settlement_status' => 'unsettled',
                    'created_by' => $officerId ?? $registration->checked_in_by,
                ]);

                $createdTrips[] = $trip;
            }

            AuditService::log(
                action: 'OJEK_VOUCHER_ISSUED',
                entityType: 'SimaksiRegistration',
                entityId: $registration->id,
                description: "Diterbitkan {$count} voucher ojek resmi untuk rombongan {$registration->registration_code} ({$registration->leader_name}).",
                payload: ['trips_count' => $count, 'fare_per_trip' => $farePerTrip]
            );

            return $createdTrips;
        });
    }

    /**
     * Dispatch / assign driver to a trip voucher.
     */
    public function assignDriver(OjekTrip $trip, int $driverId, int $officerId): OjekTrip
    {
        return DB::transaction(function () use ($trip, $driverId) {
            $lockedTrip = OjekTrip::where('id', $trip->id)->lockForUpdate()->firstOrFail();
            $driver = OjekDriver::where('id', $driverId)->lockForUpdate()->firstOrFail();

            $lockedTrip->update([
                'driver_id' => $driver->id,
                'trip_status' => 'assigned',
                'dispatched_at' => Carbon::now(),
            ]);

            // Update driver status to on_trip
            $driver->update(['status' => 'on_trip']);

            AuditService::log(
                action: 'OJEK_DISPATCHED',
                entityType: 'OjekTrip',
                entityId: $lockedTrip->id,
                description: "Trip {$lockedTrip->trip_code} ditugaskan kepada pengemudi {$driver->name} (Rompi #{$driver->registration_number}, Plat: {$driver->plate_number}).",
                payload: ['driver_id' => $driver->id, 'driver_name' => $driver->name, 'plate' => $driver->plate_number]
            );

            return $lockedTrip;
        });
    }

    /**
     * Mark an ojek trip as completed (arrived safely at destination).
     */
    public function completeTrip(OjekTrip $trip, int $officerId): OjekTrip
    {
        return DB::transaction(function () use ($trip) {
            $lockedTrip = OjekTrip::where('id', $trip->id)->lockForUpdate()->firstOrFail();

            if ($lockedTrip->trip_status === 'completed') {
                return $lockedTrip;
            }

            $lockedTrip->update([
                'trip_status' => 'completed',
                'completed_at' => Carbon::now(),
            ]);

            if ($lockedTrip->driver) {
                // Driver returns to standby queue
                $lockedTrip->driver->update(['status' => 'standby']);
            }

            AuditService::log(
                action: 'OJEK_TRIP_COMPLETED',
                entityType: 'OjekTrip',
                entityId: $lockedTrip->id,
                description: "Trip {$lockedTrip->trip_code} diselesaikan oleh pengemudi {$lockedTrip->driver?->name}. Hak bayar Rp ".number_format($lockedTrip->driver_share, 0, ',', '.').' siap direkonsiliasi.',
                payload: ['driver_id' => $lockedTrip->driver_id, 'driver_share' => $lockedTrip->driver_share]
            );

            return $lockedTrip;
        });
    }

    /**
     * Process financial payout / settlement for a driver.
     */
    public function settleDriverPayout(int $driverId, int $cashierId, string $paymentMethod = 'cash', ?string $notes = null): OjekSettlement
    {
        return DB::transaction(function () use ($driverId, $cashierId, $paymentMethod, $notes) {
            $driver = OjekDriver::where('id', $driverId)->lockForUpdate()->firstOrFail();

            $trips = OjekTrip::where('driver_id', $driverId)
                ->where('trip_status', 'completed')
                ->where('settlement_status', 'unsettled')
                ->lockForUpdate()
                ->get();

            if ($trips->isEmpty()) {
                throw new \RuntimeException("Tidak ada trip yang menunggu pencairan payout untuk pengemudi {$driver->name}.");
            }

            $totalTrips = $trips->count();
            $totalAmount = (float) $trips->sum('driver_share');

            // Generate unique settlement code using anti-collision micro-entropy
            $settlementCode = CodeGenerator::ojekSettlementCode();

            $settlement = OjekSettlement::create([
                'settlement_code' => $settlementCode,
                'driver_id' => $driver->id,
                'cashier_id' => $cashierId,
                'total_trips' => $totalTrips,
                'total_amount' => $totalAmount,
                'payment_method' => $paymentMethod,
                'notes' => $notes,
                'settled_at' => Carbon::now(),
            ]);

            // Mark trips as settled
            OjekTrip::whereIn('id', $trips->pluck('id'))->update([
                'settlement_status' => 'settled',
                'ojek_settlement_id' => $settlement->id,
            ]);

            AuditService::log(
                action: 'OJEK_PAYOUT_SETTLED',
                entityType: 'OjekSettlement',
                entityId: $settlement->id,
                description: "Pencairan honor ojek {$settlement->settlement_code}: Rp ".number_format($totalAmount, 0, ',', '.')." dibayarkan kepada {$driver->name} (#{$driver->registration_number}) untuk {$totalTrips} trip.",
                payload: [
                    'settlement_code' => $settlementCode,
                    'driver_id' => $driver->id,
                    'total_trips' => $totalTrips,
                    'total_amount' => $totalAmount,
                ]
            );

            return $settlement;
        });
    }
}
