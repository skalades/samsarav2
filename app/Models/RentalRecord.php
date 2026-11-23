<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RentalRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_item_id',
        'simaksi_registration_id',
        'pos_order_id',
        'customer_name',
        'item_name',
        'quantity',
        'condition_out',
        'handed_out_by',
        'handed_out_at',
        'condition_in',
        'returned_to',
        'returned_at',
        'status',
        'damage_penalty',
        'notes',
    ];

    protected $casts = [
        'handed_out_at' => 'datetime',
        'returned_at' => 'datetime',
        'quantity' => 'integer',
        'damage_penalty' => 'decimal:2',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }

    public function simaksi(): BelongsTo
    {
        return $this->belongsTo(SimaksiRegistration::class, 'simaksi_registration_id');
    }

    public function handedOfficer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handed_out_by');
    }

    public function returnedOfficer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'returned_to');
    }
}
