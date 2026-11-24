<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Models\ClimberMember;
use App\Models\SimaksiRegistration;
use App\Models\User;
use App\Support\Barcode;
use App\Support\QrCode;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BasecampLocationAndQrScanValidationTest extends TestCase
{
    use RefreshDatabase;

    protected User $superadmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superadmin = User::create([
            'name' => 'Superadmin SAMSARA',
            'email' => 'superadmin@samsara.id',
            'password' => bcrypt('password'),
            'role' => 'superadmin',
            'status' => 'active',
        ]);
    }

    public function test_landing_page_displays_basecamp_google_maps_location(): void
    {
        // Set custom maps URL and coordinates
        AppSetting::set('basecamp_maps_url', 'https://maps.app.goo.gl/xGzRkQTTArmDxpQf9', 'Link Google Maps Basecamp');
        AppSetting::set('basecamp_coordinates', '-7.331855, 107.8096294', 'Koordinat GPS Basecamp');

        $response = $this->get(route('portal.home'));
        $response->assertOk();

        // Check for presence of Basecamp Google Maps section
        $response->assertSee('Lokasi resmi', false);
        $response->assertSee('https://maps.app.goo.gl/xGzRkQTTArmDxpQf9', false);
        $response->assertSee('-7.331855, 107.8096294', false);
        $response->assertSee('BUKA NAVIGASI GOOGLE MAPS', false);
        $response->assertSee('google.com/maps?q=', false);
    }

    public function test_superadmin_can_update_basecamp_maps_url_and_coordinates(): void
    {
        $this->actingAs($this->superadmin);

        $payload = [
            'simaksi_fee_per_person' => 25000,
            'ojek_fee_per_trip' => 30000,
            'parking_motor_fee' => 10000,
            'parking_car_fee' => 25000,
            'trash_deposit_amount' => 50000,
            'basecamp_location' => 'Samsara Basecamp, Cilawu, Garut',
            'basecamp_maps_url' => 'https://maps.app.goo.gl/xGzRkQTTArmDxpQf9',
            'basecamp_coordinates' => '-7.331855, 107.8096294',
        ];

        $response = $this->post(route('management.settings.tariffs.update'), $payload);
        $response->assertRedirect();

        $this->assertSame('https://maps.app.goo.gl/xGzRkQTTArmDxpQf9', AppSetting::get('basecamp_maps_url'));
        $this->assertSame('-7.331855, 107.8096294', AppSetting::get('basecamp_coordinates'));
    }

    public function test_qr_code_and_barcode_svg_generators_produce_valid_output(): void
    {
        $code = 'SMK-202611-XMCX';

        $qrSvg = QrCode::svg($code, 75);
        $this->assertStringContainsString('<svg', $qrSvg);
        $this->assertStringContainsString('width="75"', $qrSvg);

        $qrPng = QrCode::pngDataUri($code);
        $this->assertStringStartsWith('data:image/png;base64,', $qrPng);

        $barcodeSvg = Barcode::code128Svg($code, 40);
        $this->assertStringContainsString('<svg', $barcodeSvg);
        $this->assertStringContainsString('height="40"', $barcodeSvg);

        $barcodePng = Barcode::code128PngDataUri($code);
        $this->assertStringStartsWith('data:image/png;base64,', $barcodePng);
    }

    public function test_simaksi_pdf_ticket_renders_qr_scan_validation_gerbang(): void
    {
        $reg = SimaksiRegistration::create([
            'registration_code' => 'SMK-202611-XMCX',
            'leader_name' => 'Pendaki Tangguh',
            'leader_nik' => '3201123456780001',
            'leader_email' => 'pendaki@example.com',
            'leader_phone' => '081234567890',
            'total_climbers' => 1,
            'hiking_date' => Carbon::today(),
            'return_date' => Carbon::tomorrow(),
            'simaksi_fee' => 25000,
            'total_amount' => 25000,
            'status' => 'paid',
            'payment_status' => 'paid',
            'emergency_contact_name' => 'Keluarga',
            'emergency_contact_phone' => '081299990000',
            'emergency_relationship' => 'Orang Tua',
        ]);

        ClimberMember::create([
            'simaksi_registration_id' => $reg->id,
            'full_name' => 'Pendaki Tangguh',
            'nik' => '3201123456780001',
            'gender' => 'L',
            'is_leader' => true,
        ]);

        // Download PDF
        $response = $this->get($reg->publicPdfUrl());
        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('Content-Type'));

        $pdfContent = $response->getContent();
        // Assert that DomPDF embedded the QR Code image stream in the PDF
        $this->assertStringContainsString('/Subtype /Image', $pdfContent);
    }
}
