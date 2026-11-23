<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PosOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'cashier_id',
        'simaksi_registration_id',
        'customer_name',
        'customer_phone',
        'subtotal',
        'discount',
        'total_amount',
        'cash_tendered',
        'cash_change',
        'payment_method',
        'status',
        'void_reason',
        'voided_by',
        'voided_at',
        'notes',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'cash_tendered' => 'decimal:2',
        'cash_change' => 'decimal:2',
        'voided_at' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(PosOrderItem::class);
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function ojekTrips(): HasMany
    {
        return $this->hasMany(OjekTrip::class, 'pos_order_id');
    }

    public function simaksi(): BelongsTo
    {
        return $this->belongsTo(SimaksiRegistration::class, 'simaksi_registration_id');
    }

    public function voidOfficer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'voided_by');
    }
}
