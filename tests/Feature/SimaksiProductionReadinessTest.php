<?php

namespace Tests\Feature;

use App\Models\DailyQuota;
use App\Models\SimaksiRegistration;
use App\Models\User;
use App\Services\SimaksiService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SimaksiProductionReadinessTest extends TestCase
{
    use RefreshDatabase;

    public function test_no_double_quota_increment_on_approval_and_quota_rollback_on_rejection(): void
    {
        $hikingDate = Carbon::now()->addDays(2)->toDateString();
        $quota = DailyQuota::create([
            'quota_date' => $hikingDate,
            'max_quota' => 150,
            'booked_quota' => 0,
        ]);

        $admin = User::create([
            'name' => 'Admin Samsara',
            'email' => 'admin@samsara.id',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        // 1. Initial registration for 4 climbers
        $payload = [
            'hiking_date' => $hikingDate,
            'leader_name' => 'Bambang Sudirman',
            'leader_nik' => '3205011234560010',
            'leader_phone' => '081234567810',
            'leader_city' => 'Garut',
            'emergency_contact_name' => 'Ibu Bambang',
            'emergency_contact_phone' => '081234567811',
            'payment_method' => 'cash_pos',
            'members' => [
                ['full_name' => 'Bambang Sudirman', 'nik' => '3205011234560010', 'gender' => 'L'],
                ['full_name' => 'Anggota 1', 'nik' => '3205011234560011', 'gender' => 'L'],
                ['full_name' => 'Anggota 2', 'nik' => '3205011234560012', 'gender' => 'P'],
                ['full_name' => 'Anggota 3', 'nik' => '3205011234560013', 'gender' => 'L'],
            ],
        ];

        $response = $this->postJson(route('portal.simaksi.register'), $payload);
        $response->assertStatus(200);
        $regCode = $response->json('registration_code');

        $quota->refresh();
        $this->assertEquals(4, $quota->booked_quota, 'Quota must be booked for 4 climbers upon registration.');

        $reg = SimaksiRegistration::where('registration_code', $regCode)->first();
        $this->assertNotNull($reg);

        // 2. Approve application: booked_quota must NOT increase again to 8!
        $simaksiService = app(SimaksiService::class);
        $simaksiService->approve($reg, $admin->id, 'Dokumen lengkap dan valid.');

        $quota->refresh();
        $this->assertEquals(4, $quota->booked_quota, 'Booked quota must remain 4 and NOT double-increment.');

        // 3. Test another registration for 2 climbers that gets rejected
        $reg2 = SimaksiRegistration::create([
            'registration_code' => 'SMK-TEST-REJECT',
            'leader_name' => 'Calon Ditolak',
            'leader_nik' => '3205011234560099',
            'leader_phone' => '081234567899',
            'hiking_date' => $hikingDate,
            'total_climbers' => 2,
            'status' => 'pending',
            'simaksi_fee' => 50000,
            'total_amount' => 50000,
            'payment_status' => 'unpaid',
            'payment_method' => 'transfer',
            'emergency_contact_name' => 'Kontak Darurat',
            'emergency_contact_phone' => '081234567898',
        ]);
        $quota->increment('booked_quota', 2);
        $this->assertEquals(6, $quota->fresh()->booked_quota);

        // Reject reg2: quota must be refunded/decremented back to 4!
        $simaksiService->reject($reg2, $admin->id, 'Dokumen NIK tidak valid.');

        $quota->refresh();
        $this->assertEquals(4, $quota->booked_quota, 'Booked quota must be decremented on rejection.');
    }

    public function test_status_synchronization_with_checked_in_label(): void
    {
        $reg = SimaksiRegistration::create([
            'registration_code' => 'SMK-202611-999',
            'leader_name' => 'Ahmad Dani',
            'leader_nik' => '3205011234560020',
            'leader_phone' => '081234567820',
            'hiking_date' => Carbon::now()->toDateString(),
            'total_climbers' => 1,
            'status' => 'checked_in',
            'simaksi_fee' => 25000,
            'total_amount' => 25000,
            'payment_status' => 'paid',
            'payment_method' => 'cash_pos',
            'emergency_contact_name' => 'Kontak',
            'emergency_contact_phone' => '081234567821',
        ]);

        $response = $this->getJson(route('portal.simaksi.lookup', ['code' => 'SMK-202611-999']));
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'status' => 'checked_in',
                'status_label' => 'SEDANG MENDAKI (DI JALUR)',
            ],
        ]);
    }

    public function test_ktp_protected_route_requires_authentication(): void
    {
        Storage::fake('public');
        $ktpPath = 'ktp/secret_leader_ktp.jpg';
        Storage::disk('public')->put($ktpPath, 'image_data');

        $reg = SimaksiRegistration::create([
            'registration_code' => 'SMK-202611-KTP',
            'leader_name' => 'Rudi',
            'leader_nik' => '3205011234560030',
            'leader_phone' => '081234567830',
            'hiking_date' => Carbon::now()->toDateString(),
            'total_climbers' => 1,
            'status' => 'pending',
            'leader_ktp_path' => $ktpPath,
            'simaksi_fee' => 25000,
            'total_amount' => 25000,
            'payment_status' => 'unpaid',
            'payment_method' => 'cash_pos',
            'emergency_contact_name' => 'Kontak',
            'emergency_contact_phone' => '081234567831',
        ]);

        // Unauthenticated access must redirect to login
        $guestResponse = $this->get(route('management.simaksi.ktp', $reg->id));
        $guestResponse->assertRedirect(route('management.login'));

        // Authenticated officer can view KTP
        $officer = User::create([
            'name' => 'Petugas Verifikator',
            'email' => 'verifikator@samsara.id',
            'password' => bcrypt('password'),
            'role' => 'kasir',
        ]);

        $authResponse = $this->actingAs($officer)->get(route('management.simaksi.ktp', $reg->id));
        $authResponse->assertStatus(200);
    }

    public function test_rbac_least_privilege_enforcement(): void
    {
        // Peran lama 'auditor' kini digabung ke superadmin
        $auditor = User::create([
            'name' => 'Auditor Lapangan',
            'email' => 'auditor@samsara.id',
            'password' => bcrypt('password'),
            'role' => 'auditor',
        ]);
        $this->assertTrue($auditor->isSuperadmin());

        $gatekeeper = User::create([
            'name' => 'Gatekeeper',
            'email' => 'gatekeeper-rbac@samsara.id',
            'password' => bcrypt('password'),
            'role' => 'gatekeeper',
        ]);
        $ranger = User::create([
            'name' => 'Ranger',
            'email' => 'ranger-rbac@samsara.id',
            'password' => bcrypt('password'),
            'role' => 'ranger',
        ]);

        $reg = SimaksiRegistration::create([
            'registration_code' => 'SMK-202611-RBAC',
            'leader_name' => 'Ujang',
            'leader_nik' => '3205011234560040',
            'leader_phone' => '081234567840',
            'hiking_date' => Carbon::now()->toDateString(),
            'total_climbers' => 1,
            'status' => 'pending',
            'simaksi_fee' => 25000,
            'total_amount' => 25000,
            'payment_status' => 'unpaid',
            'payment_method' => 'cash_pos',
            'emergency_contact_name' => 'Kontak',
            'emergency_contact_phone' => '081234567841',
        ]);

        // Auditor CAN view audit log
        $this->actingAs($auditor)->get(route('management.audit.index'))->assertStatus(200);

        // Gatekeeper & Ranger CANNOT approve SIMAKSI, audit, or open the cashier modules
        $approveResponse = $this->actingAs($gatekeeper)->post(route('management.simaksi.approve', $reg->id));
        $approveResponse->assertRedirect(route('management.gate.index'));
        $approveResponse->assertSessionHas('error');
        $this->actingAs($gatekeeper)->get(route('management.pos.index'))->assertRedirect(route('management.gate.index'));
        $this->actingAs($gatekeeper)->get(route('management.ranger.index'))->assertRedirect(route('management.gate.index'));

        $this->actingAs($ranger)->get(route('management.audit.index'))->assertRedirect(route('management.ranger.index'));
        $this->actingAs($ranger)->get(route('management.gate.index'))->assertRedirect(route('management.ranger.index'));
        $this->actingAs($ranger)->get(route('management.dashboard'))->assertRedirect(route('management.ranger.index'));
        $this->assertEquals('pending', $reg->fresh()->status);

        // Kasir CAN mark paid
        $kasir = User::create([
            'name' => 'Kasir Loket',
            'email' => 'kasir@samsara.id',
            'password' => bcrypt('password'),
            'role' => 'kasir',
        ]);

        $paidResponse = $this->actingAs($kasir)->post(route('management.simaksi.paid', $reg->id));
        $paidResponse->assertRedirect(route('management.simaksi.show', $reg->id));
        $this->assertEquals('paid', $reg->fresh()->status);
    }

    public function test_lookup_masks_pii_and_medical_data_for_privacy(): void
    {
        $reg = SimaksiRegistration::create([
            'registration_code' => 'SMK-202611-PDPTEST',
            'leader_name' => 'Wahyudi Santoso',
            'leader_nik' => '3205011234567890',
            'leader_phone' => '081234567890',
            'hiking_date' => Carbon::now()->toDateString(),
            'total_climbers' => 1,
            'status' => 'pending',
            'medical_conditions' => 'Riwayat Asma Kronis',
            'emergency_contact_name' => 'Istri Wahyu',
            'emergency_contact_phone' => '081987654321',
            'simaksi_fee' => 25000,
            'total_amount' => 25000,
            'payment_status' => 'unpaid',
            'payment_method' => 'cash_pos',
        ]);

        $response = $this->getJson(route('portal.simaksi.lookup', ['code' => 'SMK-202611-PDPTEST']));
        $response->assertStatus(200);

        // NIK and Emergency Phone must be masked for UU PDP compliance
        $data = $response->json('data');
        $this->assertStringContainsString('******', $data['leader_nik']);
        $this->assertStringContainsString('****', $data['emergency_contact_phone']);

        // Sensitive medical history must not be exposed in raw text
        $this->assertStringNotContainsString('Riwayat Asma Kronis', $data['medical_conditions']);
        $this->assertNull($data['leader_ktp_url']);
    }

    public function test_quick_login_disabled_when_not_allowed(): void
    {
        config(['app.allow_quick_login' => false]);

        $response = $this->get(route('management.quick_login', ['role' => 'superadmin']));
        $response->assertStatus(403);
    }

    public function test_tariff_calculator_service_centralized_calculation(): void
    {
        $calcService = app(\App\Services\TariffCalculatorService::class);
        $result = $calcService->calculate(
            totalClimbers: 4,
            ojekCount: 2,
            hasVehicle: true,
            motorcycleCount: 2,
            carCount: 1,
            uniqueCode: 250
        );

        $this->assertEquals(4 * 25000, $result['simaksi_fee']);
        $this->assertEquals(2 * 30000, $result['ojek_fee']);
        $this->assertEquals((2 * 10000) + (1 * 25000), $result['parking_fee']);
        $expectedSubtotal = (4 * 25000) + (2 * 30000) + ((2 * 10000) + (1 * 25000));
        $this->assertEquals($expectedSubtotal, $result['subtotal']);
        $this->assertEquals($expectedSubtotal + 250, $result['total_amount']);
        // Deposit sampah dinonaktifkan sementara (default)
        $this->assertEquals(0, $result['trash_deposit_amount']);
        $this->assertEquals('waived', $result['trash_deposit_status']);

        \App\Models\AppSetting::set('trash_deposit_enabled', true, 'Status Deposit Sampah', 'boolean');
        $enabled = $calcService->calculate(totalClimbers: 1, ojekCount: 0, hasVehicle: false, motorcycleCount: 0, carCount: 0, uniqueCode: 0);
        $this->assertEquals(50000, $enabled['trash_deposit_amount']);
    }
}
