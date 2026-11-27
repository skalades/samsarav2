@extends('management.layout')

@section('title', 'Laporan Keuangan & Analitik Komprehensif')
@section('page_title', 'Laporan Keuangan & Analitik')
@section('page_subtitle', 'Rekapitulasi arus kas masuk & keluar ' . ($profile['basecamp_name'] ?? 'SAMSARA'))

@section('content')
<div class="space-y-5">

  <!-- 1. HEADER & DATE FILTER CONTROLS -->
  <div class="bg-surface ring-1 ring-line rounded-2xl p-4 sm:p-5 space-y-4">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-4 border-b border-line">
      <div class="min-w-0">
        <div class="flex items-center gap-2">
          <span class="w-2.5 h-2.5 rounded-sm bg-brand shrink-0"></span>
          <h1 class="font-display font-bold text-lg sm:text-xl text-ink uppercase tracking-wide">LAPORAN KEUANGAN &amp; ANALITIK</h1>
        </div>
        <p class="text-xs sm:text-sm text-muted mt-1">
          Arus kas masuk (SIMAKSI, POS, Parkir) &amp; kas keluar (Payout Ojek, Kas Operasional) {{ strtoupper($profile['basecamp_name'] ?? 'SAMSARA') }}.
        </p>
      </div>

      <x-ui.button variant="secondary" icon="printer" :href="route('management.reports.print', ['period' => $period, 'start_date' => $startDate->toDateString(), 'end_date' => $endDate->toDateString()])" target="_blank" class="shrink-0">
        Cetak Laporan (A4)
      </x-ui.button>
    </div>

    <!-- Quick Period Presets & Custom Filter Form -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3 text-xs">
      <div class="flex flex-nowrap lg:flex-wrap overflow-x-auto no-scrollbar items-center gap-1.5 pb-0.5 [&>a]:shrink-0 [&>a]:whitespace-nowrap">
        @foreach([
          'today' => 'Hari Ini',
          '7days' => '7 Hari',
          '30days' => '30 Hari',
          'this_month' => 'Bulan Ini',
          'last_month' => 'Bulan Lalu',
        ] as $pKey => $pLabel)
          <a href="{{ route('management.reports.index', ['period' => $pKey]) }}" class="h-9 px-3 rounded-xl font-semibold flex items-center ring-1 transition {{ $period === $pKey ? 'bg-brand text-white ring-brand' : 'bg-raised text-muted ring-line hover:text-ink' }}">
            {{ $pLabel }}
          </a>
        @endforeach
      </div>

      <form method="GET" action="{{ route('management.reports.index') }}" class="grid grid-cols-1 sm:grid-cols-[1fr_1fr_auto] gap-2 w-full lg:w-auto">
        <input type="hidden" name="period" value="custom">
        <label class="flex items-center gap-2 bg-raised ring-1 ring-line rounded-xl px-3 h-10">
          <span class="text-[11px] text-subtle uppercase shrink-0">Dari</span>
          <input type="date" name="start_date" value="{{ $startDate->toDateString() }}" class="bg-transparent text-ink text-xs sm:text-sm w-full focus:outline-none">
        </label>
        <label class="flex items-center gap-2 bg-raised ring-1 ring-line rounded-xl px-3 h-10">
          <span class="text-[11px] text-subtle uppercase shrink-0">Sampai</span>
          <input type="date" name="end_date" value="{{ $endDate->toDateString() }}" class="bg-transparent text-ink text-xs sm:text-sm w-full focus:outline-none">
        </label>
        <x-ui.button type="submit" variant="secondary" class="h-10">Terapkan</x-ui.button>
      </form>
    </div>
  </div>

  <!-- 2. EXECUTIVE METRICS KPI (4 CARDS) -->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
    <x-ui.stat label="Total pemasukan kas" :value="'Rp '.number_format($summary['total_inflow'], 0, ',', '.')" :hint="$summary['total_transactions'].' transaksi · '.$summary['total_climbers'].' pendaki'" tone="success" icon="cash" />
    <x-ui.stat label="Total pengeluaran kas" :value="'Rp '.number_format($summary['total_outflow'], 0, ',', '.')" :hint="'Ojek Rp '.number_format($summary['ojek_payouts_total'], 0, ',', '.').' · Biaya Rp '.number_format($summary['operational_expenses_total'], 0, ',', '.')" tone="danger" icon="wallet" />
    <x-ui.stat label="Laba bersih operasional" :value="($summary['net_profit'] < 0 ? '−' : '').'Rp '.number_format(abs($summary['net_profit']), 0, ',', '.')" :hint="$summary['profit_margin'].'% margin operasional'" :tone="$summary['net_profit'] >= 0 ? 'brand' : 'danger'" icon="chart" />
    <x-ui.stat label="Kas fisik laci (net)" :value="'Rp '.number_format($summary['net_cash_in_drawer'], 0, ',', '.')" :hint="'Masuk Rp '.number_format($summary['inflow_channels']['cash'], 0, ',', '.').' · Keluar −Rp '.number_format($summary['outflow_channels']['cash'], 0, ',', '.')" tone="info" icon="shield" />
  </div>

  <!-- 3. VISUAL DAILY TREND ANALYTICS CHART -->
  <x-ui.card title="Tren Harian: Pemasukan vs Pengeluaran" icon="chart">
    <x-slot:actions>
      <div class="flex items-center gap-3 text-[11px] text-muted">
        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-sm bg-emerald-500"></span> Masuk</span>
        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-sm bg-rose-500"></span> Keluar</span>
      </div>
    </x-slot:actions>

    <div class="overflow-x-auto pt-2 pb-1">
      <div class="min-w-[560px] h-56 flex flex-col justify-end space-y-2">
        <div class="flex-1 flex items-end gap-2 border-b border-line-strong px-2 pb-1">
          @foreach($trends['series'] as $day)
            @php
              $inflowHeight = $trends['max_value'] > 0 ? max(4, round(($day['inflow'] / $trends['max_value']) * 170)) : 4;
              $outflowHeight = $trends['max_value'] > 0 ? max(4, round(($day['outflow'] / $trends['max_value']) * 170)) : 4;
            @endphp
            <div class="flex-1 flex flex-col items-center justify-end group relative h-full">
              <div class="absolute -top-14 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none bg-canvas border border-line-strong rounded-lg p-2 text-center shadow-2xl z-20 whitespace-nowrap text-[11px] tabular-nums">
                <div class="font-bold text-ink mb-0.5">{{ $day['day_name'] }}, {{ $day['label'] }}</div>
                <div class="text-emerald-400">Masuk: Rp {{ number_format($day['inflow'], 0, ',', '.') }}</div>
                <div class="text-rose-400">Keluar: Rp {{ number_format($day['outflow'], 0, ',', '.') }}</div>
                <div class="text-brand font-bold">Net: Rp {{ number_format($day['net'], 0, ',', '.') }}</div>
              </div>

              <div class="w-full flex items-end justify-center gap-1">
                <div style="height: {{ $inflowHeight }}px;" class="w-1/2 max-w-[14px] bg-emerald-500/85 hover:bg-emerald-400 rounded-t-sm transition-all"></div>
                <div style="height: {{ $outflowHeight }}px;" class="w-1/2 max-w-[14px] bg-rose-500/85 hover:bg-rose-400 rounded-t-sm transition-all"></div>
              </div>

              <div class="mt-2 text-[10px] text-muted group-hover:text-ink transition tabular-nums">
                {{ $day['label'] }}
              </div>
            </div>
          @endforeach
        </div>
      </div>
    </div>
  </x-ui.card>

  <!-- 4. BREAKDOWN ANALYTICS (2 COLUMNS) -->
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

    <!-- Card A: Saluran Pembayaran & Sumber Pemasukan -->
    <x-ui.card title="Struktur Pemasukan & Kanal" icon="cash">
      <x-slot:actions>
        <span class="text-xs font-bold text-emerald-400 tabular-nums">Rp {{ number_format($summary['total_inflow'], 0, ',', '.') }}</span>
      </x-slot:actions>

      <div class="space-y-3 text-xs">
        <span class="text-[11px] text-subtle uppercase tracking-wider font-semibold block">Kanal Pembayaran Diterima</span>
        <div class="space-y-2.5">
          @php
            $cashPct = $summary['total_inflow'] > 0 ? round(($summary['inflow_channels']['cash'] / $summary['total_inflow']) * 100) : 0;
            $qrisPct = $summary['total_inflow'] > 0 ? round(($summary['inflow_channels']['qris'] / $summary['total_inflow']) * 100) : 0;
            $tfPct = $summary['total_inflow'] > 0 ? round(($summary['inflow_channels']['transfer'] / $summary['total_inflow']) * 100) : 0;
          @endphp
          <div>
            <div class="flex justify-between mb-1"><span class="text-muted flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-amber-400"></span>Uang Tunai (Cash Posko)</span><span class="font-bold text-ink tabular-nums">Rp {{ number_format($summary['inflow_channels']['cash'], 0, ',', '.') }}</span></div>
            <div class="w-full bg-raised rounded-full h-1.5 overflow-hidden"><div class="bg-amber-400 h-1.5 rounded-full" style="width: {{ $cashPct }}%;"></div></div>
          </div>
          <div>
            <div class="flex justify-between mb-1"><span class="text-muted flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-cyan-400"></span>QRIS Resmi Basecamp</span><span class="font-bold text-ink tabular-nums">Rp {{ number_format($summary['inflow_channels']['qris'], 0, ',', '.') }}</span></div>
            <div class="w-full bg-raised rounded-full h-1.5 overflow-hidden"><div class="bg-cyan-400 h-1.5 rounded-full" style="width: {{ $qrisPct }}%;"></div></div>
          </div>
          <div>
            <div class="flex justify-between mb-1"><span class="text-muted flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-blue-400"></span>Transfer Bank {{ $profile['payment_bank_name'] ?? 'Resmi' }}</span><span class="font-bold text-ink tabular-nums">Rp {{ number_format($summary['inflow_channels']['transfer'], 0, ',', '.') }}</span></div>
            <div class="w-full bg-raised rounded-full h-1.5 overflow-hidden"><div class="bg-blue-400 h-1.5 rounded-full" style="width: {{ $tfPct }}%;"></div></div>
          </div>
        </div>
      </div>

      <div class="mt-4 pt-4 border-t border-line space-y-2.5 text-xs">
        <span class="text-[11px] text-subtle uppercase tracking-wider font-semibold block">Rincian Sumber Pendapatan</span>
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
          <div class="p-2.5 rounded-xl bg-raised/70 ring-1 ring-line">
            <span class="text-[11px] text-muted block truncate">Izin Masuk SIMAKSI</span>
            <span class="font-bold text-ink block mt-0.5 tabular-nums">Rp {{ number_format($summary['revenue_stream_breakdown']['simaksi_permit'], 0, ',', '.') }}</span>
            <span class="text-[10px] text-subtle block truncate">{{ $summary['total_climbers'] ?? 0 }} Pendaki</span>
          </div>
          <div class="p-2.5 rounded-xl bg-raised/70 ring-1 ring-line">
            <span class="text-[11px] text-muted block truncate">Parkir Motor</span>
            <span class="font-bold text-amber-400 block mt-0.5 tabular-nums">Rp {{ number_format($summary['revenue_stream_breakdown']['parking_motor'] ?? 0, 0, ',', '.') }}</span>
            <span class="text-[10px] text-subtle block truncate">{{ $summary['total_motorcycles'] ?? 0 }} Unit (@ Rp {{ number_format($summary['tariff_rates']['motorcycle'] ?? 10000, 0, ',', '.') }})</span>
          </div>
          <div class="p-2.5 rounded-xl bg-raised/70 ring-1 ring-line">
            <span class="text-[11px] text-muted block truncate">Parkir Mobil</span>
            <span class="font-bold text-cyan-400 block mt-0.5 tabular-nums">Rp {{ number_format($summary['revenue_stream_breakdown']['parking_car'] ?? 0, 0, ',', '.') }}</span>
            <span class="text-[10px] text-subtle block truncate">{{ $summary['total_cars'] ?? 0 }} Unit (@ Rp {{ number_format($summary['tariff_rates']['car'] ?? 25000, 0, ',', '.') }})</span>
          </div>
          <div class="p-2.5 rounded-xl bg-raised/70 ring-1 ring-line">
            <span class="text-[11px] text-muted block truncate">Shuttle Ojek</span>
            <span class="font-bold text-ink block mt-0.5 tabular-nums">Rp {{ number_format($summary['revenue_stream_breakdown']['ojek_shuttle'], 0, ',', '.') }}</span>
            <span class="text-[10px] text-subtle block truncate">{{ $summary['total_ojek_trips_settled'] ?? 0 }} Trip</span>
          </div>
          <div class="p-2.5 rounded-xl bg-raised/70 ring-1 ring-line">
            <span class="text-[11px] text-muted block truncate">POS Logistik &amp; Sewa</span>
            <span class="font-bold text-ink block mt-0.5 tabular-nums">Rp {{ number_format($summary['revenue_stream_breakdown']['pos_retail_and_rentals'], 0, ',', '.') }}</span>
            <span class="text-[10px] text-subtle block truncate">Retail &amp; Logistik</span>
          </div>
          <div class="p-2.5 rounded-xl bg-raised/70 ring-1 ring-line">
            <span class="text-[11px] text-muted block truncate">Jaminan Hangus</span>
            <span class="font-bold text-emerald-400 block mt-0.5 tabular-nums">Rp {{ number_format($summary['revenue_stream_breakdown']['forfeited_deposit'], 0, ',', '.') }}</span>
            <span class="text-[10px] text-subtle block truncate">Kas Jalur</span>
          </div>
        </div>
      </div>
    </x-ui.card>

    <!-- Card B: Komposisi Beban Pengeluaran -->
    <x-ui.card title="Komposisi Pengeluaran" icon="wallet">
      <x-slot:actions>
        <span class="text-xs font-bold text-rose-400 tabular-nums">Rp {{ number_format($summary['total_outflow'], 0, ',', '.') }}</span>
      </x-slot:actions>

      @php
        $ojkPct = $summary['total_outflow'] > 0 ? round(($summary['ojek_payouts_total'] / $summary['total_outflow']) * 100) : 0;
        $bbmPct = $summary['total_outflow'] > 0 ? round(($summary['expense_categories']['bbm_genset'] / $summary['total_outflow']) * 100) : 0;
        $sarPct = $summary['total_outflow'] > 0 ? round(($summary['expense_categories']['logistik_sar'] / $summary['total_outflow']) * 100) : 0;
        $opsPct = $summary['total_outflow'] > 0 ? round((($summary['expense_categories']['operasional_pos'] + $summary['expense_categories']['konsumsi_petugas']) / $summary['total_outflow']) * 100) : 0;
        $cleanPct = $summary['total_outflow'] > 0 ? round((($summary['expense_categories']['kebersihan_sampah'] + $summary['expense_categories']['perbaikan_alat'] + $summary['expense_categories']['lainnya']) / $summary['total_outflow']) * 100) : 0;
      @endphp

      <div class="space-y-2.5 text-xs">
        <div>
          <div class="flex justify-between mb-1"><span class="text-muted flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-amber-400"></span>Honor Driver Ojek ({{ $summary['total_ojek_trips_settled'] }} Trip)</span><span class="font-bold text-amber-400 tabular-nums">Rp {{ number_format($summary['ojek_payouts_total'], 0, ',', '.') }}</span></div>
          <div class="w-full bg-raised rounded-full h-1.5 overflow-hidden"><div class="bg-amber-400 h-1.5 rounded-full" style="width: {{ $ojkPct }}%;"></div></div>
        </div>
        <div>
          <div class="flex justify-between mb-1"><span class="text-muted flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-rose-400"></span>BBM Genset &amp; Penerangan</span><span class="font-bold text-ink tabular-nums">Rp {{ number_format($summary['expense_categories']['bbm_genset'], 0, ',', '.') }}</span></div>
          <div class="w-full bg-raised rounded-full h-1.5 overflow-hidden"><div class="bg-rose-400 h-1.5 rounded-full" style="width: {{ $bbmPct }}%;"></div></div>
        </div>
        <div>
          <div class="flex justify-between mb-1"><span class="text-muted flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-purple-400"></span>P3K &amp; Logistik Darurat SAR</span><span class="font-bold text-ink tabular-nums">Rp {{ number_format($summary['expense_categories']['logistik_sar'], 0, ',', '.') }}</span></div>
          <div class="w-full bg-raised rounded-full h-1.5 overflow-hidden"><div class="bg-purple-400 h-1.5 rounded-full" style="width: {{ $sarPct }}%;"></div></div>
        </div>
        <div>
          <div class="flex justify-between mb-1"><span class="text-muted flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-cyan-400"></span>Operasional Pos &amp; Konsumsi</span><span class="font-bold text-ink tabular-nums">Rp {{ number_format($summary['expense_categories']['operasional_pos'] + $summary['expense_categories']['konsumsi_petugas'], 0, ',', '.') }}</span></div>
          <div class="w-full bg-raised rounded-full h-1.5 overflow-hidden"><div class="bg-cyan-400 h-1.5 rounded-full" style="width: {{ $opsPct }}%;"></div></div>
        </div>
        <div>
          <div class="flex justify-between mb-1"><span class="text-muted flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-emerald-400"></span>Kebersihan &amp; Pemeliharaan</span><span class="font-bold text-ink tabular-nums">Rp {{ number_format($summary['expense_categories']['kebersihan_sampah'] + $summary['expense_categories']['perbaikan_alat'] + $summary['expense_categories']['lainnya'], 0, ',', '.') }}</span></div>
          <div class="w-full bg-raised rounded-full h-1.5 overflow-hidden"><div class="bg-emerald-400 h-1.5 rounded-full" style="width: {{ $cleanPct }}%;"></div></div>
        </div>
      </div>

      <div class="mt-4 pt-3 border-t border-line flex flex-wrap items-center justify-between gap-2 text-xs text-muted tabular-nums">
        <span>Tunai: <strong class="text-amber-400">Rp {{ number_format($summary['outflow_channels']['cash'], 0, ',', '.') }}</strong></span>
        <span>Transfer: <strong class="text-blue-400">Rp {{ number_format($summary['outflow_channels']['transfer'], 0, ',', '.') }}</strong></span>
      </div>
    </x-ui.card>

  </div>

  <!-- 5. JURNAL BUKU KAS LENGKAP -->
  <x-ui.card title="Jurnal Buku Kas &amp; Mutasi Terpadu" subtitle="Riwayat kronologis lengkap kas masuk &amp; kas keluar" icon="receipt" :padding="false">
    {{-- Mobile: kartu --}}
    <div class="lg:hidden divide-y divide-line">
      @forelse($ledger as $entry)
        <div class="p-3.5 space-y-1.5">
          <div class="flex items-start justify-between gap-2">
            <div class="min-w-0">
              <div class="flex flex-wrap items-center gap-1.5">
                <x-ui.badge :tone="$entry['type'] === 'IN' ? 'success' : 'danger'" size="xs">{{ $entry['type'] === 'IN' ? '+ MASUK' : '− KELUAR' }}</x-ui.badge>
                <span class="font-code text-xs font-bold {{ $entry['type'] === 'IN' ? 'text-emerald-400' : 'text-rose-400' }}">{{ $entry['code'] }}</span>
              </div>
              <p class="text-xs font-semibold text-ink mt-1">{{ $entry['category_group'] }}</p>
              <p class="text-xs text-muted line-clamp-2">{{ $entry['description'] }}</p>
              <p class="text-[11px] text-subtle mt-1">{{ $entry['datetime']->format('d/m/Y H:i') }} · {{ $entry['actor'] }} · {{ strtoupper($entry['payment_method']) }}</p>
            </div>
            <div class="text-right shrink-0">
              <p class="font-display font-bold text-sm tabular-nums {{ $entry['type'] === 'IN' ? 'text-emerald-400' : 'text-rose-400' }}">
                {{ $entry['type'] === 'IN' ? '+' : '−' }} {{ rupiah($entry['amount']) }}
              </p>
              @if(!empty($entry['url']))
                <a href="{{ $entry['url'] }}" target="_blank" class="inline-block mt-1 text-[11px] text-brand hover:underline font-semibold">Detail ↗</a>
              @endif
            </div>
          </div>
        </div>
      @empty
        <x-ui.empty icon="receipt" title="Belum ada transaksi arus kas" text="Pilih rentang tanggal lain untuk melihat mutasi." />
      @endforelse
    </div>

    {{-- Desktop: tabel --}}
    <div class="hidden lg:block overflow-x-auto">
      <table class="w-full text-left text-sm">
        <thead class="bg-raised/40 border-b border-line text-[11px] text-subtle uppercase">
          <tr>
            <th class="py-3 px-4 font-semibold">Waktu</th>
            <th class="py-3 px-3 font-semibold text-center">Tipe</th>
            <th class="py-3 px-3 font-semibold">Kode</th>
            <th class="py-3 px-3 font-semibold">Kategori &amp; Keterangan</th>
            <th class="py-3 px-3 font-semibold text-center">Kanal</th>
            <th class="py-3 px-3 font-semibold text-right">Nominal</th>
            <th class="py-3 px-3 font-semibold">Petugas</th>
            <th class="py-3 px-4 font-semibold text-center">Bukti</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-line">
          @forelse($ledger as $entry)
            <tr class="hover:bg-hover/40">
              <td class="py-3 px-4 text-muted text-xs whitespace-nowrap tabular-nums">{{ $entry['datetime']->format('d/m/Y H:i') }}</td>
              <td class="py-3 px-3 text-center whitespace-nowrap">
                <x-ui.badge :tone="$entry['type'] === 'IN' ? 'success' : 'danger'" size="xs">{{ $entry['type'] === 'IN' ? '+ MASUK' : '− KELUAR' }}</x-ui.badge>
              </td>
              <td class="py-3 px-3 font-code font-bold whitespace-nowrap {{ $entry['type'] === 'IN' ? 'text-emerald-400' : 'text-rose-400' }}">{{ $entry['code'] }}</td>
              <td class="py-3 px-3">
                <x-ui.badge size="xs">{{ $entry['category_group'] }}</x-ui.badge>
                <div class="text-xs text-muted mt-1">{{ $entry['description'] }}</div>
              </td>
              <td class="py-3 px-3 text-center whitespace-nowrap">
                <x-ui.badge :tone="$entry['payment_method'] === 'cash' ? 'warning' : ($entry['payment_method'] === 'qris' ? 'info' : 'neutral')" size="xs">{{ strtoupper($entry['payment_method']) }}</x-ui.badge>
              </td>
              <td class="py-3 px-3 text-right font-bold tabular-nums whitespace-nowrap {{ $entry['type'] === 'IN' ? 'text-emerald-400' : 'text-rose-400' }}">
                {{ $entry['type'] === 'IN' ? '+' : '−' }} {{ rupiah($entry['amount']) }}
              </td>
              <td class="py-3 px-3 text-muted text-xs">{{ $entry['actor'] }}</td>
              <td class="py-3 px-4 text-center">
                @if(!empty($entry['url']))
                  <a href="{{ $entry['url'] }}" target="_blank" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-raised ring-1 ring-line text-muted hover:text-ink" title="Buka Detail Bukti">
                    <x-ui.icon name="external" class="w-4 h-4" />
                  </a>
                @else
                  <span class="text-subtle">—</span>
                @endif
              </td>
            </tr>
          @empty
            <tr><td colspan="8"><x-ui.empty icon="receipt" title="Belum ada transaksi arus kas" /></td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </x-ui.card>

</div>
@endsection
