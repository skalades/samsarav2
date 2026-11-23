<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Scheduler: Pembersihan otomatis berkas KTP fisik pendaki yang telah completed (PDP Compliance)
Schedule::command('samsara:purge-completed-ktp')->daily();

// Scheduler: Pembersihan otomatis foto regu pendaki setelah retensi 7 hari (Privacy & Storage Optimization)
Schedule::command('simaksi:purge-group-photos --days=7')->dailyAt('02:00');
