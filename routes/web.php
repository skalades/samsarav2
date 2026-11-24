<?php

use App\Http\Controllers\Management\AuditLogController;
use App\Http\Controllers\Management\AuthController;
use App\Http\Controllers\Management\CashierDeskController;
use App\Http\Controllers\Management\DashboardController;
use App\Http\Controllers\Management\GateCheckInController;
use App\Http\Controllers\Management\InspectionTemplateController;
use App\Http\Controllers\Management\InventoryController;
use App\Http\Controllers\Management\OjekController;
use App\Http\Controllers\Management\OperationalExpenseController;
use App\Http\Controllers\Management\PosController;
use App\Http\Controllers\Management\QuotaController;
use App\Http\Controllers\Management\RangerCheckpointController;
use App\Http\Controllers\Management\ReportController;
use App\Http\Controllers\Management\SimaksiController;
use App\Http\Controllers\Management\TariffSettingController;
use App\Http\Controllers\Management\UserManagementController;
use App\Http\Controllers\Management\WhatsAppController;
use App\Http\Controllers\PublicSimaksiController;
use Illuminate\Support\Facades\Route;

// Public Mobile Landing Page
Route::get('/', [PublicSimaksiController::class, 'portal'])->name('portal.home');

// PWA Offline Fallback Routes
Route::get('/offline', fn () => response(file_get_contents(public_path('offline.html')))->header('Content-Type', 'text/html; charset=utf-8'))->name('pwa.offline');
Route::get('/management/offline', fn () => response(file_get_contents(public_path('offline.html')))->header('Content-Type', 'text/html; charset=utf-8'))->name('management.offline');

// Public Self-Service SIMAKSI Registration & Ticket Tracking (with Anti-Abuse Rate Limiting)
Route::middleware('throttle:60,1')->group(function () {
    Route::get('/api/simaksi/quota', [PublicSimaksiController::class, 'checkQuota'])->name('portal.simaksi.quota');
    Route::get('/api/simaksi/lookup', [PublicSimaksiController::class, 'lookup'])->name('portal.simaksi.lookup');
    Route::get('/simaksi/ticket/{code}', [PublicSimaksiController::class, 'ticketView'])->name('portal.simaksi.ticket');
    Route::get('/simaksi/ticket/{code}/pdf', [PublicSimaksiController::class, 'downloadTicketPdf'])->name('portal.simaksi.ticket_pdf');
});

Route::middleware('throttle:15,1')->group(function () {
    Route::post('/api/simaksi/register', [PublicSimaksiController::class, 'store'])->name('portal.simaksi.register');
    Route::post('/api/simaksi/upload-proof', [PublicSimaksiController::class, 'uploadProof'])->name('portal.simaksi.upload_proof');
    Route::post('/api/simaksi/upload-ktp', [PublicSimaksiController::class, 'uploadKtp'])->name('portal.simaksi.upload_ktp');
    Route::post('/api/simaksi/upload-group-photo', [PublicSimaksiController::class, 'uploadGroupPhoto'])->name('portal.simaksi.upload_group_photo');
});

