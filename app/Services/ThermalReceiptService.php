<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\OjekSettlement;
use App\Models\OjekTrip;
use App\Models\OperationalExpense;
use App\Models\PosOrder;
use App\Models\SimaksiRegistration;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Builds printer-agnostic receipt "documents" (a list of ops) that the browser
 * module public/assets/js/escpos-bt.js turns into ESC/POS bytes and sends to a
 * Bluetooth thermal printer (e.g. RP20N 58mm).
 *
 * Ops:
 *  ['op' => 'logo']
 *  ['op' => 'text', 'text' => '..', 'align' => 'left|center|right', 'bold' => bool, 'size' => 'normal|wide|tall|big']
 *  ['op' => 'row', 'left' => '..', 'right' => '..', 'bold' => bool]
 *  ['op' => 'hr', 'char' => '-']
 *  ['op' => 'qr', 'data' => '..', 'size' => 6]
 *  ['op' => 'feed', 'lines' => 1]
 *  ['op' => 'cut']
 *
 * Wrapping to the paper width (32 col for 58mm / 48 col for 80mm) happens in JS,
 * because paper width is a per-device setting.
 */
class ThermalReceiptService
{
    /** @var array<int, array<string, mixed>> */
    private array $ops = [];

    public function posOrder(PosOrder $order): array
    {
        $this->reset()->header('STRUK TRANSAKSI');

        $this->row('NO. STRUK', $order->order_number, true)
            ->row('TANGGAL', $order->created_at?->format('d/m/Y H:i'))
            ->row('KASIR', $order->cashier?->name)
            ->row('PELANGGAN', $order->customer_name, true);
        if ($order->simaksi) {
            $this->row('KODE SIMAKSI', $order->simaksi->registration_code, true);
        }
        $this->hr();

        foreach ($order->items as $item) {
            $this->text($item->item_name, bold: true)
                ->row('  '.$item->quantity.' x '.$this->rp($item->unit_price), $this->rp($item->subtotal));
        }

        $this->hr()
            ->row('TOTAL', $this->rp($order->total_amount), true, 'tall')
            ->row('METODE', strtoupper((string) $order->payment_method));
        if ($order->payment_method === 'cash') {
            $this->row('TUNAI', $this->rp($order->cash_tendered))
                ->row('KEMBALIAN', $this->rp($order->cash_change));
        }

        $this->hr()
            ->qr($order->order_number)
            ->text('SIMPAN STRUK INI SEBAGAI BUKTI SERAH TERIMA ALAT & LAYANAN RESMI', 'center')
            ->text('LINDUNGI ALAM '.strtoupper($this->setting('mountain_name', 'GUNUNG CIKURAY')), 'center', true);

        return $this->finish();
    }

