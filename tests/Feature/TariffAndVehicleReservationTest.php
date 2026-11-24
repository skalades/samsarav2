<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Models\InventoryItem;
use App\Models\SimaksiRegistration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TariffAndVehicleReservationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed AppSettings defaults
        AppSetting::set('simaksi_fee_per_person', 25000, 'Tarif SIMAKSI', 'number', 'tariffs');
        AppSetting::set('ojek_fee_per_trip', 30000, 'Tarif Ojek', 'number', 'tariffs');
        AppSetting::set('parking_motor_fee', 10000, 'Tarif Parkir Motor', 'number', 'tariffs');
        AppSetting::set('parking_car_fee', 25000, 'Tarif Parkir Mobil', 'number', 'tariffs');
        AppSetting::set('trash_deposit_enabled', true, 'Status Deposit Sampah', 'boolean', 'policy');
        AppSetting::set('trash_deposit_amount', 50000, 'Besaran Deposit Sampah', 'number', 'policy');

        // Seed Catalog items
        InventoryItem::updateOrCreate(['sku' => 'SRV-SIMAKSI'], ['name' => 'SIMAKSI', 'category' => 'izin', 'type' => 'service', 'price' => 25000]);
        InventoryItem::updateOrCreate(['sku' => 'SRV-OJEK'], ['name' => 'Ojek Trail', 'category' => 'shuttle_ojek', 'type' => 'service', 'price' => 30000]);
        InventoryItem::updateOrCreate(['sku' => 'PRK-MOTOR'], ['name' => 'Parkir Motor', 'category' => 'parkir', 'type' => 'service', 'price' => 10000]);
        InventoryItem::updateOrCreate(['sku' => 'PRK-MOBIL'], ['name' => 'Parkir Mobil', 'category' => 'parkir', 'type' => 'service', 'price' => 25000]);
    }

    public function test_superadmin_can_access_and_update_tariffs_and_toggle_trash_deposit(): void
    {
        $superadmin = User::firstOrCreate(
            ['email' => 'admin_test@samsara.id'],
            [
                'name' => 'Superadmin Test',
                'password' => bcrypt('password'),
                'role' => 'superadmin',
                'status' => 'active',
            ]
        );

        $response = $this->actingAs($superadmin)->get(route('management.settings.tariffs'));
        $response->assertStatus(200);
        $response->assertSee('Pengaturan Tarif &amp; Kebijakan Basecamp', false);

        // Update tariffs: ojek to 40.000, motor to 15.000, car to 35.000, and disable trash deposit
        $postData = [
            'simaksi_fee_per_person' => 30000,
            'ojek_fee_per_trip' => 40000,
            'parking_motor_fee' => 15000,
            'parking_car_fee' => 35000,
            'trash_deposit_enabled' => 0, // Disable deposit
            'trash_deposit_amount' => 50000,
        ];

        $updateResponse = $this->actingAs($superadmin)->post(route('management.settings.tariffs.update'), $postData);
        $updateResponse->assertRedirect(route('management.settings.tariffs'));

        // Verify AppSetting updated
        $this->assertEquals(40000, AppSetting::get('ojek_fee_per_trip'));
        $this->assertEquals(15000, AppSetting::get('parking_motor_fee'));
        $this->assertEquals(35000, AppSetting::get('parking_car_fee'));
        $this->assertFalse(AppSetting::get('trash_deposit_enabled'));

        // Verify Inventory catalog is synced
        $this->assertEquals(40000, InventoryItem::where('sku', 'SRV-OJEK')->value('price'));
        $this->assertEquals(15000, InventoryItem::where('sku', 'PRK-MOTOR')->value('price'));
        $this->assertEquals(35000, InventoryItem::where('sku', 'PRK-MOBIL')->value('price'));
    }

    public function test_unauthorized_user_cannot_update_tariffs(): void
    {
        $kasir = User::firstOrCreate(
            ['email' => 'kasir_test@samsara.id'],
            [
                'name' => 'Kasir Test',
                'password' => bcrypt('password'),
                'role' => 'kasir',
                'status' => 'active',
            ]
        );

        $response = $this->actingAs($kasir)->get(route('management.settings.tariffs'));
        $response->assertRedirect(route('management.dashboard'));
        $response->assertSessionHas('error');

        $jsonResponse = $this->actingAs($kasir)->getJson(route('management.settings.tariffs'));
        $jsonResponse->assertStatus(403);
    }

    public function test_registration_with_vehicle_calculates_parking_fee_accurately(): void
    {
        Storage::fake('public');

        // Tariffs: SIMAKSI 25k/org, Ojek 30k/trip, Tenda 65k, Motor 10k, Mobil 25k
        // Booking: 2 climbers (50k) + 1 ojek (30k) + 2 motorcycles (20k) + 1 car (25k) = 125k subtotal
        $payload = [
            'hiking_date' => '2026-12-01',
            'return_date' => '2026-12-02',
            'leader_name' => 'Fahmi Ridwan',
            'leader_nik' => '3205011993080009',
            'leader_phone' => '081234567890',
            'leader_city' => 'Garut',
            'medical_conditions' => 'Tidak Ada',
            'emergency_contact_name' => 'Hendra',
            'emergency_relationship' => 'Orang Tua',
            'emergency_contact_phone' => '081299887766',
            'members' => [
                ['full_name' => 'Fahmi Ridwan', 'nik' => '3205011993080009', 'gender' => 'L', 'medical_conditions' => 'Tidak Ada'],
                ['full_name' => 'Dwi Santoso', 'nik' => '3205011994050002', 'gender' => 'L', 'medical_conditions' => 'Tidak Ada'],
            ],
            'ojek_count' => 1,
            'tent_count' => 0,
            'has_vehicle' => 1,
            'motorcycle_count' => 2,
            'car_count' => 1,
            'unique_code' => 150,
            'payment_method' => 'qris',
        ];

        $response = $this->postJson(route('portal.simaksi.register'), $payload);
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'has_vehicle' => true,
            'motorcycle_count' => 2,
            'car_count' => 1,
            'parking_fee' => 45000, // 2*10.000 + 1*25.000
        ]);

        $reg = SimaksiRegistration::where('leader_nik', '3205011993080009')->first();
        $this->assertNotNull($reg);
        $this->assertTrue((bool) $reg->has_vehicle);
        $this->assertEquals(2, $reg->motorcycle_count);
        $this->assertEquals(1, $reg->car_count);
        $this->assertEquals(45000, (float) $reg->parking_fee);
        $this->assertEquals(125000, (float) ($reg->simaksi_fee + $reg->ojek_fee + $reg->parking_fee));
        $this->assertEquals(125150, (float) $reg->total_amount);
        $this->assertEquals(50000, (float) $reg->trash_deposit_amount);
        $this->assertEquals('unpaid', $reg->trash_deposit_status);
    }

    public function test_registration_when_trash_deposit_disabled_sets_zero_deposit(): void
    {
        Storage::fake('public');

        // Disable trash deposit in settings
        AppSetting::set('trash_deposit_enabled', false, 'Status Deposit Sampah', 'boolean', 'policy');

        $payload = [
            'hiking_date' => '2026-12-05',
            'return_date' => '2026-12-06',
            'leader_name' => 'Gilang Ramadhan',
            'leader_nik' => '3205011991040008',
            'leader_phone' => '081299112233',
            'emergency_contact_name' => 'Rina',
            'emergency_contact_phone' => '081233445566',
            'members' => [
                ['full_name' => 'Gilang Ramadhan', 'nik' => '3205011991040008', 'gender' => 'L', 'medical_conditions' => 'Tidak Ada'],
            ],
            'payment_method' => 'cash_pos',
        ];

        $response = $this->postJson(route('portal.simaksi.register'), $payload);
        $response->assertStatus(200);

        $reg = SimaksiRegistration::where('leader_nik', '3205011991040008')->first();
        $this->assertNotNull($reg);
        $this->assertEquals(0, (float) $reg->trash_deposit_amount);
        $this->assertEquals('waived', $reg->trash_deposit_status);
    }

    public function test_registration_with_full_domicile_address_persists_correctly(): void
    {
        Storage::fake('public');

        $fullAddress = 'Jl. Otista No. 120, RT 03/RW 07, Kel. Tarogong, Kec. Tarogong Kidul, Garut, Jawa Barat';
        $payload = [
            'hiking_date' => '2026-12-10',
            'return_date' => '2026-12-11',
            'leader_name' => 'Aditya Pratama',
            'leader_nik' => '3205011995110001',
            'leader_phone' => '081322446688',
            'leader_address' => $fullAddress,
            'emergency_contact_name' => 'Siti Nurhaliza',
            'emergency_contact_phone' => '081399887766',
            'members' => [
                ['full_name' => 'Aditya Pratama', 'nik' => '3205011995110001', 'gender' => 'L', 'medical_conditions' => 'Tidak Ada'],
            ],
            'payment_method' => 'cash_pos',
        ];

        $response = $this->postJson(route('portal.simaksi.register'), $payload);
        $response->assertStatus(200);

        $reg = SimaksiRegistration::where('leader_nik', '3205011995110001')->first();
        $this->assertNotNull($reg);
        $this->assertEquals($fullAddress, $reg->leader_address);
        $this->assertEquals($fullAddress, $reg->leader_city);

        // Verify lookup API also returns leader_address
        $lookupResponse = $this->getJson(route('portal.simaksi.lookup', ['code' => $reg->registration_code]));
        $lookupResponse->assertStatus(200);
        $lookupResponse->assertJsonPath('data.leader_address', $fullAddress);
    }

    public function test_dynamic_expedition_profile_and_portal_integration(): void
    {
        $superadmin = User::firstOrCreate(
            ['email' => 'admin_test@samsara.id'],
            [
                'name' => 'Superadmin Test',
                'password' => bcrypt('password'),
                'role' => 'superadmin',
                'status' => 'active',
            ]
        );

        $payload = [
            'expedition_name' => 'SAMSARA PAPANDAYAN',
            'basecamp_name' => 'Basecamp Papandayan',
            'mountain_name' => 'Gunung Papandayan',
            'mountain_elevation' => '2.665 MDPL',
            'default_daily_quota' => 250,
            'simaksi_fee_per_person' => 35000,
            'ojek_fee_per_trip' => 45000,
            'ojek_basecamp_share' => 7000,
            'ojek_route_name' => 'Basecamp → Kawah Pos 1',
            'porter_fee_per_trip' => 400000,
            'parking_motor_fee' => 12000,
            'parking_car_fee' => 30000,
            'trash_deposit_enabled' => 1,
            'trash_deposit_amount' => 60000,
            'admin_whatsapp' => '6281234567890',
            'admin_whatsapp_display' => '+62 812-3456-7890',
            'emergency_hotline' => '+62 812-3456-7890',
            'sar_frequency' => '151.400 MHz',
            'payment_bank_name' => 'BANK MANDIRI',
            'payment_bank_account' => '130-00-1234567-8',
            'payment_bank_holder' => 'KOPERASI PAPANDAYAN',
            'payment_qris_nmid' => 'ID1099998888',
            'payment_qris_name' => 'KOPERASI PAPANDAYAN',
        ];

        $updateResponse = $this->actingAs($superadmin)->post(route('management.settings.tariffs.update'), $payload);
        $updateResponse->assertRedirect(route('management.settings.tariffs'));

        // Verify AppSettings
        $this->assertEquals('SAMSARA PAPANDAYAN', AppSetting::get('expedition_name'));
        $this->assertEquals('Gunung Papandayan', AppSetting::get('mountain_name'));
        $this->assertEquals(250, (int) AppSetting::get('default_daily_quota'));
        $this->assertEquals('151.400 MHz', AppSetting::get('sar_frequency'));
        $this->assertEquals('130-00-1234567-8', AppSetting::get('payment_bank_account'));

        // Verify Portal reflects updated dynamic settings
        $portalResponse = $this->get(route('portal.home'));
        $portalResponse->assertStatus(200);
        $portalResponse->assertSee('SAMSARA PAPANDAYAN');
        $portalResponse->assertSee('Gunung Papandayan');
        $portalResponse->assertSee('2.665 MDPL');
        $portalResponse->assertSee('151.400 MHz');
        $portalResponse->assertSee('130-00-1234567-8');
        $portalResponse->assertSee('KOPERASI PAPANDAYAN');
        $portalResponse->assertSee('+62 812-3456-7890');

        // Verify Quota check API uses dynamic default quota
        $quotaCheck = $this->getJson(route('portal.simaksi.quota', ['date' => '2026-12-25']));
        $quotaCheck->assertStatus(200);
        $quotaCheck->assertJsonPath('max_quota', 250);
        $quotaCheck->assertJsonPath('remaining_quota', 250);
    }

    public function test_superadmin_can_upload_and_remove_expedition_logo(): void
    {
        Storage::fake('public');

        $superadmin = User::firstOrCreate(
            ['email' => 'admin_test@samsara.id'],
            [
                'name' => 'Superadmin Test',
                'password' => bcrypt('password'),
                'role' => 'superadmin',
                'status' => 'active',
            ]
        );

        $fakeLogo = UploadedFile::fake()->image('basecamp_logo.png', 200, 200);

        // 1. Upload new logo
        $response = $this->actingAs($superadmin)->post(route('management.settings.tariffs.update'), [
            'simaksi_fee_per_person' => 25000,
            'ojek_fee_per_trip' => 30000,
            'parking_motor_fee' => 10000,
            'parking_car_fee' => 25000,
            'trash_deposit_amount' => 50000,
            'app_logo' => $fakeLogo,
        ]);

        $response->assertSessionHas('success');

        $logoPath = AppSetting::get('app_logo');
        $this->assertNotNull($logoPath);
        Storage::disk('public')->assertExists($logoPath);
        $this->assertNotNull(AppSetting::getLogoUrl());

        // 2. Remove logo
        $removeResponse = $this->actingAs($superadmin)->post(route('management.settings.tariffs.update'), [
            'simaksi_fee_per_person' => 25000,
            'ojek_fee_per_trip' => 30000,
            'parking_motor_fee' => 10000,
            'parking_car_fee' => 25000,
            'trash_deposit_amount' => 50000,
            'remove_logo' => '1',
        ]);

        $removeResponse->assertSessionHas('success');
        $this->assertNull(AppSetting::get('app_logo'));
        Storage::disk('public')->assertMissing($logoPath);
    }

    public function test_superadmin_can_upload_and_remove_static_qris_image(): void
    {
        Storage::fake('public');

        $superadmin = User::firstOrCreate(
            ['email' => 'admin_test@samsara.id'],
            [
                'name' => 'Superadmin Test',
                'password' => bcrypt('password'),
                'role' => 'superadmin',
                'status' => 'active',
            ]
        );

        $fakeQris = UploadedFile::fake()->image('qris_bca.png', 300, 300);

        // 1. Upload new static QRIS
        $response = $this->actingAs($superadmin)->post(route('management.settings.tariffs.update'), [
            'simaksi_fee_per_person' => 25000,
            'ojek_fee_per_trip' => 30000,
            'parking_motor_fee' => 10000,
            'parking_car_fee' => 25000,
            'trash_deposit_amount' => 50000,
            'payment_qris_image' => $fakeQris,
        ]);

        $response->assertSessionHas('success');

        $qrisPath = AppSetting::get('payment_qris_image');
        $this->assertNotNull($qrisPath);
        Storage::disk('public')->assertExists($qrisPath);
        $this->assertNotNull(AppSetting::getQrisUrl());

        // Verify Portal displays uploaded static QRIS image
        $portalResponse = $this->get(route('portal.home'));
        $portalResponse->assertStatus(200);
        $portalResponse->assertSee($qrisPath);

        // 2. Remove static QRIS
        $removeResponse = $this->actingAs($superadmin)->post(route('management.settings.tariffs.update'), [
            'simaksi_fee_per_person' => 25000,
            'ojek_fee_per_trip' => 30000,
            'parking_motor_fee' => 10000,
            'parking_car_fee' => 25000,
            'trash_deposit_amount' => 50000,
            'remove_qris_image' => '1',
        ]);

        $removeResponse->assertSessionHas('success');
        $this->assertNull(AppSetting::get('payment_qris_image'));
        Storage::disk('public')->assertMissing($qrisPath);
    }

    public function test_updating_default_daily_quota_synchronizes_upcoming_unclosed_quotas(): void
    {
        $superadmin = User::factory()->create([
            'role' => 'superadmin',
            'status' => 'active',
        ]);

        AppSetting::set('default_daily_quota', 150, 'Kapasitas Kuota Harian Resmi', 'number', 'capacity');

        // Create 2 upcoming unclosed quotas with old default 150, and 1 closed emergency quota
        $tomorrow = \Carbon\Carbon::tomorrow()->toDateString();
        $nextWeek = \Carbon\Carbon::today()->addDays(7)->toDateString();
        $closedDate = \Carbon\Carbon::today()->addDays(10)->toDateString();

        $q1 = \App\Models\DailyQuota::create(['quota_date' => $tomorrow, 'max_quota' => 150, 'booked_quota' => 10, 'is_closed' => false]);
        $q2 = \App\Models\DailyQuota::create(['quota_date' => $nextWeek, 'max_quota' => 150, 'booked_quota' => 25, 'is_closed' => false]);
        $q3 = \App\Models\DailyQuota::create(['quota_date' => $closedDate, 'max_quota' => 150, 'booked_quota' => 0, 'is_closed' => true, 'closure_reason' => 'Bad Weather']);

        $response = $this->actingAs($superadmin)->post(route('management.settings.tariffs.update'), [
            'simaksi_fee_per_person' => 25000,
            'ojek_fee_per_trip' => 30000,
            'parking_motor_fee' => 10000,
            'parking_car_fee' => 25000,
            'trash_deposit_amount' => 50000,
            'default_daily_quota' => 300,
            'sync_upcoming_quotas' => '1',
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals(300, (int) AppSetting::get('default_daily_quota'));

        // q1 and q2 should be synchronized to 300
        $this->assertEquals(300, $q1->fresh()->max_quota);
        $this->assertEquals(300, $q2->fresh()->max_quota);

        // q3 was closed, remains closed
        $this->assertTrue((bool) $q3->fresh()->is_closed);
    }
}
