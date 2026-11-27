@extends('management.layout')

@section('title', 'Riwayat Transaksi POS')
@section('page_title', 'Riwayat Transaksi Kasir POS')
@section('page_subtitle', 'Jejak transaksi penjualan, sewa, & pembatalan (VOID) dengan otorisasi')

@section('content')
<x-ui.page-header title="Riwayat Transaksi POS" subtitle="Rekapitulasi shift, kas laci, dan jejak transaksi kasir" eyebrow="Kasir Basecamp">
  <x-slot:actions>
    <x-ui.button variant="secondary" size="sm" icon="wallet" :href="route('management.pos.expenses.index')">Buku Kas Keluar</x-ui.button>
    <x-ui.button size="sm" icon="plus" :href="route('management.pos.index')">Transaksi Baru</x-ui.button>
  </x-slot:actions>
</x-ui.page-header>

@if(isset($shiftSummary))
  <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 mb-4">
    <x-ui.stat label="Omzet POS hari ini" :value="rupiah($shiftSummary['total_revenue'] ?? 0)" :hint="($shiftSummary['total_transactions'] ?? 0).' transaksi sukses'" tone="brand" icon="cash" />
    <x-ui.stat label="Kas fisik laci" :value="rupiah($shiftSummary['net_cash_in_drawer'] ?? 0)" hint="Kas masuk − kas keluar" tone="success" icon="wallet" />
    <x-ui.stat label="Payout ojek (tunai)" :value="'−'.rupiah($shiftSummary['ojek_payout_cash'] ?? 0)" hint="Honor driver terbayar" tone="warning" icon="bolt" />
    <x-ui.stat label="Kas keluar" :value="'−'.rupiah($shiftSummary['operational_expenses_cash'] ?? 0)" hint="Petty cash operasional" tone="danger" icon="alert" />
    <x-ui.stat class="col-span-2 sm:col-span-1" label="Non-tunai (QRIS & TF)" :value="rupiah(($shiftSummary['qris_total'] ?? 0) + ($shiftSummary['transfer_total'] ?? 0))" hint="Rekening & merchant" tone="info" icon="ticket" />
  </div>
@endif

