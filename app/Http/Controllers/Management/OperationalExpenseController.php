<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\OperationalExpense;
use App\Services\AuditService;
use App\Support\CodeGenerator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class OperationalExpenseController extends Controller
{
    /**
     * Display list of operational expenses / petty cash log.
     */
    public function index(Request $request)
    {
        $today = Carbon::today();
        $category = $request->query('category');
        $search = $request->query('search');

        $query = OperationalExpense::with(['cashier', 'voidedBy'])->latest();

        if ($category) {
            $query->where('category', $category);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('expense_code', 'like', "%{$search}%")
                    ->orWhere('recipient_name', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        $expenses = $query->paginate(20)->withQueryString();

        $todayTotal = (float) OperationalExpense::active()
            ->whereDate('created_at', $today)
            ->sum('amount');

        $todayCashTotal = (float) OperationalExpense::active()
            ->whereDate('created_at', $today)
            ->where('payment_method', 'cash')
            ->sum('amount');

        $todayCount = OperationalExpense::active()
            ->whereDate('created_at', $today)
            ->count();

        return view('management.pos.expenses', compact('expenses', 'todayTotal', 'todayCashTotal', 'todayCount', 'category', 'search'));
    }

    /**
     * Record a new urgent operational expense (Kas Keluar Mendadak).
     */
    public function store(Request $request)
    {
        $request->validate([
            'category' => 'required|string|in:bbm_genset,operasional_pos,logistik_sar,konsumsi_petugas,kebersihan_sampah,perbaikan_alat,lainnya',
            'amount' => 'required|numeric|min:1000',
            'recipient_name' => 'required|string|max:150',
            'payment_method' => 'required|string|in:cash,transfer',
            'notes' => 'required|string|max:1000',
            'receipt_photo' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
        ]);

        $receiptPath = null;
        if ($request->hasFile('receipt_photo')) {
            $file = $request->file('receipt_photo');
            $filename = 'receipt_exp_'.date('Ymd_His').'_'.Str::random(6).'.'.$file->extension();
            $receiptPath = $file->storeAs('expenses', $filename, 'public');
        }

        $code = CodeGenerator::expenseCode();
        $amount = (float) $request->input('amount');
        $cashier = Auth::user();

        $expense = OperationalExpense::create([
            'expense_code' => $code,
            'cashier_id' => $cashier->id,
            'category' => $request->input('category'),
            'amount' => $amount,
            'recipient_name' => trim($request->input('recipient_name')),
            'payment_method' => $request->input('payment_method', 'cash'),
            'notes' => trim($request->input('notes')),
            'receipt_photo_path' => $receiptPath,
            'status' => 'active',
        ]);

        AuditService::log(
            action: 'OPERATIONAL_EXPENSE_RECORDED',
            entityType: 'OperationalExpense',
            entityId: $expense->id,
            description: "Kasir {$cashier->name} mencatat pengeluaran kas keluar {$expense->expense_code} sebesar Rp ".number_format($amount, 0, ',', '.')." untuk {$expense->category_label} ({$expense->recipient_name}).",
            payload: [
                'code' => $code,
                'category' => $expense->category,
                'amount' => $amount,
                'recipient' => $expense->recipient_name,
                'method' => $expense->payment_method,
            ]
        );

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Pengeluaran kas keluar {$code} sebesar Rp ".number_format($amount, 0, ',', '.').' berhasil dicatat.',
                'expense' => $expense,
            ]);
        }

        return redirect()->back()->with('success', "Kas keluar {$code} sebesar Rp ".number_format($amount, 0, ',', '.').' berhasil dicatat ke buku kas operasional.');
    }

    /**
     * Void / cancel an accidental operational expense.
     */
    public function void(Request $request, int $id)
    {
        $request->validate([
            'void_reason' => 'required|string|min:5|max:255',
        ]);

        $expense = OperationalExpense::findOrFail($id);

        if ($expense->status === 'voided') {
            return redirect()->back()->with('error', "Pengeluaran {$expense->expense_code} sudah dibatalkan sebelumnya.");
        }

        $expense->update([
            'status' => 'voided',
            'voided_by' => Auth::id(),
            'voided_at' => Carbon::now(),
            'void_reason' => $request->input('void_reason'),
        ]);

        AuditService::log(
            action: 'OPERATIONAL_EXPENSE_VOIDED',
            entityType: 'OperationalExpense',
            entityId: $expense->id,
            description: "Pengeluaran operasional {$expense->expense_code} sebesar Rp ".number_format($expense->amount, 0, ',', '.').' dibatalkan (VOID).',
            reason: $request->input('void_reason')
        );

        return redirect()->back()->with('success', "Pengeluaran kas {$expense->expense_code} berhasil dibatalkan (VOID).");
    }

    /**
     * Thermal printable voucher for operational expense.
     */
    public function printVoucher(int $id)
    {
        $expense = OperationalExpense::with(['cashier', 'voidedBy'])->findOrFail($id);

        return view('management.pos.print_expense_voucher', compact('expense'));
    }
}
