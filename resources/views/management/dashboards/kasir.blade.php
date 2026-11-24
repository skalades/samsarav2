@extends('management.layout')

@section('title', 'Beranda Kasir')
@section('page_title', 'Meja Kasir')

@section('content')
@php $rp = fn ($v) => 'Rp '.number_format((float) $v, 0, ',', '.'); @endphp

<x-ui.page-header :title="'Halo, '.explode(' ', auth()->user()->name)[0]" :subtitle="$today->translatedFormat('l, d F Y').' · verifikasi bayar, cetak dokumen, catat kas'" eyebrow="Kasir Basecamp">
  <x-slot:actions>
    <x-ui.button variant="secondary" size="sm" icon="wallet" :href="route('management.pos.expenses.index')">Kas keluar</x-ui.button>
    <x-ui.button size="sm" icon="cash" :href="route('management.pos.index')">Buka POS</x-ui.button>
  </x-slot:actions>
</x-ui.page-header>

{{-- Tugas mendesak --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
  <x-ui.stat label="Siap cetak (lolos Gate)" :value="$readyToRelease->count()" hint="Surat jalan & voucher ojek" tone="info" icon="printer" :href="route('management.desk.index')" />
  <x-ui.stat label="Selesai turun" :value="$readyToFinalize->count()" hint="Selesaikan & izin parkir" tone="violet" icon="arrow-down" :href="route('management.desk.index', ['tab' => 'turun'])" />
  <x-ui.stat label="Menunggu verifikasi bayar" :value="$awaitingPaymentCount" :hint="$proofUploadedCount.' sudah unggah bukti'" tone="warning" icon="ticket" :href="route('management.simaksi.index', ['status' => 'pending'])" />
  <x-ui.stat label="Kas di laci" :value="$rp($cashDrawer['net_cash_in_drawer'] ?? 0)" :hint="$posTodayCount.' transaksi POS hari ini'" tone="success" icon="cash" :href="route('management.pos.history')" />
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mt-4">
  {{-- Kiriman Gate: naik --}}
  <x-ui.card title="Kiriman Gate · siap berangkat" subtitle="Lolos pemeriksaan alat & logistik. Cetak surat jalan lalu berangkatkan." icon="arrow-up" :padding="false">
    <x-slot:actions><x-ui.badge tone="info">{{ $readyToRelease->count() }}</x-ui.badge></x-slot:actions>
    <div class="p-3 space-y-2">
      @forelse($readyToRelease as $reg)
        <x-ui.group-row :reg="$reg" :href="route('management.desk.show', $reg->id)" :meta="'Gate '.$reg->gate_up_at?->format('H:i')" :show-status="false">
          @if($reg->ojek_count > 0)<x-ui.badge tone="warning" size="xs">Ojek {{ $reg->ojek_count }}</x-ui.badge>@endif
        </x-ui.group-row>
      @empty
        <x-ui.empty icon="printer" title="Belum ada kiriman" text="Rombongan yang lolos pemeriksaan Gate akan muncul di sini." />
      @endforelse
    </div>
  </x-ui.card>

  {{-- Kiriman Gate: turun --}}
  <x-ui.card title="Kiriman Gate · sudah turun" subtitle="Logistik dihitung ulang. Kembalikan sewa alat & cetak izin keluar parkir." icon="arrow-down" :padding="false">
    <x-slot:actions><x-ui.badge tone="violet">{{ $readyToFinalize->count() }}</x-ui.badge></x-slot:actions>
    <div class="p-3 space-y-2">
      @forelse($readyToFinalize as $reg)
        <x-ui.group-row :reg="$reg" :href="route('management.desk.show', $reg->id)" :meta="'Turun '.$reg->gate_down_at?->format('H:i')" :show-status="false">
          <x-ui.badge :tone="$reg->gate_down_clear ? 'success' : 'danger'" size="xs">{{ $reg->gate_down_clear ? 'Clear' : 'Ada selisih' }}</x-ui.badge>
        </x-ui.group-row>
      @empty
        <x-ui.empty icon="arrow-down" title="Belum ada rombongan turun" text="Rombongan yang sudah diperiksa Gate saat turun akan muncul di sini." />
      @endforelse
    </div>
  </x-ui.card>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mt-4">
  {{-- Verifikasi pembayaran --}}
  <x-ui.card class="lg:col-span-2" title="Verifikasi pembayaran" subtitle="Yang sudah unggah bukti transfer tampil paling atas" icon="ticket" :padding="false">
    <x-slot:actions><x-ui.button variant="ghost" size="sm" :href="route('management.simaksi.index', ['status' => 'pending'])">Semua</x-ui.button></x-slot:actions>
    <div class="p-3 space-y-2">
      @forelse($awaitingPayment as $reg)
        <x-ui.group-row :reg="$reg" :href="route('management.simaksi.show', $reg->id)" :meta="$rp($reg->total_amount)" :show-status="false">
          @if($reg->payment_proof_path)
            <x-ui.badge tone="success" size="xs" dot>Bukti ada</x-ui.badge>
          @else
            <x-ui.badge tone="neutral" size="xs">Belum bayar</x-ui.badge>
          @endif
        </x-ui.group-row>
      @empty
        <x-ui.empty icon="check" title="Semua pembayaran terverifikasi" />
      @endforelse
    </div>
  </x-ui.card>

  <div class="space-y-4">
    {{-- Ringkasan kas --}}
    <x-ui.card title="Kas hari ini" icon="wallet">
      <dl class="space-y-2.5 text-sm">
        <div class="flex justify-between"><dt class="text-muted">Tunai masuk</dt><dd class="text-ink font-semibold tabular-nums">{{ $rp($cashDrawer['total_cash_in'] ?? 0) }}</dd></div>
        <div class="flex justify-between"><dt class="text-muted">QRIS</dt><dd class="text-ink tabular-nums">{{ $rp($cashDrawer['qris_total'] ?? 0) }}</dd></div>
        <div class="flex justify-between"><dt class="text-muted">Transfer</dt><dd class="text-ink tabular-nums">{{ $rp($cashDrawer['transfer_total'] ?? 0) }}</dd></div>
        <div class="flex justify-between"><dt class="text-muted">Payout ojek</dt><dd class="text-amber-300 tabular-nums">−{{ $rp($cashDrawer['ojek_payout_cash'] ?? 0) }}</dd></div>
        <div class="flex justify-between"><dt class="text-muted">Kas keluar</dt><dd class="text-rose-300 tabular-nums">−{{ $rp($cashDrawer['operational_expenses_cash'] ?? 0) }}</dd></div>
        <div class="flex justify-between pt-2.5 border-t border-line"><dt class="font-semibold text-ink">Saldo laci</dt><dd class="font-display font-bold text-emerald-300 tabular-nums">{{ $rp($cashDrawer['net_cash_in_drawer'] ?? 0) }}</dd></div>
      </dl>
    </x-ui.card>

    <x-ui.card title="Lainnya" icon="box">
      <div class="grid grid-cols-2 gap-2 text-sm">
        <a href="{{ route('management.inventory.index') }}" class="p-3 rounded-xl bg-raised/60 ring-1 ring-line hover:bg-hover"><p class="text-[11px] text-muted">Alat disewa</p><p class="font-display font-bold text-ink">{{ $activeRentals }}</p></a>
        <a href="{{ route('management.ojek.index') }}" class="p-3 rounded-xl bg-raised/60 ring-1 ring-line hover:bg-hover"><p class="text-[11px] text-muted">Antrean ojek</p><p class="font-display font-bold text-ink">{{ \App\Support\Navigation::badges()['ojek'] }}</p></a>
      </div>
      @if($waFailed->isNotEmpty())
        <div class="mt-3 p-3 rounded-xl bg-rose-500/10 ring-1 ring-rose-500/25">
          <p class="text-xs font-semibold text-rose-200 flex items-center gap-1.5"><x-ui.icon name="whatsapp" class="w-4 h-4" /> {{ $waFailed->count() }} WhatsApp gagal terkirim</p>
          <ul class="mt-1.5 space-y-1 text-[11px] text-rose-200/80">
            @foreach($waFailed as $msg)
              <li class="flex justify-between gap-2">
                <span class="truncate">{{ $msg->registration?->leader_name ?? $msg->phone }}</span>
                @if($msg->registration)<a href="{{ route('management.simaksi.show', $msg->registration->id) }}" class="underline shrink-0">Kirim ulang</a>@endif
              </li>
            @endforeach
          </ul>
        </div>
      @endif
    </x-ui.card>
  </div>
</div>
@endsection
