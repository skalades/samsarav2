@extends('management.layout')

@section('title', 'Kasir POS Terpadu')
@section('page_title', 'Kasir Point of Sale (POS)')
@section('page_subtitle', 'Transaksi Tiket SIMAKSI, Shuttle Ojek, Sewa Tenda, & Logistik Pendakian')

@section('content')
  <!-- ======================================================== -->
  <!-- TOP BAR: CASH DRAWER STATUS & QUICK ACTIONS              -->
  <!-- ======================================================== -->
  <div class="mb-4 bg-surface ring-1 ring-line rounded-2xl p-3.5 sm:p-4 flex flex-col lg:flex-row lg:items-center justify-between gap-3 text-xs">
    <div class="flex flex-wrap items-center gap-x-4 gap-y-1.5">
      <div class="flex items-center gap-2">
        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse shrink-0"></span>
        <span class="text-muted uppercase text-[11px] font-semibold">Kas Laci:</span>
        <span class="font-display font-bold text-base text-emerald-400 tabular-nums">Rp {{ number_format($cashDrawer['net_cash_in_drawer'] ?? 0, 0, ',', '.') }}</span>
      </div>
      <div class="hidden sm:flex flex-wrap items-center gap-3 text-xs text-muted border-l border-line-strong pl-3 tabular-nums">
        <span>Masuk: <strong class="text-ink">Rp {{ number_format($cashDrawer['total_cash_in'] ?? 0, 0, ',', '.') }}</strong></span>
        <span>Ojek: <strong class="text-amber-400">−Rp {{ number_format($cashDrawer['ojek_payout_cash'] ?? 0, 0, ',', '.') }}</strong></span>
        <span>Keluar: <strong class="text-rose-400">−Rp {{ number_format($cashDrawer['operational_expenses_cash'] ?? 0, 0, ',', '.') }}</strong></span>
      </div>
    </div>

    <div class="flex flex-wrap items-center gap-2">
      <x-ui.button variant="secondary" size="sm" icon="wallet" :href="route('management.pos.expenses.index')">Buku Kas</x-ui.button>
      <x-ui.button variant="danger" size="sm" icon="plus" onclick="openQuickExpenseModal()">Kas Keluar</x-ui.button>
      <x-ui.button variant="secondary" size="sm" icon="clock" :href="route('management.pos.history')">Rekap Shift</x-ui.button>
    </div>
  </div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-5 lg:h-[calc(100dvh-11.5rem)] lg:min-h-[480px]">

  <!-- ======================================================== -->
  <!-- 1. LEFT 2 COLS: ITEM CATALOG GRID                        -->
  <!-- ======================================================== -->
  <div class="lg:col-span-2 max-h-[58dvh] lg:max-h-none lg:h-auto lg:min-h-0 flex flex-col bg-surface ring-1 ring-line rounded-2xl overflow-hidden">
    
    <!-- Category Filter Tabs -->
    <div class="p-3 border-b border-line flex items-center justify-between gap-3 bg-raised/40 shrink-0">
      <div class="flex flex-nowrap lg:flex-wrap overflow-x-auto no-scrollbar lg:overflow-visible gap-1.5 text-xs min-w-0 pb-0.5 [&>button]:shrink-0 [&>button]:whitespace-nowrap" id="catFilterContainer">
        <button type="button" onclick="filterCat('all', this)" class="cat-pill active h-9 px-3 rounded-xl bg-brand text-white font-semibold">
          Semua
        </button>
        <button type="button" onclick="filterCat('izin', this)" class="cat-pill h-9 px-3 rounded-xl bg-raised text-muted hover:text-ink font-medium">
          SIMAKSI
        </button>
        <button type="button" onclick="filterCat('shuttle_ojek', this)" class="cat-pill h-9 px-3 rounded-xl bg-raised text-muted hover:text-ink font-medium">
          Ojek Trail
        </button>
        <button type="button" onclick="filterCat('parkir', this)" class="cat-pill h-9 px-3 rounded-xl bg-raised text-muted hover:text-ink font-medium">
          Titip Parkir
        </button>
        <button type="button" onclick="filterCat('alat_gunung', this)" class="cat-pill h-9 px-3 rounded-xl bg-raised text-muted hover:text-ink font-medium">
          Alat Gunung
        </button>
        <button type="button" onclick="filterCat('konsumsi', this)" class="cat-pill h-9 px-3 rounded-xl bg-raised text-muted hover:text-ink font-medium">
          Gas &amp; Logistik
        </button>
        <button type="button" onclick="filterCat('porter', this)" class="cat-pill h-9 px-3 rounded-xl bg-raised text-muted hover:text-ink font-medium">
          Porter
        </button>
      </div>

      <div class="hidden xl:block shrink-0 text-[11px] text-subtle">
        Ketuk item untuk tambah
      </div>
    </div>

    <!-- Scrollable Items Grid -->
    <div class="flex-1 overflow-y-auto overscroll-y-contain p-3 lg:p-4 grid grid-cols-2 sm:grid-cols-3 gap-2.5 lg:gap-3 content-start">
      @foreach($items as $item)
        <button type="button" data-cat="{{ $item->category }}" onclick="addToCart({{ $item->id }}, '{{ addslashes($item->name) }}', {{ $item->price }}, '{{ $item->type }}', {{ $item->stock_available }})" class="item-card text-left cursor-pointer p-3 rounded-xl bg-raised/70 ring-1 ring-line hover:ring-brand active:scale-[.98] transition flex flex-col justify-between select-none">
          <div class="w-full">
            <div class="flex items-center justify-between gap-1 text-[10px] mb-1">
              <span class="font-code text-brand font-bold truncate">{{ $item->sku }}</span>
              <span class="text-subtle uppercase shrink-0">
                {{ $item->type === 'service' ? 'Layanan' : ($item->type === 'rental' ? 'Sewa' : 'Barang') }}
              </span>
            </div>
            <h4 class="font-display font-semibold text-xs sm:text-sm text-ink line-clamp-2 leading-snug">{{ $item->name }}</h4>
          </div>

          <div class="w-full pt-2 mt-2 border-t border-line flex items-center justify-between gap-1 tabular-nums">
            <span class="font-bold text-ink text-xs sm:text-sm">Rp {{ number_format($item->price, 0, ',', '.') }}</span>
            <span class="text-[10px] text-muted shrink-0">
              @if($item->type !== 'service')
                Stok {{ $item->stock_available }}
              @else
                Siap
              @endif
            </span>
          </div>
        </button>
      @endforeach
    </div>

  </div>

  <!-- Ringkasan keranjang melayang (mobile): lompat ke struk transaksi -->
  <button type="button" id="mobileCartBar" onclick="document.getElementById('posCart').scrollIntoView({ behavior: 'smooth', block: 'start' })" class="lg:hidden hidden sticky bottom-2 z-20 w-full items-center justify-between gap-2 px-4 h-12 rounded-2xl bg-brand text-white text-sm font-bold shadow-xl shadow-black/50">
    <span class="flex items-center gap-2">
      <x-ui.icon name="cash" class="w-4 h-4" />
      <span id="mobileCartCount">0 item</span>
    </span>
    <span class="flex items-center gap-1.5 tabular-nums">
      <span id="mobileCartTotal">Rp 0</span>
      <span>→ Bayar</span>
    </span>
  </button>

  <!-- ======================================================== -->
  <!-- 2. RIGHT COL: INTERACTIVE CART & CHECKOUT                -->
  <!-- ======================================================== -->
  <div id="posCart" class="scroll-mt-3 lg:min-h-0 flex flex-col bg-surface ring-1 ring-line rounded-2xl overflow-hidden">
    
    <!-- Cart Header -->
    <div class="px-4 py-3 border-b border-line flex items-center justify-between bg-raised/40 shrink-0">
      <div class="flex items-center gap-2">
        <x-ui.icon name="receipt" class="w-4 h-4 text-brand" />
        <span class="font-display font-semibold text-sm text-ink">Struk Transaksi</span>
      </div>
      <button type="button" onclick="clearCart()" class="text-xs font-medium text-muted hover:text-rose-400">
        Kosongkan
      </button>
    </div>

    <!-- Customer & Link SIMAKSI inputs -->
    <div class="p-3 border-b border-line bg-raised/30 space-y-2.5 text-xs shrink-0">
      <div>
        <label for="cartSimaksiId" class="label !text-[11px] !mb-1">Hubungkan SIMAKSI (Opsional)</label>
        <select id="cartSimaksiId" onchange="autoFillCustomerFromSimaksi(this)" class="field !h-10 !text-xs">
          <option value="">-- Bukan Transaksi SIMAKSI Langsung --</option>
          @foreach($activeRegistrations as $reg)
            <option value="{{ $reg->id }}" data-name="{{ $reg->leader_name }}" data-phone="{{ $reg->leader_phone }}">
              {{ $reg->registration_code }} — {{ $reg->leader_name }} ({{ $reg->total_climbers }} Org)
            </option>
          @endforeach
        </select>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
        <div>
          <label for="cartCustomerName" class="label !text-[11px] !mb-1">Nama Pelanggan / Ketua</label>
          <input type="text" id="cartCustomerName" placeholder="Contoh: Arif" required class="field !h-10 !text-xs">
        </div>
        <div>
          <label for="cartCustomerPhone" class="label !text-[11px] !mb-1">No. WhatsApp</label>
          <input type="tel" id="cartCustomerPhone" placeholder="0812..." class="field !h-10 !text-xs">
        </div>
      </div>
    </div>

    <!-- Cart Item List -->
    <div class="flex-1 min-h-[120px] overflow-y-auto overscroll-y-contain p-3 space-y-2 text-xs" id="cartItemsList">
      <div class="text-center py-8 text-muted" id="emptyCartNotice">
        Keranjang transaksi kosong.<br>
        <span class="text-[11px] text-subtle">Pilih barang dari katalog.</span>
      </div>
    </div>

    <!-- Cart Totals & Checkout -->
    <div class="p-3.5 border-t border-line bg-raised/30 space-y-2.5 text-xs shrink-0">
      
      <div class="flex justify-between items-center text-sm font-bold">
        <span class="text-muted">Total Bayar</span>
        <span class="font-display text-xl text-brand tabular-nums" id="cartTotalDisplay">Rp 0</span>
      </div>

      <!-- Payment Method Toggle -->
      <div class="grid grid-cols-3 gap-1.5">
        <button type="button" onclick="setPaymentMethod('cash', this)" class="pay-method-btn active h-9 text-center rounded-xl border border-brand bg-brand text-white font-semibold text-xs">
          Tunai
        </button>
        <button type="button" onclick="setPaymentMethod('qris', this)" class="pay-method-btn h-9 text-center rounded-xl border border-line-strong bg-raised text-muted hover:text-ink font-semibold text-xs">
          QRIS
        </button>
        <button type="button" onclick="setPaymentMethod('transfer', this)" class="pay-method-btn h-9 text-center rounded-xl border border-line-strong bg-raised text-muted hover:text-ink font-semibold text-xs">
          Transfer
        </button>
      </div>

      <!-- Cash calculation -->
      <div id="cashCalcBox" class="p-2.5 bg-canvas rounded-xl border border-line space-y-1.5">
        <div class="flex justify-between items-center gap-2">
          <label for="cashTendered" class="text-[11px] text-muted font-medium">Uang Diterima</label>
          <input type="text" inputmode="numeric" data-currency-input id="cashTendered" oninput="calcChange()" placeholder="0" class="w-36 bg-surface border border-line-strong rounded-lg px-2.5 py-1.5 text-right text-ink font-bold text-sm tabular-nums focus:outline-none focus:border-brand">
        </div>
        <div class="flex justify-between items-center text-xs tabular-nums">
          <span class="text-muted">Kembalian</span>
          <span class="text-emerald-400 font-bold" id="cashChangeDisplay">Rp 0</span>
        </div>
      </div>

      <!-- QRIS Display Box (When QRIS is selected) -->
      <div id="qrisDisplayBox" class="hidden p-2.5 bg-canvas rounded-xl border border-sky-500/40 space-y-1.5 text-center">
        <div class="flex items-center justify-between text-[11px]">
          <span class="text-sky-400 font-bold uppercase">QRIS {{ \App\Models\AppSetting::get('basecamp_name', 'BASECAMP SAMSARA') }}</span>
          <span class="text-[10px] text-muted font-code">NMID: {{ \App\Models\AppSetting::get('payment_qris_nmid', config('samsara.payment.qris_nmid')) }}</span>
        </div>
        <div class="w-28 h-28 mx-auto bg-white p-1.5 rounded-lg flex items-center justify-center shadow-md">
          @if(\App\Models\AppSetting::get('payment_qris_image'))
            <img src="{{ asset('storage/' . \App\Models\AppSetting::get('payment_qris_image')) }}" alt="QRIS Basecamp" class="w-full h-full object-contain">
          @else
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=130x130&data=00020101021226600016ID.SAMSARA.GARUT01189360099900000000015204581253033605802ID5918SAMSARA+CIKURAY6005GARUT6304A1B2" alt="QRIS Mockup" class="w-full h-full object-contain">
          @endif
        </div>
        <p class="text-[10px] text-muted">Tunjukkan QRIS kepada pendaki untuk scan langsung.</p>
      </div>

      <!-- Submit Checkout Button -->
      <button type="button" onclick="submitCheckout()" id="btnCheckout" class="tap-active w-full h-11 bg-brand hover:bg-brand-hover text-white font-semibold text-sm rounded-xl transition shadow-lg shadow-brand/20">
        Cetak Struk &amp; Selesaikan
      </button>

    </div>

  </div>

