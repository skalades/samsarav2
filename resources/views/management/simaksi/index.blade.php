@extends('management.layout')

@section('title', 'Verifikasi SIMAKSI')
@section('page_title', 'Verifikasi SIMAKSI')

@section('content')
@php
  $filters = [
    '' => ['Semua', 'all', 'neutral'],
    'pending' => ['Menunggu bayar', 'pending', 'warning'],
    'paid' => ['Lunas · ke Gate', 'paid', 'success'],
    'gate_cleared' => ['Lolos Gate', 'gate_cleared', 'info'],
    'checked_in' => ['Di jalur', 'checked_in', 'brand'],
    'returned' => ['Sudah turun', 'returned', 'violet'],
    'completed' => ['Selesai', 'completed', 'neutral'],
  ];
  $rp = fn ($v) => 'Rp '.number_format((float) $v, 0, ',', '.');
@endphp

<x-ui.page-header title="Verifikasi SIMAKSI" subtitle="Cek berkas & bukti bayar, lalu konfirmasi lunas — info pendakian terkirim otomatis lewat WhatsApp" eyebrow="Kasir Basecamp" />

{{-- Filter status --}}
<div class="flex gap-1.5 overflow-x-auto no-scrollbar -mx-3 px-3 sm:mx-0 sm:px-0 pb-1 mb-3">
  @foreach($filters as $key => [$label, $countKey, $tone])
    @php $active = ($status ?? '') === $key; @endphp
    <a href="{{ route('management.simaksi.index', array_filter(['status' => $key, 'search' => $search])) }}"
       class="shrink-0 h-10 px-3.5 rounded-xl text-sm font-semibold flex items-center gap-2 ring-1 transition {{ $active ? 'bg-raised text-ink ring-brand/40' : 'bg-surface text-muted ring-line hover:text-ink' }}">
      {{ $label }}
      <x-ui.badge :tone="$active ? 'brand' : $tone" size="xs">{{ $statusCounts[$countKey] ?? 0 }}</x-ui.badge>
    </a>
  @endforeach
</div>

{{-- Cari --}}
<form action="{{ route('management.simaksi.index') }}" method="GET" class="flex gap-2 mb-4">
  @if($status)<input type="hidden" name="status" value="{{ $status }}">@endif
  <div class="relative flex-1">
    <x-ui.icon name="search" class="w-5 h-5 text-subtle absolute left-3.5 top-1/2 -translate-y-1/2" />
    <input type="text" name="search" value="{{ $search }}" placeholder="Kode, nama ketua, no. WA, atau 3 digit kode unik" class="field !pl-11">
  </div>
  <x-ui.button type="submit" variant="secondary">Cari</x-ui.button>
  @if($search)<x-ui.button variant="ghost" :href="route('management.simaksi.index', array_filter(['status' => $status]))">Reset</x-ui.button>@endif
</form>

