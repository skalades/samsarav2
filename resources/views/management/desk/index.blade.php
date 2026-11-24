@extends('management.layout')

@section('title', 'Antrean dari Gate')
@section('page_title', 'Antrean dari Gate')

@section('content')
<x-ui.page-header title="Antrean dari Gate" subtitle="Rombongan yang sudah diperiksa Gatekeeper dan menunggu tindakan kasir" eyebrow="Meja Kasir" />

<div class="flex gap-2 mb-4 p-1 rounded-2xl bg-surface ring-1 ring-line w-full sm:w-fit">
  <a href="{{ route('management.desk.index') }}" class="flex-1 sm:flex-none flex items-center justify-center gap-2 h-11 px-4 rounded-xl text-sm font-semibold transition {{ $tab === 'naik' ? 'bg-raised text-ink ring-1 ring-line-strong' : 'text-muted hover:text-ink' }}">
    <x-ui.icon name="arrow-up" class="w-4 h-4 text-sky-400" /> Siap berangkat
    <x-ui.badge tone="info" size="xs">{{ $ascending->count() }}</x-ui.badge>
  </a>
  <a href="{{ route('management.desk.index', ['tab' => 'turun']) }}" class="flex-1 sm:flex-none flex items-center justify-center gap-2 h-11 px-4 rounded-xl text-sm font-semibold transition {{ $tab === 'turun' ? 'bg-raised text-ink ring-1 ring-line-strong' : 'text-muted hover:text-ink' }}">
    <x-ui.icon name="arrow-down" class="w-4 h-4 text-violet-300" /> Sudah turun
    <x-ui.badge tone="violet" size="xs">{{ $descending->count() }}</x-ui.badge>
  </a>
</div>

@if($tab === 'naik')
  <x-ui.card :padding="false">
    <div class="p-3 space-y-2">
      @forelse($ascending as $reg)
        <x-ui.group-row :reg="$reg" :href="route('management.desk.show', $reg->id)" :meta="'Diperiksa '.$reg->gateUpOfficer?->name.' · '.$reg->gate_up_at?->format('H:i')" :show-status="false">
          @if($reg->ojek_count > 0)<x-ui.badge tone="warning" size="xs">Ojek {{ $reg->ojek_count }}</x-ui.badge>@endif
          <x-ui.badge tone="info" size="xs" class="hidden sm:inline-flex">Cetak surat jalan</x-ui.badge>
        </x-ui.group-row>
      @empty
        <x-ui.empty icon="printer" title="Tidak ada antrean berangkat" text="Rombongan yang lolos pemeriksaan naik di Gate akan muncul di sini secara otomatis." />
      @endforelse
    </div>
  </x-ui.card>
@else
  <x-ui.card :padding="false">
    <div class="p-3 space-y-2">
      @forelse($descending as $reg)
        <x-ui.group-row :reg="$reg" :href="route('management.desk.show', $reg->id)" :meta="'Diperiksa '.$reg->gateDownOfficer?->name.' · '.$reg->gate_down_at?->format('H:i')" :show-status="false">
          <x-ui.badge :tone="$reg->gate_down_clear ? 'success' : 'danger'" size="xs" dot>{{ $reg->gate_down_clear ? 'Clear' : 'Ada selisih' }}</x-ui.badge>
        </x-ui.group-row>
      @empty
        <x-ui.empty icon="arrow-down" title="Tidak ada rombongan turun" text="Rombongan yang sudah dihitung ulang logistiknya di Gate akan muncul di sini." />
      @endforelse
    </div>
  </x-ui.card>
@endif
@endsection
