<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
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
    }
}
