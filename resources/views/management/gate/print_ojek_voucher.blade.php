<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>VOUCHER OJEK RESMI — {{ $registration->registration_code }} — SAMSARA</title>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700;800&family=Space+Grotesk:wght@600;700;800&display=swap');

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

    .vouchers-container {
      display: flex;
      flex-direction: column;
      gap: 16px;
      width: 80mm;
    }

    .voucher-card {
      background: #FFF;
      padding: 16px;
      border: 1.5px dashed #000;
      box-shadow: 0 4px 12px rgba(0,0,0,0.06);
      font-size: 11px;
      line-height: 1.4;
      page-break-inside: avoid;
    }

    .text-center { text-align: center; }
    .text-right { text-align: right; }
    .font-bold { font-weight: 700; }
    .font-black { font-weight: 800; }
    .border-b { border-bottom: 1px dashed #000; padding-bottom: 6px; margin-bottom: 6px; }
    .border-t { border-top: 1px dashed #000; padding-top: 6px; margin-top: 6px; }
    .border-solid { border-bottom: 1.5px solid #000; }
    
    .thermal-logo {
      max-width: 45mm;
      max-height: 40px;
      display: block;
      margin: 0 auto 5px auto;
      object-fit: contain;
      filter: grayscale(100%) contrast(150%);
    }
    
    .row { display: flex; justify-content: space-between; }

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
      .vouchers-container { width: 100%; gap: 10px; }
      .voucher-card { border: 1px dashed #000; box-shadow: none; padding: 12px; margin-bottom: 15px; }
      .actions { display: none; }
    }
  </style>
</head>
<body>

  <!-- Screen Actions Bar -->
  <div class="actions">
    <button class="btn-print" onclick="window.print()">[PRINT] CETAK VOUCHER</button>
    <a href="{{ route('management.gate.index', ['code' => $registration->registration_code]) }}" class="btn-back">← KEMBALI KE GATE</a>
  </div>

  <div class="vouchers-container" style="margin-top: 15px;">
    
    @forelse($trips as $index => $trip)
      <div class="voucher-card">
        <!-- Header -->
        <div class="text-center border-b">
          @if(\App\Models\AppSetting::get('app_logo'))
            <img src="{{ asset('storage/' . \App\Models\AppSetting::get('app_logo')) }}" alt="Logo" class="thermal-logo">
          @endif
          <div class="font-black" style="font-size: 13px; letter-spacing: 1px;">{{ \App\Models\AppSetting::get('expedition_name', 'SAMSARA EXPEDITION') }}</div>
          <div class="font-bold" style="font-size: 10px; color: #FF5722;">KUPON TIKET OJEK RESMI</div>
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
            <span>NO. SIMAKSI:</span>
            <span class="font-bold">{{ $registration->registration_code }}</span>
          </div>
          <div class="row">
            <span>ROMBONGAN:</span>
            <span class="font-bold">{{ $registration->leader_name }}</span>
          </div>
          <div class="row">
            <span>PENUMPANG:</span>
            <span class="font-bold">{{ $trip->passenger_name }}</span>
          </div>
          <div class="row">
            <span>TRAYEK:</span>
            <span class="font-bold">{{ $trip->route ?? \App\Models\AppSetting::get('ojek_route_name', 'Basecamp → Pos 1') }}</span>
          </div>
          <div class="row">
            <span>TANGGAL:</span>
            <span>{{ date('d/m/Y H:i') }} WIB</span>
          </div>
          <div class="row">
            <span>STATUS BAYAR:</span>
            <span class="font-bold">LUNAS DI LOKET (PAID)</span>
          </div>
        </div>

        <!-- Penugasan Driver -->
        <div style="font-size: 10px; margin-top: 4px;">
          <div class="row">
            <span>DRIVER BERTUGAS:</span>
            @if($trip->driver)
              <span class="font-bold">{{ $trip->driver->name }} ({{ $trip->driver->registration_number }})</span>
            @else
              <span class="font-bold">[ Antrean Pangkalan ]</span>
            @endif
          </div>
          @if($trip->driver)
            <div class="row">
              <span>PLAT NOMOR:</span>
              <span class="font-bold">{{ $trip->driver->plate_number }}</span>
            </div>
          @endif
        </div>

        <!-- Stamp / Dispatcher Signature -->
        <div class="stamp-box">
          <div style="font-weight: bold; text-transform: uppercase;">VERIFIKASI PANGKALAN OJEK</div>
          <div style="height: 28px;"></div>
          <div>Serahkan kupon ini kepada pengemudi di pangkalan</div>
          <div style="font-size: 8px; color: #555;">Wajib kenakan helm keselamatan & satu motor satu penumpang</div>
        </div>

        <div class="text-center" style="font-size: 8px; margin-top: 6px; color: #666;">
          KU-{{ $index + 1 }} DARI {{ count($trips) }} TIKET • TIKET HANYA BERLAKU HARI INI
        </div>
      </div>
    @empty
      <div class="voucher-card text-center">
        <p>Tidak ada data voucher ojek untuk rombongan ini.</p>
        <p style="font-size: 9px; color: #777;">Pastikan rombongan memilih opsi shuttle ojek saat pendaftaran atau kasir.</p>
      </div>
    @endforelse

  </div>

  <script>
    // Optional: auto trigger print on load if query param print=1 is passed
    if (new URLSearchParams(window.location.search).get('autoprint') === '1') {
      window.print();
    }
  </script>
</body>
</html>
