<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'label',
        'description',
        'type',
        'group',
    ];

    /**
     * Get a setting value with automatic type casting.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();
        if (! $setting) {
            return $default;
        }

        return match ($setting->type) {
            'number', 'float', 'integer' => is_numeric($setting->value) ? (float) $setting->value : $default,
            'boolean', 'bool' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($setting->value, true) ?? $default,
            default => $setting->value ?? $default,
        };
    }

    /**
     * Set / update a setting value.
     */
    public static function set(string $key, mixed $value, ?string $label = null, ?string $type = null, ?string $group = null): static
    {
        $stringValue = is_bool($value) ? ($value ? '1' : '0') : (string) $value;

        $setting = static::updateOrCreate(
            ['key' => $key],
            array_filter([
                'value' => $stringValue,
                'label' => $label,
                'type' => $type,
                'group' => $group,
            ], fn ($v) => ! is_null($v))
        );

        return $setting;
    }

    /**
     * Get all active tariffs and policy configs in a clean structured array.
     */
    public static function getTariffs(): array
    {
        return [
            'simaksi_fee_per_person' => (float) static::get('simaksi_fee_per_person', 25000),
            'ojek_fee_per_trip' => (float) static::get('ojek_fee_per_trip', 30000),
            'ojek_basecamp_share' => (float) static::get('ojek_basecamp_share', 5000),
            'ojek_route_name' => (string) static::get('ojek_route_name', 'Basecamp → Pos 1'),
            'porter_fee_per_trip' => (float) static::get('porter_fee_per_trip', 350000),
            'parking_motor_fee' => (float) static::get('parking_motor_fee', 10000),
            'parking_car_fee' => (float) static::get('parking_car_fee', 25000),
            'trash_deposit_enabled' => (bool) static::get('trash_deposit_enabled', false),
            'trash_deposit_amount' => (float) static::get('trash_deposit_amount', 50000),
        ];
    }

    /**
     * Get trail time-windows and checkpoint configuration.
     */
    public static function getTrailConfig(): array
    {
        return [
            'tektok_checkin_start' => (string) static::get('tektok_checkin_start', '03:00'),
            'tektok_checkin_end' => (string) static::get('tektok_checkin_end', '07:00'),
            'tektok_max_checkout_hour' => (int) static::get('tektok_max_checkout_hour', 19),
            'camp_checkin_start' => (string) static::get('camp_checkin_start', '07:00'),
            'camp_checkin_end' => (string) static::get('camp_checkin_end', '16:00'),
            'checkpoints' => [
                'POS_1' => ['name' => 'Pos 1 (Transit Ojek)', 'elevation' => '1.780 MDPL', 'can_camp' => false, 'water' => false,
                    'description' => 'Titik turun armada ojek trail dan pintu gerbang hutan tropis primer.'],
                'POS_2' => ['name' => 'Pos 2 (Mata Air / Camp)', 'elevation' => '2.050 MDPL', 'can_camp' => true, 'water' => true,
                    'description' => 'Mata air alami dan area camp resmi. Titik pengisian air terakhir — isi penuh botol di sini.'],
                'POS_3' => ['name' => 'Pos 3 (Camp Area Atas)', 'elevation' => '2.400 MDPL', 'can_camp' => true, 'water' => false,
                    'description' => 'Area camp resmi di bagian atas jalur, titik terakhir sebelum jalur puncak.'],
                'PUNCAK' => ['name' => 'Puncak Cikuray', 'elevation' => '2.821 MDPL', 'can_camp' => false, 'water' => false,
                    'description' => 'Titik tertinggi dengan panorama samudra awan 360°.'],
            ],
        ];
    }

    /**
     * Get full expedition and operational profile.
     */
    public static function getExpeditionProfile(): array
    {
        $bankAccount = (string) static::get('payment_bank_account', '810-0982-111');
        $rawAccount = preg_replace('/[^0-9]/', '', $bankAccount);
        $appLogo = static::get('app_logo', null);
        $qrisImage = static::get('payment_qris_image', null);

        return [
            'app_logo' => $appLogo,
            'app_logo_url' => $appLogo ? asset('storage/'.$appLogo) : null,
            'payment_qris_image' => $qrisImage,
            'payment_qris_image_url' => $qrisImage ? asset('storage/'.$qrisImage) : null,
            'expedition_name' => (string) static::get('expedition_name', 'SAMSARA EXPEDITION'),
            'basecamp_name' => (string) static::get('basecamp_name', 'Basecamp SAMSARA'),
            'mountain_name' => (string) static::get('mountain_name', 'Gunung Cikuray'),
            'mountain_elevation' => (string) static::get('mountain_elevation', '2.821 MDPL'),
            'mountain_elevation_num' => (int) static::get('mountain_elevation_num', 2821),
            'peak_name' => (string) static::get('peak_name', 'Puncak Triangulasi Gunung Cikuray'),
            'basecamp_elevation' => (string) static::get('basecamp_elevation', '1.450 MDPL'),
            'basecamp_location' => (string) static::get('basecamp_location', 'Kecamatan Cilawu / Bayongbong, Garut, Jawa Barat'),
            'basecamp_maps_url' => (string) static::get('basecamp_maps_url', 'https://maps.app.goo.gl/xGzRkQTTArmDxpQf9'),
            'basecamp_coordinates' => (string) static::get('basecamp_coordinates', '-7.331855, 107.8096294'),
            'basecamp_latitude' => (string) static::get('basecamp_latitude', '-7.331855'),
            'basecamp_longitude' => (string) static::get('basecamp_longitude', '107.8096294'),
            'default_daily_quota' => (int) static::get('default_daily_quota', 150),
            'min_climbers_per_group' => (int) static::get('min_climbers_per_group', 1),
            'max_climbers_per_group' => (int) static::get('max_climbers_per_group', 15),
            'admin_whatsapp' => (string) static::get('admin_whatsapp', '6282122334455'),
            'admin_whatsapp_display' => (string) static::get('admin_whatsapp_display', '+62 821-2233-4455'),
            'emergency_hotline' => (string) static::get('emergency_hotline', '+62 821-2233-4455'),
            'sar_frequency' => (string) static::get('sar_frequency', '149.250 MHz'),
            'payment_bank_name' => (string) static::get('payment_bank_name', 'BANK CENTRAL ASIA (BCA)'),
            'payment_bank_account' => $bankAccount,
            'payment_bank_account_raw' => (string) static::get('payment_bank_account_raw', $rawAccount ?: '8100982111'),
            'payment_bank_holder' => (string) static::get('payment_bank_holder', 'PT SAMSARA ADVENTURE GARUT'),
            'payment_qris_nmid' => (string) static::get('payment_qris_nmid', 'ID102026110099'),
            'payment_qris_name' => (string) static::get('payment_qris_name', 'PT SAMSARA ADVENTURE GARUT'),
        ];
    }

    /**
     * Get full public URL for uploaded logo, or null.
     */
    public static function getLogoUrl(): ?string
    {
        $logo = static::get('app_logo');
        if (! $logo) {
            return null;
        }

        return asset('storage/'.$logo);
    }

    /**
     * Get full public URL for uploaded static QRIS image, or null.
     */
    public static function getQrisUrl(): ?string
    {
        $qris = static::get('payment_qris_image');
        if (! $qris) {
            return null;
        }

        return asset('storage/'.$qris);
    }
}
