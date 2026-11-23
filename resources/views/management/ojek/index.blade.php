@extends('management.layout')

@section('title', 'Dispatch & Antrean Ojek Resmi — SAMSARA')
@section('page_title', 'Dispatch & Antrean Ojek Resmi')
@section('page_subtitle', 'Pangkalan Armada Shuttle Basecamp — Pos 1')

@section('content')
<div class="space-y-6">

  <!-- ======================================================== -->
  <!-- 1. LIVE METRICS & QUICK SUMMARY                          -->
  <!-- ======================================================== -->
  <div class="grid grid-cols-2 md:grid-cols-4 gap-4 font-mono-tech">
    <div class="p-4 bg-[#121418] border border-[#1E2229] rounded-xl flex flex-col justify-between">
      <div class="flex items-center justify-between text-[11px] text-stone-400">
        <span>DRIVER STANDBY</span>
        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
      </div>
      <div class="mt-2 text-2xl font-display font-black text-white">
        {{ $standbyDrivers->count() }} <span class="text-xs font-mono-tech font-normal text-stone-400">Motor</span>
      </div>
      <span class="text-[10px] text-emerald-400 mt-1">Siap berangkat giliran terdepan</span>
    </div>

    <div class="p-4 bg-[#121418] border border-[#1E2229] rounded-xl flex flex-col justify-between">
      <div class="flex items-center justify-between text-[11px] text-stone-400">
        <span>SEDANG DI JALUR</span>
        <span class="w-2 h-2 rounded-full bg-[#FF5722]"></span>
      </div>
      <div class="mt-2 text-2xl font-display font-black text-white">
        {{ $onTripDrivers->count() }} <span class="text-xs font-mono-tech font-normal text-stone-400">Trip</span>
      </div>
      <span class="text-[10px] text-[#FF5722] mt-1">Mengantar menuju Pos 1</span>
    </div>

    <div class="p-4 bg-[#121418] border border-[#1E2229] rounded-xl flex flex-col justify-between">
      <div class="flex items-center justify-between text-[11px] text-stone-400">
        <span>TRIP SELESAI HARI INI</span>
        <svg class="w-3.5 h-3.5 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
      </div>
      <div class="mt-2 text-2xl font-display font-black text-white">
        {{ $todayTripCount }} <span class="text-xs font-mono-tech font-normal text-stone-400">Trip</span>
      </div>
      <span class="text-[10px] text-stone-400 mt-1">Total Omzet: Rp {{ number_format($todayGrossFare, 0, ',', '.') }}</span>
    </div>

    <div class="p-4 bg-[#121418] border border-[#1E2229] rounded-xl flex flex-col justify-between">
      <div class="flex items-center justify-between text-[11px] text-stone-400">
        <span>HAK PENGEMUDI HARI INI</span>
        <svg class="w-3.5 h-3.5 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
      </div>
      <div class="mt-2 text-xl font-display font-black text-emerald-400">
        Rp {{ number_format($todayDriverEarnings, 0, ',', '.') }}
      </div>
      <span class="text-[10px] text-stone-400 mt-1">Kas Jalur: Rp {{ number_format($todayBasecampShare, 0, ',', '.') }}</span>
    </div>
  </div>

  @if(session('success'))
    <div class="p-3.5 bg-emerald-500/10 border border-emerald-500/30 rounded-xl text-emerald-400 font-mono-tech text-xs flex items-center justify-between">
      <div class="flex items-center gap-2">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        <span>{{ session('success') }}</span>
      </div>
    </div>
  @endif

  @if(session('error'))
    <div class="p-3.5 bg-red-500/10 border border-red-500/30 rounded-xl text-red-400 font-mono-tech text-xs flex items-center gap-2">
      <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
      <span>{{ session('error') }}</span>
    </div>
  @endif

  <!-- ======================================================== -->
  <!-- 2. MAIN WORKSPACE: DISPATCH PANE & LIVE QUEUE            -->
  <!-- ======================================================== -->
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 font-mono-tech text-xs">
    
    <!-- LEFT COLUMN (2 COLS): VOUCHERS WAITING & ONGOING TRIPS -->
    <div class="lg:col-span-2 space-y-6">
      
      <!-- Box A: Tiket / Voucher Menunggu Penugasan Driver -->
      <div class="bg-[#121418] border border-[#1E2229] rounded-xl p-5 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-[#1E2229]">
          <div class="flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-amber-400"></span>
            <h3 class="font-display font-bold text-sm text-white uppercase tracking-wider">
              Voucher Menunggu Penugasan Driver ({{ $pendingTrips->count() }})
            </h3>
          </div>
          <span class="text-[10px] text-stone-400">Pemberangkatan Segera</span>
        </div>

        @if($pendingTrips->isEmpty())
          <div class="py-8 text-center text-stone-500">
            <svg class="w-8 h-8 mx-auto mb-2 text-stone-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p>Tidak ada voucher ojek yang menunggu penugasan saat ini.</p>
            <p class="text-[10px] text-stone-600 mt-1">Voucher baru akan otomatis muncul saat pendaki check-in atau membeli tiket di Kasir POS.</p>
          </div>
        @else
          <div class="space-y-3">
            @foreach($pendingTrips as $trip)
              <div class="p-3.5 bg-[#16191F] border border-[#262B35] rounded-xl flex flex-col md:flex-row md:items-center justify-between gap-3">
                <div class="space-y-1">
                  <div class="flex items-center gap-2">
                    <span class="font-bold text-[#FF5722] text-sm">{{ $trip->trip_code }}</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $trip->status_badge['bg'] }}">
                      {{ $trip->status_badge['label'] }}
                    </span>
                  </div>
                  <div class="text-white font-bold">{{ $trip->passenger_name }}</div>
                  <div class="text-[10px] text-stone-400 flex items-center gap-3">
                    <span>Rute: {{ $trip->route }}</span>
                    <span>•</span>
                    <span>Tarif: Rp {{ number_format($trip->fare_amount, 0, ',', '.') }}</span>
                    @if($trip->simaksi)
                      <span>•</span>
                      <span class="text-cyan-400">Ref: {{ $trip->simaksi->registration_code }}</span>
                    @endif
                  </div>
                </div>

                <!-- Form Tugaskan Driver Standby Terdepan -->
                <form action="{{ route('management.ojek.assign', $trip->id) }}" method="POST" class="flex items-center gap-2 shrink-0">
                  @csrf
                  <select name="driver_id" id="driver_select_{{ $trip->id }}" required class="bg-[#121418] border border-[#2A303C] rounded-lg px-2.5 py-1.5 text-xs text-white">
                    <option value="">-- Pilih Driver Standby --</option>
                    @foreach($standbyDrivers as $idx => $driver)
                      <option value="{{ $driver->id }}">
                        #{{ $idx + 1 }} {{ $driver->registration_number }} — {{ $driver->name }} ({{ $driver->plate_number }})
                      </option>
                    @endforeach
                  </select>

                  <button type="submit" name="action" value="assign_and_print" class="px-3 py-1.5 bg-[#FF5722] hover:bg-[#ff6f43] text-black font-bold rounded-lg transition whitespace-nowrap flex items-center gap-1">
                    <span>Tugaskan & Cetak</span>
                  </button>

                  <button type="button" onclick="printTripWithSelectedDriver({{ $trip->id }})" title="Cetak Voucher Tiket" class="px-2.5 py-1.5 bg-[#16191F] hover:bg-[#202530] border border-[#262B35] rounded-lg text-stone-200 hover:text-white font-bold transition flex items-center gap-1.5 text-xs whitespace-nowrap">
                    <svg class="w-3.5 h-3.5 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span>Cetak</span>
                  </button>
                </form>
              </div>
            @endforeach
          </div>
        @endif
      </div>

      <!-- Box B: Trip Aktif Sedang Jalan -->
      <div class="bg-[#121418] border border-[#1E2229] rounded-xl p-5 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-[#1E2229]">
          <div class="flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-[#FF5722] animate-ping"></span>
            <h3 class="font-display font-bold text-sm text-white uppercase tracking-wider">
              Trip Sedang Berjalan di Jalur ({{ $activeTrips->count() }})
            </h3>
          </div>
          <span class="text-[10px] text-stone-400">Konfirmasi Tiba di Pos</span>
        </div>

        @if($activeTrips->isEmpty())
          <div class="py-6 text-center text-stone-500">
            Tidak ada armada yang sedang mengantar di jalur saat ini.
          </div>
        @else
          <div class="overflow-x-auto">
            <table class="w-full text-left font-mono-tech text-xs">
              <thead>
                <tr class="text-[10px] text-stone-400 uppercase border-b border-[#1E2229]">
                  <th class="pb-2">KODE TRIP</th>
                  <th class="pb-2">PENGEMUDI RESMI</th>
                  <th class="pb-2">PENUMPANG</th>
                  <th class="pb-2">JAM BERANGKAT</th>
                  <th class="pb-2 text-right">AKSI</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-[#1A1E26]">
                @foreach($activeTrips as $active)
                  <tr class="hover:bg-[#16191F]">
                    <td class="py-3 font-bold text-[#FF5722]">{{ $active->trip_code }}</td>
                    <td class="py-3 text-white">
                      <div class="font-bold flex items-center gap-1.5">
                        <span class="px-1.5 py-0.5 rounded bg-[#16191F] border border-[#262B35] text-[10px] text-amber-400">
                          {{ $active->driver?->registration_number }}
                        </span>
                        <span>{{ $active->driver?->name }}</span>
                      </div>
                      <div class="text-[10px] text-stone-400">Plat: {{ $active->driver?->plate_number }}</div>
                    </td>
                    <td class="py-3 text-stone-300">
                      <div>{{ $active->passenger_name }}</div>
                      <div class="text-[10px] text-stone-400">{{ $active->route }}</div>
                    </td>
                    <td class="py-3 text-stone-300">
                      {{ $active->dispatched_at ? $active->dispatched_at->format('H:i') : '-' }} WIB
                    </td>
                    <td class="py-3 text-right">
                      <div class="flex items-center justify-end gap-1.5">
                        <a href="{{ route('management.ojek.print_voucher', $active->id) }}" target="_blank" title="Cetak Ulang Voucher" class="px-2 py-1 bg-[#16191F] hover:bg-[#202530] border border-[#262B35] rounded text-stone-300 hover:text-white transition flex items-center gap-1 text-[10px]">
                          <span>Cetak</span>
                        </a>
                        <form action="{{ route('management.ojek.complete', $active->id) }}" method="POST" class="inline" onsubmit="return confirm('Konfirmasi trip {{ $active->trip_code }} telah selesai sampai tujuan?')">
                          @csrf
                          <button type="submit" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-500 text-white rounded text-[10px] font-bold uppercase transition">
                            ✓ Selesai Trip
                          </button>
                        </form>
                      </div>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @endif
      </div>

    </div>

    <!-- RIGHT COLUMN (1 COL): LIVE QUEUE ANTREAN DRIVER DI PANGKALAN -->
    <div class="space-y-6">
      
      <div class="bg-[#121418] border border-[#1E2229] rounded-xl p-5 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-[#1E2229]">
          <div class="flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
            <h3 class="font-display font-bold text-sm text-white uppercase tracking-wider">
              Giliran Antrean Pangkalan
            </h3>
          </div>
          <a href="{{ route('management.ojek.drivers') }}" class="text-[10px] text-[#FF5722] hover:underline">
            Kelola Driver →
          </a>
        </div>

        <p class="text-[10px] text-stone-400">
          Sistem giliran berurutan (FIFO). Driver yang baru kembali dari Pos 1 akan berada di urutan paling belakang.
        </p>

        <div class="space-y-2">
          @forelse($standbyDrivers as $index => $driver)
            <div class="p-2.5 rounded-lg bg-[#16191F] border {{ $index === 0 ? 'border-emerald-500/50 bg-emerald-950/20' : 'border-[#262B35]' }} flex items-center justify-between">
              <div class="flex items-center gap-2.5">
                <div class="w-6 h-6 rounded flex items-center justify-center font-bold text-xs {{ $index === 0 ? 'bg-emerald-500 text-black' : 'bg-[#121418] text-stone-400 border border-[#262B35]' }}">
                  {{ $index + 1 }}
                </div>
                <div>
                  <div class="flex items-center gap-1.5">
                    <span class="font-bold text-white">{{ $driver->name }}</span>
                    <span class="text-[10px] font-mono-tech text-amber-400">({{ $driver->registration_number }})</span>
                  </div>
                  <div class="text-[10px] text-stone-400 font-mono-tech">
                    Plat: <span class="text-stone-300 font-bold">{{ $driver->plate_number }}</span>
                  </div>
                </div>
              </div>

              @if($index === 0)
                <span class="px-1.5 py-0.5 rounded bg-emerald-500/20 text-emerald-400 text-[9px] font-bold border border-emerald-500/40">
                  GILIRAN 1
                </span>
              @endif
            </div>
          @empty
            <div class="py-6 text-center text-stone-500">
              Tidak ada driver yang standby di pangkalan saat ini.
            </div>
          @endforelse
        </div>

        @if($offDutyDrivers->isNotEmpty())
          <div class="pt-3 border-t border-[#1E2229]">
            <span class="text-[10px] text-stone-400 uppercase font-bold block mb-2">Driver Libur / Off Duty ({{ $offDutyDrivers->count() }}):</span>
            <div class="flex flex-wrap gap-1.5">
              @foreach($offDutyDrivers as $off)
                <span class="px-2 py-0.5 rounded bg-[#16191F] border border-[#262B35] text-[10px] text-stone-400">
                  {{ $off->registration_number }} - {{ $off->name }}
                </span>
              @endforeach
            </div>
          </div>
        @endif

      </div>

      <!-- Quick Action Card -->
      <div class="p-4 bg-[#14171E] border border-[#222731] rounded-xl space-y-3 font-mono-tech text-xs">
        <span class="text-stone-300 font-bold uppercase block">AKSES CEPAT LOGISTIK:</span>
        <div class="grid grid-cols-1 gap-2">
          <a href="{{ route('management.ojek.settlements') }}" class="px-3 py-2 rounded-lg bg-[#181B22] hover:bg-[#202530] border border-[#262B35] text-stone-200 hover:text-white flex items-center justify-between transition">
            <span>Rekonsiliasi & Pencairan Payout</span>
            <span class="text-emerald-400 font-bold">→</span>
          </a>
          <a href="{{ route('management.gate.index') }}" class="px-3 py-2 rounded-lg bg-[#181B22] hover:bg-[#202530] border border-[#262B35] text-stone-200 hover:text-white flex items-center justify-between transition">
            <span>Gate Check-In & Manifest</span>
            <span class="text-cyan-400 font-bold">→</span>
          </a>
          <a href="{{ route('management.pos.index') }}" class="px-3 py-2 rounded-lg bg-[#181B22] hover:bg-[#202530] border border-[#262B35] text-stone-200 hover:text-white flex items-center justify-between transition">
            <span>Kasir POS Loket Terpadu</span>
            <span class="text-[#FF5722] font-bold">→</span>
          </a>
        </div>
      </div>

    </div>

  </div>

</div>

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
@endsection
