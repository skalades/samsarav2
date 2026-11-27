<?php

namespace Tests\Feature;

use App\Models\SimaksiRegistration;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderNotificationFeedTest extends TestCase
{
    use RefreshDatabase;

    private int $seq = 0;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(Carbon::today()->setTime(9, 0));
    }

    private function user(string $role): User
    {
        return User::create([
            'name' => 'User '.$role,
            'email' => $role.'@samsara.test',
            'password' => bcrypt('password'),
            'role' => $role,
        ]);
    }

    private function registration(array $overrides = []): SimaksiRegistration
    {
        $this->seq++;

        return SimaksiRegistration::create(array_merge([
            'registration_code' => 'SMK-NOTIF-'.$this->seq,
            'leader_name' => 'Ketua '.$this->seq,
            'leader_nik' => '32050100000000'.str_pad((string) $this->seq, 2, '0', STR_PAD_LEFT),
            'leader_phone' => '0812000000'.$this->seq,
            'emergency_contact_name' => 'Kontak',
            'emergency_contact_phone' => '081299999999',
            'hiking_date' => Carbon::tomorrow(),
            'total_climbers' => 3,
            'status' => 'pending',
            'simaksi_fee' => 75000,
            'total_amount' => 75000,
        ], $overrides));
    }

    public function test_first_poll_returns_cursor_without_old_events(): void
    {
        $this->registration();

        $res = $this->actingAs($this->user('kasir'))->getJson(route('management.notifications.orders'));

        $res->assertOk()
            ->assertJsonPath('events', [])
            ->assertJsonStructure(['cursor', 'role', 'events', 'badges' => ['simaksi', 'desk', 'gate_up', 'gate_down', 'ojek', 'wa_failed']]);
        $this->assertNotEmpty($res->json('cursor'));
    }

    public function test_new_order_after_cursor_is_reported(): void
    {
        $kasir = $this->user('kasir');
        $cursor = $this->actingAs($kasir)->getJson(route('management.notifications.orders'))->json('cursor');

        $this->travel(5)->seconds();
        $reg = $this->registration();

        $res = $this->actingAs($kasir)->getJson(route('management.notifications.orders', ['since' => $cursor]));

        $res->assertOk()
            ->assertJsonCount(1, 'events')
            ->assertJsonPath('events.0.type', 'order_created')
            ->assertJsonPath('events.0.code', $reg->registration_code)
            ->assertJsonPath('events.0.url', route('management.simaksi.show', $reg->id));

        // Pada detik yang sama event boleh terulang (>=, agar tidak ada yang terlewat),
        // tetapi key-nya identik sehingga klien tidak menampilkannya dua kali.
        $again = $this->actingAs($kasir)->getJson(route('management.notifications.orders', ['since' => $res->json('cursor')]));
        $this->assertSame($res->json('events.0.key'), $again->json('events.0.key'));

        // Setelah detik berlalu, pesanan lama tidak muncul lagi
        $this->travel(5)->seconds();
        $cursor2 = $this->actingAs($kasir)->getJson(route('management.notifications.orders', ['since' => $again->json('cursor')]))->json('cursor');
        $this->travel(5)->seconds();
        $this->actingAs($kasir)->getJson(route('management.notifications.orders', ['since' => $cursor2]))
            ->assertJsonCount(0, 'events');
    }

    public function test_payment_proof_upload_on_existing_order_is_reported(): void
    {
        $admin = $this->user('superadmin');
        $reg = $this->registration();

        $this->travel(1)->minutes();
        $cursor = $this->actingAs($admin)->getJson(route('management.notifications.orders'))->json('cursor');

        $this->travel(5)->seconds();
        $reg->update(['payment_proof_path' => 'proofs/x.jpg', 'payment_status' => 'paid']);

        $this->actingAs($admin)->getJson(route('management.notifications.orders', ['since' => $cursor]))
            ->assertOk()
            ->assertJsonCount(1, 'events')
            ->assertJsonPath('events.0.type', 'proof_uploaded');
    }

    public function test_kasir_receives_gatekeeper_ascent_and_descent_handoff_notifications(): void
    {
        $kasir = $this->user('kasir');
        $regUp = $this->registration(['status' => 'paid', 'payment_status' => 'paid', 'paid_at' => Carbon::now()->subMinutes(5)]);
        $regDownClear = $this->registration(['status' => 'checked_in', 'payment_status' => 'paid', 'paid_at' => Carbon::now()->subHours(2)]);
        $regDownIssue = $this->registration(['status' => 'checked_in', 'payment_status' => 'paid', 'paid_at' => Carbon::now()->subHours(2)]);

        $this->travel(1)->minutes();
        $cursor = $this->actingAs($kasir)->getJson(route('management.notifications.orders'))->json('cursor');

        $this->travel(5)->seconds();
        $regUp->update(['status' => 'gate_cleared', 'gate_up_at' => Carbon::now(), 'ojek_count' => 2]);
        $regDownClear->update(['status' => 'returned', 'gate_down_at' => Carbon::now(), 'gate_down_clear' => true]);
        $regDownIssue->update(['status' => 'returned', 'gate_down_at' => Carbon::now(), 'gate_down_clear' => false]);

        $res = $this->actingAs($kasir)->getJson(route('management.notifications.orders', ['since' => $cursor]));

        $res->assertOk()->assertJsonCount(3, 'events');

        $events = collect($res->json('events'))->keyBy('code');
        $this->assertSame('gate_ascent_cleared', $events[$regUp->registration_code]['type']);
        $this->assertSame(route('management.desk.show', $regUp->id), $events[$regUp->registration_code]['url']);
        $this->assertSame('gate_descent_returned', $events[$regDownClear->registration_code]['type']);
        $this->assertSame(route('management.desk.show', $regDownClear->id), $events[$regDownClear->registration_code]['url']);
        $this->assertSame('gate_descent_issue', $events[$regDownIssue->registration_code]['type']);
    }

    public function test_gatekeeper_only_receives_paid_simaksi_notifications(): void
    {
        $gatekeeper = $this->user('gatekeeper');
        $pendingReg = $this->registration(['status' => 'pending']);

        $this->travel(1)->minutes();
        $cursor = $this->actingAs($gatekeeper)->getJson(route('management.notifications.orders'))->json('cursor');

        $this->travel(5)->seconds();
        // Order baru belum bayar tidak boleh masuk ke Gatekeeper
        $this->registration(['status' => 'pending']);
        // Order yang dilunasi Kasir masuk ke Gatekeeper
        $pendingReg->update(['status' => 'paid', 'payment_status' => 'paid', 'paid_at' => Carbon::now()]);

        $res = $this->actingAs($gatekeeper)->getJson(route('management.notifications.orders', ['since' => $cursor]));

        $res->assertOk()
            ->assertJsonCount(1, 'events')
            ->assertJsonPath('events.0.type', 'simaksi_paid')
            ->assertJsonPath('events.0.code', $pendingReg->registration_code)
            ->assertJsonPath('events.0.url', route('management.gate.ascent', $pendingReg->id));
    }

    public function test_ranger_only_receives_released_group_notifications(): void
    {
        $ranger = $this->user('ranger');
        $reg = $this->registration(['status' => 'gate_cleared', 'payment_status' => 'paid', 'gate_up_at' => Carbon::now()->subMinutes(5)]);

        $this->travel(1)->minutes();
        $cursor = $this->actingAs($ranger)->getJson(route('management.notifications.orders'))->json('cursor');

        $this->travel(5)->seconds();
        // Booking baru atau lolos gate belum berangkat tidak masuk ke Ranger
        $this->registration(['status' => 'pending']);
        // Kasir memberangkatkan rombongan
        $reg->update(['status' => 'checked_in', 'released_at' => Carbon::now()]);

        $res = $this->actingAs($ranger)->getJson(route('management.notifications.orders', ['since' => $cursor]));

        $res->assertOk()
            ->assertJsonCount(1, 'events')
            ->assertJsonPath('events.0.type', 'group_released')
            ->assertJsonPath('events.0.code', $reg->registration_code)
            ->assertJsonPath('events.0.url', route('management.ranger.index', ['code' => $reg->registration_code]));
    }

    public function test_unauthenticated_cannot_read_feed(): void
    {
        $this->getJson(route('management.notifications.orders'))->assertUnauthorized();
    }

    public function test_layout_shows_bell_for_all_divisions_with_role_links(): void
    {
        $this->actingAs($this->user('kasir'))->get(route('management.dashboard'))
            ->assertOk()->assertSee('orderNotifMenu', false)->assertSee('Antrean Kasir');

        $this->actingAs($this->user('gatekeeper'))->get(route('management.gate.index'))
            ->assertOk()->assertSee('orderNotifMenu', false)->assertSee('Buka Gate');

        $this->actingAs($this->user('ranger'))->get(route('management.ranger.index'))
            ->assertOk()->assertSee('orderNotifMenu', false)->assertSee('Pos Ranger');
    }
}
