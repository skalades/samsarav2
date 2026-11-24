<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\ClimberMember;
use App\Models\SimaksiRegistration;
use Carbon\Carbon;

/**
 * Aturan anti-penyalahgunaan booking publik (kuota dipenuhi booking palsu).
 * Semua batas dapat diatur lewat AppSetting tanpa deploy ulang.
 */
class BookingGuardService
{
    /** Status yang dianggap masih "memakai" kuota / NIK pada tanggal pendakian. */
    public const ACTIVE_STATUSES = ['pending', 'submitted', 'needs_revision', 'approved', 'paid', 'gate_cleared', 'checked_in', 'returned'];

    public function holdMinutes(): int
    {
        return max(15, (int) AppSetting::get('booking_payment_hold_minutes', 120));
    }

    public function cashPosCutoffHour(): int
    {
        return min(23, max(0, (int) AppSetting::get('booking_cash_pos_cutoff_hour', 12)));
    }

    public function maxDaysAhead(): int
    {
        return max(1, (int) AppSetting::get('booking_max_days_ahead', 60));
    }

    public function maxPendingPerPhone(): int
    {
        return max(1, (int) AppSetting::get('booking_max_pending_per_phone', 2));
    }

    /**
     * QRIS/Transfer: harus dibayar (atau unggah bukti) dalam N menit.
     * Bayar tunai di Pos: kuota ditahan sampai jam cutoff di hari pendakian,
     * minimal N menit dari sekarang.
     */
    public function paymentDueAt(string $paymentMethod, Carbon $hikingDate): Carbon
    {
        $minimum = now()->addMinutes($this->holdMinutes());

        if ($paymentMethod === 'cash_pos') {
            $cutoff = $hikingDate->copy()->setTime($this->cashPosCutoffHour(), 0);

            return $cutoff->greaterThan($minimum) ? $cutoff : $minimum;
        }

        return $minimum;
    }

    /** Normalisasi nomor HP Indonesia ke format 62xxxxxxxxxx untuk pembandingan. */
    public static function normalizePhone(?string $phone): string
    {
        $digits = preg_replace('/\D/', '', (string) $phone);
        if (str_starts_with($digits, '0')) {
            $digits = '62'.substr($digits, 1);
        } elseif (str_starts_with($digits, '8')) {
            $digits = '62'.$digits;
        }

        return $digits;
    }

    /**
     * Kembalikan pesan error (string) bila booking melanggar aturan, atau null bila aman.
     *
     * @param  array<int,string>  $memberNiks
     */
    public function violation(string $leaderNik, array $memberNiks, string $leaderPhone, Carbon $hikingDate, Carbon $returnDate): ?string
    {
        // 1. NIK ganda di dalam satu rombongan (anggota pertama = ketua, boleh sama dengan leader_nik)
        $memberNiks = array_map('trim', $memberNiks);
        if (count($memberNiks) !== count(array_unique($memberNiks))) {
            return 'Terdapat NIK yang sama lebih dari sekali dalam daftar anggota rombongan.';
        }

        $allNiks = array_values(array_unique(array_merge([trim($leaderNik)], $memberNiks)));
        $from = $hikingDate->toDateString();
        $to = $returnDate->toDateString();

        // Booking lain yang masih aktif & tanggalnya beririsan (booking kedaluwarsa tidak dihitung)
        $overlapping = fn ($q) => $q->whereIn('status', self::ACTIVE_STATUSES)
            ->whereDate('hiking_date', '<=', $to)
            ->whereRaw('COALESCE(return_date, hiking_date) >= ?', [$from])
            ->whereNot(fn ($w) => $w->overdueUnpaid());

        // 2. NIK sudah terdaftar di rombongan lain pada tanggal yang beririsan
        $dupNik = ClimberMember::whereIn('nik', $allNiks)
            ->whereHas('registration', $overlapping)
            ->value('nik')
            ?? SimaksiRegistration::whereIn('leader_nik', $allNiks)->where($overlapping)->value('leader_nik');

        if ($dupNik) {
            return 'NIK '.self::maskNik($dupNik).' sudah terdaftar pada SIMAKSI lain yang aktif di tanggal tersebut. '
                .'Satu orang hanya boleh terdaftar di satu rombongan per tanggal pendakian.';
        }

        // 3. Batas booking belum bayar per nomor WhatsApp ketua
        $phone = self::normalizePhone($leaderPhone);
        if (strlen($phone) >= 10) {
            $pendingForPhone = SimaksiRegistration::where('status', 'pending')
                ->where('payment_status', '!=', 'paid')
                ->where('leader_phone', 'like', '%'.substr($phone, -9))
                ->whereNot(fn ($w) => $w->overdueUnpaid())
                ->pluck('leader_phone')
                ->filter(fn ($p) => self::normalizePhone($p) === $phone)
                ->count();

            if ($pendingForPhone >= $this->maxPendingPerPhone()) {
                return 'Nomor WhatsApp ini masih memiliki '.$pendingForPhone.' pendaftaran yang belum dibayar. '
                    .'Selesaikan pembayaran atau tunggu pendaftaran lama kedaluwarsa sebelum mendaftar lagi.';
            }
        }

        return null;
    }

    public static function maskNik(string $nik): string
    {
        return strlen($nik) <= 8 ? str_repeat('*', strlen($nik)) : substr($nik, 0, 4).'********'.substr($nik, -4);
    }
}
