@extends('management.layout')

@section('title', 'Gate Basecamp')
@section('page_title', 'Gate Basecamp')

@section('content')
@php
  $tab = in_array(request('tab'), ['naik', 'jalur', 'kasir']) ? request('tab') : 'naik';
  $todayStr = now()->toDateString();
  $tabs = [
    'naik' => ['label' => 'Siap naik', 'icon' => 'arrow-up', 'count' => $readyToAscend->count(), 'tone' => 'success'],
    'jalur' => ['label' => 'Di jalur', 'icon' => 'mountain', 'count' => $onTrail->count(), 'tone' => 'brand'],
    'kasir' => ['label' => 'Ke kasir', 'icon' => 'inbox', 'count' => $waitingDeskUp->count() + $waitingDeskDown->count(), 'tone' => 'info'],
  ];
@endphp

{{-- Cari / scan --}}
<form id="gateSearchForm" method="GET" action="{{ route('management.gate.index') }}" class="flex gap-2">
  <div class="relative flex-1 min-w-0">
    <x-ui.icon name="search" class="w-5 h-5 text-subtle absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" />
    <input id="gateTicketInput" type="text" name="code" enterkeyhint="search" placeholder="Kode tiket / 3 digit" autocomplete="off" class="field !h-12 sm:!h-14 !pl-10 !pr-16 !text-base font-code uppercase placeholder:normal-case">
    <button type="submit" class="absolute right-1.5 top-1/2 -translate-y-1/2 h-9 sm:h-10 px-3 rounded-lg bg-raised hover:bg-hover ring-1 ring-line text-xs font-semibold text-ink transition">Cari</button>
  </div>
  <button type="button" onclick="openQrScanner()" class="h-12 sm:h-14 px-3.5 sm:px-5 rounded-2xl bg-brand text-white font-semibold flex items-center gap-2 shadow-lg shadow-brand/25 shrink-0 active:scale-95 transition">
    <x-ui.icon name="scan" class="w-5 h-5 sm:w-6 sm:h-6" /><span class="hidden sm:inline">Scan QR</span>
  </button>
</form>
<p class="text-xs text-muted mt-2 mb-4">Tiket <strong class="text-ink">lunas</strong> → pemeriksaan naik. Rombongan <strong class="text-ink">sedang mendaki</strong> → pemeriksaan turun.</p>

{{-- Statistik ringkas (mobile) --}}
<div class="lg:hidden grid grid-cols-4 gap-1 p-1 rounded-2xl bg-surface ring-1 ring-line mb-4 text-center">
  @foreach([
    ['Jadwal naik', $stats['ascend_today'], 'text-emerald-300', route('management.gate.index', ['tab' => 'naik'])],
    ['Diperiksa', $stats['cleared_today'], 'text-sky-300', route('management.gate.index', ['tab' => 'kasir'])],
    ['Di jalur', $stats['on_trail'], $stats['overdue'] > 0 ? 'text-rose-300' : 'text-brand-soft', route('management.gate.index', ['tab' => 'jalur'])],
    ['Turun', $stats['returned_today'], 'text-violet-300', route('management.gate.index', ['tab' => 'kasir'])],
  ] as [$label, $value, $color, $statHref])
    <a href="{{ $statHref }}" class="flex flex-col items-center justify-center py-2 px-1 rounded-xl hover:bg-hover/60 transition">
      <p class="font-display font-bold text-xl {{ $color }} tabular-nums leading-none">{{ $value }}</p>
      <p class="text-[10px] text-muted mt-1 leading-tight">{{ $label }}</p>
    </a>
  @endforeach
</div>
@if($stats['overdue'] > 0)
  <div class="lg:hidden -mt-2 mb-4 flex items-center gap-2 px-3 py-2 rounded-xl bg-rose-500/10 ring-1 ring-rose-500/25 text-xs text-rose-200">
    <x-ui.icon name="alert" class="w-4 h-4 text-rose-400 shrink-0" /> {{ $stats['overdue'] }} rombongan terlambat turun
  </div>