    public function climberPass(SimaksiRegistration $reg): array
    {
        $this->reset()->header('SURAT JALAN PENDAKI', 'POS GERBANG BASECAMP PENDAKIAN');

        $this->text($reg->isTektok() ? '[TEKTOK - 1 HARI - DILARANG CAMP]' : '[CAMP - POS 2 / POS 3]', 'center', true)
            ->text('BUKTI FISIK PEMERIKSAAN SETIAP POS', 'center')
            ->feed()
            ->qr($reg->registration_code)
            ->text($reg->registration_code, 'center', true, 'wide')
            ->hr()
            ->row('WAKTU MASUK', ($reg->checked_in_at ?? now())->format('d/m/Y H:i').' WIB')
            ->row('KETUA TIM', strtoupper((string) $reg->leader_name), true)
            ->row('NO. WHATSAPP', $reg->leader_phone)
            ->row('TOTAL PENDAKI', $reg->total_climbers.' ORANG', true)
            ->row('RENCANA TURUN', $reg->return_date ? Carbon::parse($reg->return_date)->format('d/m/Y') : 'HARI SAMA')
            ->hr()
            ->row('STATUS MANIFEST', 'AKTIF DI JALUR')
            ->row('FOTO REGU', $reg->group_photo_path ? 'TERVERIFIKASI' : 'BELUM DIUNGGAH');
        if ($reg->ojek_count > 0) {
            $this->row('LAYANAN OJEK', $reg->ojek_count.' PENUMPANG');
        }
        if ($reg->has_vehicle || $reg->parking_fee > 0) {
            $this->row('PARKIR', $reg->motorcycle_count.' MTR / '.$reg->car_count.' MBL');
        }

        $this->hr()->text('DAFTAR MANIFEST PENDAKI:', bold: true);
        foreach ($reg->members as $idx => $m) {
            $flags = '('.$m->gender.')'
                .($m->is_leader ? ' KETUA' : '')
                .($m->medical_conditions && $m->medical_conditions !== 'Tidak Ada' ? ' *MEDIS' : '');
            $this->row(($idx + 1).'. '.strtoupper(mb_substr((string) $m->full_name, 0, 18)), $flags);
        }

        $this->hr('=')
            ->text('BUKTI PEMERIKSAAN POS LAPANGAN', 'center', true)
            ->text('POS 1: [ ]Naik [ ]Turun')
            ->text('  Jam:______ Paraf:______')
            ->text('POS 2: [ ]Lewat [ ]CAMP')
            ->text('  Jam:______ Paraf:______')
            ->text('POS 3: [ ]Lewat [ ]CAMP')
            ->text('  Jam:______ Paraf:______')
            ->hr('=')
            ->text('HOTLINE RESCUE & RADIO', 'center', true)
            ->text('SAR: '.$this->setting('emergency_hotline', '+62 821-2233-4455'), 'center')
            ->text('HT: '.$this->setting('sar_frequency', '149.250 MHz'), 'center')
            ->text('Simpan surat jalan ini & tunjukkan ke ranger di setiap Pos/Shelter.', 'center')
            ->feed()
            ->text('Dicetak '.now()->format('d-m-Y H:i:s'), 'center');

        return $this->finish();
    }

    /**
     * One document containing every voucher, separated by a cut.
     */
    public function ojekVouchers(SimaksiRegistration $reg, Collection $trips): array
    {
        $this->reset();
        $total = $trips->count();
        foreach ($trips->values() as $index => $trip) {
            $this->header('KUPON TIKET OJEK RESMI', 'PANGKALAN BASECAMP '.strtoupper($this->setting('mountain_name', 'CIKURAY')))
                ->qr($trip->trip_code)
                ->text($trip->trip_code, 'center', true, 'wide')
                ->hr()
                ->row('NO. SIMAKSI', $reg->registration_code, true)
                ->row('ROMBONGAN', $reg->leader_name)
                ->row('PENUMPANG', $trip->passenger_name, true)
                ->row('TRAYEK', $trip->route ?? $this->setting('ojek_route_name', 'Basecamp - Pos 1'))
                ->row('TANGGAL', now()->format('d/m/Y H:i'))
                ->row('STATUS', 'LUNAS DI LOKET', true);
            $this->driverRows($trip);
            $this->ojekFooter()
                ->text('KU-'.($index + 1).' DARI '.$total.' - BERLAKU HARI INI', 'center');
            if ($index < $total - 1) {
                $this->feed(3)->cut();
            }
        }

        return $this->finish();
    }

    public function tripVoucher(OjekTrip $trip): array
    {
        $this->reset()
            ->header('TIKET KUPON OJEK RESMI', 'PANGKALAN BASECAMP '.strtoupper($this->setting('mountain_name', 'CIKURAY')))
            ->qr($trip->trip_code)
            ->text($trip->trip_code, 'center', true, 'wide')
            ->hr()
            ->row('PENUMPANG', $trip->passenger_name, true)
            ->row('TRAYEK', $trip->route)
            ->row('TARIF', $this->rp($trip->fare_amount), true)
            ->row('STATUS', 'LUNAS DI KASIR');
        if ($trip->simaksi) {
            $this->row('REF SIMAKSI', $trip->simaksi->registration_code);
        }
        $this->driverRows($trip);
        $this->ojekFooter()->text('TANGGAL: '.$trip->created_at?->format('d/m/Y H:i').' WIB', 'center');

        return $this->finish();
    }

