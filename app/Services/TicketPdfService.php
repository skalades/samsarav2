<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\SimaksiRegistration;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdfWrapper;
use Carbon\Carbon;

class TicketPdfService
{
    /**
     * Generate official SIMAKSI PDF ticket.
     */
    public function generate(SimaksiRegistration $registration): DomPdfWrapper
    {
        $registration->loadMissing(['members', 'reviewer', 'checkInOfficer']);

        $profile = AppSetting::getExpeditionProfile();
        $tariffs = AppSetting::getTariffs();

        // Status labels and styling
        $statusLabels = [
            'pending' => 'MENUNGGU VERIFIKASI',
            'needs_revision' => 'PERLU PERBAIKAN',
            'approved' => 'DISETUJUI (SIAP CHECK-IN)',
            'paid' => 'LUNAS (SIAP CHECK-IN)',
            'gate_cleared' => 'LOLOS PEMERIKSAAN GATE (AMBIL SURAT JALAN DI KASIR)',
            'checked_in' => 'SEDANG MENDAKI (AKTIF DI JALUR)',
            'returned' => 'SUDAH TURUN (PENYELESAIAN DI KASIR)',
            'completed' => 'PENDAKIAN SELESAI',
            'rejected' => 'DITOLAK',
        ];

        $statusColor = match ($registration->status) {
            'approved', 'paid' => '#059669', // Emerald
            'gate_cleared', 'checked_in', 'returned' => '#2563eb', // Blue
            'completed' => '#4b5563', // Gray
            'rejected' => '#dc2626', // Red
            default => '#d97706', // Amber
        };

        $qrVerificationUrl = $registration->publicTicketUrl();

        $data = [
            'registration' => $registration,
            'members' => $registration->members,
            'profile' => $profile,
            'tariffs' => $tariffs,
            'statusLabel' => $statusLabels[$registration->status] ?? strtoupper($registration->status),
            'statusColor' => $statusColor,
            'printedAt' => Carbon::now()->translatedFormat('d F Y, H:i').' WIB',
            'qrVerificationUrl' => $qrVerificationUrl,
        ];

        $pdf = Pdf::loadView('tickets.simaksi_pdf', $data);
        $pdf->setPaper('a4', 'portrait');
        $pdf->setOption([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'sans-serif',
        ]);

        return $pdf;
    }
}
