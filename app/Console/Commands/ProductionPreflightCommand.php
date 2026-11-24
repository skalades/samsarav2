<?php

namespace App\Console\Commands;

use App\Models\AppSetting;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redis;

/**
 * Uji kelayakan go-live: memeriksa konfigurasi env, infrastruktur, data operasional
 * wajib (rekening, kontak), dan keamanan akun. Exit code 1 bila ada FAIL sehingga
 * dapat dipakai sebagai gerbang di deploy.sh / CI.
 */
class ProductionPreflightCommand extends Command
{
    protected $signature = 'samsara:preflight {--strict : Perlakukan WARN sebagai FAIL}';

    protected $description = 'Uji kelayakan konfigurasi & data sebelum/ sesudah deploy ke server produksi';

    public const HEARTBEAT_KEY = 'samsara:scheduler_heartbeat';

    /** @var array<int, array{0:string,1:string,2:string}> */
    protected array $rows = [];

    public function handle(): int
    {
        $this->checkEnvironment();
        $this->checkInfrastructure();
        $this->checkOperationalSettings();
        $this->checkAccounts();

        $this->table(['Status', 'Pemeriksaan', 'Keterangan'], $this->rows);

        $fails = count(array_filter($this->rows, fn ($r) => str_contains($r[0], 'FAIL')));
        $warns = count(array_filter($this->rows, fn ($r) => str_contains($r[0], 'WARN')));
        $passes = count($this->rows) - $fails - $warns;

        $this->newLine();
        $this->line("Ringkasan: {$passes} PASS · {$warns} WARN · {$fails} FAIL");

        if ($fails > 0 || ($this->option('strict') && $warns > 0)) {
            $this->error('BELUM LAYAK GO-LIVE — perbaiki item FAIL'.($this->option('strict') ? '/WARN' : '').' di atas.');

            return self::FAILURE;
        }

        $this->info('LAYAK GO-LIVE.');

        return self::SUCCESS;
    }

    protected function checkEnvironment(): void
    {
        $this->check(app()->environment('production'), 'APP_ENV = production', 'Sekarang: '.app()->environment());
        $this->check(! config('app.debug'), 'APP_DEBUG = false', 'Debug membocorkan stack trace & kredensial');
        $this->check(! empty(config('app.key')), 'APP_KEY terisi', 'Jalankan php artisan key:generate');
        $this->check(str_starts_with((string) config('app.url'), 'https://'), 'APP_URL memakai https', (string) config('app.url'));
        $this->check(! config('app.allow_quick_login'), 'ALLOW_QUICK_LOGIN = false', 'Quick login melewati password');
        $this->check((bool) config('session.secure'), 'SESSION_SECURE_COOKIE = true', 'Cookie sesi hanya lewat HTTPS');
        $this->check(config('app.timezone') === 'Asia/Jakarta', 'APP_TIMEZONE = Asia/Jakarta', (string) config('app.timezone'));

        $queue = (string) config('queue.default');
        $this->check($queue !== 'sync', 'QUEUE_CONNECTION bukan sync', "Sekarang: {$queue} (WA dikirim di request → lambat)", warnOnly: true);
        $this->check(! in_array(config('cache.default'), ['array', 'null'], true), 'CACHE_STORE persisten', 'Rate-limit & heartbeat butuh cache persisten');
        $this->check(! in_array(config('session.driver'), ['array', 'cookie'], true), 'SESSION_DRIVER server-side', (string) config('session.driver'));

        $mailer = (string) config('mail.default');
        $this->check(! in_array($mailer, ['log', 'array'], true), 'MAIL_MAILER aktif', "Sekarang: {$mailer}", warnOnly: true);

        $wa = (string) config('services.whatsapp.driver');
        $this->check($wa !== 'log', 'WA_GATEWAY_DRIVER aktif', "Sekarang: {$wa} (notifikasi tidak terkirim)", warnOnly: true);
        if ($wa !== 'log') {
            $this->check(! empty(config('services.whatsapp.token')), 'WA_GATEWAY_TOKEN terisi', 'Token gateway kosong');
        }

        $this->check(app()->configurationIsCached(), 'Config ter-cache', 'Jalankan php artisan config:cache', warnOnly: true);
    }

