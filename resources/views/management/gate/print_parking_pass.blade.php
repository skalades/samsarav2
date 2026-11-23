<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>IZIN KELUAR PARKIR — {{ $registration->registration_code }} — SAMSARA</title>
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

    .pass-box {
      width: 80mm;
      background: #FFF;
      padding: 18px;
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

    .thermal-logo {
      max-width: 45mm;
      max-height: 40px;
      display: block;
      margin: 0 auto 5px auto;
      object-fit: contain;
      filter: grayscale(100%) contrast(150%);
    }
    
    .row { display: flex; justify-content: space-between; }

    .badge-clearance {
      background: #000;
      color: #FFF;
      padding: 6px;
      text-align: center;
      font-weight: bold;
      letter-spacing: 1px;
      margin: 8px 0;
      font-size: 11px;
    }

    .vehicle-highlight {
      background: #F4F4F5;
      border: 1px solid #CCC;
      padding: 8px;
      margin: 8px 0;
      text-align: center;
    }

    .stamp-box {
      margin-top: 10px;
      border: 1px solid #000;
      padding: 8px;
      text-align: center;
      font-size: 9px;
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
      .pass-box { border: 1.5px solid #000; box-shadow: none; width: 100%; padding: 12px; }
      .actions { display: none; }
    }
  </style>
</head>
<body>

  <!-- Screen Actions Bar -->
  <div class="actions">
    <button class="btn-print" onclick="window.print()">🖨️ CETAK GATE PASS PARKIR (PRINT)</button>
    <a href="{{ route('management.gate.index', ['code' => $registration->registration_code]) }}" class="btn-back">← KEMBALI KE GATE</a>
  </div>

  <div class="pass-box" style="margin-top: 15px;">
    
    <!-- Header -->
    <div class="text-center border-b">
      @if(\App\Models\AppSetting::get('app_logo'))
        <img src="{{ asset('storage/' . \App\Models\AppSetting::get('app_logo')) }}" alt="Logo" class="thermal-logo">
      @endif
      <div class="font-black" style="font-size: 13px; letter-spacing: 1px;">{{ \App\Models\AppSetting::get('expedition_name', 'SAMSARA EXPEDITION') }}</div>
      <div class="font-bold" style="font-size: 10px;">TIKET IZIN KELUAR PARKIR</div>
      <div style="font-size: 8px;">SECURITY GATE PASS — AREA PARKIR {{ strtoupper(\App\Models\AppSetting::get('basecamp_name', 'BASECAMP SAMSARA')) }}</div>
    </div>

    <!-- Clearance Badge -->
    <div class="badge-clearance">
      ✓ KENDARAAN DIVERIFIKASI RESMI
    </div>

    <!-- Gate Pass Code -->
    <div class="text-center" style="font-size: 10px; margin-bottom: 6px;">
      NO. PASS: <strong>PRK-PASS-{{ $registration->registration_code }}</strong>
    </div>

    <!-- Group & Simaksi Details -->
    <div class="border-b" style="font-size: 10px;">
      <div class="row">
        <span>NO. SIMAKSI:</span>
        <span class="font-bold">{{ $registration->registration_code }}</span>
      </div>
      <div class="row">
        <span>KETUA ROMBONGAN:</span>
        <span class="font-bold">{{ $registration->leader_name }}</span>
      </div>
      <div class="row">
        <span>NO. KONTAK:</span>
        <span>{{ $registration->leader_phone }}</span>
      </div>
      <div class="row">
        <span>CHECK-IN MASUK:</span>
        <span>{{ $registration->checked_in_at ? $registration->checked_in_at->format('d/m/Y H:i') : '-' }} WIB</span>
      </div>
      <div class="row">
        <span>CHECK-OUT KELUAR:</span>
        <span>{{ $registration->checked_out_at ? $registration->checked_out_at->format('d/m/Y H:i') : date('d/m/Y H:i') }} WIB</span>
      </div>
    </div>

    <!-- Vehicle Details Highlight -->
    <div class="vehicle-highlight">
      <div style="font-size: 9px; text-transform: uppercase; color: #555;">DETAIL KENDARAAN TERDAFTAR:</div>
      <div style="font-size: 13px; font-weight: 800; margin-top: 2px;">
        @if($registration->motorcycle_count > 0 && $registration->car_count > 0)
          {{ $registration->motorcycle_count }} MOTOR & {{ $registration->car_count }} MOBIL
        @elseif($registration->motorcycle_count > 0)
          {{ $registration->motorcycle_count }} UNIT SEPEDA MOTOR
        @elseif($registration->car_count > 0)
          {{ $registration->car_count }} UNIT MOBIL PRIBADI
        @else
          1 UNIT KENDARAAN TERDATA
        @endif
      </div>
      <div style="font-size: 9px; color: #000; font-weight: bold; margin-top: 2px;">
        BIAYA PARKIR: LUNAS RESMI (Rp {{ number_format($registration->parking_fee, 0, ',', '.') }})
      </div>
    </div>

    <!-- Environmental & Security Verification -->
    <div class="border-b" style="font-size: 9px;">
      <div class="row">
        <span>STATUS SAMPAH:</span>
        <span class="font-bold">
          {{ $registration->trash_deposit_status === 'refunded' ? 'DIVERIFIKASI LENGKAP' : 'BEBAS JAMINAN' }}
        </span>
      </div>
      <div class="row">
        <span>PETUGAS GATE:</span>
        <span class="font-bold">{{ auth()->user()?->name ?? 'Ranger Pos Basecamp' }}</span>
      </div>
    </div>

    <!-- Pos Security Signature Box -->
    <div class="stamp-box">
      <div style="font-weight: bold; text-transform: uppercase;">VALIDASI POS PORTAL PARKIR</div>
      <div style="height: 35px; display: flex; align-items: center; justify-content: center; color: #999; font-size: 8px;">
        [ PARAF / CAP PETUGAS PARKIR ]
      </div>
      <div style="font-size: 8px; color: #333;">
        Tunjukkan tiket ini kepada petugas portal gerbang saat mengeluarkan kendaraan dari area parkir.
      </div>
    </div>

    <div class="text-center" style="font-size: 8px; margin-top: 8px; color: #666;">
      Terima kasih telah mendaki bersama {{ \App\Models\AppSetting::get('expedition_name', 'SAMSARA') }} {{ \App\Models\AppSetting::get('mountain_name', 'Cikuray') }}. Utamakan keselamatan dalam perjalanan pulang!
    </div>

  </div>

  <script>
    if (new URLSearchParams(window.location.search).get('autoprint') === '1') {
      window.print();
    }
  </script>
</body>
</html>
