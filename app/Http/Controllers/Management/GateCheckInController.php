<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\OjekDriver;
use App\Models\SimaksiRegistration;
use App\Services\CheckInService;
use App\Services\OjekService;
use App\Services\TicketPdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GateCheckInController extends Controller
{
    public function __construct(
        protected CheckInService $checkInService
    ) {}

    public function index(Request $request)
    {
        $code = $request->input('code');
        $selectedRegistration = null;

        if ($code) {
            $selectedRegistration = SimaksiRegistration::with(['members', 'reviewer'])
                ->where('registration_code', strtoupper(trim($code)))
                ->first();
        }

        // Active climbers currently on the trail
        $activeOnMountain = SimaksiRegistration::with('members')
            ->where('status', 'checked_in')
            ->latest('checked_in_at')
            ->get();

        // Ready for check-in today
        $readyForCheckIn = SimaksiRegistration::with('members')
            ->whereIn('status', ['paid', 'approved'])
            ->latest()
            ->take(10)
            ->get();

        // Standby Ojek Drivers Queue (FIFO)
        $standbyDrivers = OjekDriver::where('status', 'standby')
            ->where('is_active', true)
            ->orderBy('updated_at', 'asc')
            ->get();

        return view('management.gate.index', compact(
            'code',
            'selectedRegistration',
            'activeOnMountain',
            'readyForCheckIn',
            'standbyDrivers'
        ));
    }

    public function processCheckIn(Request $request, int $id)
    {
        $request->validate([
            'trash_bag_code' => 'nullable|string',
            'checked_members' => 'required|array|min:1',
            'ojek_driver_ids' => 'nullable|array',
        ]);

        $registration = SimaksiRegistration::findOrFail($id);
        $assignedDriverIds = array_values(array_filter(array_map('intval', (array) $request->input('ojek_driver_ids', []))));

        try {
            $this->checkInService->checkIn(
                registration: $registration,
                officerId: Auth::id(),
                trashBagCode: $request->input('trash_bag_code'),
                checkedMemberIds: array_map('intval', $request->input('checked_members')),
                assignedDriverIds: $assignedDriverIds
            );
        } catch (\RuntimeException $e) {
            return redirect()->route('management.gate.index', ['code' => $registration->registration_code])
                ->with('error', $e->getMessage());
        }

        $msg = "CHECK-IN BERHASIL! Rombongan {$registration->leader_name} ({$registration->registration_code}) diizinkan masuk trek.";
        if ($registration->ojek_count > 0 && count($assignedDriverIds) > 0) {
            $msg .= ' '.count($assignedDriverIds).' Driver ojek pangkalan resmi berhasil ditugaskan.';
        }

        $redirect = redirect()->route('management.gate.index', ['code' => $registration->registration_code])
            ->with('success', $msg)
            ->with('checkin_completed_id', $registration->id);

        if ($registration->ojek_count > 0) {
            $redirect->with('checkin_has_ojek', true)
                ->with('checkin_registration_id', $registration->id);
        }

        return $redirect;
    }

    public function processCheckOut(Request $request, int $id)
    {
        $registration = SimaksiRegistration::findOrFail($id);
        $trashVerified = $request->boolean('trash_verified');
        $notes = $request->input('notes');

        try {
            $this->checkInService->checkOut(
                registration: $registration,
                officerId: Auth::id(),
                trashVerified: $trashVerified,
                notes: $notes
            );
        } catch (\RuntimeException $e) {
            return redirect()->route('management.gate.index', ['code' => $registration->registration_code])
                ->with('error', $e->getMessage());
        }

        if ($registration->trash_deposit_amount > 0) {
            $formattedDeposit = 'Rp '.number_format($registration->trash_deposit_amount, 0, ',', '.');
            $msg = $trashVerified
                ? "CHECK-OUT SELESAI: Sampah telah diverifikasi utuh. Uang jaminan sampah {$formattedDeposit} DIKEMBALIKAN kepada ketua rombongan."
                : "CHECK-OUT SELESAI DENGAN CATATAN: Sampah tidak dibawa turun. Uang jaminan sampah {$formattedDeposit} HANGUS diserahkan untuk operasional pembersihan jalur.";
        } else {
            $msg = "CHECK-OUT SELESAI: Rombongan {$registration->leader_name} telah resmi turun dan selesai (Bebas deposit sampah).";
        }

        return redirect()->route('management.gate.index', ['code' => $registration->registration_code])
            ->with('success', $msg)
            ->with('checkout_completed_id', $registration->id);
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
     * Download or Print Official SIMAKSI PDF from Gate
     */
    public function downloadTicketPdf(int $id, TicketPdfService $pdfService)
    {
        $registration = SimaksiRegistration::with(['members', 'reviewer', 'checkInOfficer'])->findOrFail($id);
        $pdf = $pdfService->generate($registration);

        return $pdf->download('E-Tiket_SIMAKSI_'.$registration->registration_code.'.pdf');
    }
}
