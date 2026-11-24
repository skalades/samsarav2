<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Models\ClimberMember;
use App\Models\SimaksiCheckpoint;
use App\Models\SimaksiInspectionItem;
use App\Models\SimaksiRegistration;
use App\Models\User;
use App\Models\WhatsappMessage;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Revamp 4 level akses + alur Gatekeeper ↔ Kasir + WhatsApp Gateway.
 */
class FourLevelAccessAndGateFlowTest extends TestCase
{
    use RefreshDatabase;

    protected User $superadmin;

    protected User $kasir;

    protected User $gatekeeper;

    protected User $ranger;

    protected function setUp(): void
    {
        parent::setUp();

        $make = fn (string $role) => User::create([
            'name' => ucfirst($role).' Uji',
            'email' => "{$role}-flow@samsara.id",
            'password' => bcrypt('password'),
            'role' => $role,
            'status' => 'active',
        ]);

        $this->superadmin = $make('superadmin');
        $this->kasir = $make('kasir');
        $this->gatekeeper = $make('gatekeeper');
        $this->ranger = $make('ranger');
    }

    protected function makeRegistration(array $overrides = []): SimaksiRegistration
    {
        $reg = SimaksiRegistration::create(array_merge([
            'registration_code' => 'SMK-'.date('Ym').'-'.random_int(100, 999),
            'leader_name' => 'Asep Sunandar',
            'leader_nik' => '3205011234560001',
            'leader_phone' => '081234567890',
            'hiking_date' => Carbon::today()->toDateString(),
            'return_date' => Carbon::tomorrow()->toDateString(),
            'hiking_category' => 'camp',
            'total_climbers' => 2,
            'status' => 'pending',
            'simaksi_fee' => 50000,
            'total_amount' => 50000,
            'payment_status' => 'unpaid',
            'emergency_contact_name' => 'Ibu',
            'emergency_contact_phone' => '081200000000',
        ], $overrides));

        ClimberMember::create(['simaksi_registration_id' => $reg->id, 'full_name' => 'Asep Sunandar', 'nik' => '3205011234560001', 'gender' => 'L', 'is_leader' => true]);
        ClimberMember::create(['simaksi_registration_id' => $reg->id, 'full_name' => 'Dadang', 'nik' => '3205011234560002', 'gender' => 'L', 'is_leader' => false]);

        return $reg;
    }

    public function test_legacy_roles_map_to_four_levels(): void
    {
        $this->assertSame('superadmin', User::normalizeRole('admin'));
        $this->assertSame('superadmin', User::normalizeRole('auditor'));
        $this->assertSame('kasir', User::normalizeRole('verifikator'));
        $this->assertSame('kasir', User::normalizeRole('inventori'));
        $this->assertSame('gatekeeper', User::normalizeRole('gate_officer'));
        $this->assertSame('gatekeeper', User::normalizeRole('petugas_lapangan'));
        $this->assertSame('ranger', User::normalizeRole('ranger'));
    }

    public function test_each_level_lands_on_its_own_home_and_sees_only_its_menu(): void
    {
        $this->actingAs($this->superadmin)->get(route('management.dashboard'))
            ->assertOk()->assertSee('Ringkasan Operasional')->assertSee('Petugas & Akses');

        $this->actingAs($this->kasir)->get(route('management.dashboard'))
            ->assertOk()->assertSee('Meja Kasir')->assertSee('Antrean dari Gate')->assertDontSee('Petugas & Akses');

        $this->actingAs($this->gatekeeper)->get(route('management.dashboard'))->assertRedirect(route('management.gate.index'));
        $this->actingAs($this->gatekeeper)->get(route('management.gate.index'))
            ->assertOk()->assertSee('Siap naik')->assertDontSee('Kasir POS');

        $this->actingAs($this->ranger)->get(route('management.dashboard'))->assertRedirect(route('management.ranger.index'));
        $this->actingAs($this->ranger)->get(route('management.ranger.index'))
            ->assertOk()->assertSee('Rombongan di jalur')->assertDontSee('Verifikasi SIMAKSI');
    }

    public function test_access_is_restricted_per_level(): void
    {
        // Kasir tidak bisa mengelola petugas, tarif, audit, atau memeriksa di Gate
        foreach (['management.users.index', 'management.settings.tariffs', 'management.audit.index', 'management.gate.index', 'management.ranger.index'] as $route) {
            $this->actingAs($this->kasir)->get(route($route))->assertRedirect(route('management.dashboard'));
        }

        // Gatekeeper tidak bisa membuka POS, SIMAKSI, antrean kasir
        foreach (['management.pos.index', 'management.simaksi.index', 'management.desk.index', 'management.reports.index'] as $route) {
            $this->actingAs($this->gatekeeper)->get(route($route))->assertRedirect(route('management.gate.index'));
        }

        // Akun nonaktif tidak bisa masuk
        $this->kasir->update(['status' => 'suspended']);
        $this->actingAs($this->kasir)->get(route('management.dashboard'))->assertRedirect(route('management.login'));
    }

