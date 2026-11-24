@extends('management.layout')

@section('title', 'Kalender Kuota & Telemetri')
@section('page_title', 'Manajemen Kuota Harian & Telemetri Jalur')
@section('page_subtitle', 'Kontrol Kapasitas ' . \App\Models\AppSetting::get('default_daily_quota', 150) . ' Pendaki/Hari, Penutupan Darurat, & Kondisi Lapangan')

@section('content')
<div class="space-y-6">

  <!-- Top Month Navigation -->
  <div class="flex items-center justify-between bg-surface border border-line rounded-xl p-4">
    <div class="flex items-center gap-3">
      <div class="w-8 h-8 rounded bg-raised border border-line-strong flex items-center justify-center font-mono-tech text-xs font-bold text-brand">
        <svg class="w-4 h-4 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
      </div>
      <div>
        <h2 class="font-display font-black text-lg text-white uppercase">{{ $currentMonthName }}</h2>
        <span class="font-mono-tech text-[10px] text-stone-400">KAPASITAS RESMI: MAKSIMAL {{ \App\Models\AppSetting::get('default_daily_quota', 150) }} PENDAKI / HARI</span>
      </div>
    </div>

    <!-- Month Switcher -->
    @php
      $prevDate = \Carbon\Carbon::create($year, $month, 1)->subMonth();
      $nextDate = \Carbon\Carbon::create($year, $month, 1)->addMonth();
    @endphp
    <div class="flex items-center gap-2 font-mono-tech text-xs">
      <a href="{{ route('management.quota.index', ['year' => $prevDate->year, 'month' => $prevDate->month]) }}" class="px-3 py-1.5 rounded bg-raised hover:bg-hover border border-line-strong text-stone-300">
        ← Bulan Lalu
      </a>
      <a href="{{ route('management.quota.index', ['year' => date('Y'), 'month' => date('n')]) }}" class="px-3 py-1.5 rounded bg-raised hover:bg-hover border border-line-strong text-stone-300">
        Bulan Ini
      </a>
      <a href="{{ route('management.quota.index', ['year' => $nextDate->year, 'month' => $nextDate->month]) }}" class="px-3 py-1.5 rounded bg-raised hover:bg-hover border border-line-strong text-stone-300">
        Bulan Depan →
      </a>
    </div>
  </div>

  <!-- ======================================================== -->
  <!-- 1. MONTHLY CALENDAR GRID                                 -->
  <!-- ======================================================== -->
  <div class="bg-surface border border-line rounded-xl p-5 space-y-4">
    <div class="flex items-center justify-between pb-3 border-b border-line">
      <div class="flex items-center gap-2">
        <div class="w-2.5 h-2.5 rounded-sm bg-brand"></div>
        <h3 class="font-mono-tech font-bold text-xs uppercase tracking-wider text-white">
          GRID KUOTA BULAN {{ strtoupper($currentMonthName) }}
        </h3>
      </div>
      <div class="flex items-center gap-3 font-mono-tech text-[10px] text-stone-400">
        <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> Tersedia</span>
        <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-amber-500"></span> Kuota Menipis</span>
        <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-rose-500"></span> Ditutup Darurat</span>
      </div>
    </div>

    <!-- 7-column Calendar Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-7 gap-2.5">
      @foreach($grid as $day)
        <div class="p-3 rounded-lg border {{ $day['is_closed'] ? 'bg-rose-950/20 border-rose-500/30' : ($day['remaining'] < 20 ? 'bg-amber-950/20 border-amber-500/30' : 'bg-raised border-line-strong') }} flex flex-col justify-between font-mono-tech transition hover:border-brand">
          
          <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-bold {{ $day['is_weekend'] ? 'text-brand' : 'text-white' }}">
              {{ $day['day'] }}
            </span>
            <span class="text-[9px] text-stone-400 uppercase">{{ $day['day_name'] }}</span>
          </div>

          @if($day['is_closed'])
            <div class="my-1 py-1 px-1.5 bg-rose-500/20 text-rose-300 text-[10px] font-bold text-center rounded">
              DITUTUP
            </div>
            <span class="text-[9px] text-rose-300/80 truncate block" title="{{ $day['closure_reason'] }}">
              {{ $day['closure_reason'] ?? 'Cuaca Buruk' }}
            </span>
          @else
            <div class="space-y-0.5">
              <div class="flex justify-between text-[11px]">
                <span class="text-stone-400">Sisa:</span>
                <span class="font-bold {{ $day['remaining'] < 20 ? 'text-amber-400' : 'text-emerald-400' }}">
                  {{ $day['remaining'] }}
                </span>
              </div>
              <div class="w-full bg-raised h-1 rounded-full overflow-hidden">
                <div class="bg-brand h-full" style="width: {{ min(100, ($day['booked'] / max(1, $day['max_quota'])) * 100) }}%"></div>
              </div>
              <div class="text-[9px] text-stone-400 flex justify-between pt-0.5">
                <span>Booked: {{ $day['booked'] }}</span>
                <span>/ {{ $day['max_quota'] }}</span>
              </div>
            </div>
          @endif

          <button type="button" onclick="openQuotaModal('{{ $day['date'] }}', '{{ $day['max_quota'] }}', {{ $day['is_closed'] ? 'true' : 'false' }}, '{{ addslashes($day['closure_reason'] ?? '') }}')" class="mt-2.5 w-full py-1 text-[9px] bg-[#1F2430] hover:bg-brand hover:text-black text-stone-300 font-bold rounded transition">
            ATUR
          </button>
        </div>
      @endforeach
    </div>
  </div>

  <!-- ======================================================== -->
  <!-- 2. LIVE TRAIL TELEMETRY & CONDITIONS FORM                -->
  <!-- ======================================================== -->
  <div class="bg-surface border border-line rounded-xl p-5 space-y-4">
    <div class="flex items-center gap-2 pb-3 border-b border-line">
      <div class="w-2.5 h-2.5 rounded-sm bg-brand"></div>
      <h3 class="font-mono-tech font-bold text-xs uppercase tracking-wider text-white">
        UPDATE KONDISI TELEMETRI LAPANGAN JALUR {{ strtoupper(\App\Models\AppSetting::get('expedition_name', 'SAMSARA')) }}
      </h3>
    </div>

    <form action="{{ route('management.quota.update_trail') }}" method="POST" class="space-y-4">
      @csrf
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 font-mono-tech text-xs">
        <div>
          <label class="block text-stone-400 uppercase text-[10px] mb-1">STATUS JALUR KESELURUHAN</label>
          <select name="status" class="w-full bg-raised border border-line-strong rounded-lg p-2 text-white">
            <option value="open" {{ ($trail?->status ?? '') === 'open' ? 'selected' : '' }}>TERBUKA (NORMAL)</option>
            <option value="caution" {{ ($trail?->status ?? '') === 'caution' ? 'selected' : '' }}>WASPADA (ANGIN KENCANG/KABUT TEBAL)</option>
            <option value="closed" {{ ($trail?->status ?? '') === 'closed' ? 'selected' : '' }}>DITUTUP TOTAL (BADAI/DARURAT)</option>
          </select>
        </div>

        <div>
          <label class="block text-stone-400 uppercase text-[10px] mb-1">KONDISI AIR POS 2</label>
          <input type="text" name="pos2_water_status" value="{{ $trail?->pos2_water_status ?? 'Melimpah (12 L/mnt)' }}" required class="w-full bg-raised border border-line-strong rounded-lg p-2 text-white">
        </div>

        <div>
          <label class="block text-stone-400 uppercase text-[10px] mb-1">ESTIMASI SUHU CAMP MALAM</label>
          <input type="text" name="night_temp_celsius" value="{{ $trail?->night_temp_celsius ?? '9°C' }}" required class="w-full bg-raised border border-line-strong rounded-lg p-2 text-white">
        </div>

        <div>
          <label class="block text-stone-400 uppercase text-[10px] mb-1">KONDISI CUACA</label>
          <input type="text" name="weather_condition" value="{{ $trail?->weather_condition ?? 'Cerah Berawan' }}" required class="w-full bg-raised border border-line-strong rounded-lg p-2 text-white">
        </div>

        <div>
          <label class="block text-stone-400 uppercase text-[10px] mb-1">KONDISI TANAH & GRIP TREK</label>
          <input type="text" name="soil_condition" value="{{ $trail?->soil_condition ?? 'Padat & Kering' }}" required class="w-full bg-raised border border-line-strong rounded-lg p-2 text-white">
        </div>

        <div>
          <label class="block text-stone-400 uppercase text-[10px] mb-1">FREKUENSI RADIO SAR BASECAMP</label>
          <input type="text" name="sar_frequency" value="{{ $trail?->sar_frequency ?? \App\Models\AppSetting::get('sar_frequency', config('samsara.contact.sar_frequency')) }}" required class="w-full bg-raised border border-line-strong rounded-lg p-2 text-white">
        </div>
      </div>

      <div>
        <label class="block text-stone-400 font-mono-tech uppercase text-[10px] mb-1">CATATAN PETUGAS / RANGER {{ strtoupper(\App\Models\AppSetting::get('expedition_name', 'SAMSARA')) }}</label>
        <textarea name="ranger_notes" rows="2" class="w-full bg-raised border border-line-strong rounded-lg p-2.5 text-xs font-mono-tech text-white">{{ $trail?->ranger_notes }}</textarea>
      </div>

      <button type="submit" class="px-5 py-2.5 bg-brand hover:bg-brand-hover text-black font-mono-tech font-bold text-xs uppercase tracking-wider rounded-lg transition">
        PUBLIKASIKAN TELEMETRI LAPANGAN KE PORTAL PUBLIK
      </button>
    </form>
  </div>

