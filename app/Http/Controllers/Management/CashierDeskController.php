<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\OjekDriver;
use App\Models\RentalRecord;
use App\Models\SimaksiInspectionItem;
use App\Models\SimaksiRegistration;
use App\Services\GateFlowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Meja Kasir: menerima rombongan yang sudah diperiksa Gatekeeper.
 *  - Tab "Naik"  : lolos gate → cetak Surat Jalan & Voucher Ojek → berangkatkan
 *  - Tab "Turun" : selesai diperiksa turun → kembalikan sewa alat → selesaikan & Izin Keluar Parkir
 */
class CashierDeskController extends Controller
{
    public function __construct(protected GateFlowService $gateFlow) {}

    public function index(Request $request)
    {
        $tab = $request->query('tab') === 'turun' ? 'turun' : 'naik';

        $ascending = SimaksiRegistration::with(['members', 'gateUpOfficer'])
            ->where('status', 'gate_cleared')
            ->orderBy('gate_up_at')
            ->get();

        $descending = SimaksiRegistration::with(['gateDownOfficer'])
            ->where('status', 'returned')
            ->orderBy('gate_down_at')
            ->get();

        return view('management.desk.index', compact('tab', 'ascending', 'descending'));
    }

    public function show(int $id)
    {
        $registration = SimaksiRegistration::with(['members', 'inspectionItems', 'gateUpOfficer', 'gateDownOfficer', 'ojekTrips.driver', 'posOrders'])
            ->findOrFail($id);

        if (! in_array($registration->status, ['gate_cleared', 'returned', 'checked_in', 'completed'], true)) {
            return redirect()->route('management.simaksi.show', $registration->id);
        }

        $standbyDrivers = OjekDriver::where('status', 'standby')
            ->where('is_active', true)
            ->orderBy('updated_at')
            ->get();

        $rentals = RentalRecord::with('item')
            ->where('status', 'rented')
            ->where('simaksi_registration_id', $registration->id)
            ->get();

        return view('management.desk.show', [
            'registration' => $registration,
            'standbyDrivers' => $standbyDrivers,
            'rentals' => $rentals,
            'gearItems' => $registration->inspectionItems->where('type', SimaksiInspectionItem::TYPE_GEAR)->values(),
            'logisticItems' => $registration->inspectionItems->where('type', SimaksiInspectionItem::TYPE_LOGISTIC)->values(),
        ]);
    }

    public function release(Request $request, int $id)
    {
        $request->validate([
            'ojek_driver_ids' => 'nullable|array',
            'ojek_driver_ids.*' => 'nullable|integer',
        ]);

        $registration = SimaksiRegistration::findOrFail($id);
        $driverIds = array_values(array_filter(array_map('intval', (array) $request->input('ojek_driver_ids', []))));

        try {
            $this->gateFlow->release($registration, Auth::id(), $driverIds);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('management.desk.show', $registration->id)
            ->with('success', "{$registration->registration_code} resmi BERANGKAT. Cetak Surat Jalan".($registration->ojek_count > 0 ? ' & Voucher Ojek' : '').' lalu serahkan ke ketua rombongan.')
            ->with('print_ascent', true);
    }

    public function finalize(Request $request, int $id)
    {
        $request->validate(['notes' => 'nullable|string|max:500']);

        $registration = SimaksiRegistration::findOrFail($id);

        try {
            $this->gateFlow->finalize($registration, Auth::id(), $request->input('notes'));
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('management.desk.show', $registration->id)
            ->with('success', "{$registration->registration_code} SELESAI. Cetak Izin Keluar Parkir untuk rombongan.")
            ->with('print_descent', true);
    }
}
