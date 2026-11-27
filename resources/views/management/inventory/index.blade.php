@extends('management.layout')

@section('title', 'Inventori & Sewa Alat')
@section('page_title', 'Inventori & Persewaan Alat')
@section('page_subtitle', 'Stok alat gunung, serah-terima peminjaman, & cek kondisi pengembalian')

@section('content')
<div class="space-y-5">

  <!-- ======================================================== -->
  <!-- 1. ACTIVE EQUIPMENT RENTALS                              -->
  <!-- ======================================================== -->
  <x-ui.card :title="'Alat Sedang Disewa / Dipinjam ('.$activeRentals->count().')'" subtitle="Serah terima peminjaman aktif" icon="box" :padding="false">
    {{-- Mobile: kartu --}}
    <div class="lg:hidden divide-y divide-line">
      @forelse($activeRentals as $rental)
        <div class="p-3.5 space-y-2.5">
          <div class="flex items-start justify-between gap-2">
            <div class="min-w-0">
              <p class="font-semibold text-sm text-ink">{{ $rental->item_name }} <span class="text-brand font-bold">({{ $rental->quantity }} Unit)</span></p>
              <p class="text-xs text-muted mt-0.5">Peminjam: <strong class="text-ink">{{ $rental->customer_name }}</strong></p>
              @if($rental->simaksi)
                <p class="text-[11px] font-code text-brand-soft mt-0.5">SIMAKSI: {{ $rental->simaksi->registration_code }}</p>
              @endif
              <p class="text-[11px] text-subtle mt-1">Kondisi: {{ $rental->condition_out }} · {{ $rental->handed_out_at ? $rental->handed_out_at->format('d/m H:i') : '-' }}</p>
            </div>
          </div>
          <x-ui.button variant="success" size="sm" block icon="check" onclick="openReturnModal({{ $rental->id }}, '{{ addslashes($rental->item_name) }}', '{{ addslashes($rental->customer_name) }}', {{ $rental->quantity }})">
            Terima Pengembalian
          </x-ui.button>
        </div>
      @empty
        <x-ui.empty icon="box" title="Tidak ada alat sedang dipinjam" />
      @endforelse
    </div>

    {{-- Desktop: tabel --}}
    <div class="hidden lg:block overflow-x-auto">
      <table class="w-full text-left text-sm">
        <thead class="bg-raised/40 border-b border-line text-[11px] text-subtle uppercase">
          <tr>
            <th class="py-3 px-4 font-semibold">Nama Alat</th>
            <th class="py-3 px-3 font-semibold">Jumlah</th>
            <th class="py-3 px-3 font-semibold">Peminjam / Ketua</th>
            <th class="py-3 px-3 font-semibold">Kondisi Serah</th>
            <th class="py-3 px-3 font-semibold">Petugas</th>
            <th class="py-3 px-3 font-semibold">Waktu Ambil</th>
            <th class="py-3 px-4 font-semibold text-right">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-line">
          @forelse($activeRentals as $rental)
            <tr class="hover:bg-hover/40">
              <td class="py-3 px-4 font-semibold text-ink">{{ $rental->item_name }}</td>
              <td class="py-3 px-3 font-bold text-brand whitespace-nowrap">{{ $rental->quantity }} Unit</td>
              <td class="py-3 px-3 text-ink">
                <div>{{ $rental->customer_name }}</div>
                @if($rental->simaksi)
                  <span class="text-[11px] font-code text-muted">SIMAKSI: {{ $rental->simaksi->registration_code }}</span>
                @endif
              </td>
              <td class="py-3 px-3 text-muted">{{ $rental->condition_out }}</td>
              <td class="py-3 px-3 text-muted">{{ $rental->handedOfficer?->name ?? 'Kasir Pos' }}</td>
              <td class="py-3 px-3 text-muted whitespace-nowrap tabular-nums">{{ $rental->handed_out_at ? $rental->handed_out_at->format('d/m H:i') : '-' }}</td>
              <td class="py-3 px-4 text-right whitespace-nowrap">
                <x-ui.button variant="success" size="sm" onclick="openReturnModal({{ $rental->id }}, '{{ addslashes($rental->item_name) }}', '{{ addslashes($rental->customer_name) }}', {{ $rental->quantity }})">
                  Terima Pengembalian
                </x-ui.button>
              </td>
            </tr>
          @empty
            <tr><td colspan="7"><x-ui.empty icon="box" title="Saat ini tidak ada alat gunung yang sedang dipinjam" /></td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </x-ui.card>

  <!-- ======================================================== -->
  <!-- 2. INVENTORY CATALOG & STOCK                             -->
  <!-- ======================================================== -->
  <x-ui.card title="Katalog Alat & Logistik Basecamp" :subtitle="'Total '.$items->count().' SKU'" icon="list" :padding="false">
    {{-- Mobile: kartu --}}
    <div class="lg:hidden divide-y divide-line">
      @foreach($items as $item)
        <div class="p-3.5 flex items-center justify-between gap-3">
          <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-1.5">
              <span class="font-code text-xs font-bold text-brand">{{ $item->sku }}</span>
              <x-ui.badge :tone="$item->type === 'rental' ? 'info' : ($item->type === 'service' ? 'warning' : 'success')" size="xs">{{ strtoupper($item->type) }}</x-ui.badge>
            </div>
            <p class="font-semibold text-sm text-ink truncate mt-1">{{ $item->name }}</p>
            <p class="text-xs text-muted mt-0.5 tabular-nums">
              <strong class="text-ink">Rp {{ number_format($item->price, 0, ',', '.') }}</strong> ·
              Stok: <strong class="{{ $item->stock_available < 5 && $item->type !== 'service' ? 'text-amber-400' : 'text-ink' }}">{{ $item->type === 'service' ? '∞' : $item->stock_available.' '.$item->unit }}</strong>
            </p>
          </div>
          <x-ui.button variant="secondary" size="sm" onclick="openStockModal({{ $item->id }}, '{{ addslashes($item->name) }}', {{ $item->stock_available }}, {{ $item->price }}, {{ $item->is_active ? 'true' : 'false' }})">
            Ubah
          </x-ui.button>
        </div>
      @endforeach
    </div>

    {{-- Desktop: tabel --}}
    <div class="hidden lg:block overflow-x-auto">
      <table class="w-full text-left text-sm">
        <thead class="bg-raised/40 border-b border-line text-[11px] text-subtle uppercase">
          <tr>
            <th class="py-3 px-4 font-semibold">SKU</th>
            <th class="py-3 px-3 font-semibold">Nama Barang / Layanan</th>
            <th class="py-3 px-3 font-semibold">Kategori</th>
            <th class="py-3 px-3 font-semibold">Tipe</th>
            <th class="py-3 px-3 font-semibold text-right">Tarif Resmi</th>
            <th class="py-3 px-3 font-semibold text-right">Stok</th>
            <th class="py-3 px-3 font-semibold text-right">Total</th>
            <th class="py-3 px-4 font-semibold text-right">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-line">
          @foreach($items as $item)
            <tr class="hover:bg-hover/40">
              <td class="py-3 px-4 font-code font-bold text-brand whitespace-nowrap">{{ $item->sku }}</td>
              <td class="py-3 px-3 font-semibold text-ink">{{ $item->name }}</td>
              <td class="py-3 px-3 text-muted uppercase text-xs">{{ $item->category }}</td>
              <td class="py-3 px-3">
                <x-ui.badge :tone="$item->type === 'rental' ? 'info' : ($item->type === 'service' ? 'warning' : 'success')" size="xs">
                  {{ strtoupper($item->type) }}
                </x-ui.badge>
              </td>
              <td class="py-3 px-3 text-right text-ink font-semibold tabular-nums whitespace-nowrap">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
              <td class="py-3 px-3 text-right font-semibold tabular-nums {{ $item->stock_available < 5 && $item->type !== 'service' ? 'text-amber-400' : 'text-ink' }}">
                {{ $item->type === 'service' ? '∞' : $item->stock_available . ' ' . $item->unit }}
              </td>
              <td class="py-3 px-3 text-right text-muted tabular-nums">
                {{ $item->type === 'service' ? '∞' : $item->stock_total }}
              </td>
              <td class="py-3 px-4 text-right whitespace-nowrap">
                <x-ui.button variant="secondary" size="sm" onclick="openStockModal({{ $item->id }}, '{{ addslashes($item->name) }}', {{ $item->stock_available }}, {{ $item->price }}, {{ $item->is_active ? 'true' : 'false' }})">
                  Ubah Stok
                </x-ui.button>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </x-ui.card>

