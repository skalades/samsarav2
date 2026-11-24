<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>SURAT JALAN PENDAKI — {{ $registration->registration_code }} — SAMSARA</title>
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

    .pass-container {
      width: 80mm;
      max-width: 100%;
    }

    .pass-card {
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

    .badge-checkin {
      display: inline-block;
      padding: 3px 8px;
      background: #000;
      color: #FFF;
      font-weight: 800;
      font-size: 11px;
      margin: 4px 0;
      letter-spacing: 1px;
    }

    .member-item {
      padding: 2px 0;
      font-size: 10px;
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
      .pass-container { width: 100%; }
      .pass-card { border: 1px dashed #000; box-shadow: none; padding: 12px; margin-bottom: 0; }
      .actions { display: none; }
    }
  </style>
</head>
<body>

  <div class="pass-container">
    <div class="pass-card">
      
      <!-- Header -->
      <div class="text-center border-b border-solid">
        @php
          $profile = \App\Models\AppSetting::getExpeditionProfile();
        @endphp
        @if(!empty($profile['app_logo']))
          <img src="{{ asset('storage/' . $profile['app_logo']) }}" class="thermal-logo" alt="Logo">
        @endif
        <div class="font-black" style="font-size: 14px;">{{ $profile['expedition_name'] ?? 'SAMSARA EXPEDITION' }}</div>
        <div style="font-size: 9px;">POS GERBANG BASECAMP PENDAKIAN</div>
        <div style="font-size: 8px; color: #555;">{{ $profile['mountain_name'] ?? 'GUNUNG CIKURAY' }} ({{ $profile['mountain_elevation'] ?? '2.821 MDPL' }})</div>
      </div>

      <!-- Pass Title & Status -->
      <div class="text-center" style="margin: 6px 0;">
        <div class="badge-checkin">SURAT JALAN PENDAKI</div>
        <div style="font-size: 10px; font-weight: 800; margin-top: 3px; border: 1px solid #000; padding: 2px 4px; display: inline-block;">
          {{ $registration->isTektok() ? '[KATEGORI: TEKTOK • 1 HARI • DILARANG CAMP]' : '[KATEGORI: CAMP • POS 2 / POS 3]' }}
        </div>
        <div style="font-size: 9px; font-weight: bold; margin-top: 2px;">BUKTI FISIK PEMERIKSAAN SETIAP POS</div>
      </div>

      <!-- Registration Code & Barcode -->
      <div class="barcode-box">
        <div style="font-size: 8px; letter-spacing: 0;">KODE REGISTRASI RESMI:</div>
        <div class="font-black" style="font-size: 14px;">*{{ $registration->registration_code }}*</div>
      </div>

      <!-- Time & Leader Info -->
      <div class="border-b">
        <div class="row">
          <span>WAKTU MASUK:</span>
          <span class="font-bold">{{ $registration->checked_in_at ? $registration->checked_in_at->translatedFormat('d/m/Y H:i') : now()->translatedFormat('d/m/Y H:i') }} WIB</span>
        </div>
        <div class="row">
          <span>KETUA TIM:</span>
          <span class="font-bold">{{ strtoupper($registration->leader_name) }}</span>
        </div>
        <div class="row">
          <span>NO. WHATSAPP:</span>
          <span>{{ $registration->leader_phone }}</span>
        </div>
        <div class="row">
          <span>TOTAL PENDAKI:</span>
          <span class="font-black">{{ $registration->total_climbers }} ORANG</span>
        </div>
        <div class="row">
          <span>RENCANA TURUN:</span>
          <span>{{ $registration->return_date ? \Carbon\Carbon::parse($registration->return_date)->translatedFormat('d/m/Y') : 'HARI SAMA' }}</span>
        </div>
      </div>

      <!-- Verification Badges -->
      <div class="border-b" style="font-size: 9px;">
        <div class="row">
          <span>STATUS MANIFEST:</span>
          <span class="font-bold">AKTIF DI JALUR</span>
        </div>
        <div class="row">
          <span>FOTO REGU:</span>
          <span class="font-bold">{{ $registration->group_photo_path ? 'TERVERIFIKASI SISTEM' : 'BELUM DIUNGGAH' }}</span>
        </div>
        @if($registration->ojek_count > 0)
          <div class="row">
            <span>LAYANAN OJEK:</span>
            <span class="font-bold">{{ $registration->ojek_count }} PENUMPANG</span>
          </div>
        @endif
        @if($registration->has_vehicle || $registration->parking_fee > 0)
          <div class="row">
            <span>PARKIR KENDARAAN:</span>
            <span>{{ $registration->motorcycle_count }} MTR / {{ $registration->car_count }} MBL</span>
          </div>
        @endif
      </div>

      <!-- Manifest Summary -->
      <div class="border-b">
        <div class="font-bold" style="font-size: 9px; margin-bottom: 3px;">DAFTAR MANIFEST PENDAKI:</div>
        @foreach($registration->members as $idx => $m)
          <div class="member-item row">
            <span>{{ $idx + 1 }}. {{ strtoupper(substr($m->full_name, 0, 18)) }}</span>
            <span>
              ({{ $m->gender }})
              @if($m->is_leader) [KETUA] @endif
              @if($m->medical_conditions && $m->medical_conditions !== 'Tidak Ada')
                <strong style="color: #000;">*MEDIS</strong>
              @endif
            </span>
          </div>
        @endforeach
      </div>

      <!-- Kotak Paraf Fisik Ranger Pos (Analog Backup) -->
      <div style="margin-top: 8px; border: 1.5px solid #000; padding: 4px; font-size: 8.5px;">
        <div class="font-bold text-center border-b" style="padding-bottom: 2px; margin-bottom: 3px;">BUKTI PEMERIKSAAN POS LAPANGAN</div>
        <div class="row" style="margin-bottom: 2px;">
          <span>POS 1: [ ] Naik [ ] Turun</span>
          <span>Jam:____ Paraf:____</span>
        </div>
        <div class="row" style="margin-bottom: 2px;">
          <span>POS 2: [ ] Lewat [ ] CAMP</span>
          <span>Jam:____ Paraf:____</span>
        </div>
        <div class="row">
          <span>POS 3: [ ] Lewat [ ] CAMP</span>
          <span>Jam:____ Paraf:____</span>
        </div>
      </div>

      <!-- Emergency Hotline & Safety Rules -->
      <div class="stamp-box">
        <div class="font-bold">HOTLINE RESCUE & FREKUENSI RADIO</div>
        <div>SAR BASECAMP: {{ $profile['emergency_hotline'] ?? '+62 821-2233-4455' }}</div>
        <div>FREKUENSI HT: {{ $profile['sar_frequency'] ?? '149.250 MHz' }}</div>
        <div style="margin-top: 4px; font-style: italic;">
          Simpan surat jalan ini di saku/ransel. Tunjukkan ke ranger di setiap Pos/Shelter pendakian.
        </div>
      </div>

      <div class="text-center" style="font-size: 8px; color: #666; margin-top: 8px;">
        Dicetak Otomatis Pos Gerbang • {{ date('d-m-Y H:i:s') }}
      </div>

    </div>
  </div>

  <!-- Print Actions -->
  <div class="actions">
    <button class="btn-print" onclick="window.print()">CETAK SURAT JALAN (PRINT)</button>
    <a href="{{ route('management.gate.index', ['code' => $registration->registration_code]) }}" class="btn-back">KEMBALI KE POS GATE</a>
  </div>

  @include('management.partials.thermal_bt', ['doc' => app(\App\Services\ThermalReceiptService::class)->climberPass($registration), 'label' => 'CETAK SURAT JALAN VIA BLUETOOTH'])

  <script>
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('print') === '1') {
      window.onload = function() {
        window.print();
      };
    }
  </script>
</body>
</html>
