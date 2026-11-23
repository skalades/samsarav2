<?php

namespace App\Console\Commands;

use App\Models\SimaksiRegistration;
use App\Services\AuditService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PurgeCompletedKtpCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'samsara:purge-completed-ktp';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pembersihan otomatis file KTP fisik setelah pendakian selesai demi perlindungan privasi data pribadi (PDP)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai pembersihan berkas KTP pendaki selesai...');

        $completedRegistrations = SimaksiRegistration::where('status', 'completed')
            ->whereNotNull('leader_ktp_path')
            ->get();

        $count = 0;
        foreach ($completedRegistrations as $reg) {
            $path = $reg->leader_ktp_path;
            if ($path) {
                foreach (['public', 'local'] as $diskName) {
                    if (Storage::disk($diskName)->exists($path)) {
                        Storage::disk($diskName)->delete($path);
                    }
                }
            }

            $reg->update(['leader_ktp_path' => null]);
            $count++;

            AuditService::log(
                action: 'SCHEDULED_KTP_PURGE',
                entityType: 'SimaksiRegistration',
                entityId: $reg->id,
                description: "Berkas KTP {$reg->registration_code} berhasil dihapus permanen oleh sistem otomatis pasca-pendakian.",
                payload: ['registration_code' => $reg->registration_code]
            );
        }

        $this->info("Pembersihan selesai: {$count} berkas KTP berhasil dihapus permanen.");

        return Command::SUCCESS;
    }
}
