<?php

namespace Tests\Feature;

use App\Models\ClimberMember;
use App\Models\DailyQuota;
use App\Models\SimaksiRegistration;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DigitalTicketAndPdfDownloadTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $gateOfficer;

    protected SimaksiRegistration $registration;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin Tiket',
            'email' => 'admin-ticket@samsara.id',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->gateOfficer = User::create([
            'name' => 'Petugas Gerbang',
            'email' => 'gate-ticket@samsara.id',
            'password' => bcrypt('password'),
            'role' => 'gate_officer',
        ]);

        $hikingDate = Carbon::now()->addDays(3)->toDateString();
        DailyQuota::create([
            'quota_date' => $hikingDate,
            'max_quota' => 150,
            'booked_quota' => 3,
        ]);

        $this->registration = SimaksiRegistration::create([
            'registration_code' => 'SMK-202611-TEST1',
            'leader_name' => 'Aditya Pratama',
            'leader_nik' => '3205011122330001',
            'leader_phone' => '081234567890',
            'leader_city' => 'Bandung',
            'emergency_contact_name' => 'Ibu Aditya',
            'emergency_contact_phone' => '081234567891',
            'emergency_relationship' => 'Orang Tua',
            'hiking_date' => $hikingDate,
            'return_date' => Carbon::now()->addDays(4)->toDateString(),
            'total_climbers' => 3,
            'status' => 'paid',
            'simaksi_fee' => 75000,
            'ojek_fee' => 60000,
            'ojek_count' => 2,
            'tent_fee' => 0,
            'tent_count' => 0,
            'has_vehicle' => true,
            'motorcycle_count' => 1,
            'parking_fee' => 10000,
            'total_amount' => 145000,
            'payment_status' => 'paid',
            'payment_method' => 'qris',
            'trash_deposit_amount' => 50000,
            'trash_deposit_status' => 'unpaid',
        ]);

        ClimberMember::create([
            'simaksi_registration_id' => $this->registration->id,
            'full_name' => 'Aditya Pratama',
            'nik' => '3205011122330001',
            'gender' => 'L',
            'is_leader' => true,
        ]);

        ClimberMember::create([
            'simaksi_registration_id' => $this->registration->id,
            'full_name' => 'Bima Sakti',
            'nik' => '3205011122330002',
            'gender' => 'L',
            'is_leader' => false,
        ]);

        ClimberMember::create([
            'simaksi_registration_id' => $this->registration->id,
            'full_name' => 'Citra Lestari',
            'nik' => '3205011122330003',
            'gender' => 'P',
            'is_leader' => false,
        ]);
    }

    /**
     * Test public interactive web ticket view.
     */
    public function test_can_view_interactive_digital_ticket_web_page(): void
    {
        $response = $this->get(route('portal.simaksi.ticket', $this->registration->registration_code));

        $response->assertStatus(200);
        $response->assertSee('SMK-202611-TEST1');
        $response->assertSee('Aditya Pratama');
        $response->assertSee('Bima Sakti');
        $response->assertSee('Citra Lestari');
        $response->assertSee('Unduh PDF Resmi');
    }

    /**
     * Test public download of the official SIMAKSI PDF.
     */
    public function test_can_download_simaksi_pdf_ticket_publicly(): void
    {
        $response = $this->get(route('portal.simaksi.ticket_pdf', $this->registration->registration_code));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');

        $contentDisposition = $response->headers->get('content-disposition');
        $this->assertStringContainsString('E-Tiket_SIMAKSI_SMK-202611-TEST1.pdf', $contentDisposition);

        // Verify content begins with PDF magic header
        $content = $response->getContent();
        $this->assertStringStartsWith('%PDF-', $content);
    }

    /**
     * Test management staff download of SIMAKSI PDF.
     */
    public function test_management_staff_can_download_simaksi_pdf_ticket(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('management.simaksi.ticket_pdf', $this->registration->id));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }

    /**
     * Test gate officer download of SIMAKSI PDF.
     */
    public function test_gate_officer_can_download_simaksi_pdf_ticket(): void
    {
        // Gatekeeper tidak mencetak dokumen: dialihkan ke layar Gate
        $this->actingAs($this->gateOfficer)
            ->get(route('management.gate.ticket_pdf', $this->registration->id))
            ->assertRedirect(route('management.gate.index'));

        $kasir = User::create([
            'name' => 'Kasir Tiket',
            'email' => 'kasir-ticket@samsara.id',
            'password' => bcrypt('password'),
            'role' => 'kasir',
        ]);

        $response = $this->actingAs($kasir)
            ->get(route('management.gate.ticket_pdf', $this->registration->id));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }

    /**
     * Test lookup endpoint includes download URLs and flags.
     */
    public function test_lookup_endpoint_returns_ticket_urls(): void
    {
        $response = $this->getJson(route('portal.simaksi.lookup', ['code' => $this->registration->registration_code]));

        $response->assertStatus(200);
        $response->assertJsonPath('data.ticket_url', route('portal.simaksi.ticket', $this->registration->registration_code));
        $response->assertJsonPath('data.pdf_download_url', route('portal.simaksi.ticket_pdf', $this->registration->registration_code));
        $response->assertJsonPath('data.can_download_ticket', true);
    }

    /**
     * Test non-existent ticket code returns 404 cleanly.
     */
    public function test_invalid_ticket_code_returns_404(): void
    {
        $response1 = $this->get(route('portal.simaksi.ticket', 'SMK-NONEXISTENT'));
        $response1->assertStatus(404);

        $response2 = $this->get(route('portal.simaksi.ticket_pdf', 'SMK-NONEXISTENT'));
        $response2->assertStatus(404);
    }
}
