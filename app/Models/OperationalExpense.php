<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OperationalExpense extends Model
{
    use HasFactory;

    protected $fillable = [
        'expense_code',
        'cashier_id',
        'category',
        'amount',
        'recipient_name',
        'payment_method',
        'notes',
        'receipt_photo_path',
        'status',
        'voided_by',
        'voided_at',
        'void_reason',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'voided_at' => 'datetime',
    ];

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function voidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'voided_by');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'bbm_genset' => 'BBM Genset & Listrik',
            'operasional_pos' => 'Operasional Pos & ATK',
            'logistik_sar' => 'P3K & Logistik Darurat SAR',
            'konsumsi_petugas' => 'Konsumsi Ranger & Posko',
            'kebersihan_sampah' => 'Kantong Sampah & Kebersihan',
            'perbaikan_alat' => 'Pemeliharaan Jalur & Alat',
            default => 'Kebutuhan Mendadak Lainnya',
        };
    }
}
