@extends('management.layout')

@section('title', 'Buku Kas Keluar & Pengeluaran Operasional Mendadak')
@section('page_title', 'Buku Kas Keluar')
@section('page_subtitle', 'Pencatatan petty cash, logistik darurat, BBM genset, & operasional pos')

@section('content')
<x-ui.page-header title="Buku Pengeluaran Kas Operasional" subtitle="Pencatatan kas keluar mendadak (Petty Cash), logistik darurat, BBM genset, dan operasional pos basecamp" eyebrow="Kasir Basecamp">
  <x-slot:actions>
    <x-ui.button variant="secondary" size="sm" icon="cash" :href="route('management.pos.index')">Ke Mesin POS</x-ui.button>
    <x-ui.button variant="danger" size="sm" icon="plus" onclick="openExpenseModal()">Catat Kas Keluar</x-ui.button>
  </x-slot:actions>
</x-ui.page-header>

{{-- Stats --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-4">
  <x-ui.stat label="Total kas keluar hari ini" :value="'Rp '.number_format($todayTotal, 0, ',', '.')" :hint="$todayCount.' pengeluaran tercatat hari ini'" tone="danger" icon="wallet" />
  <x-ui.stat label="Pengeluaran tunai (kurangi laci)" :value="'Rp '.number_format($todayCashTotal, 0, ',', '.')" hint="Mengurangi saldo kas fisik kasir" tone="warning" icon="cash" />
  <x-ui.stat label="Akuntabilitas & audit" value="100% Tercatat" hint="Tercatat di Audit Log & dapat dicetak" tone="success" icon="shield" />
</div>

{{-- Filter & Search --}}
<div class="bg-surface ring-1 ring-line rounded-2xl p-3.5 sm:p-4 flex flex-col lg:flex-row gap-3 lg:items-center justify-between mb-4">
  <div class="flex flex-nowrap lg:flex-wrap overflow-x-auto no-scrollbar gap-1.5 text-xs pb-0.5 [&>a]:shrink-0 [&>a]:whitespace-nowrap">
    @foreach([
      '' => 'Semua Kategori',
      'bbm_genset' => 'BBM Genset',
      'logistik_sar' => 'Logistik SAR / P3K',
      'operasional_pos' => 'Operasional Pos',
      'kebersihan_sampah' => 'Sampah & Karung',
    ] as $catKey => $catLabel)
      @php $isCat = ($category ?? '') === $catKey; @endphp
      <a href="{{ route('management.pos.expenses.index', $catKey ? ['category' => $catKey] : []) }}" class="h-9 px-3 rounded-xl font-semibold flex items-center ring-1 transition {{ $isCat ? 'bg-brand text-white ring-brand' : 'bg-raised text-muted ring-line hover:text-ink' }}">
        {{ $catLabel }}
      </a>
    @endforeach
  </div>

  <form method="GET" action="{{ route('management.pos.expenses.index') }}" class="w-full lg:w-72 relative">
    @if($category)<input type="hidden" name="category" value="{{ $category }}">@endif
    <x-ui.icon name="search" class="w-4 h-4 text-subtle absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" />
    <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari kode / toko / catatan..." class="field !pl-10">
  </form>
</div>

<x-ui.card :padding="false">
  {{-- Mobile: kartu --}}
  <div class="lg:hidden divide-y divide-line">
    @forelse($expenses as $exp)
      <div class="p-3.5 space-y-2 {{ $exp->status === 'voided' ? 'opacity-50 line-through' : '' }}">
        <div class="flex items-start justify-between gap-2">
          <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-1.5">
              <span class="font-code text-xs font-bold text-rose-400">{{ $exp->expense_code }}</span>
              <x-ui.badge size="xs">{{ $exp->category_label }}</x-ui.badge>
            </div>
            <p class="font-semibold text-sm text-ink mt-1">{{ $exp->recipient_name }}</p>
            <p class="text-xs text-muted line-clamp-2 mt-0.5">{{ $exp->notes }}</p>
            <p class="text-[11px] text-subtle mt-1">{{ $exp->created_at->format('d/m/Y H:i') }} · {{ $exp->cashier?->name ?? 'Kasir Posko' }}</p>
          </div>
          <div class="text-right shrink-0">
            <p class="font-display font-bold text-base text-rose-400 tabular-nums">− Rp {{ number_format($exp->amount, 0, ',', '.') }}</p>
            <div class="mt-1 flex items-center justify-end gap-1">
              <x-ui.badge :tone="$exp->payment_method === 'cash' ? 'warning' : 'info'" size="xs">{{ strtoupper($exp->payment_method) }}</x-ui.badge>
              @if($exp->status === 'active')
                <x-ui.badge tone="success" size="xs">SAH</x-ui.badge>
              @else
                <x-ui.badge tone="danger" size="xs">VOID</x-ui.badge>
              @endif
            </div>
          </div>
        </div>
        <div class="flex items-center gap-2 pt-1">
          <x-ui.button variant="secondary" size="sm" icon="printer" class="flex-1" :href="route('management.pos.expenses.print', $exp->id)" target="_blank">Cetak Bukti</x-ui.button>
          @if($exp->status === 'active')
            <x-ui.button variant="danger" size="sm" onclick="openVoidModal({{ $exp->id }}, '{{ $exp->expense_code }}', {{ $exp->amount }})">Void</x-ui.button>
          @endif
        </div>
      </div>
    @empty
      <x-ui.empty icon="wallet" title="Belum ada pengeluaran kas" text="Catat pengeluaran kas mendadak dari tombol di atas." />
    @endforelse
  </div>

  {{-- Desktop: tabel --}}
  <div class="hidden lg:block overflow-x-auto">
    <table class="w-full text-left text-sm">
      <thead class="bg-raised/40 border-b border-line text-[11px] text-subtle uppercase">
        <tr>
          <th class="py-3 px-4 font-semibold">Kode</th>
          <th class="py-3 px-3 font-semibold">Waktu</th>
          <th class="py-3 px-3 font-semibold">Kategori &amp; Keperluan</th>
          <th class="py-3 px-3 font-semibold">Penerima</th>
          <th class="py-3 px-3 font-semibold text-right">Nominal</th>
          <th class="py-3 px-3 font-semibold text-center">Metode</th>
          <th class="py-3 px-3 font-semibold">Status</th>
          <th class="py-3 px-4 font-semibold text-right">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-line">
        @forelse($expenses as $exp)
          <tr class="hover:bg-hover/40 transition {{ $exp->status === 'voided' ? 'opacity-45 line-through' : '' }}">
            <td class="py-3 px-4 font-code font-bold text-rose-400 whitespace-nowrap">{{ $exp->expense_code }}</td>
            <td class="py-3 px-3 text-muted text-xs whitespace-nowrap tabular-nums">{{ $exp->created_at->format('d/m/Y H:i') }}</td>
            <td class="py-3 px-3">
              <x-ui.badge size="xs">{{ $exp->category_label }}</x-ui.badge>
              <div class="text-xs text-muted mt-1 max-w-xs truncate" title="{{ $exp->notes }}">{{ $exp->notes }}</div>
            </td>
            <td class="py-3 px-3 text-ink font-medium">
              <div>{{ $exp->recipient_name }}</div>
              <div class="text-[11px] text-subtle">{{ $exp->cashier?->name ?? 'Kasir Posko' }}</div>
            </td>
            <td class="py-3 px-3 text-right font-bold text-rose-400 tabular-nums whitespace-nowrap">− Rp {{ number_format($exp->amount, 0, ',', '.') }}</td>
            <td class="py-3 px-3 text-center">
              <x-ui.badge :tone="$exp->payment_method === 'cash' ? 'warning' : 'info'" size="xs">{{ strtoupper($exp->payment_method) }}</x-ui.badge>
            </td>
            <td class="py-3 px-3">
              @if($exp->status === 'active')
                <x-ui.badge tone="success" size="xs" dot>SAH</x-ui.badge>
              @else
                <x-ui.badge tone="danger" size="xs" title="{{ $exp->void_reason }}">VOID</x-ui.badge>
              @endif
            </td>
            <td class="py-3 px-4 text-right whitespace-nowrap">
              <div class="flex items-center justify-end gap-1.5">
                <x-ui.button variant="secondary" size="sm" icon="printer" :href="route('management.pos.expenses.print', $exp->id)" target="_blank">Cetak</x-ui.button>
                @if($exp->status === 'active')
                  <x-ui.button variant="danger" size="sm" onclick="openVoidModal({{ $exp->id }}, '{{ $exp->expense_code }}', {{ $exp->amount }})">Void</x-ui.button>
                @endif
              </div>
            </td>
          </tr>
        @empty
          <tr><td colspan="8"><x-ui.empty icon="wallet" title="Belum ada pengeluaran kas" /></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  @if($expenses->hasPages())
    <div class="px-4 py-3 border-t border-line">{{ $expenses->links() }}</div>
  @endif
</x-ui.card>

<x-ui.modal id="expenseModal" title="Form Kas Keluar Operasional" eyebrow="Petty Cash" maxWidth="sm:max-w-lg" onclose="closeExpenseModal()">
  <form method="POST" action="{{ route('management.pos.expenses.store') }}" enctype="multipart/form-data" class="space-y-4">
    @csrf
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
      <div>
        <label class="label">Kategori Pengeluaran *</label>
        <select name="category" required class="field">
          <option value="bbm_genset">BBM Genset &amp; Listrik</option>
          <option value="operasional_pos">Operasional Pos &amp; ATK</option>
          <option value="logistik_sar">P3K &amp; Logistik Darurat SAR</option>
          <option value="konsumsi_petugas">Konsumsi Ranger &amp; Posko</option>
          <option value="kebersihan_sampah">Kantong Sampah &amp; Kebersihan</option>
          <option value="perbaikan_alat">Pemeliharaan Jalur &amp; Alat</option>
          <option value="lainnya">Kebutuhan Mendadak Lainnya</option>
        </select>
      </div>

      <div>
        <label class="label">Metode Pengeluaran *</label>
        <select name="payment_method" required class="field">
          <option value="cash">Tunai (Kurangi Laci Kasir)</option>
          <option value="transfer">Transfer Bank</option>
        </select>
      </div>
    </div>

    <div>
      <label class="label">Nominal Pengeluaran (Rp) *</label>
      <div class="relative">
        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-muted font-semibold text-sm">Rp</span>
        <input type="text" inputmode="numeric" data-currency-input name="amount" required placeholder="Contoh: 50.000" class="field !pl-10 font-bold">
      </div>
    </div>

    <div>
      <label class="label">Nama Toko / Penerima Dana *</label>
      <input type="text" name="recipient_name" required placeholder="Contoh: SPBU Simpang / Toko Barokah / Tim SAR" class="field">
    </div>

    <div>
      <label class="label">Keperluan / Keterangan Mendadak *</label>
      <textarea name="notes" rows="3" required placeholder="Tulis rincian barang yang dibeli atau alasan pengeluaran mendadak..." class="field"></textarea>
    </div>

    <div>
      <label class="label">Foto Nota / Bukti Kasbon (Opsional)</label>
      <input type="file" name="receipt_photo" accept="image/*,application/pdf" class="w-full bg-raised border border-line-strong rounded-xl px-3 py-2 text-muted text-xs file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-hover file:text-ink file:font-semibold">
    </div>

    <div class="pt-2 border-t border-line flex items-center justify-end gap-2">
      <x-ui.button variant="secondary" onclick="closeExpenseModal()">Batal</x-ui.button>
      <x-ui.button type="submit" variant="danger" icon="check">Simpan Kas Keluar</x-ui.button>
    </div>
  </form>
</x-ui.modal>

<x-ui.modal id="voidModal" title="Batalkan (VOID) Kas Keluar" eyebrow="Audit Kas" maxWidth="sm:max-w-md" onclose="closeVoidModal()">
  <form id="voidForm" method="POST" action="" class="space-y-4">
    @csrf
    <p class="text-sm text-muted leading-relaxed">
      Apakah Anda yakin ingin membatalkan pengeluaran <span id="voidCodeText" class="font-bold text-rose-400"></span> sebesar <span id="voidAmountText" class="font-bold text-ink"></span>? Dana kas keluar ini akan dinetralkan kembali pada buku kas.
    </p>

    <div>
      <label class="label">Alasan Pembatalan (VOID) *</label>
      <input type="text" name="void_reason" required minlength="5" placeholder="Contoh: Salah input nominal / Nota dibatalkan supplier" class="field">
    </div>

    <div class="pt-2 border-t border-line flex items-center justify-end gap-2">
      <x-ui.button variant="secondary" onclick="closeVoidModal()">Tutup</x-ui.button>
      <x-ui.button type="submit" variant="danger" icon="check">Konfirmasi VOID</x-ui.button>
    </div>
  </form>
</x-ui.modal>

@push('scripts')
<script>
  function openExpenseModal() {
    document.getElementById('expenseModal')?.classList.remove('hidden');
  }
  function closeExpenseModal() {
    document.getElementById('expenseModal')?.classList.add('hidden');
  }
  function openVoidModal(id, code, amount) {
    document.getElementById('voidForm').action = `/management/pos/expenses/${id}/void`;
    document.getElementById('voidCodeText').textContent = code;
    document.getElementById('voidAmountText').textContent = 'Rp ' + Number(amount).toLocaleString('id-ID');
    document.getElementById('voidModal')?.classList.remove('hidden');
  }
  function closeVoidModal() {
    document.getElementById('voidModal')?.classList.add('hidden');
  }
</script>
@endpush
@endsection
