{{-- Beranda / landing page portal publik --}}
@php
  $trailCfg = $trailConfig ?? \App\Models\AppSetting::getTrailConfig();
  $trailRouteList = $trailRoute ?? \App\Models\AppSetting::getTrailRoute();
  $trailLast = collect($trailRouteList)->last();
  $checklist = $checklist ?? \App\Support\InspectionTemplate::get();
  $facilities = $facilities ?? \App\Models\AppSetting::getBasecampFacilities();
  $rp = fn ($v) => 'Rp '.number_format((float) $v, 0, ',', '.');
  $status = $trailCondition->status ?? 'open';
  $statusMeta = [
    'open' => ['Jalur dibuka', 'bg-emerald-400', 'text-emerald-200'],
    'caution' => ['Jalur waspada', 'bg-amber-400', 'text-amber-200'],
    'closed' => ['Jalur ditutup', 'bg-rose-400', 'text-rose-200'],
  ][$status] ?? ['Jalur dibuka', 'bg-emerald-400', 'text-emerald-200'];
  $remaining = $remainingQuota ?? (int) ($todayQuota->remaining_quota ?? 0);
  $maxQuota = $maxQuota ?? (int) ($todayQuota->max_quota ?? ($profile['default_daily_quota'] ?? 150));
  $quotaPct = $maxQuota > 0 ? round((($maxQuota - $remaining) / $maxQuota) * 100) : 0;
  $mountain = $profile['mountain_name'] ?? 'Gunung Cikuray';
  $basecamp = $profile['basecamp_name'] ?? 'Basecamp SAMSARA';
@endphp

