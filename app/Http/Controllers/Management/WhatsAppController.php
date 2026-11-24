<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Jobs\SendWhatsAppMessage;
use App\Models\SimaksiRegistration;
use App\Models\WhatsappMessage;
use App\Services\WhatsApp\WhatsAppManager;
use App\Services\WhatsApp\WhatsAppNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Status WhatsApp Gateway, log pesan, uji kirim & kirim ulang.
 */
class WhatsAppController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');

        $messages = WhatsappMessage::with(['registration', 'triggeredBy'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $counts = WhatsappMessage::selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');

        return view('management.settings.whatsapp', [
            'gateway' => app(WhatsAppManager::class)->status(),
            'drivers' => WhatsAppManager::DRIVERS,
            'messages' => $messages,
            'counts' => $counts,
            'filter' => $status,
        ]);
    }

    public function sendTest(Request $request, WhatsAppNotifier $notifier)
    {
        $request->validate(['phone' => 'required|string|min:9|max:20']);

        $record = $notifier->queue(
            reg: null,
            event: 'test',
            message: "Uji koneksi WhatsApp Gateway SAMSARA OPS berhasil.\nDikirim oleh: ".Auth::user()->name.' · '.now()->format('d M Y H:i'),
            triggeredBy: Auth::id(),
            phone: $request->input('phone'),
        );

        if (! $record) {
            return back()->with('error', 'Nomor WhatsApp tidak valid.');
        }

        return back()->with('success', 'Pesan uji dimasukkan ke antrean. Status terakhir: '.$record->fresh()->statusLabel().'.');
    }

    public function retry(int $messageId)
    {
        $record = WhatsappMessage::findOrFail($messageId);

        if ($record->status === 'sent') {
            return back()->with('error', 'Pesan ini sudah terkirim.');
        }

        $record->update(['status' => 'queued']);
        SendWhatsAppMessage::dispatch($record->id);

        return back()->with('success', 'Pesan dikirim ulang ke antrean. Status: '.$record->fresh()->statusLabel().'.');
    }

    /**
     * Kasir / superadmin: kirim ulang info pendakian & e-SIMAKSI ke ketua rombongan.
     */
    public function resendForRegistration(int $id, WhatsAppNotifier $notifier)
    {
        $registration = SimaksiRegistration::findOrFail($id);

        if (! in_array($registration->status, SimaksiRegistration::POST_PAYMENT_STATUSES, true)) {
            return back()->with('error', 'Info pendakian hanya dikirim untuk rombongan yang sudah lunas.');
        }

        $record = $notifier->resend($registration, Auth::id());

        if (! $record) {
            return back()->with('error', 'Nomor WhatsApp ketua rombongan tidak valid.');
        }

        return back()->with('success', 'Info pendakian & e-SIMAKSI dikirim ulang ke '.$record->phone.'. Status: '.$record->fresh()->statusLabel().'.');
    }
}
