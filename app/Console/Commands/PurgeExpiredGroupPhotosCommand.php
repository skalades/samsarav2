<?php

namespace App\Console\Commands;

use App\Services\AuditService;
use App\Services\GroupPhotoService;
use Illuminate\Console\Command;

class PurgeExpiredGroupPhotosCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'simaksi:purge-group-photos {--days=7 : Batas masa retensi foto dalam hitungan hari}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pembersihan otomatis foto regu pendaki yang telah melewati masa retensi (default 7 hari) untuk privasi dan efisiensi penyimpanan server.';

    /**
     * Execute the console command.
     */
    public function handle(GroupPhotoService $photoService)
    {
        $days = (int) $this->option('days');
        if ($days < 1) {
            $days = 7;
        }

        $this->info("Menjalankan pembersihan foto regu SIMAKSI dengan batas retensi {$days} hari...");

        $count = $photoService->purgeExpiredPhotos($days);

        if ($count > 0) {
            AuditService::log(
                action: 'GROUP_PHOTOS_PURGED',
                entityType: 'SystemMaintenance',
                entityId: null,
                description: "Pembersihan otomatis retensi 7 hari: Berhasil menghapus {$count} berkas foto regu kadaluarsa dari server.",
                payload: ['retention_days' => $days, 'purged_count' => $count]
            );

            $this->info("Selesai! {$count} berkas foto regu berhasil dibersihkan.");
        } else {
            $this->info('Tidak ada foto regu yang memenuhi kriteria kedaluwarsa hari ini.');
        }

        return self::SUCCESS;
    }
}
