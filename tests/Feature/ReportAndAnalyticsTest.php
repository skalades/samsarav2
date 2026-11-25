<?php

namespace Tests\Feature;

use App\Models\InventoryItem;
use App\Models\OjekDriver;
use App\Models\OjekTrip;
use App\Models\OperationalExpense;
use App\Models\SimaksiRegistration;
use App\Models\User;
use App\Services\OjekService;
use App\Services\PosService;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportAndAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $cashier;

    protected User $auditor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin Laporan',
            'email' => 'admin-laporan@samsara.id',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->cashier = User::create([
            'name' => 'Kasir Laporan',
            'email' => 'kasir-laporan@samsara.id',
            'password' => bcrypt('password'),
            'role' => 'kasir',
        ]);

        $this->auditor = User::create([
            'name' => 'Auditor Laporan',
            'email' => 'auditor-laporan@samsara.id',
            'password' => bcrypt('password'),
            'role' => 'auditor',
        ]);
    }

    public function test_unauthenticated_guest_cannot_access_reports(): void
    {
        $response = $this->get(route('management.reports.index'));
        $response->assertRedirect(route('management.login'));
    }

    public function test_authorized_staff_can_view_reports_and_analytics(): void
    {
        // Admin, Cashier, Auditor can view
        $response = $this->actingAs($this->admin)->get(route('management.reports.index'));
        $response->assertStatus(200);
        $response->assertSee('LAPORAN KEUANGAN');

        $response = $this->actingAs($this->cashier)->get(route('management.reports.index'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->auditor)->get(route('management.reports.index'));
        $response->assertStatus(200);
    }

    public function test_financial_summary_and_net_profit_calculation(): void
    {
        $today = Carbon::today();

        // 1. Inflow: Completed POS Order (Rp 120.000 cash)
        $item = InventoryItem::create([
            'sku' => 'GDS-SNACK-01',
            'name' => 'Roti Bakar Pos 00',
            'category' => 'konsumsi',
            'type' => 'good',
            'price' => 15000,
            'stock_total' => 50,
            'stock_available' => 50,
        ]);

        $posService = app(PosService::class);
        $posService->createOrder(
            cashierId: $this->cashier->id,
            customerName: 'Pendaki Logistik',
            customerPhone: null,
            items: [
                ['inventory_item_id' => $item->id, 'quantity' => 8], // 8 x 15000 = Rp 120.000
            ],
            paymentMethod: 'cash',
            cashTendered: 150000
        );

        // 2. Inflow: Direct SIMAKSI Registration (Rp 100.000 transfer)
        SimaksiRegistration::create([
            'registration_code' => 'SMK-202611-REPORT',
            'leader_name' => 'Ahmad Rombongan',
            'leader_nik' => '3205011234560001',
            'leader_phone' => '081234567890',
            'hiking_date' => $today->toDateString(),
            'total_climbers' => 4,
            'status' => 'paid',
            'simaksi_fee' => 100000,
            'total_amount' => 100000,
            'payment_status' => 'paid',
            'payment_method' => 'transfer',
            'paid_at' => $today,
            'emergency_contact_name' => 'Kontak',
            'emergency_contact_phone' => '081234567891',
        ]);

        // 3. Outflow: Ojek Driver Settlement (Rp 50.000 cash)
        $driver = OjekDriver::create([
            'registration_number' => 'OJK-REPORT-01',
            'name' => 'Driver Ojek Report',
            'plate_number' => 'Z 1111 GA',
            'status' => 'standby',
            'is_active' => true,
        ]);

        OjekTrip::create([
            'trip_code' => 'VCR-OJK-REP-01',
            'driver_id' => $driver->id,
            'passenger_name' => 'Penumpang Ojek',
            'fare_amount' => 30000,
            'driver_share' => 25000,
            'basecamp_share' => 5000,
            'payment_status' => 'paid',
            'trip_status' => 'completed',
            'settlement_status' => 'unsettled',
        ]);

        OjekTrip::create([
            'trip_code' => 'VCR-OJK-REP-02',
            'driver_id' => $driver->id,
            'passenger_name' => 'Penumpang Ojek 2',
            'fare_amount' => 30000,
            'driver_share' => 25000,
            'basecamp_share' => 5000,
            'payment_status' => 'paid',
            'trip_status' => 'completed',
            'settlement_status' => 'unsettled',
        ]);

        $ojekService = app(OjekService::class);
        $ojekService->settleDriverPayout($driver->id, $this->cashier->id, 'cash', 'Pencairan honor');

        // 4. Outflow: Operational Expense (Rp 30.000 cash for BBM Genset)
        OperationalExpense::create([
            'expense_code' => 'EXP-202611-REP01',
            'cashier_id' => $this->cashier->id,
            'category' => 'bbm_genset',
            'amount' => 30000,
            'recipient_name' => 'SPBU Terdekat',
            'payment_method' => 'cash',
            'notes' => 'Bensin genset 3 liter',
            'status' => 'active',
        ]);

        // Execute ReportService
        $reportService = app(ReportService::class);
        $summary = $reportService->getFinancialSummary($today, $today);

        // Verification:
        // Total Inflow = 120.000 (POS) + 100.000 (SIMAKSI) = Rp 220.000
        $this->assertEquals(220000, $summary['total_inflow']);

        // Total Outflow = 50.000 (Ojek) + 30.000 (Expense) = Rp 80.000
        $this->assertEquals(80000, $summary['total_outflow']);

        // Net Profit = 220.000 - 80.000 = Rp 140.000
        $this->assertEquals(140000, $summary['net_profit']);

        // Net Cash in Drawer = 120.000 (Cash In) - 80.000 (Cash Out) = Rp 40.000
        $this->assertEquals(40000, $summary['net_cash_in_drawer']);

        // Test Unified Ledger includes all 4 records
        $ledger = $reportService->getUnifiedCashLedger($today, $today);
        $this->assertCount(4, $ledger);
    }

    public function test_reports_filter_by_preset_periods_and_custom_dates(): void
    {
        $today = Carbon::today();

        // Preset 7days
        $response = $this->actingAs($this->admin)->get(route('management.reports.index', ['period' => '7days']));
        $response->assertStatus(200);
        $response->assertViewHas('period', '7days');

        // Preset this_month
        $response = $this->actingAs($this->admin)->get(route('management.reports.index', ['period' => 'this_month']));
        $response->assertStatus(200);
        $response->assertViewHas('period', 'this_month');

        // Custom range
        $response = $this->actingAs($this->admin)->get(route('management.reports.index', [
            'period' => 'custom',
            'start_date' => $today->copy()->subDays(10)->toDateString(),
            'end_date' => $today->toDateString(),
        ]));
        $response->assertStatus(200);
        $response->assertViewHas('period', 'custom');
    }

    public function test_printable_report_renders_successfully(): void
    {
        $response = $this->actingAs($this->admin)->get(route('management.reports.print', ['period' => '30days']));
        $response->assertStatus(200);
        $response->assertSee('REKAPITULASI KEUANGAN');
        $response->assertSee('Kasir & Bendahara Posko', false);
    }

    public function test_parking_revenue_is_tracked_and_displayed_separately(): void
    {
        $today = Carbon::today();

        SimaksiRegistration::create([
            'registration_code' => 'SMK-202611-PRK01',
            'leader_name' => 'Pendaki Bermotor',
            'leader_nik' => '3205011234560002',
            'leader_phone' => '081234567899',
            'hiking_date' => $today->toDateString(),
            'total_climbers' => 2,
            'status' => 'paid',
            'simaksi_fee' => 50000,
            'has_vehicle' => true,
            'motorcycle_count' => 1,
            'car_count' => 1,
            'parking_fee' => 35000,
            'total_amount' => 85000,
            'payment_status' => 'paid',
            'payment_method' => 'cash_pos',
            'paid_at' => $today,
            'emergency_contact_name' => 'Kontak',
            'emergency_contact_phone' => '081234567891',
        ]);

        $reportService = app(ReportService::class);
        $summary = $reportService->getFinancialSummary($today, $today);

        $this->assertEquals(85000, $summary['total_inflow']);
        $this->assertEquals(35000, $summary['revenue_stream_breakdown']['vehicle_parking']);
        $this->assertEquals(10000, $summary['revenue_stream_breakdown']['parking_motor']);
        $this->assertEquals(25000, $summary['revenue_stream_breakdown']['parking_car']);
        $this->assertEquals(50000, $summary['revenue_stream_breakdown']['simaksi_permit']);
        $this->assertEquals(1, $summary['total_motorcycles']);
        $this->assertEquals(1, $summary['total_cars']);

        // View verification
        $response = $this->actingAs($this->admin)->get(route('management.reports.index', ['period' => 'today']));
        $response->assertStatus(200);
        $response->assertSee('Parkir Motor');
        $response->assertSee('Rp 10.000', false);
        $response->assertSee('Parkir Mobil');
        $response->assertSee('Rp 25.000', false);

        // Print verification
        $printResponse = $this->actingAs($this->admin)->get(route('management.reports.print', ['period' => 'today']));
        $printResponse->assertStatus(200);
        $printResponse->assertSee('Titipan Parkir Sepeda Motor (Roda 2)');
        $printResponse->assertSee('Rp 10.000', false);
        $printResponse->assertSee('Titipan Parkir Mobil Pribadi (Roda 4)');
        $printResponse->assertSee('Rp 25.000', false);
    }

    public function test_qris_and_transfer_payments_are_not_classified_as_physical_cash(): void
    {
        $today = Carbon::today();

        // 1. SIMAKSI via QRIS (Rp 150.000)
        $regQris = SimaksiRegistration::create([
            'registration_code' => 'SMK-202611-QRIS01',
            'leader_name' => 'Pendaki QRIS',
            'leader_nik' => '3205011234560003',
            'leader_phone' => '081234567895',
            'hiking_date' => $today->toDateString(),
            'total_climbers' => 3,
            'status' => 'pending',
            'simaksi_fee' => 150000,
            'total_amount' => 150000,
            'payment_status' => 'unpaid',
            'payment_method' => 'qris',
            'payment_proof_path' => 'payments/proof_qris_test.jpg',
            'emergency_contact_name' => 'Kontak QRIS',
            'emergency_contact_phone' => '081234567891',
        ]);

        // Staff marks paid via controller
        $this->actingAs($this->admin)->post(route('management.simaksi.paid', $regQris->id), [
            'payment_method' => 'qris',
        ]);

        $regQris->refresh();
        $this->assertEquals('paid', $regQris->status);
        $this->assertEquals('qris', $regQris->payment_method);

        // 2. SIMAKSI via Transfer (Rp 200.000)
        $regTransfer = SimaksiRegistration::create([
            'registration_code' => 'SMK-202611-TF01',
            'leader_name' => 'Pendaki Transfer',
            'leader_nik' => '3205011234560004',
            'leader_phone' => '081234567896',
            'hiking_date' => $today->toDateString(),
            'total_climbers' => 4,
            'status' => 'pending',
            'simaksi_fee' => 200000,
            'total_amount' => 200000,
            'payment_status' => 'unpaid',
            'payment_method' => 'transfer',
            'payment_proof_path' => 'payments/proof_transfer_test.jpg',
            'emergency_contact_name' => 'Kontak Transfer',
            'emergency_contact_phone' => '081234567891',
        ]);

        // Staff marks paid without specifying payment_method (defaults to registration payment_method 'transfer')
        $this->actingAs($this->admin)->post(route('management.simaksi.paid', $regTransfer->id));

        $regTransfer->refresh();
        $this->assertEquals('paid', $regTransfer->status);
        $this->assertEquals('transfer', $regTransfer->payment_method);

        // Verify ReportService separation
        $reportService = app(ReportService::class);
        $summary = $reportService->getFinancialSummary($today, $today);

        $this->assertEquals(350000, $summary['total_inflow']);
        $this->assertEquals(0, $summary['inflow_channels']['cash']);
        $this->assertEquals(150000, $summary['inflow_channels']['qris']);
        $this->assertEquals(200000, $summary['inflow_channels']['transfer']);
        $this->assertEquals(0, $summary['net_cash_in_drawer']);

        // View verification: QRIS & Transfer must show correct values, not 0
        $response = $this->actingAs($this->admin)->get(route('management.reports.index', ['period' => 'today']));
        $response->assertStatus(200);
        $response->assertSee('Rp 150.000', false);
        $response->assertSee('Rp 200.000', false);
    }

    public function test_pos_parking_items_are_aggregated_without_relation_error(): void
    {
        $motor = InventoryItem::create([
            'sku' => 'PRK-MOTOR',
            'name' => 'Parkir Motor Harian',
            'category' => 'parkir',
            'type' => 'service',
            'price' => 10000,
            'stock_total' => 999,
            'stock_available' => 999,
        ]);

        $car = InventoryItem::create([
            'sku' => 'PRK-MOBIL',
            'name' => 'Parkir Mobil Harian',
            'category' => 'parkir',
            'type' => 'service',
            'price' => 25000,
            'stock_total' => 999,
            'stock_available' => 999,
        ]);

        $posService = app(PosService::class);
        $posService->createOrder(
            cashierId: $this->cashier->id,
            customerName: 'Tamu Parkir',
            customerPhone: null,
            items: [
                ['inventory_item_id' => $motor->id, 'quantity' => 2],
                ['inventory_item_id' => $car->id, 'quantity' => 1],
            ],
            paymentMethod: 'cash',
            cashTendered: 50000
        );

        $today = Carbon::today();
        $summary = app(ReportService::class)->getFinancialSummary($today, $today);

        $this->assertEquals(45000, $summary['total_inflow']);
        $this->assertEquals(20000, $summary['revenue_stream_breakdown']['parking_motor']);
        $this->assertEquals(25000, $summary['revenue_stream_breakdown']['parking_car']);
        $this->assertEquals(2, $summary['total_motorcycles']);
        $this->assertEquals(1, $summary['total_cars']);
        $this->assertEquals(0, $summary['revenue_stream_breakdown']['pos_retail_and_rentals']);

        $this->actingAs($this->admin)
            ->get(route('management.reports.index', ['period' => 'today']))
            ->assertStatus(200)
            ->assertSee('Rp 20.000', false)
            ->assertSee('Rp 25.000', false);

        $this->actingAs($this->admin)
            ->get(route('management.reports.print', ['period' => 'today']))
            ->assertStatus(200);
    }

    public function test_malformed_custom_date_falls_back_gracefully(): void
    {
        $this->actingAs($this->admin)
            ->get(route('management.reports.index', [
                'period' => 'custom',
                'start_date' => 'not-a-date',
                'end_date' => 'also-invalid',
            ]))
            ->assertStatus(200);
    }
}
