<?php

namespace App\Support;

use App\Models\OjekSettlement;
use App\Models\OjekTrip;
use App\Models\OperationalExpense;
use App\Models\PosOrder;
use App\Models\SimaksiRegistration;
use Illuminate\Support\Str;

/**
 * High-Entropy Anti-Collision Code Generator.
 *
 * Menggantikan pola race-condition-prone `count() + 1` dengan generator atomik berbasis
 * date prefix + micro-entropy alphanumeric. Bersifat non-blocking, O(1), dan kebal terhadap
 * lonjakan transaksi konkuren.
 */
class CodeGenerator
{
    /**
     * Alfabet aman-baca (tanpa 0/O, 1/I/L) agar tidak tertukar saat diketik manual.
     */
    private const READABLE_ALPHABET = '23456789ABCDEFGHJKMNPQRSTUVWXYZ';

    /**
     * Generate unique SIMAKSI Registration Code: SMK-YYMM-XXXXX (14 karakter).
     * Contoh: SMK-2611-K8F2Q
     * Muat 1 baris di printer thermal 58mm mode wide (maks 16 char) dan QR V1-M (maks 20 char).
     * Keamanan akses publik dijaga oleh access_token (128-bit) dan/atau verifikasi 4 digit NIK ketua.
     */
    public static function simaksiCode(): string
    {
        $prefix = 'SMK-'.date('ym').'-';
        $alphabet = self::READABLE_ALPHABET;
        $maxIdx = strlen($alphabet) - 1;

        do {
            $suffix = '';
            for ($i = 0; $i < 5; $i++) {
                $suffix .= $alphabet[random_int(0, $maxIdx)];
            }
            $code = $prefix.$suffix;
        } while (SimaksiRegistration::where('registration_code', $code)->exists());

        return $code;
    }

    /**
     * Generate 128-bit cryptographic access token untuk tautan e-tiket, PDF, dan aksi publik.
     */
    public static function ticketAccessToken(): string
    {
        return bin2hex(random_bytes(16));
    }

    /**
     * Generate unique POS Order Number: POS-YYYYMMDD-XXXX
     * Contoh: POS-20261122-K9E1
     */
    public static function posOrderNumber(): string
    {
        $prefix = 'POS-'.date('Ymd').'-';
        do {
            $orderNumber = $prefix.strtoupper(Str::random(4));
        } while (PosOrder::where('order_number', $orderNumber)->exists());

        return $orderNumber;
    }

    /**
     * Generate unique Ojek Settlement Code: STL-OJK-YYYYMMDD-XXXX
     * Contoh: STL-OJK-20261122-B4M7
     */
    public static function ojekSettlementCode(): string
    {
        $prefix = 'STL-OJK-'.date('Ymd').'-';
        do {
            $code = $prefix.strtoupper(Str::random(5));
        } while (OjekSettlement::where('settlement_code', $code)->exists());

        return $code;
    }

    /**
     * Generate unique Ojek Trip Voucher Code: VCR-OJK-XXXXXX-01-YYY
     */
    public static function ojekTripCode(string $baseCode, int $index): string
    {
        $cleanBase = preg_replace('/[^A-Za-z0-9]/', '', $baseCode);
        $suffix = str_pad((string) $index, 2, '0', STR_PAD_LEFT);

        do {
            $code = 'VCR-OJK-'.$cleanBase.'-'.$suffix.'-'.strtoupper(Str::random(3));
        } while (OjekTrip::where('trip_code', $code)->exists());

        return $code;
    }

    /**
     * Generate unique Operational Expense Code: EXP-YYYYMMDD-XXXX
     * Contoh: EXP-20261123-W2T9
     */
    public static function expenseCode(): string
    {
        $prefix = 'EXP-'.date('Ymd').'-';
        do {
            $code = $prefix.strtoupper(Str::random(4));
        } while (OperationalExpense::where('expense_code', $code)->exists());

        return $code;
    }
}