<x-ui.card title="Daftar Transaksi Kasir" icon="receipt" :padding="false">
  {{-- Mobile: kartu --}}
  <div class="lg:hidden divide-y divide-line">
    @forelse($orders as $order)
      <div class="p-3.5 space-y-2.5">
        <div class="flex items-start justify-between gap-2">
          <div class="min-w-0">
            <a href="{{ route('management.pos.receipt', $order->id) }}" target="_blank" class="font-code text-sm font-bold text-brand hover:underline">{{ $order->order_number }}</a>
            <p class="font-semibold text-sm text-ink truncate mt-0.5">{{ $order->customer_name }}</p>
            <p class="text-[11px] text-muted">{{ $order->created_at->format('d/m/Y H:i') }} · Kasir: {{ $order->cashier->name }}</p>
            @if($order->simaksi)
              <p class="text-[11px] text-brand-soft font-code mt-0.5">SIMAKSI: {{ $order->simaksi->registration_code }}</p>
            @endif
          </div>
          <div class="text-right shrink-0">
            <p class="font-display font-bold text-base text-ink tabular-nums">{{ rupiah($order->total_amount) }}</p>
            <div class="mt-1 flex items-center justify-end gap-1">
              <x-ui.badge size="xs">{{ strtoupper($order->payment_method) }}</x-ui.badge>
              @if($order->status === 'completed')
                <x-ui.badge tone="success" size="xs">Selesai</x-ui.badge>
              @else
                <x-ui.badge tone="danger" size="xs">VOID</x-ui.badge>
              @endif
            </div>
          </div>
        </div>
        <div class="flex items-center gap-2 pt-1">
          <x-ui.button variant="secondary" size="sm" icon="printer" class="flex-1" :href="route('management.pos.receipt', $order->id)" target="_blank">Cetak Struk</x-ui.button>
          @if($order->status === 'completed')
            <x-ui.button variant="danger" size="sm" onclick="openVoidModal({{ $order->id }}, '{{ $order->order_number }}')">VOID</x-ui.button>
          @endif
        </div>
      </div>
    @empty
      <x-ui.empty icon="receipt" title="Belum ada transaksi kasir" />
    @endforelse
  </div>

  {{-- Desktop: tabel --}}
  <div class="hidden lg:block overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="text-[11px] text-subtle bg-raised/40 uppercase">
        <tr>
          <th class="text-left font-semibold px-5 py-3">No. Struk</th>
          <th class="text-left font-semibold px-3 py-3">Waktu</th>
          <th class="text-left font-semibold px-3 py-3">Kasir</th>
          <th class="text-left font-semibold px-3 py-3">Pelanggan</th>
          <th class="text-right font-semibold px-3 py-3">Total</th>
          <th class="text-center font-semibold px-3 py-3">Metode</th>
          <th class="text-left font-semibold px-3 py-3">Status</th>
          <th class="text-right font-semibold px-5 py-3">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-line">
        @forelse($orders as $order)
          <tr class="hover:bg-hover/40 transition">
            <td class="px-5 py-3 font-code font-bold text-brand whitespace-nowrap">
              <a href="{{ route('management.pos.receipt', $order->id) }}" target="_blank" class="hover:underline">{{ $order->order_number }}</a>
            </td>
            <td class="px-3 py-3 text-muted whitespace-nowrap tabular-nums">{{ $order->created_at->format('d/m/Y H:i') }}</td>
            <td class="px-3 py-3 text-ink">{{ $order->cashier->name }}</td>
            <td class="px-3 py-3 text-ink">
              <div>{{ $order->customer_name }}</div>
              @if($order->simaksi)
                <span class="text-[11px] font-code text-brand-soft">SIMAKSI: {{ $order->simaksi->registration_code }}</span>
              @endif
            </td>
            <td class="px-3 py-3 text-right font-bold text-ink tabular-nums whitespace-nowrap">{{ rupiah($order->total_amount) }}</td>
            <td class="px-3 py-3 text-center"><x-ui.badge size="xs">{{ strtoupper($order->payment_method) }}</x-ui.badge></td>
            <td class="px-3 py-3">
              @if($order->status === 'completed')
                <x-ui.badge tone="success" size="xs" dot>Selesai</x-ui.badge>
              @else
                <x-ui.badge tone="danger" size="xs" title="{{ $order->void_reason }}">Dibatalkan (VOID)</x-ui.badge>
              @endif
            </td>
            <td class="px-5 py-3 text-right whitespace-nowrap">
              <div class="flex items-center justify-end gap-1.5">
                <x-ui.button variant="secondary" size="sm" :href="route('management.pos.receipt', $order->id)" target="_blank">Cetak Struk</x-ui.button>
                @if($order->status === 'completed')
                  <x-ui.button variant="danger" size="sm" onclick="openVoidModal({{ $order->id }}, '{{ $order->order_number }}')">VOID</x-ui.button>
                @endif
              </div>
            </td>
          </tr>
        @empty
          <tr><td colspan="8"><x-ui.empty icon="receipt" title="Belum ada transaksi kasir" /></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  @if($orders->hasPages())
    <div class="px-4 py-3 border-t border-line">{{ $orders->links() }}</div>
  @endif
</x-ui.card>

<x-ui.modal id="voidModal" title="Otorisasi Pembatalan (VOID)" eyebrow="Audit Kasir" maxWidth="sm:max-w-md" onclose="closeVoidModal()">
  <p class="text-sm text-muted leading-relaxed">
    Membatalkan (VOID) transaksi kasir akan mengembalikan stok barang ke inventori dan mencatat jejak audit resmi.
  </p>

  <form id="voidForm" method="POST" class="space-y-4">
    @csrf
    <div>
      <label class="label">Alasan Pembatalan (Wajib min. 5 karakter)</label>
      <textarea name="reason" required rows="3" placeholder="Contoh: Kesalahan input kasir / pelanggan membatalkan pembelian..." class="field"></textarea>
    </div>

    <div class="flex justify-end gap-2 pt-2 border-t border-line">
      <x-ui.button variant="secondary" onclick="closeVoidModal()">Batal</x-ui.button>
      <x-ui.button type="submit" variant="danger" icon="check">Konfirmasi VOID</x-ui.button>
    </div>
  </form>
</x-ui.modal>

@push('scripts')
<script>
  function openVoidModal(id, orderNumber) {
    document.getElementById('voidModalTitle').innerText = 'VOID Transaksi: ' + orderNumber;
    document.getElementById('voidForm').action = '/management/pos/void/' + id;
    document.getElementById('voidModal').classList.remove('hidden');
  }

  function closeVoidModal() {
    document.getElementById('voidModal').classList.add('hidden');
  }
</script>
@endpush
@endsection
