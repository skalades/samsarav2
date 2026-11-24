<?php

/*
|--------------------------------------------------------------------------
| SAMSARA — Nilai awal identitas, kontak & pembayaran
|--------------------------------------------------------------------------
|
| Nilai di sini HANYA dipakai bila superadmin belum mengisinya di
| Pengaturan → Tarif & Profil (tabel app_settings). Sengaja tidak ada
| nilai contoh/dummy untuk rekening, QRIS, dan nomor kontak: lebih baik
| tampil kosong daripada pendaki mentransfer ke rekening yang salah.
| Jalankan `php artisan samsara:preflight` sebelum go-live.
|
*/

return [
    'contact' => [
        'admin_whatsapp' => env('SAMSARA_ADMIN_WHATSAPP', ''),
        'admin_whatsapp_display' => env('SAMSARA_ADMIN_WHATSAPP_DISPLAY', ''),
        'emergency_hotline' => env('SAMSARA_EMERGENCY_HOTLINE', ''),
        'sar_frequency' => env('SAMSARA_SAR_FREQUENCY', ''),
        'basecamp_maps_url' => env('SAMSARA_BASECAMP_MAPS_URL', ''),
    ],

    'payment' => [
        'bank_name' => env('SAMSARA_BANK_NAME', ''),
        'bank_account' => env('SAMSARA_BANK_ACCOUNT', ''),
        'bank_holder' => env('SAMSARA_BANK_HOLDER', ''),
        'qris_nmid' => env('SAMSARA_QRIS_NMID', ''),
        'qris_name' => env('SAMSARA_QRIS_NAME', ''),
    ],

    // Akun superadmin awal (SuperAdminSeeder). Password kosong → dibuat acak & ditampilkan sekali.
    'superadmin' => [
        'email' => env('SUPERADMIN_EMAIL', 'admin@samsara.id'),
        'name' => env('SUPERADMIN_NAME', 'Super Admin SAMSARA'),
        'password' => env('SUPERADMIN_PASSWORD', ''),
        'phone' => env('SUPERADMIN_PHONE', ''),
    ],

    // Kata sandi bawaan lama yang tidak boleh dipakai di produksi (dicek oleh samsara:preflight).
    'forbidden_passwords' => ['samsara2026', 'password', '12345678', 'admin123'],

    // Nilai contoh dari SamsaraManagementSeeder (demo). samsara:preflight menolak bila masih terpakai di produksi.
    'demo_values' => ['810-0982-111', '8100982111', 'PT SAMSARA ADVENTURE GARUT', 'ID102026110099', '6282122334455', '+62 821-2233-4455'],
];
