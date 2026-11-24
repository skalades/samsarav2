<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>IZIN KELUAR PARKIR — {{ $registration->registration_code }}</title>
  @include('management.partials.thermal_compact_styles')
</head>
<body>

  <div class="actions">
    <button class="btn-print" onclick="window.print()">[PRINT] CETAK GATE PASS PARKIR</button>
    <a href="{{ route('management.gate.index', ['code' => $registration->registration_code]) }}" class="btn-back">← KEMBALI KE GATE</a>
  </div>

  @include('management.partials.thermal_bt', ['doc' => app(\App\Services\ThermalReceiptService::class)->parkingPass($registration, auth()->user()?->name), 'label' => 'CETAK GATE PASS VIA BLUETOOTH'])

  <div class="thermal-sheet">
    <div class="thermal-card">
      <div class="code-line">TIKET IZIN KELUAR PARKIR · PRK-PASS-{{ $registration->registration_code }}</div>

      <div class="sep">
        <div class="row"><span>SIMAKSI</span><span class="font-bold">{{ $registration->registration_code }}</span></div>
        <div class="row"><span>KETUA</span><span class="font-bold">{{ $registration->leader_name }}</span></div>
        <div class="row"><span>KONTAK</span><span>{{ $registration->leader_phone }}</span></div>
        <div class="row"><span>MASUK</span><span>{{ $registration->checked_in_at ? $registration->checked_in_at->format('d/m/Y H:i') : '-' }}</span></div>
        <div class="row"><span>KELUAR</span><span>{{ $registration->checked_out_at ? $registration->checked_out_at->format('d/m/Y H:i') : date('d/m/Y H:i') }}</span></div>
      </div>

      <div class="highlight-box">
        <div class="font-black" style="font-size: 11px;">
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
        <div class="font-bold" style="font-size: 9px;">
          PARKIR: LUNAS RESMI (Rp {{ number_format($registration->parking_fee, 0, ',', '.') }})
        </div>
      </div>

      <div class="row">
        <span>SAMPAH</span>
        <span class="font-bold">{{ $registration->trash_deposit_status === 'refunded' ? 'LENGKAP' : 'BEBAS JAMINAN' }}</span>
      </div>
      <div class="row">
        <span>PETUGAS</span>
        <span class="font-bold">{{ auth()->user()?->name ?? 'Ranger Pos Basecamp' }}</span>
      </div>
    </div>
  </div>

  <script>
    if (new URLSearchParams(window.location.search).get('autoprint') === '1') {
      window.print();
    }
  </script>
</body>
</html>
