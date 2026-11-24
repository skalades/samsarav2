<?php

namespace Tests\Feature;

use App\Models\ClimberMember;
use App\Models\OjekDriver;
use App\Models\OjekSettlement;
use App\Models\OjekTrip;
use App\Models\SimaksiRegistration;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OjekManagementAndVoucherTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $cashier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::firstOrCreate(
            ['email' => 'admin_test@samsara.id'],
            [
                'name' => 'Admin Test',
                'password' => bcrypt('secret'),
                'role' => 'superadmin',
                'status' => 'active',
            ]
        );

        $this->cashier = User::firstOrCreate(
            ['email' => 'cashier_test@samsara.id'],
            [
                'name' => 'Kasir Test',
                'password' => bcrypt('secret'),
                'role' => 'kasir',
                'status' => 'active',
            ]
        );
    }

    public function test_driver_crud_and_status_toggle(): void
    {
        $this->actingAs($this->admin);

        // 1. Create driver
        $response = $this->post(route('management.ojek.store_driver'), [
            'registration_number' => 'OJK-99',
            'name' => 'Mang Jaja Sutarja',
            'plate_number' => 'D 9999 OJK',
            'phone' => '08129999999',
            'status' => 'standby',
            'notes' => 'Driver uji coba',
        ]);

        $response->assertRedirect(route('management.ojek.drivers'));
        $this->assertDatabaseHas('ojek_drivers', [
            'registration_number' => 'OJK-99',
            'name' => 'Mang Jaja Sutarja',
            'plate_number' => 'D 9999 OJK',
        ]);

        $driver = OjekDriver::where('registration_number', 'OJK-99')->first();

        // 2. Toggle status
        $this->post(route('management.ojek.toggle_driver', $driver->id));
        $this->assertEquals('off_duty', $driver->fresh()->status);

        $this->post(route('management.ojek.toggle_driver', $driver->id));
        $this->assertEquals('standby', $driver->fresh()->status);
    }

    public function test_ojek_dispatch_flow_and_completion(): void
    {
        $this->actingAs($this->admin);

        $driver = OjekDriver::firstOrCreate(
            ['registration_number' => 'OJK-88'],
            [
                'name' => 'Kang Asep Uji',
                'plate_number' => 'Z 8888 AA',
                'status' => 'standby',
            ]
        );

        $trip = OjekTrip::create([
            'trip_code' => 'VCR-OJK-TEST-001',
            'passenger_name' => 'Budi Santoso',
            'fare_amount' => 30000,
            'driver_share' => 25000,
            'basecamp_share' => 5000,
            'trip_status' => 'voucher_issued',
            'settlement_status' => 'unsettled',
        ]);

        // Assign driver with assign_and_print action
        $assignResponse = $this->post(route('management.ojek.assign', $trip->id), [
            'driver_id' => $driver->id,
            'action' => 'assign_and_print',
        ]);
        $assignResponse->assertRedirect(route('management.ojek.print_voucher', $trip->id));

        $this->assertEquals('assigned', $trip->fresh()->trip_status);
        $this->assertEquals('on_trip', $driver->fresh()->status);

        // Print voucher should now show the assigned driver and plate number
        $printResponse = $this->get(route('management.ojek.print_voucher', $trip->id));
        $printResponse->assertStatus(200);
        $printResponse->assertSee('Kang Asep Uji');
        $printResponse->assertSee('OJK-88');
        $printResponse->assertSee('Z 8888 AA');

        // Complete trip
        $completeResponse = $this->post(route('management.ojek.complete', $trip->id));
        $completeResponse->assertRedirect(route('management.ojek.index'));

        $this->assertEquals('completed', $trip->fresh()->trip_status);
        $this->assertEquals('standby', $driver->fresh()->status);

        // Test print voucher passing driver_id query directly
        $trip2 = OjekTrip::create([
            'trip_code' => 'VCR-OJK-TEST-001B',
            'passenger_name' => 'Siti Nurhaliza',
            'fare_amount' => 30000,
            'driver_share' => 25000,
            'basecamp_share' => 5000,
            'trip_status' => 'voucher_issued',
            'settlement_status' => 'unsettled',
        ]);

        $directPrint = $this->get(route('management.ojek.print_voucher', $trip2->id).'?driver_id='.$driver->id.'&assign=1');
        $directPrint->assertStatus(200);
        $directPrint->assertSee('Kang Asep Uji');
        $directPrint->assertSee('OJK-88');
        $directPrint->assertSee('Z 8888 AA');
        $this->assertEquals($driver->id, $trip2->fresh()->driver_id);
    }

    public function test_settlement_payout_and_print(): void
    {
        $this->actingAs($this->cashier);

        $driver = OjekDriver::firstOrCreate(
            ['registration_number' => 'OJK-77'],
            [
                'name' => 'Mang Agus Settlement',
                'plate_number' => 'D 7777 BB',
                'status' => 'standby',
            ]
        );

        // Create completed trip for driver
        $trip = OjekTrip::create([
            'trip_code' => 'VCR-OJK-TEST-002',
            'driver_id' => $driver->id,
            'passenger_name' => 'Pendaki Ceria',
            'fare_amount' => 30000,
            'driver_share' => 25000,
            'basecamp_share' => 5000,
            'trip_status' => 'completed',
            'settlement_status' => 'unsettled',
            'completed_at' => Carbon::now(),
        ]);

        $this->assertEquals(25000, $driver->pending_payout);

        // Process payout
        $payoutResponse = $this->post(route('management.ojek.payout', $driver->id), [
            'payment_method' => 'cash',
            'notes' => 'Payout shift sore',
        ]);

        $settlement = OjekSettlement::where('driver_id', $driver->id)->latest()->first();
        $this->assertNotNull($settlement);
        $this->assertEquals(25000, $settlement->total_amount);
        $this->assertEquals('settled', $trip->fresh()->settlement_status);

        // Test print settlement view
        $printResponse = $this->get(route('management.ojek.print_settlement', $settlement->id));
        $printResponse->assertStatus(200);
        $printResponse->assertSee($settlement->settlement_code);
        $printResponse->assertSee('Mang Agus Settlement');
    }

    public function test_print_ojek_voucher_at_gate_checkin(): void
    {
        $this->actingAs($this->admin);

        $registration = SimaksiRegistration::create([
            'registration_code' => 'SMK-'.date('Ym').'-991',
            'leader_name' => 'Rahmat Hidayat',
            'leader_nik' => '3201123456780001',
            'leader_email' => 'rahmat@gmail.com',
            'leader_city' => 'Garut',
            'leader_phone' => '081233445566',
            'total_climbers' => 2,
            'hiking_date' => Carbon::today(),
            'return_date' => Carbon::tomorrow(),
            'ojek_count' => 2,
            'ojek_fee' => 60000,
            'simaksi_fee' => 50000,
            'total_amount' => 110000,
            'status' => 'paid',
            'payment_status' => 'paid',
            'group_photo_path' => 'group_photos/rahmat.jpg',
            'emergency_contact_name' => 'Ibu Rahmat',
            'emergency_contact_phone' => '081299991111',
            'emergency_relationship' => 'Orang Tua',
        ]);

        $member = ClimberMember::create([
            'simaksi_registration_id' => $registration->id,
            'full_name' => 'Rahmat Hidayat',
            'nik' => '3201123456780001',
            'gender' => 'L',
            'phone' => '081233445566',
            'is_leader' => true,
        ]);

        $driver1 = OjekDriver::firstOrCreate(
            ['registration_number' => 'OJK-11'],
            ['name' => 'Mang Dede Ojek', 'plate_number' => 'Z 1111 DD', 'status' => 'standby']
        );
        $driver2 = OjekDriver::firstOrCreate(
            ['registration_number' => 'OJK-12'],
            ['name' => 'Kang Enjang Ojek', 'plate_number' => 'Z 1212 EE', 'status' => 'standby']
        );

        // Gatekeeper: pemeriksaan naik
        $this->post(route('management.gate.ascent.submit', $registration->id), [
            'present_members' => [$member->id],
            'photo_verified' => 1,
            'gear' => [['name' => 'Tenda', 'checked' => 1]],
            'logistics' => [['name' => 'Botol plastik', 'unit' => 'botol', 'qty' => 4]],
        ])->assertRedirect(route('management.gate.index'));
        $this->assertEquals('gate_cleared', $registration->fresh()->status);

        // Kasir: berangkatkan + tugaskan driver ojek
        $releaseResponse = $this->post(route('management.desk.release', $registration->id), [
            'ojek_driver_ids' => [$driver1->id, $driver2->id],
        ]);

        $releaseResponse->assertRedirect(route('management.desk.show', $registration->id));
        $this->assertEquals('checked_in', $registration->fresh()->status);
        $this->assertEquals('on_trip', $driver1->fresh()->status);
        $this->assertEquals('on_trip', $driver2->fresh()->status);

        // Access print voucher
        $printResponse = $this->get(route('management.gate.print_ojek_voucher', $registration->id));
        $printResponse->assertStatus(200);
        $printResponse->assertSee('KUPON TIKET OJEK RESMI');
        $printResponse->assertSee('Rahmat Hidayat');
        $printResponse->assertSee('Basecamp → Pos 1');
        $printResponse->assertSee('Mang Dede Ojek');
        $printResponse->assertSee('Z 1111 DD');
    }

    public function test_print_parking_pass_at_gate_checkout(): void
    {
        $this->actingAs($this->admin);

        $registration = SimaksiRegistration::create([
            'registration_code' => 'SMK-'.date('Ym').'-992',
            'leader_name' => 'Fajar Nugraha',
            'leader_nik' => '3201123456780002',
            'leader_email' => 'fajar@gmail.com',
            'leader_city' => 'Bandung',
            'leader_phone' => '081277889900',
            'total_climbers' => 3,
            'hiking_date' => Carbon::yesterday(),
            'return_date' => Carbon::today(),
            'has_vehicle' => true,
            'motorcycle_count' => 2,
            'car_count' => 1,
            'parking_fee' => 45000,
            'simaksi_fee' => 75000,
            'total_amount' => 120000,
            'status' => 'checked_in',
            'payment_status' => 'paid',
            'checked_in_at' => Carbon::yesterday(),
            'trash_deposit_amount' => 0,
            'trash_deposit_status' => 'waived',
            'emergency_contact_name' => 'Bapak Fajar',
            'emergency_contact_phone' => '081288882222',
            'emergency_relationship' => 'Orang Tua',
        ]);

        // Gatekeeper: pemeriksaan turun, lalu kasir menyelesaikan
        $this->post(route('management.gate.descent.submit', $registration->id), [
            'notes' => 'Turun tepat waktu dan bersih',
        ])->assertRedirect(route('management.gate.index'));
        $this->assertEquals('returned', $registration->fresh()->status);

        $finalizeResponse = $this->post(route('management.desk.finalize', $registration->id));
        $finalizeResponse->assertRedirect(route('management.desk.show', $registration->id));
        $this->assertEquals('completed', $registration->fresh()->status);
        $this->assertEquals('waived', $registration->fresh()->trash_deposit_status);

        // Access print parking pass
        $printResponse = $this->get(route('management.gate.print_parking_pass', $registration->id));
        $printResponse->assertStatus(200);
        $printResponse->assertSee('TIKET IZIN KELUAR PARKIR');
        $printResponse->assertSee('Fajar Nugraha');
        $printResponse->assertSee('2 MOTOR & 1 MOBIL', false);
        $printResponse->assertSee('LUNAS RESMI');
    }
}
