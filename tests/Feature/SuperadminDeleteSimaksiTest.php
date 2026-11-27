<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\ClimberMember;
use App\Models\DailyQuota;
use App\Models\InventoryItem;
use App\Models\OjekDriver;
use App\Models\OjekSettlement;
use App\Models\OjekTrip;
use App\Models\PosOrder;
use App\Models\PosOrderItem;
use App\Models\RentalRecord;
use App\Models\SimaksiCheckpoint;
use App\Models\SimaksiInspectionItem;
use App\Models\SimaksiRegistration;
use App\Models\User;
use App\Models\WhatsappMessage;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SuperadminDeleteSimaksiTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(string $role): User
    {
        return User::create([
            'name' => 'Petugas '.$role,
            'email' => $role.'_del@samsara.test',
            'password' => bcrypt('password'),
            'role' => $role,
        ]);
    }

    public function test_non_superadmin_cannot_delete_simaksi_or_see_delete_button(): void
    {
        $reg = SimaksiRegistration::create([
            'registration_code' => 'SMK-DEL-403',
            'leader_name' => 'Pendaki Uji',
            'leader_nik' => '3205011111111111',
            'leader_phone' => '081211111111',
            'emergency_contact_name' => 'Keluarga',
            'emergency_contact_phone' => '081222222222',
            'hiking_date' => Carbon::tomorrow(),
            'total_climbers' => 2,
            'status' => 'pending',
            'simaksi_fee' => 50000,
            'total_amount' => 50000,
        ]);

        $usersByRole = [];
        foreach (['kasir', 'gatekeeper', 'ranger'] as $role) {
            $user = $this->createUser($role);
            $usersByRole[$role] = $user;
            $this->actingAs($user)
                ->delete(route('management.simaksi.destroy', $reg->id))
                ->assertRedirect(route($user->homeRoute()));

            $this->actingAs($user)
                ->deleteJson(route('management.simaksi.destroy', $reg->id))
                ->assertForbidden();
        }

        $this->actingAs($usersByRole['kasir'])
            ->get(route('management.simaksi.show', $reg->id))
            ->assertOk()
            ->assertDontSee('deleteSimaksiModal', false);

        $this->assertDatabaseHas('simaksi_registrations', ['id' => $reg->id]);
    }

    public function test_superadmin_deletes_simaksi_and_all_related_data_restoring_quota_stock_and_driver(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        $superadmin = $this->createUser('superadmin');
        $hikingDate = Carbon::tomorrow()->toDateString();

        $quota = DailyQuota::create([
            'quota_date' => $hikingDate,
            'max_quota' => 150,
            'booked_quota' => 10,
        ]);

        Storage::disk('local')->put('ktp/ktp_test.jpg', 'ktp-bytes');
        Storage::disk('public')->put('proofs/proof_test.jpg', 'proof-bytes');
        Storage::disk('public')->put('group_photos/group_test.jpg', 'group-bytes');
        Storage::disk('public')->put('gate_photos/gate_test.jpg', 'gate-bytes');

        $reg = SimaksiRegistration::create([
            'registration_code' => 'SMK-DEL-001',
            'leader_name' => 'Budi Santoso',
            'leader_nik' => '3205011234567890',
            'leader_phone' => '081234567890',
            'emergency_contact_name' => 'Siti',
            'emergency_contact_phone' => '081298765432',
            'hiking_date' => $hikingDate,
            'total_climbers' => 4,
            'status' => 'checked_in',
            'payment_status' => 'paid',
            'ojek_count' => 1,
            'ojek_fee' => 30000,
            'simaksi_fee' => 100000,
            'total_amount' => 130000,
            'leader_ktp_path' => 'ktp/ktp_test.jpg',
            'payment_proof_path' => 'proofs/proof_test.jpg',
            'group_photo_path' => 'group_photos/group_test.jpg',
            'gate_photo_path' => 'gate_photos/gate_test.jpg',
        ]);

        ClimberMember::create([
            'simaksi_registration_id' => $reg->id,
            'full_name' => 'Budi Santoso',
            'nik' => '3205011234567890',
            'gender' => 'L',
            'is_leader' => true,
            'is_checked_in' => true,
        ]);

        $driver = OjekDriver::create([
            'registration_number' => 'OJK-99',
            'name' => 'Mang Uji',
            'plate_number' => 'Z 9999 XX',
            'status' => 'on_trip',
            'is_active' => true,
        ]);

        $settlement = OjekSettlement::create([
            'settlement_code' => 'STL-TEST-1',
            'driver_id' => $driver->id,
            'cashier_id' => $superadmin->id,
            'total_trips' => 1,
            'total_amount' => 25000,
            'payment_method' => 'cash',
            'settled_at' => now(),
        ]);

        $trip = OjekTrip::create([
            'trip_code' => 'VCR-OJK-DEL-1',
            'simaksi_registration_id' => $reg->id,
            'driver_id' => $driver->id,
            'passenger_name' => 'Budi Santoso',
            'fare_amount' => 30000,
            'driver_share' => 25000,
            'basecamp_share' => 5000,
            'payment_status' => 'paid',
            'trip_status' => 'on_trip',
            'settlement_status' => 'settled',
            'ojek_settlement_id' => $settlement->id,
        ]);

        $tentItem = InventoryItem::create([
            'sku' => 'RENT-DEL-1',
            'name' => 'Tenda Dome Uji',
            'category' => 'alat_gunung',
            'type' => 'rental',
            'price' => 50000,
            'stock_total' => 10,
            'stock_available' => 8,
            'unit' => 'unit',
        ]);

        $gasItem = InventoryItem::create([
            'sku' => 'GOODS-DEL-1',
            'name' => 'Gas Kaleng Uji',
            'category' => 'konsumsi',
            'type' => 'goods',
            'price' => 20000,
            'stock_total' => 20,
            'stock_available' => 17,
            'unit' => 'kaleng',
        ]);

        $posOrder = PosOrder::create([
            'order_number' => 'POS-DEL-001',
            'cashier_id' => $superadmin->id,
            'simaksi_registration_id' => $reg->id,
            'customer_name' => 'Budi Santoso',
            'subtotal' => 160000,
            'total_amount' => 160000,
            'payment_method' => 'cash',
            'status' => 'completed',
        ]);

        PosOrderItem::create([
            'pos_order_id' => $posOrder->id,
            'inventory_item_id' => $tentItem->id,
            'item_name' => $tentItem->name,
            'category' => $tentItem->category,
            'unit_price' => 50000,
            'quantity' => 2,
            'subtotal' => 100000,
        ]);

        PosOrderItem::create([
            'pos_order_id' => $posOrder->id,
            'inventory_item_id' => $gasItem->id,
            'item_name' => $gasItem->name,
            'category' => $gasItem->category,
            'unit_price' => 20000,
            'quantity' => 3,
            'subtotal' => 60000,
        ]);

        $rental = RentalRecord::create([
            'inventory_item_id' => $tentItem->id,
            'simaksi_registration_id' => $reg->id,
            'pos_order_id' => $posOrder->id,
            'customer_name' => 'Budi Santoso',
            'item_name' => $tentItem->name,
            'quantity' => 2,
            'status' => 'rented',
        ]);

        $wa = WhatsappMessage::create([
            'simaksi_registration_id' => $reg->id,
            'event' => 'payment_confirmed',
            'phone' => '081234567890',
            'message' => 'Test WA',
            'driver' => 'log',
            'status' => 'sent',
        ]);

        SimaksiCheckpoint::create([
            'simaksi_registration_id' => $reg->id,
            'pos_code' => 'POS_1',
            'direction' => 'ascending',
            'ranger_id' => $superadmin->id,
            'scanned_at' => now(),
        ]);

        SimaksiInspectionItem::create([
            'simaksi_registration_id' => $reg->id,
            'type' => 'gear',
            'name' => 'Headlamp',
            'checked_up' => true,
        ]);

        $this->actingAs($superadmin)
            ->get(route('management.simaksi.show', $reg->id))
            ->assertOk()
            ->assertSee('deleteSimaksiModal', false);

        $res = $this->actingAs($superadmin)
            ->delete(route('management.simaksi.destroy', $reg->id), [
                'reason' => 'Data uji coba',
            ]);

        $res->assertRedirect(route('management.simaksi.index'));
        $res->assertSessionHas('success');

        $this->assertDatabaseMissing('simaksi_registrations', ['id' => $reg->id]);
        $this->assertDatabaseMissing('climber_members', ['simaksi_registration_id' => $reg->id]);
        $this->assertDatabaseMissing('ojek_trips', ['id' => $trip->id]);
        $this->assertDatabaseMissing('ojek_settlements', ['id' => $settlement->id]);
        $this->assertDatabaseMissing('rental_records', ['id' => $rental->id]);
        $this->assertDatabaseMissing('pos_orders', ['id' => $posOrder->id]);
        $this->assertDatabaseMissing('whatsapp_messages', ['id' => $wa->id]);
        $this->assertDatabaseMissing('simaksi_checkpoints', ['simaksi_registration_id' => $reg->id]);
        $this->assertDatabaseMissing('simaksi_inspection_items', ['simaksi_registration_id' => $reg->id]);

        // Kuota, status driver, dan stok barang kembali akurat (tanpa double restore)
        $this->assertSame(6, $quota->fresh()->booked_quota);
        $this->assertSame('standby', $driver->fresh()->status);
        $this->assertSame(10, $tentItem->fresh()->stock_available);
        $this->assertSame(20, $gasItem->fresh()->stock_available);

        // File fisik terhapus
        Storage::disk('local')->assertMissing('ktp/ktp_test.jpg');
        Storage::disk('public')->assertMissing('proofs/proof_test.jpg');
        Storage::disk('public')->assertMissing('group_photos/group_test.jpg');
        Storage::disk('public')->assertMissing('gate_photos/gate_test.jpg');

        // Jejak audit tercatat
        $this->assertTrue(
            AuditLog::where('action', 'DELETE_SIMAKSI')
                ->where('entity_id', $reg->id)
                ->where('reason', 'Data uji coba')
                ->exists()
        );
    }

    public function test_deleting_already_rejected_simaksi_does_not_double_release_quota(): void
    {
        $superadmin = $this->createUser('superadmin');
        $hikingDate = Carbon::tomorrow()->toDateString();

        $quota = DailyQuota::create([
            'quota_date' => $hikingDate,
            'max_quota' => 150,
            'booked_quota' => 5,
        ]);

        $reg = SimaksiRegistration::create([
            'registration_code' => 'SMK-DEL-REJ',
            'leader_name' => 'Sudah Ditolak',
            'leader_nik' => '3205019999999999',
            'leader_phone' => '081299998888',
            'emergency_contact_name' => 'Keluarga',
            'emergency_contact_phone' => '081277776666',
            'hiking_date' => $hikingDate,
            'total_climbers' => 3,
            'status' => 'rejected',
            'simaksi_fee' => 75000,
            'total_amount' => 75000,
        ]);

        $this->actingAs($superadmin)
            ->delete(route('management.simaksi.destroy', $reg->id))
            ->assertRedirect(route('management.simaksi.index'));

        $this->assertDatabaseMissing('simaksi_registrations', ['id' => $reg->id]);
        $this->assertSame(5, $quota->fresh()->booked_quota);
    }
}
