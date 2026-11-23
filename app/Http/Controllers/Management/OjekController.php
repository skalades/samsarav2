<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\OjekDriver;
use App\Models\OjekSettlement;
use App\Models\OjekTrip;
use App\Services\AuditService;
use App\Services\OjekService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OjekController extends Controller
{
    public function __construct(
        protected OjekService $ojekService
    ) {}

    /**
     * Live Dispatch & Antrean Ojek Monitor.
     */
    public function index()
    {
        $standbyDrivers = OjekDriver::where('status', 'standby')
            ->where('is_active', true)
            ->orderBy('updated_at', 'asc') // FIFO Queue
            ->get();

        $onTripDrivers = OjekDriver::where('status', 'on_trip')
            ->where('is_active', true)
            ->latest('updated_at')
            ->get();

        $offDutyDrivers = OjekDriver::whereIn('status', ['off_duty', 'suspended'])
            ->get();

        // Trips needing assignment
        $pendingTrips = OjekTrip::with(['simaksi', 'posOrder'])
            ->where('trip_status', 'voucher_issued')
            ->latest()
            ->get();

        // Ongoing trips
        $activeTrips = OjekTrip::with(['driver', 'simaksi', 'posOrder'])
            ->whereIn('trip_status', ['assigned', 'on_trip'])
            ->latest('dispatched_at')
            ->get();

        // Completed trips today
        $todayCompletedTrips = OjekTrip::with('driver')
            ->where('trip_status', 'completed')
            ->whereDate('completed_at', Carbon::today())
            ->latest('completed_at')
            ->take(15)
            ->get();

        // Metrics
        $todayTripCount = OjekTrip::where('trip_status', 'completed')
            ->whereDate('completed_at', Carbon::today())
            ->count();

        $todayGrossFare = (float) OjekTrip::where('trip_status', 'completed')
            ->whereDate('completed_at', Carbon::today())
            ->sum('fare_amount');

        $todayDriverEarnings = (float) OjekTrip::where('trip_status', 'completed')
            ->whereDate('completed_at', Carbon::today())
            ->sum('driver_share');

        $todayBasecampShare = (float) OjekTrip::where('trip_status', 'completed')
            ->whereDate('completed_at', Carbon::today())
            ->sum('basecamp_share');

        return view('management.ojek.index', compact(
            'standbyDrivers',
            'onTripDrivers',
            'offDutyDrivers',
            'pendingTrips',
            'activeTrips',
            'todayCompletedTrips',
            'todayTripCount',
            'todayGrossFare',
            'todayDriverEarnings',
            'todayBasecampShare'
        ));
    }

    /**
     * Master Data Pengemudi Ojek Resmi.
     */
    public function drivers()
    {
        $drivers = OjekDriver::withCount('trips')
            ->orderByRaw('CAST(SUBSTR(registration_number, 5) AS INTEGER) ASC')
            ->get();

        return view('management.ojek.drivers', compact('drivers'));
    }

    /**
     * Add new official ojek driver.
     */
    public function storeDriver(Request $request)
    {
        $request->validate([
            'registration_number' => 'required|string|unique:ojek_drivers,registration_number',
            'name' => 'required|string|max:100',
            'plate_number' => 'required|string|max:20',
            'phone' => 'nullable|string|max:25',
            'status' => 'required|in:standby,off_duty,suspended',
        ]);

        $driver = OjekDriver::create([
            'registration_number' => strtoupper(trim($request->input('registration_number'))),
            'name' => trim($request->input('name')),
            'plate_number' => strtoupper(trim($request->input('plate_number'))),
            'phone' => $request->input('phone'),
            'status' => $request->input('status', 'standby'),
            'is_active' => true,
            'notes' => $request->input('notes'),
        ]);

        AuditService::log(
            action: 'OJEK_DRIVER_CREATED',
            entityType: 'OjekDriver',
            entityId: $driver->id,
            description: "Registrasi pengemudi ojek resmi baru: {$driver->name} (#{$driver->registration_number}, Plat: {$driver->plate_number}).",
            payload: $driver->toArray()
        );

        return redirect()->route('management.ojek.drivers')
            ->with('success', "Driver resmi {$driver->name} (#{$driver->registration_number}) berhasil didaftarkan ke pangkalan.");
    }

    /**
     * Update driver data.
     */
    public function updateDriver(Request $request, int $id)
    {
        $driver = OjekDriver::findOrFail($id);

        $request->validate([
            'registration_number' => "required|string|unique:ojek_drivers,registration_number,{$driver->id}",
            'name' => 'required|string|max:100',
            'plate_number' => 'required|string|max:20',
            'phone' => 'nullable|string|max:25',
            'status' => 'required|in:standby,on_trip,off_duty,suspended',
        ]);

        $driver->update([
            'registration_number' => strtoupper(trim($request->input('registration_number'))),
            'name' => trim($request->input('name')),
            'plate_number' => strtoupper(trim($request->input('plate_number'))),
            'phone' => $request->input('phone'),
            'status' => $request->input('status'),
            'notes' => $request->input('notes'),
        ]);

        AuditService::log(
            action: 'OJEK_DRIVER_UPDATED',
            entityType: 'OjekDriver',
            entityId: $driver->id,
            description: "Pembaruan data driver {$driver->name} (#{$driver->registration_number}).",
            payload: $driver->toArray()
        );

        return redirect()->route('management.ojek.drivers')
            ->with('success', "Data pengemudi {$driver->name} berhasil diperbarui.");
    }

    /**
     * Quick toggle status driver (Standby <-> Off Duty).
     */
    public function toggleDriverStatus(int $id)
    {
        $driver = OjekDriver::findOrFail($id);
        $newStatus = ($driver->status === 'standby') ? 'off_duty' : 'standby';

        $driver->update(['status' => $newStatus]);

        return back()->with('success', "Status driver {$driver->name} diubah menjadi ".strtoupper($newStatus));
    }

    /**
     * Assign trip to driver.
     */
    public function assignDriver(Request $request, int $tripId)
    {
        $request->validate([
            'driver_id' => 'required|exists:ojek_drivers,id',
        ]);

        $trip = OjekTrip::findOrFail($tripId);
        $driverId = (int) $request->input('driver_id');

        $this->ojekService->assignDriver($trip, $driverId, Auth::id() ?? 1);

        // Jika opsi Tugaskan & Cetak dipilih
        if ($request->input('action') === 'assign_and_print' || $request->has('print')) {
            return redirect()->route('management.ojek.print_voucher', $trip->id);
        }

        return redirect()->route('management.ojek.index')
            ->with('success', "Trip {$trip->trip_code} berhasil ditugaskan ke {$trip->fresh()->driver->name}.");
    }

    /**
     * Complete an ojek trip.
     */
    public function completeTrip(int $tripId)
    {
        $trip = OjekTrip::findOrFail($tripId);
        $this->ojekService->completeTrip($trip, Auth::id());

        return redirect()->route('management.ojek.index')
            ->with('success', "Trip {$trip->trip_code} dinyatakan SELESAI. Driver telah kembali ke antrean pangkalan.");
    }

    /**
     * Settlements / Rekonsiliasi & Payout Page.
     */
    public function settlements()
    {
        // Drivers with pending unsettled completed trips
        $drivers = OjekDriver::with(['trips' => function ($q) {
            $q->where('trip_status', 'completed')
                ->where('settlement_status', 'unsettled');
        }])->get();

        $driversWithPending = $drivers->filter(fn ($d) => $d->trips->isNotEmpty());

        $recentSettlements = OjekSettlement::with(['driver', 'cashier'])
            ->latest('settled_at')
            ->take(20)
            ->get();

        $totalUnsettledAmount = $driversWithPending->sum(fn ($d) => $d->trips->sum('driver_share'));

        return view('management.ojek.settlements', compact('driversWithPending', 'recentSettlements', 'totalUnsettledAmount'));
    }

    /**
     * Process driver payout.
     */
    public function processPayout(Request $request, int $driverId)
    {
        $request->validate([
            'payment_method' => 'required|in:cash,transfer',
            'notes' => 'nullable|string|max:255',
        ]);

        try {
            $settlement = $this->ojekService->settleDriverPayout(
                driverId: $driverId,
                cashierId: Auth::id(),
                paymentMethod: $request->input('payment_method'),
                notes: $request->input('notes')
            );

            return redirect()->route('management.ojek.print_settlement', $settlement->id);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Print settlement payout slip.
     */
    public function printSettlement(int $id)
    {
        $settlement = OjekSettlement::with(['driver', 'cashier', 'trips'])->findOrFail($id);

        return view('management.ojek.print_settlement', compact('settlement'));
    }

    /**
     * Print single trip voucher.
     */
    public function printTripVoucher(Request $request, int $id)
    {
        $trip = OjekTrip::with(['driver', 'simaksi'])->findOrFail($id);

        // Jika driver belum tersimpan di database tapi disertakan saat cetak (?driver_id=...)
        if (! $trip->driver && $request->filled('driver_id')) {
            $driverId = (int) $request->input('driver_id');
            $driver = OjekDriver::find($driverId);

            if ($driver) {
                // Assign driver ke trip jika belum ditugaskan
                $this->ojekService->assignDriver($trip, $driver->id, Auth::id() ?? 1);
                $trip->refresh()->load('driver');
            }
        }

        return view('management.ojek.print_trip_voucher', compact('trip'));
    }
}
