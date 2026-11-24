@extends('management.layout')

@section('title', 'Ringkasan Superadmin')
@section('page_title', 'Ringkasan Operasional')

@section('content')
@php
  $rp = fn ($v) => 'Rp '.number_format((float) $v, 0, ',', '.');
  $quotaPct = min(100, round(($bookedQuota / max(1, $maxQuota)) * 100));
  $onTrail = collect($pipeline)->firstWhere('key', 'checked_in');
  $weekMax = max(1, $weekRevenue->max(fn ($d) => $d['simaksi'] + $d['pos']));
  $levelMeta = [
    'superadmin' => ['tone' => 'brand', 'icon' => 'shield'],
    'kasir' => ['tone' => 'success', 'icon' => 'cash'],
    'gatekeeper' => ['tone' => 'info', 'icon' => 'gate'],
    'ranger' => ['tone' => 'violet', 'icon' => 'pin'],
  ];
@endphp

<x-ui.page-header :title="'Selamat bertugas, '.explode(' ', auth()->user()->name)[0]" :subtitle="$today->translatedFormat('l, d F Y').' · kendali penuh basecamp'" eyebrow="Superadmin">
  <x-slot:actions>
    <x-ui.button variant="secondary" size="sm" icon="calendar" :href="route('management.quota.index')">Kuota</x-ui.button>
    <x-ui.button size="sm" icon="chart" :href="route('management.reports.index')">Laporan</x-ui.button>
  </x-slot:actions>
</x-ui.page-header>

