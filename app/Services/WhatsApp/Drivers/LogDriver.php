<?php

namespace App\Services\WhatsApp\Drivers;

use App\Services\WhatsApp\WhatsAppGateway;
use App\Services\WhatsApp\WhatsAppResult;
use Illuminate\Support\Facades\Log;

/**
 * Driver bawaan: tidak mengirim apa pun, hanya mencatat ke log aplikasi.
 * Dipakai sampai kredensial gateway resmi dipasang di .env.
 */
class LogDriver implements WhatsAppGateway
{
    public function name(): string
    {
        return 'log';
    }

    public function send(string $phone, string $message): WhatsAppResult
    {
        Log::info("[WhatsApp:log] ke {$phone}\n{$message}");

        return WhatsAppResult::skipped('Gateway belum dikonfigurasi (driver: log). Pesan hanya dicatat.');
    }
}