</div>

<!-- Modal Atur Kuota & Emergency Closure -->
<div id="quotaEditModal" class="hidden fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
  <div class="bg-surface border border-line-strong rounded-xl max-w-md w-full p-5 space-y-4 font-mono-tech text-xs">
    <div class="flex items-center justify-between pb-3 border-b border-line">
      <h3 class="font-display font-bold text-sm text-white uppercase" id="modalTitle">ATUR KUOTA TANGGAL</h3>
      <button onclick="closeQuotaModal()" class="text-stone-400 hover:text-white font-bold">✕</button>
    </div>

    <form action="{{ route('management.quota.update_date') }}" method="POST" class="space-y-3">
      @csrf
      <input type="hidden" name="date" id="modalDate">

      <div>
        <label class="block text-[10px] text-stone-400 uppercase mb-1">KUOTA MAKSIMAL (DEFAULT {{ \App\Models\AppSetting::get('default_daily_quota', 150) }})</label>
        <input type="number" name="max_quota" id="modalMaxQuota" min="0" max="500" required class="w-full bg-raised border border-line-strong rounded-lg p-2 text-white font-bold">
      </div>

      <div class="p-3 bg-raised border border-line rounded-lg space-y-2">
        <label class="flex items-center gap-2 cursor-pointer">
          <input type="checkbox" name="is_closed" id="modalIsClosed" onchange="toggleReasonBox(this.checked)" class="rounded bg-black text-brand">
          <span class="text-xs font-bold text-rose-400">TUTUP JALUR DARURAT (EMERGENCY CLOSURE)</span>
        </label>
        
        <div id="reasonContainer" class="hidden pt-1">
          <label class="block text-[10px] text-stone-400 uppercase mb-1">ALASAN PENUTUPAN JALUR:</label>
          <input type="text" name="closure_reason" id="modalClosureReason" placeholder="Contoh: Badai Siklon Tropis & Pohon Tumbang" class="w-full bg-raised border border-line-strong rounded-lg p-2 text-white text-xs">
        </div>
      </div>

      <div class="flex justify-end gap-2 pt-2">
        <button type="button" onclick="closeQuotaModal()" class="px-4 py-2 bg-raised text-stone-300 rounded-lg">BATAL</button>
        <button type="submit" class="px-4 py-2 bg-brand text-black font-bold rounded-lg">SIMPAN PERUBAHAN</button>
      </div>
    </form>
  </div>
</div>

<script>
  function openQuotaModal(date, maxQuota, isClosed, reason) {
    document.getElementById('modalTitle').innerText = 'ATUR KUOTA: ' + date;
    document.getElementById('modalDate').value = date;
    document.getElementById('modalMaxQuota').value = maxQuota;
    document.getElementById('modalIsClosed').checked = isClosed;
    document.getElementById('modalClosureReason').value = reason;
    toggleReasonBox(isClosed);
    document.getElementById('quotaEditModal').classList.remove('hidden');
  }

  function closeQuotaModal() {
    document.getElementById('quotaEditModal').classList.add('hidden');
  }

  function toggleReasonBox(show) {
    const box = document.getElementById('reasonContainer');
    if (show) box.classList.remove('hidden');
    else box.classList.add('hidden');
  }
</script>
@endsection
