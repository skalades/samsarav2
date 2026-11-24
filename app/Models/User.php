<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Empat level akses resmi SAMSARA OPS.
     */
    public const ROLE_SUPERADMIN = 'superadmin';

    public const ROLE_KASIR = 'kasir';

    public const ROLE_GATEKEEPER = 'gatekeeper';

    public const ROLE_RANGER = 'ranger';

    public const ROLES = [
        self::ROLE_SUPERADMIN => [
            'label' => 'Superadmin',
            'description' => 'Kendali penuh: kuota, tarif, petugas, laporan, audit.',
        ],
        self::ROLE_KASIR => [
            'label' => 'Kasir Basecamp',
            'description' => 'Verifikasi pembayaran, kasir POS, kas, sewa alat, cetak dokumen.',
        ],
        self::ROLE_GATEKEEPER => [
            'label' => 'Gatekeeper',
            'description' => 'Arahkan pendaki, periksa alat, logistik & foto tim saat naik/turun.',
        ],
        self::ROLE_RANGER => [
            'label' => 'Ranger Pos',
            'description' => 'Scan rombongan di Pos 1–3 & puncak, laporan lapangan.',
        ],
    ];

    /**
     * Peran lama dipetakan ke 4 level baru (data lama & kompatibilitas).
     */
    public const LEGACY_ROLE_MAP = [
        'admin' => self::ROLE_SUPERADMIN,
        'auditor' => self::ROLE_SUPERADMIN,
        'verifikator' => self::ROLE_KASIR,
        'cashier' => self::ROLE_KASIR,
        'inventori' => self::ROLE_KASIR,
        'gate_officer' => self::ROLE_GATEKEEPER,
        'petugas_lapangan' => self::ROLE_GATEKEEPER,
    ];

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'status',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public static function normalizeRole(?string $role): string
    {
        $role = (string) $role;

        return self::LEGACY_ROLE_MAP[$role] ?? (array_key_exists($role, self::ROLES) ? $role : self::ROLE_RANGER);
    }

    /**
     * Peran efektif (4 level) dari akun ini.
     */
    public function level(): string
    {
        return self::normalizeRole($this->role);
    }

    public function roleLabel(): string
    {
        return self::ROLES[$this->level()]['label'];
    }

    public function hasRole(string|array $roles): bool
    {
        $roles = array_map([self::class, 'normalizeRole'], (array) $roles);

        return in_array($this->level(), $roles, true);
    }

    public function isSuperadmin(): bool
    {
        return $this->level() === self::ROLE_SUPERADMIN;
    }

    public function isAdmin(): bool
    {
        return $this->isSuperadmin();
    }

    public function isKasir(): bool
    {
        return $this->level() === self::ROLE_KASIR;
    }

    public function isGatekeeper(): bool
    {
        return $this->level() === self::ROLE_GATEKEEPER;
    }

    public function isRanger(): bool
    {
        return $this->level() === self::ROLE_RANGER;
    }

    public function isActive(): bool
    {
        return ($this->status ?? 'active') === 'active';
    }

    /**
     * Halaman beranda sesuai level.
     */
    public function homeRoute(): string
    {
        return match ($this->level()) {
            self::ROLE_GATEKEEPER => 'management.gate.index',
            self::ROLE_RANGER => 'management.ranger.index',
            default => 'management.dashboard',
        };
    }
}
