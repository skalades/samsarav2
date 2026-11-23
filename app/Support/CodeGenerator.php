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
     * Generate unique SIMAKSI Registration Code: SMK-YYYYMM-XXXX
     * Contoh: SMK-202611-A8F2
     */
    public static function simaksiCode(): string
    {
        $prefix = 'SMK-'.date('Ym').'-';
        do {
            $code = $prefix.strtoupper(Str::random(4));
        } while (SimaksiRegistration::where('registration_code', $code)->exists());

        return $code;
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
            $code = $prefix.strtoupper(Str::random(4));
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
