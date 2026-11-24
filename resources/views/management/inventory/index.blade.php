@extends('management.layout')

@section('title', 'Inventori & Sewa Alat')
@section('page_title', 'Manajemen Inventori & Persewaan Alat')
@section('page_subtitle', 'Stok Alat Gunung, Serah-Terima Peminjaman, & Pengecekan Kondisi Fisik Pengembalian')

@section('content')
<div class="space-y-6">

  <!-- ======================================================== -->
  <!-- 1. ACTIVE EQUIPMENT RENTALS TABLE                        -->
  <!-- ======================================================== -->
  <div class="bg-surface border border-line rounded-xl p-5 space-y-4 font-mono-tech text-xs">
    <div class="flex items-center justify-between pb-3 border-b border-line">
      <div class="flex items-center gap-2">
        <span class="w-2.5 h-2.5 rounded-sm bg-brand"></span>
        <h3 class="font-mono-tech font-bold text-xs uppercase tracking-wider text-white">
          ALAT YANG SEDANG DISEWA / DI PINJAM ({{ $activeRentals->count() }} DATA)
        </h3>
      </div>
      <span class="text-[10px] text-stone-400">SERAH TERIMA PINJAM AKTIF</span>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-left font-mono-tech text-xs">
        <thead>
          <tr class="text-[10px] text-stone-400 uppercase border-b border-line">
            <th class="pb-2">NAMA ALAT</th>
            <th class="pb-2">JUMLAH</th>
            <th class="pb-2">PEMINJAM / KETUA</th>
            <th class="pb-2">KONDISI SAAT SERAH</th>
            <th class="pb-2">PETUGAS PENYERAH</th>
            <th class="pb-2">WAKTU AMBIL</th>
            <th class="pb-2 text-right">AKSI</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-line">
          @forelse($activeRentals as $rental)
            <tr class="hover:bg-raised">
              <td class="py-3 font-bold text-white">{{ $rental->item_name }}</td>
              <td class="py-3 font-bold text-brand">{{ $rental->quantity }} Unit</td>
              <td class="py-3 text-stone-200">
                <div>{{ $rental->customer_name }}</div>
                @if($rental->simaksi)
                  <span class="text-[9px] text-stone-400">SIMAKSI: {{ $rental->simaksi->registration_code }}</span>
                @endif
              </td>
              <td class="py-3 text-stone-300">{{ $rental->condition_out }}</td>
              <td class="py-3 text-stone-400">{{ $rental->handedOfficer?->name ?? 'Kasir Pos' }}</td>
              <td class="py-3 text-stone-400">{{ $rental->handed_out_at ? $rental->handed_out_at->format('d/m H:i') : '-' }}</td>
              <td class="py-3 text-right">
                <button type="button" onclick="openReturnModal({{ $rental->id }}, '{{ addslashes($rental->item_name) }}', '{{ addslashes($rental->customer_name) }}', {{ $rental->quantity }})" class="px-3 py-1 bg-emerald-600 hover:bg-emerald-500 text-white rounded font-bold text-[10px] transition">
                  TERIMA PENGEMBALIAN
                </button>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="py-6 text-center text-stone-400">Saat ini tidak ada alat gunung yang sedang dipinjam.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <!-- ======================================================== -->
  <!-- 2. INVENTORY CATALOG & STOCK TABLE                       -->
  <!-- ======================================================== -->
  <div class="bg-surface border border-line rounded-xl p-5 space-y-4 font-mono-tech text-xs">
    <div class="flex items-center justify-between pb-3 border-b border-line">
      <div class="flex items-center gap-2">
        <span class="w-2.5 h-2.5 rounded-sm bg-brand"></span>
        <h3 class="font-mono-tech font-bold text-xs uppercase tracking-wider text-white">
          KATALOG ALAT & LOGISTIK OPERASIONAL BASECAMP
        </h3>
      </div>
      <span class="text-[10px] text-stone-400">TOTAL: {{ $items->count() }} SKU</span>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-left font-mono-tech text-xs">
        <thead>
          <tr class="text-[10px] text-stone-400 uppercase border-b border-line">
            <th class="pb-2">SKU</th>
            <th class="pb-2">NAMA BARANG / LAYANAN</th>
            <th class="pb-2">KATEGORI</th>
            <th class="pb-2">TIPE</th>
            <th class="pb-2">TARIF RESMI</th>
            <th class="pb-2">STOK TERSEDIA</th>
            <th class="pb-2">TOTAL</th>
            <th class="pb-2 text-right">AKSI</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-line">
          @foreach($items as $item)
            <tr class="hover:bg-raised">
              <td class="py-2.5 font-bold text-brand">{{ $item->sku }}</td>
              <td class="py-2.5 font-bold text-white">{{ $item->name }}</td>
              <td class="py-2.5 text-stone-400 uppercase text-[10px]">{{ $item->category }}</td>
              <td class="py-2.5">
                <span class="px-2 py-0.5 rounded text-[9px] font-bold {{ $item->type === 'rental' ? 'bg-cyan-500/10 text-cyan-400 border border-cyan-500/30' : ($item->type === 'service' ? 'bg-amber-500/10 text-amber-400 border border-amber-500/30' : 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30') }}">
                  {{ strtoupper($item->type) }}
                </span>
              </td>
              <td class="py-2.5 text-white font-bold">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
              <td class="py-2.5 font-bold {{ $item->stock_available < 5 && $item->type !== 'service' ? 'text-amber-400' : 'text-stone-300' }}">
                {{ $item->type === 'service' ? '∞' : $item->stock_available . ' ' . $item->unit }}
              </td>
              <td class="py-2.5 text-stone-400">
                {{ $item->type === 'service' ? '∞' : $item->stock_total }}
              </td>
              <td class="py-2.5 text-right">
                <button type="button" onclick="openStockModal({{ $item->id }}, '{{ addslashes($item->name) }}', {{ $item->stock_available }}, {{ $item->price }}, {{ $item->is_active ? 'true' : 'false' }})" class="px-2 py-1 bg-raised hover:bg-hover text-stone-300 rounded text-[10px]">
                  Ubah Stok
                </button>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>

