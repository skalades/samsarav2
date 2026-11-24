<?php

namespace App\Services\WhatsApp;

use App\Services\WhatsApp\Drivers\FonnteDriver;
use App\Services\WhatsApp\Drivers\LogDriver;
use App\Services\WhatsApp\Drivers\WablasDriver;
use App\Services\WhatsApp\Drivers\WebhookDriver;

/**
 * Memilih driver WhatsApp Gateway dari config/services.php (WA_GATEWAY_DRIVER).
 * Bila kredensial belum lengkap, otomatis jatuh ke LogDriver agar sistem tetap jalan.
 */
class WhatsAppManager
{
    public const DRIVERS = [
        'log' => 'Log saja (belum terhubung)',
        'fonnte' => 'Fonnte',
        'wablas' => 'Wablas',
        'webhook' => 'Webhook / Gateway mandiri',
    ];

    public function driver(): WhatsAppGateway
    {
        $config = config('services.whatsapp', []);
        $driver = $config['driver'] ?? 'log';
        $endpoint = $config['endpoint'] ?? null;
        $token = $config['token'] ?? null;
        $timeout = (int) ($config['timeout'] ?? 10);

        return match (true) {
            $driver === 'fonnte' && $token => new FonnteDriver($token, $endpoint ?: 'https://api.fonnte.com/send', $timeout),
            $driver === 'wablas' && $token && $endpoint => new WablasDriver($endpoint, $token, $config['secret'] ?? null, $timeout),
            $driver === 'webhook' && $endpoint => new WebhookDriver($endpoint, $token, $timeout),
            default => new LogDriver,
        };
    }

    /**
     * Ringkasan status konfigurasi untuk halaman pengaturan (tanpa membocorkan token).
     */
    public function status(): array
    {
        $config = config('services.whatsapp', []);
        $configured = $config['driver'] ?? 'log';
        $active = $this->driver()->name();

        return [
            'configured_driver' => $configured,
            'active_driver' => $active,
            'label' => self::DRIVERS[$active] ?? $active,
            'connected' => $active !== 'log',
            'has_endpoint' => ! empty($config['endpoint']),
            'has_token' => ! empty($config['token']),
            'auto_send_on_paid' => (bool) ($config['auto_send_on_paid'] ?? true),
            'misconfigured' => $configured !== 'log' && $active === 'log',
        ];
    }
}
