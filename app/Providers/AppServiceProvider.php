<?php

namespace App\Providers;

use App\Support\Money;
use Illuminate\Support\Facades\Blade;
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

        // Directives format nominal Indonesia
        Blade::directive('rupiah', function ($expression) {
            return "<?php echo \App\Support\Money::format($expression); ?>";
        });

        Blade::directive('nominal', function ($expression) {
            return "<?php echo \App\Support\Money::formatNumber($expression); ?>";
        });
    }
}