{{-- KPI utama --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
  <x-ui.stat label="Pendapatan hari ini" :value="$rp($simaksiRevenueToday + $posRevenueToday)" :hint="'SIMAKSI '.$rp($simaksiRevenueToday).' · POS '.$rp($posRevenueToday)" tone="success" icon="cash" :href="route('management.reports.index')" />
  <x-ui.stat label="Pendaki di jalur" :value="($onTrail['climbers'] ?? 0).' orang'" :hint="($onTrail['count'] ?? 0).' rombongan aktif'" tone="brand" icon="mountain" :href="route('management.gate.index', ['tab' => 'jalur'])" />
  <x-ui.stat label="Kuota hari ini" :value="$bookedQuota.' / '.$maxQuota" :hint="$quotaPct.'% terisi'" tone="info" icon="calendar" :href="route('management.quota.index')">
    <div class="mt-2 h-1.5 rounded-full bg-raised overflow-hidden"><div class="h-full bg-sky-400" style="width: {{ $quotaPct }}%"></div></div>
  </x-ui.stat>
  <x-ui.stat label="Selesai turun hari ini" :value="$completedToday" :hint="$notClearToday > 0 ? $notClearToday.' rombongan ada selisih logistik' : 'Semua logistik kembali lengkap'" :tone="$notClearToday > 0 ? 'danger' : 'neutral'" icon="check" />
</div>

{{-- Alur pendaki --}}
<x-ui.card class="mt-4" title="Alur pendaki saat ini" subtitle="Dari pembayaran sampai selesai turun — ketuk tahap untuk membuka antreannya" icon="arrow-right">
  <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2">
    @foreach($pipeline as $i => $step)
      <a href="{{ $step['href'] }}" class="relative p-3 rounded-xl bg-raised/60 ring-1 ring-line hover:ring-line-strong hover:bg-hover transition">
        <div class="flex items-center justify-between">
          <span class="text-[11px] font-semibold text-subtle">Tahap {{ $i + 1 }}</span>
          <x-ui.badge :tone="$step['tone']" size="xs" dot>{{ $step['count'] }}</x-ui.badge>
        </div>
        <p class="mt-2 text-sm font-semibold text-ink leading-snug">{{ $step['label'] }}</p>
        @isset($step['climbers'])<p class="text-[11px] text-muted mt-0.5">{{ $step['climbers'] }} pendaki</p>@endisset
      </a>
    @endforeach
  </div>
</x-ui.card>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mt-4">
  {{-- Grafik pendapatan 7 hari --}}
  <x-ui.card class="lg:col-span-2" title="Pendapatan 7 hari" subtitle="SIMAKSI lunas + transaksi POS" icon="chart">
    <x-slot:actions>
      <span class="hidden sm:flex items-center gap-3 text-[11px] text-muted">
        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-sm bg-brand"></span>SIMAKSI</span>
        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-sm bg-sky-400"></span>POS</span>
      </span>
    </x-slot:actions>
    <div class="h-48 flex items-end gap-2 sm:gap-3">
      @foreach($weekRevenue as $day)
        @php
          $total = $day['simaksi'] + $day['pos'];
          $h = round(($total / $weekMax) * 100);
          $sPct = $total > 0 ? round($day['simaksi'] / $total * 100) : 0;
        @endphp
        <div class="flex-1 h-full flex flex-col items-center justify-end gap-1.5 group" title="{{ $day['date'] }}: {{ $rp($total) }}">
          <span class="text-[10px] text-muted tabular-nums opacity-0 group-hover:opacity-100 transition">{{ $total >= 1000000 ? number_format($total / 1000000, 1, ',', '.').'jt' : number_format($total / 1000, 0, ',', '.').'rb' }}</span>
          <div class="w-full max-w-[44px] rounded-lg overflow-hidden flex flex-col-reverse bg-raised" style="height: {{ max(4, $h) }}%">
            <div class="bg-brand" style="height: {{ $sPct }}%"></div>
            <div class="bg-sky-400 flex-1"></div>
          </div>
          <span class="text-[11px] {{ $loop->last ? 'text-ink font-semibold' : 'text-subtle' }}">{{ $day['label'] }}</span>
        </div>
      @endforeach
    </div>
  </x-ui.card>

  {{-- Tim & integrasi --}}
  <div class="space-y-4">
    <x-ui.card title="Tim petugas" icon="users">
      <x-slot:actions><x-ui.button variant="ghost" size="sm" :href="route('management.users.index')">Kelola</x-ui.button></x-slot:actions>
      <div class="grid grid-cols-2 gap-2">
        @foreach(\App\Models\User::ROLES as $key => $role)
          <a href="{{ route('management.users.index', ['level' => $key]) }}" class="flex items-center gap-2.5 p-2.5 rounded-xl bg-raised/60 ring-1 ring-line hover:bg-hover">
            <x-ui.icon :name="$levelMeta[$key]['icon']" class="w-4 h-4 text-muted" />
            <div class="min-w-0">
              <p class="text-[11px] text-muted truncate">{{ $role['label'] }}</p>
              <p class="font-display font-bold text-ink tabular-nums">{{ $staff[$key] ?? 0 }}</p>
            </div>
          </a>
        @endforeach
      </div>
    </x-ui.card>

    <x-ui.card title="WhatsApp Gateway" icon="whatsapp">
      <x-slot:actions>
        <x-ui.badge :tone="$waStatus['connected'] ? 'success' : 'warning'" dot>{{ $waStatus['connected'] ? 'Terhubung' : 'Belum aktif' }}</x-ui.badge>
      </x-slot:actions>
      <p class="text-sm text-muted">Driver: <span class="text-ink font-semibold">{{ $waStatus['label'] }}</span></p>
      <div class="flex items-center gap-4 mt-3 text-sm">
        <span class="text-muted">Terkirim hari ini <strong class="text-ink tabular-nums">{{ $waSentToday }}</strong></span>
        <span class="text-muted">Gagal <strong class="{{ $waFailed ? 'text-rose-300' : 'text-ink' }} tabular-nums">{{ $waFailed }}</strong></span>
      </div>
      <x-ui.button variant="secondary" size="sm" class="mt-3" :href="route('management.settings.whatsapp')" block>Buka pengaturan & log</x-ui.button>
    </x-ui.card>
  </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mt-4">
  {{-- Kondisi jalur --}}
  <x-ui.card title="Kondisi jalur" icon="mountain">
    @if($trail)
      <dl class="grid grid-cols-2 gap-3 text-sm">
        @php $trailTone = ['open' => 'text-emerald-300', 'caution' => 'text-amber-300', 'closed' => 'text-rose-300'][$trail->status] ?? 'text-ink'; @endphp
        <div class="p-3 rounded-xl bg-raised/60 ring-1 ring-line"><dt class="text-[11px] text-subtle">Status trek</dt><dd class="font-semibold mt-0.5 {{ $trailTone }}">{{ ['open' => 'Dibuka', 'caution' => 'Waspada', 'closed' => 'Ditutup'][$trail->status] ?? strtoupper($trail->status) }}</dd></div>
        <div class="p-3 rounded-xl bg-raised/60 ring-1 ring-line"><dt class="text-[11px] text-subtle">Suhu malam</dt><dd class="font-semibold text-ink mt-0.5">{{ $trail->night_temp_celsius }}</dd></div>
        <div class="p-3 rounded-xl bg-raised/60 ring-1 ring-line"><dt class="text-[11px] text-subtle">Cuaca</dt><dd class="text-ink mt-0.5">{{ $trail->weather_condition }}</dd></div>
        <div class="p-3 rounded-xl bg-raised/60 ring-1 ring-line"><dt class="text-[11px] text-subtle">Air Pos 2</dt><dd class="text-ink mt-0.5">{{ $trail->pos2_water_status }}</dd></div>
      </dl>
    @else
      <x-ui.empty icon="mountain" title="Belum ada laporan jalur" />
    @endif
    <x-ui.button variant="ghost" size="sm" class="mt-3" :href="route('management.quota.index')" icon="settings">Perbarui kondisi</x-ui.button>
  </x-ui.card>

  {{-- Aktivitas terbaru --}}
  <x-ui.card class="lg:col-span-2" title="Aktivitas terbaru" subtitle="Jejak audit seluruh petugas" icon="shield" :padding="false">
    <x-slot:actions><x-ui.button variant="ghost" size="sm" :href="route('management.audit.index')">Semua</x-ui.button></x-slot:actions>
    <ul class="divide-y divide-line">
      @forelse($recentLogs as $log)
        <li class="px-4 sm:px-5 py-3 flex items-start gap-3">
          <span class="mt-1 w-2 h-2 rounded-full shrink-0 {{ str_contains($log->action, 'REJECT') || str_contains($log->action, 'VOID') ? 'bg-rose-400' : (str_contains($log->action, 'GATE') ? 'bg-sky-400' : 'bg-emerald-400') }}"></span>
          <div class="min-w-0 flex-1">
            <p class="text-sm text-ink leading-snug">{{ $log->description }}</p>
            <p class="text-[11px] text-subtle mt-0.5">{{ $log->actor_name }} · {{ $log->created_at->diffForHumans() }}</p>
          </div>
        </li>
      @empty
        <li><x-ui.empty icon="shield" title="Belum ada aktivitas" /></li>
      @endforelse
    </ul>
  </x-ui.card>
</div>
@endsection
