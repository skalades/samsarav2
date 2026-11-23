<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\InventoryItem;
use App\Models\OjekTrip;
use App\Models\PosOrder;
use App\Models\PosOrderItem;
use App\Models\RentalRecord;
use App\Models\SimaksiRegistration;
use App\Support\CodeGenerator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PosService
{
    public function createOrder(
        int $cashierId,
        string $customerName,
        ?string $customerPhone,
        array $items,
        string $paymentMethod = 'cash',
        ?int $simaksiId = null,
        float $cashTendered = 0,
        ?string $notes = null
    ): PosOrder {
        return DB::transaction(function () use (
            $cashierId, $customerName, $customerPhone, $items, $paymentMethod, $simaksiId, $cashTendered, $notes
        ) {
            $subtotal = 0;
            $orderItemsData = [];

            // Sort items deterministically by inventory_item_id to prevent database deadlocks on concurrent orders
            usort($items, fn ($a, $b) => ((int) $a['inventory_item_id']) <=> ((int) $b['inventory_item_id']));

            foreach ($items as $itemData) {
                $itemId = (int) $itemData['inventory_item_id'];
                $qty = max(1, (int) $itemData['quantity']);

                // Acquire exclusive write lock (FOR UPDATE) on item row to prevent stock lost update & overselling
                $item = InventoryItem::where('id', $itemId)->lockForUpdate()->firstOrFail();

                // Stock validation for physical goods & rentals
                if ($item->type !== 'service' && $item->stock_available < $qty) {
                    throw new \RuntimeException("Stok barang {$item->name} tidak mencukupi (tersedia: {$item->stock_available}).");
                }

                $lineSubtotal = $item->price * $qty;
                $subtotal += $lineSubtotal;

                $orderItemsData[] = [
                    'inventory_item' => $item,
                    'quantity' => $qty,
                    'subtotal' => $lineSubtotal,
                ];

                // Deduct available stock atomically under lock
                if ($item->type !== 'service') {
                    $item->decrement('stock_available', $qty);
                }
            }

            // Generate unique Order Number using micro-entropy (anti-collision)
            $orderNumber = CodeGenerator::posOrderNumber();
            $cashChange = ($paymentMethod === 'cash' && $cashTendered >= $subtotal) ? ($cashTendered - $subtotal) : 0;

            $order = PosOrder::create([
                'order_number' => $orderNumber,
                'cashier_id' => $cashierId,
                'simaksi_registration_id' => $simaksiId,
                'customer_name' => $customerName,
                'customer_phone' => $customerPhone,
                'subtotal' => $subtotal,
                'discount' => 0,
                'total_amount' => $subtotal,
                'cash_tendered' => $cashTendered,
                'cash_change' => $cashChange,
                'payment_method' => $paymentMethod,
                'status' => 'completed',
                'notes' => $notes,
            ]);

            foreach ($orderItemsData as $data) {
                $item = $data['inventory_item'];
                $qty = $data['quantity'];
                $lineSub = $data['subtotal'];

                $orderItem = PosOrderItem::create([
                    'pos_order_id' => $order->id,
                    'inventory_item_id' => $item->id,
                    'item_name' => $item->name,
                    'category' => $item->category,
                    'unit_price' => $item->price,
                    'quantity' => $qty,
                    'subtotal' => $lineSub,
                ]);

                // If rental item, create rental record
                if ($item->type === 'rental') {
                    RentalRecord::create([
                        'inventory_item_id' => $item->id,
                        'simaksi_registration_id' => $simaksiId,
                        'pos_order_id' => $order->id,
                        'customer_name' => $customerName,
                        'item_name' => $item->name,
                        'quantity' => $qty,
                        'condition_out' => 'Bagus / Layak Pakai',
                        'handed_out_by' => $cashierId,
                        'handed_out_at' => Carbon::now(),
                        'status' => 'rented',
                    ]);
                }

                // If ojek shuttle item, issue operational ojek trip vouchers
                if ($item->category === 'shuttle_ojek' || $item->sku === 'SRV-OJEK') {
                    $farePerTrip = (float) $item->price;
                    $basecampShare = (float) AppSetting::get('ojek_basecamp_share', min(5000, $farePerTrip * 0.2));
                    $driverShare = max(0, $farePerTrip - $basecampShare);
                    $routeName = AppSetting::get('ojek_route_name', 'Basecamp → Pos 1');

                    for ($k = 1; $k <= $qty; $k++) {
                        $tripCode = CodeGenerator::ojekTripCode('POS'.$order->id, $k);

                        OjekTrip::create([
                            'trip_code' => $tripCode,
                            'pos_order_id' => $order->id,
                            'simaksi_registration_id' => $simaksiId,
                            'passenger_name' => $customerName.($qty > 1 ? " (#{$k})" : ''),
                            'passenger_phone' => $customerPhone,
                            'route' => $routeName,
                            'fare_amount' => $farePerTrip,
                            'driver_share' => $driverShare,
                            'basecamp_share' => $basecampShare,
                            'payment_status' => 'paid',
                            'trip_status' => 'voucher_issued',
                            'settlement_status' => 'unsettled',
                            'created_by' => $cashierId,
                        ]);
                    }
                }
            }

            if ($simaksiId) {
                $simaksi = SimaksiRegistration::find($simaksiId);
                if ($simaksi) {
                    $simaksi->update([
                        'payment_status' => 'paid',
                        'payment_method' => $paymentMethod,
                        'paid_at' => Carbon::now(),
                        'status' => 'paid',
                    ]);
                }
            }

            AuditService::log(
                action: 'POS_TRANSACTION',
                entityType: 'PosOrder',
                entityId: $order->id,
                description: "Transaksi kasir {$order->order_number} sebesar Rp ".number_format($subtotal, 0, ',', '.')." metode: {$paymentMethod}.",
                payload: ['order_number' => $orderNumber, 'total' => $subtotal, 'items_count' => count($items)]
            );

            return $order;
        });
    }

    public function voidOrder(PosOrder $order, int $officerId, string $reason): PosOrder
    {
        return DB::transaction(function () use ($order, $officerId, $reason) {
            // Lock order row exclusively
            $lockedOrder = PosOrder::where('id', $order->id)->lockForUpdate()->firstOrFail();

            if ($lockedOrder->status === 'voided') {
                throw new \RuntimeException("Transaksi POS {$lockedOrder->order_number} sudah pernah dibatalkan (VOID) sebelumnya.");
            }

            $lockedOrder->update([
                'status' => 'voided',
                'void_reason' => $reason,
                'voided_by' => $officerId,
                'voided_at' => Carbon::now(),
            ]);

            // Restore inventory stock safely under lock
            foreach ($lockedOrder->items as $item) {
                if ($item->inventory_item_id) {
                    $invItem = InventoryItem::where('id', $item->inventory_item_id)->lockForUpdate()->first();
                    if ($invItem && $invItem->type !== 'service') {
                        $invItem->increment('stock_available', $item->quantity);
                    }
                }
            }

            // Cancel associated rental records
            RentalRecord::where('pos_order_id', $lockedOrder->id)->update([
                'status' => 'lost',
                'notes' => 'Transaksi POS dibatalkan (VOID): '.$reason,
            ]);

            AuditService::log(
                action: 'VOID_POS_ORDER',
                entityType: 'PosOrder',
                entityId: $lockedOrder->id,
                description: "Transaksi POS {$lockedOrder->order_number} dibatalkan / di-VOID.",
                reason: $reason,
                payload: ['order_number' => $lockedOrder->order_number, 'amount' => $lockedOrder->total_amount]
            );

            return $lockedOrder;
        });
    }
}