</div>

<x-ui.modal id="returnModal" title="Terima Pengembalian Alat" eyebrow="Serah Terima" maxWidth="sm:max-w-md" onclose="closeReturnModal()">
  <form id="returnForm" method="POST" class="space-y-4">
    @csrf
    <div>
      <label class="label">Kondisi Alat Saat Kembali</label>
      <select name="status" id="retStatus" onchange="checkDamagePenalty(this.value)" class="field">
        <option value="returned_good">Bagus / Utuh &amp; Bersih (Stok dikembalikan)</option>
        <option value="damaged">Rusak Ringan / Robek / Frame Patah (Kena Denda)</option>
        <option value="lost">Hilang (Wajib Ganti Unit)</option>
      </select>
    </div>

    <div>
      <label class="label">Catatan Fisik Alat</label>
      <input type="text" name="condition_in" value="Lengkap & bersih tanpa cacat" required class="field">
    </div>

    <div id="penaltyBox" class="hidden">
      <label class="label !text-rose-400">Denda Kerusakan / Ganti Rugi (Rp)</label>
      <div class="relative">
        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-muted font-semibold text-sm">Rp</span>
        <input type="text" inputmode="numeric" data-currency-input name="damage_penalty" value="0" class="field !pl-10 font-bold">
      </div>
    </div>

    <div class="flex justify-end gap-2 pt-2 border-t border-line">
      <x-ui.button variant="secondary" onclick="closeReturnModal()">Batal</x-ui.button>
      <x-ui.button type="submit" variant="success" icon="check">Simpan Pengembalian</x-ui.button>
    </div>
  </form>
