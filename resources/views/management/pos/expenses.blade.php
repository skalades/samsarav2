@extends('management.layout')

@section('title', 'Buku Kas Keluar & Pengeluaran Operasional Mendadak')

@section('content')
<div class="space-y-6">

  <!-- ======================================================== -->
  <!-- HEADER & QUICK ACTIONS                                   -->
  <!-- ======================================================== -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-line">
    <div>
      <div class="flex items-center gap-2">
        <span class="w-2.5 h-2.5 rounded-sm bg-rose-500"></span>
        <h1 class="font-display font-black text-xl text-white tracking-wide uppercase">BUKU PENGELUARAN KAS OPERASIONAL</h1>
      </div>
      <p class="font-mono-tech text-xs text-stone-400 mt-0.5">Pencatatan kas keluar mendadak (Petty Cash), logistik darurat, BBM genset, dan operasional pos basecamp.</p>
    </div>

    <div class="flex items-center gap-3">
      <a href="{{ route('management.pos.index') }}" class="px-3.5 py-2 rounded-lg bg-raised border border-line-strong text-stone-300 hover:text-white font-mono-tech text-xs font-bold transition flex items-center gap-2">
        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
        <span>Ke Mesin POS</span>
      </a>

      <button type="button" onclick="openExpenseModal()" class="tap-active px-4 py-2 bg-rose-600 hover:bg-rose-500 text-white font-mono-tech text-xs font-bold uppercase rounded-lg shadow-lg shadow-rose-600/20 transition flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
        <span>+ Catat Kas Keluar Mendadak</span>
      </button>
    </div>
  </div>

  @if(session('success'))
    <div class="p-3.5 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 font-mono-tech text-xs flex items-center justify-between">
      <div class="flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        <span>{{ session('success') }}</span>
      </div>
    </div>
  @endif

  @if(session('error'))
    <div class="p-3.5 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 font-mono-tech text-xs flex items-center justify-between">
      <div class="flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        <span>{{ session('error') }}</span>
      </div>
    </div>
  @endif

  <!-- ======================================================== -->
  <!-- STATS CARDS: REKAP KAS KELUAR HARI INI                   -->
  <!-- ======================================================== -->
  <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <div class="bg-surface border border-line rounded-xl p-4 space-y-1">
      <div class="flex items-center justify-between">
        <span class="font-mono-tech text-[10px] text-stone-400 uppercase tracking-wider">TOTAL KAS KELUAR HARI INI</span>
        <span class="w-2 h-2 rounded-full bg-rose-500"></span>
      </div>
      <div class="font-mono-tech text-2xl font-black text-rose-400">
        Rp {{ number_format($todayTotal, 0, ',', '.') }}
      </div>
      <p class="font-mono-tech text-[10px] text-stone-500">{{ $todayCount }} pengeluaran operasional tercatat hari ini</p>
    </div>

    <div class="bg-surface border border-line rounded-xl p-4 space-y-1">
      <div class="flex items-center justify-between">
        <span class="font-mono-tech text-[10px] text-stone-400 uppercase tracking-wider">PENGELUARAN TUNAI (KURANGI LACI)</span>
        <span class="px-1.5 py-0.5 rounded bg-amber-500/10 text-amber-400 font-mono-tech text-[9px] font-bold">CASH OUT</span>
      </div>
      <div class="font-mono-tech text-2xl font-black text-amber-400">
        Rp {{ number_format($todayCashTotal, 0, ',', '.') }}
      </div>
      <p class="font-mono-tech text-[10px] text-stone-500">Mengurangi saldo kas fisik kasir secara otomatis</p>
    </div>

    <div class="bg-surface border border-line rounded-xl p-4 space-y-1">
      <div class="flex items-center justify-between">
        <span class="font-mono-tech text-[10px] text-stone-400 uppercase tracking-wider">AKUNTABILITAS & AUDIT</span>
        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
      </div>
      <div class="font-mono-tech text-sm font-bold text-white pt-1">
        100% Jejak Digital Tercatat
      </div>
      <p class="font-mono-tech text-[10px] text-stone-400">Setiap pengeluaran kas tercatat di Audit Log & dapat dicetak tanda terimanya.</p>
    </div>
  </div>

  <!-- ======================================================== -->
  <!-- FILTER & SEARCH                                          -->
  <!-- ======================================================== -->
  <div class="bg-surface border border-line rounded-xl p-4 flex flex-col md:flex-row gap-3 items-center justify-between">
    <div class="flex flex-wrap items-center gap-2 text-xs font-mono-tech w-full md:w-auto">
      <a href="{{ route('management.pos.expenses.index') }}" class="px-3 py-1.5 rounded-lg border transition {{ empty($category) ? 'bg-brand text-black font-bold border-brand' : 'bg-raised text-stone-300 border-line-strong hover:bg-hover' }}">
        Semua Kategori
      </a>
      <a href="{{ route('management.pos.expenses.index', ['category' => 'bbm_genset']) }}" class="px-3 py-1.5 rounded-lg border transition {{ $category === 'bbm_genset' ? 'bg-brand text-black font-bold border-brand' : 'bg-raised text-stone-300 border-line-strong hover:bg-hover' }}">
        BBM Genset
      </a>
      <a href="{{ route('management.pos.expenses.index', ['category' => 'logistik_sar']) }}" class="px-3 py-1.5 rounded-lg border transition {{ $category === 'logistik_sar' ? 'bg-brand text-black font-bold border-brand' : 'bg-raised text-stone-300 border-line-strong hover:bg-hover' }}">
        Logistik SAR / P3K
      </a>
      <a href="{{ route('management.pos.expenses.index', ['category' => 'operasional_pos']) }}" class="px-3 py-1.5 rounded-lg border transition {{ $category === 'operasional_pos' ? 'bg-brand text-black font-bold border-brand' : 'bg-raised text-stone-300 border-line-strong hover:bg-hover' }}">
        Operasional Pos
      </a>
      <a href="{{ route('management.pos.expenses.index', ['category' => 'kebersihan_sampah']) }}" class="px-3 py-1.5 rounded-lg border transition {{ $category === 'kebersihan_sampah' ? 'bg-brand text-black font-bold border-brand' : 'bg-raised text-stone-300 border-line-strong hover:bg-hover' }}">
        Sampah & Karung
      </a>
    </div>

    <form method="GET" action="{{ route('management.pos.expenses.index') }}" class="w-full md:w-72 relative">
      @if($category)<input type="hidden" name="category" value="{{ $category }}">@endif
      <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari kode / toko / catatan..." class="w-full bg-raised border border-line-strong rounded-lg pl-9 pr-3 py-1.5 text-xs text-white placeholder-stone-500 font-mono-tech focus:outline-none focus:border-brand">
      <svg class="w-4 h-4 text-stone-500 absolute left-2.5 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
    </form>
  </div>

  <!-- ======================================================== -->
  <!-- TABEL PENGELUARAN KAS KELUAR                             -->
  <!-- ======================================================== -->
  <div class="bg-surface border border-line rounded-xl overflow-hidden shadow-xl">
    <div class="overflow-x-auto">
      <table class="w-full text-left font-mono-tech text-xs">
        <thead class="bg-canvas border-b border-line text-[10px] text-stone-400 uppercase tracking-wider">
          <tr>
            <th class="py-3 px-4">Kode Kas Keluar</th>
            <th class="py-3 px-4">Waktu</th>
            <th class="py-3 px-4">Kategori Keperluan</th>
            <th class="py-3 px-4">Penerima Dana</th>
            <th class="py-3 px-4 text-right">Nominal (Rp)</th>
            <th class="py-3 px-4 text-center">Metode</th>
            <th class="py-3 px-4">Kasir Pencatat</th>
            <th class="py-3 px-4">Status</th>
            <th class="py-3 px-4 text-center">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-line">
          @forelse($expenses as $exp)
            <tr class="hover:bg-raised/70 transition {{ $exp->status === 'voided' ? 'opacity-40 line-through' : '' }}">
              <td class="py-3.5 px-4 font-bold text-white">
                <span class="text-rose-400">{{ $exp->expense_code }}</span>
              </td>
              <td class="py-3.5 px-4 text-stone-300 text-[11px]">
                {{ $exp->created_at->format('d/m/Y H:i') }}
              </td>
              <td class="py-3.5 px-4">
                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-[#1A1F29] border border-line-strong text-stone-200">
                  {{ $exp->category_label }}
                </span>
                <div class="text-[10px] text-stone-400 mt-1 max-w-xs truncate" title="{{ $exp->notes }}">
                  {{ $exp->notes }}
                </div>
              </td>
              <td class="py-3.5 px-4 text-stone-200 font-bold">
                {{ $exp->recipient_name }}
              </td>
              <td class="py-3.5 px-4 text-right font-black text-rose-400 text-sm">
                - Rp {{ number_format($exp->amount, 0, ',', '.') }}
              </td>
              <td class="py-3.5 px-4 text-center">
                <span class="px-1.5 py-0.5 rounded text-[9px] font-bold {{ $exp->payment_method === 'cash' ? 'bg-amber-500/10 text-amber-400 border border-amber-500/30' : 'bg-blue-500/10 text-blue-400 border border-blue-500/30' }}">
                  {{ strtoupper($exp->payment_method) }}
                </span>
              </td>
              <td class="py-3.5 px-4 text-stone-300 text-[11px]">
                {{ $exp->cashier?->name ?? 'Kasir Posko' }}
              </td>
              <td class="py-3.5 px-4">
                @if($exp->status === 'active')
                  <span class="px-2 py-0.5 rounded bg-emerald-500/10 border border-emerald-500/30 text-[9px] font-bold text-emerald-400">
                    SAH
                  </span>
                @else
                  <span class="px-2 py-0.5 rounded bg-rose-500/10 border border-rose-500/30 text-[9px] font-bold text-rose-400" title="{{ $exp->void_reason }}">
                    VOID (BATAL)
                  </span>
                @endif
              </td>
              <td class="py-3.5 px-4 text-center">
                <div class="flex items-center justify-center gap-1.5">
                  <a href="{{ route('management.pos.expenses.print', $exp->id) }}" target="_blank" class="p-1.5 rounded bg-raised border border-line-strong text-stone-300 hover:text-white hover:bg-hover transition" title="Cetak Bukti Pengeluaran Kas">
                    <svg class="w-3.5 h-3.5 text-stone-400 hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                  </a>

                  @if($exp->status === 'active')
                    <button type="button" onclick="openVoidModal({{ $exp->id }}, '{{ $exp->expense_code }}', {{ $exp->amount }})" class="p-1.5 rounded bg-raised border border-rose-500/30 text-rose-400 hover:bg-rose-500/20 transition" title="Batalkan (Void)">
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                  @endif
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="9" class="py-12 text-center text-stone-500">
                <div class="flex flex-col items-center justify-center gap-2">
                  <svg class="w-8 h-8 text-stone-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                  <span>Belum ada data pengeluaran kas operasional yang tercatat.</span>
                  <button type="button" onclick="openExpenseModal()" class="text-xs text-rose-400 hover:underline mt-1">+ Catat Pengeluaran Pertama</button>
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if($expenses->hasPages())
      <div class="p-4 border-t border-line bg-canvas">
        {{ $expenses->links() }}
      </div>
    @endif
  </div>

</div>

<!-- ======================================================== -->
<!-- MODAL: CATAT PENGELUARAN MENDADAK (PETTY CASH)            -->
<!-- ======================================================== -->
<div id="expenseModal" class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm hidden items-center justify-center p-4">
  <div class="bg-surface border border-line-strong rounded-2xl w-full max-w-lg overflow-hidden shadow-2xl space-y-4">
    
    <div class="p-4 border-b border-line flex items-center justify-between bg-canvas">
      <div class="flex items-center gap-2">
        <div class="w-3 h-3 rounded-full bg-rose-500"></div>
        <h3 class="font-display font-black text-sm uppercase text-white tracking-wider">FORM KAS KELUAR OPERASIONAL (MENDADAK)</h3>
      </div>
      <button type="button" onclick="closeExpenseModal()" class="text-stone-400 hover:text-white text-lg font-bold">✕</button>
    </div>

    <form method="POST" action="{{ route('management.pos.expenses.store') }}" enctype="multipart/form-data" class="p-5 space-y-4 font-mono-tech text-xs">
      @csrf

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <!-- Kategori -->
        <div>
          <label class="block text-[10px] text-stone-400 uppercase font-bold mb-1">Kategori Pengeluaran *</label>
          <select name="category" required class="w-full bg-raised border border-line-strong rounded-lg px-3 py-2 text-white text-xs focus:outline-none focus:border-rose-500">
            <option value="bbm_genset">BBM Genset & Listrik</option>
            <option value="operasional_pos">Operasional Pos & ATK</option>
            <option value="logistik_sar">P3K & Logistik Darurat SAR</option>
            <option value="konsumsi_petugas">Konsumsi Ranger & Posko</option>
            <option value="kebersihan_sampah">Kantong Sampah & Kebersihan</option>
            <option value="perbaikan_alat">Pemeliharaan Jalur & Alat</option>
            <option value="lainnya">Kebutuhan Mendadak Lainnya</option>
          </select>
        </div>

        <!-- Metode Pembayaran -->
        <div>
          <label class="block text-[10px] text-stone-400 uppercase font-bold mb-1">Metode Pengeluaran *</label>
          <select name="payment_method" required class="w-full bg-raised border border-line-strong rounded-lg px-3 py-2 text-white text-xs focus:outline-none focus:border-rose-500">
            <option value="cash">Tunai (Kurangi Laci Kasir)</option>
            <option value="transfer">Transfer Bank</option>
          </select>
        </div>
      </div>

      <!-- Nominal -->
      <div>
        <label class="block text-[10px] text-stone-400 uppercase font-bold mb-1">Nominal Pengeluaran (Rp) *</label>
        <div class="relative">
          <span class="absolute left-3 top-2 text-stone-500 font-bold">Rp</span>
          <input type="number" name="amount" min="1000" step="500" required placeholder="Contoh: 50000" class="w-full bg-raised border border-line-strong rounded-lg pl-10 pr-3 py-2 text-white font-bold text-sm focus:outline-none focus:border-rose-500">
        </div>
      </div>

      <!-- Penerima Dana / Toko -->
      <div>
        <label class="block text-[10px] text-stone-400 uppercase font-bold mb-1">Nama Toko / Penerima Dana *</label>
        <input type="text" name="recipient_name" required placeholder="Contoh: SPBU Simpang / Toko Barokah / Tim SAR" class="w-full bg-raised border border-line-strong rounded-lg px-3 py-2 text-white text-xs focus:outline-none focus:border-rose-500">
      </div>

      <!-- Keterangan -->
      <div>
        <label class="block text-[10px] text-stone-400 uppercase font-bold mb-1">Keperluan / Keterangan Mendadak *</label>
        <textarea name="notes" rows="3" required placeholder="Tulis rincian barang yang dibeli atau alasan pengeluaran mendadak..." class="w-full bg-raised border border-line-strong rounded-lg px-3 py-2 text-white text-xs focus:outline-none focus:border-rose-500"></textarea>
      </div>

      <!-- Lampiran Nota / Foto Struk -->
      <div>
        <label class="block text-[10px] text-stone-400 uppercase font-bold mb-1">Foto Nota / Bukti Kasbon (Opsional)</label>
        <input type="file" name="receipt_photo" accept="image/*,application/pdf" class="w-full bg-raised border border-line-strong rounded-lg px-3 py-1.5 text-stone-300 text-[11px] file:mr-2 file:py-1 file:px-2.5 file:rounded file:border-0 file:bg-hover file:text-stone-300 file:font-mono-tech file:text-xs hover:file:bg-hover">
      </div>

      <div class="pt-3 border-t border-line flex items-center justify-end gap-3">
        <button type="button" onclick="closeExpenseModal()" class="px-4 py-2 rounded-lg bg-raised border border-line-strong text-stone-300 hover:text-white font-bold text-xs uppercase">
          Batal
        </button>
        <button type="submit" class="tap-active px-5 py-2 rounded-lg bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs uppercase tracking-wider transition shadow-lg shadow-rose-600/20">
          Simpan Kas Keluar
        </button>
      </div>
    </form>
  </div>
</div>

<!-- ======================================================== -->
<!-- MODAL: VOID / BATALKAN PENGELUARAN                       -->
<!-- ======================================================== -->
<div id="voidModal" class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm hidden items-center justify-center p-4">
  <div class="bg-surface border border-rose-500/30 rounded-2xl w-full max-w-md overflow-hidden shadow-2xl space-y-4">
    <div class="p-4 border-b border-line bg-canvas flex items-center justify-between">
      <div class="flex items-center gap-2">
        <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
        <h3 class="font-display font-black text-sm uppercase text-white tracking-wider">BATALKAN (VOID) KAS KELUAR</h3>
      </div>
      <button type="button" onclick="closeVoidModal()" class="text-stone-400 hover:text-white text-lg font-bold">✕</button>
    </div>

    <form id="voidForm" method="POST" action="" class="p-5 space-y-4 font-mono-tech text-xs">
      @csrf
      <p class="text-stone-300 leading-relaxed text-xs">
        Apakah Anda yakin ingin membatalkan pengeluaran <span id="voidCodeText" class="font-bold text-rose-400"></span> sebesar <span id="voidAmountText" class="font-bold text-white"></span>? Dana kas keluar ini akan dinetralkan kembali pada buku kas.
      </p>

      <div>
        <label class="block text-[10px] text-stone-400 uppercase font-bold mb-1">Alasan Pembatalan (VOID) *</label>
        <input type="text" name="void_reason" required minlength="5" placeholder="Contoh: Salah input nominal / Nota dibatalkan supplier" class="w-full bg-raised border border-line-strong rounded-lg px-3 py-2 text-white text-xs focus:outline-none focus:border-rose-500">
      </div>

      <div class="pt-3 border-t border-line flex items-center justify-end gap-3">
        <button type="button" onclick="closeVoidModal()" class="px-4 py-2 rounded-lg bg-raised border border-line-strong text-stone-300 hover:text-white font-bold text-xs uppercase">
          Tutup
        </button>
        <button type="submit" class="tap-active px-5 py-2 rounded-lg bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs uppercase tracking-wider transition">
          Konfirmasi VOID
        </button>
      </div>
    </form>
  </div>
</div>

<script>
  function openExpenseModal() {
    const modal = document.getElementById('expenseModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
  }

  function closeExpenseModal() {
    const modal = document.getElementById('expenseModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
  }

  function openVoidModal(id, code, amount) {
    const modal = document.getElementById('voidModal');
    const form = document.getElementById('voidForm');
    form.action = `/management/pos/expenses/${id}/void`;
    document.getElementById('voidCodeText').textContent = code;
    document.getElementById('voidAmountText').textContent = 'Rp ' + Number(amount).toLocaleString('id-ID');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
  }

  function closeVoidModal() {
    const modal = document.getElementById('voidModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
  }
</script>
@endsection
