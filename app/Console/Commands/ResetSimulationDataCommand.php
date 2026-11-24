<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\ClimberMember;
use App\Models\DailyQuota;
use App\Models\InventoryItem;
use App\Models\OjekDriver;
use App\Models\OjekSettlement;
use App\Models\OjekTrip;
use App\Models\OperationalExpense;
use App\Models\PosOrder;
use App\Models\PosOrderItem;
use App\Models\RentalRecord;
use App\Models\SimaksiInspectionItem;
use App\Models\SimaksiRegistration;
use App\Models\WhatsappMessage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class ResetSimulationDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'samsara:reset-simulation {--force : Lewati konfirmasi interaktif (untuk otomatisasi server)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Bersihkan seluruh data transaksi dan simulasi operasional (SIMAKSI, POS, Ojek, Pengeluaran, Pesan WA) tanpa menghapus akun staf, pengaturan, atau master data';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->newLine();
        $this->alert('SAMSARA EXPEDITION — PEMBERSIHAN DATA SIMULASI & TRANSAKSI');
        $this->line('Perintah ini akan <fg=red;options=bold>MENGHAPUS SEMUA TRANSAKSI OPERASIONAL</>:');
        $this->line('  • Pendaftaran SIMAKSI, anggota regu, checklist inspeksi');
        $this->line('  • Transaksi kasir POS, rincian penjualan, kas keluar (expenses)');
        $this->line('  • Catatan sewa alat (rentals) & pemulihan stok inventori ke semula');
        $this->line('  • Riwayat trip ojek trail & slip settlement bagi hasil');
        $this->line('  • Log pesan WhatsApp Gateway & antrean job database');
        $this->line('  • Berkas KTP, bukti transfer, dan foto rombongan simulasi');
        $this->line('  • Reset kuota terisi harian kembali ke 0');
        $this->newLine();
        $this->info('Data yang <fg=green;options=bold>TETAP AMAN / TIDAK DIHAPUS</>:');
        $this->line('  ✓ Seluruh Akun Staf & Petugas (Superadmin, Kasir, Gatekeeper, Ranger)');
        $this->line('  ✓ Pengaturan Aplikasi, Tarif Tiket, Logo, QRIS, & WhatsApp Gateway');
        $this->line('  ✓ Master Data Inventori Alat (Tenda, Matras, Kompor, dll)');
        $this->line('  ✓ Master Data Driver Ojek Trail');
        $this->line('  ✓ Kalender Tanggal Kuota & Kondisi Jalur');
        $this->newLine();

        if (! $this->option('force') && ! $this->confirm('Apakah Anda yakin ingin mengosongkan seluruh data transaksi untuk simulasi baru?', false)) {
            $this->warn('Operasi dibatalkan. Tidak ada data yang diubah.');
            return self::SUCCESS;
        }

        $this->info('Memulai pembersihan data transaksi...');

        // 1. Nonaktifkan foreign key checks untuk keamanan truncate
        Schema::disableForeignKeyConstraints();

        try {
            // 2. Kosongkan tabel transaksi turunan & induk
            $this->runStep('Mengosongkan anggota regu SIMAKSI', fn () => ClimberMember::truncate());
            $this->runStep('Mengosongkan checklist inspeksi gate', fn () => SimaksiInspectionItem::truncate());
            $this->runStep('Mengosongkan catatan sewa alat', fn () => RentalRecord::truncate());
            $this->runStep('Mengosongkan rincian item kasir POS', fn () => PosOrderItem::truncate());
            $this->runStep('Mengosongkan transaksi kasir POS', fn () => PosOrder::truncate());
            $this->runStep('Mengosongkan trip armada ojek trail', fn () => OjekTrip::truncate());
            $this->runStep('Mengosongkan riwayat settlement ojek', fn () => OjekSettlement::truncate());
            $this->runStep('Mengosongkan catatan kas keluar operasional', fn () => OperationalExpense::truncate());
            $this->runStep('Mengosongkan registrasi SIMAKSI', fn () => SimaksiRegistration::truncate());
            $this->runStep('Mengosongkan riwayat log pesan WhatsApp', fn () => WhatsappMessage::truncate());
            $this->runStep('Mengosongkan audit logs operasional', fn () => AuditLog::truncate());

            // 3. Bersihkan antrean job yang tertahan di database (jika ada)
            if (Schema::hasTable('jobs')) {
                $this->runStep('Mengosongkan antrean jobs database', fn () => DB::table('jobs')->truncate());
            }
            if (Schema::hasTable('failed_jobs')) {
                $this->runStep('Mengosongkan antrean failed_jobs database', fn () => DB::table('failed_jobs')->truncate());
            }

            // 4. Reset booked_quota pada kalender kuota harian kembali ke 0
            $this->runStep('Mereset kuota terisi (booked_quota = 0)', function () {
                DailyQuota::query()->update(['booked_quota' => 0]);
            });

            // 5. Kembalikan stok tersedia inventori ke stok total
            $this->runStep('Mereset stok inventori ke stok total', function () {
                InventoryItem::query()->update(['stock_available' => DB::raw('stock_total')]);
            });

            // 6. Reset status driver ojek ke standby
            $this->runStep('Mereset status armada ojek ke standby', function () {
                OjekDriver::query()->update(['status' => 'standby']);
            });

        } finally {
            Schema::enableForeignKeyConstraints();
        }

        // 7. Bersihkan berkas upload simulasi di storage
        $this->runStep('Membersihkan berkas KTP & foto simulasi di storage', function () {
            // Local disk (KTP private)
            Storage::disk('local')->deleteDirectory('ktp');

            // Public disk (bukti transfer, foto regu, dll)
            Storage::disk('public')->deleteDirectory('ktp');
            Storage::disk('public')->deleteDirectory('payments');
            Storage::disk('public')->deleteDirectory('group_photos');
            Storage::disk('public')->deleteDirectory('gate_photos');
        });

        $this->newLine();
        $this->info('✔ SUKSES: Sistem sekarang bersih dan siap untuk simulasi baru!');
        $this->line('Anda dapat langsung membuat registrasi SIMAKSI baru atau transaksi POS untuk pengujian.');
        $this->newLine();

        return self::SUCCESS;
    }

    /**
     * Jalankan sub-langkah pembersihan dengan output visual rapi.
     */
    protected function runStep(string $title, \Closure $callback): void
    {
        $this->output->write("  • {$title}... ");
        $callback();
        $this->output->writeln('<info>OK</info>');
    }
}
