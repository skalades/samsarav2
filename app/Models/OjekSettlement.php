<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OjekSettlement extends Model
{
    use HasFactory;

    protected $fillable = [
        'settlement_code',
        'driver_id',
        'cashier_id',
        'total_trips',
        'total_amount',
        'payment_method',
        'notes',
        'settled_at',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'settled_at' => 'datetime',
    ];

    public function driver(): BelongsTo
    {
        return $this->belongsTo(OjekDriver::class, 'driver_id');
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function trips(): HasMany
    {
        return $this->hasMany(OjekTrip::class, 'ojek_settlement_id');
    }
}