    public function parkingPass(SimaksiRegistration $reg, ?string $officer = null): array
    {
        if ($reg->motorcycle_count > 0 && $reg->car_count > 0) {
            $vehicle = $reg->motorcycle_count.' MOTOR & '.$reg->car_count.' MOBIL';
        } elseif ($reg->motorcycle_count > 0) {
            $vehicle = $reg->motorcycle_count.' UNIT SEPEDA MOTOR';
        } elseif ($reg->car_count > 0) {
            $vehicle = $reg->car_count.' UNIT MOBIL PRIBADI';
        } else {
            $vehicle = '1 UNIT KENDARAAN TERDATA';
        }

        $this->reset()
            ->header('TIKET IZIN KELUAR PARKIR', 'SECURITY GATE PASS')
            ->text('KENDARAAN DIVERIFIKASI RESMI', 'center', true)
            ->qr('PRK-PASS-'.$reg->registration_code)
            ->text('PRK-PASS-'.$reg->registration_code, 'center', true)
            ->hr()
            ->row('NO. SIMAKSI', $reg->registration_code, true)
            ->row('KETUA', $reg->leader_name, true)
            ->row('NO. KONTAK', $reg->leader_phone)
            ->row('CHECK-IN', $reg->checked_in_at ? $reg->checked_in_at->format('d/m/Y H:i') : '-')
            ->row('CHECK-OUT', ($reg->checked_out_at ?? now())->format('d/m/Y H:i'))
            ->hr()
            ->text('KENDARAAN TERDAFTAR:', 'center')
            ->text($vehicle, 'center', true, 'tall')
            ->text('PARKIR LUNAS ('.$this->rp($reg->parking_fee).')', 'center', true)
            ->hr()
            ->row('STATUS SAMPAH', $reg->trash_deposit_status === 'refunded' ? 'LENGKAP' : 'BEBAS JAMINAN')
            ->row('PETUGAS', $officer ?? 'Ranger Pos Basecamp')
            ->hr()
            ->text('VALIDASI POS PORTAL PARKIR', 'center', true)
            ->feed(3)
            ->text('[ PARAF / CAP PETUGAS ]', 'center')
            ->text('Tunjukkan tiket ini ke petugas portal saat kendaraan keluar.', 'center');

        return $this->finish();
    }

    public function settlement(OjekSettlement $s): array
    {
        $this->reset()
            ->header('BUKTI PENCAIRAN HONOR OJEK')
            ->row('NO. SLIP', $s->settlement_code, true)
            ->row('TANGGAL', $s->settled_at?->format('d/m/Y H:i'))
            ->row('KASIR', $s->cashier?->name)
            ->row('METODE', strtoupper((string) $s->payment_method), true)
            ->hr()
            ->row('PENGEMUDI', $s->driver?->name, true)
            ->row('NO. ROMPI', $s->driver?->registration_number)
            ->row('PLAT MOTOR', $s->driver?->plate_number)
            ->hr()
            ->row('TOTAL TRIP', $s->total_trips.' Trip', true);
        if ($s->notes) {
            $this->row('CATATAN', $s->notes);
        }
        $this->hr()
            ->text('TOTAL DITERIMA PENGEMUDI:', 'center')
            ->text($this->rp($s->total_amount), 'center', true, 'big')
            ->hr();
        $this->signatures('Kasir', $s->cashier?->name, 'Pengemudi', $s->driver?->name)
            ->text('Slip sah sebagai bukti tanda terima kasir & rekonsiliasi paguyuban ojek.', 'center');

        return $this->finish();
    }