// Management Auth
Route::prefix('management')->name('management.')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1')->name('login.submit');
    Route::get('/quick-login', [AuthController::class, 'quickLogin'])->name('quick_login');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // ================================================================
    // Authenticated Management Routes — 4 LEVEL AKSES
    //   superadmin : semua (otomatis lolos RoleMiddleware)
    //   kasir      : SIMAKSI & pembayaran, POS, kas, sewa alat, ojek, antrean cetak
    //   gatekeeper : pemeriksaan naik/turun (alat, logistik, foto tim), dispatch HT
    //   ranger     : scanner Pos 1–3 & puncak
    // ================================================================
    Route::middleware(['auth', 'role'])->group(function () {
        Route::get('/', fn () => redirect()->route('management.dashboard'));
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // ---------------- KASIR BASECAMP ----------------
        Route::middleware('role:kasir')->group(function () {
            // SIMAKSI Verifikasi & Dokumen Privat
            Route::get('/simaksi', [SimaksiController::class, 'index'])->name('simaksi.index');
            Route::get('/simaksi/{id}', [SimaksiController::class, 'show'])->name('simaksi.show');
            Route::get('/simaksi/{id}/ktp', [SimaksiController::class, 'ktp'])->name('simaksi.ktp');
            Route::get('/simaksi/{id}/ticket-pdf', [SimaksiController::class, 'downloadTicketPdf'])->name('simaksi.ticket_pdf');
            Route::post('/simaksi/{id}/approve', [SimaksiController::class, 'approve'])->name('simaksi.approve');
            Route::post('/simaksi/{id}/reject', [SimaksiController::class, 'reject'])->name('simaksi.reject');
            Route::post('/simaksi/{id}/revision', [SimaksiController::class, 'requestRevision'])->name('simaksi.revision');
            Route::post('/simaksi/{id}/paid', [SimaksiController::class, 'markPaid'])->name('simaksi.paid');
            Route::post('/simaksi/{id}/whatsapp', [WhatsAppController::class, 'resendForRegistration'])->name('simaksi.whatsapp');

            // Antrean Kasir: kiriman Gate (siap berangkat & selesai turun)
            Route::get('/kasir/antrean', [CashierDeskController::class, 'index'])->name('desk.index');
            Route::get('/kasir/antrean/{id}', [CashierDeskController::class, 'show'])->name('desk.show');
            Route::post('/kasir/antrean/{id}/berangkatkan', [CashierDeskController::class, 'release'])->name('desk.release');
            Route::post('/kasir/antrean/{id}/selesaikan', [CashierDeskController::class, 'finalize'])->name('desk.finalize');

            // Cetak dokumen rombongan
            Route::get('/gate/{id}/ticket-pdf', [GateCheckInController::class, 'downloadTicketPdf'])->name('gate.ticket_pdf');
            Route::get('/gate/{id}/print-climber-pass', [GateCheckInController::class, 'printClimberPass'])->name('gate.print_climber_pass');
            Route::get('/gate/{id}/print-ojek-voucher', [GateCheckInController::class, 'printOjekVoucher'])->name('gate.print_ojek_voucher');
            Route::get('/gate/{id}/print-parking-pass', [GateCheckInController::class, 'printParkingPass'])->name('gate.print_parking_pass');

            // POS Kasir & Pengeluaran Operasional (Petty Cash)
            Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
            Route::post('/pos', [PosController::class, 'store'])->name('pos.store');
            Route::get('/pos/receipt/{id}', [PosController::class, 'receipt'])->name('pos.receipt');
            Route::get('/pos/history', [PosController::class, 'history'])->name('pos.history');
            Route::post('/pos/void/{id}', [PosController::class, 'void'])->name('pos.void');
            Route::get('/pos/expenses', [OperationalExpenseController::class, 'index'])->name('pos.expenses.index');
            Route::post('/pos/expenses', [OperationalExpenseController::class, 'store'])->name('pos.expenses.store');
            Route::get('/pos/expenses/{id}/print', [OperationalExpenseController::class, 'printVoucher'])->name('pos.expenses.print');
            Route::post('/pos/expenses/{id}/void', [OperationalExpenseController::class, 'void'])->name('pos.expenses.void');

            // Inventori & Sewa Alat
            Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
            Route::post('/inventory/return/{id}', [InventoryController::class, 'returnRental'])->name('inventory.return');

            // Transportasi & Ojek Resmi Basecamp
            Route::prefix('ojek')->name('ojek.')->group(function () {
                Route::get('/', [OjekController::class, 'index'])->name('index');
                Route::get('/drivers', [OjekController::class, 'drivers'])->name('drivers');
                Route::get('/settlements', [OjekController::class, 'settlements'])->name('settlements');
                Route::get('/trip/{id}/print', [OjekController::class, 'printTripVoucher'])->name('print_voucher');
                Route::get('/settlement/{id}/print', [OjekController::class, 'printSettlement'])->name('print_settlement');
                Route::post('/assign/{tripId}', [OjekController::class, 'assignDriver'])->name('assign');
                Route::post('/complete/{tripId}', [OjekController::class, 'completeTrip'])->name('complete');
                Route::post('/payout/{driverId}', [OjekController::class, 'processPayout'])->name('payout');
            });

            // Kalender kuota (lihat) & laporan keuangan
            Route::get('/quota', [QuotaController::class, 'index'])->name('quota.index');
            Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
            Route::get('/reports/print', [ReportController::class, 'print'])->name('reports.print');
        });

        // ---------------- GATEKEEPER ----------------
        Route::middleware('role:gatekeeper')->group(function () {
            Route::get('/gate', [GateCheckInController::class, 'index'])->name('gate.index');
            Route::get('/gate/{id}/naik', [GateCheckInController::class, 'ascentForm'])->name('gate.ascent');
            Route::post('/gate/{id}/naik', [GateCheckInController::class, 'submitAscent'])->name('gate.ascent.submit');
            Route::get('/gate/{id}/turun', [GateCheckInController::class, 'descentForm'])->name('gate.descent');
            Route::post('/gate/{id}/turun', [GateCheckInController::class, 'submitDescent'])->name('gate.descent.submit');
            Route::post('/gate/ht-dispatch', [GateCheckInController::class, 'recordHtDispatch'])->name('gate.ht_dispatch');
        });

        // ---------------- RANGER POS ----------------
        Route::middleware('role:ranger')->group(function () {
            Route::get('/ranger', [RangerCheckpointController::class, 'index'])->name('ranger.index');
            Route::post('/ranger/checkpoint', [RangerCheckpointController::class, 'store'])->name('ranger.checkpoint');
        });

        // ---------------- SUPERADMIN ----------------
        Route::middleware('role:superadmin')->group(function () {
            Route::post('/quota/update-date', [QuotaController::class, 'updateDate'])->name('quota.update_date');
            Route::post('/quota/update-trail', [QuotaController::class, 'updateTrailCondition'])->name('quota.update_trail');

            Route::post('/ojek/drivers', [OjekController::class, 'storeDriver'])->name('ojek.store_driver');
            Route::put('/ojek/drivers/{id}', [OjekController::class, 'updateDriver'])->name('ojek.update_driver');
            Route::post('/ojek/drivers/{id}/toggle', [OjekController::class, 'toggleDriverStatus'])->name('ojek.toggle_driver');

            Route::post('/inventory/update/{id}', [InventoryController::class, 'updateStock'])->name('inventory.update');

            Route::get('/audit', [AuditLogController::class, 'index'])->name('audit.index');

            Route::get('/settings/tariffs', [TariffSettingController::class, 'index'])->name('settings.tariffs');
            Route::post('/settings/tariffs', [TariffSettingController::class, 'update'])->name('settings.tariffs.update');
            Route::get('/settings/inspection', [InspectionTemplateController::class, 'index'])->name('settings.inspection');
            Route::post('/settings/inspection', [InspectionTemplateController::class, 'update'])->name('settings.inspection.update');

            Route::get('/settings/whatsapp', [WhatsAppController::class, 'index'])->name('settings.whatsapp');
            Route::post('/settings/whatsapp/test', [WhatsAppController::class, 'sendTest'])->name('settings.whatsapp.test');
            Route::post('/settings/whatsapp/{messageId}/retry', [WhatsAppController::class, 'retry'])->name('settings.whatsapp.retry');

            Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
            Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
            Route::put('/users/{id}', [UserManagementController::class, 'update'])->name('users.update');
            Route::post('/users/{id}/toggle', [UserManagementController::class, 'toggleStatus'])->name('users.toggle');
        });
    });
});
