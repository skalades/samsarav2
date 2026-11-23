<?php

namespace Tests\Feature;

use App\Models\DailyQuota;
use App\Models\InventoryItem;
use App\Models\OjekTrip;
use App\Models\RentalRecord;
use App\Models\SimaksiRegistration;
use App\Models\User;
use App\Services\CheckInService;
use App\Services\OjekService;
use App\Services\PosService;
use App\Services\SimaksiService;
use App\Support\CodeGenerator;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConcurrencyAndRaceConditionTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $cashier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin Concurrency',
            'email' => 'admin-race@samsara.id',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->cashier = User::create([
            'name' => 'Kasir Concurrency',
            'email' => 'cashier-race@samsara.id',
            'password' => bcrypt('password'),
            'role' => 'cashier',
        ]);
    }

    /**
     * RC-02: CodeGenerator produces zero collision on high concurrency.
     */
    public function test_code_generator_produces_unique_codes_without_collisions(): void
    {
        $simaksiCodes = [];
        $posCodes = [];
        $settlementCodes = [];

        for ($i = 0; $i < 50; $i++) {
            $simaksiCodes[] = CodeGenerator::simaksiCode();
            $posCodes[] = CodeGenerator::posOrderNumber();
            $settlementCodes[] = CodeGenerator::ojekSettlementCode();
        }

        $this->assertCount(50, array_unique($simaksiCodes), 'All SIMAKSI codes generated must be strictly unique.');
        $this->assertCount(50, array_unique($posCodes), 'All POS order numbers generated must be strictly unique.');
        $this->assertCount(50, array_unique($settlementCodes), 'All settlement codes generated must be strictly unique.');
    }

    /**
     * RC-01: Quota booking on non-existent row creates safely without unique crash.
     */
    public function test_quota_booking_on_uninitialized_date_handles_race_condition_cleanly(): void
    {
        $futureDate = Carbon::now()->addDays(10)->toDateString();

        // Ensure no DailyQuota row exists initially
        $this->assertNull(DailyQuota::whereDate('quota_date', $futureDate)->first());

        $payload = [
            'hiking_date' => $futureDate,
            'leader_name' => 'Pendaki Konkuren',
            'leader_nik' => '3205019999990001',
            'leader_phone' => '081299998888',
            'emergency_contact_name' => 'Kontak Darurat',
            'emergency_contact_phone' => '081299997777',
            'payment_method' => 'cash_pos',
            'members' => [
                ['full_name' => 'Pendaki 1', 'nik' => '3205019999990001', 'gender' => 'L'],
                ['full_name' => 'Pendaki 2', 'nik' => '3205019999990002', 'gender' => 'P'],
            ],
        ];

        $response = $this->postJson(route('portal.simaksi.register'), $payload);
        $response->assertStatus(200);

        $quota = DailyQuota::whereDate('quota_date', $futureDate)->first();
        $this->assertNotNull($quota);
        $this->assertEquals(2, $quota->booked_quota);
    }

    /**
     * RC-03: POS Inventory stock check and decrement prevents overselling & negative stock.
     */
    public function test_pos_stock_reservation_under_lock_prevents_overselling(): void
    {
        $tent = InventoryItem::create([
            'sku' => 'RNT-DOME-TEST',
            'name' => 'Tenda Dome Race Test',
            'category' => 'tenda',
            'type' => 'rental',
            'price' => 65000,
            'stock_total' => 2,
            'stock_available' => 2,
        ]);

        $posService = app(PosService::class);

        // Transaction 1: Buys 2 tents (stock 2 -> 0)
        $order1 = $posService->createOrder(
            cashierId: $this->cashier->id,
            customerName: 'Customer A',
            customerPhone: '0811111111',
            items: [
                ['inventory_item_id' => $tent->id, 'quantity' => 2],
            ],
            paymentMethod: 'cash',
            cashTendered: 200000
        );

        $this->assertNotNull($order1);
        $tent->refresh();
        $this->assertEquals(0, $tent->stock_available);

        // Transaction 2: Concurrently attempts to buy 1 more tent
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Stok barang Tenda Dome Race Test tidak mencukupi');

        $posService->createOrder(
            cashierId: $this->cashier->id,
            customerName: 'Customer B',
            customerPhone: '0822222222',
            items: [
                ['inventory_item_id' => $tent->id, 'quantity' => 1],
            ],
            paymentMethod: 'cash',
            cashTendered: 100000
        );

        // Stock must remain 0 and NOT negative
        $tent->refresh();
        $this->assertEquals(0, $tent->stock_available);
    }

    /**
     * RC-07: Concurrent POS VOID is guarded and does not restore stock twice.
     */
    public function test_pos_void_is_idempotent_and_prevents_double_stock_restoration(): void
    {
        $item = InventoryItem::create([
            'sku' => 'GDS-HEADLAMP',
            'name' => 'Headlamp Waterproof',
            'category' => 'alat_gunung',
            'type' => 'good',
            'price' => 50000,
            'stock_total' => 10,
            'stock_available' => 10,
        ]);

        $posService = app(PosService::class);

        $order = $posService->createOrder(
            cashierId: $this->cashier->id,
            customerName: 'Budi Test',
            customerPhone: null,
            items: [
                ['inventory_item_id' => $item->id, 'quantity' => 3],
            ]
        );

        $item->refresh();
        $this->assertEquals(7, $item->stock_available);

        // First VOID: restores 3 -> stock becomes 10
        $posService->voidOrder($order, $this->cashier->id, 'Pelanggan salah pilih barang');
        $item->refresh();
        $this->assertEquals(10, $item->stock_available);

        // Second VOID: must throw exception and not restore stock to 13
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('sudah pernah dibatalkan (VOID)');

        $posService->voidOrder($order, $this->cashier->id, 'Void ulang tidak sengaja');
    }

    /**
     * RC-04: Rental Return Guard prevents ghost stock inflation on double-click.
     */
    public function test_rental_return_guard_prevents_duplicate_stock_inflation(): void
    {
        $tent = InventoryItem::create([
            'sku' => 'RNT-DOME-RET',
            'name' => 'Tenda Dome 4P Return',
            'category' => 'tenda',
            'type' => 'rental',
            'price' => 60000,
            'stock_total' => 5,
            'stock_available' => 3, // 2 rented out
        ]);

        $rental = RentalRecord::create([
            'inventory_item_id' => $tent->id,
            'customer_name' => 'Ketua Rombongan C',
            'item_name' => $tent->name,
            'quantity' => 2,
            'condition_out' => 'Bagus',
            'status' => 'rented',
        ]);

        // First return request
        $response1 = $this->actingAs($this->admin)->post(route('management.inventory.return', $rental->id), [
            'status' => 'returned_good',
            'condition_in' => 'Lengkap dan bersih',
        ]);
        $response1->assertSessionHas('success');

        $tent->refresh();
        $rental->refresh();
        $this->assertEquals('returned_good', $rental->status);
        $this->assertEquals(5, $tent->stock_available, 'Stock should be restored from 3 to 5.');

        // Second return request (simulate double click / race condition)
        $response2 = $this->actingAs($this->admin)->post(route('management.inventory.return', $rental->id), [
            'status' => 'returned_good',
            'condition_in' => 'Lengkap dan bersih',
        ]);
        $response2->assertSessionHas('error');

        $tent->refresh();
        $this->assertEquals(5, $tent->stock_available, 'Stock must remain 5 and NOT inflate to 7!');
    }

    /**
     * RC-05: Simaksi rejection concurrency does not leak quota repeatedly.
     */
    public function test_simaksi_rejection_idempotency_prevents_quota_leak(): void
    {
        $hikingDate = Carbon::now()->addDays(5)->toDateString();
        $quota = DailyQuota::create([
            'quota_date' => $hikingDate,
            'max_quota' => 100,
            'booked_quota' => 4,
        ]);

        $reg = SimaksiRegistration::create([
            'registration_code' => 'SMK-TEST-LEAK-GUARD',
            'leader_name' => 'Ketua Ditolak',
            'leader_nik' => '3205010000000001',
            'leader_phone' => '081200000001',
            'emergency_contact_name' => 'Kontak',
            'emergency_contact_phone' => '081200000002',
            'hiking_date' => $hikingDate,
            'total_climbers' => 4,
            'status' => 'pending',
            'simaksi_fee' => 100000,
            'total_amount' => 100000,
        ]);

        $simaksiService = app(SimaksiService::class);

        // 1st rejection: restores 4 climbers -> booked_quota = 0
        $simaksiService->reject($reg, $this->admin->id, 'KTP buram');
        $quota->refresh();
        $this->assertEquals(0, $quota->booked_quota);

        // 2nd rejection: should NOT decrement booked_quota below 0
        $simaksiService->reject($reg, $this->admin->id, 'KTP buram lagi');
        $quota->refresh();
        $this->assertEquals(0, $quota->booked_quota, 'Booked quota must remain 0 and not leak or underflow.');
    }

    /**
     * RC-06: Gate check-in concurrency prevents double check-in and duplicate vouchers.
     */
    public function test_gate_checkin_and_ojek_voucher_idempotency_under_concurrency(): void
    {
        $hikingDate = Carbon::today()->toDateString();
        DailyQuota::create([
            'quota_date' => $hikingDate,
            'max_quota' => 150,
            'booked_quota' => 2,
        ]);

        $reg = SimaksiRegistration::create([
            'registration_code' => 'SMK-TEST-CHECKIN-RACE',
            'leader_name' => 'Danang Saputra',
            'leader_nik' => '3205018888880001',
            'leader_phone' => '081388889999',
            'emergency_contact_name' => 'Istri Danang',
            'emergency_contact_phone' => '081388889998',
            'hiking_date' => $hikingDate,
            'total_climbers' => 2,
            'ojek_count' => 2,
            'ojek_fee' => 60000,
            'status' => 'paid',
            'payment_status' => 'paid',
        ]);

        $checkInService = app(CheckInService::class);
        $ojekService = app(OjekService::class);

        // 1st Check-In
        $checkInService->checkIn(
            registration: $reg,
            officerId: $this->admin->id,
            trashBagCode: 'TB-9988',
            checkedMemberIds: []
        );

        $reg->refresh();
        $this->assertEquals('checked_in', $reg->status);

        // Verify exactly 2 ojek trip vouchers created
        $trips = OjekTrip::where('simaksi_registration_id', $reg->id)->get();
        $this->assertCount(2, $trips);

        // Calling ensureTripsForRegistration again must return existing trips without creating duplicates
        $moreTrips = $ojekService->ensureTripsForRegistration($reg, $this->admin->id);
        $this->assertCount(2, $moreTrips);
        $this->assertEquals(2, OjekTrip::where('simaksi_registration_id', $reg->id)->count());

        // 2nd Check-In on already checked-in registration must throw exception
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('sudah berstatus Check-In di jalur');

        $checkInService->checkIn(
            registration: $reg,
            officerId: $this->admin->id,
            trashBagCode: 'TB-9989',
            checkedMemberIds: []
        );
    }
}
