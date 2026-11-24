<?php

namespace Tests\Feature;

use App\Models\SimaksiRegistration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Pencarian tiket lewat nomor WhatsApp wajib bukti kepemilikan (4 digit terakhir NIK ketua),
 * agar kode tiket tidak bocor ke orang yang sekadar tahu nomor HP pendaki.
 */
class TicketLookupOwnershipTest extends TestCase
{
    use RefreshDatabase;

    private SimaksiRegistration $reg;

    protected function setUp(): void
    {
        parent::setUp();

        $this->reg = SimaksiRegistration::create([
            'registration_code' => 'SMK-202611-OWNER123',
            'leader_name' => 'Pendaki Pemilik',
            'leader_nik' => '3205011990004321',
            'leader_phone' => '081234567890',
            'leader_city' => 'Garut',
            'emergency_contact_name' => 'Keluarga',
            'emergency_contact_phone' => '081299990000',
            'hiking_date' => now()->addDays(2)->toDateString(),
            'total_climbers' => 1,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'payment_method' => 'transfer',
            'simaksi_fee' => 25000,
            'total_amount' => 25000,
        ]);
    }

    private function lookup(array $params)
    {
        return $this->getJson(route('portal.simaksi.lookup', $params));
    }

    public function test_lookup_by_code_still_works_without_nik(): void
    {
        $this->lookup(['query' => 'smk-202611-owner123'])->assertOk()
            ->assertJsonPath('data.registration_code', 'SMK-202611-OWNER123');
    }

    public function test_phone_lookup_without_nik_is_rejected_and_leaks_no_code(): void
    {
        $this->lookup(['query' => '081234567890'])->assertStatus(422)
            ->assertJsonPath('requires_nik', true)
            ->assertDontSee('SMK-202611-OWNER123');
    }

    public function test_phone_lookup_with_wrong_nik_returns_not_found(): void
    {
        $this->lookup(['query' => '081234567890', 'nik_last4' => '0000'])->assertNotFound()
            ->assertDontSee('SMK-202611-OWNER123');
    }

    public function test_phone_lookup_with_correct_nik_works_across_phone_formats(): void
    {
        foreach (['081234567890', '+62 812-3456-7890', '6281234567890'] as $phone) {
            $this->lookup(['query' => $phone, 'nik_last4' => '4321'])->assertOk()
                ->assertJsonPath('data.registration_code', 'SMK-202611-OWNER123');
        }
    }

    public function test_nik_guessing_is_locked_per_phone_after_five_failures(): void
    {
        foreach (['0001', '0002', '0003', '0004', '0005'] as $i => $guess) {
            // IP berbeda tiap percobaan: kunci berlaku per nomor, bukan per IP
            $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.'.($i + 1)])
                ->lookup(['query' => '081234567890', 'nik_last4' => $guess])->assertNotFound();
        }

        $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.99'])
            ->lookup(['query' => '081234567890', 'nik_last4' => '4321'])->assertStatus(429);
    }

    public function test_portal_has_no_hardcoded_demo_ticket_codes(): void
    {
        $this->get(route('portal.home'))->assertOk()
            ->assertDontSee('SMK-202611-001')
            ->assertDontSee('SMK-202611-002')
            ->assertSee('searchNikLast4', false);
    }
}
