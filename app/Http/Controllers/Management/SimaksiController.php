<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\SimaksiRegistration;
use App\Services\SimaksiService;
use App\Services\TicketPdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SimaksiController extends Controller
{
    public function __construct(
        protected SimaksiService $simaksiService
    ) {}

    public function index(Request $request)
    {
        $status = $request->query('status');
        $search = $request->query('search');

        $query = SimaksiRegistration::with(['members', 'reviewer']);

        if ($status) {
            if ($status === 'pending') {
                $query->whereIn('status', ['pending', 'submitted', 'needs_revision', 'approved']);
            } elseif ($status === 'paid') {
                $query->where('status', 'paid');
            } else {
                $query->where('status', $status);
            }
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('registration_code', 'like', "%{$search}%")
                    ->orWhere('leader_name', 'like', "%{$search}%")
                    ->orWhere('leader_phone', 'like', "%{$search}%")
                    ->orWhere('unique_code', $search);
            });
        }

        $registrations = $query->latest()->paginate(15)->withQueryString();

        $statusCounts = [
            'all' => SimaksiRegistration::count(),
            'pending' => SimaksiRegistration::whereIn('status', ['pending', 'submitted', 'needs_revision', 'approved'])->count(),
            'paid' => SimaksiRegistration::where('status', 'paid')->count(),
            'checked_in' => SimaksiRegistration::where('status', 'checked_in')->count(),
            'completed' => SimaksiRegistration::where('status', 'completed')->count(),
        ];

        return view('management.simaksi.index', compact('registrations', 'status', 'search', 'statusCounts'));
    }

    public function show(int $id)
    {
        $registration = SimaksiRegistration::with(['members', 'reviewer', 'checkInOfficer'])->findOrFail($id);

        $auditLogs = AuditLog::where('entity_type', 'SimaksiRegistration')
            ->where('entity_id', $registration->id)
            ->latest()
            ->get();

        return view('management.simaksi.show', compact('registration', 'auditLogs'));
    }

    public function approve(Request $request, int $id)
    {
        $registration = SimaksiRegistration::findOrFail($id);
        $notes = $request->input('notes');

        $this->simaksiService->approve($registration, Auth::id(), $notes);

        return redirect()->route('management.simaksi.show', $id)
            ->with('success', "Permohonan SIMAKSI {$registration->registration_code} berhasil DISETUJUI.");
    }

    public function reject(Request $request, int $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string|min:5',
        ]);

        $registration = SimaksiRegistration::findOrFail($id);
        $reason = $request->input('rejection_reason');

        $this->simaksiService->reject($registration, Auth::id(), $reason);

        return redirect()->route('management.simaksi.show', $id)
            ->with('success', "Permohonan SIMAKSI {$registration->registration_code} telah DITOLAK dengan alasan tersimpan.");
    }

    public function requestRevision(Request $request, int $id)
    {
        $request->validate([
            'revision_notes' => 'required|string|min:5',
        ]);

        $registration = SimaksiRegistration::findOrFail($id);
        $notes = $request->input('revision_notes');

        $this->simaksiService->requestRevision($registration, Auth::id(), $notes);

        return redirect()->route('management.simaksi.show', $id)
            ->with('success', "Instruksi perbaikan dikirim untuk SIMAKSI {$registration->registration_code}.");
    }

    public function markPaid(Request $request, int $id)
    {
        $registration = SimaksiRegistration::findOrFail($id);

        $method = $request->input('payment_method');
        if (empty($method)) {
            $method = $registration->payment_method ?? ($registration->payment_proof_path ? 'transfer' : 'cash_pos');
        }

        $this->simaksiService->markAsPaid($registration, $method, Auth::id());

        if ($request->input('redirect_to') === 'index') {
            return redirect()->route('management.simaksi.index')
                ->with('success', "Pembayaran SIMAKSI {$registration->registration_code} via ".strtoupper($method).' berhasil dikonfirmasi (PAID).');
        }

        return redirect()->route('management.simaksi.show', $id)
            ->with('success', "Pembayaran SIMAKSI {$registration->registration_code} via ".strtoupper($method).' berhasil diverifikasi lunas (PAID). Tiket siap digunakan check-in.');
    }

    /**
     * Protected KTP Document Streamer (UU PDP Compliance)
     * Restricts KTP access strictly to authenticated management staff
     */
    public function ktp(int $id)
    {
        $registration = SimaksiRegistration::findOrFail($id);
        if (! $registration->leader_ktp_path) {
            abort(404, 'Dokumen KTP tidak ditemukan atau telah dimusnahkan secara otomatis demi privasi (UU PDP).');
        }

        $disk = Storage::disk('local')->exists($registration->leader_ktp_path)
            ? 'local'
            : (Storage::disk('public')->exists($registration->leader_ktp_path) ? 'public' : null);

        if (! $disk) {
            abort(404, 'Berkas fisik KTP tidak ditemukan pada penyimpanan server.');
        }

        return Storage::disk($disk)->response($registration->leader_ktp_path);
    }

    /**
     * Download Official SIMAKSI PDF for Management Staff
     */
    public function downloadTicketPdf(int $id, TicketPdfService $pdfService)
    {
        $registration = SimaksiRegistration::with(['members', 'reviewer', 'checkInOfficer'])->findOrFail($id);
        $pdf = $pdfService->generate($registration);

        return $pdf->download('E-Tiket_SIMAKSI_'.$registration->registration_code.'.pdf');
    }
}
