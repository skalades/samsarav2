<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>STRUK TRANSAKSI {{ $order->order_number }} — {{ \App\Models\AppSetting::get('expedition_name', 'SAMSARA') }} OPS</title>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&display=swap');

    body {
      font-family: 'JetBrains Mono', monospace;
      background: #F4F4F5;
      color: #000;
      margin: 0;
      padding: 20px;
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    .receipt-box {
      width: 80mm;
      background: #FFF;
      padding: 16px;
      border: 1px dashed #CCC;
      box-shadow: 0 4px 12px rgba(0,0,0,0.08);
      font-size: 11px;
      line-height: 1.4;
    }

    .text-center { text-align: center; }
    .text-right { text-align: right; }
    .font-bold { font-weight: 700; }
    .border-b { border-bottom: 1px dashed #000; padding-bottom: 6px; margin-bottom: 6px; }
    .border-t { border-top: 1px dashed #000; padding-top: 6px; margin-top: 6px; }
    
    .thermal-logo {
      max-width: 45mm;
      max-height: 42px;
      display: block;
      margin: 0 auto 6px auto;
      object-fit: contain;
      filter: grayscale(100%) contrast(150%);
    }
    
    .row { display: flex; justify-content: space-between; }

    .actions {
      margin-top: 20px;
      display: flex;
      gap: 10px;
    }

    button, a {
      font-family: 'JetBrains Mono', monospace;
      font-size: 12px;
      font-weight: bold;
      padding: 8px 16px;
      border-radius: 6px;
      cursor: pointer;
      text-decoration: none;
    }

    .btn-print { background: #FF5722; color: #000; border: none; }
    .btn-back { background: #333; color: #FFF; border: none; }

    @media print {
      body { background: #FFF; padding: 0; }
      .receipt-box { border: none; box-shadow: none; width: 100%; padding: 0; }
      .actions { display: none; }
    }
  </style>
</head>
<body>

  <div class="receipt-box">
    
    <!-- Header -->
    <div class="text-center border-b">
      @if(\App\Models\AppSetting::get('app_logo'))
        <img src="{{ asset('storage/' . \App\Models\AppSetting::get('app_logo')) }}" alt="Logo" class="thermal-logo">
      @endif
      <div class="font-bold" style="font-size: 14px;">{{ \App\Models\AppSetting::get('expedition_name', 'SAMSARA EXPEDITION') }}</div>
      <div style="font-size: 9px;">BASECAMP {{ strtoupper(\App\Models\AppSetting::get('mountain_name', 'CIKURAY')) }} {{ \App\Models\AppSetting::get('mountain_elevation', '2.821M') }} GARUT</div>
      @if(\App\Models\AppSetting::isSarRadioEnabled())
        <div style="font-size: 9px;">Radio SAR: {{ \App\Models\AppSetting::get('sar_frequency', '149.250 MHz') }} • WA: {{ \App\Models\AppSetting::get('admin_whatsapp_display', '0821-2233-4455') }}</div>
      @else
        <div style="font-size: 9px;">Hotline SAR: {{ \App\Models\AppSetting::get('emergency_hotline', '+62 821-2233-4455') }} • WA: {{ \App\Models\AppSetting::get('admin_whatsapp_display', '0821-2233-4455') }}</div>
      @endif
    </div>

    <!-- Info -->
    <div class="border-b" style="font-size: 10px;">
      <div class="row"><span>NO. STRUK:</span><span class="font-bold">{{ $order->order_number }}</span></div>
      <div class="row"><span>TANGGAL:</span><span>{{ $order->created_at->format('d/m/Y H:i:s') }}</span></div>
      <div class="row"><span>KASIR:</span><span>{{ $order->cashier->name }}</span></div>
      <div class="row"><span>PELANGGAN:</span><span class="font-bold">{{ $order->customer_name }}</span></div>
      @if($order->simaksi)
        <div class="row"><span>KODE SIMAKSI:</span><span class="font-bold">{{ $order->simaksi->registration_code }}</span></div>
      @endif
    </div>

    <!-- Items -->
    <div style="font-size: 10px;">
      @foreach($order->items as $item)
        <div style="margin-bottom: 4px;">
          <div class="font-bold">{{ $item->item_name }}</div>
          <div class="row" style="color: #444;">
            <span>{{ $item->quantity }} x {{ rupiah($item->unit_price) }}</span>
            <span class="font-bold text-right" style="color: #000;">{{ rupiah($item->subtotal) }}</span>
          </div>
        </div>
      @endforeach
    </div>

    <!-- Totals -->
    <div class="border-t" style="font-size: 11px;">
      <div class="row font-bold" style="font-size: 12px;">
        <span>TOTAL:</span>
        <span>{{ rupiah($order->total_amount) }}</span>
      </div>
      <div class="row" style="font-size: 10px; margin-top: 4px;">
        <span>METODE:</span>
        <span>{{ strtoupper($order->payment_method) }}</span>
      </div>
      @if($order->payment_method === 'cash')
        <div class="row" style="font-size: 10px;">
          <span>TUNAI:</span>
          <span>{{ rupiah($order->cash_tendered) }}</span>
        </div>
        <div class="row" style="font-size: 10px;">
          <span>KEMBALIAN:</span>
          <span>{{ rupiah($order->cash_change) }}</span>
        </div>
      @endif
    </div>

    <!-- Footer Barcode / Info -->
    <div class="border-t text-center" style="font-size: 9px; margin-top: 10px;">
      <div style="letter-spacing: 3px; font-weight: bold; margin: 6px 0;">* {{ $order->order_number }} *</div>
      <div>SIMPAN STRUK INI SEBAGAI BUKTI SERAH TERIMA ALAT & LAYANAN RESMI</div>
      <div style="margin-top: 4px; font-weight: bold;">LINDUNGI ALAM {{ strtoupper(\App\Models\AppSetting::get('mountain_name', 'GUNUNG CIKURAY')) }}</div>
    </div>

  </div>

  <div class="actions">
    <button onclick="window.print()" class="btn-print">
      <svg style="width: 16px; height: 16px; display: inline-block; vertical-align: middle; margin-right: 6px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
      CETAK STRUK SEKARANG
    </button>
    <a href="{{ route('management.pos.index') }}" class="btn-back">← KEMBALI KE KASIR</a>
    <a href="{{ route('management.pos.history') }}" class="btn-back">RIWAYAT TRANSAKSI</a>
  </div>

  @include('management.partials.thermal_bt', ['doc' => app(\App\Services\ThermalReceiptService::class)->posOrder($order), 'label' => 'CETAK STRUK VIA BLUETOOTH'])

</body>
</html>
