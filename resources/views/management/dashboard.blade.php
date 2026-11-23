@extends('management.layout')

@section('title', 'Dashboard Operasional')
@section('page_title', 'Ringkasan Operasional Lapangan')
@section('page_subtitle', 'Monitoring Kuota, Verifikasi, & Kasir Jalur ' . \App\Models\AppSetting::get('expedition_name', 'SAMSARA'))

@section('content')
<div class="space-y-6">

  <!-- ======================================================== -->
  <!-- 1. KPI HUD METRIC CARDS                                  -->
  <!-- ======================================================== -->
  <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
    
    <!-- KPI 1: Kuota Hari Ini -->
    <div class="bg-[#121418] border border-[#1E2229] rounded-xl p-4 space-y-2">
      <div class="flex items-center justify-between text-stone-400 font-mono-tech text-[10px] uppercase">
        <span>KUOTA PENDAKI HARI INI</span>
        <span class="w-2 h-2 rounded-full {{ $remainingQuota > 20 ? 'bg-emerald-500' : 'bg-[#FF5722]' }}"></span>
      </div>
      <div class="flex items-baseline gap-2">
        <span class="font-display font-black text-2xl text-white">{{ $remainingQuota }}</span>
        <span class="font-mono-tech text-xs text-stone-400">/ {{ $maxQuota }} TERSISA</span>
      </div>
      <div class="w-full bg-[#1A1D24] h-1.5 rounded-full overflow-hidden">
        <div class="bg-[#FF5722] h-full" style="width: {{ min(100, ($bookedQuota / max(1, $maxQuota)) * 100) }}%"></div>
      </div>
      <div class="flex justify-between font-mono-tech text-[9px] text-stone-400">
        <span>Terisi: {{ $bookedQuota }} orang</span>
        <span>Maksimal: {{ $maxQuota }}/hari</span>
      </div>
    </div>

    <!-- KPI 2: SIMAKSI Status (PENDING vs PAID) -->
    <div class="bg-[#121418] border border-[#1E2229] rounded-xl p-4 space-y-2">
      <div class="flex items-center justify-between text-stone-400 font-mono-tech text-[10px] uppercase">
        <span>STATUS SIMAKSI</span>
        <a href="{{ route('management.simaksi.index') }}" class="text-[#FF5722] hover:underline">LIHAT SEMUA →</a>
      </div>
      <div class="flex items-baseline gap-2">
        <span class="font-display font-black text-2xl {{ $pendingSimaksiCount > 0 ? 'text-amber-400' : 'text-stone-300' }}">
          {{ $pendingSimaksiCount }}
        </span>
        <span class="font-mono-tech text-xs text-stone-400">PENDING (BELUM BAYAR)</span>
      </div>
      <div class="flex items-center gap-2 font-mono-tech text-[10px]">
        <span class="px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 font-bold">
          {{ $paidSimaksiCount }} LUNAS (PAID)
        </span>
        <span class="text-stone-400">Siap Gate Check-in</span>
      </div>
    </div>

    <!-- KPI 3: Pendaki di Gunung (Active On Trail) -->
    <div class="bg-[#121418] border border-[#1E2229] rounded-xl p-4 space-y-2">
      <div class="flex items-center justify-between text-stone-400 font-mono-tech text-[10px] uppercase">
        <span>PENDAKI DI JALUR</span>
        <span class="px-1.5 py-0.5 rounded bg-emerald-500/10 text-emerald-400 text-[9px] font-mono-tech font-bold">
          TERVERIFIKASI
        </span>
      </div>
      <div class="flex items-baseline gap-2">
        <span class="font-display font-black text-2xl text-white">{{ $activeClimbersOnMountain }}</span>
        <span class="font-mono-tech text-xs text-stone-400">ORANG DI TREK</span>
      </div>
      <p class="font-mono-tech text-[10px] text-stone-400">
        Status manifest & kantong sampah aktif dipantau Pos SAR Basecamp.
      </p>
    </div>

    <!-- KPI 4: Pendapatan POS Kasir Hari Ini -->
    <div class="bg-[#121418] border border-[#1E2229] rounded-xl p-4 space-y-2">
      <div class="flex items-center justify-between text-stone-400 font-mono-tech text-[10px] uppercase">
        <span>KASIR POS HARI INI</span>
        <span class="font-mono-tech text-[10px] text-stone-400">{{ $posTransactionsCount }} TRANSAKSI</span>
      </div>
      <div class="flex items-baseline gap-1">
        <span class="font-mono-tech text-xs text-[#FF5722] font-bold">Rp</span>
        <span class="font-display font-black text-2xl text-white">{{ number_format($posTodayRevenue, 0, ',', '.') }}</span>
      </div>
      <div class="flex justify-between font-mono-tech text-[9px] text-stone-400">
        <span>Alat disewa: {{ $activeRentalsCount }} unit</span>
        <a href="{{ route('management.pos.index') }}" class="text-[#FF5722] hover:underline">BUKA KASIR →</a>
      </div>
    </div>

  </div>

  <!-- ======================================================== -->
  <!-- 2. TELEMETRY STATUS & QUICK DISPATCH                     -->
  <!-- ======================================================== -->
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    
    <!-- Telemetry Card -->
    <div class="lg:col-span-2 bg-[#121418] border border-[#1E2229] rounded-xl p-5 space-y-4">
      <div class="flex items-center justify-between pb-3 border-b border-[#1E2229]">
        <div class="flex items-center gap-2">
          <div class="w-2.5 h-2.5 rounded-sm bg-[#FF5722]"></div>
          <h3 class="font-mono-tech font-bold text-xs uppercase tracking-wider text-white">
            TELEMETRI JALUR {{ strtoupper(\App\Models\AppSetting::get('mountain_name', 'CIKURAY')) }} VIA {{ strtoupper(\App\Models\AppSetting::get('basecamp_name', 'SAMSARA')) }}
          </h3>
        </div>
        <a href="{{ route('management.quota.index') }}" class="text-[10px] font-mono-tech text-[#FF5722] hover:underline">
          UPDATE TELEMETRI →
        </a>
      </div>

      <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 font-mono-tech text-xs">
        <div class="p-3 bg-[#16191F] border border-[#20242C] rounded-lg">
          <span class="text-[9px] text-stone-400 uppercase block">STATUS TREK</span>
          <span class="font-bold text-emerald-400 text-sm block mt-0.5 uppercase">{{ $trail?->status ?? 'OPEN' }}</span>
          <span class="text-[9px] text-stone-400 block mt-1">Normal & aman</span>
        </div>
        <div class="p-3 bg-[#16191F] border border-[#20242C] rounded-lg">
          <span class="text-[9px] text-stone-400 uppercase block">MATA AIR POS 2</span>
          <span class="font-bold text-cyan-400 text-xs block mt-0.5">{{ $trail?->pos2_water_status ?? 'Melimpah' }}</span>
          <span class="text-[9px] text-stone-400 block mt-1">Titik air terakhir</span>
        </div>
        <div class="p-3 bg-[#16191F] border border-[#20242C] rounded-lg">
          <span class="text-[9px] text-stone-400 uppercase block">SUHU CAMP POS 4</span>
          <span class="font-bold text-amber-300 text-sm block mt-0.5">{{ $trail?->night_temp_celsius ?? '9°C' }}</span>
          <span class="text-[9px] text-stone-400 block mt-1">Thermal mandatory</span>
        </div>
        <div class="p-3 bg-[#16191F] border border-[#20242C] rounded-lg">
          <span class="text-[9px] text-stone-400 uppercase block">RADIO SAR BC</span>
          <span class="font-bold text-white text-xs block mt-0.5">{{ $trail?->sar_frequency ?? \App\Models\AppSetting::get('sar_frequency', '149.250 MHz') }}</span>
          <span class="text-[9px] text-stone-400 block mt-1">Standby 24 Jam</span>
        </div>
      </div>

      <div class="p-3 bg-[#0A0B0D] border border-[#1A1E26] rounded-lg flex items-center justify-between text-xs font-mono-tech text-stone-400">
        <div class="flex items-center gap-2">
          <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
          <span>Catatan Ranger: {{ $trail?->ranger_notes ?? 'Kondisi jalur bersih dan padat. Tidak ada pohon tumbang.' }}</span>
        </div>
        <span class="text-[10px] text-stone-400">Update: {{ $trail?->updated_at?->diffForHumans() ?? 'Baru saja' }}</span>
      </div>
    </div>

    <!-- Quick Actions Panel -->
    <div class="bg-[#121418] border border-[#1E2229] rounded-xl p-5 space-y-3">
      <div class="flex items-center gap-2 pb-3 border-b border-[#1E2229]">
        <div class="w-2.5 h-2.5 rounded-sm bg-[#FF5722]"></div>
        <h3 class="font-mono-tech font-bold text-xs uppercase tracking-wider text-white">
          TINDAKAN CEPAT PETUGAS
        </h3>
      </div>

      <div class="space-y-2 font-mono-tech text-xs">
        <a href="{{ route('management.gate.index') }}" class="w-full p-2.5 rounded-lg bg-[#16191F] hover:bg-[#202530] border border-[#262B35] flex items-center justify-between transition">
          <div class="flex items-center gap-2">
            <svg class="w-4 h-4 text-[#FF5722]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            <span class="text-stone-200">Verifikasi Check-In Gerbang</span>
          </div>
          <span class="text-stone-400">→</span>
        </a>

        <a href="{{ route('management.pos.index') }}" class="w-full p-2.5 rounded-lg bg-[#16191F] hover:bg-[#202530] border border-[#262B35] flex items-center justify-between transition">
          <div class="flex items-center gap-2">
            <svg class="w-4 h-4 text-[#FF5722]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            <span class="text-stone-200">Buka Kasir POS Baru</span>
          </div>
          <span class="text-stone-400">→</span>
        </a>

        <a href="{{ route('management.simaksi.index', ['status' => 'submitted']) }}" class="w-full p-2.5 rounded-lg bg-[#16191F] hover:bg-[#202530] border border-[#262B35] flex items-center justify-between transition">
          <div class="flex items-center gap-2">
            <svg class="w-4 h-4 text-[#FF5722]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <span class="text-stone-200">Review Permohonan Masuk</span>
          </div>
          <span class="text-stone-400">→</span>
        </a>

        <a href="{{ route('management.inventory.index') }}" class="w-full p-2.5 rounded-lg bg-[#16191F] hover:bg-[#202530] border border-[#262B35] flex items-center justify-between transition">
          <div class="flex items-center gap-2">
            <svg class="w-4 h-4 text-[#FF5722]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            <span class="text-stone-200">Serah Terima & Cek Alat</span>
          </div>
          <span class="text-stone-400">→</span>
        </a>
      </div>
    </div>

  </div>

  <!-- ======================================================== -->
  <!-- 3. RECENT SIMAKSI & AUDIT LOG TABLE                      -->
  <!-- ======================================================== -->
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    <!-- Recent SIMAKSI Table -->
    <div class="lg:col-span-2 bg-[#121418] border border-[#1E2229] rounded-xl p-5 space-y-4">
      <div class="flex items-center justify-between pb-3 border-b border-[#1E2229]">
        <div class="flex items-center gap-2">
          <div class="w-2.5 h-2.5 rounded-sm bg-[#FF5722]"></div>
          <h3 class="font-mono-tech font-bold text-xs uppercase tracking-wider text-white">
            PERMOHONAN SIMAKSI TERAKHIR
          </h3>
        </div>
        <a href="{{ route('management.simaksi.index') }}" class="text-[10px] font-mono-tech text-[#FF5722] hover:underline">
          SEMUA PERMOHONAN →
        </a>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left font-mono-tech text-xs">
          <thead>
            <tr class="text-[10px] text-stone-400 uppercase border-b border-[#1E2229]">
              <th class="pb-2">KODE</th>
              <th class="pb-2">KETUA TIM</th>
              <th class="pb-2">TGL NAIK</th>
              <th class="pb-2">ROMBONGAN</th>
              <th class="pb-2">STATUS</th>
              <th class="pb-2 text-right">AKSI</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#1A1E26]">
            @forelse($recentRegistrations as $reg)
              <tr class="hover:bg-[#16191F] transition">
                <td class="py-3 font-bold text-[#FF5722]">{{ $reg->registration_code }}</td>
                <td class="py-3 text-white">
                  <div>{{ $reg->leader_name }}</div>
                  <span class="text-[10px] text-stone-400 font-normal block truncate max-w-[180px]" title="{{ $reg->leader_address ?? $reg->leader_city }}">{{ $reg->leader_address ?? $reg->leader_city ?? '-' }}</span>
                </td>
                <td class="py-3 text-stone-300">{{ $reg->hiking_date->format('d M Y') }}</td>
                <td class="py-3 text-stone-300">{{ $reg->total_climbers }} Orang</td>
                <td class="py-3">
                  <span class="px-2 py-0.5 rounded text-[10px] font-bold border {{ $reg->status_badge['bg'] }}">
                    {{ $reg->status_badge['label'] }}
                  </span>
                </td>
                <td class="py-3 text-right">
                  <a href="{{ route('management.simaksi.show', $reg->id) }}" class="px-2.5 py-1 rounded bg-[#1A1E26] hover:bg-[#252A36] text-stone-300 hover:text-white border border-[#2E3544] text-[10px] transition">
                    Detail
                  </a>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="py-4 text-center text-stone-400">Belum ada permohonan SIMAKSI.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    <!-- Live Audit Stream -->
    <div class="bg-[#121418] border border-[#1E2229] rounded-xl p-5 space-y-4">
      <div class="flex items-center justify-between pb-3 border-b border-[#1E2229]">
        <div class="flex items-center gap-2">
          <div class="w-2.5 h-2.5 rounded-sm bg-[#FF5722]"></div>
          <h3 class="font-mono-tech font-bold text-xs uppercase tracking-wider text-white">
            AUDIT TRAIL SISTEM
          </h3>
        </div>
        <a href="{{ route('management.audit.index') }}" class="text-[10px] font-mono-tech text-[#FF5722] hover:underline">
          SEMUA LOG →
        </a>
      </div>

      <div class="space-y-3 font-mono-tech text-xs">
        @forelse($recentLogs as $log)
          <div class="p-2.5 rounded bg-[#16191F] border border-[#20242C] space-y-1">
            <div class="flex items-center justify-between text-[10px]">
              <span class="text-[#FF5722] font-bold">{{ $log->action }}</span>
              <span class="text-stone-400">{{ $log->created_at->diffForHumans() }}</span>
            </div>
            <p class="text-stone-300 text-[11px] leading-snug">{{ $log->description }}</p>
            <div class="text-[9px] text-stone-400 flex justify-between pt-1 border-t border-[#1E2229]">
              <span>Oleh: {{ $log->actor_name }} ({{ $log->actor_role }})</span>
              <span>IP: {{ $log->ip_address }}</span>
            </div>
          </div>
        @empty
          <div class="text-center py-4 text-stone-400">Belum ada catatan audit.</div>
        @endforelse
      </div>
    </div>

  </div>

</div>
@endsection
