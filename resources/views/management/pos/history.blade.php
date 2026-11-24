@extends('management.layout')

@section('title', 'Riwayat Transaksi POS')
@section('page_title', 'Riwayat Transaksi Kasir POS')
@section('page_subtitle', 'Jejak Transaksi Penjualan, Sewa, & Pembatalan (VOID) dengan Otorisasi')

@section('content')
<div class="space-y-6">

  <!-- ======================================================== -->
  <!-- REKAPITULASI SHIFT & TUTUP KASIR HARI INI               -->
  <!-- ======================================================== -->
  @if(isset($shiftSummary))
    <div class="bg-surface border border-line rounded-xl p-5 space-y-4 font-mono-tech">
      <div class="flex items-center justify-between pb-3 border-b border-line">
        <div class="flex items-center gap-2">
          <span class="w-2.5 h-2.5 rounded-sm bg-emerald-400"></span>
          <h3 class="font-bold text-xs uppercase tracking-wider text-white">
            REKAPITULASI SHIFT & REKONSILIASI KAS LACI (HARI INI)
          </h3>
        </div>
        <div class="flex items-center gap-3">
          <a href="{{ route('management.pos.expenses.index') }}" class="text-[10px] text-rose-400 hover:underline font-mono-tech flex items-center gap-1">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <span>Buku Kas Keluar</span>
          </a>
          <span class="text-[10px] text-stone-500">|</span>
          <span class="text-[10px] text-stone-400 font-mono-tech">REKONSILIASI KAS RESMI</span>
        </div>
      </div>

      <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 text-xs">
        <div class="p-3 bg-raised border border-line rounded-lg">
          <span class="text-[9px] text-stone-400 block uppercase">TOTAL OMZET POS</span>
          <span class="font-bold text-base text-brand block mt-0.5">{{ rupiah($shiftSummary['total_revenue'] ?? 0) }}</span>
          <span class="text-[9px] text-stone-500">{{ $shiftSummary['total_transactions'] ?? 0 }} Transaksi Sukses</span>
        </div>
        <div class="p-3 bg-raised border border-emerald-500/40 rounded-lg">
          <span class="text-[9px] text-emerald-400 block uppercase font-bold">KAS FISIK LACI (NET CASH)</span>
          <span class="font-black text-base text-emerald-400 block mt-0.5">{{ rupiah($shiftSummary['net_cash_in_drawer'] ?? 0) }}</span>
          <span class="text-[9px] text-stone-400">Kas Masuk - Kas Keluar</span>
        </div>
        <div class="p-3 bg-raised border border-line rounded-lg">
          <span class="text-[9px] text-stone-400 block uppercase">PAYOUT OJEK (TUNAI)</span>
          <span class="font-bold text-sm text-amber-400 block mt-0.5">-{{ rupiah($shiftSummary['ojek_payout_cash'] ?? 0) }}</span>
          <span class="text-[9px] text-stone-500">Honor Driver Terbayar</span>
        </div>
        <div class="p-3 bg-raised border border-line rounded-lg">
          <span class="text-[9px] text-stone-400 block uppercase">KAS KELUAR OPERASIONAL</span>
          <span class="font-bold text-sm text-rose-400 block mt-0.5">-{{ rupiah($shiftSummary['operational_expenses_cash'] ?? 0) }}</span>
          <span class="text-[9px] text-stone-500">Petty Cash / Mendadak</span>
        </div>
        <div class="p-3 bg-raised border border-line rounded-lg">
          <span class="text-[9px] text-stone-400 block uppercase">NON-TUNAI (QRIS & TF)</span>
          <span class="font-bold text-sm text-cyan-400 block mt-0.5">{{ rupiah(($shiftSummary['qris_total'] ?? 0) + ($shiftSummary['transfer_total'] ?? 0)) }}</span>
          <span class="text-[9px] text-stone-500">Rekening & Merchant</span>
        </div>
      </div>
    </div>
  @endif

  <div class="bg-surface border border-line rounded-xl p-5 space-y-4">
    <div class="flex items-center justify-between pb-3 border-b border-line">
      <div class="flex items-center gap-2">
        <div class="w-2.5 h-2.5 rounded-sm bg-brand"></div>
        <h3 class="font-mono-tech font-bold text-xs uppercase tracking-wider text-white">
          DAFTAR TRANSAKSI KASIR
        </h3>
      </div>
      <a href="{{ route('management.pos.index') }}" class="px-3 py-1.5 rounded bg-brand text-black font-mono-tech font-bold text-xs">
        + TRANSAKSI BARU
      </a>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-left font-mono-tech text-xs">
        <thead>
          <tr class="text-[10px] text-stone-400 uppercase border-b border-line">
            <th class="pb-3">NO. STRUK</th>
            <th class="pb-3">WAKTU</th>
            <th class="pb-3">KASIR</th>
            <th class="pb-3">PELANGGAN</th>
            <th class="pb-3">TOTAL</th>
            <th class="pb-3">METODE</th>
            <th class="pb-3">STATUS</th>
            <th class="pb-3 text-right">AKSI</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-line">
          @forelse($orders as $order)
            <tr class="hover:bg-raised transition">
              <td class="py-3 font-bold text-brand">
                <a href="{{ route('management.pos.receipt', $order->id) }}" target="_blank" class="hover:underline">
                  {{ $order->order_number }}
                </a>
              </td>
              <td class="py-3 text-stone-400">{{ $order->created_at->format('d/m/Y H:i') }}</td>
              <td class="py-3 text-white">{{ $order->cashier->name }}</td>
              <td class="py-3 text-stone-200">
                <div>{{ $order->customer_name }}</div>
                @if($order->simaksi)
                  <span class="text-[9px] text-brand">SIMAKSI: {{ $order->simaksi->registration_code }}</span>
                @endif
              </td>
              <td class="py-3 font-bold text-white">{{ rupiah($order->total_amount) }}</td>
              <td class="py-3 text-stone-300 uppercase">{{ $order->payment_method }}</td>
              <td class="py-3">
                @if($order->status === 'completed')
                  <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">
                    SELESAI
                  </span>
                @else
                  <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/30" title="{{ $order->void_reason }}">
                    DIBATALKAN (VOID)
                  </span>
                @endif
              </td>
              <td class="py-3 text-right space-x-1.5">
                <a href="{{ route('management.pos.receipt', $order->id) }}" target="_blank" class="px-2 py-1 bg-raised hover:bg-hover text-stone-300 rounded text-[10px]">
                  Cetak Struk
                </a>
                @if($order->status === 'completed')
                  <button type="button" onclick="openVoidModal({{ $order->id }}, '{{ $order->order_number }}')" class="px-2 py-1 bg-rose-950/40 hover:bg-rose-900/60 text-rose-300 border border-rose-500/30 rounded text-[10px]">
                    VOID
                  </button>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="8" class="py-6 text-center text-stone-400">Belum ada transaksi kasir.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="pt-4 border-t border-line">
      {{ $orders->links() }}
    </div>
  </div>

