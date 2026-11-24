<?php

namespace Tests\Feature;

use App\Models\ClimberMember;
use App\Models\DailyQuota;
use App\Models\InventoryItem;
use App\Models\OjekDriver;
use App\Models\OjekTrip;
use App\Models\PosOrder;
use App\Models\SimaksiRegistration;
use App\Models\User;
use App\Models\WhatsappMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResetSimulationDataCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_simulation_data_command_clears_transactions_and_preserves_masters(): void
    {
        // 1. Setup master user & settings
        $user = User::factory()->create(['email' => 'admin@samsara.id', 'role' => 'superadmin']);
        $item = InventoryItem::create([
            'sku' => 'TENT-01',
            'name' => 'Tenda Dome 4P',
            'category' => 'shelter',
            'type' => 'rental',
            'price' => 60000,
            'stock_total' => 10,
            'stock_available' => 3, // altered stock
            'is_active' => true,
        ]);
        $driver = OjekDriver::create([
            'registration_number' => 'OJK-001',
            'name' => 'Mang Ujang',
            'plate_number' => 'Z 1234 AB',
            'status' => 'on_trip',
        ]);
        $quota = DailyQuota::create([
            'quota_date' => now()->toDateString(),
            'max_quota' => 150,
            'booked_quota' => 25,
        ]);

        // 2. Setup transaction records
        $simaksi = SimaksiRegistration::create([
            'registration_code' => 'SMK-TEST-001',
            'leader_name' => 'Pendaki Uji',
            'leader_nik' => '3201010101010001',
            'leader_phone' => '081234567890',
            'leader_city' => 'Bandung',
            'emergency_contact_name' => 'Kontak Darurat',
            'emergency_contact_phone' => '081234567899',
            'emergency_relationship' => 'Orang Tua',
            'hiking_date' => now()->toDateString(),
            'return_date' => now()->toDateString(),
            'total_climbers' => 2,
            'status' => 'paid',
            'payment_status' => 'paid',
            'total_amount' => 50000,
        ]);

        ClimberMember::create([
            'simaksi_registration_id' => $simaksi->id,
            'full_name' => 'Anggota 1',
            'id_card_number' => '3201010101010002',
            'phone' => '081234567891',
        ]);

        PosOrder::create([
            'order_number' => 'POS-TEST-001',
            'customer_name' => 'Pelanggan Uji',
            'subtotal' => 50000,
            'total_amount' => 50000,
            'payment_method' => 'cash',
            'status' => 'paid',
            'cashier_id' => $user->id,
        ]);

        OjekTrip::create([
            'trip_code' => 'TRIP-TEST-001',
            'passenger_name' => 'Penumpang Uji',
            'simaksi_registration_id' => $simaksi->id,
            'driver_id' => $driver->id,
            'fare_amount' => 50000,
            'driver_share' => 40000,
            'basecamp_share' => 10000,
            'trip_status' => 'completed',
        ]);

        WhatsappMessage::create([
            'event' => 'payment_confirmed',
            'driver' => 'fonnte',
            'phone' => '081234567890',
            'message' => 'Pesan uji',
            'status' => 'sent',
        ]);

        // Assert data exists before reset
        $this->assertEquals(1, SimaksiRegistration::count());
        $this->assertEquals(1, ClimberMember::count());
        $this->assertEquals(1, PosOrder::count());
        $this->assertEquals(1, OjekTrip::count());
        $this->assertEquals(1, WhatsappMessage::count());

        // 3. Run the reset command with --force
        $this->artisan('samsara:reset-simulation', ['--force' => true])
            ->assertSuccessful();

        // 4. Assert transaction data is cleared
        $this->assertEquals(0, SimaksiRegistration::count());
        $this->assertEquals(0, ClimberMember::count());
        $this->assertEquals(0, PosOrder::count());
        $this->assertEquals(0, OjekTrip::count());
        $this->assertEquals(0, WhatsappMessage::count());

        // 5. Assert master data is preserved and reset properly
        $this->assertEquals(1, User::count());
        $this->assertEquals(1, InventoryItem::count());
        $this->assertEquals(1, OjekDriver::count());
        $this->assertEquals(1, DailyQuota::count());

        // Stock restored
        $item->refresh();
        $this->assertEquals(10, $item->stock_available);

        // Driver status reset
        $driver->refresh();
        $this->assertEquals('standby', $driver->status);

        // Quota reset
        $quota->refresh();
        $this->assertEquals(0, $quota->booked_quota);
    }
}
