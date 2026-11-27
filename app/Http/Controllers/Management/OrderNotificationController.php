<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\SimaksiRegistration;
use App\Models\User;
use App\Support\Navigation;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Umpan notifikasi operasional lintas divisi (polling real-time per role).
 *
 * Klien mengirim `since` (cursor waktu server dari respons sebelumnya) dan menerima
 * event yang relevan dengan divisinya:
 *  - Kasir (+ Superadmin):
 *      - order_created         : booking baru dari portal publik
 *      - proof_uploaded        : pendaki unggah bukti bayar
 *      - gate_ascent_cleared   : Gatekeeper meloloskan rombongan naik → cetak Surat Jalan
 *      - gate_descent_returned : Gatekeeper memeriksa rombongan turun → selesaikan & izin parkir
 *      - gate_descent_issue    : Gatekeeper melaporkan selisih logistik saat turun
 *  - Gatekeeper (+ Superadmin):
 *      - simaksi_paid          : Kasir konfirmasi lunas → siap periksa naik di Gate
 *  - Ranger (+ Superadmin):
 *      - group_released        : Kasir memberangkatkan rombongan → pantau di pos jalur
 *
 * Tanpa `since`, hanya cursor + badge yang dikembalikan (inisialisasi tanpa banjir notifikasi lama).
 */
class OrderNotificationController extends Controller
{
    private const LIMIT = 20;

    public function feed(Request $request): JsonResponse
    {
        $now = Carbon::now();
        $since = $this->parseSince($request->query('since'), $now);
        $user = $request->user();
        $role = $user?->level();

        $events = [];

        if ($since && $role) {
            $isSuperadmin = $role === User::ROLE_SUPERADMIN;

            if ($isSuperadmin || $role === User::ROLE_KASIR) {
                $events = array_merge($events, $this->eventsForCashier($since));
            }

            if ($isSuperadmin || $role === User::ROLE_GATEKEEPER) {
                $events = array_merge($events, $this->eventsForGatekeeper($since));
            }

            if ($isSuperadmin || $role === User::ROLE_RANGER) {
                $events = array_merge($events, $this->eventsForRanger($since));
            }

            usort($events, fn ($a, $b) => strcmp($b['at'], $a['at']));
        }

        $badges = Navigation::badges();

        return response()->json([
            'cursor' => $now->toIso8601String(),
            'role' => $role,
            'events' => array_slice($events, 0, self::LIMIT),
            'badges' => [
                'simaksi' => (int) ($badges['simaksi'] ?? 0),
                'desk' => (int) ($badges['desk'] ?? 0),
                'gate_up' => (int) ($badges['gate_up'] ?? 0),
                'gate_down' => (int) ($badges['gate_down'] ?? 0),
                'ojek' => (int) ($badges['ojek'] ?? 0),
                'wa_failed' => (int) ($badges['wa_failed'] ?? 0),
            ],
        ])->header('Cache-Control', 'no-store, private');
    }

    /**
     * Event untuk divisi Kasir: pesanan baru, bukti transfer, serta kiriman Gatekeeper (naik & turun).
     */
    private function eventsForCashier(Carbon $since): array
    {
        $events = [];

        $created = SimaksiRegistration::query()
            ->where('created_at', '>=', $since)
            ->latest('created_at')
            ->limit(self::LIMIT)
            ->get();

        foreach ($created as $reg) {
            $events[] = $this->event(
                reg: $reg,
                type: 'order_created',
                title: 'Pesanan baru masuk',
                at: $reg->created_at,
                url: route('management.simaksi.show', $reg->id),
                includePrice: true,
            );
        }

        $proofs = SimaksiRegistration::query()
            ->where('updated_at', '>=', $since)
            ->where('created_at', '<', $since)
            ->whereNotNull('payment_proof_path')
            ->where('payment_status', 'paid')
            ->whereIn('status', SimaksiRegistration::STATUS_PENDING_GROUP)
            ->latest('updated_at')
            ->limit(self::LIMIT)
            ->get();

        foreach ($proofs as $reg) {
            $events[] = $this->event(
                reg: $reg,
                type: 'proof_uploaded',
                title: 'Bukti transfer masuk',
                at: $reg->updated_at,
                url: route('management.simaksi.show', $reg->id),
                includePrice: true,
            );
        }

        // Kiriman Gatekeeper (naik): lolos pemeriksaan alat & logistik → siap cetak Surat Jalan
        $gateUp = SimaksiRegistration::query()
            ->where('status', 'gate_cleared')
            ->whereNotNull('gate_up_at')
            ->where('gate_up_at', '>=', $since)
            ->latest('gate_up_at')
            ->limit(self::LIMIT)
            ->get();

        foreach ($gateUp as $reg) {
            $extra = $reg->ojek_count > 0 ? "Ojek {$reg->ojek_count}x · siap cetak surat jalan" : 'Siap cetak surat jalan';
            $events[] = $this->event(
                reg: $reg,
                type: 'gate_ascent_cleared',
                title: 'Kiriman Gate · Siap berangkat',
                at: $reg->gate_up_at,
                url: route('management.desk.show', $reg->id),
                extra: $extra,
            );
        }

        // Kiriman Gatekeeper (turun): selesai hitung ulang logistik → siap diselesaikan & izin parkir
        $gateDown = SimaksiRegistration::query()
            ->where('status', 'returned')
            ->whereNotNull('gate_down_at')
            ->where('gate_down_at', '>=', $since)
            ->latest('gate_down_at')
            ->limit(self::LIMIT)
            ->get();

        foreach ($gateDown as $reg) {
            $isClear = (bool) $reg->gate_down_clear;
            $events[] = $this->event(
                reg: $reg,
                type: $isClear ? 'gate_descent_returned' : 'gate_descent_issue',
                title: $isClear ? 'Kiriman Gate · Sudah turun' : 'Kiriman Gate · Selisih logistik',
                at: $reg->gate_down_at,
                url: route('management.desk.show', $reg->id),
                extra: $isClear ? 'Logistik lengkap · siap izin parkir' : 'Ada selisih logistik saat turun',
            );
        }

        return $events;
    }

