<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\DailyQuota;
use App\Models\InventoryItem;
use App\Services\AuditService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TariffSettingController extends Controller
{
    /**
     * Display tariff, policy, operational, and banking management page for Superadmin & Admin.
     */
    public function index()
    {
        $tariffs = AppSetting::getTariffs();
        $profile = AppSetting::getExpeditionProfile();

        return view('management.settings.tariffs', compact('tariffs', 'profile'));
    }

    /**
     * Update official basecamp tariffs, capacity, contacts, and payment configurations.
     */
    public function update(Request $request)
    {
        $request->validate([
            // Identity & Mountain Profile
            'app_logo' => 'nullable|image|mimes:png,jpg,jpeg,svg,webp|max:2048',
            'remove_logo' => 'nullable|in:0,1',
            'expedition_name' => 'nullable|string|max:100',
            'basecamp_name' => 'nullable|string|max:100',
            'mountain_name' => 'nullable|string|max:100',
            'mountain_elevation' => 'nullable|string|max:50',
            'mountain_elevation_num' => 'nullable|integer|min:0|max:9000',
            'peak_name' => 'nullable|string|max:100',
            'basecamp_elevation' => 'nullable|string|max:50',

            // Tariffs & Logistics
            'simaksi_fee_per_person' => 'required|numeric|min:0',
            'ojek_fee_per_trip' => 'required|numeric|min:0',
            'ojek_basecamp_share' => 'nullable|numeric|min:0',
            'ojek_route_name' => 'nullable|string|max:255',
            'porter_fee_per_trip' => 'nullable|numeric|min:0',
            'parking_motor_fee' => 'required|numeric|min:0',
            'parking_car_fee' => 'required|numeric|min:0',
            'trash_deposit_enabled' => 'nullable|in:0,1',
            'trash_deposit_amount' => 'required|numeric|min:0',

            // Capacity & Quota
            'default_daily_quota' => 'nullable|integer|min:10|max:1000',
            'sync_upcoming_quotas' => 'nullable|in:0,1',
            'min_climbers_per_group' => 'nullable|integer|min:1|max:10',
            'max_climbers_per_group' => 'nullable|integer|min:1|max:50',

            // Operational & SAR
            'admin_whatsapp' => 'nullable|string|max:30',
            'admin_whatsapp_display' => 'nullable|string|max:30',
            'emergency_hotline' => 'nullable|string|max:30',
            'sar_radio_enabled' => 'nullable|in:0,1',
            'sar_frequency' => 'nullable|string|max:50',
            'basecamp_location' => 'nullable|string|max:255',
            'basecamp_maps_url' => 'nullable|url|max:500',
            'basecamp_coordinates' => 'nullable|string|max:100',

            // Banking & QRIS
            'payment_bank_name' => 'nullable|string|max:100',
            'payment_bank_account' => 'nullable|string|max:50',
            'payment_bank_holder' => 'nullable|string|max:150',
            'payment_qris_nmid' => 'nullable|string|max:50',
            'payment_qris_name' => 'nullable|string|max:150',
            'payment_qris_image' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
            'remove_qris_image' => 'nullable|in:0,1',
        ]);

        $simaksi = (float) $request->input('simaksi_fee_per_person');
        $ojek = (float) $request->input('ojek_fee_per_trip');
        $ojekShare = (float) $request->input('ojek_basecamp_share', 5000);
        $ojekRoute = $request->input('ojek_route_name', 'Basecamp SAMSARA - Pos 1 (Perkebunan Warga)');
        $porter = (float) $request->input('porter_fee_per_trip', 350000);
        $parkMotor = (float) $request->input('parking_motor_fee');
        $parkCar = (float) $request->input('parking_car_fee');
        $trashEnabled = $request->boolean('trash_deposit_enabled');
        $trashAmount = (float) $request->input('trash_deposit_amount');

        // Handle Logo Upload / Removal
        if ($request->boolean('remove_logo')) {
            $oldLogo = AppSetting::get('app_logo');
            if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                Storage::disk('public')->delete($oldLogo);
            }
            AppSetting::where('key', 'app_logo')->delete();
        } elseif ($request->hasFile('app_logo') && $request->file('app_logo')->isValid()) {
            $oldLogo = AppSetting::get('app_logo');
            if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                Storage::disk('public')->delete($oldLogo);
            }
            $logoPath = $request->file('app_logo')->store('logos', 'public');
            AppSetting::set('app_logo', $logoPath, 'Logo Resmi Ekspedisi & Thermal Print', 'string', 'identity');
        }

        // Update Expedition & Mountain Identity
        if ($request->filled('expedition_name')) {
            AppSetting::set('expedition_name', (string) $request->input('expedition_name'), 'Nama Ekspedisi Resmi', 'string', 'identity');
        }
        if ($request->filled('basecamp_name')) {
            AppSetting::set('basecamp_name', (string) $request->input('basecamp_name'), 'Nama Basecamp Resmi', 'string', 'identity');
        }
        if ($request->filled('mountain_name')) {
            AppSetting::set('mountain_name', (string) $request->input('mountain_name'), 'Nama Gunung', 'string', 'identity');
        }
        if ($request->filled('mountain_elevation')) {
            AppSetting::set('mountain_elevation', (string) $request->input('mountain_elevation'), 'Elevasi Puncak Gunung', 'string', 'identity');
        }
        if ($request->filled('mountain_elevation_num')) {
            AppSetting::set('mountain_elevation_num', (int) $request->input('mountain_elevation_num'), 'Angka Elevasi Puncak (MDPL)', 'number', 'identity');
        }
        if ($request->filled('peak_name')) {
            AppSetting::set('peak_name', (string) $request->input('peak_name'), 'Nama Puncak Triangulasi', 'string', 'identity');
        }
        if ($request->filled('basecamp_elevation')) {
            AppSetting::set('basecamp_elevation', (string) $request->input('basecamp_elevation'), 'Elevasi Basecamp', 'string', 'identity');
        }

        // Update Tariffs & Policies
        AppSetting::set('simaksi_fee_per_person', $simaksi, 'Tarif Masuk SIMAKSI', 'number', 'tariffs');
        AppSetting::set('ojek_fee_per_trip', $ojek, 'Tarif Shuttle Ojek Trail', 'number', 'tariffs');
        AppSetting::set('ojek_basecamp_share', $ojekShare, 'Bagi Hasil Kas Jalur Ojek', 'number', 'tariffs');
        AppSetting::set('ojek_route_name', $ojekRoute, 'Nama Trayek Shuttle Ojek', 'string', 'tariffs');
        AppSetting::set('porter_fee_per_trip', $porter, 'Tarif Porter Tim Lokal', 'number', 'tariffs');
        AppSetting::set('parking_motor_fee', $parkMotor, 'Tarif Parkir Sepeda Motor', 'number', 'tariffs');
        AppSetting::set('parking_car_fee', $parkCar, 'Tarif Parkir Mobil Pribadi', 'number', 'tariffs');
        AppSetting::set('trash_deposit_enabled', $trashEnabled, 'Status Deposit Sampah', 'boolean', 'policy');
        AppSetting::set('trash_deposit_amount', $trashAmount, 'Besaran Deposit Sampah', 'number', 'policy');

        // Update Capacity & Quotas
        if ($request->filled('default_daily_quota')) {
            $newDailyQuota = (int) $request->input('default_daily_quota');
            $oldDailyQuota = (int) AppSetting::get('default_daily_quota', 150);

            AppSetting::set('default_daily_quota', $newDailyQuota, 'Kapasitas Kuota Harian Resmi', 'number', 'capacity');

            // Sinkronkan seluruh tanggal kalender mendatang yang aktif jika diaktifkan atau jika nilai berubah
            $shouldSync = $request->has('sync_upcoming_quotas')
                ? $request->boolean('sync_upcoming_quotas')
                : ($newDailyQuota !== $oldDailyQuota);

            if ($shouldSync) {
                $query = DailyQuota::where('quota_date', '>=', Carbon::today())
                    ->where('is_closed', false);

                if ($newDailyQuota !== $oldDailyQuota) {
                    $query->where('max_quota', $oldDailyQuota);
                } else {
                    $query->where('max_quota', '!=', $newDailyQuota);
                }

                $syncedCount = $query->update(['max_quota' => $newDailyQuota]);

                if ($syncedCount > 0) {
                    AuditService::log(
                        action: 'SYNC_DAILY_QUOTAS',
                        entityType: 'DailyQuota',
                        entityId: 0,
                        description: "Kapasitas {$syncedCount} tanggal kalender mendatang otomatis disinkronkan ke {$newDailyQuota} pendaki/hari.",
                        payload: ['old_quota' => $oldDailyQuota, 'new_quota' => $newDailyQuota, 'synced_dates' => $syncedCount]
                    );
                }
            }
        }
        if ($request->filled('min_climbers_per_group')) {
            AppSetting::set('min_climbers_per_group', (int) $request->input('min_climbers_per_group'), 'Batas Minimal Pendaki Rombongan', 'number', 'capacity');
        }
        if ($request->filled('max_climbers_per_group')) {
            AppSetting::set('max_climbers_per_group', (int) $request->input('max_climbers_per_group'), 'Batas Maksimal Pendaki Rombongan', 'number', 'capacity');
        }

        // Update Operational & SAR Contacts
        if ($request->filled('admin_whatsapp')) {
            AppSetting::set('admin_whatsapp', preg_replace('/[^0-9]/', '', (string) $request->input('admin_whatsapp')), 'Nomor WhatsApp Admin', 'string', 'contact');
        }
        if ($request->filled('admin_whatsapp_display')) {
            AppSetting::set('admin_whatsapp_display', (string) $request->input('admin_whatsapp_display'), 'Tampilan Nomor WhatsApp Admin', 'string', 'contact');
        }
        if ($request->filled('emergency_hotline')) {
            AppSetting::set('emergency_hotline', (string) $request->input('emergency_hotline'), 'Hotline Darurat Basecamp', 'string', 'contact');
        }
        $sarRadioEnabled = $request->boolean('sar_radio_enabled');
        AppSetting::set('sar_radio_enabled', $sarRadioEnabled, 'Status Radio HT SAR Basecamp', 'boolean', 'contact');

        if ($request->filled('sar_frequency')) {
            AppSetting::set('sar_frequency', (string) $request->input('sar_frequency'), 'Frekuensi Radio SAR Basecamp', 'string', 'contact');
        }
        if ($request->filled('basecamp_location')) {
            AppSetting::set('basecamp_location', (string) $request->input('basecamp_location'), 'Lokasi Basecamp', 'string', 'contact');
        }
        if ($request->filled('basecamp_maps_url')) {
            AppSetting::set('basecamp_maps_url', (string) $request->input('basecamp_maps_url'), 'Link Google Maps Basecamp', 'string', 'contact');
        }
        if ($request->filled('basecamp_coordinates')) {
            AppSetting::set('basecamp_coordinates', (string) $request->input('basecamp_coordinates'), 'Koordinat GPS Basecamp', 'string', 'contact');
        }

        // Update Banking & QRIS
        if ($request->filled('payment_bank_name')) {
            AppSetting::set('payment_bank_name', (string) $request->input('payment_bank_name'), 'Nama Bank Transfer', 'string', 'banking');
        }
        if ($request->filled('payment_bank_account')) {
            $bankAcc = (string) $request->input('payment_bank_account');
            AppSetting::set('payment_bank_account', $bankAcc, 'Nomor Rekening Bank', 'string', 'banking');
            AppSetting::set('payment_bank_account_raw', preg_replace('/[^0-9]/', '', $bankAcc), 'Nomor Rekening Mentah', 'string', 'banking');
        }
        if ($request->filled('payment_bank_holder')) {
            AppSetting::set('payment_bank_holder', (string) $request->input('payment_bank_holder'), 'Nama Pemilik Rekening', 'string', 'banking');
        }
        if ($request->filled('payment_qris_nmid')) {
            AppSetting::set('payment_qris_nmid', (string) $request->input('payment_qris_nmid'), 'NMID QRIS Resmi', 'string', 'banking');
        }
        if ($request->filled('payment_qris_name')) {
            AppSetting::set('payment_qris_name', (string) $request->input('payment_qris_name'), 'Nama Merchant QRIS', 'string', 'banking');
        }

        // Handle Static QRIS Barcode Image Upload / Removal
        if ($request->boolean('remove_qris_image')) {
            $oldQris = AppSetting::get('payment_qris_image');
            if ($oldQris && Storage::disk('public')->exists($oldQris)) {
                Storage::disk('public')->delete($oldQris);
            }
            AppSetting::where('key', 'payment_qris_image')->delete();
        } elseif ($request->hasFile('payment_qris_image') && $request->file('payment_qris_image')->isValid()) {
            $oldQris = AppSetting::get('payment_qris_image');
            if ($oldQris && Storage::disk('public')->exists($oldQris)) {
                Storage::disk('public')->delete($oldQris);
            }
            $qrisPath = $request->file('payment_qris_image')->store('qris', 'public');
            AppSetting::set('payment_qris_image', $qrisPath, 'Gambar Barcode QRIS Statis Resmi', 'string', 'banking');
        }

        // Keep Inventory Catalog (POS) in sync
        InventoryItem::where('sku', 'SRV-SIMAKSI')->update(['price' => $simaksi]);
        InventoryItem::where('sku', 'SRV-OJEK')->update(['price' => $ojek]);
        InventoryItem::where('sku', 'SRV-PORTER')->update(['price' => $porter]);
        InventoryItem::where('sku', 'PRK-MOTOR')->update(['price' => $parkMotor]);
        InventoryItem::where('sku', 'PRK-MOBIL')->update(['price' => $parkCar]);

        // Audit Trail
        AuditService::log(
            action: 'UPDATE_EXPEDITION_TARIFFS',
            entityType: 'AppSetting',
            entityId: 0,
            description: 'Superadmin memperbarui konfigurasi sistem operasional ekspedisi (Tarif, Kuota, Kontak SAR, dan Rekening Bank).',
            payload: [
                'simaksi_fee' => $simaksi,
                'ojek_fee' => $ojek,
                'porter_fee' => $porter,
                'parking_motor_fee' => $parkMotor,
                'parking_car_fee' => $parkCar,
                'trash_deposit_enabled' => $trashEnabled,
                'trash_deposit_amount' => $trashAmount,
                'default_daily_quota' => $request->input('default_daily_quota'),
            ]
        );

        return redirect()->route('management.settings.tariffs')
            ->with('success', 'Konfigurasi tarif operasional, kuota konservasi, kontak SAR, dan rekening pembayaran berhasil diperbarui secara real-time!');
    }
}
