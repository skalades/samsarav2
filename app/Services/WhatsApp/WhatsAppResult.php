<?php

namespace App\Services\WhatsApp;

final class WhatsAppResult
{
    public function __construct(
        public readonly bool $success,
        public readonly string $status,
        public readonly ?string $response = null,
    ) {}

    public static function sent(?string $response = null): self
    {
        return new self(true, 'sent', $response);
    }

    public static function failed(?string $response = null): self
    {
        return new self(false, 'failed', $response);
    }

    /**
     * Gateway belum dikonfigurasi: pesan hanya dicatat (tidak dikirim).
     */
    public static function skipped(?string $response = null): self
    {
        return new self(true, 'skipped', $response);
    }
}