<div id="sec-beranda" class="space-y-6 md:space-y-10">

  {{-- ================= HERO ================= --}}
  <section class="relative overflow-hidden rounded-3xl ring-1 ring-line bg-surface">
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,rgba(255,87,34,.22),transparent_55%)]"></div>
    <svg class="absolute inset-x-0 bottom-0 w-full h-[55%] md:h-[65%] opacity-70 pointer-events-none" viewBox="0 0 1200 400" preserveAspectRatio="none" aria-hidden="true">
      <defs>
        <linearGradient id="mtnBack" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#2F3641"/><stop offset="1" stop-color="#181C22"/></linearGradient>
        <linearGradient id="mtnFront" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#1F242C"/><stop offset="1" stop-color="#12151A"/></linearGradient>
      </defs>
      <path fill="url(#mtnBack)" opacity=".7" d="M0 400 L0 300 L180 230 L320 270 L470 170 L600 230 L760 150 L900 220 L1060 190 L1200 240 L1200 400 Z"/>
      <path fill="url(#mtnFront)" d="M0 400 L0 340 L260 300 L520 120 L600 60 L680 120 L940 300 L1200 330 L1200 400 Z"/>
      <path fill="#ECEFF3" opacity=".85" d="M560 90 L600 60 L640 90 L622 98 L608 86 L596 100 L580 92 Z"/>
    </svg>

    <div class="relative p-5 sm:p-7 lg:p-9 grid grid-cols-1 lg:grid-cols-12 gap-5 lg:gap-8 items-end">
      <div class="lg:col-span-7">
        <div class="flex flex-wrap gap-2 mb-4">
          <span class="inline-flex items-center gap-2 h-7 px-2.5 rounded-full bg-canvas/75 ring-1 ring-line text-xs font-semibold {{ $statusMeta[2] }}">
            <span class="relative flex w-2 h-2"><span class="absolute inline-flex w-full h-full rounded-full {{ $statusMeta[1] }} opacity-60 animate-ping"></span><span class="relative inline-flex w-2 h-2 rounded-full {{ $statusMeta[1] }}"></span></span>
            {{ $statusMeta[0] }}
          </span>
          <span class="inline-flex items-center gap-1.5 h-7 px-2.5 rounded-full bg-canvas/75 ring-1 ring-line text-xs text-muted">
            Kuota hari ini <strong class="text-ink tabular-nums">{{ $remaining }}</strong>/{{ $maxQuota }}
          </span>
          @if($trailCondition?->night_temp_celsius)
            <span class="hidden sm:inline-flex items-center gap-1.5 h-7 px-2.5 rounded-full bg-canvas/75 ring-1 ring-line text-xs text-muted">Suhu malam <strong class="text-ink">{{ $trailCondition->night_temp_celsius }}</strong></span>
          @endif
        </div>

        <h1 class="font-display font-bold text-[28px] leading-[1.12] sm:text-4xl lg:text-[44px] text-ink tracking-tight">
          Mendaki {{ $mountain }},<br><span class="text-brand">tertib dari gerbang</span> sampai puncak.
        </h1>
        <p class="mt-3 text-sm sm:text-base text-muted max-w-xl leading-relaxed">
          Pesan <strong class="text-ink font-semibold">SIMAKSI online {{ $mountain }}</strong> resmi via {{ $basecamp }}. E-tiket dikirim ke WhatsApp, rombongan diperiksa di gerbang, dan setiap pos dipantau ranger.
        </p>

        <div class="mt-5 flex flex-col sm:flex-row gap-2.5 sm:gap-3">
          <button type="button" onclick="App.nav.switchTab('sec-booking')" class="tap-active h-12 px-6 rounded-xl bg-brand hover:bg-brand-hover text-white text-sm sm:text-base font-semibold flex items-center justify-center gap-2 shadow-lg shadow-brand/25">
            Pesan SIMAKSI <x-ui.icon name="arrow-right" class="w-4 h-4 sm:w-5 sm:h-5" />
          </button>
          <button type="button" onclick="App.modal.open('ticketModal')" class="tap-active h-12 px-5 rounded-xl bg-canvas/80 ring-1 ring-line-strong hover:bg-hover text-ink text-sm sm:text-base font-semibold flex items-center justify-center gap-2">
            <x-ui.icon name="ticket" class="w-4 h-4 sm:w-5 sm:h-5 text-brand" /> Cek status tiket
          </button>
        </div>
      </div>

      {{-- Kartu kuota (menyatu di grid, tidak melayang menutupi ruang kosong) --}}
      <div class="lg:col-span-5 rounded-2xl bg-canvas/85 backdrop-blur ring-1 ring-line p-4 sm:p-5">
        <div class="flex items-center justify-between gap-2">
          <p class="text-xs font-semibold text-muted">Kuota pendakian hari ini</p>
          <time datetime="{{ now()->toDateString() }}" class="text-[11px] text-subtle">{{ now()->translatedFormat('d M Y') }}</time>
        </div>
        <p class="mt-1.5 font-display font-bold text-2xl sm:text-3xl text-ink tabular-nums">{{ $remaining }} <span class="text-sm font-medium text-muted">slot tersisa</span></p>
        <div class="mt-2.5 h-2 rounded-full bg-raised overflow-hidden"><div class="h-full rounded-full {{ $remaining < 30 ? 'bg-amber-400' : 'bg-brand' }}" style="width: {{ $quotaPct }}%"></div></div>
        <div class="mt-3 pt-3 border-t border-line flex items-center justify-between gap-2">
          <span class="text-[11px] text-subtle">Maks. {{ $maxQuota }} pendaki/hari</span>
          <button type="button" onclick="App.nav.switchTab('sec-booking')" class="text-xs sm:text-sm font-semibold text-brand-soft hover:text-brand flex items-center gap-1">Cek tanggal lain <x-ui.icon name="chevron-right" class="w-4 h-4" /></button>
        </div>
      </div>
    </div>
  </section>

  {{-- ================= ANGKA KUNCI ================= --}}
  <section class="grid grid-cols-2 md:grid-cols-4 gap-3">
    @foreach([
      [$profile['mountain_elevation_num'] ?? '2.821', 'mdpl', 'Ketinggian puncak'],
      [$maxQuota, 'orang/hari', 'Kuota konservasi'],
      [number_format($trailLast['cum']['dist_m'] / 1000, 1, ',', '.'), 'km', 'Panjang jalur'],
      ['±'.round($trailLast['cum']['up_min'] / 60, 1), 'jam', 'Basecamp → puncak'],
    ] as [$val, $unit, $label])
      <div class="rounded-2xl bg-surface ring-1 ring-line p-4">
        <p class="font-display font-bold text-2xl md:text-3xl text-ink tabular-nums">{{ is_numeric($val) && $val >= 1000 ? number_format($val, 0, ',', '.') : $val }} <span class="text-sm font-medium text-muted">{{ $unit }}</span></p>
        <p class="text-xs text-muted mt-1">{{ $label }}</p>
      </div>
    @endforeach
  </section>

  {{-- ================= ALUR PENDAKIAN ================= --}}
  <section>
    <div class="flex items-end justify-between gap-4 mb-4">
      <div>
        <p class="text-xs font-semibold uppercase tracking-wider text-brand">Alur pendakian</p>
        <h2 class="font-display font-bold text-2xl md:text-3xl text-ink mt-1">Dari daftar sampai turun, 5 langkah</h2>
      </div>
      <button type="button" onclick="App.nav.switchTab('sec-booking')" class="hidden sm:flex text-sm font-semibold text-brand-soft hover:text-brand items-center gap-1">Mulai daftar <x-ui.icon name="chevron-right" class="w-4 h-4" /></button>
    </div>
    <ol class="grid grid-cols-1 md:grid-cols-5 gap-3">
      @foreach($steps as $i => $step)
        <li class="relative rounded-2xl bg-surface ring-1 ring-line p-4 md:p-5 flex md:flex-col gap-4 md:gap-3">
          <div class="flex md:items-center justify-between shrink-0">
            <span class="w-11 h-11 rounded-xl bg-brand/10 ring-1 ring-brand/25 text-brand flex items-center justify-center"><x-ui.icon :name="$step['icon']" class="w-5 h-5" /></span>
            <span class="hidden md:block font-display font-bold text-3xl text-line-strong">{{ $i + 1 }}</span>
          </div>
          <div>
            <p class="text-[11px] font-semibold text-subtle md:hidden">Langkah {{ $i + 1 }}</p>
            <h3 class="font-semibold text-ink leading-snug">{{ $step['title'] }}</h3>
            <p class="text-sm text-muted mt-1 leading-relaxed">{{ $step['text'] }}</p>
          </div>
        </li>
      @endforeach
    </ol>
  </section>

  {{-- ================= PERSIAPAN: ALAT & SAMPAH ================= --}}
  <section class="grid grid-cols-1 lg:grid-cols-2 gap-3">
    <div class="rounded-3xl bg-surface ring-1 ring-line p-5 md:p-7">
      <span class="w-11 h-11 rounded-xl bg-emerald-500/10 ring-1 ring-emerald-500/25 text-emerald-300 flex items-center justify-center"><x-ui.icon name="checklist" class="w-5 h-5" /></span>
      <h2 class="font-display font-bold text-xl md:text-2xl text-ink mt-4">Alat wajib yang dicek di gerbang</h2>
      <p class="text-sm text-muted mt-1">Siapkan sebelum berangkat agar pemeriksaan cepat.</p>
      <ul class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-2">
        @foreach($checklist['gear'] as $gear)
          <li class="flex items-center gap-2.5 p-3 rounded-xl bg-raised/60 ring-1 ring-line text-sm text-ink">
            <x-ui.icon name="check" class="w-4 h-4 text-emerald-400 shrink-0" /> {{ $gear }}
          </li>
        @endforeach
      </ul>
    </div>
    <div class="rounded-3xl bg-gradient-to-br from-brand/15 via-surface to-surface ring-1 ring-brand/25 p-5 md:p-7">
      <span class="w-11 h-11 rounded-xl bg-brand/15 ring-1 ring-brand/30 text-brand flex items-center justify-center"><x-ui.icon name="trash" class="w-5 h-5" /></span>
      <h2 class="font-display font-bold text-xl md:text-2xl text-ink mt-4">Bawa naik, bawa turun</h2>
      <p class="text-sm text-muted mt-1">Barang di bawah ini <strong class="text-ink">dihitung saat naik</strong> dan <strong class="text-ink">dicocokkan lagi saat turun</strong>. Kemasan kosong tetap wajib dibawa kembali.</p>
      <ul class="mt-4 space-y-2">
        @foreach($checklist['logistic'] as $item)
          <li class="flex items-center justify-between gap-3 p-3 rounded-xl bg-canvas/50 ring-1 ring-line text-sm">
            <span class="text-ink">{{ $item['name'] }}</span>
            <span class="text-xs text-muted shrink-0">dihitung per {{ $item['unit'] }}</span>
          </li>
        @endforeach
      </ul>
    </div>
  </section>

  {{-- ================= RUTE & PETA ================= --}}
  <section class="rounded-3xl bg-surface ring-1 ring-line p-4 md:p-7">
    <div class="flex items-end justify-between gap-4 mb-4">
      <div>
        <p class="text-xs font-semibold uppercase tracking-wider text-brand">Peta jalur</p>
        <h2 class="font-display font-bold text-2xl md:text-3xl text-ink mt-1">Pos & ketinggian</h2>
        <p class="text-sm text-muted mt-1">Pilih segmen untuk melihat jarak, waktu tempuh, kemiringan, dan fasilitas. Atur jam mulai untuk perkiraan tiba.</p>
      </div>
      <button type="button" onclick="App.nav.switchTab('sec-waypoints')" class="hidden sm:flex text-sm font-semibold text-brand-soft hover:text-brand items-center gap-1 shrink-0">Detail rute <x-ui.icon name="chevron-right" class="w-4 h-4" /></button>
    </div>

    @include('portal.partials.trail-map', ['mapId' => 'trailMapHome'])

    <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-2">
      <div class="flex gap-3 p-3.5 rounded-2xl bg-emerald-500/10 ring-1 ring-emerald-500/25">
        <x-ui.icon name="refresh" class="w-5 h-5 text-emerald-300 shrink-0 mt-0.5" />
        <p class="text-sm text-muted"><strong class="text-ink">Pos 1–3:</strong> air melimpah, toilet, mushola, dan shelter.</p>
      </div>
      <div class="flex gap-3 p-3.5 rounded-2xl bg-sky-500/10 ring-1 ring-sky-500/25">
        <x-ui.icon name="home" class="w-5 h-5 text-sky-300 shrink-0 mt-0.5" />
        <p class="text-sm text-muted"><strong class="text-ink">Camp:</strong> Pos 2 atau Pos 3, disarankan Pos 3 karena paling dekat ke puncak. <strong class="text-rose-300">Puncak dilarang camp.</strong></p>
      </div>
    </div>

    {{-- Ringkasan terstruktur pos jalur & jam operasional agar langsung terbaca mesin pencari & ringkasan AI --}}
    <div class="mt-4 pt-4 border-t border-line space-y-3">
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
        <div class="p-3 rounded-xl bg-raised/60 ring-1 ring-line flex items-center justify-between gap-2">
          <span class="text-muted">Check-in <strong class="text-ink">Tektok (1 Hari)</strong></span>
          <span class="font-semibold text-ink tabular-nums">{{ $trailCfg['tektok_checkin_start'] }}–{{ $trailCfg['tektok_checkin_end'] }} WIB (maks. {{ $trailCfg['tektok_max_checkout_hour'] }}:00)</span>
        </div>
        <div class="p-3 rounded-xl bg-raised/60 ring-1 ring-line flex items-center justify-between gap-2">
          <span class="text-muted">Check-in <strong class="text-ink">Camp (Bermalam)</strong></span>
          <span class="font-semibold text-ink tabular-nums">{{ $trailCfg['camp_checkin_start'] }}–{{ $trailCfg['camp_checkin_end'] }} WIB</span>
        </div>
      </div>

      {{-- Mobile: kartu pos tanpa scroll horizontal --}}
      <div class="md:hidden space-y-2">
        @foreach($trailRouteList as $pt)
          @continue($pt['type'] === 'landmark')
          <div class="p-3 rounded-2xl bg-canvas/50 ring-1 ring-line space-y-1">
            <div class="flex items-center justify-between gap-2">
              <span class="font-semibold text-sm text-ink">{{ $pt['code'] }} · {{ $pt['name'] }}</span>
              <span class="text-xs font-bold text-brand-soft tabular-nums shrink-0">{{ number_format($pt['elev'], 0, ',', '.') }} MDPL</span>
            </div>
            <p class="text-xs text-muted">
              Jarak {{ ($pt['cum']['dist_m'] ?? 0) === 0 ? '0 m (Start)' : number_format($pt['cum']['dist_m'] / 1000, 1, ',', '.').' km' }}
              ·
              @if(!empty($pt['camp_recommended']))
                <span class="text-emerald-300 font-semibold">Camp disarankan</span> ·
              @elseif(!empty($pt['camp']))
                <span class="text-sky-300 font-semibold">Camp resmi</span> ·
              @elseif(!empty($pt['no_camp']))
                <span class="text-rose-300 font-semibold">Dilarang camp</span> ·
              @endif
              {{ count($pt['facilities'] ?? []) ? implode(', ', $pt['facilities']) : ($pt['type'] === 'pos' ? 'Pos istirahat (tanpa sumber air)' : '') }}
            </p>
          </div>
        @endforeach
      </div>

      <div class="hidden md:block overflow-x-auto rounded-2xl ring-1 ring-line bg-canvas/40">
        <table class="w-full text-left text-xs">
          <caption class="sr-only">Daftar pos jalur pendakian {{ $mountain }} via {{ $basecamp }} beserta elevasi, jarak, dan fasilitas</caption>
          <thead class="bg-raised/70 text-muted border-b border-line">
            <tr>
              <th scope="col" class="px-3.5 py-2.5 font-semibold">Pos / Titik</th>
              <th scope="col" class="px-3.5 py-2.5 font-semibold">Ketinggian</th>
              <th scope="col" class="px-3.5 py-2.5 font-semibold">Jarak Total</th>
              <th scope="col" class="px-3.5 py-2.5 font-semibold">Camp &amp; Fasilitas</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-line">
            @foreach($trailRouteList as $pt)
              @continue($pt['type'] === 'landmark')
              <tr>
                <td class="px-3.5 py-2.5 font-semibold text-ink whitespace-nowrap">{{ $pt['code'] }} · {{ $pt['name'] }}</td>
                <td class="px-3.5 py-2.5 text-muted tabular-nums whitespace-nowrap">{{ number_format($pt['elev'], 0, ',', '.') }} MDPL</td>
                <td class="px-3.5 py-2.5 text-muted tabular-nums whitespace-nowrap">{{ ($pt['cum']['dist_m'] ?? 0) === 0 ? '0 m (Start)' : number_format($pt['cum']['dist_m'] / 1000, 1, ',', '.').' km' }}</td>
                <td class="px-3.5 py-2.5 text-muted">
                  @if(!empty($pt['camp_recommended']))
                    <span class="text-emerald-300 font-semibold">Camp disarankan</span> ·
                  @elseif(!empty($pt['camp']))
                    <span class="text-sky-300 font-semibold">Camp resmi</span> ·
                  @elseif(!empty($pt['no_camp']))
                    <span class="text-rose-300 font-semibold">Dilarang camp</span>
                  @endif
                  {{ count($pt['facilities'] ?? []) ? implode(', ', $pt['facilities']) : ($pt['type'] === 'pos' ? 'Pos istirahat (tanpa sumber air)' : '') }}
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
    <button type="button" onclick="App.nav.switchTab('sec-waypoints')" class="sm:hidden mt-3 w-full h-11 rounded-xl bg-raised ring-1 ring-line text-sm font-semibold text-ink flex items-center justify-center gap-1">Detail semua pos <x-ui.icon name="chevron-right" class="w-4 h-4" /></button>
  </section>

  {{-- ================= KENAPA SAMSARA ================= --}}
  <section>
    <p class="text-xs font-semibold uppercase tracking-wider text-brand">Kenapa jalur Samsara</p>
    <h2 class="font-display font-bold text-2xl md:text-3xl text-ink mt-1 mb-4">Pemandangan terbaik, layanan lengkap</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
      @foreach([
        ['mountain', 'Lautan awan & bayangan segitiga', 'Puncak kerucut Cikuray memproyeksikan bayangan segitiga raksasa di atas samudera awan saat matahari terbit.'],
        ['pin', 'Air melimpah di Pos 1–3', 'Koridor hutan yang teduh dengan air, toilet, mushola, dan shelter di tiga pos pertama. Pos 3 jadi titik isi ulang terakhir.'],
        ['bolt', 'Ojek perkebunan warga & SAR 24 jam', 'Hemat ±2 jam lewat ojek resmi ke Pos 1, dengan posko siaga dan tim SAR yang siaga sepanjang hari.'],
      ] as [$icon, $title, $text])
        <div class="rounded-2xl bg-surface ring-1 ring-line p-5">
          <span class="w-11 h-11 rounded-xl bg-raised ring-1 ring-line text-brand flex items-center justify-center"><x-ui.icon :name="$icon" class="w-5 h-5" /></span>
          <h3 class="font-semibold text-ink mt-4">{{ $title }}</h3>
          <p class="text-sm text-muted mt-1 leading-relaxed">{{ $text }}</p>
        </div>
      @endforeach
    </div>
  </section>

  {{-- ================= TARIF & FASILITAS ================= --}}
  <section class="grid grid-cols-1 lg:grid-cols-5 gap-3">
    <div class="lg:col-span-2 rounded-3xl bg-surface ring-1 ring-line p-5 md:p-7">
      <p class="text-xs font-semibold uppercase tracking-wider text-brand">Tarif resmi</p>
      <h2 class="font-display font-bold text-xl md:text-2xl text-ink mt-1">Transparan, tanpa biaya tersembunyi</h2>
      <dl class="mt-5 divide-y divide-line">
        @foreach([
          ['SIMAKSI', 'per orang', $tariffs['simaksi_fee_per_person'] ?? 25000],
          ['Ojek '.($tariffs['ojek_route_name'] ?? 'Basecamp → Pos 1'), 'per perjalanan', $tariffs['ojek_fee_per_trip'] ?? 30000],
          ['Parkir motor', 'per kendaraan', $tariffs['parking_motor_fee'] ?? 10000],
          ['Parkir mobil', 'per kendaraan', $tariffs['parking_car_fee'] ?? 25000],
        ] as [$name, $per, $price])
          <div class="flex items-center justify-between gap-3 py-3">
            <dt><span class="block text-sm text-ink">{{ $name }}</span><span class="block text-xs text-subtle">{{ $per }}</span></dt>
            <dd class="font-display font-bold text-ink tabular-nums">{{ $rp($price) }}</dd>
          </div>
        @endforeach
      </dl>
      <button type="button" onclick="App.nav.switchTab('sec-logistik')" class="mt-2 text-sm font-semibold text-brand-soft hover:text-brand flex items-center gap-1">Layanan porter & sewa alat <x-ui.icon name="chevron-right" class="w-4 h-4" /></button>
    </div>
    <div class="lg:col-span-3 rounded-3xl bg-surface ring-1 ring-line p-5 md:p-7">
      <p class="text-xs font-semibold uppercase tracking-wider text-brand">Fasilitas basecamp</p>
      <h2 class="font-display font-bold text-xl md:text-2xl text-ink mt-1">Lengkap, siap melayani 24 jam</h2>
      <div class="mt-5 grid grid-cols-2 sm:grid-cols-3 gap-2">
        @foreach($facilities as [$icon, $title, $text])
          <div class="p-3.5 rounded-2xl bg-raised/60 ring-1 ring-line">
            <x-ui.icon :name="$icon" class="w-5 h-5 text-brand" />
            <p class="text-sm font-semibold text-ink mt-2.5">{{ $title }}</p>
            <p class="text-xs text-muted">{{ $text }}</p>
          </div>
        @endforeach
      </div>
    </div>
  </section>

  @include('portal.partials.location')

  {{-- ================= FAQ ================= --}}
  <section id="faq" aria-labelledby="faqHeading">
    <p class="text-xs font-semibold uppercase tracking-wider text-brand">Pertanyaan umum</p>
    <h2 id="faqHeading" class="font-display font-bold text-2xl md:text-3xl text-ink mt-1 mb-4">Yang sering ditanyakan</h2>
    <div class="space-y-2">
      @foreach($faqs as $faq)
        <details class="group rounded-2xl bg-surface ring-1 ring-line open:ring-line-strong">
          <summary class="flex items-center justify-between gap-4 px-5 py-4 cursor-pointer">
            <span class="font-semibold text-ink">{{ $faq['q'] }}</span>
            <x-ui.icon name="plus" class="w-5 h-5 text-muted shrink-0 transition group-open:rotate-45" />
          </summary>
          <p class="px-5 pb-5 -mt-1 text-sm text-muted leading-relaxed">{{ $faq['a'] }}</p>
        </details>
      @endforeach
    </div>
  </section>

  {{-- ================= CTA ================= --}}
  <section class="relative overflow-hidden rounded-3xl bg-brand p-6 md:p-10">
    <svg class="absolute right-0 bottom-0 w-2/3 md:w-1/2 h-full text-black/10" viewBox="0 0 400 200" preserveAspectRatio="xMaxYMax meet" aria-hidden="true"><path fill="currentColor" d="M0 200 L120 90 L170 120 L250 30 L330 110 L400 70 L400 200 Z"/></svg>
    <div class="relative max-w-xl">
      <h2 class="font-display font-bold text-2xl md:text-4xl text-white leading-tight">Siap menapaki atap Garut?</h2>
      <p class="text-white/85 mt-2">Amankan slot rombongan Anda sekarang — kuota dibatasi {{ $maxQuota }} pendaki per hari.</p>
      <button type="button" onclick="App.nav.switchTab('sec-booking')" class="tap-active mt-6 h-14 px-7 rounded-2xl bg-white text-black text-base font-semibold inline-flex items-center gap-2 hover:bg-white/90">
        Pesan SIMAKSI <x-ui.icon name="arrow-right" class="w-5 h-5" />
      </button>
    </div>
  </section>
</div>
