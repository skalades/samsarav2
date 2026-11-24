<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\OjekSettlement;
use App\Models\OperationalExpense;
use App\Models\PosOrder;
use App\Models\PosOrderItem;
use App\Models\SimaksiRegistration;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class ReportService
{
    /**
     * Get aggregate financial summary (Inflow, Outflow, Net Margin, and Channel Breakdown).
     */
    public function getFinancialSummary(Carbon $startDate, Carbon $endDate): array
    {
        $start = $startDate->copy()->startOfDay();
        $end = $endDate->copy()->endOfDay();

        // =========================================================================
        // 1. PEMASUKAN (INFLOWS)
        // =========================================================================

        // A. POS Orders (Completed)
        $posOrders = PosOrder::whereBetween('created_at', [$start, $end])
            ->where('status', 'completed')
            ->get();

        $posRevenue = (float) $posOrders->sum('total_amount');
        $posCash = (float) $posOrders->where('payment_method', 'cash')->sum('total_amount');
        $posQris = (float) $posOrders->where('payment_method', 'qris')->sum('total_amount');
        $posTransfer = (float) $posOrders->where('payment_method', 'transfer')->sum('total_amount');

        // B. Direct SIMAKSI Registrations (Paid without linked POS Order)
        $simaksiDirect = SimaksiRegistration::whereBetween('paid_at', [$start, $end])
            ->whereIn('status', \App\Models\SimaksiRegistration::REVENUE_STATUSES)
            ->whereDoesntHave('posOrders')
            ->get();

        $simaksiDirectRevenue = (float) $simaksiDirect->sum('total_amount');
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

        // C. Forfeited Trash Deposits (Masuk ke Kas Operasional Pembersihan Jalur)
        $forfeitedDeposits = (float) SimaksiRegistration::whereBetween('checked_out_at', [$start, $end])
            ->where('trash_deposit_status', 'forfeited')
            ->sum('trash_deposit_amount');

        // Total Inflow
        $totalInflow = $posRevenue + $simaksiDirectRevenue + $forfeitedDeposits;

        // Inflow Channels
        $inflowChannels = [
            'cash' => $posCash + $simaksiCash,
            'qris' => $posQris + $simaksiQris,
            'transfer' => $posTransfer + $simaksiTransfer,
        ];

        // Revenue Breakdown by Service Source
        $simaksiFeeTotal = (float) $simaksiDirect->sum('simaksi_fee');
        $ojekFeeDirect = (float) $simaksiDirect->sum('ojek_fee');

        $tariffs = AppSetting::getTariffs();
        $motorRate = (float) ($tariffs['parking_motor_fee'] ?? 10000);
        $carRate = (float) ($tariffs['parking_car_fee'] ?? 25000);

        // Calculate SIMAKSI Parking breakdown (Motorcycle vs Car)
        $simaksiMotorFee = 0.0;
        $simaksiCarFee = 0.0;
        foreach ($simaksiDirect as $reg) {
            $fee = (float) $reg->parking_fee;
            if ($fee <= 0) {
                continue;
            }

            $mCount = (int) $reg->motorcycle_count;
            $cCount = (int) $reg->car_count;

            if ($mCount > 0 && $cCount === 0) {
                $simaksiMotorFee += $fee;
            } elseif ($cCount > 0 && $mCount === 0) {
                $simaksiCarFee += $fee;
            } elseif ($mCount > 0 && $cCount > 0) {
                $calcMotor = $mCount * $motorRate;
                $calcCar = $cCount * $carRate;
                $calcTotal = $calcMotor + $calcCar;
                if ($calcTotal > 0) {
                    $motorShare = round(($calcMotor / $calcTotal) * $fee);
                    $simaksiMotorFee += $motorShare;
                    $simaksiCarFee += ($fee - $motorShare);
                } else {
                    $simaksiMotorFee += $fee;
                }
            } else {
                $simaksiMotorFee += $fee;
            }
        }

        // Check if any POS orders contain parking services
        $posParkingItems = PosOrderItem::with('inventoryItem')
            ->whereIn('pos_order_id', function ($q) use ($start, $end) {
                $q->select('id')->from('pos_orders')->whereBetween('created_at', [$start, $end])->where('status', 'completed');
            })
            ->where(function ($q) {
                $q->where('category', 'parkir')
                    ->orWhere('item_name', 'like', '%parkir%');
            })
            ->get();

        $posMotorFee = 0.0;
        $posCarFee = 0.0;
        $posMotorCount = 0;
        $posCarCount = 0;

        foreach ($posParkingItems as $item) {
            $subtotal = (float) $item->subtotal;
            $sku = $item->inventoryItem?->sku ?? '';
            $name = strtolower($item->item_name);

            if ($sku === 'PRK-MOBIL' || str_contains($name, 'mobil') || str_contains($name, 'roda 4')) {
                $posCarFee += $subtotal;
                $posCarCount += (int) $item->quantity;
            } else {
                $posMotorFee += $subtotal;
                $posMotorCount += (int) $item->quantity;
            }
        }

        $parkingMotorTotal = $simaksiMotorFee + $posMotorFee;
        $parkingCarTotal = $simaksiCarFee + $posCarFee;
        $parkingFeeTotal = $parkingMotorTotal + $parkingCarTotal;
        $posParkingFee = $posMotorFee + $posCarFee;
        $posRetailAndRentals = max(0, $posRevenue - $posParkingFee);

        $revenueStreamBreakdown = [
            'simaksi_permit' => $simaksiFeeTotal,
            'parking_motor' => $parkingMotorTotal,
            'parking_car' => $parkingCarTotal,
            'vehicle_parking' => $parkingFeeTotal,
            'pos_retail_and_rentals' => $posRetailAndRentals,
            'ojek_shuttle' => $ojekFeeDirect,
            'forfeited_deposit' => $forfeitedDeposits,
        ];

        // =========================================================================
        // 2. PENGELUARAN (OUTFLOWS)
        // =========================================================================

        // A. Ojek Driver Payouts
        $ojekSettlements = OjekSettlement::whereBetween('settled_at', [$start, $end])->get();
        $ojekPayoutsTotal = (float) $ojekSettlements->sum('total_amount');
        $ojekPayoutsCash = (float) $ojekSettlements->where('payment_method', 'cash')->sum('total_amount');
        $ojekPayoutsTransfer = (float) $ojekSettlements->where('payment_method', 'transfer')->sum('total_amount');

        // B. Operational Expenses (Active / Non-Voided)
        $operationalExpenses = OperationalExpense::active()
            ->whereBetween('created_at', [$start, $end])
            ->get();

        $operationalExpensesTotal = (float) $operationalExpenses->sum('amount');
        $operationalExpensesCash = (float) $operationalExpenses->where('payment_method', 'cash')->sum('amount');
        $operationalExpensesTransfer = (float) $operationalExpenses->where('payment_method', 'transfer')->sum('amount');

        // Operational Expenses by Category
        $expenseCategories = [
            'bbm_genset' => (float) $operationalExpenses->where('category', 'bbm_genset')->sum('amount'),
            'operasional_pos' => (float) $operationalExpenses->where('category', 'operasional_pos')->sum('amount'),
            'logistik_sar' => (float) $operationalExpenses->where('category', 'logistik_sar')->sum('amount'),
            'konsumsi_petugas' => (float) $operationalExpenses->where('category', 'konsumsi_petugas')->sum('amount'),
            'kebersihan_sampah' => (float) $operationalExpenses->where('category', 'kebersihan_sampah')->sum('amount'),
            'perbaikan_alat' => (float) $operationalExpenses->where('category', 'perbaikan_alat')->sum('amount'),
            'lainnya' => (float) $operationalExpenses->where('category', 'lainnya')->sum('amount'),
        ];

        // Total Outflow
        $totalOutflow = $ojekPayoutsTotal + $operationalExpensesTotal;

        // Outflow Channels
        $outflowChannels = [
            'cash' => $ojekPayoutsCash + $operationalExpensesCash,
            'transfer' => $ojekPayoutsTransfer + $operationalExpensesTransfer,
        ];

        // =========================================================================
        // 3. LABA BERSIH & NET METRICS
        // =========================================================================
        $netProfit = $totalInflow - $totalOutflow;
        $profitMargin = $totalInflow > 0 ? round(($netProfit / $totalInflow) * 100, 1) : 0;
        $netCashInDrawer = $inflowChannels['cash'] - $outflowChannels['cash'];

        return [
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'total_inflow' => $totalInflow,
            'total_outflow' => $totalOutflow,
            'net_profit' => $netProfit,
            'profit_margin' => $profitMargin,
            'net_cash_in_drawer' => $netCashInDrawer,
            'pos_revenue' => $posRevenue,
            'simaksi_direct_revenue' => $simaksiDirectRevenue,
            'forfeited_deposits' => $forfeitedDeposits,
            'ojek_payouts_total' => $ojekPayoutsTotal,
            'operational_expenses_total' => $operationalExpensesTotal,
            'inflow_channels' => $inflowChannels,
            'outflow_channels' => $outflowChannels,
            'revenue_stream_breakdown' => $revenueStreamBreakdown,
            'expense_categories' => $expenseCategories,
            'total_transactions' => $posOrders->count() + $simaksiDirect->count(),
            'total_climbers' => (int) $simaksiDirect->sum('total_climbers'),
            'total_motorcycles' => (int) $simaksiDirect->sum('motorcycle_count') + $posMotorCount,
            'total_cars' => (int) $simaksiDirect->sum('car_count') + $posCarCount,
            'tariff_rates' => [
                'motorcycle' => $motorRate,
                'car' => $carRate,
            ],
            'total_ojek_trips_settled' => (int) $ojekSettlements->sum('total_trips'),
            'total_expenses_recorded' => $operationalExpenses->count(),
        ];
    }

    /**
     * Generate daily series for interactive visualization & charts.
     */
    public function getDailyTrendAnalytics(Carbon $startDate, Carbon $endDate): array
    {
        $start = $startDate->copy()->startOfDay();
        $end = $endDate->copy()->endOfDay();

        $period = CarbonPeriod::create($start, '1 day', $end);
        $dailyTrends = [];
        $maxInflow = 0;
        $maxOutflow = 0;

        // Pre-aggregate daily totals using single SQL queries with GROUP BY DATE (Eliminates N+1 query loop)
        $posByDate = PosOrder::whereBetween('created_at', [$start, $end])
            ->where('status', 'completed')
            ->selectRaw('DATE(created_at) as dt, SUM(total_amount) as total')
            ->groupBy('dt')
            ->pluck('total', 'dt')
            ->toArray();

        $simaksiByDate = SimaksiRegistration::whereBetween('paid_at', [$start, $end])
            ->whereIn('status', \App\Models\SimaksiRegistration::REVENUE_STATUSES)
            ->whereDoesntHave('posOrders')
            ->selectRaw('DATE(paid_at) as dt, SUM(total_amount) as total')
            ->groupBy('dt')
            ->pluck('total', 'dt')
            ->toArray();

        $ojekByDate = OjekSettlement::whereBetween('settled_at', [$start, $end])
            ->selectRaw('DATE(settled_at) as dt, SUM(total_amount) as total')
            ->groupBy('dt')
            ->pluck('total', 'dt')
            ->toArray();

        $expenseByDate = OperationalExpense::active()
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('DATE(created_at) as dt, SUM(amount) as total')
            ->groupBy('dt')
            ->pluck('total', 'dt')
            ->toArray();

        foreach ($period as $date) {
            $key = $date->format('Y-m-d');

            $pos = (float) ($posByDate[$key] ?? 0);
            $simaksi = (float) ($simaksiByDate[$key] ?? 0);
            $inflow = $pos + $simaksi;

            $ojek = (float) ($ojekByDate[$key] ?? 0);
            $expense = (float) ($expenseByDate[$key] ?? 0);
            $outflow = $ojek + $expense;
            $net = $inflow - $outflow;

            if ($inflow > $maxInflow) {
                $maxInflow = $inflow;
            }
            if ($outflow > $maxOutflow) {
                $maxOutflow = $outflow;
            }

            $dailyTrends[] = [
                'date' => $key,
                'label' => $date->format('d M'),
                'day_name' => $date->translatedFormat('l'),
                'inflow' => $inflow,
                'outflow' => $outflow,
                'net' => $net,
            ];
        }

        $chartMax = max(100000, max($maxInflow, $maxOutflow));

        return [
            'series' => $dailyTrends,
            'max_value' => $chartMax,
            'days_count' => count($dailyTrends),
        ];
    }

    /**
     * Get a unified chronological cash journal (Ledger Kas Terpadu).
     */
    public function getUnifiedCashLedger(Carbon $startDate, Carbon $endDate, int $limit = 100): array
    {
        $start = $startDate->copy()->startOfDay();
        $end = $endDate->copy()->endOfDay();

        $ledger = [];

        // 1. POS Completed Orders (+)
        $posOrders = PosOrder::with('cashier')
            ->whereBetween('created_at', [$start, $end])
            ->where('status', 'completed')
            ->get();

        foreach ($posOrders as $order) {
            $ledger[] = [
                'datetime' => $order->created_at,
                'type' => 'IN',
                'category_group' => 'POS KASIR',
                'category_label' => 'Penjualan / Sewa Logistik Basecamp',
                'code' => $order->order_number,
                'description' => "Transaksi POS kasir {$order->order_number} ({$order->customer_name})",
                'payment_method' => $order->payment_method,
                'amount' => (float) $order->total_amount,
                'actor' => $order->cashier?->name ?? 'Kasir Posko',
                'url' => route('management.pos.receipt', $order->id),
            ];
        }

        // 2. Direct SIMAKSI Registrations (+)
        $simaksi = SimaksiRegistration::with('reviewer')
            ->whereBetween('paid_at', [$start, $end])
            ->whereIn('status', \App\Models\SimaksiRegistration::REVENUE_STATUSES)
            ->whereDoesntHave('posOrders')
            ->get();

        foreach ($simaksi as $reg) {
            $vehicleNote = '';
            if ($reg->parking_fee > 0) {
                $vehicleParts = [];
                if ($reg->motorcycle_count > 0) {
                    $vehicleParts[] = "{$reg->motorcycle_count} Motor";
                }
                if ($reg->car_count > 0) {
                    $vehicleParts[] = "{$reg->car_count} Mobil";
                }
                $vehicleNote = ' + Parkir ('.implode(', ', $vehicleParts).')';
            }

            $ledger[] = [
                'datetime' => $reg->paid_at ?? $reg->updated_at,
                'type' => 'IN',
                'category_group' => 'SIMAKSI RESMI',
                'category_label' => 'Izin Masuk Kawasan Konservasi',
                'code' => $reg->registration_code,
                'description' => "Pembayaran SIMAKSI {$reg->registration_code} ({$reg->leader_name} - {$reg->total_climbers} pendaki{$vehicleNote})",
                'payment_method' => $reg->payment_method === 'cash_pos' ? 'cash' : $reg->payment_method,
                'amount' => (float) $reg->total_amount,
                'actor' => $reg->reviewer?->name ?? 'Verifikator SIMAKSI',
                'url' => route('management.simaksi.show', $reg->id),
            ];
        }

        // 3. Forfeited Trash Deposits (+)
        $forfeited = SimaksiRegistration::with('checkInOfficer')
            ->whereBetween('checked_out_at', [$start, $end])
            ->where('trash_deposit_status', 'forfeited')
            ->where('trash_deposit_amount', '>', 0)
            ->get();

        foreach ($forfeited as $f) {
            $ledger[] = [
                'datetime' => $f->checked_out_at,
                'type' => 'IN',
                'category_group' => 'DANA KEBERSIHAN',
                'category_label' => 'Jaminan Sampah Hangus (Forfeited)',
                'code' => 'FORFEIT-'.$f->registration_code,
                'description' => "Jaminan sampah hangus rombongan {$f->leader_name} dialokasikan ke kas pembersihan jalur.",
                'payment_method' => 'cash',
                'amount' => (float) $f->trash_deposit_amount,
                'actor' => $f->checkInOfficer?->name ?? 'Petugas Gate',
                'url' => route('management.simaksi.show', $f->id),
            ];
        }

        // 4. Ojek Driver Payouts (-)
        $settlements = OjekSettlement::with(['driver', 'cashier'])
            ->whereBetween('settled_at', [$start, $end])
            ->get();

        foreach ($settlements as $s) {
            $ledger[] = [
                'datetime' => $s->settled_at,
                'type' => 'OUT',
                'category_group' => 'HONOR OJEK',
                'category_label' => 'Pencairan Payout Pengemudi Ojek',
                'code' => $s->settlement_code,
                'description' => "Pencairan honor ojek {$s->settlement_code} untuk {$s->driver?->name} ({$s->total_trips} trip)",
                'payment_method' => $s->payment_method,
                'amount' => (float) $s->total_amount,
                'actor' => $s->cashier?->name ?? 'Kasir Posko',
                'url' => route('management.ojek.print_settlement', $s->id),
            ];
        }

        // 5. Operational Expenses (-)
        $expenses = OperationalExpense::with(['cashier', 'voidedBy'])
            ->whereBetween('created_at', [$start, $end])
            ->active()
            ->get();

        foreach ($expenses as $exp) {
            $ledger[] = [
                'datetime' => $exp->created_at,
                'type' => 'OUT',
                'category_group' => 'OPERASIONAL',
                'category_label' => $exp->category_label,
                'code' => $exp->expense_code,
                'description' => "{$exp->category_label}: {$exp->notes} (Penerima: {$exp->recipient_name})",
                'payment_method' => $exp->payment_method,
                'amount' => (float) $exp->amount,
                'actor' => $exp->cashier?->name ?? 'Kasir Posko',
                'url' => route('management.pos.expenses.print', $exp->id),
            ];
        }

        // Sort descending by datetime
        usort($ledger, function ($a, $b) {
            return $b['datetime'] <=> $a['datetime'];
        });

        return array_slice($ledger, 0, $limit);
    }
}
