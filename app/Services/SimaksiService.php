<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\ClimberMember;
use App\Models\DailyQuota;
use App\Models\InventoryItem;
use App\Models\OjekDriver;
use App\Models\OjekSettlement;
use App\Models\OjekTrip;
use App\Models\PosOrder;
use App\Models\RentalRecord;
use App\Models\SimaksiCheckpoint;
use App\Models\SimaksiInspectionItem;
use App\Models\SimaksiRegistration;
use App\Models\WhatsappMessage;
use App\Services\WhatsApp\WhatsAppNotifier;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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

    /**
     * Batalkan otomatis booking yang melewati batas waktu pembayaran tanpa bayar
     * dan tanpa bukti transfer, lalu kembalikan kuotanya. Mencegah kuota "disandera"
     * oleh booking palsu. Opsional dibatasi ke satu tanggal pendakian.
     *
     * @return int jumlah booking yang dibatalkan
     */
    public function expireOverdueBookings(?string $hikingDate = null): int
    {
        $ids = SimaksiRegistration::query()
            ->overdueUnpaid()
            ->when($hikingDate, fn ($q) => $q->whereDate('hiking_date', $hikingDate))
            ->pluck('id');

        $expired = 0;
        foreach ($ids as $id) {
            $expired += DB::transaction(function () use ($id) {
                $locked = SimaksiRegistration::where('id', $id)->lockForUpdate()->first();

                // Cek ulang di dalam lock: bisa saja baru dibayar / unggah bukti barusan
                if (! $locked || ! $locked->isOverdueUnpaid()) {
                    return 0;
                }

                $locked->update([
                    'status' => 'rejected',
                    'reviewed_at' => Carbon::now(),
                    'rejection_reason' => 'Dibatalkan otomatis: melewati batas waktu pembayaran.',
                ]);

                $quota = DailyQuota::whereDate('quota_date', $locked->hiking_date)->lockForUpdate()->first();
                if ($quota && $quota->booked_quota > 0) {
                    $quota->decrement('booked_quota', min($quota->booked_quota, $locked->total_climbers));
                }

                AuditService::log(
                    action: 'EXPIRE_SIMAKSI',
                    entityType: 'SimaksiRegistration',
                    entityId: $locked->id,
                    description: "SIMAKSI {$locked->registration_code} dibatalkan otomatis (tidak dibayar sampai ".$locked->payment_due_at?->format('d M Y H:i')."). Kuota {$locked->total_climbers} pendaki dikembalikan.",
                    payload: ['status' => 'rejected', 'reason' => 'payment_timeout']
                );

                return 1;
            });
        }

        return $expired;
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
        return DB::transaction(function () use ($registration, $method, $officerId) {
            $locked = SimaksiRegistration::where('id', $registration->id)->lockForUpdate()->firstOrFail();

            // Idempoten: rombongan yang sudah lunas / sudah lanjut ke Gate tidak boleh mundur statusnya
            if ($locked->payment_status === 'paid' && in_array($locked->status, SimaksiRegistration::POST_PAYMENT_STATUSES, true)) {
                return $locked;
            }

            $locked->update([
                'payment_status' => 'paid',
                'payment_method' => $method,
                'paid_at' => Carbon::now(),
                'status' => 'paid', // siap diperiksa Gatekeeper
            ]);

            // Kirim WhatsApp otomatis: info pendakian lengkap + tautan e-SIMAKSI (setelah commit)
            app(WhatsAppNotifier::class)->notifyPaymentConfirmed($locked, $officerId);

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

    /**
     * Hapus permanen permohonan SIMAKSI beserta seluruh data terkait
     * (anggota, voucher/trip ojek, sewa alat, transaksi POS terkait, log WA,
     * checkpoint, checklist gate, berkas fisik, serta kembalikan kuota & stok).
     *
     * @return array{registration_code:string,ojek_trips:int,rental_records:int,pos_orders:int,whatsapp_messages:int,quota_released:int}
     */
    public function deleteWithRelated(SimaksiRegistration $registration, int $officerId, ?string $reason = null): array
    {
        $filesToDelete = [];

        $summary = DB::transaction(function () use ($registration, $reason, &$filesToDelete) {
            $locked = SimaksiRegistration::where('id', $registration->id)->lockForUpdate()->firstOrFail();

            $fileCandidates = [
                $locked->leader_ktp_path,
                $locked->payment_proof_path,
                $locked->group_photo_path,
                $locked->gate_photo_path,
            ];
            $filesToDelete = array_values(array_filter(array_unique($fileCandidates)));

            // 1. Kembalikan kuota harian bila status masih menahan kuota (selain rejected)
            $quotaReleased = 0;
            if ($locked->status !== 'rejected') {
                $quota = DailyQuota::whereDate('quota_date', $locked->hiking_date)->lockForUpdate()->first();
                if ($quota && $quota->booked_quota > 0) {
                    $quotaReleased = min($quota->booked_quota, (int) $locked->total_climbers);
                    if ($quotaReleased > 0) {
                        $quota->decrement('booked_quota', $quotaReleased);
                    }
                }
            }

            // Kumpulkan ID transaksi POS terkait agar semua turunan POS & SIMAKSI ikut bersih
            $posOrders = PosOrder::with('items.item')
                ->where('simaksi_registration_id', $locked->id)
                ->lockForUpdate()
                ->get();
            $posOrderIds = $posOrders->pluck('id')->all();

            // 2. Hapus trip/voucher ojek terkait + sesuaikan settlement + pulihkan status driver
            $ojekTrips = OjekTrip::where('simaksi_registration_id', $locked->id)
                ->when(! empty($posOrderIds), fn ($q) => $q->orWhereIn('pos_order_id', $posOrderIds))
                ->lockForUpdate()
                ->get();

            $affectedDriverIds = $ojekTrips
                ->whereIn('trip_status', ['assigned', 'on_trip'])
                ->pluck('driver_id')
                ->filter()
                ->unique()
                ->values()
                ->all();

            $settlementAdjustments = $ojekTrips
                ->whereNotNull('ojek_settlement_id')
                ->groupBy('ojek_settlement_id');

            foreach ($settlementAdjustments as $settlementId => $tripsInSettlement) {
                $settlement = OjekSettlement::where('id', $settlementId)->lockForUpdate()->first();
                if (! $settlement) {
                    continue;
                }
                $newCount = max(0, (int) $settlement->total_trips - $tripsInSettlement->count());
                $newAmount = max(0, (float) $settlement->total_amount - (float) $tripsInSettlement->sum('driver_share'));
                if ($newCount === 0) {
                    $settlement->delete();
                } else {
                    $settlement->update([
                        'total_trips' => $newCount,
                        'total_amount' => $newAmount,
                    ]);
                }
            }

            $ojekCount = $ojekTrips->count();
            if ($ojekCount > 0) {
                OjekTrip::whereIn('id', $ojekTrips->pluck('id')->all())->delete();
            }

            foreach ($affectedDriverIds as $driverId) {
                $driver = OjekDriver::where('id', $driverId)->lockForUpdate()->first();
                if (! $driver || $driver->status !== 'on_trip') {
                    continue;
                }
                $stillActive = OjekTrip::where('driver_id', $driver->id)
                    ->whereIn('trip_status', ['assigned', 'on_trip'])
                    ->exists();
                if (! $stillActive) {
                    $driver->update(['status' => 'standby']);
                }
            }

            // 3. Kembalikan stok alat yang masih status 'rented', lalu hapus record sewa
            $rentals = RentalRecord::where('simaksi_registration_id', $locked->id)
                ->when(! empty($posOrderIds), fn ($q) => $q->orWhereIn('pos_order_id', $posOrderIds))
                ->lockForUpdate()
                ->get();

            foreach ($rentals as $rental) {
                if ($rental->status === 'rented' && $rental->inventory_item_id) {
                    $item = InventoryItem::where('id', $rental->inventory_item_id)->lockForUpdate()->first();
                    if ($item && $item->type !== 'service') {
                        $restore = (int) $rental->quantity;
                        if ($item->stock_total > 0) {
                            $restore = min($restore, max(0, (int) $item->stock_total - (int) $item->stock_available));
                        }
                        if ($restore > 0) {
                            $item->increment('stock_available', $restore);
                        }
                    }
                }
            }

            $rentalCount = $rentals->count();
            if ($rentalCount > 0) {
                RentalRecord::whereIn('id', $rentals->pluck('id')->all())->delete();
            }

            // 4. Kembalikan stok barang ritel pada PosOrder yang completed, lalu hapus PosOrder terkait
            $posCount = $posOrders->count();
            foreach ($posOrders as $order) {
                if ($order->status === 'completed') {
                    foreach ($order->items as $orderItem) {
                        if (! $orderItem->inventory_item_id) {
                            continue;
                        }
                        $inv = InventoryItem::where('id', $orderItem->inventory_item_id)->lockForUpdate()->first();
                        // Stok tipe 'rental' sudah ditangani lewat RentalRecord di atas agar tidak double-restore
                        if ($inv && $inv->type === 'goods') {
                            $restore = (int) $orderItem->quantity;
                            if ($inv->stock_total > 0) {
                                $restore = min($restore, max(0, (int) $inv->stock_total - (int) $inv->stock_available));
                            }
                            if ($restore > 0) {
                                $inv->increment('stock_available', $restore);
                            }
                        }
                    }
                }
                $order->delete();
            }

            // 5. Hapus pesan WhatsApp, checkpoint ranger, checklist gate, dan manifest anggota
            $waCount = WhatsappMessage::where('simaksi_registration_id', $locked->id)->delete();
            SimaksiCheckpoint::where('simaksi_registration_id', $locked->id)->delete();
            SimaksiInspectionItem::where('simaksi_registration_id', $locked->id)->delete();
            ClimberMember::where('simaksi_registration_id', $locked->id)->delete();

            $code = $locked->registration_code;
            $leaderName = $locked->leader_name;
            $climbers = (int) $locked->total_climbers;
            $regId = $locked->id;

            // 6. Hapus record utama SIMAKSI
            $locked->delete();

            $result = [
                'registration_code' => $code,
                'ojek_trips' => $ojekCount,
                'rental_records' => $rentalCount,
                'pos_orders' => $posCount,
                'whatsapp_messages' => $waCount,
                'quota_released' => $quotaReleased,
            ];

            AuditService::log(
                action: 'DELETE_SIMAKSI',
                entityType: 'SimaksiRegistration',
                entityId: $regId,
                description: "Superadmin menghapus permanen SIMAKSI {$code} ({$leaderName}, {$climbers} pendaki) beserta data terkait (Ojek: {$ojekCount}, Sewa: {$rentalCount}, POS: {$posCount}, WA: {$waCount}).",
                reason: $reason ?: null,
                payload: $result
            );

            return $result;
        });

        // Hapus berkas fisik setelah transaksi database sukses commit
        if (! empty($filesToDelete)) {
            DB::afterCommit(function () use ($filesToDelete): void {
                foreach ($filesToDelete as $path) {
                    foreach (['local', 'public'] as $disk) {
                        if (Storage::disk($disk)->exists($path)) {
                            Storage::disk($disk)->delete($path);
                        }
                    }
                }
            });
        }

        return $summary;
    }
}
