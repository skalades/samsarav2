<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OjekDriver extends Model
{
    use HasFactory;

    protected $fillable = [
        'registration_number',
        'name',
        'plate_number',
        'phone',
        'status',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function trips(): HasMany
    {
        return $this->hasMany(OjekTrip::class, 'driver_id');
    }

    public function settlements(): HasMany
    {
        return $this->hasMany(OjekSettlement::class, 'driver_id');
    }

    /**
     * Unsettled completed trips waiting for cash payout.
     */
    public function unsettledTrips()
    {
        return $this->trips()
            ->where('trip_status', 'completed')
            ->where('settlement_status', 'unsettled');
    }

    /**
     * Total unsettled earnings waiting for payout.
     */
    public function getPendingPayoutAttribute(): float
    {
        return (float) $this->unsettledTrips()->sum('driver_share');
    }

    /**
     * Status UI badge presentation.
     */
    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'standby' => [
                'label' => 'STANDBY (ANTRE)',
                'bg' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30',
                'dot' => 'bg-emerald-400',
            ],
            'on_trip' => [
                'label' => 'SEDANG JALAN',
                'bg' => 'bg-[#FF5722]/10 text-[#FF5722] border-[#FF5722]/30',
                'dot' => 'bg-[#FF5722]',
            ],
            'suspended' => [
                'label' => 'DISUSPEN',
                'bg' => 'bg-red-500/10 text-red-400 border-red-500/30',
                'dot' => 'bg-red-400',
            ],
            default => [
                'label' => 'OFF DUTY',
                'bg' => 'bg-stone-500/10 text-stone-400 border-stone-500/30',
                'dot' => 'bg-stone-400',
            ],
        };
    }
}
