@extends('management.layout')

@section('title', 'Pos Ranger · '.str_replace('_', ' ', $activePos))
@section('page_title', 'Pos Ranger')
@section('page_subtitle', ($trailConfig['checkpoints'][$activePos]['name'] ?? $activePos).' · '.($trailConfig['checkpoints'][$activePos]['elevation'] ?? ''))

@section('content')
@php
  $posList = $trailConfig['checkpoints'];
  $sel = $selectedRegistration;
  $canCamp = $sel && ! $sel->isTektok() && ($posList[$activePos]['can_camp'] ?? false);
  $sar = \App\Models\AppSetting::get('sar_frequency', '149.250 MHz');
@endphp

<div class="max-w-2xl mx-auto space-y-4">

  {{-- Pilih pos --}}
  <div class="grid grid-cols-4 gap-1 p-1 rounded-2xl bg-surface ring-1 ring-line">
    @foreach($posList as $key => $pos)
      <a href="{{ route('management.ranger.index', ['pos' => $key]) }}" class="flex flex-col items-center justify-center h-14 rounded-xl text-center transition {{ $activePos === $key ? 'bg-brand text-white shadow-lg shadow-brand/25' : 'text-muted hover:text-ink hover:bg-hover' }}">
        <span class="text-sm font-bold">{{ str_replace('_', ' ', $key) }}</span>
        <span class="text-[10px] {{ $activePos === $key ? 'text-white/80' : 'text-subtle' }}">{{ $pos['elevation'] }}</span>
      </a>
    @endforeach
  </div>

  {{-- Cari / scan --}}
  <form id="rangerSearchForm" method="GET" action="{{ route('management.ranger.index') }}" class="flex gap-2">
    <input type="hidden" name="pos" value="{{ $activePos }}">
    <div class="relative flex-1">
      <x-ui.icon name="search" class="w-5 h-5 text-subtle absolute left-3.5 top-1/2 -translate-y-1/2" />
      <input id="rangerCodeInput" type="text" name="code" value="{{ $code }}" placeholder="Kode tiket / 3 digit" autocomplete="off" class="field !h-14 !pl-11 !text-base font-code uppercase placeholder:normal-case">
    </div>
    <button type="button" onclick="openQrScanner()" class="h-14 px-5 rounded-2xl bg-brand text-white font-semibold flex items-center gap-2 shadow-lg shadow-brand/25 shrink-0">
      <x-ui.icon name="scan" class="w-6 h-6" /><span class="hidden sm:inline">Scan</span>
    </button>
  </form>

  @if($sel)
    {{-- Rombongan terpilih --}}
    <div class="rounded-3xl ring-2 {{ $sel->isTektok() ? 'ring-sky-500/40' : 'ring-emerald-500/40' }} bg-surface p-4 sm:p-5 space-y-4">
      <div class="flex items-start gap-3">
        @if($sel->photoUrl())
          <a href="{{ $sel->photoUrl() }}" target="_blank" class="shrink-0"><img src="{{ $sel->photoUrl() }}" alt="Foto tim" class="w-16 h-16 rounded-xl object-cover ring-1 ring-line"></a>
        @endif
        <div class="min-w-0 flex-1">
          <div class="flex flex-wrap gap-1.5">
            <x-ui.badge :tone="$sel->isTektok() ? 'info' : 'success'">{{ $sel->isTektok() ? 'Tektok' : 'Camp' }}</x-ui.badge>
            <x-ui.badge>{{ $sel->total_climbers }} pendaki</x-ui.badge>
            <x-ui.badge :tone="$sel->statusTone()">{{ $sel->statusLabel() }}</x-ui.badge>
          </div>
          <h2 class="font-display font-bold text-xl text-ink mt-1.5 truncate">{{ $sel->leader_name }}</h2>
          <p class="text-xs text-muted"><span class="font-code text-ink/80">{{ $sel->registration_code }}</span> · {{ $sel->leader_phone }}</p>
        </div>
      </div>

      <div class="grid grid-cols-2 gap-2 text-sm">
        <div class="p-3 rounded-xl bg-raised/60 ring-1 ring-line"><p class="text-[11px] text-subtle">Posisi terakhir</p><p class="font-semibold text-ink">{{ str_replace('_', ' ', $sel->last_seen_pos ?: 'Basecamp') }}</p></div>
        <div class="p-3 rounded-xl bg-raised/60 ring-1 ring-line"><p class="text-[11px] text-subtle">Waktu</p><p class="font-semibold text-ink">{{ $sel->last_seen_at?->diffForHumans() ?? '-' }}</p></div>
      </div>

      <p class="text-xs p-3 rounded-xl {{ $sel->isTektok() ? 'bg-sky-500/10 text-sky-200 ring-1 ring-sky-500/20' : 'bg-emerald-500/10 text-emerald-200 ring-1 ring-emerald-500/20' }}">
        @if($sel->isTektok())
          Tektok: wajib kembali ke basecamp sebelum pukul {{ $trailConfig['tektok_max_checkout_hour'] }}:00 dan dilarang mendirikan tenda.
        @else
          Camp: tenda hanya di Pos 2 (mata air) atau Pos 3 (camp atas).
        @endif
      </p>

      @if($sel->status === 'checked_in')
        <form method="POST" action="{{ route('management.ranger.checkpoint') }}" class="space-y-3">
          @csrf
          <input type="hidden" name="registration_id" value="{{ $sel->id }}">
          <input type="hidden" name="pos_code" value="{{ $activePos }}">
          <div class="grid grid-cols-2 gap-2">
            <button type="submit" name="direction" value="ascending" class="h-20 rounded-2xl text-white font-bold flex flex-col items-center justify-center gap-1 transition {{ $suggestedDirection === 'ascending' ? 'bg-emerald-600 ring-4 ring-emerald-500/30' : 'bg-emerald-600/70 hover:bg-emerald-600' }}">
              <x-ui.icon name="arrow-up" class="w-6 h-6" /> Naik
            </button>
            <button type="submit" name="direction" value="descending" class="h-20 rounded-2xl text-black font-bold flex flex-col items-center justify-center gap-1 transition {{ $suggestedDirection === 'descending' ? 'bg-amber-500 ring-4 ring-amber-500/30' : 'bg-amber-500/70 hover:bg-amber-500' }}">
              <x-ui.icon name="arrow-down" class="w-6 h-6" /> Turun
            </button>
          </div>
          @if($canCamp)
            <button type="submit" name="direction" value="camping" class="w-full h-14 rounded-2xl bg-violet-600 hover:bg-violet-500 text-white font-bold flex items-center justify-center gap-2">
              <x-ui.icon name="mountain" class="w-5 h-5" /> Buka tenda di {{ str_replace('_', ' ', $activePos) }}
            </button>
          @endif
          <input type="text" name="notes" class="field" placeholder="Catatan kondisi (opsional): anggota fit, cuaca gerimis…">
        </form>
      @else
        <p class="text-sm text-amber-200 p-3 rounded-xl bg-amber-500/10 ring-1 ring-amber-500/20">Rombongan ini tidak sedang mendaki, jadi tidak bisa dicatat di pos.</p>
      @endif
    </div>
  @elseif($code)
    <x-ui.card>
      <x-ui.empty icon="alert" title="Rombongan tidak ditemukan" :text="'Kode “'.$code.'” tidak terdaftar atau belum berangkat dari basecamp.'" />
    </x-ui.card>
  @endif

  {{-- Rombongan aktif --}}
  <x-ui.card title="Rombongan di jalur" subtitle="Ketuk untuk mencatat tanpa scan" icon="users" :padding="false">
    <x-slot:actions><x-ui.badge tone="brand">{{ $activeGroups->count() }}</x-ui.badge></x-slot:actions>
    <div class="p-3 space-y-2 max-h-[420px] overflow-y-auto">
      @forelse($activeGroups as $reg)
        <x-ui.group-row :reg="$reg" :href="route('management.ranger.index', ['pos' => $activePos, 'code' => $reg->registration_code])" :meta="str_replace('_', ' ', $reg->last_seen_pos ?? 'BASECAMP').' · '.$reg->last_seen_at?->diffForHumans()" :show-status="false">
          @if($reg->last_seen_pos === $activePos)<x-ui.badge tone="neutral" size="xs">Sudah di sini</x-ui.badge>@endif
        </x-ui.group-row>
      @empty
        <x-ui.empty icon="mountain" title="Jalur kosong" />
      @endforelse
    </div>
  </x-ui.card>

  {{-- Log hari ini --}}
  <x-ui.card :title="'Tercatat di '.str_replace('_', ' ', $activePos).' hari ini'" icon="clock" :padding="false">
    <x-slot:actions><x-ui.badge>{{ count($todayLogs) }}</x-ui.badge></x-slot:actions>
    <ul class="divide-y divide-line">
      @forelse($todayLogs as $log)
        <li class="px-4 sm:px-5 py-3 flex items-center gap-3">
          <x-ui.badge :tone="['ascending' => 'success', 'descending' => 'warning', 'camping' => 'violet'][$log->direction] ?? 'neutral'" size="xs">
            {{ ['ascending' => 'Naik', 'descending' => 'Turun', 'camping' => 'Camp'][$log->direction] ?? $log->direction }}
          </x-ui.badge>
          <div class="min-w-0 flex-1">
            <p class="text-sm text-ink truncate">{{ $log->registration?->leader_name ?? 'Rombongan' }}</p>
            <p class="text-[11px] text-subtle font-code">{{ $log->registration?->registration_code }}{{ $log->entry_source === 'radio_ht' ? ' · via HT' : '' }}</p>
          </div>
          <span class="text-xs text-muted tabular-nums">{{ $log->scanned_at?->format('H:i') }}</span>
        </li>
      @empty
        <li><x-ui.empty icon="clock" title="Belum ada catatan hari ini" /></li>
      @endforelse
    </ul>
  </x-ui.card>

  <div class="flex items-start gap-3 p-4 rounded-2xl bg-amber-500/5 ring-1 ring-amber-500/20 text-sm">
    <span class="w-9 h-9 rounded-xl bg-amber-500/15 text-amber-300 flex items-center justify-center shrink-0"><x-ui.icon name="radio" class="w-5 h-5" /></span>
    <div>
      <p class="font-semibold text-ink">Ponsel mati? Lapor via HT {{ $sar }}</p>
      <p class="text-xs text-muted mt-0.5">Sebutkan pos, 3 digit terakhir kode tiket, dan arah. Gatekeeper di basecamp akan mencatatnya.</p>
    </div>
  </div>
</div>

@include('management.partials.qr_scanner', ['formId' => 'rangerSearchForm', 'inputId' => 'rangerCodeInput', 'autoOpen' => false])
@endsection
