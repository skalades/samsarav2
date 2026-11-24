<?php

namespace App\Support;

use App\Models\AppSetting;

/**
 * Daftar baku pemeriksaan Gate (hybrid):
 *  - gear     : alat wajib, cukup dicentang (ada / tidak)
 *  - logistic : barang berpotensi jadi sampah, dihitung jumlahnya saat naik & dicocokkan saat turun
 * Bisa diubah superadmin di Pengaturan → Checklist Gate.
 */
class InspectionTemplate
{
    public const SETTING_KEY = 'inspection_template';

    public const DEFAULT_GEAR = [
        'Tenda',
        'Sleeping bag / matras',
        'Jaket hangat & jas hujan',
        'Headlamp / senter + baterai',
        'Obat-obatan pribadi / P3K',
        'Kompor & nesting',
        'Kantong sampah (trash bag)',
    ];

    public const DEFAULT_LOGISTIC = [
        ['name' => 'Botol air mineral plastik', 'unit' => 'botol'],
        ['name' => 'Bungkus mi instan', 'unit' => 'bungkus'],
        ['name' => 'Kaleng (sarden / kornet / minuman)', 'unit' => 'kaleng'],
        ['name' => 'Tabung gas portable', 'unit' => 'tabung'],
        ['name' => 'Bungkus makanan ringan / snack', 'unit' => 'bungkus'],
        ['name' => 'Tisu basah / kemasan sachet', 'unit' => 'pak'],
    ];

    /**
     * @return array{gear: array<int,string>, logistic: array<int,array{name:string,unit:string}>}
     */
    public static function get(): array
    {
        $stored = AppSetting::get(self::SETTING_KEY);
        if (is_string($stored)) {
            $stored = json_decode($stored, true);
        }

        $gear = array_values(array_filter(array_map('trim', (array) ($stored['gear'] ?? self::DEFAULT_GEAR))));
        $logistic = collect((array) ($stored['logistic'] ?? self::DEFAULT_LOGISTIC))
            ->map(fn ($row) => [
                'name' => trim((string) (is_array($row) ? ($row['name'] ?? '') : $row)),
                'unit' => trim((string) (is_array($row) ? ($row['unit'] ?? 'pcs') : 'pcs')) ?: 'pcs',
            ])
            ->filter(fn ($row) => $row['name'] !== '')
            ->values()
            ->all();

        return ['gear' => $gear, 'logistic' => $logistic];
    }

    public static function save(array $gear, array $logistic): void
    {
        AppSetting::set(
            self::SETTING_KEY,
            json_encode(['gear' => array_values($gear), 'logistic' => array_values($logistic)], JSON_UNESCAPED_UNICODE),
            'Checklist Pemeriksaan Gate',
            'json',
            'policy'
        );
    }
}
