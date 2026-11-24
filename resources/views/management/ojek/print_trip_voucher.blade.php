<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>VOUCHER OJEK {{ $trip->trip_code }}</title>
  @include('management.partials.thermal_compact_styles')
</head>
<body>

  <div class="actions">
    <button class="btn-print" onclick="window.print()">[PRINT] CETAK TIKET</button>
    <a href="{{ route('management.ojek.index') }}" class="btn-back">← KEMBALI KE DISPATCH</a>
  </div>

  @include('management.partials.thermal_bt', ['doc' => app(\App\Services\ThermalReceiptService::class)->tripVoucher($trip), 'label' => 'CETAK TIKET VIA BLUETOOTH'])

  <div class="thermal-sheet">
    <div class="thermal-card">
      <div class="code-line">KUPON OJEK · *{{ $trip->trip_code }}*</div>

      <div class="sep">
        <div class="row"><span>PENUMPANG</span><span class="font-bold">{{ $trip->passenger_name }}</span></div>
        <div class="row"><span>TRAYEK</span><span class="font-bold">{{ $trip->route }}</span></div>
        <div class="row"><span>TARIF</span><span class="font-bold">Rp {{ number_format($trip->fare_amount, 0, ',', '.') }} (LUNAS)</span></div>
        @if($trip->simaksi)
          <div class="row"><span>SIMAKSI</span><span>{{ $trip->simaksi->registration_code }}</span></div>
        @endif
        <div class="row"><span>TANGGAL</span><span>{{ $trip->created_at->format('d/m/Y H:i') }}</span></div>
      </div>

      <div class="row">
        <span>DRIVER</span>
        @if($trip->driver)
          <span class="font-bold">{{ $trip->driver->name }} ({{ $trip->driver->registration_number }})</span>
        @else
          <span class="font-bold">[Antrean Pangkalan]</span>
        @endif
      </div>
      @if($trip->driver)
        <div class="row"><span>PLAT</span><span class="font-bold">{{ $trip->driver->plate_number }}</span></div>
      @endif
    </div>
  </div>

  <script>
    if (new URLSearchParams(window.location.search).get('autoprint') === '1') {
      window.print();
    }
  </script>
</body>
</html>
