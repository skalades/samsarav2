<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClimberMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'simaksi_registration_id',
        'full_name',
        'nik',
        'phone',
        'gender',
        'city_origin',
        'medical_conditions',
        'is_leader',
        'is_checked_in',
    ];

    protected $casts = [
        'is_leader' => 'boolean',
        'is_checked_in' => 'boolean',
    ];

    public function registration(): BelongsTo
    {
        return $this->belongsTo(SimaksiRegistration::class, 'simaksi_registration_id');
    }
}
