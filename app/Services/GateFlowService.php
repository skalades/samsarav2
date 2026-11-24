<?php

namespace App\Services;

use App\Models\ClimberMember;
use App\Models\SimaksiCheckpoint;
use App\Models\SimaksiInspectionItem;
use App\Models\SimaksiRegistration;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

/**
 * Alur serah-terima Gatekeeper ↔ Kasir:
 *
 *  paid ──(Gate periksa naik)──▶ gate_cleared ──(Kasir cetak & berangkatkan)──▶ checked_in
 *  checked_in ──(Gate periksa turun)──▶ returned ──(Kasir selesaikan)──▶ completed
 */
class GateFlowService
{
    public function __construct(
        protected CheckInService $checkInService,
        protected GroupPhotoService $photoService,
    ) {}

    /**
     * Gatekeeper: pemeriksaan sebelum naik (anggota, foto tim, alat wajib, logistik).
     *
     * @param  array<int,int>  $presentMemberIds
     * @param  array<int,array{name:string,checked:bool}>  $gear
     * @param  array<int,array{name:string,unit:?string,qty:int}>  $logistics
     */
    public function clearAscent(
        SimaksiRegistration $registration,
        int $officerId,
        array $presentMemberIds,
        bool $photoVerified,
        ?UploadedFile $photo,
        array $gear,
        array $logistics,
        ?string $notes = null,
    ): SimaksiRegistration {
        if ($photo) {
            // Simpan + watermark foto tim yang diambil Gatekeeper di gerbang
            $path = $this->photoService->processAndStore($registration, $photo);
            $registration->forceFill(['gate_photo_path' => $path])->save();
            $photoVerified = true;
        }

        return DB::transaction(function () use ($registration, $officerId, $presentMemberIds, $photoVerified, $gear, $logistics, $notes) {
            $locked = SimaksiRegistration::where('id', $registration->id)->lockForUpdate()->firstOrFail();

            if ($locked->status !== 'paid') {
                throw new \RuntimeException("Rombongan {$locked->registration_code} tidak bisa diperiksa naik (status: {$locked->statusLabel()}). Pastikan sudah LUNAS di kasir.");
            }

            if (count($presentMemberIds) === 0) {
                throw new \RuntimeException('Tandai minimal satu anggota yang hadir di gerbang.');
            }

            if (! $photoVerified && ! $locked->gate_photo_path && ! $locked->group_photo_path) {
                throw new \RuntimeException('Foto tim wajib ada: ambil foto rombongan di gerbang atau verifikasi foto yang diunggah pendaki.');
            }

            ClimberMember::where('simaksi_registration_id', $locked->id)->update(['is_checked_in' => false]);
            ClimberMember::where('simaksi_registration_id', $locked->id)
                ->whereIn('id', $presentMemberIds)
                ->update(['is_checked_in' => true]);

            SimaksiInspectionItem::where('simaksi_registration_id', $locked->id)->delete();

            foreach ($gear as $row) {
                $name = trim((string) ($row['name'] ?? ''));
                if ($name === '') {
                    continue;
                }
                SimaksiInspectionItem::create([
                    'simaksi_registration_id' => $locked->id,
                    'type' => SimaksiInspectionItem::TYPE_GEAR,
                    'name' => mb_substr($name, 0, 100),
                    'checked_up' => (bool) ($row['checked'] ?? false),
                ]);
            }

            foreach ($logistics as $row) {
                $name = trim((string) ($row['name'] ?? ''));
                $qty = max(0, (int) ($row['qty'] ?? 0));
                if ($name === '' || $qty === 0) {
                    continue;
                }
                SimaksiInspectionItem::create([
                    'simaksi_registration_id' => $locked->id,
                    'type' => SimaksiInspectionItem::TYPE_LOGISTIC,
                    'name' => mb_substr($name, 0, 100),
                    'unit' => mb_substr(trim((string) ($row['unit'] ?? 'pcs')) ?: 'pcs', 0, 20),
                    'qty_up' => $qty,
                    'checked_up' => true,
                ]);
            }

            $locked->update([
                'status' => 'gate_cleared',
                'gate_up_by' => $officerId,
                'gate_up_at' => Carbon::now(),
                'gate_up_notes' => $notes,
                'gate_photo_verified' => $photoVerified || (bool) $locked->gate_photo_path,
            ]);

            $logisticTotal = collect($logistics)->sum(fn ($r) => max(0, (int) ($r['qty'] ?? 0)));
            AuditService::log(
                action: 'GATE_ASCENT_CLEARED',
                entityType: 'SimaksiRegistration',
                entityId: $locked->id,
                description: "Gate: {$locked->registration_code} lolos pemeriksaan naik. Hadir ".count($presentMemberIds)." pendaki, {$logisticTotal} unit logistik tercatat. Diteruskan ke kasir untuk cetak surat jalan.",
                reason: $notes,
                payload: ['present_members' => $presentMemberIds, 'logistic_units' => $logisticTotal]
            );

            return $locked;
        });
    }

