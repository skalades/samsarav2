<?php

namespace App\Console\Commands;

use App\Services\SimaksiService;
use Illuminate\Console\Command;

class ExpireUnpaidSimaksiCommand extends Command
{
    protected $signature = 'simaksi:expire-unpaid {--date= : Batasi ke satu tanggal pendakian (YYYY-MM-DD)}';

    protected $description = 'Batalkan otomatis booking SIMAKSI yang melewati batas waktu pembayaran dan kembalikan kuotanya';

    public function handle(SimaksiService $service): int
    {
        $count = $service->expireOverdueBookings($this->option('date') ?: null);

        $this->info("{$count} booking SIMAKSI kedaluwarsa dibatalkan & kuotanya dikembalikan.");

        return self::SUCCESS;
    }
}