</div>

<!-- Modal Terima Pengembalian Alat -->
<div id="returnModal" class="hidden fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
  <div class="bg-surface border border-line-strong rounded-xl max-w-md w-full p-5 space-y-4 font-mono-tech text-xs">
    <div class="flex items-center justify-between pb-3 border-b border-line">
      <h3 class="font-display font-bold text-sm text-white uppercase" id="returnModalTitle">TERIMA PENGEMBALIAN ALAT</h3>
      <button onclick="closeReturnModal()" class="text-stone-400 hover:text-white font-bold">✕</button>
    </div>

    <form id="returnForm" method="POST" class="space-y-3">
      @csrf
      <div>
        <label class="block text-[10px] text-stone-400 uppercase mb-1">KONDISI ALAT SAAT KEMBALI:</label>
        <select name="status" id="retStatus" onchange="checkDamagePenalty(this.value)" class="w-full bg-raised border border-line-strong rounded-lg p-2 text-white">
          <option value="returned_good">Bagus / Utuh & Bersih (Stok dikembalikan)</option>
          <option value="damaged">Rusak Ringan / Robek / Frame Patah (Kena Denda)</option>
          <option value="lost">Hilang (Wajib Ganti Unit)</option>
        </select>
      </div>

      <div>
        <label class="block text-[10px] text-stone-400 uppercase mb-1">CATATAN FISIK ALAT:</label>
        <input type="text" name="condition_in" value="Lengkap & bersih tanpa cacat" required class="w-full bg-raised border border-line-strong rounded-lg p-2 text-white text-xs">
      </div>

      <div id="penaltyBox" class="hidden">
        <label class="block text-[10px] text-rose-400 uppercase mb-1 font-bold">DENDA KERUSAKAN / GANTI RUGI (RP):</label>
        <input type="number" name="damage_penalty" value="0" min="0" class="w-full bg-raised border border-line-strong rounded-lg p-2 text-white font-bold">
      </div>

      <div class="flex justify-end gap-2 pt-2">
        <button type="button" onclick="closeReturnModal()" class="px-4 py-2 bg-raised text-stone-300 rounded-lg">BATAL</button>
        <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-lg">SIMPAN PENGEMBALIAN</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Update Stok Item -->
<div id="stockModal" class="hidden fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
  <div class="bg-surface border border-line-strong rounded-xl max-w-md w-full p-5 space-y-4 font-mono-tech text-xs">
    <div class="flex items-center justify-between pb-3 border-b border-line">
      <h3 class="font-display font-bold text-sm text-white uppercase" id="stockModalTitle">UBAH STOK & HARGA</h3>
      <button onclick="closeStockModal()" class="text-stone-400 hover:text-white font-bold">✕</button>
    </div>

    <form id="stockForm" method="POST" class="space-y-3">
      @csrf
      <div>
        <label class="block text-[10px] text-stone-400 uppercase mb-1">STOK TERSEDIA SAAT INI:</label>
        <input type="number" name="stock_available" id="stockAvailInput" min="0" required class="w-full bg-raised border border-line-strong rounded-lg p-2 text-white font-bold">
      </div>

      <div>
        <label class="block text-[10px] text-stone-400 uppercase mb-1">TARIF RESMI (RP):</label>
        <input type="number" name="price" id="stockPriceInput" min="0" required class="w-full bg-raised border border-line-strong rounded-lg p-2 text-white font-bold">
      </div>

      <label class="flex items-center gap-2 pt-1 cursor-pointer">
        <input type="checkbox" name="is_active" id="stockIsActive" value="1" class="rounded bg-black text-brand">
        <span class="text-stone-200">Item Aktif di Katalog POS</span>
      </label>

      <div class="flex justify-end gap-2 pt-2">
        <button type="button" onclick="closeStockModal()" class="px-4 py-2 bg-raised text-stone-300 rounded-lg">BATAL</button>
        <button type="submit" class="px-4 py-2 bg-brand text-black font-bold rounded-lg">SIMPAN</button>
      </div>
    </form>
  </div>
</div>

<script>
  function openReturnModal(id, itemName, customerName, qty) {
    document.getElementById('returnModalTitle').innerText = 'PENGEMBALIAN: ' + itemName + ' (' + qty + ' Unit)';
    document.getElementById('returnForm').action = '/management/inventory/return/' + id;
    document.getElementById('returnModal').classList.remove('hidden');
  }

  function closeReturnModal() {
    document.getElementById('returnModal').classList.add('hidden');
  }

  function checkDamagePenalty(val) {
    const box = document.getElementById('penaltyBox');
    if (val === 'damaged' || val === 'lost') {
      box.classList.remove('hidden');
    } else {
      box.classList.add('hidden');
    }
  }

  function openStockModal(id, name, stock, price, isActive) {
    document.getElementById('stockModalTitle').innerText = 'UBAH DATA: ' + name;
    document.getElementById('stockAvailInput').value = stock;
    document.getElementById('stockPriceInput').value = price;
    document.getElementById('stockIsActive').checked = isActive;
    document.getElementById('stockForm').action = '/management/inventory/update/' + id;
    document.getElementById('stockModal').classList.remove('hidden');
  }

  function closeStockModal() {
    document.getElementById('stockModal').classList.add('hidden');
  }
</script>
@endsection
