<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Models\SimaksiRegistration;
use App\Models\User;
use App\Support\CodeGenerator;
use Database\Seeders\SuperAdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Regresi untuk temuan audit kelayakan go-live.
 */
class GoLiveHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_portal_never_shows_demo_payment_details_when_unconfigured(): void
    {
        $html = $this->get(route('portal.home'))->assertOk()->getContent();

        foreach (['810-0982-111', '8100982111', 'PT SAMSARA ADVENTURE GARUT', 'ID102026110099', '6282122334455', '2233-4455', 'api.qrserver.com'] as $demo) {
            $this->assertStringNotContainsString($demo, $html, "Portal masih menampilkan nilai contoh: {$demo}");
        }
    }

    public function test_configured_payment_details_are_shown(): void
    {
        AppSetting::set('payment_bank_account', '123-456-7890', 'Rekening');
        AppSetting::set('payment_bank_holder', 'CV BASECAMP RESMI', 'Pemilik');

        $this->get(route('portal.home'))->assertOk()
            ->assertSee('123-456-7890')
            ->assertSee('CV BASECAMP RESMI');
    }

    public function test_superadmin_seeder_never_uses_known_default_password(): void
    {
        config(['samsara.superadmin.password' => '']);
        $this->seed(SuperAdminSeeder::class);

        $admin = User::where('email', config('samsara.superadmin.email'))->firstOrFail();
        $this->assertSame(User::ROLE_SUPERADMIN, $admin->role);
        $this->assertFalse(Hash::check('samsara2026', $admin->password));
    }

    public function test_superadmin_seeder_rejects_forbidden_password_and_keeps_existing_password(): void
    {
        config(['samsara.superadmin.password' => 'samsara2026']);
        try {
            $this->seed(SuperAdminSeeder::class);
            $this->fail('Seeder seharusnya menolak password bawaan.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('dilarang', $e->getMessage());
        }

        config(['samsara.superadmin.password' => 'Rahasia-Kuat-2026!']);
        $this->seed(SuperAdminSeeder::class);
        config(['samsara.superadmin.password' => 'Password-Lain-2026!']);
        $this->seed(SuperAdminSeeder::class);

        $admin = User::where('email', config('samsara.superadmin.email'))->firstOrFail();
        $this->assertTrue(Hash::check('Rahasia-Kuat-2026!', $admin->password), 'Seeder ulang tidak boleh menimpa password.');
    }

    public function test_simaksi_code_is_compact_readable_and_token_is_high_entropy(): void
    {
        $code = CodeGenerator::simaksiCode();
        $this->assertMatchesRegularExpression('/^SMK-\d{4}-[2-9A-HJ-KM-NP-Z]{5}$/', $code);
        $this->assertLessThanOrEqual(16, strlen($code), 'Kode harus muat 1 baris di printer thermal 58mm wide.');

        $token = CodeGenerator::ticketAccessToken();
        $this->assertSame(32, strlen($token));
    }

    public function test_currency_sanitizer_leaves_passwords_and_account_numbers_untouched(): void
    {
        Route::middleware('web')->post('/__test/echo', fn () => request()->only(['password', 'payment_bank_account', 'amount']));

        $this->post('/__test/echo', [
            'password' => '123.456.789',
            'payment_bank_account' => '810.098.211',
            'amount' => 'Rp 25.000',
        ])->assertExactJson([
            'password' => '123.456.789',
            'payment_bank_account' => '810.098.211',
            'amount' => 25000,
        ]);
    }

    public function test_public_ticket_and_lookup_mask_phone_and_full_address(): void
    {
        $reg = SimaksiRegistration::create([
            'registration_code' => 'SMK-202611-TESTMASK',
            'leader_name' => 'Pendaki Privat',
            'leader_nik' => '3205011990000001',
            'leader_phone' => '081234567890',
            'leader_city' => 'Garut',
            'leader_address' => 'Jl. Rahasia No. 7 RT 01 RW 02',
            'emergency_contact_name' => 'Keluarga',
            'emergency_contact_phone' => '081299990000',
            'hiking_date' => now()->addDays(2)->toDateString(),
            'total_climbers' => 1,
            'status' => 'paid',
            'payment_status' => 'paid',
            'payment_method' => 'transfer',
            'simaksi_fee' => 25000,
            'total_amount' => 25000,
        ]);

        $this->get($reg->publicTicketUrl())->assertOk()
            ->assertDontSee('081234567890')
            ->assertSee('0812****890');

        $this->getJson(route('portal.simaksi.lookup', [
            'code' => $reg->registration_code,
            'token' => $reg->access_token,
        ]))->assertOk()
            ->assertJsonPath('data.leader_address', 'Garut')
            ->assertDontSee('Jl. Rahasia');
    }

    public function test_preflight_fails_outside_production_configuration(): void
    {
        $this->artisan('samsara:preflight')->assertExitCode(1);
    }
}
