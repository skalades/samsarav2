<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\SimaksiCheckpoint;
use App\Models\SimaksiRegistration;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CheckpointTrackingService
{
    /**
     * Record a checkpoint scan or manual entry for a climbing group.
     */
    public function recordCheckpoint(
        SimaksiRegistration $registration,
        string $posCode,
        string $direction,
        int $rangerId,
        string $source = 'scanner',
        ?string $notes = null
    ): SimaksiCheckpoint {
        return DB::transaction(function () use ($registration, $posCode, $direction, $rangerId, $source, $notes) {
            $locked = SimaksiRegistration::where('id', $registration->id)->lockForUpdate()->firstOrFail();

            // Safety Policy: Tektok climbers CANNOT camp anywhere
            if ($locked->isTektok() && $direction === 'camping') {
                throw new \InvalidArgumentException('Pelanggaran Regulasi: Pendaki kategori Tektok tidak diizinkan mendirikan tenda.');
            }

            // Safety Policy: Camping ONLY permitted at official campsites (POS_2 and POS_3)
            if ($direction === 'camping' && ! in_array($posCode, ['POS_2', 'POS_3'])) {
                throw new \InvalidArgumentException("Lokasi camp tidak valid: Area camp resmi SAMSARA hanya berada di Pos 2 dan Pos 3 (Lokasi: {$posCode} dilarang).");
            }

            $now = Carbon::now();

            $checkpoint = SimaksiCheckpoint::create([
                'simaksi_registration_id' => $locked->id,
                'pos_code' => strtoupper($posCode),
                'direction' => strtolower($direction),
                'ranger_id' => $rangerId,
                'entry_source' => $source,
                'notes' => $notes,
                'scanned_at' => $now,
            ]);

            $updateData = [
                'last_seen_pos' => strtoupper($posCode),
                'last_seen_direction' => strtolower($direction),
                'last_seen_at' => $now,
            ];

            if ($direction === 'camping') {
                $updateData['camp_location'] = strtoupper($posCode);
            }

            $locked->update($updateData);

            AuditService::log(
                action: 'CHECKPOINT_RECORDED',
                entityType: 'SimaksiRegistration',
                entityId: $locked->id,
                description: "Checkpoint {$posCode} ({$direction}) dicatat via {$source} untuk {$locked->registration_code}.",
                payload: [
                    'pos_code' => $posCode,
                    'direction' => $direction,
                    'entry_source' => $source,
                    'notes' => $notes,
                    'ranger_id' => $rangerId,
                ]
            );

            return $checkpoint;
        });
    }

    /**
     * Intelligently suggest whether a climber is going UP or DOWN at a given pos.
     */
    public function suggestDirection(SimaksiRegistration $registration, string $currentPos): string
    {
        $posHierarchy = [
            'BASECAMP' => 0,
            'POS_1' => 1,
            'POS_2' => 2,
            'POS_3' => 3,
            'PUNCAK' => 4,
        ];

        $currentPosOrder = $posHierarchy[strtoupper($currentPos)] ?? 1;

        // If the registration is already marked as camp at this pos
        if ($registration->camp_location === strtoupper($currentPos)) {
            return 'camping';
        }

        // Check recent checkpoints history
        $lastLog = $registration->checkpoints()->first();
        if (! $lastLog) {
            return 'ascending'; // First checkpoint after gate is ascending
        }

        $lastPosOrder = $posHierarchy[$lastLog->pos_code] ?? 0;

        // If currently descending, keep descending
        if ($lastLog->direction === 'descending') {
            return 'descending';
        }

        // If current pos is lower than last recorded pos, user is heading DOWN
        if ($currentPosOrder < $lastPosOrder) {
            return 'descending';
        }

        // If at the highest camping pos (POS_3) and staying, suggest camping if category is camp
        if ($registration->isCamp() && in_array(strtoupper($currentPos), ['POS_2', 'POS_3']) && ! $registration->camp_location) {
            return 'camping';
        }

        return 'ascending';
    }

    /**
     * Check if a registration is considered overdue based on category and time limits.
     */
    public function isOverdue(SimaksiRegistration $registration): bool
    {
        if ($registration->status !== 'checked_in') {
            return false;
        }

        $now = Carbon::now();

        // 1. Tektok Overdue: Not checked out by max tektok return time (default 19:00 WIB)
        if ($registration->isTektok()) {
            $cutoffHour = (int) AppSetting::get('tektok_max_checkout_hour', 19);
            if ($now->hour >= $cutoffHour) {
                return true;
            }
        }

        // 2. Trail Sector Gap: If last seen > 3.5 hours ago without subsequent checkpoint
        if ($registration->last_seen_at) {
            $hoursSinceLastSeen = $registration->last_seen_at->diffInHours($now);
            // If they are moving (not camping) and haven't touched any checkpoint in > 3.5 hours
            if ($registration->last_seen_direction !== 'camping' && $hoursSinceLastSeen >= 3) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get live summary of all active groups on the mountain for Basecamp Radar.
     */
    public function getActiveMountainRadar(): array
    {
        $activeGroups = SimaksiRegistration::with(['members', 'checkpoints'])
            ->where('status', 'checked_in')
            ->orderBy('last_seen_at', 'desc')
            ->get();

        $byPos = [
            'BASECAMP' => [],
            'POS_1' => [],
            'POS_2' => [],
            'POS_3' => [],
            'PUNCAK' => [],
        ];

        $counts = [
            'total_active_climbers' => 0,
            'tektok_count' => 0,
            'camp_count' => 0,
            'overdue_count' => 0,
            'pos2_tents' => 0,
            'pos3_tents' => 0,
        ];

        foreach ($activeGroups as $reg) {
            $counts['total_active_climbers'] += $reg->total_climbers;
            if ($reg->isTektok()) {
                $counts['tektok_count'] += $reg->total_climbers;
            } else {
                $counts['camp_count'] += $reg->total_climbers;
            }

            $posKey = $reg->last_seen_pos ?: 'BASECAMP';
            if (! isset($byPos[$posKey])) {
                $byPos[$posKey] = [];
            }

            $isOverdue = $this->isOverdue($reg);
            if ($isOverdue) {
                $counts['overdue_count']++;
            }

            if ($reg->camp_location === 'POS_2') {
                $counts['pos2_tents'] += max(1, (int) $reg->tent_count);
            } elseif ($reg->camp_location === 'POS_3') {
                $counts['pos3_tents'] += max(1, (int) $reg->tent_count);
            }

            $byPos[$posKey][] = [
                'registration' => $reg,
                'is_overdue' => $isOverdue,
            ];
        }

        return [
            'active_groups' => $activeGroups,
            'by_pos' => $byPos,
            'counts' => $counts,
        ];
    }
}
