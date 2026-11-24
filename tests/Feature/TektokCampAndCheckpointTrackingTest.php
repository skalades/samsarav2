<?php

namespace Tests\Feature;

use App\Models\ClimberMember;
use App\Models\DailyQuota;
use App\Models\SimaksiCheckpoint;
use App\Models\SimaksiRegistration;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TektokCampAndCheckpointTrackingTest extends TestCase
{
    use RefreshDatabase;

    protected User $ranger;

    protected User $gateOfficer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ranger = User::create([
            'name' => 'Ranger Pos',
            'email' => 'ranger-pos@samsara.id',
            'password' => bcrypt('password'),
            'role' => 'ranger',
        ]);

        $this->gateOfficer = User::create([
            'name' => 'Operator Basecamp',
            'email' => 'operator-basecamp@samsara.id',
            'password' => bcrypt('password'),
            'role' => 'gate_officer',
        ]);

        DailyQuota::create([
            'quota_date' => Carbon::today()->toDateString(),
            'max_quota' => 150,
            'booked_quota' => 0,
        ]);
    }

    public function test_tektok_registration_enforces_same_day_return(): void
    {
        $response = $this->postJson(route('portal.simaksi.register'), [
            'hiking_category' => 'tektok',
            'hiking_date' => Carbon::today()->toDateString(),
            'return_date' => Carbon::today()->addDays(2)->toDateString(), // Try to send multi-day
            'leader_name' => 'Pendaki Tektok Cepat',
            'leader_nik' => '3205011122330010',
            'leader_phone' => '081234567899',
            'leader_address' => 'Bandung',
            'emergency_contact_name' => 'Ibu',
            'emergency_contact_phone' => '081234567800',
            'payment_method' => 'cash_pos',
            'members' => [
                [
                    'full_name' => 'Pendaki Tektok Cepat',
                    'nik' => '3205011122330010',
                    'gender' => 'L',
                ],
            ],
        ]);

        $response->assertStatus(200);
        $reg = SimaksiRegistration::where('leader_name', 'Pendaki Tektok Cepat')->first();
        $this->assertNotNull($reg);
        $this->assertEquals('tektok', $reg->hiking_category);
        // Assert return_date was normalized to hiking_date
        $this->assertEquals($reg->hiking_date->toDateString(), $reg->return_date->toDateString());
    }

    public function test_ranger_checkpoint_scan_and_tektok_camping_prevention(): void
    {
        $reg = SimaksiRegistration::create([
            'registration_code' => 'SMK-202611-TK01',
            'leader_name' => 'Rangga Tektok',
            'leader_nik' => '3205011122330099',
            'leader_phone' => '081234567811',
            'emergency_contact_name' => 'Ibu',
            'emergency_contact_phone' => '081234567800',
            'hiking_category' => 'tektok',
            'hiking_date' => Carbon::today()->toDateString(),
            'return_date' => Carbon::today()->toDateString(),
            'total_climbers' => 2,
            'status' => 'checked_in',
        ]);

        // 1. Ranger Pos 1 scan ascending
        $response = $this->actingAs($this->ranger)->post(route('management.ranger.checkpoint'), [
            'registration_id' => $reg->id,
            'pos_code' => 'POS_1',
            'direction' => 'ascending',
        ]);

        $response->assertSessionHas('success');
        $reg->refresh();
        $this->assertEquals('POS_1', $reg->last_seen_pos);
        $this->assertEquals('ascending', $reg->last_seen_direction);

        // 2. Tektok trying to camp at Pos 2 should be rejected with policy error
        $campResponse = $this->actingAs($this->ranger)->post(route('management.ranger.checkpoint'), [
            'registration_id' => $reg->id,
            'pos_code' => 'POS_2',
            'direction' => 'camping',
        ]);

        $campResponse->assertSessionHas('error');
    }

    public function test_camp_registration_can_confirm_camping_at_pos_2_or_3(): void
    {
        $reg = SimaksiRegistration::create([
            'registration_code' => 'SMK-202611-CP01',
            'leader_name' => 'Fajar Camp',
            'leader_nik' => '3205011122330088',
            'leader_phone' => '081234567822',
            'emergency_contact_name' => 'Ayah',
            'emergency_contact_phone' => '081234567800',
            'hiking_category' => 'camp',
            'planned_campsite' => 'POS_2',
            'hiking_date' => Carbon::today()->toDateString(),
            'return_date' => Carbon::today()->addDay()->toDateString(),
            'total_climbers' => 4,
            'status' => 'checked_in',
        ]);

        // Ranger Pos 2 records group camping at Pos 2
        $response = $this->actingAs($this->ranger)->post(route('management.ranger.checkpoint'), [
            'registration_id' => $reg->id,
            'pos_code' => 'POS_2',
            'direction' => 'camping',
            'notes' => 'Mendirikan 2 tenda di dekat mata air',
        ]);

        $response->assertSessionHas('success');
        $reg->refresh();
        $this->assertEquals('POS_2', $reg->last_seen_pos);
        $this->assertEquals('camping', $reg->last_seen_direction);
        $this->assertEquals('POS_2', $reg->camp_location);
    }

    public function test_basecamp_radio_ht_dispatch_fallback(): void
    {
        $reg = SimaksiRegistration::create([
            'registration_code' => 'SMK-202611-HT01',
            'leader_name' => 'Dedi Radio',
            'leader_nik' => '3205011122330077',
            'leader_phone' => '081234567833',
            'emergency_contact_name' => 'Kakak',
            'emergency_contact_phone' => '081234567800',
            'hiking_category' => 'tektok',
            'hiking_date' => Carbon::today()->toDateString(),
            'return_date' => Carbon::today()->toDateString(),
            'total_climbers' => 2,
            'status' => 'checked_in',
        ]);

        // Ranger HP at Pos 3 died; Basecamp inputs via radio HT
        $response = $this->actingAs($this->gateOfficer)->post(route('management.gate.ht_dispatch'), [
            'registration_code' => 'HT01', // partial code match
            'pos_code' => 'POS_3',
            'direction' => 'descending',
            'notes' => 'Laporan HT Ranger Pos 3: rombongan mulai turun',
        ]);

        $response->assertSessionHas('success');
        $reg->refresh();
        $this->assertEquals('POS_3', $reg->last_seen_pos);
        $this->assertEquals('descending', $reg->last_seen_direction);

        $checkpoint = SimaksiCheckpoint::where('simaksi_registration_id', $reg->id)->first();
        $this->assertNotNull($checkpoint);
        $this->assertEquals('radio_ht', $checkpoint->entry_source);
    }
}