@endif

{{-- Statistik (desktop) --}}
<div class="hidden lg:grid grid-cols-4 gap-3 mb-4">
  <x-ui.stat label="Jadwal naik hari ini" :value="$stats['ascend_today']" tone="success" icon="arrow-up" />
  <x-ui.stat label="Diperiksa naik hari ini" :value="$stats['cleared_today']" tone="info" icon="check" />
  <x-ui.stat label="Pendaki di jalur" :value="$stats['on_trail']" :hint="$stats['overdue'] > 0 ? $stats['overdue'].' rombongan terlambat!' : 'Semua sesuai jadwal'" :tone="$stats['overdue'] > 0 ? 'danger' : 'brand'" icon="mountain" />
  <x-ui.stat label="Diperiksa turun hari ini" :value="$stats['returned_today']" tone="violet" icon="arrow-down" />
</div>

{{-- Tab --}}
<div class="grid grid-cols-3 gap-1 p-1 rounded-2xl bg-surface ring-1 ring-line mb-3">
  @foreach($tabs as $key => $t)
    <a href="{{ route('management.gate.index', ['tab' => $key]) }}" class="flex items-center justify-center gap-1.5 h-11 px-1.5 sm:px-3 rounded-xl text-xs sm:text-sm font-semibold whitespace-nowrap transition {{ $tab === $key ? 'bg-raised text-ink ring-1 ring-line-strong' : 'text-muted hover:text-ink' }}">
      <x-ui.icon :name="$t['icon']" class="w-4 h-4 shrink-0 hidden sm:inline" />
      <span class="truncate">{{ $t['label'] }}</span>
      <x-ui.badge :tone="$t['tone']" size="xs" class="shrink-0">{{ $t['count'] }}</x-ui.badge>
    </a>
  @endforeach
</div>

@if($tab === 'naik')
  <x-ui.card :padding="false" title="Rombongan lunas · siap diperiksa naik" subtitle="Periksa anggota, foto tim, alat wajib, dan hitung logistik">
    <div class="p-3 space-y-2">
      @forelse($readyToAscend as $reg)
        @php $isToday = $reg->hiking_date?->toDateString() === $todayStr; @endphp
        <x-ui.group-row :reg="$reg" :href="route('management.gate.ascent', $reg->id)" :meta="$isToday ? null : $reg->hiking_date?->diffForHumans()" :show-status="false">
          @if($isToday)<x-ui.badge tone="success" size="xs" dot>Hari ini</x-ui.badge>@endif
          @if(! $reg->group_photo_path)<x-ui.badge tone="warning" size="xs" class="hidden sm:inline-flex">Belum ada foto</x-ui.badge>@endif
        </x-ui.group-row>
      @empty
        <x-ui.empty icon="arrow-up" title="Belum ada rombongan lunas" text="Rombongan muncul di sini setelah kasir memverifikasi pembayaran." />
      @endforelse
    </div>
  </x-ui.card>
