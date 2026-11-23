<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\RentalRecord;
use App\Services\AuditService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function index()
    {
        $items = InventoryItem::orderBy('category')->get();
        $activeRentals = RentalRecord::with(['item', 'handedOfficer'])
            ->where('status', 'rented')
            ->latest()
            ->get();

        $returnedRentals = RentalRecord::with(['item', 'returnedOfficer'])
            ->whereIn('status', ['returned_good', 'damaged', 'lost'])
            ->latest('returned_at')
            ->take(15)
            ->get();

        return view('management.inventory.index', compact('items', 'activeRentals', 'returnedRentals'));
    }

    public function returnRental(Request $request, int $id)
    {
        $request->validate([
            'condition_in' => 'required|string',
            'status' => 'required|string|in:returned_good,damaged,lost',
            'damage_penalty' => 'nullable|numeric|min:0',
        ]);

        $status = $request->input('status');
        $penalty = (float) $request->input('damage_penalty', 0);
        $conditionIn = $request->input('condition_in');

        return DB::transaction(function () use ($request, $id, $status, $penalty, $conditionIn) {
            // Lock rental record to prevent concurrent double-return stock inflation
            $rental = RentalRecord::where('id', $id)->lockForUpdate()->firstOrFail();

            if ($rental->status !== 'rented') {
                return back()->with('error', "Alat sewa ini sudah pernah diproses pengembaliannya (Status: {$rental->status}).");
            }

            $rental->update([
                'status' => $status,
                'condition_in' => $conditionIn,
                'damage_penalty' => $penalty,
                'returned_to' => Auth::id(),
                'returned_at' => Carbon::now(),
                'notes' => $request->input('notes'),
            ]);

            // If returned good or repairable, restore available stock atomically under lock
            if ($status === 'returned_good') {
                $item = InventoryItem::where('id', $rental->inventory_item_id)->lockForUpdate()->first();
                if ($item) {
                    $item->increment('stock_available', $rental->quantity);
                }
            }

            AuditService::log(
                action: 'RETURN_RENTAL_ITEM',
                entityType: 'RentalRecord',
                entityId: $rental->id,
                description: "Pengembalian alat sewa {$rental->item_name} ({$rental->quantity} unit) oleh {$rental->customer_name}. Kondisi: {$conditionIn}.",
                payload: ['status' => $status, 'penalty' => $penalty]
            );

            return back()->with('success', "Pengembalian alat {$rental->item_name} berhasil dicatat.");
        });
    }

    public function updateStock(Request $request, int $id)
    {
        $request->validate([
            'stock_available' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0',
        ]);

        $item = InventoryItem::findOrFail($id);
        $oldStock = $item->stock_available;
        $newStock = (int) $request->input('stock_available');

        $item->update([
            'stock_available' => $newStock,
            'price' => (float) $request->input('price'),
            'is_active' => $request->boolean('is_active'),
        ]);

        AuditService::log(
            action: 'UPDATE_INVENTORY_STOCK',
            entityType: 'InventoryItem',
            entityId: $item->id,
            description: "Penyesuaian stok/harga {$item->name} (SKU: {$item->sku}): stok {$oldStock} -> {$newStock}.",
            payload: ['old' => $oldStock, 'new' => $newStock, 'price' => $item->price]
        );

        return back()->with('success', "Data inventori {$item->name} berhasil diperbarui.");
    }
}
