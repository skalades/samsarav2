<?php

namespace App\Services\WhatsApp\Drivers;

use App\Services\WhatsApp\WhatsAppGateway;
use App\Services\WhatsApp\WhatsAppResult;
use Illuminate\Support\Facades\Http;

/**
 * Wablas — endpoint domain server Anda, mis. https://tegal.wablas.com
 * Header Authorization: <token>[.<secret>], body: phone, message.
 */
class WablasDriver implements WhatsAppGateway
{
    public function __construct(
        protected string $endpoint,
        protected string $token,
        protected ?string $secret = null,
        protected int $timeout = 10,
    ) {}

    public function name(): string
    {
        return 'wablas';
    }

    public function send(string $phone, string $message): WhatsAppResult
    {
        $auth = $this->secret ? "{$this->token}.{$this->secret}" : $this->token;
        $url = rtrim($this->endpoint, '/').'/api/send-message';

        $response = Http::asForm()
            ->withHeaders(['Authorization' => $auth])
            ->timeout($this->timeout)
            ->post($url, [
                'phone' => $phone,
                'message' => $message,
            ]);

        $body = $response->body();
        $ok = $response->successful() && (bool) data_get($response->json(), 'status', false);

        return $ok ? WhatsAppResult::sent($body) : WhatsAppResult::failed($body);
    }
}
