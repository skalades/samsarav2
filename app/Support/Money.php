<?php

namespace App\Support;

class Money
{
    /**
     * Format a numeric value as Indonesian Rupiah string: e.g. "Rp 25.000" or "-Rp 10.000".
     */
    public static function format(mixed $amount, bool $withPrefix = true): string
    {
        if ($amount === null || $amount === '') {
            $amount = 0;
        }

        $num = (float) $amount;
        $isNegative = $num < 0;
        $abs = abs($num);

        $formatted = number_format($abs, 0, ',', '.');

        if ($withPrefix) {
            return ($isNegative ? '-Rp ' : 'Rp ') . $formatted;
        }

        return ($isNegative ? '-' : '') . $formatted;
    }

    /**
     * Format a numeric value with Indonesian thousand separators (no "Rp " prefix): e.g. "25.000".
     */
    public static function formatNumber(mixed $amount): string
    {
        return self::format($amount, false);
    }

    /**
     * Parse an Indonesian-formatted currency string or raw number to float:
     * e.g. "Rp 25.000" -> 25000.0, "1.500.000" -> 1500000.0.
     */
    public static function parse(mixed $value): float
    {
        if ($value === null || $value === '') {
            return 0.0;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        $str = (string) $value;
        $isNegative = str_contains($str, '-');

        // Extract numbers and comma
        $clean = preg_replace('/[^\d,]/', '', $str);
        if ($clean === '' || $clean === null) {
            return 0.0;
        }

        // If comma exists, replace with dot
        $clean = str_replace(',', '.', $clean);
        $float = (float) $clean;

        return $isNegative ? -$float : $float;
    }

    /**
     * Sanitize a value that may contain Indonesian currency formatting:
     * e.g. "25.000" -> 25000, "Rp 150.000" -> 150000.
     */
    public static function sanitize(mixed $value): mixed
    {
        if (!is_string($value)) {
            return $value;
        }

        $trimmed = trim($value);

        // Strict matches for Indonesian currency formats:
        // "Rp 25.000", "Rp. 25.000", "25.000", "1.250.000", "-Rp 50.000", "-50.000"
        if (preg_match('/^-?\s*(?:Rp\.?\s*)?\d{1,3}(?:\.\d{3})+(?:,\d+)?$/i', $trimmed)) {
            $isNegative = str_starts_with($trimmed, '-');
            $clean = preg_replace('/[^\d,]/', '', $trimmed);
            $clean = str_replace(',', '.', (string) $clean);
            $float = (float) $clean;
            return $isNegative ? -$float : $float;
        }

        // Explicit "Rp" with plain digits, e.g. "Rp 50000"
        if (preg_match('/^-?\s*Rp\.?\s*\d+$/i', $trimmed)) {
            $isNegative = str_starts_with($trimmed, '-');
            $clean = preg_replace('/[^\d]/', '', $trimmed);
            $float = (float) $clean;
            return $isNegative ? -$float : $float;
        }

        return $value;
    }
}
