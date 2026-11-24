<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\SimaksiInspectionItem;
use App\Models\SimaksiRegistration;
use App\Services\CheckpointTrackingService;
use App\Services\GateFlowService;
use App\Services\OjekService;
use App\Services\TicketPdfService;
use App\Support\InspectionTemplate;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Gatekeeper: arahkan pendaki, periksa alat/logistik/foto tim saat naik,
 * hitung ulang logistik saat turun, lalu teruskan ke kasir.
 * Cetak dokumen (surat jalan, voucher ojek, izin parkir) tetap di sini tetapi khusus kasir.
 */
class GateCheckInController extends Controller
{
    public function __construct(
        protected GateFlowService $gateFlow,
        protected CheckpointTrackingService $trackingService
    ) {}

    public function index(Request $request)
    {
        $code = strtoupper(trim((string) $request->input('code')));

        // Scan / ketik kode → langsung ke langkah yang sesuai
        if ($code !== '') {
            $registration = $this->findByCode($code);

            if (! $registration) {
                return redirect()->route('management.gate.index')
                    ->with('error', "Kode [{$code}] tidak ditemukan. Periksa kembali tiket pendaki.");
            }

            return match ($registration->status) {
                'paid' => redirect()->route('management.gate.ascent', $registration->id),
                'checked_in' => redirect()->route('management.gate.descent', $registration->id),
                default => redirect()->route('management.gate.index')
                    ->with('error', "{$registration->registration_code} · {$registration->leader_name}: {$registration->statusLabel()}."),
            };
        }

        $today = Carbon::today();

        $readyToAscend = SimaksiRegistration::with('members')
            ->where('status', 'paid')
            ->orderByRaw('CASE WHEN DATE(hiking_date) = ? THEN 0 ELSE 1 END', [$today->toDateString()])
            ->orderBy('hiking_date')
            ->take(30)
            ->get();

        $waitingDeskUp = SimaksiRegistration::where('status', 'gate_cleared')->orderBy('gate_up_at')->get();
        $waitingDeskDown = SimaksiRegistration::where('status', 'returned')->orderBy('gate_down_at')->get();

        $radarData = $this->trackingService->getActiveMountainRadar();
        $onTrail = collect($radarData['by_pos'])->flatten(1)
            ->sortByDesc(fn ($row) => [$row['is_overdue'] ? 1 : 0, optional($row['registration']->last_seen_at)->timestamp])
            ->values();

        $stats = [
            'ascend_today' => SimaksiRegistration::where('status', 'paid')->whereDate('hiking_date', $today)->count(),
            'cleared_today' => SimaksiRegistration::whereDate('gate_up_at', $today)->count(),
            'returned_today' => SimaksiRegistration::whereDate('gate_down_at', $today)->count(),
            'on_trail' => $radarData['counts']['total_active_climbers'],
            'overdue' => $radarData['counts']['overdue_count'],
        ];

        return view('management.gate.index', compact(
            'readyToAscend',
            'waitingDeskUp',
            'waitingDeskDown',
            'onTrail',
            'radarData',
            'stats'
        ));
    }

    public function ascentForm(int $id)
    {
        $registration = SimaksiRegistration::with('members')->findOrFail($id);

        if ($registration->status !== 'paid') {
            return redirect()->route('management.gate.index')
                ->with('error', "{$registration->registration_code}: {$registration->statusLabel()}. Pemeriksaan naik hanya untuk rombongan yang sudah lunas.");
        }

        return view('management.gate.ascent', [
            'registration' => $registration,
            'template' => InspectionTemplate::get(),
        ]);
    }

