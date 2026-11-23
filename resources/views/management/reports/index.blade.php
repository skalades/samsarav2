@extends('management.layout')

@section('title', 'Laporan Keuangan & Analitik Komprehensif')

@section('content')
<div class="space-y-6">

  <!-- ======================================================== -->
  <!-- 1. HEADER & DATE FILTER CONTROLS                         -->
  <!-- ======================================================== -->
  <div class="bg-[#121418] border border-[#1E2229] rounded-2xl p-5 shadow-xl space-y-4">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-4 border-b border-[#1E2229]">
      <div>
        <div class="flex items-center gap-2">
          <span class="w-2.5 h-2.5 rounded-sm bg-[#FF5722]"></span>
          <h1 class="font-display font-black text-xl text-white tracking-wide uppercase">LAPORAN KEUANGAN & ANALITIK OPERASIONAL</h1>
        </div>
        <p class="font-mono-tech text-xs text-stone-400 mt-0.5">
          Rekapitulasi resmi arus kas masuk (SIMAKSI, POS, Parkir) & kas keluar (Payout Ojek, Kas Operasional) {{ strtoupper($profile['basecamp_name'] ?? 'SAMSARA') }}.
        </p>
      </div>

      <div class="flex items-center gap-2.5 shrink-0">
        <a href="{{ route('management.reports.print', ['period' => $period, 'start_date' => $startDate->toDateString(), 'end_date' => $endDate->toDateString()]) }}" target="_blank" class="tap-active px-4 py-2 rounded-lg bg-[#181B22] border border-[#2A303C] hover:border-stone-400 text-stone-200 hover:text-white font-mono-tech text-xs font-bold transition flex items-center gap-2 shadow-sm">
          <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
          <span>CETAK LAPORAN RESMI (A4)</span>
        </a>
      </div>
    </div>

    <!-- Quick Period Presets & Custom Filter Form -->
    <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4 font-mono-tech text-xs">
      
      <!-- Quick Range Pills -->
      <div class="flex flex-wrap items-center gap-1.5">
        <span class="text-[10px] text-stone-500 uppercase mr-1">PERIODE:</span>
        <a href="{{ route('management.reports.index', ['period' => 'today']) }}" class="px-3 py-1.5 rounded-lg border transition {{ $period === 'today' ? 'bg-[#FF5722] text-black font-bold border-[#FF5722]' : 'bg-[#16191F] text-stone-300 border-[#262B35] hover:bg-[#1E232D]' }}">
          Hari Ini
        </a>
        <a href="{{ route('management.reports.index', ['period' => '7days']) }}" class="px-3 py-1.5 rounded-lg border transition {{ $period === '7days' ? 'bg-[#FF5722] text-black font-bold border-[#FF5722]' : 'bg-[#16191F] text-stone-300 border-[#262B35] hover:bg-[#1E232D]' }}">
          7 Hari Terakhir
        </a>
        <a href="{{ route('management.reports.index', ['period' => '30days']) }}" class="px-3 py-1.5 rounded-lg border transition {{ $period === '30days' ? 'bg-[#FF5722] text-black font-bold border-[#FF5722]' : 'bg-[#16191F] text-stone-300 border-[#262B35] hover:bg-[#1E232D]' }}">
          30 Hari Terakhir
        </a>
        <a href="{{ route('management.reports.index', ['period' => 'this_month']) }}" class="px-3 py-1.5 rounded-lg border transition {{ $period === 'this_month' ? 'bg-[#FF5722] text-black font-bold border-[#FF5722]' : 'bg-[#16191F] text-stone-300 border-[#262B35] hover:bg-[#1E232D]' }}">
          Bulan Ini
        </a>
        <a href="{{ route('management.reports.index', ['period' => 'last_month']) }}" class="px-3 py-1.5 rounded-lg border transition {{ $period === 'last_month' ? 'bg-[#FF5722] text-black font-bold border-[#FF5722]' : 'bg-[#16191F] text-stone-300 border-[#262B35] hover:bg-[#1E232D]' }}">
          Bulan Lalu
        </a>
      </div>

      <!-- Custom Date Picker Form -->
      <form method="GET" action="{{ route('management.reports.index') }}" class="flex items-center gap-2 w-full lg:w-auto">
        <input type="hidden" name="period" value="custom">
        <div class="flex items-center gap-1.5 bg-[#16191F] border border-[#262B35] rounded-lg px-2.5 py-1">
          <span class="text-[10px] text-stone-500 uppercase">DARI:</span>
          <input type="date" name="start_date" value="{{ $startDate->toDateString() }}" class="bg-transparent text-white text-xs focus:outline-none">
        </div>
        <span class="text-stone-600 font-bold">-</span>
        <div class="flex items-center gap-1.5 bg-[#16191F] border border-[#262B35] rounded-lg px-2.5 py-1">
          <span class="text-[10px] text-stone-500 uppercase">SAMPAI:</span>
          <input type="date" name="end_date" value="{{ $endDate->toDateString() }}" class="bg-transparent text-white text-xs focus:outline-none">
        </div>
        <button type="submit" class="px-3 py-1.5 rounded-lg bg-[#202530] hover:bg-[#282F3D] text-stone-200 hover:text-white font-bold transition border border-[#303847]">
          Terapkan
        </button>
      </form>

    </div>
  </div>

  <!-- ======================================================== -->
  <!-- 2. EXECUTIVE METRICS KPI (4 CARDS)                       -->
  <!-- ======================================================== -->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    
    <!-- Total Inflow -->
    <div class="bg-[#121418] border border-[#1E2229] rounded-xl p-4 space-y-2 relative overflow-hidden">
      <div class="flex items-center justify-between">
        <span class="font-mono-tech text-[10px] text-stone-400 uppercase tracking-wider">TOTAL PEMASUKAN KAS</span>
        <span class="px-1.5 py-0.5 rounded bg-emerald-500/10 text-emerald-400 font-mono-tech text-[9px] font-bold border border-emerald-500/30">INFLOW</span>
      </div>
      <div class="font-mono-tech text-2xl font-black text-emerald-400">
        Rp {{ number_format($summary['total_inflow'], 0, ',', '.') }}
      </div>
      <div class="text-[10px] font-mono-tech text-stone-400 flex items-center justify-between pt-1 border-t border-[#1C2028]">
        <span>{{ $summary['total_transactions'] }} Transaksi Sukses</span>
        <span class="text-stone-300">{{ $summary['total_climbers'] }} Pendaki</span>
      </div>
    </div>

    <!-- Total Outflow -->
    <div class="bg-[#121418] border border-[#1E2229] rounded-xl p-4 space-y-2 relative overflow-hidden">
      <div class="flex items-center justify-between">
        <span class="font-mono-tech text-[10px] text-stone-400 uppercase tracking-wider">TOTAL PENGELUARAN KAS</span>
        <span class="px-1.5 py-0.5 rounded bg-rose-500/10 text-rose-400 font-mono-tech text-[9px] font-bold border border-rose-500/30">OUTFLOW</span>
      </div>
      <div class="font-mono-tech text-2xl font-black text-rose-400">
        Rp {{ number_format($summary['total_outflow'], 0, ',', '.') }}
      </div>
      <div class="text-[10px] font-mono-tech text-stone-400 flex items-center justify-between pt-1 border-t border-[#1C2028]">
        <span>Payout Ojek: <strong class="text-amber-400">Rp {{ number_format($summary['ojek_payouts_total'], 0, ',', '.') }}</strong></span>
        <span>Biaya: <strong class="text-rose-400">Rp {{ number_format($summary['operational_expenses_total'], 0, ',', '.') }}</strong></span>
      </div>
    </div>

    <!-- Net Profit / Margin -->
    <div class="bg-[#121418] border {{ $summary['net_profit'] >= 0 ? 'border-emerald-500/30' : 'border-rose-500/30' }} rounded-xl p-4 space-y-2 relative overflow-hidden">
      <div class="flex items-center justify-between">
        <span class="font-mono-tech text-[10px] text-stone-400 uppercase tracking-wider">LABA BERSIH OPERASIONAL</span>
        <span class="px-1.5 py-0.5 rounded {{ $summary['profit_margin'] >= 0 ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30' : 'bg-rose-500/10 text-rose-400 border border-rose-500/30' }} font-mono-tech text-[9px] font-bold">
          {{ $summary['profit_margin'] }}% MARGIN
        </span>
      </div>
      <div class="font-mono-tech text-2xl font-black {{ $summary['net_profit'] >= 0 ? 'text-white' : 'text-rose-400' }}">
        {{ $summary['net_profit'] < 0 ? '-' : '' }}Rp {{ number_format(abs($summary['net_profit']), 0, ',', '.') }}
      </div>
      <div class="text-[10px] font-mono-tech text-stone-400 pt-1 border-t border-[#1C2028]">
        <span>Total Pemasukan dikurangi Semua Beban Operasional</span>
      </div>
    </div>

    <!-- Saldo Kas Fisik Laci (Cash in Drawer) -->
    <div class="bg-[#121418] border border-[#1E2229] rounded-xl p-4 space-y-2 relative overflow-hidden">
      <div class="flex items-center justify-between">
        <span class="font-mono-tech text-[10px] text-stone-400 uppercase tracking-wider">KAS FISIK LACI (NET DRAWER)</span>
        <span class="w-2.5 h-2.5 rounded-full bg-cyan-400"></span>
      </div>
      <div class="font-mono-tech text-2xl font-black text-cyan-400">
        Rp {{ number_format($summary['net_cash_in_drawer'], 0, ',', '.') }}
      </div>
      <div class="text-[10px] font-mono-tech text-stone-400 flex items-center justify-between pt-1 border-t border-[#1C2028]">
        <span>Kas Masuk: <strong class="text-stone-300">Rp {{ number_format($summary['inflow_channels']['cash'], 0, ',', '.') }}</strong></span>
        <span>Kas Keluar: <strong class="text-stone-300">-Rp {{ number_format($summary['outflow_channels']['cash'], 0, ',', '.') }}</strong></span>
      </div>
    </div>

  </div>

  <!-- ======================================================== -->
  <!-- 3. VISUAL DAILY TREND ANALYTICS CHART (SVG RESPONSIVE)   -->
  <!-- ======================================================== -->
  <div class="bg-[#121418] border border-[#1E2229] rounded-2xl p-5 shadow-xl space-y-4">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-[#1E2229]">
      <div class="flex items-center gap-2">
        <div class="w-2.5 h-2.5 rounded-sm bg-[#FF5722]"></div>
        <h3 class="font-mono-tech font-bold text-xs uppercase tracking-wider text-white">
          TREN HARIAN: PEMASUKAN (HIJAU) VS PENGELUARAN (MERAH)
        </h3>
      </div>
      <div class="flex items-center gap-4 font-mono-tech text-[10px]">
        <div class="flex items-center gap-1.5">
          <span class="w-3 h-3 rounded-sm bg-emerald-500"></span>
          <span class="text-stone-300">Pemasukan</span>
        </div>
        <div class="flex items-center gap-1.5">
          <span class="w-3 h-3 rounded-sm bg-rose-500"></span>
          <span class="text-stone-300">Pengeluaran</span>
        </div>
        <div class="flex items-center gap-1.5">
          <span class="w-2 h-2 rounded-full bg-[#FF5722]"></span>
          <span class="text-stone-400">Laba Bersih</span>
        </div>
      </div>
    </div>

    <!-- Chart Body -->
    <div class="overflow-x-auto pt-2 pb-1">
      <div class="min-w-[700px] h-64 flex flex-col justify-end space-y-2">
        
        <!-- Bars Container -->
        <div class="flex-1 flex items-end gap-2 border-b border-[#252B37] px-2 pb-1">
          @foreach($trends['series'] as $day)
            @php
              $inflowHeight = $trends['max_value'] > 0 ? max(4, round(($day['inflow'] / $trends['max_value']) * 190)) : 4;
              $outflowHeight = $trends['max_value'] > 0 ? max(4, round(($day['outflow'] / $trends['max_value']) * 190)) : 4;
            @endphp
            <div class="flex-1 flex flex-col items-center justify-end group relative h-full">
              
              <!-- Hover Tooltip -->
              <div class="absolute -top-14 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none bg-[#090A0D] border border-[#2E3543] rounded-lg p-2 text-center shadow-2xl z-20 whitespace-nowrap font-mono-tech text-[10px]">
                <div class="font-bold text-white mb-0.5">{{ $day['day_name'] }}, {{ $day['label'] }}</div>
                <div class="text-emerald-400">Masuk: Rp {{ number_format($day['inflow'], 0, ',', '.') }}</div>
                <div class="text-rose-400">Keluar: Rp {{ number_format($day['outflow'], 0, ',', '.') }}</div>
                <div class="text-[#FF5722] font-bold">Net: Rp {{ number_format($day['net'], 0, ',', '.') }}</div>
              </div>

              <!-- Paired Bars -->
              <div class="w-full flex items-end justify-center gap-1">
                <!-- Inflow Bar -->
                <div style="height: {{ $inflowHeight }}px;" class="w-1/2 max-w-[14px] bg-emerald-500/80 hover:bg-emerald-400 rounded-t-sm transition-all duration-300"></div>
                <!-- Outflow Bar -->
                <div style="height: {{ $outflowHeight }}px;" class="w-1/2 max-w-[14px] bg-rose-500/80 hover:bg-rose-400 rounded-t-sm transition-all duration-300"></div>
              </div>

              <!-- Date Label -->
              <div class="mt-2 text-[9px] font-mono-tech text-stone-400 group-hover:text-white transition">
                {{ $day['label'] }}
              </div>
            </div>
          @endforeach
        </div>

      </div>
    </div>
  </div>

  <!-- ======================================================== -->
  <!-- 4. BREAKDOWN ANALYTICS (2 COLUMNS)                       -->
  <!-- ======================================================== -->
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    <!-- Card A: Saluran Pembayaran & Sumber Pemasukan -->
    <div class="bg-[#121418] border border-[#1E2229] rounded-2xl p-5 shadow-xl space-y-4">
      <div class="flex items-center justify-between pb-3 border-b border-[#1E2229]">
        <div class="flex items-center gap-2">
          <span class="w-2.5 h-2.5 rounded-sm bg-emerald-500"></span>
          <h3 class="font-mono-tech font-bold text-xs uppercase tracking-wider text-white">
            STRUKTUR PEMASUKAN & KANAL PEMBAYARAN
          </h3>
        </div>
        <span class="text-[10px] font-mono-tech text-emerald-400 font-bold">Rp {{ number_format($summary['total_inflow'], 0, ',', '.') }}</span>
      </div>

      <!-- Payment Channels (Tunai / QRIS / Transfer) -->
      <div class="space-y-3 font-mono-tech text-xs">
        <span class="text-[10px] text-stone-500 uppercase tracking-wider block">Kanal Pembayaran Diterima:</span>

        <div class="space-y-2">
          <!-- Cash -->
          <div>
            <div class="flex justify-between text-xs mb-1">
              <span class="text-stone-300 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                Uang Tunai (Cash Fisik Posko)
              </span>
              <span class="font-bold text-white">Rp {{ number_format($summary['inflow_channels']['cash'], 0, ',', '.') }}</span>
            </div>
            @php $cashPct = $summary['total_inflow'] > 0 ? round(($summary['inflow_channels']['cash'] / $summary['total_inflow']) * 100) : 0; @endphp
            <div class="w-full bg-[#181B22] rounded-full h-1.5 overflow-hidden">
              <div class="bg-amber-400 h-1.5 rounded-full" style="width: {{ $cashPct }}%;"></div>
            </div>
          </div>

          <!-- QRIS -->
          <div>
            <div class="flex justify-between text-xs mb-1">
              <span class="text-stone-300 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-cyan-400"></span>
                QRIS Resmi Basecamp
              </span>
              <span class="font-bold text-white">Rp {{ number_format($summary['inflow_channels']['qris'], 0, ',', '.') }}</span>
            </div>
            @php $qrisPct = $summary['total_inflow'] > 0 ? round(($summary['inflow_channels']['qris'] / $summary['total_inflow']) * 100) : 0; @endphp
            <div class="w-full bg-[#181B22] rounded-full h-1.5 overflow-hidden">
              <div class="bg-cyan-400 h-1.5 rounded-full" style="width: {{ $qrisPct }}%;"></div>
            </div>
          </div>

          <!-- Transfer Bank -->
          <div>
            <div class="flex justify-between text-xs mb-1">
              <span class="text-stone-300 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-blue-400"></span>
                Transfer Bank {{ $profile['payment_bank_name'] ?? 'Resmi' }}
              </span>
              <span class="font-bold text-white">Rp {{ number_format($summary['inflow_channels']['transfer'], 0, ',', '.') }}</span>
            </div>
            @php $tfPct = $summary['total_inflow'] > 0 ? round(($summary['inflow_channels']['transfer'] / $summary['total_inflow']) * 100) : 0; @endphp
            <div class="w-full bg-[#181B22] rounded-full h-1.5 overflow-hidden">
              <div class="bg-blue-400 h-1.5 rounded-full" style="width: {{ $tfPct }}%;"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Detail Sumber Pendapatan -->
      <div class="pt-3 border-t border-[#1E2229] space-y-2 font-mono-tech text-xs">
        <span class="text-[10px] text-stone-500 uppercase tracking-wider block">Rincian Sumber Pendapatan:</span>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-3 gap-2 text-[11px]">
          <!-- 1. SIMAKSI -->
          <div class="p-2.5 rounded-lg bg-[#16191F] border border-[#222731]">
            <span class="text-[10px] text-stone-400 block truncate">Izin Masuk SIMAKSI</span>
            <span class="font-bold text-white block mt-0.5">Rp {{ number_format($summary['revenue_stream_breakdown']['simaksi_permit'], 0, ',', '.') }}</span>
            <span class="text-[9px] text-stone-500 block truncate">{{ $summary['total_climbers'] ?? 0 }} Pendaki Terdaftar</span>
          </div>

          <!-- 2. Parkir Sepeda Motor (Roda 2) -->
          <div class="p-2.5 rounded-lg bg-[#16191F] border border-[#222731]">
            <div class="flex items-center justify-between">
              <span class="text-[10px] text-stone-400 block truncate">Parkir Motor</span>
              <span class="text-[9px] px-1.5 py-0.2 rounded bg-amber-500/10 text-amber-400 border border-amber-500/30 font-bold">RODA 2</span>
            </div>
            <span class="font-bold text-amber-400 block mt-0.5">Rp {{ number_format($summary['revenue_stream_breakdown']['parking_motor'] ?? 0, 0, ',', '.') }}</span>
            <span class="text-[9px] text-stone-500 block truncate">{{ $summary['total_motorcycles'] ?? 0 }} Unit (@ Rp {{ number_format($summary['tariff_rates']['motorcycle'] ?? 10000, 0, ',', '.') }})</span>
          </div>

          <!-- 3. Parkir Mobil Pribadi (Roda 4) -->
          <div class="p-2.5 rounded-lg bg-[#16191F] border border-[#222731]">
            <div class="flex items-center justify-between">
              <span class="text-[10px] text-stone-400 block truncate">Parkir Mobil</span>
              <span class="text-[9px] px-1.5 py-0.2 rounded bg-blue-500/10 text-blue-400 border border-blue-500/30 font-bold">RODA 4</span>
            </div>
            <span class="font-bold text-cyan-400 block mt-0.5">Rp {{ number_format($summary['revenue_stream_breakdown']['parking_car'] ?? 0, 0, ',', '.') }}</span>
            <span class="text-[9px] text-stone-500 block truncate">{{ $summary['total_cars'] ?? 0 }} Unit (@ Rp {{ number_format($summary['tariff_rates']['car'] ?? 25000, 0, ',', '.') }})</span>
          </div>

          <!-- 4. Shuttle Ojek -->
          <div class="p-2.5 rounded-lg bg-[#16191F] border border-[#222731]">
            <span class="text-[10px] text-stone-400 block truncate">Shuttle Ojek Terdaftar</span>
            <span class="font-bold text-white block mt-0.5">Rp {{ number_format($summary['revenue_stream_breakdown']['ojek_shuttle'], 0, ',', '.') }}</span>
            <span class="text-[9px] text-stone-500 block truncate">{{ $summary['total_ojek_trips_settled'] ?? 0 }} Trip Dicairkan</span>
          </div>

          <!-- 5. POS Logistik & Sewa -->
          <div class="p-2.5 rounded-lg bg-[#16191F] border border-[#222731]">
            <span class="text-[10px] text-stone-400 block truncate">POS Logistik & Sewa</span>
            <span class="font-bold text-white block mt-0.5">Rp {{ number_format($summary['revenue_stream_breakdown']['pos_retail_and_rentals'], 0, ',', '.') }}</span>
            <span class="text-[9px] text-stone-500 block truncate">Retail & Logistik Posko</span>
          </div>

          <!-- 6. Jaminan Sampah Hangus -->
          <div class="p-2.5 rounded-lg bg-[#16191F] border border-[#222731]">
            <span class="text-[10px] text-stone-400 block truncate">Jaminan Sampah Hangus</span>
            <span class="font-bold text-emerald-400 block mt-0.5">Rp {{ number_format($summary['revenue_stream_breakdown']['forfeited_deposit'], 0, ',', '.') }}</span>
            <span class="text-[9px] text-stone-500 block truncate">Alokasi Kas Jalur</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Card B: Komposisi Beban Pengeluaran -->
    <div class="bg-[#121418] border border-[#1E2229] rounded-2xl p-5 shadow-xl space-y-4">
      <div class="flex items-center justify-between pb-3 border-b border-[#1E2229]">
        <div class="flex items-center gap-2">
          <span class="w-2.5 h-2.5 rounded-sm bg-rose-500"></span>
          <h3 class="font-mono-tech font-bold text-xs uppercase tracking-wider text-white">
            KOMPOSISI PENGELUARAN & BIAYA OPERASIONAL
          </h3>
        </div>
        <span class="text-[10px] font-mono-tech text-rose-400 font-bold">Rp {{ number_format($summary['total_outflow'], 0, ',', '.') }}</span>
      </div>

      <div class="space-y-3 font-mono-tech text-xs">
        <span class="text-[10px] text-stone-500 uppercase tracking-wider block">Beban Utama Operasional:</span>

        <div class="space-y-2.5">
          <!-- Ojek Payout -->
          <div>
            <div class="flex justify-between text-xs mb-1">
              <span class="text-stone-300 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                Pencairan Honor Driver Ojek ({{ $summary['total_ojek_trips_settled'] }} Trip)
              </span>
              <span class="font-bold text-amber-400">Rp {{ number_format($summary['ojek_payouts_total'], 0, ',', '.') }}</span>
            </div>
            @php $ojkPct = $summary['total_outflow'] > 0 ? round(($summary['ojek_payouts_total'] / $summary['total_outflow']) * 100) : 0; @endphp
            <div class="w-full bg-[#181B22] rounded-full h-1.5 overflow-hidden">
              <div class="bg-amber-400 h-1.5 rounded-full" style="width: {{ $ojkPct }}%;"></div>
            </div>
          </div>

          <!-- BBM Genset -->
          <div>
            <div class="flex justify-between text-xs mb-1">
              <span class="text-stone-300 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-rose-400"></span>
                BBM Genset & Penerangan Basecamp
              </span>
              <span class="font-bold text-white">Rp {{ number_format($summary['expense_categories']['bbm_genset'], 0, ',', '.') }}</span>
            </div>
            @php $bbmPct = $summary['total_outflow'] > 0 ? round(($summary['expense_categories']['bbm_genset'] / $summary['total_outflow']) * 100) : 0; @endphp
            <div class="w-full bg-[#181B22] rounded-full h-1.5 overflow-hidden">
              <div class="bg-rose-400 h-1.5 rounded-full" style="width: {{ $bbmPct }}%;"></div>
            </div>
          </div>

          <!-- Logistik SAR -->
          <div>
            <div class="flex justify-between text-xs mb-1">
              <span class="text-stone-300 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-purple-400"></span>
                P3K & Logistik Darurat SAR
              </span>
              <span class="font-bold text-white">Rp {{ number_format($summary['expense_categories']['logistik_sar'], 0, ',', '.') }}</span>
            </div>
            @php $sarPct = $summary['total_outflow'] > 0 ? round(($summary['expense_categories']['logistik_sar'] / $summary['total_outflow']) * 100) : 0; @endphp
            <div class="w-full bg-[#181B22] rounded-full h-1.5 overflow-hidden">
              <div class="bg-purple-400 h-1.5 rounded-full" style="width: {{ $sarPct }}%;"></div>
            </div>
          </div>

          <!-- Operasional Pos & Konsumsi -->
          <div>
            <div class="flex justify-between text-xs mb-1">
              <span class="text-stone-300 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-cyan-400"></span>
                Operasional Pos, ATK & Konsumsi Petugas
              </span>
              <span class="font-bold text-white">Rp {{ number_format($summary['expense_categories']['operasional_pos'] + $summary['expense_categories']['konsumsi_petugas'], 0, ',', '.') }}</span>
            </div>
            @php $opsPct = $summary['total_outflow'] > 0 ? round((($summary['expense_categories']['operasional_pos'] + $summary['expense_categories']['konsumsi_petugas']) / $summary['total_outflow']) * 100) : 0; @endphp
            <div class="w-full bg-[#181B22] rounded-full h-1.5 overflow-hidden">
              <div class="bg-cyan-400 h-1.5 rounded-full" style="width: {{ $opsPct }}%;"></div>
            </div>
          </div>

          <!-- Kebersihan & Pemeliharaan -->
          <div>
            <div class="flex justify-between text-xs mb-1">
              <span class="text-stone-300 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                Kebersihan Sampah & Pemeliharaan Jalur
              </span>
              <span class="font-bold text-white">Rp {{ number_format($summary['expense_categories']['kebersihan_sampah'] + $summary['expense_categories']['perbaikan_alat'] + $summary['expense_categories']['lainnya'], 0, ',', '.') }}</span>
            </div>
            @php $cleanPct = $summary['total_outflow'] > 0 ? round((($summary['expense_categories']['kebersihan_sampah'] + $summary['expense_categories']['perbaikan_alat'] + $summary['expense_categories']['lainnya']) / $summary['total_outflow']) * 100) : 0; @endphp
            <div class="w-full bg-[#181B22] rounded-full h-1.5 overflow-hidden">
              <div class="bg-emerald-400 h-1.5 rounded-full" style="width: {{ $cleanPct }}%;"></div>
            </div>
          </div>
        </div>
      </div>

      <div class="pt-3 border-t border-[#1E2229] flex items-center justify-between text-[11px] font-mono-tech text-stone-400">
        <span>Pengeluaran Tunai: <strong class="text-amber-400">Rp {{ number_format($summary['outflow_channels']['cash'], 0, ',', '.') }}</strong></span>
        <span>Transfer: <strong class="text-blue-400">Rp {{ number_format($summary['outflow_channels']['transfer'], 0, ',', '.') }}</strong></span>
      </div>
    </div>

  </div>

  <!-- ======================================================== -->
  <!-- 5. JURNAL BUKU KAS LENGKAP (UNIFIED CASH LEDGER)         -->
  <!-- ======================================================== -->
  <div class="bg-[#121418] border border-[#1E2229] rounded-2xl overflow-hidden shadow-2xl space-y-3">
    
    <div class="p-4 bg-[#0E1013] border-b border-[#1E2229] flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 font-mono-tech">
      <div class="flex items-center gap-2">
        <span class="w-2.5 h-2.5 rounded-sm bg-cyan-400"></span>
        <h3 class="font-bold text-xs uppercase tracking-wider text-white">
          JURNAL BUKU KAS & MUTASI TRANSAKSI TERPADU
        </h3>
      </div>
      <div class="text-[10px] text-stone-400">
        Menampilkan riwayat kronologis lengkap kas masuk & kas keluar
      </div>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-left font-mono-tech text-xs">
        <thead class="bg-[#0A0B0D] border-b border-[#1E2229] text-[10px] text-stone-400 uppercase tracking-wider">
          <tr>
            <th class="py-3 px-4">Waktu & Tanggal</th>
            <th class="py-3 px-4 text-center">Tipe</th>
            <th class="py-3 px-4">Kode Transaksi</th>
            <th class="py-3 px-4">Kategori & Keterangan</th>
            <th class="py-3 px-4 text-center">Kanal</th>
            <th class="py-3 px-4 text-right">Nominal Arus Kas</th>
            <th class="py-3 px-4">Petugas / Kasir</th>
            <th class="py-3 px-4 text-center">Rincian</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-[#1A1E26]">
          @forelse($ledger as $entry)
            <tr class="hover:bg-[#16191F]/70 transition">
              <td class="py-3.5 px-4 text-stone-300 text-[11px] whitespace-nowrap">
                {{ $entry['datetime']->format('d/m/Y H:i') }}
              </td>

              <td class="py-3.5 px-4 text-center">
                @if($entry['type'] === 'IN')
                  <span class="px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 text-[9px] font-bold">
                    + MASUK
                  </span>
                @else
                  <span class="px-2 py-0.5 rounded bg-rose-500/10 text-rose-400 border border-rose-500/30 text-[9px] font-bold">
                    - KELUAR
                  </span>
                @endif
              </td>

              <td class="py-3.5 px-4 font-bold">
                <span class="{{ $entry['type'] === 'IN' ? 'text-emerald-400' : 'text-rose-400' }}">
                  {{ $entry['code'] }}
                </span>
              </td>

              <td class="py-3.5 px-4">
                <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-[#181C24] border border-[#262C38] text-stone-200">
                  {{ $entry['category_group'] }}
                </span>
                <div class="text-[11px] text-stone-300 mt-1">
                  {{ $entry['description'] }}
                </div>
              </td>

              <td class="py-3.5 px-4 text-center">
                <span class="px-1.5 py-0.5 rounded text-[9px] font-bold {{ $entry['payment_method'] === 'cash' ? 'bg-amber-500/10 text-amber-400 border border-amber-500/30' : ($entry['payment_method'] === 'qris' ? 'bg-cyan-500/10 text-cyan-400 border border-cyan-500/30' : 'bg-blue-500/10 text-blue-400 border border-blue-500/30') }}">
                  {{ strtoupper($entry['payment_method']) }}
                </span>
              </td>

              <td class="py-3.5 px-4 text-right font-black text-sm whitespace-nowrap {{ $entry['type'] === 'IN' ? 'text-emerald-400' : 'text-rose-400' }}">
                {{ $entry['type'] === 'IN' ? '+' : '-' }} Rp {{ number_format($entry['amount'], 0, ',', '.') }}
              </td>

              <td class="py-3.5 px-4 text-stone-300 text-[11px]">
                {{ $entry['actor'] }}
              </td>

              <td class="py-3.5 px-4 text-center">
                @if(!empty($entry['url']))
                  <a href="{{ $entry['url'] }}" target="_blank" class="p-1.5 rounded bg-[#181B22] border border-[#282E3A] text-stone-300 hover:text-white hover:bg-[#222733] transition inline-flex items-center" title="Buka Detail Bukti">
                    <svg class="w-3.5 h-3.5 text-stone-400 hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                  </a>
                @else
                  <span class="text-stone-600">-</span>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="8" class="py-12 text-center text-stone-500">
                Belum ada transaksi arus kas pada rentang tanggal yang dipilih.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

  </div>

</div>
@endsection
