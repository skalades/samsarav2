<?php

namespace Tests\Feature;

use App\Models\DailyQuota;
use App\Models\SimaksiRegistration;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BookingAntiFraudTest extends TestCase
{
    use RefreshDatabase;

    private int $seq = 0;

    protected function setUp(): void
    {
        parent::setUp();
        // Jam tetap pagi hari agar travel() beberapa jam tidak melewati tengah malam
        $this->travelTo(Carbon::today()->setTime(8, 0));
    }

    private function payload(array $overrides = [], int $members = 2): array
    {
        $this->seq++;
        $base = '32050119900'.str_pad((string) $this->seq, 3, '0', STR_PAD_LEFT);
        $list = [];
        for ($i = 1; $i <= $members; $i++) {
            $list[] = ['full_name' => "Pendaki {$i}", 'nik' => $base.str_pad((string) $i, 2, '0', STR_PAD_LEFT), 'gender' => 'L'];
        }

        return array_merge([
            'hiking_category' => 'camp',
            'hiking_date' => Carbon::today()->addDays(3)->toDateString(),
            'return_date' => Carbon::today()->addDays(4)->toDateString(),
            'leader_name' => 'Pendaki 1',
            'leader_nik' => $list[0]['nik'],
            'leader_phone' => '0812345'.str_pad((string) $this->seq, 5, '0', STR_PAD_LEFT),
            'emergency_contact_name' => 'Keluarga',
            'emergency_contact_phone' => '081299990000',
            'payment_method' => 'transfer',
            'members' => $list,
        ], $overrides);
    }

    private function register(array $payload, string $ip = '10.0.0.1')
    {
        return $this->withServerVariables(['REMOTE_ADDR' => $ip])->postJson(route('portal.simaksi.register'), $payload);
    }

    public function test_unpaid_booking_gets_payment_deadline_and_expires_releasing_quota(): void
    {
        $res = $this->register($this->payload())->assertOk();
        $reg = SimaksiRegistration::where('registration_code', $res->json('registration_code'))->firstOrFail();

        $this->assertNotNull($reg->payment_due_at);
        $this->assertNotNull($res->json('payment_due_label'));
        $this->assertEquals(2, DailyQuota::whereDate('quota_date', $reg->hiking_date)->value('booked_quota'));

        // Belum lewat batas → tidak dibatalkan
        $this->artisan('simaksi:expire-unpaid')->assertSuccessful();
        $this->assertEquals('pending', $reg->fresh()->status);

        // Lewat batas → dibatalkan & kuota kembali
        $this->travel(3)->hours();
        $this->artisan('simaksi:expire-unpaid')->assertSuccessful();

        $this->assertEquals('rejected', $reg->fresh()->status);
        $this->assertStringContainsString('batas waktu pembayaran', $reg->fresh()->rejection_reason);
        $this->assertEquals(0, DailyQuota::whereDate('quota_date', $reg->hiking_date)->value('booked_quota'));

        // Idempoten: dijalankan ulang tidak mengurangi kuota lagi
        $this->artisan('simaksi:expire-unpaid')->assertSuccessful();
        $this->assertEquals(0, DailyQuota::whereDate('quota_date', $reg->hiking_date)->value('booked_quota'));
    }

    public function test_booking_with_payment_proof_or_marked_paid_never_expires(): void
    {
        Storage::fake('public');

        $withProof = $this->register($this->payload([
            'payment_proof' => UploadedFile::fake()->image('bukti.jpg'),
        ]))->assertOk();
        $this->assertNull(SimaksiRegistration::where('registration_code', $withProof->json('registration_code'))->value('payment_due_at'));

        $res = $this->register($this->payload(), '10.0.0.2')->assertOk();
        $reg = SimaksiRegistration::where('registration_code', $res->json('registration_code'))->firstOrFail();
        app(\App\Services\SimaksiService::class)->markAsPaid($reg, 'transfer');

        $this->travel(3)->hours();
        $this->artisan('simaksi:expire-unpaid');

        $this->assertEquals('pending', SimaksiRegistration::where('registration_code', $withProof->json('registration_code'))->value('status'));
        $this->assertEquals('paid', $reg->fresh()->status);
    }

    public function test_new_registration_reclaims_quota_from_expired_bookings_without_scheduler(): void
    {
        $date = Carbon::today()->addDays(3)->toDateString();
        DailyQuota::create(['quota_date' => $date, 'max_quota' => 4, 'booked_quota' => 0]);

        $this->register($this->payload(members: 4))->assertOk();
        $this->register($this->payload(members: 2), '10.0.0.2')->assertStatus(422); // penuh

        $this->travel(3)->hours();

        // Tanpa menjalankan scheduler, booking baru tetap bisa masuk karena booking basi dilepas dulu
        $this->register($this->payload(members: 2), '10.0.0.3')->assertOk();
        $this->assertEquals(2, DailyQuota::whereDate('quota_date', $date)->value('booked_quota'));
    }

    public function test_cash_pos_booking_is_held_until_cutoff_on_hiking_day(): void
    {
        $res = $this->register($this->payload(['payment_method' => 'cash_pos']))->assertOk();
        $reg = SimaksiRegistration::where('registration_code', $res->json('registration_code'))->firstOrFail();

        $this->assertEquals(
            Carbon::today()->addDays(3)->setTime(12, 0)->toDateTimeString(),
            $reg->payment_due_at->toDateTimeString()
        );
    }

    public function test_rejects_past_far_future_dates_and_invalid_nik(): void
    {
        $this->register($this->payload(['hiking_date' => Carbon::yesterday()->toDateString(), 'return_date' => null]))
            ->assertStatus(422)->assertJsonValidationErrors('hiking_date');

        $this->register($this->payload(['hiking_date' => Carbon::today()->addDays(61)->toDateString(), 'return_date' => null]), '10.0.0.2')
            ->assertStatus(422)->assertJsonValidationErrors('hiking_date');

        $this->register($this->payload(['leader_nik' => '12345']), '10.0.0.3')
            ->assertStatus(422)->assertJsonValidationErrors('leader_nik');
    }

    public function test_same_nik_cannot_be_booked_twice_on_overlapping_dates(): void
    {
        $first = $this->payload();
        $this->register($first)->assertOk();

        // Rombongan lain memakai NIK anggota yang sama di tanggal beririsan → ditolak
        $second = $this->payload();
        $second['members'][1]['nik'] = $first['members'][1]['nik'];
        $res = $this->register($second, '10.0.0.2')->assertStatus(422);
        $this->assertStringContainsString('sudah terdaftar', $res->json('message'));
        $this->assertStringNotContainsString($first['members'][1]['nik'], $res->json('message')); // NIK dimasking

        // NIK ganda di satu rombongan → ditolak
        $dupInside = $this->payload();
        $dupInside['members'][1]['nik'] = $dupInside['members'][0]['nik'];
        $this->register($dupInside, '10.0.0.3')->assertStatus(422);

        // Tanggal berbeda (tidak beririsan) → boleh
        $later = $first;
        $later['hiking_date'] = Carbon::today()->addDays(10)->toDateString();
        $later['return_date'] = Carbon::today()->addDays(11)->toDateString();
        $later['leader_phone'] = '081377770000';
        $this->register($later, '10.0.0.4')->assertOk();
    }

    public function test_limits_unpaid_bookings_per_whatsapp_number(): void
    {
        $this->register($this->payload(['leader_phone' => '0812-1111-2222']))->assertOk();
        $this->register($this->payload(['leader_phone' => '+62 812 1111 2222']), '10.0.0.2')->assertOk();

        $res = $this->register($this->payload(['leader_phone' => '081211112222']), '10.0.0.3')->assertStatus(422);
        $this->assertStringContainsString('belum dibayar', $res->json('message'));
    }

    public function test_registration_endpoint_is_rate_limited_per_ip(): void
    {
        for ($i = 0; $i < 3; $i++) {
            $this->register($this->payload(), '10.9.9.9')->assertOk();
        }

        $this->register($this->payload(), '10.9.9.9')->assertStatus(429);
        $this->register($this->payload(), '10.9.9.8')->assertOk();
    }

    public function test_cannot_upload_proof_to_expired_booking(): void
    {
        Storage::fake('public');
        $res = $this->register($this->payload())->assertOk();

        $this->travel(3)->hours();
        $this->artisan('simaksi:expire-unpaid');

        $this->postJson(route('portal.simaksi.upload_proof'), [
            'registration_code' => $res->json('registration_code'),
            'access_token' => $res->json('access_token'),
            'payment_proof' => UploadedFile::fake()->image('telat.jpg'),
        ])->assertStatus(422);
    }
}