    public function expense(OperationalExpense $e): array
    {
        $this->reset()
            ->header('BUKTI PENGELUARAN KAS', '(PETTY CASH OUT VOUCHER)')
            ->row('NO. VOUCHER', $e->expense_code, true)
            ->row('WAKTU', $e->created_at?->format('d/m/Y H:i'))
            ->row('KASIR', $e->cashier?->name ?? 'Kasir Posko')
            ->row('METODE KAS', strtoupper((string) $e->payment_method), true)
            ->hr()
            ->row('KATEGORI', $e->category_label, true)
            ->row('DIBAYAR KE', $e->recipient_name, true)
            ->text('Keperluan:')
            ->text((string) $e->notes)
            ->hr('=')
            ->row('TOTAL KELUAR', $this->rp($e->amount), true, 'tall')
            ->hr();
        $this->signatures('Kasir Posko', $e->cashier?->name ?? 'Kasir', 'Penerima', $e->recipient_name)
            ->text('Dicetak resmi Sistem POS Terpadu SAMSARA.', 'center')
            ->text('Bukti sah pengeluaran laci kasir.', 'center');

        return $this->finish();
    }

    // ------------------------------------------------------------------ helpers

    private function reset(): static
    {
        $this->ops = [];

        return $this;
    }

    private function finish(): array
    {
        $this->feed(3)->cut();

        return $this->ops;
    }

    private function header(string $title, ?string $subtitle = null): static
    {
        $this->ops[] = ['op' => 'logo'];
        $this->text($this->setting('expedition_name', 'SAMSARA EXPEDITION'), 'center', true, 'wide')
            ->text('BASECAMP '.strtoupper($this->setting('mountain_name', 'CIKURAY')).' '.$this->setting('mountain_elevation', '2.821 MDPL'), 'center')
            ->text('SAR: '.$this->setting('sar_frequency', '149.250 MHz').' | WA: '.$this->setting('admin_whatsapp_display', '0821-2233-4455'), 'center')
            ->hr('=')
            ->text($title, 'center', true);
        if ($subtitle) {
            $this->text($subtitle, 'center');
        }

        return $this->hr();
    }

    private function driverRows(OjekTrip $trip): void
    {
        $this->hr();
        if ($trip->driver) {
            $this->row('DRIVER', $trip->driver->name.' ('.$trip->driver->registration_number.')', true)
                ->row('PLAT NOMOR', $trip->driver->plate_number, true);
        } else {
            $this->row('DRIVER', '[ Antrean Pangkalan ]', true);
        }
    }

    private function ojekFooter(): static
    {
        return $this->hr()
            ->text('VERIFIKASI PANGKALAN OJEK', 'center', true)
            ->feed(3)
            ->text('Serahkan kupon ini kepada pengemudi.', 'center')
            ->text('Wajib helm & satu penumpang per motor.', 'center');
    }

    private function signatures(string $leftTitle, ?string $leftName, string $rightTitle, ?string $rightName): static
    {
        return $this->row($leftTitle, $rightTitle)
            ->feed(3)
            ->row('('.($leftName ?? '-').')', '('.($rightName ?? '-').')')
            ->feed();
    }

    private function text(string $text, string $align = 'left', bool $bold = false, string $size = 'normal'): static
    {
        $this->ops[] = ['op' => 'text', 'text' => $text, 'align' => $align, 'bold' => $bold, 'size' => $size];

        return $this;
    }

    private function row(string $left, mixed $right, bool $bold = false, string $size = 'normal'): static
    {
        $this->ops[] = ['op' => 'row', 'left' => $left, 'right' => (string) ($right ?? '-'), 'bold' => $bold, 'size' => $size];

        return $this;
    }

    private function hr(string $char = '-'): static
    {
        $this->ops[] = ['op' => 'hr', 'char' => $char];

        return $this;
    }

    private function qr(?string $data, int $size = 6): static
    {
        if ($data) {
            $this->ops[] = ['op' => 'qr', 'data' => $data, 'size' => $size];
        }

        return $this;
    }

    private function feed(int $lines = 1): static
    {
        $this->ops[] = ['op' => 'feed', 'lines' => $lines];

        return $this;
    }

    private function cut(): static
    {
        $this->ops[] = ['op' => 'cut'];

        return $this;
    }

    private function rp(mixed $amount): string
    {
        return 'Rp '.number_format((float) $amount, 0, ',', '.');
    }

    private function setting(string $key, string $default): string
    {
        return (string) AppSetting::get($key, $default);
    }
}