<x-ui.card :padding="false">
  {{-- Mobile: kartu --}}
  <div class="lg:hidden divide-y divide-line">
    @forelse($registrations as $reg)
      @php $unpaid = $reg->payment_status !== 'paid' && ! in_array($reg->status, ['rejected']); @endphp
      <div class="p-3">
        <a href="{{ route('management.simaksi.show', $reg->id) }}" class="flex items-start gap-3">
          <div class="w-11 h-11 shrink-0 rounded-xl bg-canvas ring-1 ring-line flex flex-col items-center justify-center leading-none">
            <span class="font-display font-bold text-base text-ink">{{ $reg->total_climbers }}</span>
            <span class="text-[9px] text-subtle mt-0.5">orang</span>
          </div>
          <div class="min-w-0 flex-1">
            <div class="flex items-start justify-between gap-2">
              <p class="font-semibold text-sm text-ink truncate">{{ $reg->leader_name }}</p>
              <x-ui.badge :tone="$reg->statusTone()" size="xs">{{ $reg->statusLabel() }}</x-ui.badge>
            </div>
            <p class="text-[11px] text-muted mt-0.5"><span class="font-code text-ink/80">{{ $reg->registration_code }}</span> · {{ $reg->hiking_date?->translatedFormat('d M') }}</p>
            <p class="text-sm font-semibold text-ink mt-1 tabular-nums">{{ $rp($reg->total_amount) }}
              @if($reg->unique_code > 0)<span class="text-[11px] text-amber-300 font-normal">kode unik +{{ $reg->unique_code }}</span>@endif
            </p>
          </div>
        </a>
        @if($unpaid || $reg->payment_proof_path)
          <div class="flex gap-2 mt-2.5 pl-14">
            @if($reg->payment_proof_path)
              <button type="button" onclick="showProofModal('{{ asset('storage/'.$reg->payment_proof_path) }}', '{{ $reg->registration_code }}', '{{ addslashes($reg->leader_name) }}', '{{ number_format($reg->total_amount, 0, ',', '.') }}', '{{ strtoupper($reg->payment_method ?? 'TRANSFER') }}')" class="h-9 px-3 rounded-lg bg-sky-500/10 ring-1 ring-sky-500/25 text-sky-300 text-xs font-semibold flex items-center gap-1.5">
                <x-ui.icon name="file" class="w-4 h-4" /> Bukti bayar
              </button>
            @endif
            @if($unpaid)
              @php $methodGuess = $reg->payment_method ?? ($reg->payment_proof_path ? 'transfer' : 'cash_pos'); @endphp
              <form action="{{ route('management.simaksi.paid', $reg->id) }}" method="POST" class="flex-1">
                @csrf
                <input type="hidden" name="redirect_to" value="index">
                <input type="hidden" name="payment_method" value="{{ $methodGuess }}">
                <button type="submit" onclick="return confirm('Konfirmasi LUNAS via {{ strtoupper($methodGuess) }} untuk {{ $reg->registration_code }}? Info pendakian akan dikirim lewat WhatsApp.')" class="w-full h-9 px-3 rounded-lg bg-emerald-600 text-white text-xs font-bold flex items-center justify-center gap-1.5">
                  <x-ui.icon name="check" class="w-4 h-4" /> Konfirmasi lunas
                </button>
              </form>
            @endif
          </div>
        @endif
      </div>
    @empty
      <x-ui.empty icon="ticket" title="Tidak ada permohonan" text="Tidak ada SIMAKSI yang cocok dengan filter ini." />
    @endforelse
  </div>

  {{-- Desktop: tabel --}}
  <div class="hidden lg:block overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="text-[11px] text-subtle bg-raised/40">
        <tr>
          <th class="text-left font-semibold px-5 py-3">Rombongan</th>
          <th class="text-left font-semibold px-3 py-3">Jadwal</th>
          <th class="text-right font-semibold px-3 py-3">Biaya</th>
          <th class="text-left font-semibold px-3 py-3">Status</th>
          <th class="text-right font-semibold px-5 py-3">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-line">
        @forelse($registrations as $reg)
          @php $unpaid = $reg->payment_status !== 'paid' && ! in_array($reg->status, ['rejected']); @endphp
          <tr class="hover:bg-hover/40">
            <td class="px-5 py-3">
              <a href="{{ route('management.simaksi.show', $reg->id) }}" class="font-semibold text-ink hover:text-brand">{{ $reg->leader_name }}</a>
              <p class="text-[11px] text-muted"><span class="font-code">{{ $reg->registration_code }}</span> · {{ $reg->total_climbers }} orang · {{ $reg->leader_phone }}</p>
            </td>
            <td class="px-3 py-3 text-muted whitespace-nowrap">
              <p class="text-ink">{{ $reg->hiking_date?->translatedFormat('d M Y') }}</p>
              <p class="text-[11px]">{{ $reg->isTektok() ? 'Tektok' : 'Camp' }}{{ $reg->return_date && ! $reg->isTektok() ? ' · turun '.$reg->return_date->translatedFormat('d M') : '' }}</p>
            </td>
            <td class="px-3 py-3 text-right whitespace-nowrap">
              <p class="font-semibold text-ink tabular-nums">{{ $rp($reg->total_amount) }}</p>
              @if($reg->payment_proof_path)
                <button type="button" onclick="showProofModal('{{ asset('storage/'.$reg->payment_proof_path) }}', '{{ $reg->registration_code }}', '{{ addslashes($reg->leader_name) }}', '{{ number_format($reg->total_amount, 0, ',', '.') }}', '{{ strtoupper($reg->payment_method ?? 'TRANSFER') }}')" class="text-[11px] text-sky-300 hover:underline">Lihat bukti</button>
              @elseif($reg->unique_code > 0)
                <p class="text-[11px] text-amber-300">kode unik +{{ $reg->unique_code }}</p>
              @endif
            </td>
            <td class="px-3 py-3">
              <x-ui.badge :tone="$reg->statusTone()" dot>{{ $reg->statusLabel() }}</x-ui.badge>
              @if($reg->status === 'needs_revision' && $reg->revision_notes)<p class="text-[11px] text-amber-300/80 mt-1 max-w-[220px] truncate" title="{{ $reg->revision_notes }}">{{ $reg->revision_notes }}</p>@endif
            </td>
            <td class="px-5 py-3">
              <div class="flex justify-end gap-2">
                @if($unpaid)
                  @php $methodGuess = $reg->payment_method ?? ($reg->payment_proof_path ? 'transfer' : 'cash_pos'); @endphp
                  <form action="{{ route('management.simaksi.paid', $reg->id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="redirect_to" value="index">
                    <input type="hidden" name="payment_method" value="{{ $methodGuess }}">
                    <x-ui.button type="submit" variant="success" size="sm" icon="check" onclick="return confirm('Konfirmasi LUNAS via {{ strtoupper($methodGuess) }} untuk {{ $reg->registration_code }}? Info pendakian akan dikirim lewat WhatsApp.')">Lunas</x-ui.button>
                  </form>
                @elseif(in_array($reg->status, ['gate_cleared', 'returned']))
                  <x-ui.button size="sm" variant="info" icon="inbox" :href="route('management.desk.show', $reg->id)">Meja kasir</x-ui.button>
                @endif
                <x-ui.button size="sm" variant="secondary" :href="route('management.simaksi.show', $reg->id)">Detail</x-ui.button>
              </div>
            </td>
          </tr>
        @empty
          <tr><td colspan="5"><x-ui.empty icon="ticket" title="Tidak ada permohonan" text="Tidak ada SIMAKSI yang cocok dengan filter ini." /></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  @if($registrations->hasPages())
    <div class="px-4 py-3 border-t border-line">{{ $registrations->links() }}</div>
  @endif
</x-ui.card>
@endsection
