{{-- Tab Layanan & tarif resmi --}}
@php $rp = fn ($v) => 'Rp '.number_format((float) $v, 0, ',', '.'); @endphp
<div id="sec-logistik" class="hidden space-y-4 md:space-y-6">

  @include('portal.partials.tab-heading', ['eyebrow' => 'Layanan basecamp', 'title' => 'Tarif resmi & layanan pendukung', 'text' => 'Transparan, tanpa biaya tersembunyi.'])

  <div class="grid grid-cols-1 md:grid-cols-3 gap-3 md:gap-4">
    {{-- Ojek --}}
    <div class="rounded-3xl bg-surface ring-1 ring-line p-5 md:p-6 flex flex-col">
      <span class="w-11 h-11 rounded-xl bg-brand/10 ring-1 ring-brand/25 text-brand flex items-center justify-center"><x-ui.icon name="bolt" class="w-5 h-5" /></span>
      <h3 class="font-display font-bold text-lg text-ink mt-4">Shuttle ojek trail</h3>
      <p class="text-xs text-muted mt-0.5">{{ $tariffs['ojek_route_name'] ?? 'Basecamp → Pos 1' }}</p>
      <p class="font-display font-bold text-2xl text-ink mt-4 tabular-nums">{{ $rp($tariffs['ojek_fee_per_trip'] ?? 30000) }} <span class="text-sm font-medium text-muted">/ perjalanan</span></p>
      <p class="text-sm text-muted mt-3 leading-relaxed">Memotong jalan makadam perkebunan teh ±2,1 km dan menghemat waktu tempuh hingga 45 menit sebelum trek hutan.</p>
      <p class="text-xs text-subtle mt-auto pt-4">Voucher dicetak kasir setelah rombongan lolos pemeriksaan gerbang.</p>
    </div>

    {{-- Porter --}}
    <div class="rounded-3xl bg-surface ring-1 ring-line p-5 md:p-6 flex flex-col">
      <span class="w-11 h-11 rounded-xl bg-emerald-500/10 ring-1 ring-emerald-500/25 text-emerald-300 flex items-center justify-center"><x-ui.icon name="users" class="w-5 h-5" /></span>
      <h3 class="font-display font-bold text-lg text-ink mt-4">Porter lokal</h3>
      <p class="text-xs text-muted mt-0.5">Maksimal 20 kg per porter</p>
      <p class="font-display font-bold text-2xl text-ink mt-4 tabular-nums">{{ $rp($tariffs['porter_fee_per_trip'] ?? 350000) }} <span class="text-sm font-medium text-muted">/ trip (2H1M)</span></p>
      <p class="text-sm text-muted mt-3 leading-relaxed">Warga lokal bersertifikasi dengan kemampuan navigasi gunung, membantu mengangkut tenda, air, dan peralatan masak ke area camp.</p>
    </div>

    {{-- Parkir --}}
    <div class="rounded-3xl bg-surface ring-1 ring-line p-5 md:p-6 flex flex-col">
      <span class="w-11 h-11 rounded-xl bg-sky-500/10 ring-1 ring-sky-500/25 text-sky-300 flex items-center justify-center"><x-ui.icon name="gate" class="w-5 h-5" /></span>
      <h3 class="font-display font-bold text-lg text-ink mt-4">Parkir 24 jam</h3>
      <p class="text-xs text-muted mt-0.5">Area beratap & pengawasan CCTV</p>
      <dl class="mt-4 space-y-1.5">
        <div class="flex items-baseline justify-between"><dt class="text-sm text-muted">Motor</dt><dd class="font-display font-bold text-xl text-ink tabular-nums">{{ $rp($tariffs['parking_motor_fee'] ?? 10000) }}</dd></div>
        <div class="flex items-baseline justify-between"><dt class="text-sm text-muted">Mobil</dt><dd class="font-display font-bold text-xl text-ink tabular-nums">{{ $rp($tariffs['parking_car_fee'] ?? 25000) }}</dd></div>
      </dl>
      <p class="text-sm text-muted mt-3 leading-relaxed">Pos jaga ranger 24 jam, dengan penitipan helm dan jaket gratis.</p>
    </div>
  </div>

  <div class="rounded-3xl bg-gradient-to-br from-brand/15 via-surface to-surface ring-1 ring-brand/25 p-5 md:p-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div>
      <h3 class="font-display font-bold text-xl md:text-2xl text-ink">Pesan SIMAKSI sekaligus layanan</h3>
      <p class="text-sm text-muted mt-1">Tambahkan ojek atau parkir langsung saat reservasi — satu kali bayar.</p>
    </div>
    <button type="button" onclick="App.nav.switchTab('sec-booking')" class="tap-active h-14 px-7 rounded-2xl bg-brand hover:bg-brand-hover text-white font-semibold flex items-center justify-center gap-2 shrink-0 shadow-lg shadow-brand/25">
      Buat reservasi <x-ui.icon name="arrow-right" class="w-5 h-5" />
    </button>
  </div>
</div>
