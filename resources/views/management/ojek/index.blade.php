@extends('management.layout')

@section('title', 'Dispatch & Antrean Ojek Resmi — SAMSARA')
@section('page_title', 'Dispatch & Antrean Ojek Resmi')
@section('page_subtitle', 'Pangkalan Armada Shuttle Basecamp — Pos 1')

@section('content')
<div class="space-y-5">

  <!-- 1. LIVE METRICS -->
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
    <x-ui.stat label="Driver standby" :value="$standbyDrivers->count().' Motor'" hint="Siap berangkat giliran terdepan" tone="success" icon="users" />
    <x-ui.stat label="Sedang di jalur" :value="$onTripDrivers->count().' Trip'" hint="Mengantar menuju Pos 1" tone="brand" icon="bolt" />
    <x-ui.stat label="Trip selesai hari ini" :value="$todayTripCount.' Trip'" :hint="'Omzet: Rp '.number_format($todayGrossFare, 0, ',', '.')" tone="info" icon="check" />
    <x-ui.stat label="Hak pengemudi hari ini" :value="'Rp '.number_format($todayDriverEarnings, 0, ',', '.')" :hint="'Kas jalur: Rp '.number_format($todayBasecampShare, 0, ',', '.')" tone="success" icon="cash" />
  </div>

  <!-- 2. MAIN WORKSPACE -->
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
    
    <!-- LEFT COLUMN (2 COLS) -->
    <div class="lg:col-span-2 space-y-5">
      
      <!-- Box A: Voucher Menunggu Penugasan -->
      <x-ui.card :title="'Voucher Menunggu Driver ('.$pendingTrips->count().')'" subtitle="Pemberangkatan segera ke Pos 1" icon="bolt" :padding="false">
        @if($pendingTrips->isEmpty())
          <x-ui.empty icon="check" title="Tidak ada voucher menunggu" text="Voucher baru otomatis muncul saat pendaki lolos Gate atau membeli di Kasir POS." />
        @else
          <div class="p-3.5 space-y-3">
            @foreach($pendingTrips as $trip)
              <div class="p-3.5 bg-raised/70 ring-1 ring-line-strong rounded-2xl space-y-3">
                <div class="flex flex-wrap items-start justify-between gap-2">
                  <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                      <span class="font-code font-bold text-brand text-sm">{{ $trip->trip_code }}</span>
                      <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $trip->status_badge['bg'] }}">
                        {{ $trip->status_badge['label'] }}
                      </span>
                    </div>
                    <p class="text-ink font-semibold text-sm mt-1">{{ $trip->passenger_name }}</p>
                    <p class="text-xs text-muted mt-0.5">
                      {{ $trip->route }} · <strong class="text-ink tabular-nums">Rp {{ number_format($trip->fare_amount, 0, ',', '.') }}</strong>
                      @if($trip->simaksi) · <span class="text-sky-400 font-code">{{ $trip->simaksi->registration_code }}</span>@endif
                    </p>
                  </div>
                </div>

                <!-- Form Tugaskan Driver -->
                <form action="{{ route('management.ojek.assign', $trip->id) }}" method="POST" class="flex flex-col sm:flex-row sm:items-center gap-2 pt-2 border-t border-line">
                  @csrf
                  <select name="driver_id" id="driver_select_{{ $trip->id }}" required class="field flex-1">
                    <option value="">-- Pilih Driver Standby --</option>
                    @foreach($standbyDrivers as $idx => $driver)
                      <option value="{{ $driver->id }}">
                        #{{ $idx + 1 }} {{ $driver->registration_number }} — {{ $driver->name }} ({{ $driver->plate_number }})
                      </option>
                    @endforeach
                  </select>

                  <div class="flex items-center gap-2 shrink-0">
                    <x-ui.button type="submit" name="action" value="assign_and_print" class="flex-1 sm:flex-none">Tugaskan &amp; Cetak</x-ui.button>
                    <x-ui.button variant="secondary" icon="printer" onclick="printTripWithSelectedDriver({{ $trip->id }})">Cetak</x-ui.button>
                  </div>
                </form>
              </div>
            @endforeach
          </div>
        @endif
      </x-ui.card>

      <!-- Box B: Trip Aktif Sedang Jalan -->
      <x-ui.card :title="'Trip Berjalan di Jalur ('.$activeTrips->count().')'" subtitle="Konfirmasi apabila pengemudi sudah tiba" icon="mountain" :padding="false">
        @if($activeTrips->isEmpty())
          <x-ui.empty icon="bolt" title="Tidak ada armada di jalur" text="Belum ada driver yang sedang mengantar saat ini." />
        @else
          {{-- Mobile: kartu --}}
          <div class="lg:hidden divide-y divide-line">
            @foreach($activeTrips as $active)
              <div class="p-3.5 space-y-2.5">
                <div class="flex items-start justify-between gap-2">
                  <div class="min-w-0">
                    <span class="font-code font-bold text-brand text-sm">{{ $active->trip_code }}</span>
                    <p class="text-sm font-semibold text-ink mt-0.5">{{ $active->driver?->name }} <span class="text-xs font-code text-amber-400">({{ $active->driver?->registration_number }})</span></p>
                    <p class="text-xs text-muted">{{ $active->passenger_name }} · {{ $active->route }}</p>
                    <p class="text-[11px] text-subtle mt-0.5">Plat: {{ $active->driver?->plate_number }} · Berangkat {{ $active->dispatched_at ? $active->dispatched_at->format('H:i') : '-' }} WIB</p>
                  </div>
                </div>
                <div class="flex items-center gap-2 pt-1">
                  <x-ui.button variant="secondary" size="sm" icon="printer" :href="route('management.ojek.print_voucher', $active->id)" target="_blank">Cetak</x-ui.button>
                  <form action="{{ route('management.ojek.complete', $active->id) }}" method="POST" class="flex-1" onsubmit="return confirm('Konfirmasi trip {{ $active->trip_code }} telah selesai sampai tujuan?')">
                    @csrf
                    <x-ui.button type="submit" variant="success" size="sm" block icon="check">Selesai Trip</x-ui.button>
                  </form>
                </div>
              </div>
            @endforeach
          </div>

          {{-- Desktop: tabel --}}
          <div class="hidden lg:block overflow-x-auto">
            <table class="w-full text-left text-sm">
              <thead class="bg-raised/40 border-b border-line text-[11px] text-subtle uppercase">
                <tr>
                  <th class="py-3 px-4 font-semibold">Kode Trip</th>
                  <th class="py-3 px-3 font-semibold">Pengemudi Resmi</th>
                  <th class="py-3 px-3 font-semibold">Penumpang</th>
                  <th class="py-3 px-3 font-semibold">Berangkat</th>
                  <th class="py-3 px-4 font-semibold text-right">Aksi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-line">
                @foreach($activeTrips as $active)
                  <tr class="hover:bg-hover/40">
                    <td class="py-3 px-4 font-code font-bold text-brand whitespace-nowrap">{{ $active->trip_code }}</td>
                    <td class="py-3 px-3">
                      <div class="font-semibold text-ink flex items-center gap-1.5">
                        <x-ui.badge tone="warning" size="xs">{{ $active->driver?->registration_number }}</x-ui.badge>
                        <span>{{ $active->driver?->name }}</span>
                      </div>
                      <div class="text-xs text-muted">Plat: {{ $active->driver?->plate_number }}</div>
                    </td>
                    <td class="py-3 px-3">
                      <div class="text-ink">{{ $active->passenger_name }}</div>
                      <div class="text-xs text-muted">{{ $active->route }}</div>
                    </td>
                    <td class="py-3 px-3 text-muted tabular-nums whitespace-nowrap">
                      {{ $active->dispatched_at ? $active->dispatched_at->format('H:i') : '-' }} WIB
                    </td>
                    <td class="py-3 px-4 text-right whitespace-nowrap">
                      <div class="flex items-center justify-end gap-1.5">
                        <x-ui.button variant="secondary" size="sm" :href="route('management.ojek.print_voucher', $active->id)" target="_blank">Cetak</x-ui.button>
                        <form action="{{ route('management.ojek.complete', $active->id) }}" method="POST" class="inline" onsubmit="return confirm('Konfirmasi trip {{ $active->trip_code }} telah selesai sampai tujuan?')">
                          @csrf
                          <x-ui.button type="submit" variant="success" size="sm" icon="check">Selesai</x-ui.button>
                        </form>
                      </div>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @endif
      </x-ui.card>

    </div>

    <!-- RIGHT COLUMN (1 COL) -->
    <div class="space-y-5">
      <x-ui.card title="Antrean Pangkalan (FIFO)" subtitle="Driver kembali dari Pos 1 masuk urutan belakang" icon="users">
        <x-slot:actions><a href="{{ route('management.ojek.drivers') }}" class="text-xs font-semibold text-brand hover:underline">Kelola →</a></x-slot:actions>
        <div class="space-y-2">
          @forelse($standbyDrivers as $index => $driver)
            <div class="p-2.5 rounded-xl bg-raised/70 ring-1 {{ $index === 0 ? 'ring-emerald-500/40 bg-emerald-950/20' : 'ring-line' }} flex items-center justify-between gap-2">
              <div class="flex items-center gap-2.5 min-w-0">
                <div class="w-7 h-7 rounded-lg flex items-center justify-center font-bold text-xs shrink-0 {{ $index === 0 ? 'bg-emerald-500 text-black' : 'bg-surface text-muted ring-1 ring-line' }}">
                  {{ $index + 1 }}
                </div>
                <div class="min-w-0">
                  <div class="flex items-center gap-1.5">
                    <span class="font-semibold text-sm text-ink truncate">{{ $driver->name }}</span>
                    <span class="text-[11px] font-code text-amber-400 shrink-0">{{ $driver->registration_number }}</span>
                  </div>
                  <div class="text-[11px] text-muted">Plat: <strong class="text-ink">{{ $driver->plate_number }}</strong></div>
                </div>
              </div>

              @if($index === 0)
                <x-ui.badge tone="success" size="xs">Giliran 1</x-ui.badge>
              @endif
            </div>
          @empty
            <x-ui.empty icon="users" title="Tidak ada driver standby" />
          @endforelse
        </div>

        @if($offDutyDrivers->isNotEmpty())
          <div class="mt-3 pt-3 border-t border-line">
            <span class="text-[11px] text-muted font-semibold block mb-2">Driver Off Duty ({{ $offDutyDrivers->count() }})</span>
            <div class="flex flex-wrap gap-1.5">
              @foreach($offDutyDrivers as $off)
                <x-ui.badge size="xs">{{ $off->registration_number }} · {{ $off->name }}</x-ui.badge>
              @endforeach
            </div>
          </div>
        @endif
      </x-ui.card>

      <x-ui.card title="Akses Cepat Logistik" icon="grid">
        <div class="space-y-2">
          <a href="{{ route('management.ojek.settlements') }}" class="px-3.5 py-2.5 rounded-xl bg-raised hover:bg-hover ring-1 ring-line text-sm font-medium text-ink flex items-center justify-between transition">
            <span>Rekonsiliasi &amp; Payout</span>
            <x-ui.icon name="chevron-right" class="w-4 h-4 text-emerald-400" />
          </a>
          <a href="{{ route('management.gate.index') }}" class="px-3.5 py-2.5 rounded-xl bg-raised hover:bg-hover ring-1 ring-line text-sm font-medium text-ink flex items-center justify-between transition">
            <span>Gate Check-In &amp; Manifest</span>
            <x-ui.icon name="chevron-right" class="w-4 h-4 text-sky-400" />
          </a>
          <a href="{{ route('management.pos.index') }}" class="px-3.5 py-2.5 rounded-xl bg-raised hover:bg-hover ring-1 ring-line text-sm font-medium text-ink flex items-center justify-between transition">
            <span>Kasir POS Basecamp</span>
            <x-ui.icon name="chevron-right" class="w-4 h-4 text-brand" />
          </a>
        </div>
      </x-ui.card>
    </div>

  </div>

</div>

@push('scripts')
<script>
  function printTripWithSelectedDriver(tripId) {
    const select = document.getElementById('driver_select_' + tripId);
    const driverId = select ? select.value : '';
    let url = '/management/ojek/trip/' + tripId + '/print';
    if (driverId) {
      url += '?driver_id=' + encodeURIComponent(driverId) + '&assign=1';
      window.open(url, '_blank');
      setTimeout(() => { window.location.reload(); }, 1200);
    } else {
      window.open(url, '_blank');
    }
  }
</script>
@endpush
@endsection
