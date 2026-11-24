<?php

namespace Tests\Feature;

use App\Models\ClimberMember;
use App\Models\SimaksiRegistration;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GatekeeperScannerAuditTest extends TestCase
{
    use RefreshDatabase;

    protected User $gatekeeper;
    protected User $ranger;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gatekeeper = User::create([
            'name' => 'Petugas Gatekeeper Basecamp',
            'email' => 'gatekeeper@samsara.id',
            'password' => bcrypt('password'),
            'role' => 'gatekeeper',
            'status' => 'active',
        ]);

        $this->ranger = User::create([
            'name' => 'Ranger Pos 1',
            'email' => 'ranger1@samsara.id',
            'password' => bcrypt('password'),
            'role' => 'ranger',
            'status' => 'active',
        ]);
    }

    private function createRegistration(string $code, string $status = 'paid'): SimaksiRegistration
    {
        $reg = SimaksiRegistration::create([
            'registration_code' => $code,
            'leader_name' => 'd Pebryana Putra',
            'leader_nik' => '3205041202940002',
            'leader_email' => 'pebryana@example.com',
            'leader_phone' => '082187093648',
            'total_climbers' => 1,
            'hiking_date' => Carbon::today(),
            'return_date' => Carbon::tomorrow(),
            'simaksi_fee' => 30000,
            'total_amount' => 50834,
            'status' => $status,
            'payment_status' => 'paid',
            'emergency_contact_name' => 'Wati',
            'emergency_contact_phone' => '082187093648',
            'emergency_relationship' => 'Orang Tua',
        ]);

        ClimberMember::create([
            'simaksi_registration_id' => $reg->id,
            'full_name' => 'd Pebryana Putra',
            'nik' => '3205041202940002',
            'gender' => 'L',
            'is_leader' => true,
        ]);

        return $reg;
    }

    public function test_gatekeeper_view_renders_multi_camera_selector_and_scanner_controls(): void
    {
        $this->actingAs($this->gatekeeper);

        $response = $this->get(route('management.gate.index'));
        $response->assertOk();

        // 1. Verify scanner overlay & elements
        $response->assertSee('id="qrScannerOverlay"', false);
        $response->assertSee('id="qrCameraSelect"', false);
        $response->assertSee('id="btnSwitchCamera"', false);
        $response->assertSee('id="btnToggleTorch"', false);
        $response->assertSee('id="qrReaderViewport"', false);
        $response->assertSee('id="qrManualCodeInput"', false);

        // 2. Verify local offline-first asset is loaded
        $response->assertSee('assets/js/html5-qrcode.min.js', false);
    }

    public function test_scanner_decodes_and_routes_paid_ticket_via_exact_code(): void
    {
        $reg = $this->createRegistration('SMK-202611-50GQ', 'paid');

        $this->actingAs($this->gatekeeper)
            ->get(route('management.gate.index', ['code' => 'SMK-202611-50GQ']))
            ->assertRedirect(route('management.gate.ascent', $reg->id));
    }

    public function test_scanner_decodes_and_routes_checked_in_ticket_to_descent(): void
    {
        $reg = $this->createRegistration('SMK-202611-50GQ', 'checked_in');

        $this->actingAs($this->gatekeeper)
            ->get(route('management.gate.index', ['code' => 'SMK-202611-50GQ']))
            ->assertRedirect(route('management.gate.descent', $reg->id));
    }

    public function test_scanner_handles_raw_barcode_with_asterisks(): void
    {
        $reg = $this->createRegistration('SMK-202611-50GQ', 'paid');

        // Scanned 1D barcode with asterisks: *SMK-202611-50GQ*
        $this->actingAs($this->gatekeeper)
            ->get(route('management.gate.index', ['code' => '*SMK-202611-50GQ*']))
            ->assertRedirect(route('management.gate.ascent', $reg->id));
    }

    public function test_scanner_handles_scanned_full_ticket_url(): void
    {
        $reg = $this->createRegistration('SMK-202611-50GQ', 'paid');

        // Scanned QR code containing full public ticket URL
        $fullUrl = 'https://samsara.id/simaksi/ticket/SMK-202611-50GQ';

        $this->actingAs($this->gatekeeper)
            ->get(route('management.gate.index', ['code' => $fullUrl]))
            ->assertRedirect(route('management.gate.ascent', $reg->id));
    }

    public function test_scanner_handles_partial_suffix_or_lowercase(): void
    {
        $reg = $this->createRegistration('SMK-202611-50GQ', 'paid');

        // Operator typed or scanned lowercase suffix '50gq'
        $this->actingAs($this->gatekeeper)
            ->get(route('management.gate.index', ['code' => '50gq']))
            ->assertRedirect(route('management.gate.ascent', $reg->id));
    }

    public function test_scanner_informs_when_ticket_is_not_found(): void
    {
        $this->actingAs($this->gatekeeper)
            ->get(route('management.gate.index', ['code' => 'NON-EXISTENT']))
            ->assertRedirect(route('management.gate.index'))
            ->assertSessionHas('error');
    }

    public function test_ranger_checkpoint_resolves_scanned_ticket(): void
    {
        $reg = $this->createRegistration('SMK-202611-50GQ', 'checked_in');

        // Ranger scans ticket at Pos 1
        $response = $this->actingAs($this->ranger)
            ->get(route('management.ranger.index', ['pos' => 'POS_1', 'code' => 'SMK-202611-50GQ']));

        $response->assertOk();
        $response->assertSee('d Pebryana Putra', false);
    }
}
