<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyQuota extends Model
{
    use HasFactory;

    protected $fillable = [
        'quota_date',
        'max_quota',
        'booked_quota',
        'is_closed',
        'closure_reason',
    ];

    protected $casts = [
        'quota_date' => 'date',
        'is_closed' => 'boolean',
        'max_quota' => 'integer',
        'booked_quota' => 'integer',
    ];

    public function getRemainingQuotaAttribute(): int
    {
        if ($this->is_closed) {
            return 0;
        }

        return max(0, $this->max_quota - $this->booked_quota);
    }
}
