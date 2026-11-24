<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\InventoryItem;

/**
 * Authoritative Centralized Service for SAMSARA Expedition Tariffs and Fee Calculations.
 *
 * Menerapkan prinsip Single Source of Truth (SSOT) agar aturan kalkulasi tarif
 * SIMAKSI, ojek trail, parkir kendaraan, dan deposit sampah tidak terduplikasi.
 */
class TariffCalculatorService
{
    /**
     * Get active tariff rates from AppSetting with catalog/default fallbacks.
     */
    public function getRates(): array
    {
        $tariffs = AppSetting::getTariffs();

        return [
            'simaksi_rate' => (float) ($tariffs['simaksi_fee_per_person'] ?? (InventoryItem::where('sku', 'SRV-SIMAKSI')->value('price') ?? 25000)),
            'ojek_rate' => (float) ($tariffs['ojek_fee_per_trip'] ?? (InventoryItem::where('sku', 'SRV-OJEK')->value('price') ?? 30000)),
            'motorcycle_rate' => (float) ($tariffs['parking_motor_fee'] ?? (InventoryItem::where('sku', 'PRK-MOTOR')->value('price') ?? 10000)),
            'car_rate' => (float) ($tariffs['parking_car_fee'] ?? (InventoryItem::where('sku', 'PRK-MOBIL')->value('price') ?? 25000)),
            'trash_deposit_enabled' => (bool) ($tariffs['trash_deposit_enabled'] ?? false),
            'trash_deposit_amount' => (float) ($tariffs['trash_deposit_amount'] ?? 50000),
        ];
    }

    /**
     * Calculate comprehensive bill breakdown for SIMAKSI registration.
     */
    public function calculate(
        int $totalClimbers,
        int $ojekCount = 0,
        bool $hasVehicle = false,
        int $motorcycleCount = 0,
        int $carCount = 0,
        int $uniqueCode = 0
    ): array {
        $rates = $this->getRates();

        $simaksiFee = max(0, $totalClimbers) * $rates['simaksi_rate'];
        $ojekCountClean = max(0, $ojekCount);
        $ojekFee = $ojekCountClean * $rates['ojek_rate'];

        $motorCountClean = $hasVehicle ? max(0, $motorcycleCount) : 0;
        $carCountClean = $hasVehicle ? max(0, $carCount) : 0;
        $parkingFee = ($motorCountClean * $rates['motorcycle_rate']) + ($carCountClean * $rates['car_rate']);

        $subtotal = $simaksiFee + $ojekFee + $parkingFee;
        $totalAmount = $subtotal + max(0, $uniqueCode);

        $trashDepositStatus = $rates['trash_deposit_enabled'] ? 'unpaid' : 'waived';
        $trashDepositAmount = $rates['trash_deposit_enabled'] ? $rates['trash_deposit_amount'] : 0.0;

        return [
            'rates' => $rates,
            'total_climbers' => $totalClimbers,
            'simaksi_fee' => $simaksiFee,
            'ojek_count' => $ojekCountClean,
            'ojek_fee' => $ojekFee,
            'has_vehicle' => $hasVehicle,
            'motorcycle_count' => $motorCountClean,
            'car_count' => $carCountClean,
            'parking_fee' => $parkingFee,
            'subtotal' => $subtotal,
            'unique_code' => $uniqueCode,
            'total_amount' => $totalAmount,
            'trash_deposit_enabled' => $rates['trash_deposit_enabled'],
            'trash_deposit_amount' => $trashDepositAmount,
            'trash_deposit_status' => $trashDepositStatus,
        ];
    }
}