    /**
     * Kasir: cetak Surat Jalan & Voucher Ojek lalu berangkatkan rombongan (status → checked_in).
     *
     * @param  array<int,int>  $driverIds
     */
    public function release(SimaksiRegistration $registration, int $cashierId, array $driverIds = []): SimaksiRegistration
    {
        if ($registration->fresh()->status !== 'gate_cleared') {
            throw new \RuntimeException("Rombongan {$registration->registration_code} belum lolos pemeriksaan Gate.");
        }

        $presentIds = ClimberMember::where('simaksi_registration_id', $registration->id)
            ->where('is_checked_in', true)
            ->pluck('id')
            ->all();

        $locked = $this->checkInService->checkIn(
            registration: $registration,
            officerId: $registration->gate_up_by ?: $cashierId,
            trashBagCode: null,
            checkedMemberIds: $presentIds,
            assignedDriverIds: $driverIds,
        );

        $locked->update([
            'released_by' => $cashierId,
            'released_at' => Carbon::now(),
        ]);

        AuditService::log(
            action: 'DESK_RELEASE',
            entityType: 'SimaksiRegistration',
            entityId: $locked->id,
            description: "Kasir mencetak surat jalan".($locked->ojek_count > 0 ? ' & voucher ojek' : '')." untuk {$locked->registration_code}. Rombongan resmi berangkat mendaki.",
        );

        return $locked;
    }

    /**
     * Gatekeeper: pemeriksaan saat turun — hitung ulang logistik & alat.
     *
     * @param  array<int,int>  $qtyDown  [itemId => jumlah dibawa turun]
     * @param  array<int,bool>  $gearDown  [itemId => kembali]
     */
    public function recordDescent(
        SimaksiRegistration $registration,
        int $officerId,
        array $qtyDown,
        array $gearDown,
        ?string $notes = null,
    ): SimaksiRegistration {
        return DB::transaction(function () use ($registration, $officerId, $qtyDown, $gearDown, $notes) {
            $locked = SimaksiRegistration::where('id', $registration->id)->lockForUpdate()->firstOrFail();

            if ($locked->status !== 'checked_in') {
                throw new \RuntimeException("Rombongan {$locked->registration_code} tidak sedang mendaki (status: {$locked->statusLabel()}).");
            }

            $items = SimaksiInspectionItem::where('simaksi_registration_id', $locked->id)->get();
            $missing = [];

            foreach ($items as $item) {
                if ($item->type === SimaksiInspectionItem::TYPE_LOGISTIC) {
                    $item->qty_down = max(0, (int) ($qtyDown[$item->id] ?? 0));
                    $item->checked_down = $item->qty_down >= (int) $item->qty_up;
                    if ($item->missingQty() > 0) {
                        $missing[] = "{$item->name}: kurang {$item->missingQty()} {$item->unit}";
                    }
                } else {
                    $item->checked_down = (bool) ($gearDown[$item->id] ?? false);
                    if ($item->checked_up && ! $item->checked_down) {
                        $missing[] = "{$item->name}: tidak dibawa turun";
                    }
                }
                $item->save();
            }

            $clear = count($missing) === 0;
            $now = Carbon::now();

            $locked->update([
                'status' => 'returned',
                'gate_down_by' => $officerId,
                'gate_down_at' => $now,
                'gate_down_notes' => trim(($notes ?? '').($missing ? "\nSelisih: ".implode('; ', $missing) : '')) ?: null,
                'gate_down_clear' => $clear,
                'last_seen_pos' => 'BASECAMP',
                'last_seen_direction' => 'descending',
                'last_seen_at' => $now,
            ]);

            SimaksiCheckpoint::create([
                'simaksi_registration_id' => $locked->id,
                'pos_code' => 'BASECAMP',
                'direction' => 'descending',
                'ranger_id' => $officerId,
                'entry_source' => 'scanner',
                'notes' => $clear ? 'Tiba di gerbang. Logistik & alat lengkap (CLEAR).' : 'Tiba di gerbang dengan selisih logistik/alat.',
                'scanned_at' => $now,
            ]);

            AuditService::log(
                action: 'GATE_DESCENT_CHECKED',
                entityType: 'SimaksiRegistration',
                entityId: $locked->id,
                description: "Gate: {$locked->registration_code} turun. ".($clear ? 'Logistik lengkap (CLEAR).' : 'Ada selisih: '.implode('; ', $missing)).' Diteruskan ke kasir untuk penyelesaian.',
                reason: $notes,
                payload: ['clear' => $clear, 'missing' => $missing]
            );

            return $locked;
        });
    }

    /**
     * Kasir: selesaikan rombongan setelah turun (status → completed, KTP dimusnahkan).
     */
    public function finalize(SimaksiRegistration $registration, int $cashierId, ?string $notes = null): SimaksiRegistration
    {
        if ($registration->fresh()->status !== 'returned') {
            throw new \RuntimeException("Rombongan {$registration->registration_code} belum melewati pemeriksaan turun di Gate.");
        }

        $locked = $this->checkInService->checkOut(
            registration: $registration,
            officerId: $cashierId,
            trashVerified: (bool) $registration->gate_down_clear,
            notes: $notes,
        );

        $locked->update([
            'finalized_by' => $cashierId,
            'finalized_at' => Carbon::now(),
        ]);

        return $locked;
    }
}
