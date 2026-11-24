<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SimaksiCheckpoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'simaksi_registration_id',
        'pos_code',
        'direction',
        'ranger_id',
        'entry_source',
        'notes',
        'scanned_at',
    ];

    protected $casts = [
        'scanned_at' => 'datetime',
    ];

    public function registration(): BelongsTo
    {
        return $this->belongsTo(SimaksiRegistration::class, 'simaksi_registration_id');
    }

    public function ranger(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ranger_id');
    }

    /**
     * Human-friendly pos name.
     */
    public function getPosNameAttribute(): string
    {
        return match ($this->pos_code) {
            'BASECAMP' => 'Basecamp Gerbang',
            'POS_1' => 'Pos 1 (Tanjakan EOD)',
            'POS_2' => 'Pos 2 (Batu Gendrung - Hutan Pinus)',
            'POS_3' => 'Pos 3 (Thun 30)',
            'PUNCAK' => 'Puncak Cikuray',
            default => $this->pos_code,
        };
    }

    /**
     * Human-friendly direction label.
     */
    public function getDirectionLabelAttribute(): string
    {
        return match ($this->direction) {
            'ascending' => 'Naik [↑]',
            'descending' => 'Turun [↓]',
            'camping' => 'Buka Tenda [CAMP]',
            default => strtoupper($this->direction),
        };
    }
}
