<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OjekTrip extends Model
{
    use HasFactory;

    protected $fillable = [
        'trip_code',
        'simaksi_registration_id',
        'pos_order_id',
        'driver_id',
        'passenger_name',
        'passenger_phone',
        'route',
        'fare_amount',
        'driver_share',
        'basecamp_share',
        'payment_status',
        'trip_status',
        'settlement_status',
        'ojek_settlement_id',
        'dispatched_at',
        'completed_at',
        'created_by',
    ];

    protected $casts = [
        'fare_amount' => 'decimal:2',
        'driver_share' => 'decimal:2',
        'basecamp_share' => 'decimal:2',
        'dispatched_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function driver(): BelongsTo
    {
        return $this->belongsTo(OjekDriver::class, 'driver_id');
    }

    public function simaksi(): BelongsTo
    {
        return $this->belongsTo(SimaksiRegistration::class, 'simaksi_registration_id');
    }

    public function posOrder(): BelongsTo
    {
        return $this->belongsTo(PosOrder::class, 'pos_order_id');
    }

    public function settlement(): BelongsTo
    {
        return $this->belongsTo(OjekSettlement::class, 'ojek_settlement_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getStatusBadgeAttribute(): array
    {
        return match ($this->trip_status) {
            'voucher_issued' => [
                'label' => 'VOUCHER TERBIT (ANTRE)',
                'bg' => 'bg-amber-500/10 text-amber-400 border-amber-500/30',
            ],
            'assigned' => [
                'label' => 'DRIVER DITUGASKAN',
                'bg' => 'bg-cyan-500/10 text-cyan-400 border-cyan-500/30',
            ],
            'on_trip' => [
                'label' => 'SEDANG DI JALUR',
                'bg' => 'bg-[#FF5722]/10 text-[#FF5722] border-[#FF5722]/30',
            ],
            'completed' => [
                'label' => 'SELESAI (TIBA DI POS)',
                'bg' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30',
            ],
            'cancelled' => [
                'label' => 'DIBATALKAN',
                'bg' => 'bg-red-500/10 text-red-400 border-red-500/30',
            ],
            default => [
                'label' => strtoupper($this->trip_status),
                'bg' => 'bg-stone-500/10 text-stone-400 border-stone-500/30',
            ],
        };
    }
}
