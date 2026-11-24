<?php

use App\Support\Money;

if (!function_exists('rupiah')) {
    /**
     * Format a number into standard Indonesian Rupiah ("Rp 25.000").
     */
    function rupiah(mixed $amount, bool $withPrefix = true): string
    {
        return Money::format($amount, $withPrefix);
    }
}

if (!function_exists('format_nominal')) {
    /**
     * Format a number with Indonesian thousand separators ("25.000").
     */
    function format_nominal(mixed $amount): string
    {
        return Money::formatNumber($amount);
    }
}

if (!function_exists('parse_rupiah')) {
    /**
     * Parse an Indonesian-formatted currency string to float.
     */
    function parse_rupiah(mixed $value): float
    {
        return Money::parse($value);
    }
}
