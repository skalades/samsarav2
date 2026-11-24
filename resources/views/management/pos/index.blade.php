@extends('management.layout')

@section('title', 'Kasir POS Terpadu')
@section('page_title', 'Kasir Point of Sale (POS)')
@section('page_subtitle', 'Transaksi Tiket SIMAKSI, Shuttle Ojek, Sewa Tenda, & Logistik Pendakian')

@section('content')
  <!-- ======================================================== -->
  <!-- TOP BAR: CASH DRAWER STATUS & QUICK ACTIONS              -->
  <!-- ======================================================== -->
  <div class="mb-4 bg-surface border border-line rounded-xl p-3 flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3 font-mono-tech text-xs">
    <div class="flex flex-wrap items-center gap-4">
      <div class="flex items-center gap-2">
        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
        <span class="text-stone-400 uppercase text-[10px]">KAS FISIK LACI (DRAWER):</span>
        <span class="font-black text-sm text-emerald-400">Rp {{ number_format($cashDrawer['net_cash_in_drawer'] ?? 0, 0, ',', '.') }}</span>
      </div>
      <div class="hidden sm:flex items-center gap-3 text-[11px] text-stone-400 border-l border-line-strong pl-3">
        <span>Kas Masuk: <strong class="text-stone-200">Rp {{ number_format($cashDrawer['total_cash_in'] ?? 0, 0, ',', '.') }}</strong></span>
        <span>Payout Ojek: <strong class="text-amber-400">-Rp {{ number_format($cashDrawer['ojek_payout_cash'] ?? 0, 0, ',', '.') }}</strong></span>
        <span>Kas Keluar: <strong class="text-rose-400">-Rp {{ number_format($cashDrawer['operational_expenses_cash'] ?? 0, 0, ',', '.') }}</strong></span>
      </div>
    </div>

    <div class="flex flex-wrap items-center gap-2 w-full md:w-auto justify-start md:justify-end">
      <a href="{{ route('management.pos.expenses.index') }}" class="px-3 py-1.5 rounded-lg bg-raised border border-line-strong text-stone-300 hover:text-white transition text-xs flex items-center gap-1.5">
        <svg class="w-3.5 h-3.5 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        <span>Buku Kas Keluar</span>
      </a>

      <button type="button" onclick="openQuickExpenseModal()" class="px-3 py-1.5 rounded-lg bg-rose-600/90 hover:bg-rose-600 text-white font-bold transition text-xs flex items-center gap-1.5 shadow-md shadow-rose-600/10">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
        <span>- Catat Kas Keluar</span>
      </button>

      <a href="{{ route('management.pos.history') }}" class="px-3 py-1.5 rounded-lg bg-raised border border-line-strong text-stone-300 hover:text-white transition text-xs flex items-center gap-1.5">
        <svg class="w-3.5 h-3.5 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span>Rekap Shift</span>
      </a>
    </div>
  </div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6 lg:h-[calc(100vh-12rem)]">

  <!-- ======================================================== -->
  <!-- 1. LEFT 2 COLS: ITEM CATALOG GRID                        -->
  <!-- ======================================================== -->
  <div class="lg:col-span-2 h-[62vh] lg:h-auto flex flex-col bg-surface border border-line rounded-xl overflow-hidden">
    
    <!-- Category Filter Tabs -->
    <div class="p-3 border-b border-line flex items-center justify-between gap-3 bg-canvas">
      <div class="flex flex-nowrap lg:flex-wrap overflow-x-auto lg:overflow-visible gap-1.5 font-mono-tech text-xs min-w-0 -mx-1 px-1 pb-0.5 [&>button]:shrink-0 [&>button]:whitespace-nowrap" id="catFilterContainer">
        <button onclick="filterCat('all', this)" class="cat-pill active px-3 py-1.5 rounded-lg bg-brand text-black font-bold">
          SEMUA
        </button>
        <button onclick="filterCat('izin', this)" class="cat-pill px-3 py-1.5 rounded-lg bg-raised text-stone-300 hover:text-white">
          SIMAKSI
        </button>
        <button onclick="filterCat('shuttle_ojek', this)" class="cat-pill px-3 py-1.5 rounded-lg bg-raised text-stone-300 hover:text-white">
          OJEK TRAIL
        </button>
        <button onclick="filterCat('parkir', this)" class="cat-pill px-3 py-1.5 rounded-lg bg-raised text-stone-300 hover:text-white">
          TITIP PARKIR
        </button>
        <button onclick="filterCat('alat_gunung', this)" class="cat-pill px-3 py-1.5 rounded-lg bg-raised text-stone-300 hover:text-white">
          ALAT GUNUNG
        </button>
        <button onclick="filterCat('konsumsi', this)" class="cat-pill px-3 py-1.5 rounded-lg bg-raised text-stone-300 hover:text-white">
          GAS & LOGISTIK
        </button>
        <button onclick="filterCat('porter', this)" class="cat-pill px-3 py-1.5 rounded-lg bg-raised text-stone-300 hover:text-white">
          PORTER
        </button>
      </div>

      <div class="hidden xl:block shrink-0 font-mono-tech text-[10px] text-stone-400">
        KLIK ITEM UNTUK MENAMBAH KE KERANJANG
      </div>
    </div>

    <!-- Scrollable Items Grid -->
    <div class="flex-1 overflow-y-auto p-3 lg:p-4 grid grid-cols-2 sm:grid-cols-3 gap-2 lg:gap-3 content-start font-mono-tech">
      @foreach($items as $item)
        <div data-cat="{{ $item->category }}" onclick="addToCart({{ $item->id }}, '{{ addslashes($item->name) }}', {{ $item->price }}, '{{ $item->type }}', {{ $item->stock_available }})" class="item-card cursor-pointer p-3 rounded-xl bg-raised border border-line-strong hover:border-brand transition flex flex-col justify-between select-none">
          <div>
            <div class="flex items-center justify-between text-[9px] mb-1">
              <span class="text-brand font-bold">{{ $item->sku }}</span>
              <span class="text-stone-400 uppercase">
                {{ $item->type === 'service' ? 'LAYANAN' : ($item->type === 'rental' ? 'SEWA' : 'BARANG') }}
              </span>
            </div>
            <h4 class="font-display font-bold text-xs text-white line-clamp-2 leading-snug">{{ $item->name }}</h4>
          </div>

          <div class="pt-2 mt-2 border-t border-line flex items-center justify-between">
            <span class="font-bold text-white text-xs">Rp {{ number_format($item->price, 0, ',', '.') }}</span>
            <span class="text-[9px] text-stone-400">
              @if($item->type !== 'service')
                Stok: {{ $item->stock_available }}
              @else
                Tersedia
              @endif
            </span>
          </div>
        </div>
      @endforeach
    </div>

  </div>

  <!-- Ringkasan keranjang melayang (mobile): lompat ke struk transaksi -->
  <button type="button" id="mobileCartBar" onclick="document.getElementById('posCart').scrollIntoView({ behavior: 'smooth', block: 'start' })" class="lg:hidden hidden sticky bottom-2 z-20 w-full items-center justify-between gap-2 px-4 py-3 rounded-xl bg-brand text-black font-mono-tech text-xs font-bold shadow-xl shadow-black/50">
    <span class="flex items-center gap-2">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
      <span id="mobileCartCount">0 item</span>
    </span>
    <span class="flex items-center gap-1.5">
      <span id="mobileCartTotal">Rp 0</span>
      <span>→ BAYAR</span>
    </span>
  </button>

  <!-- ======================================================== -->
  <!-- 2. RIGHT COL: INTERACTIVE CART & CHECKOUT                -->
  <!-- ======================================================== -->
  <div id="posCart" class="scroll-mt-3 lg:min-h-0 flex flex-col bg-surface border border-line rounded-xl overflow-hidden font-mono-tech">
    
    <!-- Cart Header -->
    <div class="p-3 border-b border-line flex items-center justify-between bg-canvas">
      <div class="flex items-center gap-2">
        <svg class="w-4 h-4 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
        <span class="font-bold text-xs text-white uppercase tracking-wider">STRUK TRANSAKSI</span>
      </div>
      <button onclick="clearCart()" class="text-[10px] text-stone-400 hover:text-rose-400">
        KOSONGKAN
      </button>
    </div>

    <!-- Customer & Link SIMAKSI inputs -->
    <div class="p-3 border-b border-line bg-raised space-y-2 text-xs">
      <div>
        <label class="block text-[9px] text-stone-400 uppercase mb-0.5">HUBUNGKAN DENGAN KODE SIMAKSI (OPSIONAL):</label>
        <select id="cartSimaksiId" onchange="autoFillCustomerFromSimaksi(this)" class="w-full bg-surface border border-line-strong rounded p-1.5 text-xs text-white focus:outline-none focus:border-brand">
          <option value="">-- Bukan Transaksi SIMAKSI Langsung --</option>
          @foreach($activeRegistrations as $reg)
            <option value="{{ $reg->id }}" data-name="{{ $reg->leader_name }}" data-phone="{{ $reg->leader_phone }}">
              {{ $reg->registration_code }} — {{ $reg->leader_name }} ({{ $reg->total_climbers }} Org)
            </option>
          @endforeach
        </select>
      </div>

      <div class="grid grid-cols-2 gap-2">
        <div>
          <label class="block text-[9px] text-stone-400 uppercase mb-0.5">NAMA PELANGGAN / KETUA</label>
          <input type="text" id="cartCustomerName" placeholder="Contoh: Arif" required class="w-full bg-surface border border-line-strong rounded p-1.5 text-xs text-white focus:outline-none focus:border-brand">
        </div>
        <div>
          <label class="block text-[9px] text-stone-400 uppercase mb-0.5">NO. WHATSAPP</label>
          <input type="text" id="cartCustomerPhone" placeholder="0812..." class="w-full bg-surface border border-line-strong rounded p-1.5 text-xs text-white focus:outline-none focus:border-brand">
        </div>
      </div>
    </div>

    <!-- Cart Item List -->
    <div class="flex-1 overflow-y-auto p-3 space-y-2 text-xs" id="cartItemsList">
      <!-- Items dynamically populated via JS -->
      <div class="text-center py-12 text-stone-400" id="emptyCartNotice">
        Keranjang transaksi kosong.<br>
        <span class="text-[10px]">Pilih barang dari katalog.</span>
      </div>
    </div>

    <!-- Cart Totals & Checkout -->
    <div class="p-3 border-t border-line bg-canvas space-y-2 text-xs">
      
      <div class="flex justify-between items-center text-sm font-bold pt-1">
        <span class="text-stone-300">TOTAL BAYAR:</span>
        <span class="text-xl text-brand" id="cartTotalDisplay">Rp 0</span>
      </div>

      <!-- Payment Method Toggle -->
      <div class="grid grid-cols-3 gap-1 pt-1">
        <button type="button" onclick="setPaymentMethod('cash', this)" class="pay-method-btn active py-1.5 text-center rounded border border-brand bg-brand text-black font-bold text-[10px]">
          TUNAI / CASH
        </button>
        <button type="button" onclick="setPaymentMethod('qris', this)" class="pay-method-btn py-1.5 text-center rounded border border-line-strong bg-raised text-stone-300 hover:text-white font-bold text-[10px]">
          QRIS RESMI
        </button>
        <button type="button" onclick="setPaymentMethod('transfer', this)" class="pay-method-btn py-1.5 text-center rounded border border-line-strong bg-raised text-stone-300 hover:text-white font-bold text-[10px]">
          TRANSFER
        </button>
      </div>

      <!-- Cash calculation -->
      <div id="cashCalcBox" class="p-2 bg-raised rounded border border-line space-y-1">
        <div class="flex justify-between items-center">
          <label class="text-[9px] text-stone-400 uppercase">Uang Diterima:</label>
          <input type="text" inputmode="numeric" data-currency-input id="cashTendered" oninput="calcChange()" placeholder="0" class="w-32 bg-surface border border-line-strong rounded px-2 py-1 text-right text-white font-bold text-xs">
        </div>
        <div class="flex justify-between items-center text-[10px]">
          <span class="text-stone-400">Kembalian:</span>
          <span class="text-emerald-400 font-bold" id="cashChangeDisplay">Rp 0</span>
        </div>
      </div>

      <!-- QRIS Display Box (When QRIS is selected) -->
      <div id="qrisDisplayBox" class="hidden p-2.5 bg-raised rounded border border-blue-500/40 space-y-1.5 text-center">
        <div class="flex items-center justify-between text-[10px]">
          <span class="text-blue-400 font-bold uppercase">QRIS {{ \App\Models\AppSetting::get('basecamp_name', 'BASECAMP SAMSARA') }}</span>
          <span class="text-[9px] text-stone-400 font-mono-tech">NMID: {{ \App\Models\AppSetting::get('payment_qris_nmid', config('samsara.payment.qris_nmid')) }}</span>
        </div>
        <div class="w-32 h-32 mx-auto bg-white p-1.5 rounded-lg flex items-center justify-center shadow-md">
          @if(\App\Models\AppSetting::get('payment_qris_image'))
            <img src="{{ asset('storage/' . \App\Models\AppSetting::get('payment_qris_image')) }}" alt="QRIS Basecamp" class="w-full h-full object-contain">
          @else
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=130x130&data=00020101021226600016ID.SAMSARA.GARUT01189360099900000000015204581253033605802ID5918SAMSARA+CIKURAY6005GARUT6304A1B2" alt="QRIS Mockup" class="w-full h-full object-contain">
          @endif
        </div>
        <p class="text-[9px] text-stone-400">Tunjukkan barcode ini kepada pendaki untuk scan langsung.</p>
      </div>

      <!-- Submit Checkout Button -->
      <button type="button" onclick="submitCheckout()" id="btnCheckout" class="w-full py-3 bg-brand hover:bg-brand-hover text-black font-mono-tech font-bold text-xs uppercase tracking-wider rounded-lg transition shadow-lg shadow-[#FF5722]/20">
        CETAK STRUK & SELESAIKAN TRANSAKSI
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
      b.className = 'cat-pill px-3 py-1.5 rounded-lg bg-raised text-stone-300 hover:text-white';
    });
    btn.className = 'cat-pill active px-3 py-1.5 rounded-lg bg-brand text-black font-bold';

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
        <div class="p-2 bg-raised border border-line rounded flex items-center justify-between">
          <div class="flex-1 pr-2">
            <span class="font-bold text-white block text-xs truncate">${item.name}</span>
            <span class="text-[10px] text-stone-400">@ Rp ${item.price.toLocaleString('id-ID')}</span>
          </div>
          <div class="flex items-center gap-1.5">
            <div class="flex items-center bg-surface border border-line-strong rounded">
              <button onclick="adjustQty(${item.id}, -1)" class="px-2 py-0.5 text-stone-300 hover:text-white">-</button>
              <span class="px-2 font-bold text-white text-xs">${item.qty}</span>
              <button onclick="adjustQty(${item.id}, 1)" class="px-2 py-0.5 text-stone-300 hover:text-white">+</button>
            </div>
            <span class="w-16 text-right font-bold text-white text-xs">Rp ${lineSub.toLocaleString('id-ID')}</span>
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
      b.className = 'pay-method-btn py-1.5 text-center rounded border border-line-strong bg-raised text-stone-300 hover:text-white font-bold text-[10px]';
    });
    btn.className = 'pay-method-btn active py-1.5 text-center rounded border border-brand bg-brand text-black font-bold text-[10px]';
    
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
    const modal = document.getElementById('quickExpenseModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
  }

  function closeQuickExpenseModal() {
    const modal = document.getElementById('quickExpenseModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
  }
</script>

<!-- ======================================================== -->
<!-- MODAL CEPAT: CATAT KAS KELUAR MENDADAK DI POS            -->
<!-- ======================================================== -->
<div id="quickExpenseModal" class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm hidden items-center justify-center p-4">
  <div class="bg-surface border border-line-strong rounded-2xl w-full max-w-md overflow-hidden shadow-2xl space-y-4">
    
    <div class="p-4 border-b border-line flex items-center justify-between bg-canvas">
      <div class="flex items-center gap-2">
        <div class="w-3 h-3 rounded-full bg-rose-500"></div>
        <h3 class="font-display font-black text-sm uppercase text-white tracking-wider">CATAT KAS KELUAR (MENDADAK)</h3>
      </div>
      <button type="button" onclick="closeQuickExpenseModal()" class="text-stone-400 hover:text-white text-lg font-bold">✕</button>
    </div>

    <form method="POST" action="{{ route('management.pos.expenses.store') }}" class="p-5 space-y-4 font-mono-tech text-xs">
      @csrf

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block text-[10px] text-stone-400 uppercase font-bold mb-1">Kategori *</label>
          <select name="category" required class="w-full bg-raised border border-line-strong rounded-lg px-2.5 py-2 text-white text-xs focus:outline-none focus:border-rose-500">
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
          <label class="block text-[10px] text-stone-400 uppercase font-bold mb-1">Metode *</label>
          <select name="payment_method" required class="w-full bg-raised border border-line-strong rounded-lg px-2.5 py-2 text-white text-xs focus:outline-none focus:border-rose-500">
            <option value="cash">Tunai (Laci Kasir)</option>
            <option value="transfer">Transfer</option>
          </select>
        </div>
      </div>

      <div>
        <label class="block text-[10px] text-stone-400 uppercase font-bold mb-1">Nominal Kas Keluar (Rp) *</label>
        <div class="relative">
          <span class="absolute left-3 top-2 text-stone-500 font-bold">Rp</span>
          <input type="text" inputmode="numeric" data-currency-input name="amount" required placeholder="50.000" class="w-full bg-raised border border-line-strong rounded-lg pl-10 pr-3 py-2 text-white font-bold text-sm focus:outline-none focus:border-rose-500">
        </div>
      </div>

      <div>
        <label class="block text-[10px] text-stone-400 uppercase font-bold mb-1">Penerima Dana / Toko *</label>
        <input type="text" name="recipient_name" required placeholder="SPBU / Warung / Toko Kelontong" class="w-full bg-raised border border-line-strong rounded-lg px-3 py-2 text-white text-xs focus:outline-none focus:border-rose-500">
      </div>

      <div>
        <label class="block text-[10px] text-stone-400 uppercase font-bold mb-1">Keperluan Pengeluaran *</label>
        <textarea name="notes" rows="2" required placeholder="Tuliskan keperluan pembelian mendadak..." class="w-full bg-raised border border-line-strong rounded-lg px-3 py-2 text-white text-xs focus:outline-none focus:border-rose-500"></textarea>
      </div>

      <div class="pt-2 border-t border-line flex items-center justify-end gap-3">
        <button type="button" onclick="closeQuickExpenseModal()" class="px-3.5 py-2 rounded-lg bg-raised border border-line-strong text-stone-300 hover:text-white font-bold text-xs uppercase">
          Batal
        </button>
        <button type="submit" class="tap-active px-4 py-2 rounded-lg bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs uppercase tracking-wider transition">
          Simpan Kas Keluar
        </button>
      </div>
    </form>
  </div>
</div>
@endsection
