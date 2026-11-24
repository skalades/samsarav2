<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\ClimberMember;
use App\Models\DailyQuota;
use App\Models\InventoryItem;
use App\Models\SimaksiRegistration;
use App\Models\TrailCondition;
use App\Services\AuditService;
use App\Services\BookingGuardService;
use App\Services\SimaksiService;
use App\Services\GroupPhotoService;
use App\Services\TicketPdfService;
use App\Support\CodeGenerator;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class PublicSimaksiController extends Controller
{
    /**
     * Render Public Portal Blade View with Live Quota & Trail Telemetry
     */
    public function portal(Request $request)
    {
        $defaultQuota = (int) AppSetting::get('default_daily_quota', 150);
        $todayStr = Carbon::today()->toDateString();
        $todayQuota = DailyQuota::whereDate('quota_date', $todayStr)->first();
        if (! $todayQuota) {
            try {
                $todayQuota = DailyQuota::create([
                    'quota_date' => $todayStr,
                    'max_quota' => $defaultQuota,
                    'booked_quota' => 0,
                ]);
            } catch (QueryException $e) {
                $todayQuota = DailyQuota::whereDate('quota_date', $todayStr)->first();
            }
        }

        $trailCondition = TrailCondition::latest()->first();
        $tariffs = AppSetting::getTariffs();
        $profile = AppSetting::getExpeditionProfile();

        return view('portal.index', compact('todayQuota', 'trailCondition', 'tariffs', 'profile'));
    }

    /**
     * Live Quota Telemetry API by Date
     */
    public function checkQuota(Request $request)
    {
        $defaultQuota = (int) AppSetting::get('default_daily_quota', 150);
        $date = $request->query('date', Carbon::today()->toDateString());
        $quota = DailyQuota::whereDate('quota_date', $date)->first();
        if (! $quota) {
            return response()->json([
                'success' => true,
                'quota_date' => $date,
                'max_quota' => $defaultQuota,
                'booked_quota' => 0,
                'remaining_quota' => $defaultQuota,
                'is_closed' => false,
                'closure_reason' => null,
            ]);
        }

        return response()->json([
            'success' => true,
            'quota_date' => $date,
            'max_quota' => $quota->max_quota,
            'booked_quota' => $quota->booked_quota,
            'remaining_quota' => $quota->remaining_quota,
            'is_closed' => (bool) $quota->is_closed,
            'closure_reason' => $quota->closure_reason,
        ]);
    }

    public function store(Request $request)
    {
        // Support both array and JSON-encoded string for members in multipart requests
        if ($request->has('members') && is_string($request->input('members'))) {
            $decoded = json_decode($request->input('members'), true);
            if (is_array($decoded)) {
                $request->merge(['members' => $decoded]);
            }
        }

        $guard = app(BookingGuardService::class);
        $today = Carbon::today()->toDateString();
        $lastBookableDate = Carbon::today()->addDays($guard->maxDaysAhead())->toDateString();

        $request->validate([
            'hiking_category' => 'nullable|string|in:tektok,camp',
            'planned_campsite' => 'nullable|string|in:POS_2,POS_3',
            'hiking_date' => "required|date|after_or_equal:{$today}|before_or_equal:{$lastBookableDate}",
            'return_date' => 'nullable|date|after_or_equal:hiking_date',
            'leader_name' => 'required|string|max:100',
            'leader_nik' => 'required|digits:16',
            'leader_phone' => ['required', 'string', 'max:25', 'regex:/^\+?[0-9\s\-().]{9,20}$/'],
            'leader_city' => 'nullable|string|max:255',
            'leader_address' => 'nullable|string|max:500',
            'emergency_contact_name' => 'required|string|max:100',
            'emergency_contact_phone' => 'required|string|max:25',
            'emergency_relationship' => 'nullable|string|max:30',
            'medical_conditions' => 'nullable|string|max:255',
            'members' => 'required|array|min:1|max:15',
            'members.*.full_name' => 'required|string|max:100',
            'members.*.nik' => 'required|digits:16',
            'members.*.gender' => 'required|in:L,P',
            'members.*.medical_conditions' => 'nullable|string|max:255',
            'payment_method' => 'required|string|in:qris,transfer,cash_pos',
            'leader_ktp' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:10240',
            'payment_proof' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
            'ojek_count' => 'nullable|integer|min:0|max:50',
            'has_vehicle' => 'nullable|boolean',
            'motorcycle_count' => 'nullable|integer|min:0|max:50',
            'car_count' => 'nullable|integer|min:0|max:20',
            'unique_code' => 'nullable|integer|min:0|max:999',
        ], [
            'hiking_date.after_or_equal' => 'Tanggal pendakian tidak boleh tanggal yang sudah lewat.',
            'hiking_date.before_or_equal' => 'Pendaftaran hanya dibuka maksimal '.$guard->maxDaysAhead().' hari ke depan.',
            'leader_nik.digits' => 'NIK ketua harus 16 digit angka sesuai KTP.',
            'members.*.nik.digits' => 'NIK setiap anggota harus 16 digit angka sesuai KTP.',
            'leader_phone.regex' => 'Format nomor WhatsApp ketua tidak valid.',
        ]);

        // Lepaskan dulu kuota dari booking kedaluwarsa di tanggal ini (tidak bergantung pada scheduler)
        $hikingDateStr = Carbon::parse($request->input('hiking_date'))->toDateString();
        app(SimaksiService::class)->expireOverdueBookings($hikingDateStr);

        return DB::transaction(function () use ($request, $guard) {
            $hikingDate = Carbon::parse($request->input('hiking_date'));
            $members = $request->input('members');
            $totalClimbers = count($members);

            // Ensure Daily Quota row exists and is locked exclusively (FOR UPDATE)
            // Uses resilient atomic fetch-or-create with duplicate-key catch to prevent TOCTOU race conditions
            $dateStr = $hikingDate->toDateString();
            $defaultDailyQuota = (int) AppSetting::get('default_daily_quota', 150);

            $hikingCategory = $request->input('hiking_category', 'camp');
            $plannedCampsite = ($hikingCategory === 'camp') ? $request->input('planned_campsite') : null;
            $returnDateStr = ($hikingCategory === 'tektok')
                ? $hikingDate->toDateString()
                : ($request->input('return_date') ? Carbon::parse($request->input('return_date'))->toDateString() : $hikingDate->copy()->addDay()->toDateString());

            $quota = DailyQuota::whereDate('quota_date', $dateStr)->lockForUpdate()->first();
            if (! $quota) {
                try {
                    $quota = DailyQuota::create([
                        'quota_date' => $dateStr,
                        'max_quota' => $defaultDailyQuota,
                        'booked_quota' => 0,
                    ]);
                } catch (QueryException $e) {
                    // Concurrent request created the row in this exact window; acquire lock on created row
                    $quota = DailyQuota::whereDate('quota_date', $dateStr)->lockForUpdate()->firstOrFail();
                }
            }

            if ($quota->is_closed) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jalur pendakian pada tanggal tersebut sedang ditutup darurat: '.($quota->closure_reason ?? 'Cuaca Ekstrem'),
                ], 422);
            }

            if (($quota->booked_quota + $totalClimbers) > $quota->max_quota) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sisa kuota pada tanggal tersebut tidak mencukupi (Tersisa: '.max(0, $quota->max_quota - $quota->booked_quota).' pendaki).',
                ], 422);
            }

            // Anti-fraud: NIK ganda / NIK sudah dipakai rombongan lain / terlalu banyak booking belum bayar per nomor WA.
            // Dicek di dalam lock kuota agar dua request paralel tidak sama-sama lolos.
            $violation = $guard->violation(
                leaderNik: (string) $request->input('leader_nik'),
                memberNiks: array_map(fn ($m) => (string) $m['nik'], $members),
                leaderPhone: (string) $request->input('leader_phone'),
                hikingDate: $hikingDate,
                returnDate: Carbon::parse($returnDateStr),
            );
            if ($violation) {
                return response()->json(['success' => false, 'message' => $violation], 422);
            }

            // Calculate 3-digit unique transfer code (101 - 999) for Transfer & QRIS
            $paymentMethod = $request->input('payment_method', 'qris');
            $uniqueCode = 0;
            if (in_array($paymentMethod, ['transfer', 'qris'])) {
                $clientCode = (int) $request->input('unique_code');
                if ($clientCode >= 100 && $clientCode <= 999) {
                    $uniqueCode = $clientCode;
                } else {
                    $uniqueCode = rand(101, 899);
                }
            }

            $ojekCount = $request->has('ojek_count') ? (int) $request->input('ojek_count') : ($request->boolean('book_ojek') ? 1 : 0);
            $hasVehicle = $request->boolean('has_vehicle');
            $motorcycleCount = $hasVehicle ? (int) $request->input('motorcycle_count', 0) : 0;
            $carCount = $hasVehicle ? (int) $request->input('car_count', 0) : 0;

            // Authoritative centralized fee calculation
            $tariffCalc = app(\App\Services\TariffCalculatorService::class)->calculate(
                totalClimbers: $totalClimbers,
                ojekCount: $ojekCount,
                hasVehicle: $hasVehicle,
                motorcycleCount: $motorcycleCount,
                carCount: $carCount,
                uniqueCode: $uniqueCode
            );

            $simaksiFee = $tariffCalc['simaksi_fee'];
            $ojekFee = $tariffCalc['ojek_fee'];
            $parkingFee = $tariffCalc['parking_fee'];
            $subtotalAmount = $tariffCalc['subtotal'];
            $totalAmount = $tariffCalc['total_amount'];
            $trashDepositAmount = $tariffCalc['trash_deposit_amount'];
            $trashDepositStatus = $tariffCalc['trash_deposit_status'];
            $tentFee = 0;
            $tentCount = 0;

            // Handle Leader KTP File (Saved strictly to private local disk for UU PDP compliance)
            $ktpPath = null;
            if ($request->hasFile('leader_ktp')) {
                $ktpFile = $request->file('leader_ktp');
                $ktpFilename = 'ktp_'.date('Ymd_His').'_'.Str::random(12).'.'.$ktpFile->extension();
                $ktpPath = $ktpFile->storeAs('ktp', $ktpFilename, 'local');
            }

            // Handle Payment Proof File
            $proofPath = null;
            if ($request->hasFile('payment_proof')) {
                $file = $request->file('payment_proof');
                $filename = 'proof_'.date('Ymd_His').'_'.Str::random(8).'.'.$file->extension();
                $proofPath = $file->storeAs('payments', $filename, 'public');
            }

            // Generate Registration Code: SMK-YYYYMM-XXXX (High-Entropy Anti-Collision)
            $code = CodeGenerator::simaksiCode();

            // Address resolution (supports full address)
            $leaderAddress = $request->input('leader_address') ?: $request->input('leader_city', 'Indonesia');
            $leaderCity = $request->input('leader_city') ?: $leaderAddress;

            // Batas waktu bayar: lewat dari ini tanpa bayar/bukti → dibatalkan otomatis & kuota dilepas
            $paymentDueAt = $proofPath ? null : $guard->paymentDueAt($paymentMethod, $hikingDate);

            // Create Registration
            $reg = SimaksiRegistration::create([
                'registration_code' => $code,
                'leader_name' => $request->input('leader_name'),
                'leader_nik' => $request->input('leader_nik'),
                // Simpan hanya digit agar pencarian tiket & batas booking per nomor WA konsisten
                'leader_phone' => preg_replace('/\D/', '', (string) $request->input('leader_phone')),
                'leader_email' => $request->input('leader_email'),
                'leader_city' => $leaderCity,
                'leader_address' => $leaderAddress,
                'leader_ktp_path' => $ktpPath,
                'medical_conditions' => $request->input('medical_conditions', 'Tidak Ada'),
                'emergency_contact_name' => $request->input('emergency_contact_name'),
                'emergency_contact_phone' => $request->input('emergency_contact_phone'),
                'emergency_relationship' => $request->input('emergency_relationship', 'Keluarga'),
                'hiking_category' => $hikingCategory,
                'planned_campsite' => $plannedCampsite,
                'hiking_date' => $hikingDate->toDateString(),
                'return_date' => $returnDateStr,
                'total_climbers' => $totalClimbers,
                'status' => 'pending', // Menunggu verifikasi / konfirmasi kasir
                'simaksi_fee' => $simaksiFee,
                'ojek_fee' => $ojekFee,
                'ojek_count' => $ojekCount,
                'tent_fee' => $tentFee,
                'tent_count' => $tentCount,
                'has_vehicle' => $hasVehicle,
                'motorcycle_count' => $motorcycleCount,
                'car_count' => $carCount,
                'parking_fee' => $parkingFee,
                'unique_code' => $uniqueCode,
                'total_amount' => $totalAmount,
                'payment_status' => $proofPath ? 'paid' : 'unpaid', // Jika upload bukti transfer, status bayar pending verifikasi
                'payment_method' => $request->input('payment_method'),
                'payment_proof_path' => $proofPath,
                'payment_due_at' => $paymentDueAt,
                'trash_deposit_amount' => $trashDepositAmount,
                'trash_deposit_status' => $trashDepositStatus,
            ]);

            // Save all Climber Members
            foreach ($members as $idx => $m) {
                ClimberMember::create([
                    'simaksi_registration_id' => $reg->id,
                    'full_name' => $m['full_name'],
                    'nik' => $m['nik'],
                    'phone' => $m['phone'] ?? null,
                    'gender' => $m['gender'] ?? 'L',
                    'city_origin' => $m['city_origin'] ?? $reg->leader_city,
                    'medical_conditions' => $m['medical_conditions'] ?? 'Tidak Ada',
                    'is_leader' => ($idx === 0),
                    'is_checked_in' => false,
                ]);
            }

            // Reserve/Lock Quota atomically
            $quota->increment('booked_quota', $totalClimbers);

            AuditService::log(
                action: 'PUBLIC_SIMAKSI_REGISTRATION',
                entityType: 'SimaksiRegistration',
                entityId: $reg->id,
                description: "Pendaftaran mandiri SIMAKSI {$code} oleh {$reg->leader_name} ({$totalClimbers} orang). Total Rp ".number_format($totalAmount, 0, ',', '.')." via {$reg->payment_method}.",
                payload: [
                    'code' => $code,
                    'total' => $totalAmount,
                    'has_proof' => ! is_null($proofPath),
                ]
            );

            return response()->json([
                'success' => true,
                'registration_code' => $code,
                'leader_name' => $reg->leader_name,
                'total_climbers' => $totalClimbers,
                'hiking_date' => $hikingDate->format('d M Y'),
                'subtotal_amount' => $subtotalAmount,
                'parking_fee' => $parkingFee,
                'has_vehicle' => $hasVehicle,
                'motorcycle_count' => $motorcycleCount,
                'car_count' => $carCount,
                'unique_code' => $uniqueCode,
                'total_amount' => $totalAmount,
                'payment_method' => strtoupper($reg->payment_method),
                'has_ktp' => ! is_null($ktpPath),
                'leader_ktp_url' => null,
                'has_proof' => ! is_null($proofPath),
                'trash_deposit_amount' => $reg->trash_deposit_amount,
                'trash_deposit_status' => $reg->trash_deposit_status,
                'status' => $reg->status,
                'payment_due_at' => $paymentDueAt?->toIso8601String(),
                'payment_due_label' => $paymentDueAt?->translatedFormat('d M Y H:i').($paymentDueAt ? ' WIB' : ''),
                'ticket_url' => route('portal.simaksi.ticket', $code),
                'pdf_download_url' => route('portal.simaksi.ticket_pdf', $code),
                'message' => 'Pendaftaran SIMAKSI berhasil dikirim. Kode tiket Anda adalah '.$code,
            ]);
        });
    }

    /**
     * Public Ticket Lookup & Tracking
     * Allows climbers to track status by Registration Code (SMK-...) or WhatsApp Phone Number
     */
    public function lookup(Request $request)
    {
        $query = trim($request->input('code') ?? $request->input('phone') ?? $request->input('query') ?? '');

        if (empty($query)) {
            return response()->json([
                'success' => false,
                'message' => 'Silakan masukkan Kode Registrasi (SMK-...) atau Nomor WhatsApp Ketua.',
            ], 422);
        }

        $notFound = fn () => response()->json([
            'success' => false,
            'message' => 'Data tiket SIMAKSI tidak ditemukan untuk pencarian: '.htmlspecialchars($query),
        ], 404);

        $cleanPhone = preg_replace('/[^0-9]/', '', $query);
        $isPhoneQuery = ! str_starts_with(strtoupper($query), 'SMK-') && strlen($cleanPhone) >= 10;

        if (! $isPhoneQuery) {
            // Kode registrasi: kode 8 karakter acak berperan sebagai kunci akses tiket
            $reg = SimaksiRegistration::with('members')
                ->where('registration_code', strtoupper($query))
                ->first();

            return $reg ? $this->lookupResponse($reg) : $notFound();
        }

        // Nomor WhatsApp mudah diketahui orang lain → wajib bukti kepemilikan: 4 digit terakhir NIK ketua.
        // Tanpa ini siapa pun yang tahu nomor HP pendaki bisa mendapat kode tiket lalu mengganti KTP/bukti bayarnya.
        $nikLast4 = (string) $request->input('nik_last4', '');
        if (! preg_match('/^\d{4}$/', $nikLast4)) {
            return response()->json([
                'success' => false,
                'requires_nik' => true,
                'message' => 'Pencarian dengan nomor WhatsApp wajib disertai 4 digit terakhir NIK ketua rombongan.',
            ], 422);
        }

        // Batasi tebakan NIK per nomor (lintas IP): 5 kegagalan / 30 menit
        $phone = BookingGuardService::normalizePhone($cleanPhone);
        $attemptKey = 'simaksi-lookup-phone:'.$phone;
        if (RateLimiter::tooManyAttempts($attemptKey, 5)) {
            $minutes = (int) ceil(RateLimiter::availableIn($attemptKey) / 60);

            return response()->json([
                'success' => false,
                'message' => "Terlalu banyak percobaan untuk nomor ini. Coba lagi dalam {$minutes} menit atau gunakan Kode SIMAKSI.",
            ], 429);
        }

        $reg = SimaksiRegistration::with('members')
            ->where('leader_phone', 'like', '%'.substr($phone, -9))
            ->where('leader_nik', 'like', '%'.$nikLast4)
            ->latest()
            ->get()
            ->first(fn ($r) => BookingGuardService::normalizePhone($r->leader_phone) === $phone
                && str_ends_with((string) $r->leader_nik, $nikLast4));

        if (! $reg) {
            RateLimiter::hit($attemptKey, 30 * 60);

            return $notFound();
        }

        RateLimiter::clear($attemptKey);

        return $this->lookupResponse($reg);
    }

    /**
     * Ringkasan tiket untuk portal publik (data pribadi dimasking sesuai UU PDP).
     */
    private function lookupResponse(SimaksiRegistration $reg)
    {

        $proofUrl = null;
        if ($reg->payment_proof_path) {
            $proofUrl = asset('storage/'.$reg->payment_proof_path);
        }

        // Status description & styling badges
        $statusLabels = [
            'pending' => 'MENUNGGU VERIFIKASI',
            'needs_revision' => 'PERLU PERBAIKAN BERKAS',
            'revision_needed' => 'PERLU PERBAIKAN BERKAS',
            'approved' => 'DISETUJUI / SIAP CHECK-IN',
            'paid' => 'LUNAS / SIAP CHECK-IN',
            'gate_cleared' => 'LOLOS PEMERIKSAAN GATE (AMBIL SURAT JALAN DI KASIR)',
            'checked_in' => 'SEDANG MENDAKI (DI JALUR)',
            'returned' => 'SUDAH TURUN (PENYELESAIAN DI KASIR)',
            'active_hiking' => 'SEDANG MENDAKI (DI JALUR)',
            'completed' => 'PENDAKIAN SELESAI',
            'rejected' => 'DITOLAK',
        ];

        // Masking functions for UU PDP (Personal Data Protection) compliance
        $maskNik = function (?string $nik): string {
            if (empty($nik)) return '-';
            $len = strlen($nik);
            if ($len <= 8) return str_repeat('*', $len);
            return substr($nik, 0, 4) . '******' . substr($nik, -4);
        };

        $maskPhone = function (?string $phone): string {
            if (empty($phone)) return '-';
            $len = strlen($phone);
            if ($len <= 6) return str_repeat('*', $len);
            return substr($phone, 0, 4) . '****' . substr($phone, -3);
        };

        $medicalMask = (!empty($reg->medical_conditions) && $reg->medical_conditions !== 'Tidak Ada')
            ? 'Tercatat dalam SOP Petugas (Privat)'
            : 'Tidak Ada';

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $reg->id,
                'registration_code' => $reg->registration_code,
                'leader_name' => $reg->leader_name,
                'leader_nik' => $maskNik($reg->leader_nik),
                'leader_phone' => $maskPhone($reg->leader_phone),
                'leader_city' => $reg->leader_city,
                'leader_address' => $reg->leader_city, // Alamat lengkap hanya untuk petugas (UU PDP)
                'medical_conditions' => $medicalMask,
                'emergency_contact_name' => $reg->emergency_contact_name,
                'emergency_contact_phone' => $maskPhone($reg->emergency_contact_phone),
                'emergency_relationship' => $reg->emergency_relationship,
                'hiking_date' => $reg->hiking_date ? Carbon::parse($reg->hiking_date)->format('d M Y') : '-',
                'hiking_date_raw' => $reg->hiking_date,
                'return_date' => $reg->return_date ? Carbon::parse($reg->return_date)->format('d M Y') : '-',
                'total_climbers' => $reg->total_climbers,
                'status' => $reg->status,
                'status_label' => $statusLabels[$reg->status] ?? strtoupper($reg->status),
                'revision_notes' => $reg->revision_notes,
                'payment_status' => $reg->payment_status,
                'payment_due_label' => ($reg->status === 'pending' && $reg->payment_status !== 'paid' && $reg->payment_due_at)
                    ? $reg->payment_due_at->translatedFormat('d M Y H:i').' WIB'
                    : null,
                'payment_method' => strtoupper($reg->payment_method),
                'simaksi_fee' => $reg->simaksi_fee,
                'ojek_fee' => $reg->ojek_fee,
                'ojek_count' => $reg->ojek_count,
                'tent_fee' => $reg->tent_fee,
                'tent_count' => $reg->tent_count,
                'has_vehicle' => (bool) $reg->has_vehicle,
                'motorcycle_count' => (int) $reg->motorcycle_count,
                'car_count' => (int) $reg->car_count,
                'parking_fee' => (float) $reg->parking_fee,
                'unique_code' => $reg->unique_code,
                'total_amount' => $reg->total_amount,
                'has_ktp' => ! is_null($reg->leader_ktp_path),
                'leader_ktp_url' => null, // KTP photo is restricted to authenticated management staff
                'has_proof' => ! is_null($reg->payment_proof_path),
                'payment_proof_url' => $proofUrl,
                'trash_deposit_status' => $reg->trash_deposit_status,
                'trash_deposit_amount' => $reg->trash_deposit_amount,
                'ticket_url' => route('portal.simaksi.ticket', $reg->registration_code),
                'pdf_download_url' => route('portal.simaksi.ticket_pdf', $reg->registration_code),
                'qr_data_uri' => \App\Support\QrCode::pngDataUri($reg->registration_code, 8, 4),
                'can_download_ticket' => in_array($reg->status, \App\Models\SimaksiRegistration::REVENUE_STATUSES),
                'created_at' => $reg->created_at->format('d M Y H:i WIB'),
                'members' => $reg->members->map(function ($m) use ($maskNik) {
                    return [
                        'id' => $m->id,
                        'full_name' => $m->full_name,
                        'nik' => $maskNik($m->nik),
                        'gender' => $m->gender,
                        'medical_conditions' => (!empty($m->medical_conditions) && $m->medical_conditions !== 'Tidak Ada')
                            ? 'Tercatat dalam SOP Petugas (Privat)'
                            : 'Tidak Ada',
                        'is_leader' => (bool) $m->is_leader,
                        'is_checked_in' => (bool) $m->is_checked_in,
                    ];
                }),
            ],
        ]);
    }

    /**
     * Upload Payment Proof Susulan (Anti-terclose recovery)
     * For climbers who closed the window to pay and return to upload proof later
     */
    public function uploadProof(Request $request)
    {
        $request->validate([
            'registration_code' => 'required|string',
            'payment_proof' => 'required|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
        ]);

        $code = strtoupper(trim($request->input('registration_code')));
        $reg = SimaksiRegistration::where('registration_code', $code)->first();

        if (! $reg) {
            return response()->json([
                'success' => false,
                'message' => 'Tiket registrasi SIMAKSI '.$code.' tidak ditemukan.',
            ], 404);
        }

        // Booking yang sudah dibatalkan (mis. kedaluwarsa) tidak bisa dihidupkan lagi lewat unggah bukti
        if ($reg->status === 'rejected') {
            return response()->json([
                'success' => false,
                'message' => 'SIMAKSI '.$code.' sudah dibatalkan ('.($reg->rejection_reason ?: 'ditolak').'). Silakan daftar ulang atau hubungi basecamp bila Anda sudah terlanjur transfer.',
            ], 422);
        }

        $file = $request->file('payment_proof');
        $filename = 'proof_susulan_'.date('Ymd_His').'_'.Str::random(8).'.'.$file->extension();
        $proofPath = $file->storeAs('payments', $filename, 'public');

        $updates = [
            'payment_proof_path' => $proofPath,
            'payment_status' => 'paid', // Status transfer masuk verifikasi
        ];

        // If it was in revision needed status due to proof issue, return it to pending
        if ($reg->status === 'revision_needed') {
            $updates['status'] = 'pending';
        }

        $reg->update($updates);

        AuditService::log(
            action: 'PAYMENT_PROOF_SUSULAN_UPLOADED',
            entityType: 'SimaksiRegistration',
            entityId: $reg->id,
            description: "Bukti transfer susulan diunggah oleh pemohon SIMAKSI {$code}.",
            payload: [
                'code' => $code,
                'file' => $filename,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Bukti pembayaran susulan berhasil diunggah. Tim admin kasir akan segera memverifikasi.',
            'payment_proof_url' => asset('storage/'.$proofPath),
            'status' => $reg->status,
            'payment_status' => $reg->payment_status,
        ]);
    }

    /**
     * Upload / Re-upload KTP Susulan (e.g. for needs_revision)
     */
    public function uploadKtp(Request $request)
    {
        $request->validate([
            'registration_code' => 'required|string',
            'leader_ktp' => 'required|file|mimes:jpg,jpeg,png,webp,pdf|max:10240',
        ]);

        $code = strtoupper(trim($request->input('registration_code')));
        $reg = SimaksiRegistration::where('registration_code', $code)->first();

        if (! $reg) {
            return response()->json([
                'success' => false,
                'message' => 'Tiket registrasi SIMAKSI '.$code.' tidak ditemukan.',
            ], 404);
        }

        $file = $request->file('leader_ktp');
        $filename = 'ktp_reupload_'.date('Ymd_His').'_'.Str::random(12).'.'.$file->extension();
        $ktpPath = $file->storeAs('ktp', $filename, 'local');

        $updates = [
            'leader_ktp_path' => $ktpPath,
        ];

        if (in_array($reg->status, ['needs_revision', 'revision_needed'])) {
            $updates['status'] = 'pending';
        }

        $reg->update($updates);

        AuditService::log(
            action: 'KTP_REVISION_UPLOADED',
            entityType: 'SimaksiRegistration',
            entityId: $reg->id,
            description: "Foto KTP perbaikan/susulan diunggah oleh pemohon SIMAKSI {$code}.",
            payload: [
                'code' => $code,
                'file' => $filename,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Foto KTP perbaikan berhasil diunggah. Tim verifikator akan segera memverifikasi.',
            'has_ktp' => true,
            'leader_ktp_url' => null,
            'status' => $reg->status,
        ]);
    }

    /**
     * Upload Group Photo by Hikers / Team Leader (with automatic compression & SAR watermarking)
     */
    public function uploadGroupPhoto(Request $request, GroupPhotoService $photoService)
    {
        $request->validate([
            'registration_code' => 'required|string',
            'group_photo' => 'required|file|mimes:jpg,jpeg,png,webp|max:15360',
        ]);

        $code = strtoupper(trim($request->input('registration_code')));
        $reg = SimaksiRegistration::where('registration_code', $code)->first();

        if (! $reg) {
            return response()->json([
                'success' => false,
                'message' => 'Tiket registrasi SIMAKSI '.$code.' tidak ditemukan.',
            ], 404);
        }

        try {
            $path = $photoService->processAndStore($reg, $request->file('group_photo'));

            AuditService::log(
                action: 'GROUP_PHOTO_UPLOADED',
                entityType: 'SimaksiRegistration',
                entityId: $reg->id,
                description: "Foto regu rombongan {$reg->leader_name} ({$code}) berhasil diunggah & dibubuhi watermark otomatis.",
                payload: [
                    'code' => $code,
                    'path' => $path,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Foto regu keberangkatan berhasil diunggah dan tervalidasi watermark resmi!',
                'photo_url' => asset('storage/'.$path).'?v='.time(),
                'uploaded_at' => Carbon::now()->translatedFormat('d M Y, H:i').' WIB',
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses foto regu. Pastikan berkas berupa foto JPG/PNG/WEBP yang valid lalu coba lagi.',
            ], 500);
        }
    }

    /**
     * Interactive Responsive Digital Web Ticket View
     */
    public function ticketView(string $code)
    {
        $code = strtoupper(trim($code));
        $registration = SimaksiRegistration::with(['members', 'reviewer', 'checkInOfficer'])
            ->where('registration_code', $code)
            ->firstOrFail();

        $profile = AppSetting::getExpeditionProfile();
        $members = $registration->members;

        $statusLabels = [
            'pending' => 'MENUNGGU VERIFIKASI',
            'needs_revision' => 'PERLU PERBAIKAN BERKAS',
            'approved' => 'DISETUJUI / SIAP CHECK-IN',
            'paid' => 'LUNAS / SIAP CHECK-IN',
            'gate_cleared' => 'LOLOS PEMERIKSAAN GATE (AMBIL SURAT JALAN DI KASIR)',
            'checked_in' => 'SEDANG MENDAKI (AKTIF DI JALUR)',
            'returned' => 'SUDAH TURUN (PENYELESAIAN DI KASIR)',
            'completed' => 'PENDAKIAN SELESAI',
            'rejected' => 'DITOLAK',
        ];

        $statusLabel = $statusLabels[$registration->status] ?? strtoupper($registration->status);

        return view('tickets.simaksi_digital', compact('registration', 'members', 'profile', 'statusLabel'));
    }

    /**
     * Download Official SIMAKSI Ticket as PDF
     */
    public function downloadTicketPdf(string $code, TicketPdfService $pdfService)
    {
        $code = strtoupper(trim($code));
        $registration = SimaksiRegistration::with(['members', 'reviewer', 'checkInOfficer'])
            ->where('registration_code', $code)
            ->firstOrFail();

        $pdf = $pdfService->generate($registration);
        $filename = 'E-Tiket_SIMAKSI_'.$registration->registration_code.'.pdf';

        return $pdf->download($filename);
    }
}