    public function test_full_gate_to_cashier_flow_with_clear_descent(): void
    {
        Storage::fake('public');
        $reg = $this->makeRegistration();

        // 1. Kasir konfirmasi lunas → WhatsApp diantrekan
        $this->actingAs($this->kasir)->post(route('management.simaksi.paid', $reg->id), ['payment_method' => 'transfer'])->assertRedirect();
        $this->assertSame('paid', $reg->fresh()->status);

        // 2. Kasir belum bisa memberangkatkan sebelum lolos Gate
        $this->actingAs($this->kasir)->post(route('management.desk.release', $reg->id))->assertSessionHas('error');
        $this->assertSame('paid', $reg->fresh()->status);

        // 3. Gatekeeper: foto tim wajib
        $memberIds = $reg->members()->pluck('id')->all();
        $this->actingAs($this->gatekeeper)->post(route('management.gate.ascent.submit', $reg->id), [
            'present_members' => $memberIds,
        ])->assertSessionHas('error');
        $this->assertSame('paid', $reg->fresh()->status);

        // 4. Gatekeeper: lolos dengan foto baru + checklist hybrid
        $this->actingAs($this->gatekeeper)->post(route('management.gate.ascent.submit', $reg->id), [
            'present_members' => $memberIds,
            'team_photo' => UploadedFile::fake()->image('tim.jpg', 800, 600),
            'gear' => [['name' => 'Tenda', 'checked' => 1], ['name' => 'Headlamp', 'checked' => 0]],
            'logistics' => [
                ['name' => 'Botol plastik', 'unit' => 'botol', 'qty' => 6],
                ['name' => 'Bungkus mi', 'unit' => 'bungkus', 'qty' => 10],
                ['name' => 'Kaleng', 'unit' => 'kaleng', 'qty' => 0],
            ],
            'notes' => 'Headlamp 1 orang tidak ada',
        ])->assertRedirect(route('management.gate.index'));

        $reg->refresh();
        $this->assertSame('gate_cleared', $reg->status);
        $this->assertSame($this->gatekeeper->id, $reg->gate_up_by);
        $this->assertTrue($reg->gate_photo_verified);
        $this->assertNotNull($reg->gate_photo_path);
        $this->assertSame(2, $reg->inspectionItems()->where('type', 'gear')->count());
        $this->assertSame(2, $reg->inspectionItems()->where('type', 'logistic')->count(), 'Baris logistik ber-qty 0 tidak disimpan');

        // 5. Kasir: berangkatkan → sedang mendaki, cetak surat jalan
        $this->actingAs($this->kasir)->get(route('management.desk.show', $reg->id))->assertOk()->assertSee('Berangkatkan');
        $this->actingAs($this->kasir)->post(route('management.desk.release', $reg->id))->assertRedirect(route('management.desk.show', $reg->id));
        $reg->refresh();
        $this->assertSame('checked_in', $reg->status);
        $this->assertSame($this->kasir->id, $reg->released_by);
        $this->actingAs($this->kasir)->get(route('management.gate.print_climber_pass', $reg->id))->assertOk();

        // 6. Ranger mencatat di Pos 2
        $this->actingAs($this->ranger)->post(route('management.ranger.checkpoint'), [
            'registration_id' => $reg->id, 'pos_code' => 'POS_2', 'direction' => 'ascending',
        ])->assertSessionHas('success');

        // 7. Gatekeeper: turun, semua logistik & alat kembali
        $items = $reg->inspectionItems()->get();
        $qtyDown = $items->where('type', 'logistic')->mapWithKeys(fn ($i) => [$i->id => $i->qty_up])->all();
        $gearDown = $items->where('type', 'gear')->where('checked_up', true)->mapWithKeys(fn ($i) => [$i->id => 1])->all();

        $this->actingAs($this->gatekeeper)->post(route('management.gate.descent.submit', $reg->id), [
            'qty_down' => $qtyDown,
            'gear_down' => $gearDown,
        ])->assertRedirect(route('management.gate.index'))->assertSessionHas('success');

        $reg->refresh();
        $this->assertSame('returned', $reg->status);
        $this->assertTrue($reg->gate_down_clear);

        // 8. Kasir: selesaikan → completed, KTP dimusnahkan, cetak izin parkir
        $this->actingAs($this->kasir)->post(route('management.desk.finalize', $reg->id))->assertRedirect(route('management.desk.show', $reg->id));
        $reg->refresh();
        $this->assertSame('completed', $reg->status);
        $this->assertSame($this->kasir->id, $reg->finalized_by);
        $this->assertNull($reg->leader_ktp_path);
        $this->assertSame(1, SimaksiCheckpoint::where('simaksi_registration_id', $reg->id)->where('pos_code', 'BASECAMP')->where('direction', 'descending')->count());
        $this->actingAs($this->kasir)->get(route('management.gate.print_parking_pass', $reg->id))->assertOk();
    }

