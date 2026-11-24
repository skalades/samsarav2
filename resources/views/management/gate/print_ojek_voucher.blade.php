<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>VOUCHER OJEK — {{ $registration->registration_code }}</title>
  @include('management.partials.thermal_compact_styles')
</head>
<body>

  <div class="actions">
    <button class="btn-print" onclick="window.print()">[PRINT] CETAK VOUCHER</button>
    <a href="{{ route('management.gate.index', ['code' => $registration->registration_code]) }}" class="btn-back">← KEMBALI KE GATE</a>
  </div>

  @if(count($trips))
    @include('management.partials.thermal_bt', ['doc' => app(\App\Services\ThermalReceiptService::class)->ojekVouchers($registration, collect($trips)), 'label' => 'CETAK ' . count($trips) . ' VOUCHER VIA BLUETOOTH'])
  @endif

  <div class="thermal-sheet">
    @forelse($trips as $index => $trip)
      <div class="thermal-card">
        <div class="code-line">KUPON TIKET OJEK RESMI · *{{ $trip->trip_code }}*</div>

        <div class="sep">
          <div class="row"><span>SIMAKSI</span><span class="font-bold">{{ $registration->registration_code }}</span></div>
          <div class="row"><span>ROMBONGAN</span><span class="font-bold">{{ $registration->leader_name }}</span></div>
          <div class="row"><span>PENUMPANG</span><span class="font-bold">{{ $trip->passenger_name }}</span></div>
          <div class="row"><span>TRAYEK</span><span class="font-bold">{{ $trip->route ?? \App\Models\AppSetting::get('ojek_route_name', 'Basecamp → Pos 1') }}</span></div>
          <div class="row"><span>STATUS</span><span class="font-bold">LUNAS · {{ date('d/m H:i') }}</span></div>
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
    @empty
      <div class="thermal-card text-center">
        <div class="font-bold">Tidak ada voucher ojek.</div>
      </div>
    @endforelse
  </div>

  <script>
    if (new URLSearchParams(window.location.search).get('autoprint') === '1') {
      window.print();
    }
  </script>
</body>
</html>