</div>

<!-- Interactive POS Script -->
<script>
  let cart = [];
  let paymentMethod = 'cash';

  function filterCat(cat, btn) {
    document.querySelectorAll('.cat-pill').forEach(b => {
      b.className = 'cat-pill h-9 px-3 rounded-xl bg-raised text-muted hover:text-ink font-medium';
    });
    btn.className = 'cat-pill active h-9 px-3 rounded-xl bg-brand text-white font-semibold';

    document.querySelectorAll('.item-card').forEach(card => {
      if (cat === 'all' || card.dataset.cat === cat) {
        card.style.display = 'flex';
      } else {
        card.style.display = 'none';
      }
    });
  }

  function addToCart(id, name, price, type, stock) {
    const existing = cart.find(i => i.id === id);
    if (existing) {
      if (type !== 'service' && existing.qty >= stock) {
        alert('Stok maksimal barang ini telah tercapai (' + stock + ').');
        return;
      }
      existing.qty++;
    } else {
      if (type !== 'service' && stock <= 0) {
        alert('Barang ini sedang habis stok.');
        return;
      }
      cart.push({ id, name, price, qty: 1, type, stock });
    }
    renderCart();
  }

  function adjustQty(id, delta) {
    const item = cart.find(i => i.id === id);
    if (!item) return;
    item.qty += delta;
    if (item.qty <= 0) {
      cart = cart.filter(i => i.id !== id);
    }
    renderCart();
  }

  function clearCart() {
    cart = [];
    renderCart();
  }

  function renderCart() {
    const container = document.getElementById('cartItemsList');
    if (cart.length === 0) {
      container.innerHTML = `
        <div class="text-center py-12 text-stone-400" id="emptyCartNotice">
          Keranjang transaksi kosong.<br>
          <span class="text-[10px]">Pilih barang dari katalog.</span>
        </div>
      `;
      document.getElementById('cartTotalDisplay').innerText = 'Rp 0';
      updateMobileCartBar(0, 0);
      calcChange();
      return;
    }

    let total = 0;
    container.innerHTML = cart.map(item => {
      const lineSub = item.price * item.qty;
      total += lineSub;
      return `
        <div class="p-2.5 bg-raised border border-line rounded-xl flex items-center justify-between gap-2">
          <div class="flex-1 min-w-0">
            <span class="font-semibold text-ink block text-xs truncate">${item.name}</span>
            <span class="text-[11px] text-muted tabular-nums">@ Rp ${item.price.toLocaleString('id-ID')}</span>
          </div>
          <div class="flex items-center gap-2 shrink-0">
            <div class="flex items-center bg-surface border border-line-strong rounded-lg overflow-hidden">
              <button type="button" onclick="adjustQty(${item.id}, -1)" class="w-8 h-8 text-muted hover:text-ink flex items-center justify-center font-bold">−</button>
              <span class="w-7 text-center font-bold text-ink text-xs tabular-nums">${item.qty}</span>
              <button type="button" onclick="adjustQty(${item.id}, 1)" class="w-8 h-8 text-muted hover:text-ink flex items-center justify-center font-bold">+</button>
            </div>
            <span class="w-20 text-right font-bold text-ink text-xs tabular-nums">Rp ${lineSub.toLocaleString('id-ID')}</span>
          </div>
        </div>
      `;
    }).join('');

    document.getElementById('cartTotalDisplay').innerText = 'Rp ' + total.toLocaleString('id-ID');
    updateMobileCartBar(cart.reduce((n, i) => n + i.qty, 0), total);
    calcChange();
  }

  function updateMobileCartBar(qty, total) {
    const bar = document.getElementById('mobileCartBar');
    if (!bar) return;
    document.getElementById('mobileCartCount').innerText = qty + ' item';
    document.getElementById('mobileCartTotal').innerText = 'Rp ' + total.toLocaleString('id-ID');
    bar.classList.toggle('hidden', qty === 0);
    bar.classList.toggle('flex', qty > 0);
  }

  function setPaymentMethod(method, btn) {
    paymentMethod = method;
    document.querySelectorAll('.pay-method-btn').forEach(b => {
      b.className = 'pay-method-btn h-9 text-center rounded-xl border border-line-strong bg-raised text-muted hover:text-ink font-semibold text-xs';
    });
    btn.className = 'pay-method-btn active h-9 text-center rounded-xl border border-brand bg-brand text-white font-semibold text-xs';
    
    const cashBox = document.getElementById('cashCalcBox');
    const qrisBox = document.getElementById('qrisDisplayBox');
    if (method === 'cash') {
      if (cashBox) cashBox.classList.remove('hidden');
      if (qrisBox) qrisBox.classList.add('hidden');
    } else if (method === 'qris') {
      if (cashBox) cashBox.classList.add('hidden');
      if (qrisBox) qrisBox.classList.remove('hidden');
    } else {
      if (cashBox) cashBox.classList.add('hidden');
      if (qrisBox) qrisBox.classList.add('hidden');
    }
  }

  function calcChange() {
    const total = cart.reduce((acc, i) => acc + (i.price * i.qty), 0);
    const tendered = typeof parseRupiah === 'function' 
      ? parseRupiah(document.getElementById('cashTendered').value) 
      : (parseFloat(document.getElementById('cashTendered').value.replace(/\D/g, '')) || 0);
    const change = Math.max(0, tendered - total);
    document.getElementById('cashChangeDisplay').innerText = typeof formatRupiah === 'function'
      ? formatRupiah(change)
      : ('Rp ' + change.toLocaleString('id-ID'));
  }

  function autoFillCustomerFromSimaksi(selectEl) {
    const opt = selectEl.options[selectEl.selectedIndex];
    if (opt.value) {
      document.getElementById('cartCustomerName').value = opt.dataset.name || '';
      document.getElementById('cartCustomerPhone').value = opt.dataset.phone || '';
    }
  }

  function submitCheckout() {
    if (cart.length === 0) {
      alert('Pilih minimal 1 item untuk transaksi.');
      return;
    }

    const customerName = document.getElementById('cartCustomerName').value.trim();
    if (!customerName) {
      alert('Masukkan nama pelanggan / ketua tim.');
      document.getElementById('cartCustomerName').focus();
      return;
    }

    const customerPhone = document.getElementById('cartCustomerPhone').value.trim();
    const simaksiId = document.getElementById('cartSimaksiId').value || null;
    const cashTendered = typeof parseRupiah === 'function'
      ? parseRupiah(document.getElementById('cashTendered').value)
      : (parseFloat(document.getElementById('cashTendered').value.replace(/\D/g, '')) || 0);

    const payload = {
      customer_name: customerName,
      customer_phone: customerPhone,
      simaksi_registration_id: simaksiId,
      payment_method: paymentMethod,
      cash_tendered: cashTendered,
      items: cart.map(i => ({ inventory_item_id: i.id, quantity: i.qty })),
      _token: '{{ csrf_token() }}'
    };

    const btn = document.getElementById('btnCheckout');
    btn.innerText = 'MEMPROSES...';
    btn.disabled = true;

    fetch('{{ route('management.pos.store') }}', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: JSON.stringify(payload)
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        window.location.href = data.redirect_url;
      } else {
        alert('Gagal transaksi: ' + (data.message || 'Terjadi kesalahan sistem.'));
        btn.innerText = 'CETAK STRUK & SELESAIKAN TRANSAKSI';
        btn.disabled = false;
      }
    })
    .catch(err => {
      alert('Error koneksi jaringan.');
      btn.innerText = 'CETAK STRUK & SELESAIKAN TRANSAKSI';
      btn.disabled = false;
    });
  }

  function openQuickExpenseModal() {
    document.getElementById('quickExpenseModal')?.classList.remove('hidden');
  }

  function closeQuickExpenseModal() {
    document.getElementById('quickExpenseModal')?.classList.add('hidden');
  }