    public function test_descent_with_missing_logistics_is_flagged(): void
    {
        $reg = $this->makeRegistration(['status' => 'checked_in', 'payment_status' => 'paid', 'checked_in_at' => now()]);
        $bottle = SimaksiInspectionItem::create(['simaksi_registration_id' => $reg->id, 'type' => 'logistic', 'name' => 'Botol plastik', 'unit' => 'botol', 'qty_up' => 6, 'checked_up' => true]);
        $tent = SimaksiInspectionItem::create(['simaksi_registration_id' => $reg->id, 'type' => 'gear', 'name' => 'Tenda', 'checked_up' => true]);

        $this->actingAs($this->gatekeeper)->post(route('management.gate.descent.submit', $reg->id), [
            'qty_down' => [$bottle->id => 4],
            'gear_down' => [$tent->id => 1],
        ])->assertSessionHas('error');

        $reg->refresh();
        $this->assertSame('returned', $reg->status);
        $this->assertFalse($reg->gate_down_clear);
        $this->assertStringContainsString('Botol plastik: kurang 2 botol', $reg->gate_down_notes);
        $this->assertSame(2, $bottle->fresh()->missingQty());

        $this->actingAs($this->kasir)->get(route('management.desk.show', $reg->id))->assertOk()->assertSee('ada selisih logistik', false);
    }

    public function test_scan_routes_gatekeeper_to_the_right_inspection(): void
    {
        $paid = $this->makeRegistration(['status' => 'paid', 'payment_status' => 'paid']);
        $onTrail = $this->makeRegistration(['status' => 'checked_in', 'payment_status' => 'paid']);

        $this->actingAs($this->gatekeeper)->get(route('management.gate.index', ['code' => $paid->registration_code]))
            ->assertRedirect(route('management.gate.ascent', $paid->id));
        $this->actingAs($this->gatekeeper)->get(route('management.gate.index', ['code' => $onTrail->registration_code]))
            ->assertRedirect(route('management.gate.descent', $onTrail->id));

        $this->actingAs($this->gatekeeper)->get(route('management.gate.ascent', $paid->id))->assertOk()->assertSee('Anggota hadir');
        $this->actingAs($this->gatekeeper)->get(route('management.gate.descent', $onTrail->id))->assertOk()->assertSee('Hitung ulang logistik');
    }

    public function test_whatsapp_is_logged_when_gateway_not_configured(): void
    {
        config(['services.whatsapp.driver' => 'log']);
        $reg = $this->makeRegistration();

        $this->actingAs($this->kasir)->post(route('management.simaksi.paid', $reg->id), ['payment_method' => 'cash_pos']);

        $msg = WhatsappMessage::where('simaksi_registration_id', $reg->id)->first();
        $this->assertNotNull($msg);
        $this->assertSame('payment_confirmed', $msg->event);
        $this->assertSame('6281234567890', $msg->phone);
        $this->assertSame('skipped', $msg->status);
        $this->assertStringContainsString($reg->registration_code, $msg->message);
        $this->assertStringContainsString(route('portal.simaksi.ticket', $reg->registration_code), $msg->message);
        $this->assertStringContainsString(route('portal.simaksi.ticket_pdf', $reg->registration_code), $msg->message);

        // Konfirmasi ulang tidak mengirim pesan ganda
        $this->actingAs($this->kasir)->post(route('management.simaksi.paid', $reg->id), ['payment_method' => 'cash_pos']);
        $this->assertSame(1, WhatsappMessage::where('simaksi_registration_id', $reg->id)->count());
    }

    public function test_whatsapp_is_sent_through_fonnte_when_configured(): void
    {
        config(['services.whatsapp.driver' => 'fonnte', 'services.whatsapp.token' => 'test-token']);
        Http::fake(['api.fonnte.com/*' => Http::response(['status' => true, 'detail' => 'success'], 200)]);

        $reg = $this->makeRegistration();
        $this->actingAs($this->kasir)->post(route('management.simaksi.paid', $reg->id), ['payment_method' => 'qris']);

        $msg = WhatsappMessage::where('simaksi_registration_id', $reg->id)->firstOrFail();
        $this->assertSame('sent', $msg->status);
        $this->assertSame('fonnte', $msg->driver);
        $this->assertNotNull($msg->sent_at);

        Http::assertSent(fn ($request) => $request->url() === 'https://api.fonnte.com/send'
            && $request->hasHeader('Authorization', 'test-token')
            && $request['target'] === '6281234567890');

        // Kasir bisa kirim ulang
        $this->actingAs($this->kasir)->post(route('management.simaksi.whatsapp', $reg->id))->assertSessionHas('success');
        $this->assertSame(2, WhatsappMessage::where('simaksi_registration_id', $reg->id)->count());
    }

