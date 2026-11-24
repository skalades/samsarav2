<?php

namespace App\Support;

use App\Models\OjekTrip;
use App\Models\SimaksiRegistration;
use App\Models\User;
use App\Models\WhatsappMessage;

/**
 * Menu per level akses. Sidebar (desktop & drawer) + bottom nav (mobile).
 * Setiap item: label, route, match (pola routeIs), icon, badge (opsional).
 */
class Navigation
{
    protected static ?array $badges = null;

    public static function badges(): array
    {
        if (self::$badges !== null) {
            return self::$badges;
        }

        $counts = SimaksiRegistration::selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');
        $pending = collect(SimaksiRegistration::STATUS_PENDING_GROUP)->sum(fn ($s) => (int) ($counts[$s] ?? 0));

        return self::$badges = [
            'simaksi' => $pending,
            'desk' => (int) ($counts['gate_cleared'] ?? 0) + (int) ($counts['returned'] ?? 0),
            'gate_up' => (int) ($counts['paid'] ?? 0),
            'gate_down' => (int) ($counts['checked_in'] ?? 0),
            'ojek' => OjekTrip::where('trip_status', 'voucher_issued')->count(),
            'wa_failed' => WhatsappMessage::where('status', 'failed')->count(),
        ];
    }

    public static function item(string $label, string $route, string $icon, ?string $match = null, ?string $badge = null, array $params = []): array
    {
        return compact('label', 'route', 'icon', 'match', 'badge', 'params');
    }

    /**
     * @return array<int, array{title:string, items:array}>
     */
    public static function sidebar(User $user): array
    {
        $i = fn (...$a) => self::item(...$a);

        return match ($user->level()) {
            User::ROLE_SUPERADMIN => [
                ['title' => 'Ringkasan', 'items' => [
                    $i('Dashboard', 'management.dashboard', 'grid'),
                ]],
                ['title' => 'Alur Pendaki', 'items' => [
                    $i('Verifikasi SIMAKSI', 'management.simaksi.index', 'ticket', 'management.simaksi.*', 'simaksi'),
                    $i('Antrean Kasir', 'management.desk.index', 'inbox', 'management.desk.*', 'desk'),
                    $i('Gate Naik / Turun', 'management.gate.index', 'gate', 'management.gate.*'),
                    $i('Pos Ranger', 'management.ranger.index', 'pin', 'management.ranger.*'),
                    $i('Kuota & Kalender', 'management.quota.index', 'calendar', 'management.quota.*'),
                ]],
                ['title' => 'Kasir & Logistik', 'items' => [
                    $i('Kasir POS', 'management.pos.index', 'cash', 'management.pos.index'),
                    $i('Riwayat Transaksi', 'management.pos.history', 'receipt', 'management.pos.history'),
                    $i('Kas Keluar', 'management.pos.expenses.index', 'wallet', 'management.pos.expenses.*'),
                    $i('Inventori & Sewa', 'management.inventory.index', 'box', 'management.inventory.*'),
                ]],
                ['title' => 'Ojek Resmi', 'items' => [
                    $i('Dispatch Ojek', 'management.ojek.index', 'bolt', 'management.ojek.index', 'ojek'),
                    $i('Data Driver', 'management.ojek.drivers', 'users', 'management.ojek.drivers'),
                    $i('Rekonsiliasi Payout', 'management.ojek.settlements', 'wallet', 'management.ojek.settlements'),
                ]],
                ['title' => 'Analitik', 'items' => [
                    $i('Laporan Keuangan', 'management.reports.index', 'chart', 'management.reports.*'),
                    $i('Audit Log', 'management.audit.index', 'shield', 'management.audit.*'),
                ]],
                ['title' => 'Pengaturan', 'items' => [
                    $i('Petugas & Akses', 'management.users.index', 'user', 'management.users.*'),
                    $i('Tarif & Kebijakan', 'management.settings.tariffs', 'settings', 'management.settings.tariffs*'),
                    $i('Checklist Gate', 'management.settings.inspection', 'checklist', 'management.settings.inspection*'),
                    $i('WhatsApp Gateway', 'management.settings.whatsapp', 'whatsapp', 'management.settings.whatsapp*', 'wa_failed'),
                ]],
            ],
            User::ROLE_KASIR => [
                ['title' => 'Meja Kasir', 'items' => [
                    $i('Beranda Kasir', 'management.dashboard', 'home'),
                    $i('Verifikasi SIMAKSI', 'management.simaksi.index', 'ticket', 'management.simaksi.*', 'simaksi'),
                    $i('Antrean dari Gate', 'management.desk.index', 'inbox', 'management.desk.*', 'desk'),
                ]],
                ['title' => 'Transaksi & Kas', 'items' => [
                    $i('Kasir POS', 'management.pos.index', 'cash', 'management.pos.index'),
                    $i('Riwayat Transaksi', 'management.pos.history', 'receipt', 'management.pos.history'),
                    $i('Kas Keluar', 'management.pos.expenses.index', 'wallet', 'management.pos.expenses.*'),
                ]],
                ['title' => 'Layanan', 'items' => [
                    $i('Sewa Alat', 'management.inventory.index', 'box', 'management.inventory.*'),
                    $i('Dispatch Ojek', 'management.ojek.index', 'bolt', 'management.ojek.index', 'ojek'),
                    $i('Data Driver', 'management.ojek.drivers', 'users', 'management.ojek.drivers'),
                    $i('Payout Ojek', 'management.ojek.settlements', 'wallet', 'management.ojek.settlements'),
                ]],
                ['title' => 'Informasi', 'items' => [
                    $i('Kuota & Kalender', 'management.quota.index', 'calendar', 'management.quota.*'),
                    $i('Laporan Keuangan', 'management.reports.index', 'chart', 'management.reports.*'),
                ]],
            ],
            User::ROLE_GATEKEEPER => [
                ['title' => 'Gate Basecamp', 'items' => [
                    $i('Siap Naik', 'management.gate.index', 'arrow-up', null, 'gate_up', ['tab' => 'naik']),
                    $i('Di Jalur / Turun', 'management.gate.index', 'arrow-down', null, 'gate_down', ['tab' => 'jalur']),
                    $i('Menunggu Kasir', 'management.gate.index', 'inbox', null, 'desk', ['tab' => 'kasir']),
                ]],
            ],
            default => [
                ['title' => 'Pos Ranger', 'items' => [
                    $i('Scanner Pos', 'management.ranger.index', 'scan', 'management.ranger.*'),
                ]],
            ],
        };
    }