    public function submitAscent(Request $request, int $id)
    {
        $request->validate([
            'present_members' => 'required|array|min:1',
            'present_members.*' => 'integer',
            'team_photo' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:15360',
            'photo_verified' => 'nullable|boolean',
            'gear' => 'nullable|array',
            'gear.*.name' => 'nullable|string|max:100',
            'gear.*.checked' => 'nullable|boolean',
            'logistics' => 'nullable|array',
            'logistics.*.name' => 'nullable|string|max:100',
            'logistics.*.unit' => 'nullable|string|max:20',
            'logistics.*.qty' => 'nullable|integer|min:0|max:9999',
            'notes' => 'nullable|string|max:500',
        ], [
            'present_members.required' => 'Tandai minimal satu anggota yang hadir.',
        ]);

        $registration = SimaksiRegistration::findOrFail($id);

        try {
            $this->gateFlow->clearAscent(
                registration: $registration,
                officerId: Auth::id(),
                presentMemberIds: array_map('intval', $request->input('present_members', [])),
                photoVerified: $request->boolean('photo_verified'),
                photo: $request->file('team_photo'),
                gear: array_values((array) $request->input('gear', [])),
                logistics: array_values((array) $request->input('logistics', [])),
                notes: $request->input('notes'),
            );
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('management.gate.index')
            ->with('success', "{$registration->registration_code} · {$registration->leader_name} LOLOS GATE. Arahkan rombongan ke kasir untuk mengambil Surat Jalan".($registration->ojek_count > 0 ? ' & Voucher Ojek.' : '.'));
    }

    public function descentForm(int $id)
    {
        $registration = SimaksiRegistration::with(['members', 'inspectionItems', 'checkpoints'])->findOrFail($id);

        if ($registration->status !== 'checked_in') {
            return redirect()->route('management.gate.index')
                ->with('error', "{$registration->registration_code}: {$registration->statusLabel()}. Pemeriksaan turun hanya untuk rombongan yang sedang mendaki.");
        }

        return view('management.gate.descent', [
            'registration' => $registration,
            'gearItems' => $registration->inspectionItems->where('type', SimaksiInspectionItem::TYPE_GEAR)->values(),
            'logisticItems' => $registration->inspectionItems->where('type', SimaksiInspectionItem::TYPE_LOGISTIC)->values(),
        ]);
    }

    public function submitDescent(Request $request, int $id)
    {
        $request->validate([
            'qty_down' => 'nullable|array',
            'qty_down.*' => 'nullable|integer|min:0|max:9999',
            'gear_down' => 'nullable|array',
            'notes' => 'nullable|string|max:500',
        ]);

        $registration = SimaksiRegistration::findOrFail($id);

        try {
            $result = $this->gateFlow->recordDescent(
                registration: $registration,
                officerId: Auth::id(),
                qtyDown: array_map('intval', (array) $request->input('qty_down', [])),
                gearDown: array_map('boolval', (array) $request->input('gear_down', [])),
                notes: $request->input('notes'),
            );
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        $msg = $result->gate_down_clear
            ? "{$registration->registration_code} CLEAR: logistik & alat lengkap. Arahkan ke kasir untuk penyelesaian & izin keluar parkir."
            : "{$registration->registration_code} tercatat turun DENGAN SELISIH. Catatan diteruskan ke kasir.";

        return redirect()->route('management.gate.index')->with($result->gate_down_clear ? 'success' : 'error', $msg);
    }

    /**
     * Quick Radio HT Dispatch Entry when Ranger's phone in the mountain dies.
     */
    public function recordHtDispatch(Request $request)
    {
        $request->validate([
            'registration_code' => 'required|string',
            'pos_code' => 'required|string|in:POS_1,POS_2,POS_3,PUNCAK,BASECAMP',
            'direction' => 'required|string|in:ascending,descending,camping',
            'notes' => 'nullable|string|max:255',
        ]);

        $code = strtoupper(trim($request->input('registration_code')));
        $registration = $this->findByCode($code);

        if (! $registration) {
            return redirect()->route('management.gate.index')
                ->with('error', "Kode registrasi [{$code}] tidak ditemukan dalam manifest.");
        }

        try {
            $notes = $request->input('notes') ?: 'Input cepat operator basecamp via radio HT';
            $this->trackingService->recordCheckpoint(
                registration: $registration,
                posCode: $request->input('pos_code'),
                direction: $request->input('direction'),
                rangerId: Auth::id() ?: 1,
                source: 'radio_ht',
                notes: $notes
            );
        } catch (\Exception $e) {
            return redirect()->route('management.gate.index')
                ->with('error', $e->getMessage());
        }

        return redirect()->route('management.gate.index')
            ->with('success', "DISPATCH HT: {$registration->leader_name} ({$registration->registration_code}) dicatat di {$request->input('pos_code')} arah {$request->input('direction')}.");
    }

    /**
     * Print official ojek shuttle voucher(s) for the registered group.
     */
    public function printOjekVoucher(int $id)
    {
        $registration = SimaksiRegistration::with(['ojekTrips.driver', 'members'])->findOrFail($id);

        if ($registration->ojek_count > 0 && $registration->ojekTrips->isEmpty()) {
            app(OjekService::class)->ensureTripsForRegistration($registration, Auth::id());
            $registration->load('ojekTrips.driver');
        }

        $trips = $registration->ojekTrips;

        return view('management.gate.print_ojek_voucher', compact('registration', 'trips'));
    }

    /**
     * Print official parking clearance gate pass when climber completes check-out.
     */
    public function printParkingPass(int $id)
    {
        $registration = SimaksiRegistration::findOrFail($id);

        return view('management.gate.print_parking_pass', compact('registration'));
    }

    /**
     * Print official Climber Pass (Surat Jalan Saku Thermal) for checkpoints.
     */
    public function printClimberPass(int $id)
    {
        $registration = SimaksiRegistration::with(['members', 'checkInOfficer'])->findOrFail($id);

        return view('management.gate.print_climber_pass', compact('registration'));
    }

    /**
     * Download or Print Official SIMAKSI PDF
     */
    public function downloadTicketPdf(int $id, TicketPdfService $pdfService)
    {
        $registration = SimaksiRegistration::with(['members', 'reviewer', 'checkInOfficer'])->findOrFail($id);
        $pdf = $pdfService->generate($registration);

        return $pdf->download('E-Tiket_SIMAKSI_'.$registration->registration_code.'.pdf');
    }

    protected function findByCode(string $code): ?SimaksiRegistration
    {
        $code = strtoupper(trim($code));

        // If a full URL or ticket path was scanned, extract the SMK code
        if (preg_match('/SMK-[A-Z0-9-]+/i', $code, $matches)) {
            $code = strtoupper($matches[0]);
        }

        $clean = trim($code, '*# ');

        return SimaksiRegistration::where('registration_code', $clean)
            ->orWhere('registration_code', 'LIKE', '%-'.ltrim($clean, '#').'%')
            ->orWhere('registration_code', 'LIKE', '%'.$clean.'%')
            ->first();
    }
}
