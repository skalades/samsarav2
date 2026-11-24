<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SimaksiInspectionItem extends Model
{
    public const TYPE_GEAR = 'gear';

    public const TYPE_LOGISTIC = 'logistic';

    protected $fillable = [
        'simaksi_registration_id',
        'type',
        'name',
        'unit',
        'qty_up',
        'qty_down',
        'checked_up',
        'checked_down',
        'notes',
    ];

    protected $casts = [
        'qty_up' => 'integer',
        'qty_down' => 'integer',
        'checked_up' => 'boolean',
        'checked_down' => 'boolean',
    ];

    public function registration(): BelongsTo
    {
        return $this->belongsTo(SimaksiRegistration::class, 'simaksi_registration_id');
    }

    /**
     * Selisih logistik yang tidak kembali (calon sampah tertinggal).
     */
    public function missingQty(): int
    {
        if ($this->type !== self::TYPE_LOGISTIC || $this->qty_down === null) {
            return 0;
        }

        return max(0, (int) $this->qty_up - (int) $this->qty_down);
    }

    public function isClearOnDescent(): bool
    {
        return $this->type === self::TYPE_LOGISTIC
            ? $this->missingQty() === 0 && $this->qty_down !== null
            : ! $this->checked_up || (bool) $this->checked_down;
    }
}
