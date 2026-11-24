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
                'POS_1' => ['name' => 'Pos 1 (Tanjakan EOD)', 'elevation' => '1.424 MDPL', 'can_camp' => false, 'water' => true,
                    'description' => 'Titik turun ojek dan awal jalur hutan. Air melimpah, toilet, mushola, dan shelter.'],
                'POS_2' => ['name' => 'Pos 2 (Batu Gendrung)', 'elevation' => '1.682 MDPL', 'can_camp' => true, 'water' => true,
                    'description' => 'Area camp resmi. Air melimpah, toilet, mushola, dan shelter.'],
                'POS_3' => ['name' => 'Pos 3 (Thun 30)', 'elevation' => '2.097 MDPL', 'can_camp' => true, 'water' => true,
                    'description' => 'Area camp yang disarankan, paling dekat ke puncak dan sumber air terakhir. Tinggalkan tenda di sini saat summit attack. Air melimpah, toilet, mushola, dan shelter.'],
                'PUNCAK' => ['name' => 'Puncak Cikuray', 'elevation' => '2.821 MDPL', 'can_camp' => false, 'water' => false,
                    'description' => 'Titik tertinggi dengan panorama samudra awan 360°. Dilarang mendirikan tenda di puncak.'],
            ],
        ];
    }

    /**
     * Titik jalur pendakian lengkap (survei GPS "TITIK POS.kmz", Okt 2026) untuk peta & rute publik.
     * Key `checkpoint` menghubungkan titik ke pos ranger (POS_1..POS_3, PUNCAK) bila ada.
     * Isi `photo` (path di storage/public) saat foto pos sudah tersedia.
     *
     * Setiap titik (kecuali basecamp) mendapat `seg` = segmen dari titik sebelumnya:
     * dist_m, gain_m, grade (%), max_grade (%), up_min, down_min, measured, dan `line`
     * (polyline jalur asli [[lat,lng,elev],...]). Jarak & garis diambil dari track GPS yang
     * sudah dirapikan (resources/data/trail-track.json); bila tidak ada, dihitung dari koordinat.
     * Isi `dist_m` / `up_min` / `down_min` pada titik untuk menimpa dengan data lapangan.
     */
    public static function getTrailRoute(): array
    {
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }

        $points = static::trailPoints();
        $track = static::trailTrack();
        $cumDist = 0; $cumUp = 0; $cumDown = 0;

        foreach ($points as $i => &$p) {
            if ($i === 0) {
                $p['seg'] = null;
                $p['cum'] = ['dist_m' => 0, 'up_min' => 0, 'down_min' => 0];
                continue;
            }
            $a = $points[$i - 1];
            $flat = static::haversine($a['lat'], $a['lng'], $p['lat'], $p['lng']);
            $gain = $p['elev'] - $a['elev'];
            $measured = $track[$i] ?? null;
            // Jarak: data lapangan > track GPS terukur > estimasi (jarak miring × faktor kelok 1,15)
            $dist = (int) ($p['dist_m'] ?? $measured['dist_m'] ?? round(sqrt($flat ** 2 + $gain ** 2) * 1.15 / 10) * 10);
            $horiz = $measured ? $dist : $flat;
            // Naismith disesuaikan pendaki ber-carrier: 3 km/jam + 60 menit tiap naik 300 m
            $up = (int) ($p['up_min'] ?? static::roundTo5($dist / 1000 / 3 * 60 + max(0, $gain) / 300 * 60));
            // Turun: ±60% waktu naik (jalur licin/terjal), minimal 10 menit
            $down = (int) ($p['down_min'] ?? max(10, static::roundTo5($up * 0.6)));
            $grade = $horiz > 0 ? (int) round($gain / $horiz * 100) : 0;
            $p['seg'] = [
                'dist_m' => $dist, 'gain_m' => $gain, 'grade' => $grade,
                'max_grade' => (int) ($measured['max_grade'] ?? $grade),
                'up_min' => $up, 'down_min' => $down,
                'measured' => (bool) $measured,
                'line' => $measured['line'] ?? [[$a['lat'], $a['lng'], $a['elev']], [$p['lat'], $p['lng'], $p['elev']]],
            ];
            $cumDist += $dist; $cumUp += $up; $cumDown += $down;
            $p['cum'] = ['dist_m' => $cumDist, 'up_min' => $cumUp, 'down_min' => $cumDown];
        }
        unset($p);

        return $cache = $points;
    }

    /** Segmen track GPS bersih, dikunci indeks titik tujuan (1 = Basecamp→Pos 1, dst). */
    private static function trailTrack(): array
    {
        $file = resource_path('data/trail-track.json');
        if (! is_file($file)) {
            return [];
        }
        $data = json_decode((string) file_get_contents($file), true);

        return is_array($data['segments'] ?? null) ? $data['segments'] : [];
    }

    private static function haversine(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $dLat = deg2rad($lat2 - $lat1); $dLng = deg2rad($lng2 - $lng1);
        $h = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return 2 * 6371000 * asin(sqrt($h));
    }

    private static function roundTo5(float $min): int
    {
        return (int) max(5, round($min / 5) * 5);
    }

    /** Titik mentah jalur dari survei GPS. */
    private static function trailPoints(): array
    {
        $posFacilities = ['Air melimpah', 'Toilet', 'Mushola', 'Shelter'];

        return [
            ['code' => 'BASECAMP', 'name' => 'Basecamp SAMSARA', 'type' => 'basecamp', 'lat' => -7.3320208, 'lng' => 107.8099483, 'elev' => 1245,
                'camp' => false, 'facilities' => ['Fasilitas lengkap'], 'checkpoint' => 'BASECAMP', 'photo' => null,
                'description' => 'Registrasi SIMAKSI, pemeriksaan gerbang, briefing ranger, parkir, dan ojek resmi ke Pos 1.'],
            ['code' => 'POS 1', 'name' => 'Tanjakan EOD', 'type' => 'pos', 'lat' => -7.3317858, 'lng' => 107.8251087, 'elev' => 1424,
                'camp' => false, 'facilities' => $posFacilities, 'checkpoint' => 'POS_1', 'photo' => null,
                'description' => 'Titik turun ojek dan awal jalur hutan.', 'ojek_min' => 15],
            ['code' => 'POS 2', 'name' => 'Batu Gendrung', 'type' => 'pos', 'lat' => -7.3294081, 'lng' => 107.8365907, 'elev' => 1682,
                'camp' => true, 'facilities' => $posFacilities, 'checkpoint' => 'POS_2', 'photo' => null,
                'description' => 'Area camp resmi, dijaga ranger. Boleh camp di sini, tapi jarak ke puncak masih jauh.'],
            ['code' => 'JEMBATAN', 'name' => 'Jembatan', 'type' => 'landmark', 'lat' => -7.3284039, 'lng' => 107.8440201, 'elev' => 1942,
                'camp' => false, 'facilities' => [], 'checkpoint' => null, 'photo' => null,
                'description' => 'Penanda jalur antara Pos 2 dan Pos 3.'],
            ['code' => 'POS 3', 'name' => 'Thun 30', 'type' => 'pos', 'lat' => -7.3273546, 'lng' => 107.8474549, 'elev' => 2097,
                'camp' => true, 'camp_recommended' => true, 'facilities' => $posFacilities, 'checkpoint' => 'POS_3', 'photo' => null,
                'description' => 'Area camp yang disarankan karena paling dekat ke puncak. Dirikan tenda dan tinggalkan perlengkapan di sini, lalu summit attack cukup membawa daypack, air, headlamp, dan jaket. Sumber air terakhir, isi penuh botol.'],
            ['code' => 'POS 4', 'name' => 'Baru Datar', 'type' => 'pos', 'lat' => -7.3261534, 'lng' => 107.8502524, 'elev' => 2257,
                'camp' => false, 'facilities' => [], 'checkpoint' => null, 'photo' => null,
                'description' => 'Pos istirahat di jalur puncak. Tidak ada sumber air, dilarang mendirikan tenda.'],
            ['code' => 'POS 5', 'name' => 'Batu Gede', 'type' => 'pos', 'lat' => -7.3244179, 'lng' => 107.8535238, 'elev' => 2488,
                'camp' => false, 'facilities' => [], 'checkpoint' => null, 'photo' => null,
                'description' => 'Pos istirahat di jalur puncak. Tidak ada sumber air, dilarang mendirikan tenda.'],
            ['code' => 'POS 6', 'name' => 'Puncak Bayangan', 'type' => 'pos', 'lat' => -7.3247499, 'lng' => 107.8576225, 'elev' => 2688,
                'camp' => false, 'facilities' => [], 'checkpoint' => null, 'photo' => null,
                'description' => 'Pos terakhir sebelum puncak. Tidak ada sumber air, dilarang mendirikan tenda.'],
            ['code' => 'PUNCAK', 'name' => 'Puncak Cikuray', 'type' => 'peak', 'lat' => -7.3225978, 'lng' => 107.8598500, 'elev' => 2821,
                'camp' => false, 'no_camp' => true, 'facilities' => [], 'checkpoint' => 'PUNCAK', 'photo' => null,
                'description' => 'Titik tertinggi dengan panorama samudra awan 360°. Dilarang mendirikan tenda di puncak, turun kembali ke Pos 3.'],
        ];
    }

    /**
     * Cek apakah publikasi frekuensi radio HT darurat SAR aktif.
     */
    public static function isSarRadioEnabled(): bool
    {
        return (bool) static::get('sar_radio_enabled', false);
    }

    /**
     * Fasilitas Basecamp SAMSARA: [icon, judul, keterangan].
     */
    public static function getBasecampFacilities(): array
    {
        $facilities = [
            ['home', 'Mushola', 'Air wudhu pegunungan'],
            ['refresh', 'Toilet & kamar mandi', 'Air bersih mengalir'],
            ['gate', 'Parkir dijaga', 'Motor & mobil, 24 jam'],
            ['shield', 'Posko SAR & P3K', 'Siaga 24 jam'],
            ['bolt', 'Ojek resmi', 'Basecamp ⇄ Pos 1'],
            ['box', 'Sewa alat', 'Tenda, matras, kompor'],
            ['users', 'Area istirahat', 'Ruang tunggu rombongan'],
        ];

        if (static::isSarRadioEnabled()) {
            $facilities[] = ['radio', 'Radio jalur', (string) static::get('sar_frequency', config('samsara.contact.sar_frequency'))];
        } else {
            $facilities[] = ['phone', 'Hotline SAR 24 Jam', (string) static::get('emergency_hotline', config('samsara.contact.emergency_hotline'))];
        }

        $facilities[] = ['checklist', 'Loket & briefing', 'Registrasi dan cek alat'];

        return $facilities;
    }

    /**
     * Get full expedition and operational profile.
     */
    public static function getExpeditionProfile(): array
    {
        $bankAccount = (string) static::get('payment_bank_account', config('samsara.payment.bank_account'));
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
            'basecamp_elevation' => (string) static::get('basecamp_elevation', '1.245 MDPL'),
            'basecamp_location' => (string) static::get('basecamp_location', 'Kecamatan Cilawu / Bayongbong, Garut, Jawa Barat'),
            'basecamp_maps_url' => (string) static::get('basecamp_maps_url', config('samsara.contact.basecamp_maps_url')),
            'basecamp_coordinates' => (string) static::get('basecamp_coordinates', '-7.331855, 107.8096294'),
            'basecamp_latitude' => (string) static::get('basecamp_latitude', '-7.331855'),
            'basecamp_longitude' => (string) static::get('basecamp_longitude', '107.8096294'),
            'default_daily_quota' => (int) static::get('default_daily_quota', 150),
            'min_climbers_per_group' => (int) static::get('min_climbers_per_group', 1),
            'max_climbers_per_group' => (int) static::get('max_climbers_per_group', 15),
            'admin_whatsapp' => (string) static::get('admin_whatsapp', config('samsara.contact.admin_whatsapp')),
            'admin_whatsapp_display' => (string) static::get('admin_whatsapp_display', config('samsara.contact.admin_whatsapp_display')),
            'emergency_hotline' => (string) static::get('emergency_hotline', config('samsara.contact.emergency_hotline')),
            'sar_radio_enabled' => static::isSarRadioEnabled(),
            'sar_frequency' => static::isSarRadioEnabled() ? (string) static::get('sar_frequency', config('samsara.contact.sar_frequency')) : null,
            'sar_frequency_raw' => (string) static::get('sar_frequency', config('samsara.contact.sar_frequency')),
            'payment_bank_name' => (string) static::get('payment_bank_name', config('samsara.payment.bank_name')),
            'payment_bank_account' => $bankAccount,
            'payment_bank_account_raw' => (string) static::get('payment_bank_account_raw', $rawAccount),
            'payment_bank_holder' => (string) static::get('payment_bank_holder', config('samsara.payment.bank_holder')),
            'payment_qris_nmid' => (string) static::get('payment_qris_nmid', config('samsara.payment.qris_nmid')),
            'payment_qris_name' => (string) static::get('payment_qris_name', config('samsara.payment.qris_name')),
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