</x-ui.modal>

<x-ui.modal id="stockModal" title="Ubah Stok & Harga" eyebrow="Inventori" maxWidth="sm:max-w-md" onclose="closeStockModal()">
  <form id="stockForm" method="POST" class="space-y-4">
    @csrf
    <div>
      <label class="label">Stok Tersedia Saat Ini</label>
      <input type="number" name="stock_available" id="stockAvailInput" min="0" required class="field font-bold">
    </div>

    <div>
      <label class="label">Tarif Resmi (Rp)</label>
      <div class="relative">
        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-muted font-semibold text-sm">Rp</span>
        <input type="text" inputmode="numeric" data-currency-input name="price" id="stockPriceInput" required class="field !pl-10 font-bold">
      </div>
    </div>

    <label class="flex items-center gap-2.5 pt-1 cursor-pointer select-none">
      <input type="checkbox" name="is_active" id="stockIsActive" value="1" class="w-5 h-5 rounded accent-orange-500">
      <span class="text-sm text-ink">Item Aktif di Katalog POS</span>
    </label>

    <div class="flex justify-end gap-2 pt-2 border-t border-line">
      <x-ui.button variant="secondary" onclick="closeStockModal()">Batal</x-ui.button>
      <x-ui.button type="submit" icon="check">Simpan</x-ui.button>
    </div>
  </form>
</x-ui.modal>

@push('scripts')
<script>
  function openReturnModal(id, itemName, customerName, qty) {
    document.getElementById('returnModalTitle').innerText = 'Pengembalian: ' + itemName + ' (' + qty + ' Unit)';
    document.getElementById('returnForm').action = '/management/inventory/return/' + id;
    document.getElementById('returnModal').classList.remove('hidden');
  }

  function closeReturnModal() {
    document.getElementById('returnModal').classList.add('hidden');
  }

  function checkDamagePenalty(val) {
    const box = document.getElementById('penaltyBox');
    if (val === 'damaged' || val === 'lost') box.classList.remove('hidden');
    else box.classList.add('hidden');
  }

  function openStockModal(id, name, stock, price, isActive) {
    document.getElementById('stockModalTitle').innerText = 'Ubah Data: ' + name;
    document.getElementById('stockAvailInput').value = stock;
    if (typeof setCurrencyInputValue === 'function') {
      setCurrencyInputValue('stockPriceInput', price);
    } else {
      document.getElementById('stockPriceInput').value = price;
    }
    document.getElementById('stockIsActive').checked = isActive;
    document.getElementById('stockForm').action = '/management/inventory/update/' + id;
    document.getElementById('stockModal').classList.remove('hidden');
  }

  function closeStockModal() {
    document.getElementById('stockModal').classList.add('hidden');
  }
</script>
@endpush
@endsection
