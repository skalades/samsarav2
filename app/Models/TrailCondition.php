<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrailCondition extends Model
{
    use HasFactory;

    protected $fillable = [
        'status',
        'pos2_water_status',
        'night_temp_celsius',
        'weather_condition',
        'soil_condition',
        'wind_speed',
        'sar_frequency',
        'ranger_notes',
        'updated_by',
    ];

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
