<?php

namespace Tests\Feature;

use App\Models\SimaksiRegistration;
use App\Models\User;
use App\Services\CheckInService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SimaksiMedicalAndKtpPurgeTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_register_simaksi_with_medical_conditions_and_ktp(): void
    {
        Storage::fake('public');
        Storage::fake('local');

        $tomorrow = Carbon::now()->addDay()->toDateString();
        $ktpFile = UploadedFile::fake()->image('ktp_leader.jpg', 600, 400);

        $payload = [
            'hiking_date' => $tomorrow,
            'leader_name' => 'Budi Santoso',
            'leader_nik' => '3205011234560001',
            'leader_phone' => '081234567890',
            'leader_city' => 'Garut',
            'medical_conditions' => 'Riwayat Asma Ringan',
            'emergency_contact_name' => 'Siti Aminah',
            'emergency_contact_phone' => '081987654321',
            'emergency_relationship' => 'Orang Tua',
            'payment_method' => 'cash_pos',
            'leader_ktp' => $ktpFile,
            'members' => [
                [
                    'full_name' => 'Budi Santoso',
                    'nik' => '3205011234560001',
                    'gender' => 'L',
                    'medical_conditions' => 'Riwayat Asma Ringan',
                ],
                [
                    'full_name' => 'Ahmad Dani',
                    'nik' => '3205011234560002',
                    'gender' => 'L',
                    'medical_conditions' => 'Alergi Dingin Ekstrem',
                ],
            ],
        ];

        $response = $this->postJson(route('portal.simaksi.register'), $payload);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $regCode = $response->json('registration_code');
        $reg = SimaksiRegistration::where('registration_code', $regCode)->first();
        $this->assertNotNull($reg);
        $this->assertEquals('Riwayat Asma Ringan', $reg->medical_conditions);
        $this->assertNotNull($reg->leader_ktp_path);
        Storage::disk('local')->assertExists($reg->leader_ktp_path);
        Storage::disk('public')->assertMissing($reg->leader_ktp_path);

        $members = $reg->members;
        $this->assertCount(2, $members);
        $this->assertEquals('Riwayat Asma Ringan', $members[0]->medical_conditions);
        $this->assertEquals('Alergi Dingin Ekstrem', $members[1]->medical_conditions);
    }

    public function test_can_register_solo_climber_1_person(): void
    {
        Storage::fake('public');

        $tomorrow = Carbon::now()->addDay()->toDateString();
        $ktpFile = UploadedFile::fake()->image('ktp_solo.jpg', 600, 400);

        $payload = [
            'hiking_date' => $tomorrow,
            'leader_name' => 'Fajar Solo Explorer',
            'leader_nik' => '3205011234560077',
            'leader_phone' => '081299998888',
            'leader_city' => 'Jakarta Barat',
            'medical_conditions' => 'Tidak Ada',
            'emergency_contact_name' => 'Bapak Fajar',
            'emergency_contact_phone' => '081299997777',
            'emergency_relationship' => 'Orang Tua',
            'payment_method' => 'cash_pos',
            'leader_ktp' => $ktpFile,
            'members' => [
                [
                    'full_name' => 'Fajar Solo Explorer',
                    'nik' => '3205011234560077',
                    'gender' => 'L',
                    'medical_conditions' => 'Tidak Ada',
                ],
            ],
        ];

        $response = $this->postJson(route('portal.simaksi.register'), $payload);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $regCode = $response->json('registration_code');
        $reg = SimaksiRegistration::where('registration_code', $regCode)->first();
        $this->assertNotNull($reg);
        $this->assertEquals(1, $reg->total_climbers);
        $this->assertEquals(25000, $reg->simaksi_fee);
        $this->assertCount(1, $reg->members);
        $this->assertTrue((bool) $reg->members[0]->is_leader);
    }

    public function test_ktp_is_automatically_purged_on_gate_checkout(): void
    {
        Storage::fake('public');

        // Create dummy KTP file
        $ktpPath = 'ktp/test_ktp_'.uniqid().'.jpg';
        Storage::disk('public')->put($ktpPath, 'fake_ktp_content');
        Storage::disk('public')->assertExists($ktpPath);

        $user = User::first() ?? User::create([
            'name' => 'Ranger Pos 1',
            'email' => 'ranger1@samsara.id',
            'password' => bcrypt('password'),
            'role' => 'gate_officer',
        ]);

        $reg = SimaksiRegistration::create([
            'registration_code' => 'SMK-TEST-001',
            'leader_name' => 'Rian Hidayat',
            'leader_nik' => '3205011234560003',
            'leader_phone' => '081234567899',
            'leader_city' => 'Bandung',
            'leader_ktp_path' => $ktpPath,
            'medical_conditions' => 'Tidak Ada',
            'emergency_contact_name' => 'Ibu Rian',
            'emergency_contact_phone' => '081234567898',
            'hiking_date' => Carbon::now()->toDateString(),
            'total_climbers' => 1,
            'status' => 'checked_in',
            'simaksi_fee' => 25000,
            'total_amount' => 25000,
            'payment_status' => 'paid',
            'payment_method' => 'qris',
            'trash_deposit_status' => 'held',
            'trash_deposit_amount' => 50000,
        ]);

        // Process Check-out
        $checkInService = app(CheckInService::class);
        $completedReg = $checkInService->checkOut($reg, $user->id, true, 'Semua sehat dan sampah turun');

        $this->assertEquals('completed', $completedReg->status);
        $this->assertNull($completedReg->leader_ktp_path);

        // Assert physical file on disk was destroyed
        Storage::disk('public')->assertMissing($ktpPath);
    }

    public function test_purge_command_cleans_orphaned_completed_ktp_files(): void
    {
        Storage::fake('public');

        $ktpPath = 'ktp/orphaned_completed_ktp.jpg';
        Storage::disk('public')->put($ktpPath, 'orphaned_ktp_content');
        Storage::disk('public')->assertExists($ktpPath);

        $reg = SimaksiRegistration::create([
            'registration_code' => 'SMK-TEST-ORPHAN',
            'leader_name' => 'Dedi Mulyadi',
            'leader_nik' => '3205011234560099',
            'leader_phone' => '081234567888',
            'leader_city' => 'Jakarta',
            'leader_ktp_path' => $ktpPath,
            'medical_conditions' => 'Tidak Ada',
            'emergency_contact_name' => 'Kontak',
            'emergency_contact_phone' => '081234567887',
            'hiking_date' => Carbon::now()->subDays(2)->toDateString(),
            'total_climbers' => 1,
            'status' => 'completed',
            'simaksi_fee' => 25000,
            'total_amount' => 25000,
            'payment_status' => 'paid',
            'payment_method' => 'cash_pos',
        ]);

        Artisan::call('samsara:purge-completed-ktp');

        $reg->refresh();
        $this->assertNull($reg->leader_ktp_path);
        Storage::disk('public')->assertMissing($ktpPath);
    }

    public function test_simaksi_index_displays_proof_modal_trigger_for_uploaded_proof(): void
    {
        $officer = User::create([
            'name' => 'Petugas Kasir',
            'email' => 'kasir@samsara.id',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $reg = SimaksiRegistration::create([
            'registration_code' => 'SMK-TEST-PROOF-01',
            'leader_name' => 'Joko Anwar',
            'leader_nik' => '3205011234560777',
            'leader_phone' => '081234567777',
            'leader_city' => 'Bandung',
            'emergency_contact_name' => 'Kontak',
            'emergency_contact_phone' => '081234567776',
            'hiking_date' => Carbon::now()->addDay()->toDateString(),
            'total_climbers' => 2,
            'status' => 'pending',
            'simaksi_fee' => 50000,
            'total_amount' => 50000,
            'payment_status' => 'pending',
            'payment_method' => 'qris',
            'payment_proof_path' => 'proofs/test_transfer.jpg',
        ]);

        $response = $this->actingAs($officer)->get(route('management.simaksi.index'));

        $response->assertStatus(200);
        $response->assertSee('showProofModal');
        $response->assertSee('paymentProofModal');
        $response->assertSee('Bukti Bayar');
    }
}
