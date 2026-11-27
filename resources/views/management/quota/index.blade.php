@extends('management.layout')

@section('title', 'Kalender Kuota & Telemetri')
@section('page_title', 'Kuota Harian & Telemetri Jalur')
@section('page_subtitle', 'Kontrol kapasitas ' . \App\Models\AppSetting::get('default_daily_quota', 150) . ' pendaki/hari & kondisi lapangan')

@section('content')
@php
  $prevDate = \Carbon\Carbon::create($year, $month, 1)->subMonth();
  $nextDate = \Carbon\Carbon::create($year, $month, 1)->addMonth();
@endphp

<div class="space-y-5">

  <!-- Top Month Navigation -->
  <div class="bg-surface ring-1 ring-line rounded-2xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
    <div class="flex items-center gap-3 min-w-0">
      <span class="w-10 h-10 shrink-0 rounded-xl bg-brand/15 ring-1 ring-brand/30 flex items-center justify-center text-brand">
        <x-ui.icon name="calendar" class="w-5 h-5" />
      </span>
      <div class="min-w-0">
        <h2 class="font-display font-bold text-lg text-ink uppercase truncate">{{ $currentMonthName }}</h2>
        <p class="text-xs text-muted">Kapasitas resmi: maks. {{ \App\Models\AppSetting::get('default_daily_quota', 150) }} pendaki / hari</p>
      </div>
    </div>

    <!-- Month Switcher -->
    <div class="grid grid-cols-3 sm:flex items-center gap-2 text-xs font-semibold shrink-0">
      <a href="{{ route('management.quota.index', ['year' => $prevDate->year, 'month' => $prevDate->month]) }}" class="h-9 px-3 rounded-xl bg-raised hover:bg-hover ring-1 ring-line text-ink flex items-center justify-center whitespace-nowrap transition">
        ← Lalu
      </a>
      <a href="{{ route('management.quota.index', ['year' => date('Y'), 'month' => date('n')]) }}" class="h-9 px-3 rounded-xl bg-raised hover:bg-hover ring-1 ring-line text-ink flex items-center justify-center whitespace-nowrap transition">
        Bulan Ini
      </a>
      <a href="{{ route('management.quota.index', ['year' => $nextDate->year, 'month' => $nextDate->month]) }}" class="h-9 px-3 rounded-xl bg-raised hover:bg-hover ring-1 ring-line text-ink flex items-center justify-center whitespace-nowrap transition">
        Depan →
      </a>
    </div>
  </div>

  <!-- ======================================================== -->
  <!-- 1. MONTHLY CALENDAR GRID                                 -->
  <!-- ======================================================== -->
  <x-ui.card :title="'Grid Kuota Bulan '.ucfirst($currentMonthName)" icon="calendar">
    <x-slot:actions>
      <div class="hidden sm:flex flex-wrap items-center gap-3 text-[11px] text-muted">
        <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> Tersedia</span>
        <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-amber-500"></span> Menipis</span>
        <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-rose-500"></span> Ditutup</span>
      </div>
    </x-slot:actions>

    <div class="sm:hidden flex flex-wrap items-center gap-3 text-[11px] text-muted mb-3">
      <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> Tersedia</span>
      <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-amber-500"></span> Menipis</span>
      <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-rose-500"></span> Ditutup</span>
    </div>

    <!-- 7-column Calendar Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-7 gap-2">
      @foreach($grid as $day)
        <div class="p-3 rounded-xl border {{ $day['is_closed'] ? 'bg-rose-950/20 border-rose-500/30' : ($day['remaining'] < 20 ? 'bg-amber-950/20 border-amber-500/30' : 'bg-raised/70 border-line-strong') }} flex flex-col justify-between transition hover:border-brand">
          <div class="flex items-center justify-between mb-2">
            <span class="font-display text-sm font-bold {{ $day['is_weekend'] ? 'text-brand' : 'text-ink' }}">
              {{ $day['day'] }}
            </span>
            <span class="text-[10px] text-muted uppercase">{{ $day['day_name'] }}</span>
          </div>

          @if($day['is_closed'])
            <div class="my-1 py-1 px-1.5 bg-rose-500/20 text-rose-300 text-[10px] font-bold text-center rounded-lg">
              DITUTUP
            </div>
            <span class="text-[10px] text-rose-300/80 truncate block" title="{{ $day['closure_reason'] }}">
              {{ $day['closure_reason'] ?? 'Cuaca Buruk' }}
            </span>
          @else
            <div class="space-y-1 tabular-nums">
              <div class="flex justify-between text-xs">
                <span class="text-muted">Sisa:</span>
                <span class="font-bold {{ $day['remaining'] < 20 ? 'text-amber-400' : 'text-emerald-400' }}">
                  {{ $day['remaining'] }}
                </span>
              </div>
              <div class="w-full bg-canvas h-1.5 rounded-full overflow-hidden">
                <div class="bg-brand h-full rounded-full" style="width: {{ min(100, ($day['booked'] / max(1, $day['max_quota'])) * 100) }}%"></div>
              </div>
              <div class="text-[10px] text-subtle flex justify-between">
                <span>Booked {{ $day['booked'] }}</span>
                <span>/{{ $day['max_quota'] }}</span>
              </div>
            </div>
          @endif

          <button type="button" onclick="openQuotaModal('{{ $day['date'] }}', '{{ $day['max_quota'] }}', {{ $day['is_closed'] ? 'true' : 'false' }}, '{{ addslashes($day['closure_reason'] ?? '') }}')" class="mt-2.5 w-full h-8 text-[11px] bg-surface hover:bg-brand hover:text-white text-ink font-semibold rounded-lg ring-1 ring-line transition">
            Atur
          </button>
        </div>
      @endforeach
    </div>
  </x-ui.card>

  <!-- ======================================================== -->
  <!-- 2. LIVE TRAIL TELEMETRY & CONDITIONS FORM                -->
  <!-- ======================================================== -->
  <x-ui.card :title="'Update Kondisi Telemetri Jalur '.strtoupper(\App\Models\AppSetting::get('expedition_name', 'SAMSARA'))" subtitle="Langsung disinkronkan ke portal publik pendaki" icon="mountain">
    <form action="{{ route('management.quota.update_trail') }}" method="POST" class="space-y-4">
      @csrf
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5">
        <div>
          <label class="label">Status Jalur Keseluruhan</label>
          <select name="status" class="field">
            <option value="open" {{ ($trail?->status ?? '') === 'open' ? 'selected' : '' }}>Terbuka (Normal)</option>
            <option value="caution" {{ ($trail?->status ?? '') === 'caution' ? 'selected' : '' }}>Waspada (Angin Kencang / Kabut)</option>
            <option value="closed" {{ ($trail?->status ?? '') === 'closed' ? 'selected' : '' }}>Ditutup Total (Darurat)</option>
          </select>
        </div>

        <div>
          <label class="label">Kondisi Air Pos 2</label>
          <input type="text" name="pos2_water_status" value="{{ $trail?->pos2_water_status ?? 'Melimpah (12 L/mnt)' }}" required class="field">
        </div>

        <div>
          <label class="label">Estimasi Suhu Camp Malam</label>
          <input type="text" name="night_temp_celsius" value="{{ $trail?->night_temp_celsius ?? '9°C' }}" required class="field">
        </div>

        <div>
          <label class="label">Kondisi Cuaca</label>
          <input type="text" name="weather_condition" value="{{ $trail?->weather_condition ?? 'Cerah Berawan' }}" required class="field">
        </div>

        <div>
          <label class="label">Kondisi Tanah &amp; Grip Trek</label>
          <input type="text" name="soil_condition" value="{{ $trail?->soil_condition ?? 'Padat & Kering' }}" required class="field">
        </div>

        <div>
          <label class="label">Frekuensi Radio SAR Basecamp</label>
          <input type="text" name="sar_frequency" value="{{ $trail?->sar_frequency ?? \App\Models\AppSetting::get('sar_frequency', config('samsara.contact.sar_frequency')) }}" required class="field">
        </div>
      </div>

      <div>
        <label class="label">Catatan Petugas / Ranger</label>
        <textarea name="ranger_notes" rows="2" class="field">{{ $trail?->ranger_notes }}</textarea>
      </div>

      <div class="flex justify-end">
        <x-ui.button type="submit" size="lg" icon="check">Publikasikan Telemetri ke Portal</x-ui.button>
      </div>
    </form>
  </x-ui.card>

