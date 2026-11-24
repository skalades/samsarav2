<?php

namespace App\Services\WhatsApp\Drivers;

use App\Services\WhatsApp\WhatsAppGateway;
use App\Services\WhatsApp\WhatsAppResult;
use Illuminate\Support\Facades\Http;

/**
 * Webhook generik — untuk gateway mandiri (mis. server Baileys / WA Business API proxy).
 * POST JSON {phone, message} dengan header Authorization: Bearer <token>. Sukses = HTTP 2xx.
 */
class WebhookDriver implements WhatsAppGateway
{
    public function __construct(
        protected string $endpoint,
        protected ?string $token = null,
        protected int $timeout = 10,
    ) {}

    public function name(): string
    {
        return 'webhook';
    }

    public function send(string $phone, string $message): WhatsAppResult
    {
        $request = Http::acceptJson()->timeout($this->timeout);
        if ($this->token) {
            $request = $request->withToken($this->token);
        }

        $response = $request->post($this->endpoint, [
            'phone' => $phone,
            'message' => $message,
        ]);

        return $response->successful()
            ? WhatsAppResult::sent($response->body())
            : WhatsAppResult::failed($response->body());
    }
}
