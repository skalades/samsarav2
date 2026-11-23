<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>SLIP PENCAIRAN HONOR OJEK {{ $settlement->settlement_code }} — SAMSARA</title>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700;800&display=swap');

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

    .settlement-card {
      width: 80mm;
      background: #FFF;
      padding: 16px;
      border: 1.5px solid #000;
      box-shadow: 0 4px 12px rgba(0,0,0,0.06);
      font-size: 11px;
      line-height: 1.4;
    }

    .text-center { text-align: center; }
    .text-right { text-align: right; }
    .font-bold { font-weight: 700; }
    .font-black { font-weight: 800; }
    .border-b { border-bottom: 1px dashed #000; padding-bottom: 6px; margin-bottom: 6px; }
    .border-t { border-top: 1px dashed #000; padding-top: 6px; margin-top: 6px; }
    .row { display: flex; justify-content: space-between; }

    .thermal-logo {
      max-width: 45mm;
      max-height: 40px;
      display: block;
      margin: 0 auto 5px auto;
      object-fit: contain;
      filter: grayscale(100%) contrast(150%);
    }

    .signature-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 8px;
      margin-top: 15px;
      padding-top: 8px;
      border-top: 1px dashed #000;
      text-align: center;
      font-size: 9px;
    }

    .sig-space {
      height: 40px;
    }

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
      .settlement-card { border: 1.5px solid #000; box-shadow: none; width: 100%; padding: 0; }
      .actions { display: none; }
    }
  </style>
</head>
<body>

  <div class="actions">
    <button class="btn-print" onclick="window.print()">🖨️ CETAK SLIP PENCAIRAN</button>
    <a href="{{ route('management.ojek.settlements') }}" class="btn-back">← KEMBALI KE REKONSILIASI</a>
  </div>

  <div class="settlement-card" style="margin-top: 15px;">
    <!-- Header -->
    <div class="text-center border-b">
      @if(\App\Models\AppSetting::get('app_logo'))
        <img src="{{ asset('storage/' . \App\Models\AppSetting::get('app_logo')) }}" alt="Logo" class="thermal-logo">
      @endif
      <div class="font-black" style="font-size: 13px; letter-spacing: 1px;">{{ \App\Models\AppSetting::get('expedition_name', 'SAMSARA EXPEDITION') }}</div>
      <div class="font-bold" style="font-size: 10px;">BUKTI PENCAIRAN HONOR OJEK (PAYOUT)</div>
      <div style="font-size: 8px;">KASIR BASECAMP {{ strtoupper(\App\Models\AppSetting::get('mountain_name', 'CIKURAY')) }} {{ \App\Models\AppSetting::get('mountain_elevation', '2.821M') }} GARUT</div>
    </div>

    <!-- Info -->
    <div class="border-b" style="font-size: 10px;">
      <div class="row">
        <span>NO. SLIP:</span>
        <span class="font-bold">{{ $settlement->settlement_code }}</span>
      </div>
      <div class="row">
        <span>TANGGAL:</span>
        <span>{{ $settlement->settled_at->format('d/m/Y H:i') }} WIB</span>
      </div>
      <div class="row">
        <span>KASIR BERTUGAS:</span>
        <span>{{ $settlement->cashier?->name }}</span>
      </div>
      <div class="row">
        <span>METODE:</span>
        <span class="font-bold" style="text-transform: uppercase;">{{ $settlement->payment_method }}</span>
      </div>
    </div>

    <!-- Driver Info -->
    <div class="border-b" style="font-size: 10px;">
      <div class="row">
        <span>PENGEMUDI:</span>
        <span class="font-bold">{{ $settlement->driver?->name }}</span>
      </div>
      <div class="row">
        <span>NO. ROMPI:</span>
        <span class="font-bold">{{ $settlement->driver?->registration_number }}</span>
      </div>
      <div class="row">
        <span>PLAT MOTOR:</span>
        <span class="font-bold">{{ $settlement->driver?->plate_number }}</span>
      </div>
    </div>

    <!-- Breakdown -->
    <div class="border-b" style="font-size: 10px;">
      <div class="row">
        <span>TOTAL TRIP DISELESAIKAN:</span>
        <span class="font-bold">{{ $settlement->total_trips }} Trip</span>
      </div>
      @if($settlement->notes)
        <div class="row">
          <span>CATATAN:</span>
          <span>{{ $settlement->notes }}</span>
        </div>
      @endif
    </div>

    <!-- Total Disbursed -->
    <div style="margin: 8px 0; padding: 6px; background: #F4F4F5; text-align: center;">
      <div style="font-size: 9px; text-transform: uppercase;">TOTAL DITERIMA PENGEMUDI:</div>
      <div style="font-size: 16px; font-weight: 800; color: #000;">
        Rp {{ number_format($settlement->total_amount, 0, ',', '.') }}
      </div>
    </div>

    <!-- Signatures -->
    <div class="signature-grid">
      <div>
        <div>Kasir Loket</div>
        <div class="sig-space"></div>
        <div class="font-bold">{{ $settlement->cashier?->name }}</div>
      </div>
      <div>
        <div>Pengemudi Penerima</div>
        <div class="sig-space"></div>
        <div class="font-bold">{{ $settlement->driver?->name }}</div>
      </div>
    </div>

    <div class="text-center" style="font-size: 8px; margin-top: 10px; color: #666;">
      Slip ini sah sebagai bukti tanda terima uang kasir & rekonsiliasi paguyuban ojek.
    </div>
  </div>

</body>
</html>