@elseif($tab === 'jalur')
  <x-ui.card :padding="false" title="Sedang mendaki · periksa saat turun" subtitle="Hitung ulang logistik agar tidak ada sampah tertinggal">
    <div class="p-3 space-y-2">
      @forelse($onTrail as $row)
        @php
          $reg = $row['registration'];
          $posLabel = 'Terakhir '.str_replace('_', ' ', $reg->last_seen_pos ?? 'BASECAMP');
          $trailMeta = $reg->last_seen_at ? $posLabel.' · '.$reg->last_seen_at->diffForHumans() : $posLabel;
        @endphp
        <x-ui.group-row :reg="$reg" :href="route('management.gate.descent', $reg->id)" :meta="$trailMeta" :show-status="false">
          @if($row['is_overdue'])<x-ui.badge tone="danger" size="xs" dot>Terlambat</x-ui.badge>@endif
        </x-ui.group-row>
      @empty
        <x-ui.empty icon="mountain" title="Jalur kosong" text="Tidak ada rombongan yang sedang mendaki." />
      @endforelse
    </div>
  </x-ui.card>

  {{-- Dispatch HT --}}
  <details class="mt-4 bg-surface rounded-2xl ring-1 ring-line overflow-hidden">
    <summary class="flex items-center justify-between gap-3 px-4 sm:px-5 py-4 cursor-pointer">
      <span class="flex items-center gap-2.5">
        <span class="w-8 h-8 rounded-lg bg-raised ring-1 ring-line flex items-center justify-center text-amber-300"><x-ui.icon name="radio" class="w-4 h-4" /></span>
        <span>
          <span class="block font-display font-semibold text-[15px] text-ink">Catat laporan radio HT</span>
          <span class="block text-xs text-muted">Saat ranger di pos tidak bisa memakai ponsel</span>
        </span>
      </span>
      <x-ui.icon name="chevron-down" class="w-4 h-4 text-subtle" />
    </summary>
    <form method="POST" action="{{ route('management.gate.ht_dispatch') }}" class="px-4 sm:px-5 pb-5 grid grid-cols-1 sm:grid-cols-2 gap-3">
      @csrf
      <div class="sm:col-span-2">
        <label class="label">Kode registrasi</label>
        <input type="text" name="registration_code" required class="field font-code uppercase" placeholder="SMK-202611-001 atau 001">
      </div>
      <div>
        <label class="label">Posisi</label>
        <select name="pos_code" class="field">
          <option value="POS_1">Pos 1</option><option value="POS_2">Pos 2</option><option value="POS_3">Pos 3</option><option value="PUNCAK">Puncak</option>
        </select>
      </div>
      <div>
        <label class="label">Arah</label>
        <select name="direction" class="field">
          <option value="ascending">Naik</option><option value="descending">Turun</option><option value="camping">Buka tenda</option>
        </select>
      </div>
      <div class="sm:col-span-2">
        <label class="label">Catatan</label>
        <input type="text" name="notes" class="field" placeholder="opsional">
      </div>
      <x-ui.button type="submit" variant="secondary" icon="radio" class="sm:col-span-2">Simpan laporan HT</x-ui.button>
    </form>
  </details>
@else
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
    <x-ui.card :padding="false" title="Menunggu cetak surat jalan" subtitle="Sudah lolos Gate, arahkan ke kasir">
      <div class="p-3 space-y-2">
        @forelse($waitingDeskUp as $reg)
          <x-ui.group-row :reg="$reg" :meta="$reg->gate_up_at ? 'Diperiksa '.$reg->gate_up_at->format('H:i') : 'Menunggu cetak'" :show-status="false" />
        @empty
          <x-ui.empty icon="printer" title="Kosong" />
        @endforelse
      </div>
    </x-ui.card>
    <x-ui.card :padding="false" title="Menunggu penyelesaian" subtitle="Sudah turun, arahkan ke kasir">
      <div class="p-3 space-y-2">
        @forelse($waitingDeskDown as $reg)
          <x-ui.group-row :reg="$reg" :meta="$reg->gate_down_at ? 'Turun '.$reg->gate_down_at->format('H:i') : 'Menunggu selesai'" :show-status="false">
            <x-ui.badge :tone="$reg->gate_down_clear ? 'success' : 'danger'" size="xs">{{ $reg->gate_down_clear ? 'Clear' : 'Selisih' }}</x-ui.badge>
          </x-ui.group-row>
        @empty
          <x-ui.empty icon="arrow-down" title="Kosong" />
        @endforelse
      </div>
    </x-ui.card>
  </div>
@endif

@include('management.partials.qr_scanner', ['formId' => 'gateSearchForm', 'inputId' => 'gateTicketInput', 'autoOpen' => request()->has('scan')])
@endsection
