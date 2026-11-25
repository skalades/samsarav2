<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\SimaksiRegistration;
use App\Support\Navigation;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Umpan notifikasi pesanan SIMAKSI untuk Sistem Manajemen (polling real-time).
 *
 * Klien mengirim `since` (cursor waktu server dari respons sebelumnya) dan menerima:
 *  - order_created : pesanan/booking baru dari portal publik
 *  - proof_uploaded: pendaki mengunggah bukti transfer untuk pesanan yang sudah ada
 * Tanpa `since`, hanya cursor + badge yang dikembalikan (inisialisasi tanpa banjir notifikasi lama).
 */
class OrderNotificationController extends Controller
{
    private const LIMIT = 20;

    public function feed(Request $request): JsonResponse
    {
        $now = Carbon::now();
        $since = $this->parseSince($request->query('since'), $now);

        $events = [];

        if ($since) {
            $created = SimaksiRegistration::query()
                ->where('created_at', '>=', $since)
                ->latest('created_at')
                ->limit(self::LIMIT)
                ->get();

            foreach ($created as $reg) {
                $events[] = $this->event($reg, 'order_created', 'Pesanan baru masuk', $reg->created_at);
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
                $events[] = $this->event($reg, 'proof_uploaded', 'Bukti transfer masuk', $reg->updated_at);
            }

            usort($events, fn ($a, $b) => strcmp($b['at'], $a['at']));
        }

        $badges = Navigation::badges();

        return response()->json([
            'cursor' => $now->toIso8601String(),
            'events' => $events,
            'badges' => [
                'simaksi' => (int) ($badges['simaksi'] ?? 0),
                'desk' => (int) ($badges['desk'] ?? 0),
            ],
        ])->header('Cache-Control', 'no-store, private');
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

    private function event(SimaksiRegistration $reg, string $type, string $title, ?Carbon $at): array
    {
        $date = $reg->hiking_date?->translatedFormat('d M Y');
        $total = 'Rp '.number_format((int) $reg->total_amount, 0, ',', '.');

        return [
            'key' => $type.':'.$reg->id.':'.($at?->timestamp ?? 0),
            'type' => $type,
            'title' => $title,
            'body' => trim("{$reg->leader_name} · {$reg->total_climbers} pendaki".($date ? " · {$date}" : '')." · {$total}"),
            'code' => $reg->registration_code,
            'url' => route('management.simaksi.show', $reg->id),
            'at' => ($at ?? Carbon::now())->toIso8601String(),
        ];
    }
}
