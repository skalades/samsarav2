<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Bukti Pengeluaran Kas — {{ $expense->expense_code }}</title>
  <style>
    @page {
      size: 80mm auto;
      margin: 4mm;
    }
    body {
      font-family: 'Courier New', Courier, monospace;
      font-size: 11px;
      color: #000;
      background: #fff;
      margin: 0;
      padding: 6px;
      max-width: 76mm;
      margin: 0 auto;
    }
    .text-center { text-align: center; }
    .text-right { text-align: right; }
    .font-bold { font-weight: bold; }
    .divider {
      border-top: 1px dashed #000;
      margin: 6px 0;
    }
    .double-divider {
      border-top: 2px solid #000;
      margin: 8px 0;
    }
    .row {
      display: flex;
      justify-content: space-between;
      margin-bottom: 3px;
    }
    .thermal-logo {
      max-width: 45mm;
      max-height: 42px;
      display: block;
      margin: 0 auto 6px auto;
      object-fit: contain;
      filter: grayscale(100%) contrast(150%);
    }
    .signature-grid {
      display: flex;
      justify-content: space-between;
      margin-top: 20px;
      text-align: center;
      font-size: 9px;
    }
    .signature-box {
      width: 45%;
    }
    .signature-line {
      margin-top: 35px;
      border-top: 1px solid #000;
    }
    .no-print {
      margin-bottom: 12px;
      text-align: center;
    }
    .print-btn {
      background: #111;
      color: #fff;
      padding: 6px 14px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-weight: bold;
      font-size: 12px;
    }
    @media print {
      .no-print { display: none !important; }
    }
  </style>
</head>
<body>

  <div class="no-print">
    <button onclick="window.print()" class="print-btn">CETAK BUKTI KAS KELUAR</button>
  </div>

  <div class="text-center">
    @if(\App\Models\AppSetting::get('app_logo'))
      <img src="{{ asset('storage/' . \App\Models\AppSetting::get('app_logo')) }}" alt="Logo" class="thermal-logo">
    @endif
    <div class="font-bold" style="font-size: 13px;">{{ strtoupper(\App\Models\AppSetting::get('expedition_name', 'SAMSARA EXPEDITION')) }}</div>
    <div style="font-size: 9px;">BASECAMP {{ strtoupper(\App\Models\AppSetting::get('basecamp_name', 'SAMSARA')) }} ({{ \App\Models\AppSetting::get('mountain_name', 'CIKURAY') }} {{ \App\Models\AppSetting::get('mountain_elevation', '2.821M') }})</div>
    <div class="divider"></div>
    <div class="font-bold" style="font-size: 11px;">BUKTI PENGELUARAN KAS OPERASIONAL</div>
    <div style="font-size: 10px;">(PETTY CASH OUT VOUCHER)</div>
  </div>

  <div class="divider"></div>

  <div class="row">
    <span>No. Voucher:</span>
    <span class="font-bold">{{ $expense->expense_code }}</span>
  </div>
  <div class="row">
    <span>Waktu/Tgl:</span>
    <span>{{ $expense->created_at->format('d/m/Y H:i') }} WIB</span>
  </div>
  <div class="row">
    <span>Kasir Petugas:</span>
    <span>{{ $expense->cashier?->name ?? 'Kasir Posko' }}</span>
  </div>
  <div class="row">
    <span>Metode Kas:</span>
    <span class="font-bold">{{ strtoupper($expense->payment_method) }}</span>
  </div>

  <div class="divider"></div>

  <div class="row">
    <span>Kategori:</span>
    <span class="font-bold">{{ $expense->category_label }}</span>
  </div>
  <div class="row">
    <span>Dibayarkan ke:</span>
    <span class="font-bold">{{ $expense->recipient_name }}</span>
  </div>

  <div style="margin-top: 4px; margin-bottom: 4px;">
    <span>Keperluan:</span>
    <div style="font-style: italic; font-size: 10px; margin-top: 2px;">{{ $expense->notes }}</div>
  </div>

  <div class="double-divider"></div>

  <div class="row" style="font-size: 13px;">
    <span class="font-bold">TOTAL KAS KELUAR:</span>
    <span class="font-bold">Rp {{ number_format($expense->amount, 0, ',', '.') }}</span>
  </div>

  <div class="divider"></div>

  <div class="signature-grid">
    <div class="signature-box">
      <div>Kasir Posko</div>
      <div class="signature-line">({{ $expense->cashier?->name ?? 'Kasir' }})</div>
    </div>
    <div class="signature-box">
      <div>Penerima Dana</div>
      <div class="signature-line">({{ $expense->recipient_name }})</div>
    </div>
  </div>

  <div class="divider" style="margin-top: 15px;"></div>
  <div class="text-center" style="font-size: 8.5px; color: #555;">
    Dicetak resmi oleh Sistem POS Terpadu SAMSARA.<br>
    Bukti sah pengeluaran laci kasir basecamp.
  </div>

</body>
</html>
