<?php

namespace App\Services\WhatsApp;

use App\Jobs\SendWhatsAppMessage;
use App\Models\AppSetting;
use App\Models\SimaksiRegistration;
use App\Models\WhatsappMessage;
use App\Services\NotificationService;
use App\Support\InspectionTemplate;

/**
 * Menyusun & mengantrekan pesan WhatsApp ke pendaki.
 * Setiap pesan dicatat di tabel whatsapp_messages agar bisa dipantau & dikirim ulang.
 */
class WhatsAppNotifier
{
    /**
     * Dipanggil otomatis saat kasir mengonfirmasi LUNAS.
     */
    public function notifyPaymentConfirmed(SimaksiRegistration $reg, ?int $triggeredBy = null): ?WhatsappMessage
    {
        if (! config('services.whatsapp.auto_send_on_paid', true)) {
            return null;
        }

        return $this->queue($reg, 'payment_confirmed', $this->paymentConfirmedMessage($reg), $triggeredBy);
    }

    /**
     * Kirim ulang info pendakian secara manual (oleh kasir / superadmin).
     */
    public function resend(SimaksiRegistration $reg, ?int $triggeredBy = null): ?WhatsappMessage
    {
        return $this->queue($reg, 'manual', $this->paymentConfirmedMessage($reg), $triggeredBy);
    }

    public function queue(?SimaksiRegistration $reg, string $event, string $message, ?int $triggeredBy = null, ?string $phone = null): ?WhatsappMessage
    {
        $phone = NotificationService::formatPhone((string) ($phone ?? $reg?->leader_phone));
        if (strlen($phone) < 9) {
            return null;
        }

        $record = WhatsappMessage::create([
            'simaksi_registration_id' => $reg?->id,
            'event' => $event,
            'phone' => $phone,
            'message' => $message,
            'driver' => app(WhatsAppManager::class)->driver()->name(),
            'status' => 'queued',
            'triggered_by' => $triggeredBy,
        ]);

        SendWhatsAppMessage::dispatch($record->id)->afterCommit();

        return $record;
    }

    public function paymentConfirmedMessage(SimaksiRegistration $reg): string
    {
        $profile = AppSetting::getExpeditionProfile();
        $trail = AppSetting::getTrailConfig();
        $expedition = $profile['expedition_name'];
        $mountain = $profile['mountain_name'].' '.$profile['mountain_elevation'];
        $basecamp = $profile['basecamp_name'];

        $hikingDate = $reg->hiking_date?->translatedFormat('l, d F Y') ?? '-';
        $returnDate = $reg->return_date?->translatedFormat('l, d F Y');
        $isTektok = $reg->hiking_category === 'tektok';
        $category = $isTektok ? 'TEKTOK (pulang hari)' : 'CAMP (bermalam)';
        $checkinWindow = $isTektok
            ? "{$trail['tektok_checkin_start']} - {$trail['tektok_checkin_end']} WIB"
            : "{$trail['camp_checkin_start']} - {$trail['camp_checkin_end']} WIB";

        $lines = [];
        $lines[] = "*{$expedition} — E-SIMAKSI TERBIT*";
        $lines[] = "{$mountain}";
        $lines[] = '';
        $lines[] = "Halo *{$reg->leader_name}*,";
        $lines[] = 'Pembayaran SIMAKSI rombongan Anda sudah kami verifikasi *LUNAS*. Berikut informasi lengkap pendakian Anda.';
        $lines[] = '';
        $lines[] = '*TIKET*';
        $lines[] = "• Kode: *{$reg->registration_code}*";
        $lines[] = '• Total dibayar: *Rp '.number_format((float) $reg->total_amount, 0, ',', '.').'*'.($reg->payment_method ? ' ('.strtoupper($reg->payment_method).')' : '');
        $lines[] = '';
        $lines[] = '*JADWAL & ROMBONGAN*';
        $lines[] = "• Naik: *{$hikingDate}*";
        if ($returnDate && ! $isTektok) {
            $lines[] = "• Turun: *{$returnDate}*";
        }
        $lines[] = "• Kategori: *{$category}*";
        $lines[] = "• Jumlah pendaki: *{$reg->total_climbers} orang*";
        if (! $isTektok && $reg->planned_campsite) {
            $lines[] = '• Rencana camp: *'.str_replace('_', ' ', strtoupper((string) $reg->planned_campsite)).'*';
        }
        if (! $isTektok) {
            $lines[] = '• Camp disarankan di *Pos 3* (terdekat ke puncak). *Dilarang camp di puncak*, tinggalkan tenda di Pos 3 saat summit attack.';
        }

        $extras = [];
        if ((int) $reg->ojek_count > 0) {
            $extras[] = "Ojek trail {$reg->ojek_count}x";
        }
        if ((int) $reg->tent_count > 0) {
            $extras[] = "Sewa tenda {$reg->tent_count}";
        }
        if ((int) $reg->motorcycle_count > 0 || (int) $reg->car_count > 0) {
            $extras[] = 'Parkir '.trim(((int) $reg->motorcycle_count > 0 ? "{$reg->motorcycle_count} motor " : '').((int) $reg->car_count > 0 ? "{$reg->car_count} mobil" : ''));
        }
        if ($extras) {
            $lines[] = '• Layanan: '.implode(', ', $extras);
        }

        $lines[] = '';
        $lines[] = '*CHECK-IN DI BASECAMP*';
        $lines[] = "• Lokasi: {$basecamp}";
        $lines[] = "• Jam check-in: *{$checkinWindow}*";
        $lines[] = '• Datang lengkap bersama seluruh anggota & bawa KTP asli.';
        $lines[] = '• Gatekeeper akan memeriksa alat wajib, menghitung logistik yang berpotensi jadi sampah, dan memotret tim.';
        $lines[] = '• Setelah lolos pemeriksaan, ambil Surat Jalan'.((int) $reg->ojek_count > 0 ? ' & Voucher Ojek' : '').' di kasir.';
        $lines[] = '• Saat turun, logistik dihitung ulang — pastikan semua sampah dibawa kembali.';

        $gear = InspectionTemplate::get()['gear'];
        if ($gear) {
            $lines[] = '';
            $lines[] = '*ALAT WAJIB*';
            foreach ($gear as $item) {
                $lines[] = "• {$item}";
            }
        }

        $lines[] = '';
        $lines[] = '*E-SIMAKSI / E-TIKET*';
        $lines[] = route('portal.simaksi.ticket', $reg->registration_code);
        $lines[] = 'Unduh PDF:';
        $lines[] = route('portal.simaksi.ticket_pdf', $reg->registration_code);
        $lines[] = '';
        $lines[] = '*DARURAT*';
        $lines[] = "• Hotline basecamp: {$profile['emergency_hotline']}";
        if (! empty($profile['sar_radio_enabled']) && ! empty($profile['sar_frequency'])) {
            $lines[] = "• Frekuensi radio SAR: {$profile['sar_frequency']}";
        }
        $lines[] = '';
        $lines[] = 'Salam lestari, selamat sampai puncak dan kembali dengan selamat.';
        $lines[] = "*{$basecamp}*";

        return implode("\n", $lines);
    }
}
