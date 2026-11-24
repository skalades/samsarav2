<?php

namespace App\Providers;

use App\Support\Money;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        require_once __DIR__ . '/../Support/helpers.php';
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Tanggal & waktu relatif selalu berbahasa Indonesia ("3 hari yang lalu", "Sabtu, 21 Nov")
        \Carbon\Carbon::setLocale('id');

        // Paginasi bergaya gelap sesuai design system SAMSARA OPS
        \Illuminate\Pagination\Paginator::defaultView('vendor.pagination.samsara');

        // Anti-fraud: batas pendaftaran SIMAKSI publik per IP (kuota harian kecil, 1 booking bisa 15 orang)
        RateLimiter::for('simaksi-register', function (Request $request) {
            $tooMany = fn () => response()->json([
                'success' => false,
                'message' => 'Terlalu banyak pendaftaran dari jaringan Anda. Silakan coba lagi nanti atau hubungi basecamp.',
            ], 429);

            return [
                Limit::perMinute(3)->by('simaksi-reg-m:'.$request->ip())->response($tooMany),
                Limit::perHour(10)->by('simaksi-reg-h:'.$request->ip())->response($tooMany),
            ];
        });

        // Directives format nominal Indonesia
        Blade::directive('rupiah', function ($expression) {
            return "<?php echo \App\Support\Money::format($expression); ?>";
        });

        Blade::directive('nominal', function ($expression) {
            return "<?php echo \App\Support\Money::formatNumber($expression); ?>";
        });
    }
}
