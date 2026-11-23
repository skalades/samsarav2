<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\OjekSettlement;
use App\Models\OperationalExpense;
use App\Models\PosOrder;
use App\Models\SimaksiRegistration;
use App\Services\PosService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PosController extends Controller
{
    public function __construct(
        protected PosService $posService
    ) {}

    public function index()
    {
        $items = InventoryItem::where('is_active', true)->orderBy('category')->get();
        $categories = $items->pluck('category')->unique();

        // Registrations for quick attach in POS (pending or paid)
        $activeRegistrations = SimaksiRegistration::whereIn('status', ['pending', 'paid', 'checked_in'])
            ->latest()
            ->take(30)
            ->get();

        $cashDrawer = $this->getCashDrawerSummary();

        return view('management.pos.index', compact('items', 'categories', 'activeRegistrations', 'cashDrawer'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string',
            'items' => 'required|array|min:1',
            'payment_method' => 'required|string|in:cash,qris,transfer',
        ]);

        try {
            $order = $this->posService->createOrder(
                cashierId: Auth::id(),
                customerName: $request->input('customer_name'),
                customerPhone: $request->input('customer_phone'),
                items: $request->input('items'),
                paymentMethod: $request->input('payment_method'),
                simaksiId: $request->input('simaksi_registration_id'),
                cashTendered: (float) $request->input('cash_tendered', 0),
                notes: $request->input('notes')
            );

            return response()->json([
                'success' => true,
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'redirect_url' => route('management.pos.receipt', $order->id),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function receipt(int $id)
    {
        $order = PosOrder::with(['items', 'cashier', 'simaksi'])->findOrFail($id);

        return view('management.pos.receipt', compact('order'));
    }

    public function history(Request $request)
    {
        $today = Carbon::today();
        $orders = PosOrder::with(['cashier', 'items'])
            ->latest()
            ->paginate(15);

        $shiftSummary = $this->getCashDrawerSummary($today);

        return view('management.pos.history', compact('orders', 'shiftSummary'));
    }

    /**
     * Get complete cash drawer status including inflow, ojek payouts, and operational expenses.
     */
    public function getCashDrawerSummary(?Carbon $date = null): array
    {
        $date = $date ?? Carbon::today();

        // 1. Kas Masuk (Inflow)
        $posCash = (float) PosOrder::whereDate('created_at', $date)
            ->where('status', 'completed')
            ->where('payment_method', 'cash')
            ->sum('total_amount');

        $simaksiDirect = SimaksiRegistration::whereDate('paid_at', $date)
            ->whereIn('status', ['approved', 'paid', 'checked_in', 'completed'])
            ->get();

        $simaksiCash = 0.0;
        $simaksiQris = 0.0;
        $simaksiTransfer = 0.0;

        foreach ($simaksiDirect as $reg) {
            $amt = (float) $reg->total_amount;
            $m = strtolower((string) $reg->payment_method);

            if ($m === 'qris') {
                $simaksiQris += $amt;
            } elseif (in_array($m, ['transfer', 'bank_transfer', 'bca', 'mandiri', 'bri'])) {
                $simaksiTransfer += $amt;
            } elseif (in_array($m, ['cash', 'cash_pos', 'tunai'])) {
                $simaksiCash += $amt;
            } else {
                if (! empty($reg->payment_proof_path)) {
                    $simaksiTransfer += $amt;
                } else {
                    $simaksiCash += $amt;
                }
            }
        }

        $totalCashIn = $posCash + $simaksiCash;

        // Non-Cash
        $posQris = (float) PosOrder::whereDate('created_at', $date)
            ->where('status', 'completed')
            ->where('payment_method', 'qris')
            ->sum('total_amount');

        $posTransfer = (float) PosOrder::whereDate('created_at', $date)
            ->where('status', 'completed')
            ->where('payment_method', 'transfer')
            ->sum('total_amount');

        // 2. Kas Keluar (Outflow)
        $ojekPayoutCash = (float) OjekSettlement::whereDate('settled_at', $date)
            ->where('payment_method', 'cash')
            ->sum('total_amount');

        $operationalExpensesCash = (float) OperationalExpense::active()
            ->whereDate('created_at', $date)
            ->where('payment_method', 'cash')
            ->sum('amount');

        $totalCashOut = $ojekPayoutCash + $operationalExpensesCash;

        // 3. Saldo Kas Riil Fisik di Laci Kasir (Net Cash in Drawer)
        $netCashInDrawer = $totalCashIn - $totalCashOut;

        return [
            'total_transactions' => PosOrder::whereDate('created_at', $date)->where('status', 'completed')->count(),
            'total_revenue' => $totalCashIn + $posQris + $simaksiQris + $posTransfer + $simaksiTransfer,
            'pos_cash' => $posCash,
            'simaksi_cash' => $simaksiCash,
            'cash_total' => $totalCashIn, // Compatibility with existing views
            'total_cash_in' => $totalCashIn,
            'qris_total' => $posQris + $simaksiQris,
            'transfer_total' => $posTransfer + $simaksiTransfer,
            'ojek_payout_cash' => $ojekPayoutCash,
            'operational_expenses_cash' => $operationalExpensesCash,
            'total_cash_out' => $totalCashOut,
            'net_cash_in_drawer' => $netCashInDrawer,
            'void_count' => PosOrder::whereDate('created_at', $date)->where('status', 'voided')->count(),
        ];
    }

    public function void(Request $request, int $id)
    {
        $request->validate([
            'reason' => 'required|string|min:5',
        ]);

        $order = PosOrder::findOrFail($id);

        if ($order->status === 'voided') {
            return back()->with('error', 'Transaksi ini sudah di-void sebelumnya.');
        }

        $this->posService->voidOrder($order, Auth::id(), $request->input('reason'));

        return back()->with('success', "Transaksi {$order->order_number} berhasil di-VOID dan stok barang dikembalikan.");
    }
}
