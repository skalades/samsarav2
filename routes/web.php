<?php

use App\Http\Controllers\Management\AuditLogController;
use App\Http\Controllers\Management\AuthController;
use App\Http\Controllers\Management\DashboardController;
use App\Http\Controllers\Management\GateCheckInController;
use App\Http\Controllers\Management\InventoryController;
use App\Http\Controllers\Management\OjekController;
use App\Http\Controllers\Management\OperationalExpenseController;
use App\Http\Controllers\Management\PosController;
use App\Http\Controllers\Management\QuotaController;
use App\Http\Controllers\Management\ReportController;
use App\Http\Controllers\Management\SimaksiController;
use App\Http\Controllers\Management\TariffSettingController;
use App\Http\Controllers\PublicSimaksiController;
use Illuminate\Support\Facades\Route;

// Public Mobile Landing Page
Route::get('/', [PublicSimaksiController::class, 'portal'])->name('portal.home');

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

    // Authenticated Management Routes
    Route::middleware('auth')->group(function () {
        Route::get('/', fn () => redirect()->route('management.dashboard'));
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // SIMAKSI Verifikasi & Dokumen Privat
        Route::get('/simaksi', [SimaksiController::class, 'index'])->name('simaksi.index');
        Route::get('/simaksi/{id}', [SimaksiController::class, 'show'])->name('simaksi.show');
        Route::get('/simaksi/{id}/ktp', [SimaksiController::class, 'ktp'])->name('simaksi.ktp');
        Route::get('/simaksi/{id}/ticket-pdf', [SimaksiController::class, 'downloadTicketPdf'])->name('simaksi.ticket_pdf');

        Route::middleware('role:superadmin,admin,verifikator,kasir')->group(function () {
            Route::post('/simaksi/{id}/approve', [SimaksiController::class, 'approve'])->name('simaksi.approve');
            Route::post('/simaksi/{id}/reject', [SimaksiController::class, 'reject'])->name('simaksi.reject');
            Route::post('/simaksi/{id}/revision', [SimaksiController::class, 'requestRevision'])->name('simaksi.revision');
        });

        Route::middleware('role:superadmin,admin,kasir')->group(function () {
            Route::post('/simaksi/{id}/paid', [SimaksiController::class, 'markPaid'])->name('simaksi.paid');
        });

        // Kalender Kuota & Telemetri
        Route::get('/quota', [QuotaController::class, 'index'])->name('quota.index');
        Route::middleware('role:superadmin,admin')->group(function () {
            Route::post('/quota/update-date', [QuotaController::class, 'updateDate'])->name('quota.update_date');
            Route::post('/quota/update-trail', [QuotaController::class, 'updateTrailCondition'])->name('quota.update_trail');
        });

        // POS Kasir & Pengeluaran Operasional (Petty Cash)
        Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
        Route::get('/pos/receipt/{id}', [PosController::class, 'receipt'])->name('pos.receipt');
        Route::get('/pos/history', [PosController::class, 'history'])->name('pos.history');
        Route::get('/pos/expenses', [OperationalExpenseController::class, 'index'])->name('pos.expenses.index');
        Route::get('/pos/expenses/{id}/print', [OperationalExpenseController::class, 'printVoucher'])->name('pos.expenses.print');

        Route::middleware('role:superadmin,admin,kasir')->group(function () {
            Route::post('/pos', [PosController::class, 'store'])->name('pos.store');
            Route::post('/pos/void/{id}', [PosController::class, 'void'])->name('pos.void');
            Route::post('/pos/expenses', [OperationalExpenseController::class, 'store'])->name('pos.expenses.store');
            Route::post('/pos/expenses/{id}/void', [OperationalExpenseController::class, 'void'])->name('pos.expenses.void');
        });

        // Gate Check-in & Manifest & Vouchers
        Route::get('/gate', [GateCheckInController::class, 'index'])->name('gate.index');
        Route::get('/gate/{id}/ticket-pdf', [GateCheckInController::class, 'downloadTicketPdf'])->name('gate.ticket_pdf');
        Route::get('/gate/{id}/print-climber-pass', [GateCheckInController::class, 'printClimberPass'])->name('gate.print_climber_pass');
        Route::get('/gate/{id}/print-ojek-voucher', [GateCheckInController::class, 'printOjekVoucher'])->name('gate.print_ojek_voucher');
        Route::get('/gate/{id}/print-parking-pass', [GateCheckInController::class, 'printParkingPass'])->name('gate.print_parking_pass');
        Route::middleware('role:superadmin,admin,kasir,gate_officer')->group(function () {
            Route::post('/gate/{id}/checkin', [GateCheckInController::class, 'processCheckIn'])->name('gate.checkin');
            Route::post('/gate/{id}/checkout', [GateCheckInController::class, 'processCheckOut'])->name('gate.checkout');
        });

        // Transportasi & Ojek Resmi Basecamp
        Route::prefix('ojek')->name('ojek.')->group(function () {
            Route::get('/', [OjekController::class, 'index'])->name('index');
            Route::get('/drivers', [OjekController::class, 'drivers'])->name('drivers');
            Route::get('/settlements', [OjekController::class, 'settlements'])->name('settlements');
            Route::get('/trip/{id}/print', [OjekController::class, 'printTripVoucher'])->name('print_voucher');
            Route::get('/settlement/{id}/print', [OjekController::class, 'printSettlement'])->name('print_settlement');

            Route::middleware('role:superadmin,admin,kasir,gate_officer')->group(function () {
                Route::post('/assign/{tripId}', [OjekController::class, 'assignDriver'])->name('assign');
                Route::post('/complete/{tripId}', [OjekController::class, 'completeTrip'])->name('complete');
            });

            Route::middleware('role:superadmin,admin')->group(function () {
                Route::post('/drivers', [OjekController::class, 'storeDriver'])->name('store_driver');
                Route::put('/drivers/{id}', [OjekController::class, 'updateDriver'])->name('update_driver');
                Route::post('/drivers/{id}/toggle', [OjekController::class, 'toggleDriverStatus'])->name('toggle_driver');
            });

            Route::middleware('role:superadmin,admin,kasir')->group(function () {
                Route::post('/payout/{driverId}', [OjekController::class, 'processPayout'])->name('payout');
            });
        });

        // Inventori & Sewa Alat
        Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
        Route::middleware('role:superadmin,admin,kasir')->group(function () {
            Route::post('/inventory/return/{id}', [InventoryController::class, 'returnRental'])->name('inventory.return');
        });
        Route::middleware('role:superadmin,admin')->group(function () {
            Route::post('/inventory/update/{id}', [InventoryController::class, 'updateStock'])->name('inventory.update');
        });

        // Audit Logs (Superadmin, Admin, Auditor)
        Route::middleware('role:superadmin,admin,auditor')->group(function () {
            Route::get('/audit', [AuditLogController::class, 'index'])->name('audit.index');
        });

        // Laporan Keuangan & Analitik Komprehensif (Superadmin, Admin, Kasir, Auditor)
        Route::middleware('role:superadmin,admin,kasir,auditor')->group(function () {
            Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
            Route::get('/reports/print', [ReportController::class, 'print'])->name('reports.print');
        });

        // Pengaturan Tarif & Kebijakan Basecamp (Superadmin & Admin)
        Route::middleware('role:superadmin,admin')->group(function () {
            Route::get('/settings/tariffs', [TariffSettingController::class, 'index'])->name('settings.tariffs');
            Route::post('/settings/tariffs', [TariffSettingController::class, 'update'])->name('settings.tariffs.update');
        });
    });
});