</div>

<!-- Modal VOID Transaksi -->
<div id="voidModal" class="hidden fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
  <div class="bg-surface border border-line-strong rounded-xl max-w-md w-full p-5 space-y-4 font-mono-tech text-xs">
    <div class="flex items-center justify-between pb-3 border-b border-line">
      <h3 class="font-display font-bold text-sm text-rose-400 uppercase" id="voidModalTitle">OTORISASI PEMBATALAN (VOID)</h3>
      <button onclick="closeVoidModal()" class="text-stone-400 hover:text-white font-bold">✕</button>
    </div>

    <p class="text-stone-300 leading-relaxed">
      Membatalkan (VOID) transaksi kasir akan mengembalikan stok barang ke inventori dan mencatat jejak audit resmi.
    </p>

    <form id="voidForm" method="POST" class="space-y-3">
      @csrf
      <div>
        <label class="block text-[10px] text-stone-400 uppercase mb-1">ALASAN PEMBATALAN (WAJIB MIN. 5 KARAKTER):</label>
        <textarea name="reason" required rows="3" placeholder="Contoh: Kesalahan input kasir / pelanggan membatalkan pembelian..." class="w-full bg-raised border border-line-strong rounded-lg p-2.5 text-xs text-white focus:outline-none focus:border-rose-500"></textarea>
      </div>

      <div class="flex justify-end gap-2 pt-2">
        <button type="button" onclick="closeVoidModal()" class="px-4 py-2 bg-raised text-stone-300 rounded-lg">BATAL</button>
        <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-500 text-white font-bold rounded-lg">KONFIRMASI VOID</button>
      </div>
    </form>
  </div>
</div>

<script>
  function openVoidModal(id, orderNumber) {
    document.getElementById('voidModalTitle').innerText = 'VOID TRANSAKSI: ' + orderNumber;
    document.getElementById('voidForm').action = '/management/pos/void/' + id;
    document.getElementById('voidModal').classList.remove('hidden');
  }

  function closeVoidModal() {
    document.getElementById('voidModal').classList.add('hidden');
  }
</script>
@endsection
