<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\SimaksiRegistration;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Format clean WhatsApp phone number (convert 08... to 628...)
     */
    public static function formatPhone(string $phone): string
    {
        $clean = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($clean, '0')) {
            $clean = '62'.substr($clean, 1);
        } elseif (str_starts_with($clean, '8')) {
            $clean = '62'.$clean;
        }

        return $clean;
    }

    /**
     * Generate wa.me Click-to-Chat URL
     */
    public static function getWhatsAppUrl(string $phone, string $message): string
    {
        $cleanPhone = self::formatPhone($phone);

        return 'https://wa.me/'.$cleanPhone.'?text='.rawurlencode($message);
    }

    /**
     * WhatsApp message for new registration (Booking Confirmation & Payment Instructions)
     */
    public static function bookingCreatedMessage(SimaksiRegistration $reg): string
    {
        $hikingDate = $reg->hiking_date ? $reg->hiking_date->format('d M Y') : '-';
        $total = number_format($reg->total_amount, 0, ',', '.');
        $uniqueNote = $reg->unique_code > 0 ? " (Termasuk 3 digit kode unik verifikasi: +{$reg->unique_code})" : '';
        $bankName = AppSetting::get('payment_bank_name', config('samsara.payment.bank_name'));
        $bankAcc = AppSetting::get('payment_bank_account', config('samsara.payment.bank_account'));
        $bankHolder = AppSetting::get('payment_bank_holder', config('samsara.payment.bank_holder'));
        $expedition = AppSetting::get('expedition_name', 'SAMSARA EXPEDITION');
        $mountain = AppSetting::get('mountain_name', 'GUNUNG CIKURAY');
        $elevation = AppSetting::get('mountain_elevation', '2.821 MDPL');
        $basecamp = AppSetting::get('basecamp_name', 'Basecamp SAMSARA');
        $portalUrl = url('/?code='.$reg->registration_code);

        return <<<TEXT
*{$expedition} — {$mountain} {$elevation}*
// NOTIFIKASI REGISTRASI TIKET SIMAKSI //

Halo *{$reg->leader_name}*,
Pendaftaran izin pendakian Anda via Jalur Samsara Garut telah kami terima di sistem.

[RINCIAN PERMOHONAN]
- Kode Tiket: *{$reg->registration_code}*
- Tanggal Naik: *{$hikingDate}*
- Total Rombongan: *{$reg->total_climbers} Orang*
- Total Biaya: *Rp {$total}*{$uniqueNote}
- Status: *PENDING (MENUNGGU PEMBAYARAN)*

[REKENING RESMI OPERASIONAL]
- {$bankName}: *{$bankAcc}*
- Atas Nama: *{$bankHolder}*
- Mohon transfer persis sesuai nominal di atas untuk percepatan verifikasi otomatis.

[PELACAKAN TIKET & UPLOAD STRUK]
Bila Anda ter-close saat transfer, buka menu CEK TIKET di portal:
{$portalUrl}

Salam Lestari,
*Pos {$basecamp} Cikuray*
TEXT;
    }

    /**
     * Pesan WhatsApp saat SIMAKSI diverifikasi LUNAS oleh kasir:
     * seluruh informasi pendakian + tautan e-SIMAKSI / e-tiket.
     */
    public static function paymentConfirmedMessage(SimaksiRegistration $reg): string
    {
        return app(\App\Services\WhatsApp\WhatsAppNotifier::class)->paymentConfirmedMessage($reg);
    }

    /**
     * WhatsApp message when revision is requested by verifier
     */
    public static function revisionRequestedMessage(SimaksiRegistration $reg, string $notes): string
    {
        $portalUrl = url('/?code='.$reg->registration_code);

        return <<<TEXT
*SAMSARA EXPEDITION — INSTRUKSI PERBAIKAN BERKAS*
// KODE TIKET: {$reg->registration_code} //

Halo *{$reg->leader_name}*,
Petugas verifikator kami telah meninjau berkas permohonan SIMAKSI Anda. Terdapat data/dokumen yang perlu diperbaiki:

[CATATAN PETUGAS VERIFIKATOR]
"{$notes}"

Segera lakukan perbaikan foto KTP / bukti transfer melalui portal resmi SAMSARA:
{$portalUrl}

Permohonan akan diproses kembali segera setelah Anda memperbarui berkas. Terima kasih atas kerja samanya.
TEXT;
    }

    /**
     * Dispatch WhatsApp automatically via configured external gateway API (Fonnte / Wablas)
     * Optional automated fallback to async webhook
     */
    public static function sendAutomated(string $phone, string $message): bool
    {
        $endpoint = config('services.wa_gateway.endpoint');
        $token = config('services.wa_gateway.token');

        if (! $endpoint || ! $token) {
            // Gateway not configured in .env, fallback to manual wa.me link
            return false;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $token,
            ])->timeout(5)->post($endpoint, [
                'target' => self::formatPhone($phone),
                'message' => $message,
            ]);

            return $response->successful();
        } catch (\Throwable $e) {
            Log::warning('WhatsApp Gateway notification failed: '.$e->getMessage());

            return false;
        }
    }
}