    /**
     * Bottom nav mobile (maks. 4 item + tombol Menu bila perlu).
     */
    public static function bottom(User $user): array
    {
        $i = fn (...$a) => self::item(...$a);

        return match ($user->level()) {
            User::ROLE_SUPERADMIN => [
                $i('Ringkasan', 'management.dashboard', 'grid'),
                $i('SIMAKSI', 'management.simaksi.index', 'ticket', 'management.simaksi.*', 'simaksi'),
                $i('Antrean', 'management.desk.index', 'inbox', 'management.desk.*', 'desk'),
                $i('Laporan', 'management.reports.index', 'chart', 'management.reports.*'),
            ],
            User::ROLE_KASIR => [
                $i('Beranda', 'management.dashboard', 'home'),
                $i('SIMAKSI', 'management.simaksi.index', 'ticket', 'management.simaksi.*', 'simaksi'),
                $i('Antrean', 'management.desk.index', 'inbox', 'management.desk.*', 'desk'),
                $i('POS', 'management.pos.index', 'cash', 'management.pos.index'),
            ],
            User::ROLE_GATEKEEPER => [
                $i('Naik', 'management.gate.index', 'arrow-up', null, 'gate_up', ['tab' => 'naik']),
                $i('Scan', 'management.gate.index', 'scan', null, null, ['scan' => 1]),
                $i('Turun', 'management.gate.index', 'arrow-down', null, 'gate_down', ['tab' => 'jalur']),
                $i('Kasir', 'management.gate.index', 'inbox', null, 'desk', ['tab' => 'kasir']),
            ],
            default => [],
        };
    }

    public static function isActive(array $item): bool
    {
        $request = request();

        if (! empty($item['params']['tab'])) {
            return $request->routeIs($item['route']) && $request->query('tab', 'naik') === $item['params']['tab'] && ! $request->has('scan');
        }
        if (! empty($item['params']['scan'])) {
            return $request->routeIs($item['route']) && $request->has('scan');
        }

        return $request->routeIs($item['match'] ?? $item['route']);
    }

    public static function needsDrawer(User $user): bool
    {
        return in_array($user->level(), [User::ROLE_SUPERADMIN, User::ROLE_KASIR], true);
    }
}