    /**
     * Event untuk divisi Gatekeeper: tiket sudah dikonfirmasi lunas oleh Kasir dan siap diperiksa naik.
     */
    private function eventsForGatekeeper(Carbon $since): array
    {
        $events = [];

        $paid = SimaksiRegistration::query()
            ->where('status', 'paid')
            ->whereNotNull('paid_at')
            ->where('paid_at', '>=', $since)
            ->latest('paid_at')
            ->limit(self::LIMIT)
            ->get();

        foreach ($paid as $reg) {
            $category = $reg->isTektok() ? 'Tektok' : 'Camp';
            $events[] = $this->event(
                reg: $reg,
                type: 'simaksi_paid',
                title: 'Dari Kasir · Lunas siap naik',
                at: $reg->paid_at,
                url: route('management.gate.ascent', $reg->id),
                extra: "{$category} · siap periksa Gate",
            );
        }

        return $events;
    }

    /**
     * Event untuk divisi Ranger: rombongan resmi diberangkatkan dari Basecamp oleh Kasir.
     */
    private function eventsForRanger(Carbon $since): array
    {
        $events = [];

        $released = SimaksiRegistration::query()
            ->where('status', 'checked_in')
            ->whereNotNull('released_at')
            ->where('released_at', '>=', $since)
            ->latest('released_at')
            ->limit(self::LIMIT)
            ->get();

        foreach ($released as $reg) {
            $category = $reg->isTektok() ? 'Tektok' : 'Camp';
            $events[] = $this->event(
                reg: $reg,
                type: 'group_released',
                title: 'Rombongan mulai mendaki',
                at: $reg->released_at,
                url: route('management.ranger.index', ['code' => $reg->registration_code]),
                extra: "{$category} · berangkat dari Basecamp",
            );
        }

        return $events;
    }

    private function parseSince(mixed $raw, Carbon $now): ?Carbon
    {
        if (! is_string($raw) || $raw === '') {
            return null;
        }

        try {
            $since = Carbon::parse($raw);
        } catch (\Throwable) {
            return null;
        }

        // Batasi jendela agar klien yang lama tertidur tidak memicu banjir notifikasi
        $floor = $now->copy()->subHours(6);

        return $since->lessThan($floor) ? $floor : $since->min($now);
    }

    private function event(
        SimaksiRegistration $reg,
        string $type,
        string $title,
        ?Carbon $at,
        string $url,
        bool $includePrice = false,
        ?string $extra = null,
    ): array {
        $parts = ["{$reg->leader_name} · {$reg->total_climbers} pendaki"];

        if ($extra) {
            $parts[] = $extra;
        } elseif ($date = $reg->hiking_date?->translatedFormat('d M Y')) {
            $parts[] = $date;
        }

        if ($includePrice) {
            $parts[] = 'Rp '.number_format((int) $reg->total_amount, 0, ',', '.');
        }

        return [
            'key' => $type.':'.$reg->id.':'.($at?->timestamp ?? 0),
            'type' => $type,
            'title' => $title,
            'body' => implode(' · ', $parts),
            'code' => $reg->registration_code,
            'url' => $url,
            'at' => ($at ?? Carbon::now())->toIso8601String(),
        ];
    }
}
