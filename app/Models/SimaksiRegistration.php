<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SimaksiRegistration extends Model
{
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | Alur status rombongan
    |--------------------------------------------------------------------------
    | pending → paid (Kasir verifikasi lunas)
    |   → gate_cleared (Gatekeeper: alat, logistik, foto tim OK)
    |   → checked_in  (Kasir cetak Surat Jalan & Voucher Ojek — sedang mendaki)
    |   → returned    (Gatekeeper: logistik dicocokkan ulang saat turun)
    |   → completed   (Kasir menyelesaikan & cetak Izin Keluar Parkir)
    */
    public const STATUS_PENDING_GROUP = ['pending', 'submitted', 'needs_revision', 'approved'];

    public const POST_PAYMENT_STATUSES = ['paid', 'gate_cleared', 'checked_in', 'returned', 'completed'];

    public const REVENUE_STATUSES = ['approved', 'paid', 'gate_cleared', 'checked_in', 'returned', 'completed'];

    public const STATUS_LABELS = [
        'pending' => 'Menunggu pembayaran',
        'submitted' => 'Menunggu pembayaran',
        'needs_revision' => 'Perlu perbaikan berkas',
        'approved' => 'Menunggu pembayaran',
        'paid' => 'Lunas · menunggu pemeriksaan Gate',
        'gate_cleared' => 'Lolos Gate · menunggu cetak kasir',
        'checked_in' => 'Sedang mendaki',
        'returned' => 'Sudah turun · menunggu kasir',
        'completed' => 'Selesai',
        'rejected' => 'Ditolak / batal',
    ];

    public const STATUS_TONES = [
        'paid' => 'success',
        'gate_cleared' => 'info',
        'checked_in' => 'brand',
        'returned' => 'violet',
        'completed' => 'neutral',
        'rejected' => 'danger',
        'needs_revision' => 'warning',
    ];

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
        'payment_due_at',
        'trash_deposit_amount',
        'trash_deposit_status',
        'trash_bag_code',
        'gate_up_by',
        'gate_up_at',
        'gate_up_notes',
        'gate_photo_path',
        'gate_photo_verified',
        'released_by',
        'released_at',
        'gate_down_by',
        'gate_down_at',
        'gate_down_notes',
        'gate_down_clear',
        'finalized_by',
        'finalized_at',
    ];

    protected $casts = [
        'hiking_date' => 'date',
        'return_date' => 'date',
        'reviewed_at' => 'datetime',
        'checked_in_at' => 'datetime',
        'checked_out_at' => 'datetime',
        'paid_at' => 'datetime',
        'payment_due_at' => 'datetime',
        'gate_up_at' => 'datetime',
        'gate_photo_verified' => 'boolean',
        'released_at' => 'datetime',
        'gate_down_at' => 'datetime',
        'gate_down_clear' => 'boolean',
        'finalized_at' => 'datetime',
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

    /**
     * Booking publik yang belum dibayar, belum unggah bukti, dan sudah lewat batas waktu bayar.
     */
    public function scopeOverdueUnpaid($query)
    {
        return $query->where('status', 'pending')
            ->where('payment_status', '!=', 'paid')
            ->whereNull('payment_proof_path')
            ->whereNotNull('payment_due_at')
            ->where('payment_due_at', '<=', now());
    }

    public function isOverdueUnpaid(): bool
    {
        return $this->status === 'pending'
            && $this->payment_status !== 'paid'
            && $this->payment_proof_path === null
            && $this->payment_due_at !== null
            && $this->payment_due_at->lte(now());
    }

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

    public function inspectionItems(): HasMany
    {
        return $this->hasMany(SimaksiInspectionItem::class, 'simaksi_registration_id')->orderBy('id');
    }

    public function whatsappMessages(): HasMany
    {
        return $this->hasMany(WhatsappMessage::class, 'simaksi_registration_id')->latest();
    }

    public function gateUpOfficer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'gate_up_by');
    }

    public function gateDownOfficer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'gate_down_by');
    }

    public function releasedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'released_by');
    }

    public function finalizedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'finalized_by');
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? ucfirst((string) $this->status);
    }

    public function statusTone(): string
    {
        return self::STATUS_TONES[$this->status] ?? 'warning';
    }

    /**
     * Catatan medis "bermakna" (bukan kosong / "Tidak Ada" / "-").
     */
    public static function meaningfulMedical(?string $text): ?string
    {
        $clean = trim((string) $text);

        return in_array(mb_strtolower($clean), ['', '-', 'tidak ada', 'tidak', 'none', 'nihil', 'tidak ada riwayat'], true) ? null : $clean;
    }

    public function photoUrl(): ?string
    {
        $path = $this->gate_photo_path ?: $this->group_photo_path;

        return $path ? asset('storage/'.$path) : null;
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
        $bg = match ($this->status) {
            'paid' => 'bg-emerald-500/10 border-emerald-500/30 text-emerald-400',
            'gate_cleared' => 'bg-sky-500/10 border-sky-500/30 text-sky-400',
            'checked_in' => 'bg-brand/10 border-brand/30 text-brand',
            'returned' => 'bg-violet-500/10 border-violet-500/30 text-violet-300',
            'completed' => 'bg-stone-500/10 border-stone-500/30 text-stone-300',
            'rejected' => 'bg-rose-500/10 border-rose-500/30 text-rose-400',
            default => 'bg-amber-500/10 border-amber-500/30 text-amber-400',
        };

        return ['bg' => $bg, 'label' => strtoupper($this->statusLabel()), 'tone' => $this->statusTone()];
    }
}
