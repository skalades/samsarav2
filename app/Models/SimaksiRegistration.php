<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SimaksiRegistration extends Model
{
    use HasFactory;

    protected $fillable = [
        'registration_code',
        'leader_name',
        'leader_nik',
        'leader_phone',
        'leader_email',
        'leader_city',
        'leader_address',
        'leader_ktp_path',
        'medical_conditions',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_relationship',
        'hiking_date',
        'return_date',
        'total_climbers',
        'hiking_category',
        'planned_campsite',
        'camp_location',
        'last_seen_pos',
        'last_seen_direction',
        'last_seen_at',
        'status',
        'revision_notes',
        'rejection_reason',
        'reviewed_by',
        'reviewed_at',
        'checked_in_by',
        'checked_in_at',
        'checked_out_at',
        'simaksi_fee',
        'ojek_fee',
        'ojek_count',
        'tent_fee',
        'tent_count',
        'has_vehicle',
        'motorcycle_count',
        'car_count',
        'parking_fee',
        'unique_code',
        'total_amount',
        'payment_status',
        'payment_method',
        'payment_proof_path',
        'group_photo_path',
        'group_photo_uploaded_at',
        'paid_at',
        'trash_deposit_amount',
        'trash_deposit_status',
        'trash_bag_code',
    ];

    protected $casts = [
        'hiking_date' => 'date',
        'return_date' => 'date',
        'reviewed_at' => 'datetime',
        'checked_in_at' => 'datetime',
        'checked_out_at' => 'datetime',
        'paid_at' => 'datetime',
        'group_photo_uploaded_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'simaksi_fee' => 'decimal:2',
        'ojek_fee' => 'decimal:2',
        'ojek_count' => 'integer',
        'tent_fee' => 'decimal:2',
        'tent_count' => 'integer',
        'has_vehicle' => 'boolean',
        'motorcycle_count' => 'integer',
        'car_count' => 'integer',
        'parking_fee' => 'decimal:2',
        'unique_code' => 'integer',
        'total_amount' => 'decimal:2',
        'trash_deposit_amount' => 'decimal:2',
    ];

    public function members(): HasMany
    {
        return $this->hasMany(ClimberMember::class);
    }

    public function ojekTrips(): HasMany
    {
        return $this->hasMany(OjekTrip::class, 'simaksi_registration_id');
    }

    public function posOrders(): HasMany
    {
        return $this->hasMany(PosOrder::class, 'simaksi_registration_id');
    }

    public function checkpoints(): HasMany
    {
        return $this->hasMany(SimaksiCheckpoint::class, 'simaksi_registration_id')->orderBy('scanned_at', 'desc');
    }

    public function isTektok(): bool
    {
        return $this->hiking_category === 'tektok';
    }

    public function isCamp(): bool
    {
        return $this->hiking_category === 'camp';
    }

    public function getCategoryBadgeAttribute(): array
    {
        if ($this->isTektok()) {
            return [
                'bg' => 'bg-cyan-500/15 border-cyan-500/40 text-cyan-300',
                'label' => 'TEKTOK (03:00 - 07:00)',
                'icon' => '[TEKTOK]',
            ];
        }

        return [
            'bg' => 'bg-emerald-500/15 border-emerald-500/40 text-emerald-300',
            'label' => 'CAMP (BERMALAM)',
            'icon' => '[CAMP]',
        ];
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function checkInOfficer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_in_by');
    }

    public function getLeaderKtpUrlAttribute(): ?string
    {
        // Berkas KTP disimpan privat di local disk demi UU PDP; hanya diakses via route management.simaksi.ktp
        return null;
    }

    public function getPaymentProofUrlAttribute(): ?string
    {
        return $this->payment_proof_path ? asset('storage/'.$this->payment_proof_path) : null;
    }

    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'paid' => ['bg' => 'bg-emerald-500/10 border-emerald-500/30 text-emerald-400', 'label' => 'PAID / LUNAS'],
            'checked_in' => ['bg' => 'bg-[#FF5722]/10 border-[#FF5722]/30 text-[#FF5722]', 'label' => 'CHECKED-IN (DI JALUR)'],
            'completed' => ['bg' => 'bg-stone-500/10 border-stone-500/30 text-stone-300', 'label' => 'SELESAI (TURUN)'],
            'rejected' => ['bg' => 'bg-rose-500/10 border-rose-500/30 text-rose-400', 'label' => 'DITOLAK / BATAL'],
            default => ['bg' => 'bg-amber-500/10 border-amber-500/30 text-amber-400', 'label' => 'PENDING (MENUNGGU PEMBAYARAN)'],
        };
    }
}
