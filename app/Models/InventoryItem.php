<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku',
        'name',
        'category',
        'type',
        'price',
        'stock_total',
        'stock_available',
        'unit',
        'description',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock_total' => 'integer',
        'stock_available' => 'integer',
        'is_active' => 'boolean',
    ];
}
