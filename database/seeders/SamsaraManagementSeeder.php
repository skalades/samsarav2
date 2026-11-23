<?php

namespace Database\Seeders;

use App\Models\AppSetting;
use App\Models\AuditLog;
use App\Models\DailyQuota;
use App\Models\InventoryItem;
use App\Models\PosOrder;
use App\Models\PosOrderItem;
use App\Models\RentalRecord;
use App\Models\SimaksiRegistration;
use App\Models\TrailCondition;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SamsaraManagementSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('samsara2026');

        // 1. Multi-role Users
        $users = [
            [
                'name' => 'Bayu Pratama (Superadmin)',
                'email' => 'admin@samsara.id',
                'password' => $password,
                'role' => 'superadmin',
                'phone' => '081211112222',
                'status' => 'active',
            ],
            [
                'name' => 'Rian Hidayat (Verifikator)',
                'email' => 'verifikator@samsara.id',
                'password' => $password,
                'role' => 'verifikator',
                'phone' => '081233334444',
                'status' => 'active',
            ],
            [
                'name' => 'Siti Aminah (Kasir Operasional Loket)',
                'email' => 'kasir@samsara.id',
                'password' => $password,
                'role' => 'kasir',
                'phone' => '081255556666',
                'status' => 'active',
            ],
            [
                'name' => 'Danu Ranger (Petugas Gate)',
                'email' => 'gate@samsara.id',
                'password' => $password,
                'role' => 'petugas_lapangan',
                'phone' => '081277778888',
                'status' => 'active',
            ],
            [
                'name' => 'Hendra Logistik (Inventori)',
                'email' => 'inventori@samsara.id',
                'password' => $password,
                'role' => 'inventori',
                'phone' => '081299990000',
                'status' => 'active',
            ],
            [
                'name' => 'Maya Sari (Auditor)',
                'email' => 'auditor@samsara.id',
                'password' => $password,
                'role' => 'auditor',
                'phone' => '081288881111',
                'status' => 'active',
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(['email' => $userData['email']], $userData);
        }

        $adminUser = User::where('email', 'admin@samsara.id')->first();
        $verifUser = User::where('email', 'verifikator@samsara.id')->first();
        $gateUser = User::where('email', 'gate@samsara.id')->first();
        $cashierUser = User::where('email', 'kasir@samsara.id')->first();

        // 2. Trail Condition
        TrailCondition::create([
            'status' => 'open',
            'pos2_water_status' => 'Melimpah (12 L/mnt)',
            'night_temp_celsius' => '9°C (Dini Hari)',
            'weather_condition' => 'Cerah Berawan',
            'soil_condition' => 'Padat & Kering (Optimal)',
            'wind_speed' => '14 km/jam Barat Daya',
            'sar_frequency' => '149.250 MHz',
            'ranger_notes' => 'Trek normal dari Pos 00 Basecamp hingga Puncak Triangulasi. Pos 2 sumber air aktif.',
            'updated_by' => $adminUser->id,
        ]);

        // 3. Daily Quotas (30 days from today)
        $today = Carbon::today();
        $defaultDailyQuota = (int) AppSetting::get('default_daily_quota', 150);
        for ($i = 0; $i < 30; $i++) {
            $date = $today->copy()->addDays($i);
            $booked = $i === 0 ? 108 : ($i === 7 ? 142 : rand(10, 60));
            $existing = DailyQuota::whereDate('quota_date', $date->toDateString())->first();
            if ($existing) {
                $existing->update([
                    'max_quota' => $defaultDailyQuota,
                    'booked_quota' => $booked,
                    'is_closed' => false,
                ]);
            } else {
                DailyQuota::create([
                    'quota_date' => $date->toDateString(),
                    'max_quota' => $defaultDailyQuota,
                    'booked_quota' => $booked,
                    'is_closed' => false,
                ]);
            }
        }

        // 4. Inventory & Catalog
        $inventoryItems = [
            ['sku' => 'SRV-SIMAKSI', 'name' => 'SIMAKSI Masuk Jalur Samsara', 'category' => 'izin', 'type' => 'service', 'price' => 25000, 'stock_total' => 9999, 'stock_available' => 9999, 'unit' => 'orang', 'description' => 'Izin resmi masuk kawasan konservasi Gunung Cikuray.'],
            ['sku' => 'SRV-OJEK', 'name' => 'Shuttle Ojek Basecamp - Pos 1', 'category' => 'shuttle_ojek', 'type' => 'service', 'price' => 30000, 'stock_total' => 9999, 'stock_available' => 9999, 'unit' => 'trip', 'description' => 'Layanan armada ojek warga lokal melalui kebun teh.'],
            ['sku' => 'SRV-PORTER', 'name' => 'Jasa Porter Jalur Samsara (2H1M)', 'category' => 'porter', 'type' => 'service', 'price' => 350000, 'stock_total' => 10, 'stock_available' => 7, 'unit' => 'trip', 'description' => 'Beban maksimal 20 KG hingga Camping Ground Pos 4.'],
            ['sku' => 'RNT-SB-TH', 'name' => 'Sleeping Bag Thermal Polar Bulu', 'category' => 'alat_gunung', 'type' => 'rental', 'price' => 20000, 'stock_total' => 45, 'stock_available' => 38, 'unit' => 'unit/malam', 'description' => 'Kenyamanan isolasi termal hingga suhu 5°C.'],
            ['sku' => 'RNT-NESTING', 'name' => 'Kompor Portabel & Nesting Set', 'category' => 'alat_gunung', 'type' => 'rental', 'price' => 25000, 'stock_total' => 20, 'stock_available' => 16, 'unit' => 'set/malam', 'description' => 'Kompor ultralight & nesting aluminium 3 susun.'],
            ['sku' => 'RNT-MATRAS', 'name' => 'Matras Alumunium Foil Foam', 'category' => 'alat_gunung', 'type' => 'rental', 'price' => 10000, 'stock_total' => 50, 'stock_available' => 44, 'unit' => 'unit/malam', 'description' => 'Menahan rambatan dingin tanah basah.'],
            ['sku' => 'GDS-GAS', 'name' => 'Gas Canister Outdoor 230g', 'category' => 'konsumsi', 'type' => 'good', 'price' => 28000, 'stock_total' => 80, 'stock_available' => 80, 'unit' => 'canister', 'description' => 'Gas ulir butane-propane standar outdoor.'],
            ['sku' => 'GDS-JAS', 'name' => 'Jas Hujan Ponco Darurat HDPE', 'category' => 'konsumsi', 'type' => 'good', 'price' => 15000, 'stock_total' => 60, 'stock_available' => 60, 'unit' => 'pcs', 'description' => 'Ponco tebal pelindung hujan mendadak.'],
            ['sku' => 'GDS-AIR', 'name' => 'Air Mineral Botol 1500ml', 'category' => 'konsumsi', 'type' => 'good', 'price' => 8000, 'stock_total' => 120, 'stock_available' => 120, 'unit' => 'botol', 'description' => 'Cadangan air minum sebelum memasuki jalur.'],
            ['sku' => 'PRK-MOTOR', 'name' => 'Titip Parkir Motor Basecamp', 'category' => 'parkir', 'type' => 'service', 'price' => 10000, 'stock_total' => 9999, 'stock_available' => 9999, 'unit' => 'unit', 'description' => 'Keamanan parkir motor 24 jam di basecamp.'],
            ['sku' => 'PRK-MOBIL', 'name' => 'Titip Parkir Mobil Basecamp', 'category' => 'parkir', 'type' => 'service', 'price' => 25000, 'stock_total' => 9999, 'stock_available' => 9999, 'unit' => 'unit', 'description' => 'Keamanan parkir mobil roda empat di basecamp.'],
        ];

        foreach ($inventoryItems as $item) {
            InventoryItem::updateOrCreate(['sku' => $item['sku']], $item);
        }

        // 4b. Expedition Tariffs & Policies Settings
        AppSetting::updateOrCreate(
            ['key' => 'simaksi_fee_per_person'],
            ['value' => '25000', 'label' => 'Tarif Masuk SIMAKSI', 'description' => 'Biaya permit masuk konservasi per orang', 'type' => 'number', 'group' => 'tariffs']
        );
        AppSetting::updateOrCreate(
            ['key' => 'ojek_fee_per_trip'],
            ['value' => '30000', 'label' => 'Tarif Shuttle Ojek Trail', 'description' => 'Biaya shuttle ojek per trip / armada dari Basecamp ke Pos 1', 'type' => 'number', 'group' => 'tariffs']
        );
        AppSetting::updateOrCreate(
            ['key' => 'ojek_basecamp_share'],
            ['value' => '5000', 'label' => 'Bagi Hasil Kas Jalur Ojek', 'description' => 'Alokasi bagi hasil kas jalur basecamp per trip ojek', 'type' => 'number', 'group' => 'tariffs']
        );
        AppSetting::updateOrCreate(
            ['key' => 'ojek_route_name'],
            ['value' => 'Basecamp → Pos 1', 'label' => 'Nama Trayek Shuttle Ojek', 'description' => 'Deskripsi rute resmi pangkalan ojek', 'type' => 'string', 'group' => 'tariffs']
        );
        AppSetting::updateOrCreate(
            ['key' => 'porter_fee_per_trip'],
            ['value' => '350000', 'label' => 'Tarif Porter Tim Lokal', 'description' => 'Biaya porter lokal per trip 2H1M maks 20kg', 'type' => 'number', 'group' => 'tariffs']
        );
        AppSetting::updateOrCreate(
            ['key' => 'parking_motor_fee'],
            ['value' => '10000', 'label' => 'Tarif Parkir Sepeda Motor', 'description' => 'Biaya penitipan parkir motor basecamp per unit', 'type' => 'number', 'group' => 'tariffs']
        );
        AppSetting::updateOrCreate(
            ['key' => 'parking_car_fee'],
            ['value' => '25000', 'label' => 'Tarif Parkir Mobil Pribadi', 'description' => 'Biaya penitipan parkir mobil basecamp per unit', 'type' => 'number', 'group' => 'tariffs']
        );
        AppSetting::updateOrCreate(
            ['key' => 'trash_deposit_enabled'],
            ['value' => '1', 'label' => 'Status Deposit Sampah', 'description' => 'Status aktif jaminan kantong sampah di gerbang basecamp', 'type' => 'boolean', 'group' => 'policy']
        );
        AppSetting::updateOrCreate(
            ['key' => 'trash_deposit_amount'],
            ['value' => '50000', 'label' => 'Besaran Deposit Sampah', 'description' => 'Nominal jaminan sampah (dikembalikan saat turun membawa sampah)', 'type' => 'number', 'group' => 'policy']
        );
        AppSetting::updateOrCreate(
            ['key' => 'default_daily_quota'],
            ['value' => '150', 'label' => 'Kapasitas Kuota Harian Resmi', 'description' => 'Batas maksimal pendaki per hari sesuai daya dukung jalur', 'type' => 'number', 'group' => 'capacity']
        );
        AppSetting::updateOrCreate(
            ['key' => 'min_climbers_per_group'],
            ['value' => '1', 'label' => 'Batas Minimal Pendaki Rombongan', 'description' => 'Batas minimal anggota rombongan', 'type' => 'number', 'group' => 'capacity']
        );
        AppSetting::updateOrCreate(
            ['key' => 'max_climbers_per_group'],
            ['value' => '15', 'label' => 'Batas Maksimal Pendaki Rombongan', 'description' => 'Batas maksimal anggota per registrasi', 'type' => 'number', 'group' => 'capacity']
        );
        AppSetting::updateOrCreate(
            ['key' => 'admin_whatsapp'],
            ['value' => '6282122334455', 'label' => 'Nomor WhatsApp Admin', 'description' => 'Nomor WhatsApp resmi format internasional 62...', 'type' => 'string', 'group' => 'contact']
        );
        AppSetting::updateOrCreate(
            ['key' => 'admin_whatsapp_display'],
            ['value' => '+62 821-2233-4455', 'label' => 'Tampilan Nomor WhatsApp Admin', 'description' => 'Format tampilan nomor WhatsApp admin untuk UI', 'type' => 'string', 'group' => 'contact']
        );
        AppSetting::updateOrCreate(
            ['key' => 'emergency_hotline'],
            ['value' => '+62 821-2233-4455', 'label' => 'Hotline Darurat Basecamp', 'description' => 'Nomor telepon siaga call center ranger', 'type' => 'string', 'group' => 'contact']
        );
        AppSetting::updateOrCreate(
            ['key' => 'sar_frequency'],
            ['value' => '149.250 MHz', 'label' => 'Frekuensi Radio SAR Basecamp', 'description' => 'Frekuensi komunikasi HT lapangan', 'type' => 'string', 'group' => 'contact']
        );
        AppSetting::updateOrCreate(
            ['key' => 'payment_bank_name'],
            ['value' => 'BANK CENTRAL ASIA (BCA)', 'label' => 'Nama Bank Transfer', 'description' => 'Nama institusi perbankan resmi', 'type' => 'string', 'group' => 'banking']
        );
        AppSetting::updateOrCreate(
            ['key' => 'payment_bank_account'],
            ['value' => '810-0982-111', 'label' => 'Nomor Rekening Bank', 'description' => 'Nomor rekening bank tujuan transfer', 'type' => 'string', 'group' => 'banking']
        );
        AppSetting::updateOrCreate(
            ['key' => 'payment_bank_holder'],
            ['value' => 'PT SAMSARA ADVENTURE GARUT', 'label' => 'Nama Pemilik Rekening', 'description' => 'Nama atas nama rekening bank', 'type' => 'string', 'group' => 'banking']
        );
        AppSetting::updateOrCreate(
            ['key' => 'payment_qris_nmid'],
            ['value' => 'ID102026110099', 'label' => 'NMID QRIS Resmi', 'description' => 'National Merchant ID QRIS', 'type' => 'string', 'group' => 'banking']
        );
        AppSetting::updateOrCreate(
            ['key' => 'payment_qris_name'],
            ['value' => 'PT SAMSARA ADVENTURE GARUT', 'label' => 'Nama Merchant QRIS', 'description' => 'Nama merchant QRIS resmi', 'type' => 'string', 'group' => 'banking']
        );

        // 5. Sample Registrations
        if (SimaksiRegistration::where('registration_code', 'SMK-202611-001')->exists()) {
            return;
        }

        // Reg 1: Paid & Ready for check-in
        $reg1 = SimaksiRegistration::create([
            'registration_code' => 'SMK-202611-001',
            'leader_name' => 'Arif Rahmansyah',
            'leader_nik' => '3205011995080001',
            'leader_phone' => '081288219901',
            'leader_email' => 'arif.rahman@example.com',
            'leader_city' => 'Bandung',
            'emergency_contact_name' => 'Ratna Dewi (Ibu)',
            'emergency_contact_phone' => '081277119900',
            'emergency_relationship' => 'Orang Tua',
            'hiking_date' => $today->toDateString(),
            'return_date' => $today->copy()->addDay()->toDateString(),
            'total_climbers' => 4,
            'status' => 'paid',
            'simaksi_fee' => 100000,
            'ojek_fee' => 30000,
            'tent_fee' => 65000,
            'total_amount' => 195000,
            'payment_status' => 'paid',
            'payment_method' => 'qris',
            'paid_at' => Carbon::now()->subHours(2),
            'reviewed_by' => $verifUser->id,
            'reviewed_at' => Carbon::now()->subHours(4),
            'trash_deposit_amount' => 50000,
            'trash_deposit_status' => 'unpaid',
        ]);

        $members1 = [
            ['full_name' => 'Arif Rahmansyah', 'nik' => '3205011995080001', 'phone' => '081288219901', 'gender' => 'L', 'city_origin' => 'Bandung', 'is_leader' => true],
            ['full_name' => 'Rangga Wijaya', 'nik' => '3205011996020002', 'phone' => '081299001122', 'gender' => 'L', 'city_origin' => 'Bandung', 'is_leader' => false],
            ['full_name' => 'Nadya Safitri', 'nik' => '3205011998050003', 'phone' => '081344556677', 'gender' => 'P', 'city_origin' => 'Cimahi', 'is_leader' => false],
            ['full_name' => 'Bima Sena', 'nik' => '3205011994110004', 'phone' => '081566778899', 'gender' => 'L', 'city_origin' => 'Garut', 'is_leader' => false],
        ];
        foreach ($members1 as $m) {
            $reg1->members()->create($m);
        }

        // Reg 2: Submitted (Needs Verification)
        $reg2 = SimaksiRegistration::create([
            'registration_code' => 'SMK-202611-002',
            'leader_name' => 'Dian Pratama',
            'leader_nik' => '3273011992040005',
            'leader_phone' => '085711223344',
            'leader_email' => 'dian.pratama@example.com',
            'leader_city' => 'Jakarta Selatan',
            'emergency_contact_name' => 'Agus Pratama (Ayah)',
            'emergency_contact_phone' => '081122334455',
            'emergency_relationship' => 'Orang Tua',
            'hiking_date' => $today->copy()->addDays(2)->toDateString(),
            'return_date' => $today->copy()->addDays(3)->toDateString(),
            'total_climbers' => 2,
            'status' => 'pending',
            'simaksi_fee' => 50000,
            'ojek_fee' => 0,
            'tent_fee' => 0,
            'total_amount' => 50000,
            'payment_status' => 'unpaid',
            'trash_deposit_amount' => 50000,
            'trash_deposit_status' => 'unpaid',
        ]);
        $reg2->members()->create(['full_name' => 'Dian Pratama', 'nik' => '3273011992040005', 'phone' => '085711223344', 'gender' => 'L', 'city_origin' => 'Jakarta Selatan', 'is_leader' => true]);
        $reg2->members()->create(['full_name' => 'Yuda Ramadhan', 'nik' => '3273011993070006', 'phone' => '085799887766', 'gender' => 'L', 'city_origin' => 'Jakarta Selatan', 'is_leader' => false]);

        // Reg 3: Needs Revision
        $reg3 = SimaksiRegistration::create([
            'registration_code' => 'SMK-202611-003',
            'leader_name' => 'Fajar Nugraha',
            'leader_nik' => '3204011997090007',
            'leader_phone' => '087811992288',
            'leader_email' => 'fajar.nugraha@example.com',
            'leader_city' => 'Tasikmalaya',
            'emergency_contact_name' => 'Nia Kurniati (Kakak)',
            'emergency_contact_phone' => '087822334455',
            'emergency_relationship' => 'Keluarga',
            'hiking_date' => $today->copy()->addDays(3)->toDateString(),
            'return_date' => $today->copy()->addDays(4)->toDateString(),
            'total_climbers' => 3,
            'status' => 'pending',
            'revision_notes' => 'Nomor kontak darurat tidak dapat dihubungi saat uji panggil. Harap perbarui kontak wali aktif.',
            'reviewed_by' => $verifUser->id,
            'reviewed_at' => Carbon::now()->subHours(1),
            'simaksi_fee' => 75000,
            'total_amount' => 75000,
            'payment_status' => 'unpaid',
        ]);
        $reg3->members()->create(['full_name' => 'Fajar Nugraha', 'nik' => '3204011997090007', 'phone' => '087811992288', 'gender' => 'L', 'city_origin' => 'Tasikmalaya', 'is_leader' => true]);
        $reg3->members()->create(['full_name' => 'Irfan Hakim', 'nik' => '3204011998010008', 'phone' => '087833445566', 'gender' => 'L', 'city_origin' => 'Tasikmalaya', 'is_leader' => false]);
        $reg3->members()->create(['full_name' => 'Anisa Rahma', 'nik' => '3204012000030009', 'phone' => '087855667788', 'gender' => 'P', 'city_origin' => 'Tasikmalaya', 'is_leader' => false]);

        // Reg 4: Checked-in (Active on mountain)
        $reg4 = SimaksiRegistration::create([
            'registration_code' => 'SMK-202611-004',
            'leader_name' => 'Hendro Kusumo',
            'leader_nik' => '3201011991050010',
            'leader_phone' => '081399881122',
            'leader_email' => 'hendro.k@example.com',
            'leader_city' => 'Bogor',
            'emergency_contact_name' => 'Wati (Istri)',
            'emergency_contact_phone' => '081399881100',
            'emergency_relationship' => 'Istri',
            'hiking_date' => $today->toDateString(),
            'return_date' => $today->copy()->addDay()->toDateString(),
            'total_climbers' => 3,
            'status' => 'checked_in',
            'simaksi_fee' => 75000,
            'total_amount' => 75000,
            'payment_status' => 'paid',
            'payment_method' => 'cash_pos',
            'paid_at' => Carbon::now()->subHours(6),
            'reviewed_by' => $verifUser->id,
            'reviewed_at' => Carbon::now()->subHours(8),
            'checked_in_by' => $gateUser->id,
            'checked_in_at' => Carbon::now()->subHours(5),
            'trash_deposit_amount' => 50000,
            'trash_deposit_status' => 'deposited',
            'trash_bag_code' => 'TB-2026-089',
        ]);
        $reg4->members()->create(['full_name' => 'Hendro Kusumo', 'nik' => '3201011991050010', 'phone' => '081399881122', 'gender' => 'L', 'city_origin' => 'Bogor', 'is_leader' => true, 'is_checked_in' => true]);
        $reg4->members()->create(['full_name' => 'Gita Gutawa', 'nik' => '3201011993080011', 'phone' => '081311223344', 'gender' => 'P', 'city_origin' => 'Bogor', 'is_leader' => false, 'is_checked_in' => true]);
        $reg4->members()->create(['full_name' => 'Doni Salmanan', 'nik' => '3201011995120012', 'phone' => '081355667788', 'gender' => 'L', 'city_origin' => 'Depok', 'is_leader' => false, 'is_checked_in' => true]);

        // 6. Sample POS Order
        $nestingItem = InventoryItem::where('sku', 'RNT-NESTING')->first();
        $gasItem = InventoryItem::where('sku', 'GDS-GAS')->first();

        $order1 = PosOrder::create([
            'order_number' => 'POS-202611-0001',
            'cashier_id' => $cashierUser->id,
            'simaksi_registration_id' => $reg4->id,
            'customer_name' => 'Hendro Kusumo',
            'customer_phone' => '081399881122',
            'subtotal' => 53000,
            'discount' => 0,
            'total_amount' => 53000,
            'cash_tendered' => 60000,
            'cash_change' => 7000,
            'payment_method' => 'cash',
            'status' => 'completed',
            'notes' => 'Sewa nesting 1 set + gas kaleng 1 pcs',
        ]);

        PosOrderItem::create([
            'pos_order_id' => $order1->id,
            'inventory_item_id' => $nestingItem->id,
            'item_name' => $nestingItem->name,
            'category' => $nestingItem->category,
            'unit_price' => 25000,
            'quantity' => 1,
            'subtotal' => 25000,
        ]);

        PosOrderItem::create([
            'pos_order_id' => $order1->id,
            'inventory_item_id' => $gasItem->id,
            'item_name' => $gasItem->name,
            'category' => $gasItem->category,
            'unit_price' => 28000,
            'quantity' => 1,
            'subtotal' => 28000,
        ]);

        // 7. Sample Rental Record
        RentalRecord::create([
            'inventory_item_id' => $nestingItem->id,
            'simaksi_registration_id' => $reg4->id,
            'pos_order_id' => $order1->id,
            'customer_name' => 'Hendro Kusumo',
            'item_name' => 'Kompor Portabel & Nesting Set',
            'quantity' => 1,
            'condition_out' => 'Bagus / Lengkap dengan pasak & frame',
            'handed_out_by' => $cashierUser->id,
            'handed_out_at' => Carbon::now()->subHours(5),
            'status' => 'rented',
        ]);

        // 8. Sample Audit Logs
        AuditLog::create([
            'user_id' => $adminUser->id,
            'actor_name' => $adminUser->name,
            'actor_role' => $adminUser->role,
            'action' => 'UPDATE_TRAIL_CONDITION',
            'description' => 'Memperbarui status telemetri jalur menjadi BUKA (NORMAL).',
            'ip_address' => '127.0.0.1',
            'created_at' => Carbon::now()->subHours(6),
        ]);

        AuditLog::create([
            'user_id' => $verifUser->id,
            'actor_name' => $verifUser->name,
            'actor_role' => $verifUser->role,
            'action' => 'APPROVE_SIMAKSI',
            'entity_type' => 'SimaksiRegistration',
            'entity_id' => $reg1->id,
            'description' => 'Menyetujui SIMAKSI SMK-202611-001 untuk 4 orang pendaki.',
            'ip_address' => '127.0.0.1',
            'created_at' => Carbon::now()->subHours(4),
        ]);

        AuditLog::create([
            'user_id' => $gateUser->id,
            'actor_name' => $gateUser->name,
            'actor_role' => $gateUser->role,
            'action' => 'GATE_CHECKIN',
            'entity_type' => 'SimaksiRegistration',
            'entity_id' => $reg4->id,
            'description' => 'Check-in gerbang untuk rombongan Hendro Kusumo. Kantong sampah #TB-2026-089 diterbitkan.',
            'ip_address' => '127.0.0.1',
            'created_at' => Carbon::now()->subHours(5),
        ]);
    }
}
