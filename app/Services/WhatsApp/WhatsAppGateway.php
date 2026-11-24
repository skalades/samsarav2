<?php

namespace App\Services\WhatsApp;

/**
 * Kontrak driver WhatsApp Gateway. Tambah penyedia baru cukup dengan
 * mengimplementasikan interface ini lalu mendaftarkannya di WhatsAppManager.
 */
interface WhatsAppGateway
{
    public function name(): string;

    /**
     * Kirim pesan teks ke nomor format internasional (62xxxxxxxxxx).
     */
    public function send(string $phone, string $message): WhatsAppResult;
}
