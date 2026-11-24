{{-- Tab Reservasi: kalkulator & ringkasan biaya. Semua ID / onclick dipakai App.booking di app.js — jangan diubah. --}}
@php
  $rp = fn ($v) => 'Rp '.number_format((float) $v, 0, ',', '.');
  $minClimbers = $profile['min_climbers_per_group'] ?? 1;
  $maxClimbers = $profile['max_climbers_per_group'] ?? 15;
  $stepBtn = 'tap-active w-11 h-11 text-xl font-semibold text-ink hover:bg-hover flex items-center justify-center transition';
@endphp

<div id="sec-booking" class="hidden space-y-4 md:space-y-6">

  @include('portal.partials.tab-heading', ['eyebrow' => 'Reservasi', 'title' => 'Hitung & pesan SIMAKSI', 'text' => 'Estimasi langsung, bayar setelah mengisi data rombongan.'])

  <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 md:gap-6 items-start">

    {{-- ============ KIRI: FORM ============ --}}
    <div class="lg:col-span-7 space-y-3 md:space-y-4">

      {{-- 1. Tanggal & kuota --}}
      <section class="rounded-3xl bg-surface ring-1 ring-line p-5 md:p-6">
        <div class="flex items-center gap-3">
          <span class="w-8 h-8 rounded-full bg-brand/15 text-brand text-sm font-bold flex items-center justify-center">1</span>
          <h3 class="font-display font-semibold text-lg text-ink">Tanggal pendakian</h3>
        </div>
        <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-3 items-end">
          <div>
            <label for="bookDate" class="label">Tanggal naik</label>
            <input type="date" id="bookDate" value="{{ date('Y-m-d') }}" min="{{ date('Y-m-d') }}" class="field !h-12">
          </div>
          <div class="h-12 px-4 rounded-xl bg-raised/70 ring-1 ring-line flex items-center justify-between gap-3">
            <span class="flex items-center gap-2 text-xs font-medium text-muted">
              <span class="relative flex h-2 w-2"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-60"></span><span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-400"></span></span>
              Kuota hari itu
            </span>
            <span class="flex items-baseline gap-1">
              <span id="heroRemainingQuota" class="font-display font-bold text-base text-brand-soft tabular-nums">{{ $todayQuota->remaining_quota }} TERSISA</span>
              <span class="text-xs text-subtle">/ {{ $todayQuota->max_quota }}</span>
            </span>
          </div>
        </div>
      </section>

      {{-- 2. Jumlah pendaki --}}
      <section class="rounded-3xl bg-surface ring-1 ring-line p-5 md:p-6">
        <div class="flex items-center justify-between gap-3">
          <div class="flex items-center gap-3">
            <span class="w-8 h-8 rounded-full bg-brand/15 text-brand text-sm font-bold flex items-center justify-center">2</span>
            <h3 class="font-display font-semibold text-lg text-ink">Jumlah pendaki</h3>
          </div>
          <span id="climberCountLabel" class="text-sm font-semibold text-brand-soft">4 ORANG</span>
        </div>
        <div class="mt-4 flex items-center rounded-2xl bg-canvas ring-1 ring-line-strong overflow-hidden">
          <button type="button" onclick="App.booking.adjustClimbers(-1)" class="{{ $stepBtn }} !w-16 !h-16 text-2xl" aria-label="Kurangi pendaki">−</button>
          <div id="climberCountBox" class="flex-1 text-center font-display font-bold text-3xl text-ink tabular-nums">4</div>
          <button type="button" onclick="App.booking.adjustClimbers(1)" class="{{ $stepBtn }} !w-16 !h-16 text-2xl" aria-label="Tambah pendaki">+</button>
        </div>
        <p class="text-xs text-subtle mt-2">Solo ({{ $minClimbers }} orang) hingga rombongan maksimal {{ $maxClimbers }} orang.</p>
      </section>

      {{-- 3. Layanan --}}
      <section class="rounded-3xl bg-surface ring-1 ring-line p-5 md:p-6 space-y-3">
        <div class="flex items-center gap-3">
          <span class="w-8 h-8 rounded-full bg-brand/15 text-brand text-sm font-bold flex items-center justify-center">3</span>
          <div>
            <h3 class="font-display font-semibold text-lg text-ink leading-tight">Layanan tambahan</h3>
            <p class="text-xs text-subtle">Opsional — bisa ditambahkan nanti di basecamp.</p>
          </div>
        </div>

        {{-- Ojek --}}
        <div class="p-4 rounded-2xl bg-raised/60 ring-1 ring-line">
          <div class="flex items-start justify-between gap-3">
            <div class="flex items-start gap-3 min-w-0">
              <span class="w-10 h-10 shrink-0 rounded-xl bg-brand/10 ring-1 ring-brand/25 text-brand flex items-center justify-center"><x-ui.icon name="bolt" class="w-5 h-5" /></span>
              <div class="min-w-0">
                <p class="text-sm font-semibold text-ink">Shuttle ojek trail</p>
                <p class="text-xs text-muted">{{ $tariffs['ojek_route_name'] ?? 'Basecamp → Pos 1' }} · {{ $rp($tariffs['ojek_fee_per_trip'] ?? 30000) }} / armada</p>
                <p class="text-[11px] text-subtle mt-0.5">1 motor = 1 pendaki</p>
              </div>
            </div>
            <span id="bookOjekTotalLabel" class="text-sm font-semibold text-brand-soft tabular-nums shrink-0">Rp 0</span>
          </div>
          <div class="mt-3 flex justify-end">
            <div class="flex items-center rounded-xl bg-canvas ring-1 ring-line-strong overflow-hidden">
              <button type="button" onclick="App.booking.adjustOjek(-1)" class="{{ $stepBtn }}" aria-label="Kurangi ojek">−</button>
              <div id="bookOjekCountBox" class="w-12 text-center font-display font-bold text-lg text-ink tabular-nums">0</div>
              <button type="button" onclick="App.booking.adjustOjek(1)" class="{{ $stepBtn }}" aria-label="Tambah ojek">+</button>
            </div>
          </div>
        </div>

        {{-- Parkir --}}
        <div class="p-4 rounded-2xl bg-raised/60 ring-1 ring-line space-y-3">
          <label class="flex items-start justify-between gap-3 cursor-pointer select-none">
            <span class="flex items-start gap-3 min-w-0">
              <span class="w-10 h-10 shrink-0 rounded-xl bg-sky-500/10 ring-1 ring-sky-500/25 text-sky-300 flex items-center justify-center"><x-ui.icon name="gate" class="w-5 h-5" /></span>
              <span class="min-w-0">
                <span class="block text-sm font-semibold text-ink">Bawa kendaraan pribadi?</span>
                <span class="block text-xs text-muted">Titip parkir aman 24 jam di basecamp</span>
              </span>
            </span>
            <span class="flex items-center gap-3 shrink-0">
              <span id="bookParkingTotalLabel" class="text-sm font-semibold text-sky-300 tabular-nums">Rp 0</span>
              <input type="checkbox" id="bookHasVehicle" onchange="App.booking.toggleVehicle(this.checked)" class="w-6 h-6 rounded-md accent-orange-500 cursor-pointer">
            </span>
          </label>

          <div id="bookVehicleOptions" class="hidden pt-3 border-t border-line space-y-2">
            @foreach([
              ['Sepeda motor', $tariffs['parking_motor_fee'] ?? 10000, 'adjustMotorcycle', 'bookMotorCountBox'],
              ['Mobil pribadi', $tariffs['parking_car_fee'] ?? 25000, 'adjustCar', 'bookCarCountBox'],
            ] as [$vName, $vFee, $vFn, $vBox])
              <div class="flex items-center justify-between gap-3 p-3 rounded-xl bg-surface ring-1 ring-line">
                <div>
                  <p class="text-sm font-semibold text-ink">{{ $vName }}</p>
                  <p class="text-xs text-muted">{{ $rp($vFee) }} / unit</p>
                </div>
                <div class="flex items-center rounded-xl bg-canvas ring-1 ring-line-strong overflow-hidden">
                  <button type="button" onclick="App.booking.{{ $vFn }}(-1)" class="{{ $stepBtn }}" aria-label="Kurangi">−</button>
                  <div id="{{ $vBox }}" class="w-12 text-center font-display font-bold text-lg text-ink tabular-nums">0</div>
                  <button type="button" onclick="App.booking.{{ $vFn }}(1)" class="{{ $stepBtn }}" aria-label="Tambah">+</button>
                </div>
              </div>
            @endforeach
          </div>
        </div>

        {{-- Kebijakan deposit --}}
        @if($tariffs['trash_deposit_enabled'])
          <div class="flex items-center justify-between gap-3 px-4 py-3 rounded-xl bg-emerald-500/10 ring-1 ring-emerald-500/25 text-sm text-emerald-200">
            <span class="flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span> Deposit sampah (dikembalikan)</span>
            <strong>{{ $rp($tariffs['trash_deposit_amount']) }}</strong>
          </div>
        @else
          <div class="flex items-center justify-between gap-3 px-4 py-3 rounded-xl bg-raised/60 ring-1 ring-line text-sm text-muted">
            <span class="flex items-center gap-2"><x-ui.icon name="check" class="w-4 h-4 text-emerald-400" /> Deposit sampah</span>
            <strong class="text-ink">Bebas biaya</strong>
          </div>
        @endif
      </section>
    </div>

    {{-- ============ KANAN: RINGKASAN ============ --}}
    <div class="lg:col-span-5 lg:sticky lg:top-24 space-y-3 md:space-y-4">
      <section class="rounded-3xl bg-surface ring-1 ring-brand/25 p-5 md:p-6 shadow-xl shadow-black/20">
        <div class="flex items-center justify-between">
          <h3 class="font-display font-semibold text-lg text-ink">Ringkasan biaya</h3>
          <span class="text-[11px] font-semibold text-subtle uppercase tracking-wider">Estimasi</span>
        </div>

        <dl class="mt-4 space-y-3 text-sm">
          <div class="flex justify-between gap-3"><dt id="txtSimaksiSub" class="text-muted">SIMAKSI (4 Org):</dt><dd id="valSimaksiSub" class="text-ink font-semibold tabular-nums">Rp 100.000</dd></div>
          <div class="flex justify-between gap-3"><dt id="txtOjekSub" class="text-muted">Ojek (0 Armada):</dt><dd id="valOjekSub" class="text-ink font-semibold tabular-nums">Rp 0</dd></div>
          <div class="flex justify-between gap-3 hidden" id="rowParkirSub"><dt id="txtParkirSub" class="text-muted">Parkir Kendaraan:</dt><dd id="valParkirSub" class="text-sky-300 font-semibold tabular-nums">Rp 0</dd></div>
        </dl>

        <div class="mt-4 pt-4 border-t border-line flex items-end justify-between gap-3">
          <span class="text-sm font-semibold text-ink">Total estimasi</span>
          <span id="valTotalSub" class="font-display font-bold text-3xl text-brand-soft tabular-nums leading-none">Rp 100.000</span>
        </div>

        <button type="button" onclick="App.booking.openRegistrationModal()" class="tap-active mt-5 w-full h-14 rounded-2xl bg-brand hover:bg-brand-hover text-white text-base font-semibold flex items-center justify-center gap-2 shadow-xl shadow-brand/25">
          Daftar permit SIMAKSI <x-ui.icon name="arrow-right" class="w-5 h-5" />
        </button>
        <p class="text-xs text-subtle text-center mt-3">Setelah dibayar & diverifikasi, e-SIMAKSI dikirim ke WhatsApp ketua.</p>
      </section>

      <section class="rounded-2xl bg-surface ring-1 ring-line p-4 flex items-center justify-between gap-3">
        <div class="flex items-center gap-3 min-w-0">
          <span class="w-10 h-10 shrink-0 rounded-xl bg-raised ring-1 ring-line text-brand flex items-center justify-center"><x-ui.icon name="ticket" class="w-5 h-5" /></span>
          <div class="min-w-0">
            <p class="text-sm font-semibold text-ink">Sudah punya kode tiket?</p>
            <p class="text-xs text-muted">Lacak izin, unggah bukti bayar, atau perbaiki berkas.</p>
          </div>
        </div>
        <button type="button" onclick="App.modal.open('ticketModal')" class="tap-active h-10 px-4 rounded-xl bg-raised ring-1 ring-line-strong text-sm font-semibold text-ink hover:bg-hover shrink-0">Cek tiket</button>
      </section>

      <div class="grid grid-cols-3 gap-2 text-center">
        @foreach([
          ['Status jalur', $trailCondition ? ['open' => 'Dibuka', 'caution' => 'Waspada', 'closed' => 'Ditutup'][$trailCondition->status] ?? 'Dibuka' : 'Dibuka', 'text-emerald-300'],
          ['Air Pos 2', $trailCondition ? explode(' ', trim($trailCondition->pos2_water_status))[0] : 'Melimpah', 'text-sky-300'],
          ['Suhu dini hari', $trailCondition ? \Illuminate\Support\Str::before($trailCondition->night_temp_celsius, ' (') : '9°C', 'text-amber-300'],
        ] as [$lbl, $val, $col])
          <button type="button" onclick="App.nav.switchTab('sec-telemetry')" class="rounded-xl bg-surface ring-1 ring-line hover:ring-line-strong p-3 transition">
            <span class="block text-[11px] text-subtle">{{ $lbl }}</span>
            <span class="block text-sm font-semibold {{ $col }} mt-0.5 truncate">{{ $val }}</span>
          </button>
        @endforeach
      </div>
    </div>
  </div>
</div>
