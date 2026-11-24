<?php

namespace Tests\Feature;

use App\Models\ClimberMember;
use App\Models\OperationalExpense;
use App\Models\SimaksiRegistration;
use App\Models\User;
use App\Services\ThermalReceiptService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThermalBluetoothPrintTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin Thermal',
            'email' => 'thermal_admin@samsara.id',
            'password' => bcrypt('secret'),
            'role' => 'superadmin',
            'status' => 'active',
        ]);
    }

    private function registration(): SimaksiRegistration
    {
        $reg = SimaksiRegistration::create([
            'registration_code' => 'SMK-'.date('Ym').'-777',
            'leader_name' => 'Rina Thermal',
            'leader_nik' => '3201123456780077',
            'leader_email' => 'rina@example.com',
            'leader_city' => 'Garut',
            'leader_phone' => '081200001111',
            'total_climbers' => 1,
            'hiking_date' => Carbon::today(),
            'return_date' => Carbon::tomorrow(),
            'simaksi_fee' => 25000,
            'total_amount' => 25000,
            'status' => 'checked_in',
            'payment_status' => 'paid',
            'checked_in_at' => Carbon::now(),
            'emergency_contact_name' => 'Ibu Rina',
            'emergency_contact_phone' => '081299990000',
            'emergency_relationship' => 'Orang Tua',
        ]);

        ClimberMember::create([
            'simaksi_registration_id' => $reg->id,
            'full_name' => 'Rina Thermal',
            'nik' => '3201123456780077',
            'gender' => 'P',
            'is_leader' => true,
        ]);

        return $reg->load('members');
    }

    public function test_climber_pass_document_contains_simaksi_code_rows_and_cut_without_qr(): void
    {
        $doc = app(ThermalReceiptService::class)->climberPass($this->registration());

        $ops = array_column($doc, 'op');
        $this->assertSame('logo', $ops[0]);
        $this->assertSame('cut', end($ops));
        $this->assertNotContains('qr', $ops);

        $texts = array_column(array_filter($doc, fn ($o) => $o['op'] === 'text'), 'text');
        $this->assertContains('SMK-'.date('Ym').'-777', $texts);

        $rights = array_column(array_filter($doc, fn ($o) => $o['op'] === 'row'), 'right');
        $this->assertContains('RINA THERMAL', $rights);
        $this->assertContains('1 ORANG', $rights);
    }

    public function test_print_pages_render_bluetooth_controls_without_qr(): void
    {
        $reg = $this->registration();
        $this->actingAs($this->admin);

        foreach ([
            route('management.gate.print_climber_pass', $reg->id),
            route('management.gate.print_parking_pass', $reg->id),
        ] as $url) {
            $res = $this->get($url);
            $res->assertOk();
            $res->assertSee('assets/js/escpos-bt.js', false);
            $res->assertSee('data-btp-print', false);
            $res->assertDontSee('"op":"qr"', false);
            $res->assertDontSee('data:image/png;base64,', false);
            $res->assertSee($reg->registration_code, false);
        }
    }

    public function test_expense_voucher_renders_bluetooth_document(): void
    {
        $expense = OperationalExpense::create([
            'expense_code' => 'EXP-20261124-BT',
            'cashier_id' => $this->admin->id,
            'category' => 'operasional_pos',
            'amount' => 15000,
            'recipient_name' => 'Warung Mitra',
            'payment_method' => 'cash',
            'notes' => 'Beli kertas thermal',
            'status' => 'active',
        ]);

        $res = $this->actingAs($this->admin)->get(route('management.pos.expenses.print', $expense->id));
        $res->assertOk();
        $res->assertSee('data-btp-print', false);
        $res->assertSee('Rp 15.000', false);
    }

    public function test_ojek_and_parking_vouchers_are_compact_without_header_or_footer(): void
    {
        $reg = $this->registration();
        $reg->update(['ojek_count' => 1, 'motorcycle_count' => 1, 'parking_fee' => 10000]);

        $trip = \App\Models\OjekTrip::create([
            'simaksi_registration_id' => $reg->id,
            'trip_code' => 'VCR-COMPACT-01',
            'passenger_name' => 'Rina Thermal',
            'route' => 'Basecamp → Pos 1',
            'fare_amount' => 30000,
            'driver_share' => 25000,
            'basecamp_share' => 5000,
            'trip_status' => 'voucher_issued',
            'settlement_status' => 'unsettled',
        ]);

        $service = app(ThermalReceiptService::class);
        foreach ([
            $service->ojekVouchers($reg, collect([$trip])),
            $service->tripVoucher($trip),
            $service->parkingPass($reg, 'Kasir'),
        ] as $doc) {
            $ops = array_column($doc, 'op');
            $this->assertNotContains('logo', $ops);
            $this->assertNotContains('qr', $ops);
            $texts = array_column(array_filter($doc, fn ($o) => $o['op'] === 'text'), 'text');
            $this->assertNotContains('VERIFIKASI PANGKALAN OJEK', $texts);
            $this->assertNotContains('VALIDASI POS PORTAL PARKIR', $texts);
        }

        $this->actingAs($this->admin);
        foreach ([
            route('management.gate.print_ojek_voucher', $reg->id),
            route('management.ojek.print_voucher', $trip->id),
            route('management.gate.print_parking_pass', $reg->id),
        ] as $url) {
            $res = $this->get($url);
            $res->assertOk();
            $res->assertSee('margin: 0;', false);
            $res->assertDontSee('VERIFIKASI PANGKALAN OJEK', false);
            $res->assertDontSee('VALIDASI POS PORTAL PARKIR', false);
            $res->assertDontSee('thermal-logo', false);
        }
    }
}
