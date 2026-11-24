<?php

namespace App\Jobs;

use App\Models\WhatsappMessage;
use App\Services\WhatsApp\WhatsAppManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Kirim satu pesan WhatsApp lewat gateway aktif, dengan retry bertahap.
 */
class SendWhatsAppMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public int $messageId) {}

    public function backoff(): array
    {
        return [30, 120, 600];
    }

    public function handle(WhatsAppManager $manager): void
    {
        $record = WhatsappMessage::find($this->messageId);
        if (! $record || $record->status === 'sent') {
            return;
        }

        $gateway = $manager->driver();
        $record->increment('attempts');

        try {
            $result = $gateway->send($record->phone, $record->message);
        } catch (\Throwable $e) {
            Log::warning("WhatsApp #{$record->id} gagal: ".$e->getMessage());
            $record->update(['driver' => $gateway->name(), 'status' => 'failed', 'response' => mb_substr($e->getMessage(), 0, 2000)]);

            if ($this->attempts() < $this->tries) {
                $this->release($this->backoff()[$this->attempts() - 1] ?? 600);
            }

            return;
        }

        $record->update([
            'driver' => $gateway->name(),
            'status' => $result->status,
            'response' => $result->response ? mb_substr($result->response, 0, 2000) : null,
            'sent_at' => $result->status === 'sent' ? now() : $record->sent_at,
        ]);

        if (! $result->success && $this->attempts() < $this->tries) {
            $this->release($this->backoff()[$this->attempts() - 1] ?? 600);
        }
    }
}