    public function test_whatsapp_failure_is_recorded_and_can_be_retried(): void
    {
        config(['services.whatsapp.driver' => 'fonnte', 'services.whatsapp.token' => 'test-token']);
        Http::fake(['api.fonnte.com/*' => Http::response(['status' => false, 'reason' => 'device disconnected'], 200)]);

        $reg = $this->makeRegistration();
        $this->actingAs($this->kasir)->post(route('management.simaksi.paid', $reg->id), ['payment_method' => 'qris']);

        $msg = WhatsappMessage::where('simaksi_registration_id', $reg->id)->firstOrFail();
        $this->assertSame('failed', $msg->status);
        $this->assertStringContainsString('device disconnected', $msg->response);

        $this->actingAs($this->superadmin)->get(route('management.settings.whatsapp'))->assertOk()->assertSee('Gagal');
        $this->actingAs($this->superadmin)->post(route('management.settings.whatsapp.retry', $msg->id))->assertSessionHas('success');
    }

    public function test_superadmin_manages_staff_accounts(): void
    {
        $this->actingAs($this->superadmin)->get(route('management.users.index'))->assertOk()->assertSee('Gatekeeper');

        $this->actingAs($this->superadmin)->post(route('management.users.store'), [
            'name' => 'Ranger Baru',
            'email' => 'ranger.baru@samsara.id',
            'role' => 'ranger',
            'password' => 'rahasia123',
        ])->assertRedirect(route('management.users.index'));

        $user = User::where('email', 'ranger.baru@samsara.id')->firstOrFail();
        $this->assertTrue($user->isRanger());

        $this->actingAs($this->superadmin)->put(route('management.users.update', $user->id), [
            'name' => 'Ranger Baru', 'email' => 'ranger.baru@samsara.id', 'role' => 'gatekeeper',
        ])->assertRedirect();
        $this->assertTrue($user->fresh()->isGatekeeper());

        $this->actingAs($this->superadmin)->post(route('management.users.toggle', $user->id));
        $this->assertFalse($user->fresh()->isActive());

        // Tidak bisa menonaktifkan diri sendiri
        $this->actingAs($this->superadmin)->post(route('management.users.toggle', $this->superadmin->id))->assertSessionHas('error');

        // Kasir tidak bisa membuat akun
        $this->actingAs($this->kasir)->post(route('management.users.store'), [
            'name' => 'X', 'email' => 'x@samsara.id', 'role' => 'superadmin', 'password' => 'rahasia123',
        ])->assertRedirect(route('management.dashboard'));
        $this->assertDatabaseMissing('users', ['email' => 'x@samsara.id']);
    }

    public function test_superadmin_edits_inspection_checklist_used_by_gate(): void
    {
        $this->actingAs($this->superadmin)->post(route('management.settings.inspection.update'), [
            'gear' => ['Tenda', 'Trekking pole', ''],
            'logistic' => [['name' => 'Botol kaca', 'unit' => 'botol'], ['name' => '', 'unit' => 'x']],
        ])->assertSessionHas('success');

        $template = \App\Support\InspectionTemplate::get();
        $this->assertSame(['Tenda', 'Trekking pole'], $template['gear']);
        $this->assertSame([['name' => 'Botol kaca', 'unit' => 'botol']], $template['logistic']);

        $reg = $this->makeRegistration(['status' => 'paid', 'payment_status' => 'paid']);
        $this->actingAs($this->gatekeeper)->get(route('management.gate.ascent', $reg->id))->assertSee('Trekking pole')->assertSee('Botol kaca');
    }

    public function test_new_pages_render_for_their_levels(): void
    {
        AppSetting::set('trash_deposit_enabled', false);

        foreach ([
            [$this->superadmin, 'management.settings.whatsapp'],
            [$this->superadmin, 'management.settings.inspection'],
            [$this->superadmin, 'management.users.index'],
            [$this->superadmin, 'management.gate.index'],
            [$this->superadmin, 'management.ranger.index'],
            [$this->kasir, 'management.desk.index'],
            [$this->gatekeeper, 'management.gate.index'],
        ] as [$user, $route]) {
            $this->actingAs($user)->get(route($route))->assertOk();
        }

        $this->actingAs($this->kasir)->get(route('management.desk.index', ['tab' => 'turun']))->assertOk();
        foreach (['naik', 'jalur', 'kasir'] as $tab) {
            $this->actingAs($this->gatekeeper)->get(route('management.gate.index', ['tab' => $tab]))->assertOk();
        }
    }
}
