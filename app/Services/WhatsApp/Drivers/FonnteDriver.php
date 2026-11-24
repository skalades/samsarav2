<?php

namespace App\Services\WhatsApp\Drivers;

use App\Services\WhatsApp\WhatsAppGateway;
use App\Services\WhatsApp\WhatsAppResult;
use Illuminate\Support\Facades\Http;

/**
 * Fonnte (https://fonnte.com) — header Authorization: <token>, body form: target, message.
 */
class FonnteDriver implements WhatsAppGateway
{
    public function __construct(
        protected string $token,
        protected string $endpoint = 'https://api.fonnte.com/send',
        protected int $timeout = 10,
    ) {}

    public function name(): string
    {
        return 'fonnte';
    }

    public function send(string $phone, string $message): WhatsAppResult
    {
        $response = Http::asForm()
            ->withHeaders(['Authorization' => $this->token])
            ->timeout($this->timeout)
            ->post($this->endpoint, [
                'target' => $phone,
                'message' => $message,
                'countryCode' => '62',
            ]);

        $body = $response->body();
        $ok = $response->successful() && (bool) data_get($response->json(), 'status', false);

        return $ok ? WhatsAppResult::sent($body) : WhatsAppResult::failed($body);
    }
}
