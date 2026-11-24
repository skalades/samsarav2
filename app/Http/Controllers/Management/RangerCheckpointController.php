<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\SimaksiCheckpoint;
use App\Models\SimaksiRegistration;
use App\Services\CheckpointTrackingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RangerCheckpointController extends Controller
{
    public function __construct(
        protected CheckpointTrackingService $trackingService
    ) {}

    /**
     * Ranger mobile-first scanner interface for Pos 1, Pos 2, Pos 3.
     */
    public function index(Request $request)
    {
        $activePos = strtoupper($request->query('pos', $request->session()->get('ranger_active_pos', 'POS_1')));
        $validPositions = ['POS_1', 'POS_2', 'POS_3', 'PUNCAK'];
        if (! in_array($activePos, $validPositions)) {
            $activePos = 'POS_1';
        }
        $request->session()->put('ranger_active_pos', $activePos);

        $code = trim((string) $request->input('code'));
        $selectedRegistration = null;
        $suggestedDirection = 'ascending';

        if ($code) {
            $cleanCode = strtoupper($code);
            $selectedRegistration = SimaksiRegistration::with(['members', 'checkpoints'])
                ->where('registration_code', $cleanCode)
                ->orWhere('registration_code', 'LIKE', '%-'.ltrim($cleanCode, '#').'%')
                ->first();

            if ($selectedRegistration) {
                $suggestedDirection = $this->trackingService->suggestDirection($selectedRegistration, $activePos);
            }
        }

        // Recent scan logs at this specific pos today
        $todayLogs = SimaksiCheckpoint::with(['registration', 'ranger'])
            ->where('pos_code', $activePos)
            ->whereDate('scanned_at', Carbon::today())
            ->orderBy('scanned_at', 'desc')
            ->take(15)
            ->get();

        $trailConfig = AppSetting::getTrailConfig();

        // Rombongan aktif di jalur: ketuk untuk mencatat tanpa scan
        $activeGroups = SimaksiRegistration::where('status', 'checked_in')
            ->orderByRaw('CASE WHEN last_seen_pos = ? THEN 1 ELSE 0 END', [$activePos])
            ->orderBy('last_seen_at', 'desc')
            ->take(40)
            ->get();

        return view('management.ranger.scanner', compact(
            'activePos',
            'code',
            'selectedRegistration',
            'suggestedDirection',
            'todayLogs',
            'trailConfig',
            'activeGroups'
        ));
    }

    /**
     * Process checkpoint scan submission from ranger.
     */
    public function store(Request $request)
    {
        $request->validate([
            'registration_id' => 'required|exists:simaksi_registrations,id',
            'pos_code' => 'required|string|in:POS_1,POS_2,POS_3,PUNCAK,BASECAMP',
            'direction' => 'required|string|in:ascending,descending,camping',
            'entry_source' => 'nullable|string|in:scanner,radio_ht,manual',
            'notes' => 'nullable|string|max:255',
        ]);

        $registration = SimaksiRegistration::findOrFail($request->input('registration_id'));
        $posCode = strtoupper($request->input('pos_code'));
        $direction = strtolower($request->input('direction'));
        $source = $request->input('entry_source', 'scanner');
        $notes = $request->input('notes');

        try {
            $this->trackingService->recordCheckpoint(
                registration: $registration,
                posCode: $posCode,
                direction: $direction,
                rangerId: Auth::id() ?: 1,
                source: $source,
                notes: $notes
            );
        } catch (\InvalidArgumentException $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return redirect()->route('management.ranger.index', ['pos' => $posCode, 'code' => $registration->registration_code])
                ->with('error', $e->getMessage());
        }

        $dirLabel = match ($direction) {
            'ascending' => 'Naik [↑]',
            'descending' => 'Turun [↓]',
            'camping' => 'Buka Tenda [CAMP]',
            default => strtoupper($direction),
        };

        $msg = "BERHASIL DICATAT: Rombongan {$registration->leader_name} ({$registration->registration_code}) di {$posCode} arah {$dirLabel}.";

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $msg]);
        }

        return redirect()->route('management.ranger.index', ['pos' => $posCode])
            ->with('success', $msg);
    }
}
