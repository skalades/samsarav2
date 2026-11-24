<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsappMessage extends Model
{
    protected $fillable = [
        'simaksi_registration_id',
        'event',
        'phone',
        'message',
        'driver',
        'status',
        'attempts',
        'response',
        'sent_at',
        'triggered_by',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'attempts' => 'integer',
    ];

    public const EVENT_LABELS = [
        'payment_confirmed' => 'Tiket lunas & info pendakian',
        'manual' => 'Kirim ulang manual',
        'test' => 'Uji koneksi',
    ];

    public function registration(): BelongsTo
    {
        return $this->belongsTo(SimaksiRegistration::class, 'simaksi_registration_id');
    }

    public function triggeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'triggered_by');
    }

    public function statusTone(): string
    {
        return match ($this->status) {
            'sent' => 'success',
            'failed' => 'danger',
            'skipped' => 'neutral',
            default => 'warning',
        };
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'sent' => 'Terkirim',
            'failed' => 'Gagal',
            'skipped' => 'Dicatat (gateway belum aktif)',
            default => 'Dalam antrean',
        };
    }
}
