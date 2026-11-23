<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>VOUCHER OJEK {{ $trip->trip_code }} — SAMSARA</title>
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

    .voucher-card {
      width: 80mm;
      background: #FFF;
      padding: 16px;
      border: 1.5px dashed #000;
      box-shadow: 0 4px 12px rgba(0,0,0,0.06);
      font-size: 11px;
      line-height: 1.4;
    }

    .text-center { text-align: center; }
    .text-right { text-align: right; }
    .font-bold { font-weight: 700; }
    .font-black { font-weight: 800; }
    .border-b { border-bottom: 1px dashed #000; padding-bottom: 6px; margin-bottom: 6px; }
    .row { display: flex; justify-content: space-between; }

    .thermal-logo {
      max-width: 45mm;
      max-height: 40px;
      display: block;
      margin: 0 auto 5px auto;
      object-fit: contain;
      filter: grayscale(100%) contrast(150%);
    }

    .barcode-box {
      margin: 8px 0;
      padding: 6px;
      border: 1px solid #000;
      text-align: center;
      background: #FAFAFA;
      letter-spacing: 2px;
      font-weight: bold;
    }

    .stamp-box {
      margin-top: 8px;
      border: 1px dashed #666;
      padding: 6px;
      text-align: center;
      font-size: 9px;
      color: #333;
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
      .voucher-card { border: 1px dashed #000; box-shadow: none; width: 100%; padding: 0; }
      .actions { display: none; }
    }
  </style>
</head>
<body>

  <div class="actions">
    <button class="btn-print" onclick="window.print()">🖨️ CETAK TIKET</button>
    <a href="{{ route('management.ojek.index') }}" class="btn-back">← KEMBALI KE DISPATCH</a>
  </div>

  <div class="voucher-card" style="margin-top: 15px;">
    <!-- Header -->
    <div class="text-center border-b">
      @if(\App\Models\AppSetting::get('app_logo'))
        <img src="{{ asset('storage/' . \App\Models\AppSetting::get('app_logo')) }}" alt="Logo" class="thermal-logo">
      @endif
      <div class="font-black" style="font-size: 13px; letter-spacing: 1px;">{{ \App\Models\AppSetting::get('expedition_name', 'SAMSARA EXPEDITION') }}</div>
      <div class="font-bold" style="font-size: 10px; color: #FF5722;">TIKET KUPON OJEK RESMI</div>
      <div style="font-size: 8px;">PANGKALAN BASECAMP {{ strtoupper(\App\Models\AppSetting::get('mountain_name', 'CIKURAY')) }} {{ \App\Models\AppSetting::get('mountain_elevation', '2.821M') }}</div>
    </div>

    <!-- Voucher Code Box -->
    <div class="barcode-box">
      <div style="font-size: 12px; color: #000;">* {{ $trip->trip_code }} *</div>
      <div style="font-size: 8px; font-weight: normal; letter-spacing: 0;">SERI VOUCHER OPERASIONAL</div>
    </div>

    <!-- Detail Data -->
    <div class="border-b" style="font-size: 10px;">
      <div class="row">
        <span>PENUMPANG:</span>
        <span class="font-bold">{{ $trip->passenger_name }}</span>
      </div>
      <div class="row">
        <span>TRAYEK:</span>
        <span class="font-bold">{{ $trip->route }}</span>
      </div>
      <div class="row">
        <span>TARIF:</span>
        <span class="font-bold">Rp {{ number_format($trip->fare_amount, 0, ',', '.') }}</span>
      </div>
      <div class="row">
        <span>STATUS:</span>
        <span class="font-bold">LUNAS DI KASIR</span>
      </div>
      @if($trip->simaksi)
        <div class="row">
          <span>REF SIMAKSI:</span>
          <span>{{ $trip->simaksi->registration_code }}</span>
        </div>
      @endif
    </div>

    <!-- Driver Info -->
    <div style="font-size: 10px; margin-top: 4px;">
      <div class="row">
        <span>DRIVER BERTUGAS:</span>
        @if($trip->driver)
          <span class="font-bold">{{ $trip->driver->name }} ({{ $trip->driver->registration_number }})</span>
        @else
          <span class="font-bold">[ Menunggu Antrean Pangkalan ]</span>
        @endif
      </div>
      @if($trip->driver)
        <div class="row">
          <span>PLAT MOTOR:</span>
          <span class="font-bold">{{ $trip->driver->plate_number }}</span>
        </div>
      @endif
    </div>

    <div class="stamp-box">
      <div style="font-weight: bold; text-transform: uppercase;">VERIFIKASI PANGKALAN OJEK</div>
      <div style="height: 30px;"></div>
      <div>Serahkan kupon ini kepada pengemudi di pangkalan</div>
      <div style="font-size: 8px; color: #555;">Wajib helm keselamatan & satu penumpang per motor</div>
    </div>

    <div class="text-center" style="font-size: 8px; margin-top: 8px; color: #666;">
      TANGGAL: {{ $trip->created_at->format('d/m/Y H:i') }} WIB
    </div>
  </div>

</body>
</html>