    protected function checkInfrastructure(): void
    {
        try {
            DB::connection()->getPdo();
            $this->markPass('Koneksi database', (string) config('database.default'));
            $pending = $this->pendingMigrations();
            $this->check($pending === 0, 'Migrasi database lengkap', "{$pending} migrasi belum dijalankan");
        } catch (\Throwable $e) {
            $this->markFail('Koneksi database', $this->short($e));
        }

        $usesRedis = in_array('redis', [config('cache.default'), config('session.driver'), config('queue.default')], true);
        if ($usesRedis) {
            try {
                Redis::connection()->ping();
                $this->markPass('Koneksi Redis', (string) config('database.redis.client'));
            } catch (\Throwable $e) {
                $this->markFail('Koneksi Redis', $this->short($e));
            }
            $this->check(! in_array(config('database.redis.default.password'), [null, '', 'null'], true), 'REDIS_PASSWORD terisi', 'Redis tanpa password', warnOnly: true);
        }

        foreach (['storage/app', 'storage/framework', 'storage/logs', 'bootstrap/cache'] as $dir) {
            $this->check(is_writable(base_path($dir)), "Folder {$dir} dapat ditulis", 'chown www-data / chmod 775');
        }

        $this->check(file_exists(public_path('storage')), 'Symlink public/storage', 'Jalankan php artisan storage:link');

        $beat = Cache::get(self::HEARTBEAT_KEY);
        $alive = $beat && now()->diffInMinutes($beat, true) <= 5;
        $this->check($alive, 'Scheduler berjalan (heartbeat ≤ 5 menit)',
            $beat ? 'Terakhir: '.$beat : 'Belum pernah — pasang cron/supervisor schedule:work', warnOnly: true);
    }

    protected function checkOperationalSettings(): void
    {
        $required = [
            'payment_bank_name' => 'Nama bank tujuan transfer',
            'payment_bank_account' => 'Nomor rekening resmi',
            'payment_bank_holder' => 'Nama pemilik rekening',
            'admin_whatsapp' => 'Nomor WhatsApp admin',
            'emergency_hotline' => 'Hotline darurat SAR',
        ];

        foreach ($required as $key => $label) {
            $value = trim((string) AppSetting::get($key, config('samsara.'.(str_starts_with($key, 'payment_') ? 'payment.'.substr($key, 8) : 'contact.'.$key))));
            $isDemo = in_array($value, config('samsara.demo_values', []), true);
            $this->check($value !== '' && ! $isDemo, "Pengaturan: {$label}", $isDemo
                ? "Masih nilai DEMO ({$value}) — ganti dengan data resmi"
                : "Isi di Pengaturan → Tarif & Profil ({$key})");
        }

        $this->check(! empty(AppSetting::get('payment_qris_image')), 'Gambar QRIS resmi diunggah', 'Tanpa QRIS, pendaki hanya bisa transfer/tunai', warnOnly: true);
        $this->check((int) AppSetting::get('default_daily_quota', 0) > 0, 'Kuota harian default > 0', 'Atur di Kalender Kuota', warnOnly: true);
    }

    protected function checkAccounts(): void
    {
        try {
            $superadmins = User::where('role', User::ROLE_SUPERADMIN)->where('status', 'active')->count();
            $this->check($superadmins > 0, 'Ada superadmin aktif', 'Jalankan php artisan db:seed --class=SuperAdminSeeder');

            $weak = User::where('status', 'active')->get(['email', 'password'])
                ->filter(fn ($u) => collect(config('samsara.forbidden_passwords', []))->contains(fn ($p) => Hash::check($p, $u->password)))
                ->pluck('email');
            $this->check($weak->isEmpty(), 'Tidak ada akun ber-password bawaan', 'Ganti password: '.$weak->implode(', '));

            $this->check(User::whereIn('email', ['kasir@samsara.id', 'gate@samsara.id', 'ranger@samsara.id'])->doesntExist(),
                'Tidak ada akun demo seeder', 'Nonaktifkan/hapus akun demo kasir/gate/ranger@samsara.id', warnOnly: true);
        } catch (\Throwable $e) {
            $this->markFail('Pemeriksaan akun', $this->short($e));
        }
    }

    protected function pendingMigrations(): int
    {
        Artisan::call('migrate:status', ['--pending' => true]);

        return substr_count(Artisan::output(), 'Pending');
    }

    protected function check(bool $ok, string $label, string $hint = '', bool $warnOnly = false): void
    {
        if ($ok) {
            $this->markPass($label);
        } elseif ($warnOnly) {
            $this->rows[] = ['<fg=yellow>WARN</>', $label, $hint];
        } else {
            $this->markFail($label, $hint);
        }
    }

    protected function markPass(string $label, string $note = ''): void
    {
        $this->rows[] = ['<fg=green>PASS</>', $label, $note];
    }

    protected function markFail(string $label, string $hint): void
    {
        $this->rows[] = ['<fg=red>FAIL</>', $label, $hint];
    }

    protected function short(\Throwable $e): string
    {
        return mb_strimwidth($e->getMessage(), 0, 90, '…');
    }
}
