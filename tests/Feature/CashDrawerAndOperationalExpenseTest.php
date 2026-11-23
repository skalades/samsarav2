<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Models\InventoryItem;
use App\Models\OjekDriver;
use App\Models\OjekTrip;
use App\Models\OperationalExpense;
use App\Models\User;
use App\Services\OjekService;
use App\Services\PosService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashDrawerAndOperationalExpenseTest extends TestCase
{
    use RefreshDatabase;

    protected User $cashier;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cashier = User::create([
            'name' => 'Kasir Operasional',
            'email' => 'kasir-ops@samsara.id',
            'password' => bcrypt('password'),
            'role' => 'kasir',
        ]);

        $this->admin = User::create([
            'name' => 'Admin Operasional',
            'email' => 'admin-ops@samsara.id',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
    }

    public function test_tariffs_settings_update_without_tent_rental(): void
    {
        // 1. AppSetting::getTariffs() does not have tent_rental_enabled or tent_fee_per_unit
        $tariffs = AppSetting::getTariffs();
        $this->assertArrayNotHasKey('tent_rental_enabled', $tariffs);
        $this->assertArrayNotHasKey('tent_fee_per_unit', $tariffs);

        // 2. Admin updates tariffs
        $response = $this->actingAs($this->admin)->post(route('management.settings.tariffs.update'), [
            'simaksi_fee_per_person' => 25000,
            'ojek_fee_per_trip' => 30000,
            'parking_motor_fee' => 10000,
            'parking_car_fee' => 25000,
            'trash_deposit_amount' => 50000,
        ]);
        $response->assertSessionHas('success');

        // 3. Confirm RNT-DOME4 does not exist in inventory catalog
        $tentItem = InventoryItem::where('sku', 'RNT-DOME4')->first();
        $this->assertNull($tentItem);
    }

    public function test_cashier_can_record_urgent_operational_expense(): void
    {
        $response = $this->actingAs($this->cashier)->post(route('management.pos.expenses.store'), [
            'category' => 'bbm_genset',
            'amount' => 75000,
            'recipient_name' => 'SPBU Pertamina Simpang',
            'payment_method' => 'cash',
            'notes' => 'Beli Pertalite 7.5 Liter untuk genset penerangan basecamp malam hari.',
        ]);

        $response->assertSessionHas('success');

        $expense = OperationalExpense::where('category', 'bbm_genset')->first();
        $this->assertNotNull($expense);
        $this->assertEquals(75000, (float) $expense->amount);
        $this->assertEquals('SPBU Pertamina Simpang', $expense->recipient_name);
        $this->assertEquals('active', $expense->status);
        $this->assertEquals($this->cashier->id, $expense->cashier_id);
    }

    public function test_operational_expense_can_be_voided_with_reason(): void
    {
        $expense = OperationalExpense::create([
            'expense_code' => 'EXP-20261123-VOID',
            'cashier_id' => $this->cashier->id,
            'category' => 'operasional_pos',
            'amount' => 25000,
            'recipient_name' => 'Fotokopi Mitra',
            'payment_method' => 'cash',
            'notes' => 'Fotokopi formulir SIMAKSI cadangan',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->cashier)->post(route('management.pos.expenses.void', $expense->id), [
            'void_reason' => 'Nota dibatalkan karena toko fotokopi tutup',
        ]);

        $response->assertSessionHas('success');
        $expense->refresh();
        $this->assertEquals('voided', $expense->status);
        $this->assertEquals($this->cashier->id, $expense->voided_by);
        $this->assertEquals('Nota dibatalkan karena toko fotokopi tutup', $expense->void_reason);
    }

    public function test_cash_drawer_reconciliation_automatically_deducts_ojek_payout_and_expenses(): void
    {
        // 1. Kas Masuk POS: Customer buys good for Rp 100.000 in cash
        $good = InventoryItem::create([
            'sku' => 'GDS-GAS-CAN',
            'name' => 'Gas Hicook 230g',
            'category' => 'konsumsi',
            'type' => 'good',
            'price' => 25000,
            'stock_total' => 10,
            'stock_available' => 10,
        ]);

        $posService = app(PosService::class);
        $posService->createOrder(
            cashierId: $this->cashier->id,
            customerName: 'Pendaki Kasir',
            customerPhone: '0811223344',
            items: [
                ['inventory_item_id' => $good->id, 'quantity' => 4], // 4 x 25.000 = Rp 100.000
            ],
            paymentMethod: 'cash',
            cashTendered: 100000
        );

        // 2. Driver Ojek Payout: Driver completes 2 trips (share Rp 25.000/trip = Rp 50.000 cash payout)
        $driver = OjekDriver::create([
            'registration_number' => 'OJK-99',
            'name' => 'Mang Ujang Ojek',
            'plate_number' => 'Z 9999 GA',
            'status' => 'standby',
            'is_active' => true,
        ]);

        $trip1 = OjekTrip::create([
            'trip_code' => 'VCR-OJK-TEST-01',
            'driver_id' => $driver->id,
            'passenger_name' => 'Penumpang 1',
            'fare_amount' => 30000,
            'driver_share' => 25000,
            'basecamp_share' => 5000,
            'payment_status' => 'paid',
            'trip_status' => 'completed',
            'settlement_status' => 'unsettled',
        ]);

        $trip2 = OjekTrip::create([
            'trip_code' => 'VCR-OJK-TEST-02',
            'driver_id' => $driver->id,
            'passenger_name' => 'Penumpang 2',
            'fare_amount' => 30000,
            'driver_share' => 25000,
            'basecamp_share' => 5000,
            'payment_status' => 'paid',
            'trip_status' => 'completed',
            'settlement_status' => 'unsettled',
        ]);

        $ojekService = app(OjekService::class);
        $settlement = $ojekService->settleDriverPayout(
            driverId: $driver->id,
            cashierId: $this->cashier->id,
            paymentMethod: 'cash',
            notes: 'Pencairan kasir posko'
        );
        $this->assertEquals(50000, (float) $settlement->total_amount);

        // 3. Operational Expense: Cashier spends Rp 20.000 cash for emergency trash bags
        OperationalExpense::create([
            'expense_code' => 'EXP-20261123-BAGS',
            'cashier_id' => $this->cashier->id,
            'category' => 'kebersihan_sampah',
            'amount' => 20000,
            'recipient_name' => 'Warung Plastik',
            'payment_method' => 'cash',
            'notes' => 'Beli 2 pack kantong sampah hitam darurat',
            'status' => 'active',
        ]);

        // 4. View POS History / Cash Drawer Summary:
        // Kas Masuk = Rp 100.000
        // Payout Ojek = Rp 50.000
        // Kas Keluar Operasional = Rp 20.000
        // Net Cash in Drawer = 100.000 - 50.000 - 20.000 = Rp 30.000!
        $response = $this->actingAs($this->cashier)->get(route('management.pos.history'));
        $response->assertStatus(200);

        $shiftSummary = $response->viewData('shiftSummary');
        $this->assertEquals(100000, $shiftSummary['total_cash_in']);
        $this->assertEquals(50000, $shiftSummary['ojek_payout_cash']);
        $this->assertEquals(20000, $shiftSummary['operational_expenses_cash']);
        $this->assertEquals(70000, $shiftSummary['total_cash_out']);
        $this->assertEquals(30000, $shiftSummary['net_cash_in_drawer']);
    }
}