</script>

<!-- ======================================================== -->
<!-- MODAL CEPAT: CATAT KAS KELUAR MENDADAK DI POS            -->
<!-- ======================================================== -->
<x-ui.modal id="quickExpenseModal" title="Catat Kas Keluar (Mendadak)" eyebrow="Kas Operasional" maxWidth="sm:max-w-md" onclose="closeQuickExpenseModal()">
  <form id="quickExpenseForm" method="POST" action="{{ route('management.pos.expenses.store') }}" class="space-y-4">
    @csrf

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
      <div>
        <label class="label">Kategori *</label>
        <select name="category" required class="field">
          <option value="bbm_genset">BBM Genset</option>
          <option value="operasional_pos">Operasional Pos</option>
          <option value="logistik_sar">P3K / SAR</option>
          <option value="konsumsi_petugas">Konsumsi Petugas</option>
          <option value="kebersihan_sampah">Kantong Sampah</option>
          <option value="perbaikan_alat">Perbaikan Alat</option>
          <option value="lainnya">Lainnya</option>
        </select>
      </div>

      <div>
        <label class="label">Metode *</label>
        <select name="payment_method" required class="field">
          <option value="cash">Tunai (Laci Kasir)</option>
          <option value="transfer">Transfer</option>
        </select>
      </div>
    </div>

    <div>
      <label class="label">Nominal Kas Keluar (Rp) *</label>
      <div class="relative">
        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-muted font-semibold text-sm">Rp</span>
        <input type="text" inputmode="numeric" data-currency-input name="amount" required placeholder="50.000" class="field !pl-10 font-bold">
      </div>
    </div>

    <div>
      <label class="label">Penerima Dana / Toko *</label>
      <input type="text" name="recipient_name" required placeholder="SPBU / Warung / Toko Kelontong" class="field">
    </div>

    <div>
      <label class="label">Keperluan Pengeluaran *</label>
      <textarea name="notes" rows="2" required placeholder="Tuliskan keperluan pembelian mendadak..." class="field"></textarea>
    </div>

    <div class="pt-2 border-t border-line flex items-center justify-end gap-2">
      <x-ui.button variant="secondary" onclick="closeQuickExpenseModal()">Batal</x-ui.button>
      <x-ui.button type="submit" variant="danger" icon="check">Simpan Kas Keluar</x-ui.button>
    </div>
  </form>
</x-ui.modal>
@endsection