</div>

<!-- Modal Atur Kuota & Emergency Closure -->
<x-ui.modal id="quotaEditModal" title="Atur Kuota Tanggal" eyebrow="Kontrol Kuota" maxWidth="sm:max-w-md" onclose="closeQuotaModal()">
  <form action="{{ route('management.quota.update_date') }}" method="POST" class="space-y-4">
    @csrf
    <input type="hidden" name="date" id="modalDate">

    <div>
      <label class="label">Kuota Maksimal (Default {{ \App\Models\AppSetting::get('default_daily_quota', 150) }})</label>
      <input type="number" name="max_quota" id="modalMaxQuota" min="0" max="500" required class="field font-bold">
    </div>

    <div class="p-3.5 bg-raised border border-line rounded-xl space-y-3">
      <label class="flex items-center gap-2.5 cursor-pointer select-none">
        <input type="checkbox" name="is_closed" id="modalIsClosed" onchange="toggleReasonBox(this.checked)" class="w-5 h-5 rounded accent-rose-500">
        <span class="text-xs sm:text-sm font-semibold text-rose-400">Tutup Jalur Darurat (Emergency Closure)</span>
      </label>
      
      <div id="reasonContainer" class="hidden pt-1">
        <label class="label">Alasan Penutupan Jalur</label>
        <input type="text" name="closure_reason" id="modalClosureReason" placeholder="Contoh: Badai & Pohon Tumbang" class="field">
      </div>
    </div>

    <div class="flex justify-end gap-2 pt-2 border-t border-line">
      <x-ui.button variant="secondary" onclick="closeQuotaModal()">Batal</x-ui.button>
      <x-ui.button type="submit" icon="check">Simpan Perubahan</x-ui.button>
    </div>
  </form>
</x-ui.modal>

@push('scripts')
<script>
  function openQuotaModal(date, maxQuota, isClosed, reason) {
    document.getElementById('quotaEditModalTitle').innerText = 'Atur Kuota: ' + date;
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
@endpush
@endsection
