{{-- Beranda / landing page portal publik --}}
@php
  $trailCfg = \App\Models\AppSetting::getTrailConfig();
  $checklist = \App\Support\InspectionTemplate::get();
  $rp = fn ($v) => 'Rp '.number_format((float) $v, 0, ',', '.');
  $status = $trailCondition->status ?? 'open';
  $statusMeta = [
    'open' => ['Jalur dibuka', 'bg-emerald-400', 'text-emerald-200'],
    'caution' => ['Jalur waspada', 'bg-amber-400', 'text-amber-200'],
    'closed' => ['Jalur ditutup', 'bg-rose-400', 'text-rose-200'],
  ][$status] ?? ['Jalur dibuka', 'bg-emerald-400', 'text-emerald-200'];
  $remaining = (int) ($todayQuota->remaining_quota ?? 0);
  $maxQuota = (int) ($todayQuota->max_quota ?? ($profile['default_daily_quota'] ?? 150));
  $quotaPct = $maxQuota > 0 ? round((($maxQuota - $remaining) / $maxQuota) * 100) : 0;
  $mountain = $profile['mountain_name'] ?? 'Gunung Cikuray';
  $basecamp = $profile['basecamp_name'] ?? 'Basecamp SAMSARA';

  $route = array_merge(
    [['name' => 'Basecamp', 'elevation' => $profile['basecamp_elevation'] ?? '1.450 MDPL', 'note' => 'Registrasi, gate & parkir']],
    collect($trailCfg['checkpoints'])->map(fn ($p, $k) => ['name' => str_replace('_', ' ', ucwords(strtolower($k), '_')), 'elevation' => $p['elevation'], 'note' => preg_replace('/^[^(]*\(|\)$/', '', $p['name'])])->values()->all()
  );
  $route[count($route) - 1]['name'] = 'Puncak';
  $elevNums = collect($route)->map(fn ($r) => (int) preg_replace('/\D/', '', $r['elevation']));
  $minE = $elevNums->min(); $maxE = max($elevNums->max(), $minE + 1);

  $steps = [
    ['icon' => 'ticket', 'title' => 'Daftar & bayar online', 'text' => 'Pilih tanggal, isi data rombongan dan KTP ketua, lalu bayar via transfer, QRIS, atau tunai di basecamp.'],
    ['icon' => 'whatsapp', 'title' => 'E-SIMAKSI dikirim ke WhatsApp', 'text' => 'Setelah kasir memverifikasi lunas, info pendakian lengkap dan tautan e-SIMAKSI langsung masuk ke WhatsApp ketua.'],
    ['icon' => 'checklist', 'title' => 'Pemeriksaan di gerbang', 'text' => 'Gatekeeper mengecek anggota, alat wajib, memotret tim, dan menghitung logistik yang berpotensi jadi sampah.'],
    ['icon' => 'printer', 'title' => 'Ambil surat jalan, mulai mendaki', 'text' => 'Kasir mencetak surat jalan'.((float) ($tariffs['ojek_fee_per_trip'] ?? 0) > 0 ? ' dan voucher ojek' : '').'. Ranger mencatat rombongan di setiap pos.'],
    ['icon' => 'arrow-down', 'title' => 'Turun & hitung ulang', 'text' => 'Logistik dicocokkan lagi di gerbang. Semua sampah kembali, lalu kasir menerbitkan izin keluar parkir.'],
  ];
@endphp

<div id="sec-beranda" class="space-y-6 md:space-y-10">

  {{-- ================= HERO ================= --}}
  <section class="relative overflow-hidden rounded-3xl ring-1 ring-line bg-surface">
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,rgba(255,87,34,.25),transparent_55%)]"></div>
    <svg class="absolute inset-x-0 bottom-0 w-full h-[55%] md:h-[70%]" viewBox="0 0 1200 400" preserveAspectRatio="none" aria-hidden="true">
      <defs>
        <linearGradient id="mtnBack" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#2F3641"/><stop offset="1" stop-color="#181C22"/></linearGradient>
        <linearGradient id="mtnFront" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#1F242C"/><stop offset="1" stop-color="#12151A"/></linearGradient>
      </defs>
      <path fill="url(#mtnBack)" opacity=".7" d="M0 400 L0 300 L180 230 L320 270 L470 170 L600 230 L760 150 L900 220 L1060 190 L1200 240 L1200 400 Z"/>
      <path fill="url(#mtnFront)" d="M0 400 L0 340 L260 300 L520 120 L600 60 L680 120 L940 300 L1200 330 L1200 400 Z"/>
      <path fill="#ECEFF3" opacity=".9" d="M560 90 L600 60 L640 90 L622 98 L608 86 L596 100 L580 92 Z"/>
    </svg>

    <div class="relative px-5 pt-8 pb-44 sm:px-8 md:px-12 md:pt-14 md:pb-56 max-w-3xl">
      <div class="flex flex-wrap gap-2 mb-5">
        <span class="inline-flex items-center gap-2 h-8 px-3 rounded-full bg-canvas/70 ring-1 ring-line text-xs font-semibold {{ $statusMeta[2] }}">
          <span class="relative flex w-2 h-2"><span class="absolute inline-flex w-full h-full rounded-full {{ $statusMeta[1] }} opacity-60 animate-ping"></span><span class="relative inline-flex w-2 h-2 rounded-full {{ $statusMeta[1] }}"></span></span>
          {{ $statusMeta[0] }}
        </span>
        <span class="inline-flex items-center gap-1.5 h-8 px-3 rounded-full bg-canvas/70 ring-1 ring-line text-xs text-muted">
          Kuota hari ini <strong class="text-ink tabular-nums">{{ $remaining }}</strong>/{{ $maxQuota }}
        </span>
        @if($trailCondition?->night_temp_celsius)
          <span class="hidden sm:inline-flex items-center gap-1.5 h-8 px-3 rounded-full bg-canvas/70 ring-1 ring-line text-xs text-muted">Suhu malam <strong class="text-ink">{{ $trailCondition->night_temp_celsius }}</strong></span>
        @endif
      </div>

      <h1 class="font-display font-bold text-[34px] leading-[1.08] sm:text-5xl md:text-6xl text-ink tracking-tight">
        Mendaki {{ $mountain }},<br><span class="text-brand">tertib dari gerbang</span> sampai puncak.
      </h1>
      <p class="mt-4 text-base md:text-lg text-muted max-w-xl">
        Pesan SIMAKSI resmi via {{ $basecamp }}. E-tiket dikirim ke WhatsApp, rombongan diperiksa di gerbang, dan setiap pos dipantau ranger.
      </p>

      <div class="mt-7 flex flex-col sm:flex-row gap-3">
        <button type="button" onclick="App.nav.switchTab('sec-booking')" class="tap-active h-14 px-7 rounded-2xl bg-brand hover:bg-brand-hover text-white text-base font-semibold flex items-center justify-center gap-2 shadow-xl shadow-brand/25">
          Pesan SIMAKSI <x-ui.icon name="arrow-right" class="w-5 h-5" />
        </button>
        <button type="button" onclick="App.modal.open('ticketModal')" class="tap-active h-14 px-6 rounded-2xl bg-canvas/70 ring-1 ring-line-strong hover:bg-hover text-ink text-base font-semibold flex items-center justify-center gap-2">
          <x-ui.icon name="ticket" class="w-5 h-5 text-brand" /> Cek status tiket
        </button>
      </div>
    </div>

    {{-- Kartu kuota --}}
    <div class="absolute left-4 right-4 bottom-4 sm:left-auto sm:right-6 sm:bottom-6 sm:w-80 rounded-2xl bg-canvas/80 backdrop-blur ring-1 ring-line p-4">
      <div class="flex items-center justify-between">
        <p class="text-xs font-semibold text-muted">Kuota pendakian hari ini</p>
        <span class="text-[11px] text-subtle">{{ now()->translatedFormat('d M Y') }}</span>
      </div>
      <p class="mt-1 font-display font-bold text-2xl text-ink tabular-nums">{{ $remaining }} <span class="text-sm font-medium text-muted">slot tersisa</span></p>
      <div class="mt-2 h-2 rounded-full bg-raised overflow-hidden"><div class="h-full rounded-full {{ $remaining < 30 ? 'bg-amber-400' : 'bg-brand' }}" style="width: {{ $quotaPct }}%"></div></div>
      <button type="button" onclick="App.nav.switchTab('sec-booking')" class="mt-3 text-sm font-semibold text-brand-soft hover:text-brand flex items-center gap-1">Cek tanggal lain <x-ui.icon name="chevron-right" class="w-4 h-4" /></button>
    </div>
  </section>

  {{-- ================= ANGKA KUNCI ================= --}}
  <section class="grid grid-cols-2 md:grid-cols-4 gap-3">
    @foreach([
      [$profile['mountain_elevation_num'] ?? '2.821', 'mdpl', 'Ketinggian puncak'],
      [$maxQuota, 'orang/hari', 'Kuota konservasi'],
      ['6,2', 'km', 'Panjang jalur'],
      ['6–8', 'jam', 'Basecamp → puncak'],
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

  {{-- ================= RUTE ================= --}}
  <section class="rounded-3xl bg-surface ring-1 ring-line p-5 md:p-7">
    <div class="flex items-end justify-between gap-4">
      <div>
        <p class="text-xs font-semibold uppercase tracking-wider text-brand">Rute</p>
        <h2 class="font-display font-bold text-2xl md:text-3xl text-ink mt-1">Pos & ketinggian</h2>
      </div>
      <button type="button" onclick="App.nav.switchTab('sec-waypoints')" class="text-sm font-semibold text-brand-soft hover:text-brand flex items-center gap-1 shrink-0">Detail rute <x-ui.icon name="chevron-right" class="w-4 h-4" /></button>
    </div>

    {{-- Profil elevasi (desktop) --}}
    <div class="hidden md:flex items-end gap-2 h-44 mt-8">
      @foreach($route as $i => $pt)
        @php $h = 18 + round((($elevNums[$i] - $minE) / ($maxE - $minE)) * 82); @endphp
        <div class="flex-1 flex flex-col items-center justify-end h-full">
          <span class="text-xs font-semibold text-ink tabular-nums mb-1.5">{{ $pt['elevation'] }}</span>
          <div class="w-full rounded-t-xl {{ $loop->last ? 'bg-brand' : 'bg-raised ring-1 ring-inset ring-line-strong' }}" style="height: {{ $h }}%"></div>
        </div>
      @endforeach
    </div>
    <div class="hidden md:grid gap-2 mt-3" style="grid-template-columns: repeat({{ count($route) }}, minmax(0, 1fr))">
      @foreach($route as $pt)
        <div class="text-center">
          <p class="text-sm font-semibold text-ink">{{ $pt['name'] }}</p>
          <p class="text-xs text-muted">{{ $pt['note'] }}</p>
        </div>
      @endforeach
    </div>

    {{-- Timeline (mobile) --}}
    <ol class="md:hidden mt-5 relative">
      @foreach($route as $pt)
        <li class="relative flex gap-4 pb-5 last:pb-0">
          @unless($loop->last)<span class="absolute left-[11px] top-6 bottom-0 w-0.5 bg-line-strong"></span>@endunless
          <span class="relative w-6 h-6 rounded-full shrink-0 flex items-center justify-center {{ $loop->last ? 'bg-brand' : 'bg-raised ring-2 ring-line-strong' }}">
            <span class="w-2 h-2 rounded-full {{ $loop->last ? 'bg-white' : 'bg-muted' }}"></span>
          </span>
          <div class="flex-1 flex items-start justify-between gap-3 -mt-0.5">
            <div>
              <p class="text-sm font-semibold text-ink">{{ $pt['name'] }}</p>
              <p class="text-xs text-muted">{{ $pt['note'] }}</p>
            </div>
            <span class="text-xs font-semibold text-ink tabular-nums shrink-0">{{ $pt['elevation'] }}</span>
          </div>
        </li>
      @endforeach
    </ol>
  </section>

  {{-- ================= KENAPA SAMSARA ================= --}}
  <section>
    <p class="text-xs font-semibold uppercase tracking-wider text-brand">Kenapa jalur Samsara</p>
    <h2 class="font-display font-bold text-2xl md:text-3xl text-ink mt-1 mb-4">Pemandangan terbaik, layanan lengkap</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
      @foreach([
        ['mountain', 'Lautan awan & bayangan segitiga', 'Puncak kerucut Cikuray memproyeksikan bayangan segitiga raksasa di atas samudera awan saat matahari terbit.'],
        ['pin', 'Hutan lumut & mata air Pos 2', 'Koridor hutan yang teduh dengan mata air jernih — titik isi ulang air terakhir sebelum puncak.'],
        ['bolt', 'Ojek kebun teh & SAR 24 jam', 'Hemat ±2 jam lewat ojek resmi ke Pos 1, dengan radio jalur dan tim SAR yang siaga sepanjang hari.'],
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
      <h2 class="font-display font-bold text-xl md:text-2xl text-ink mt-1">Siap melayani 24 jam</h2>
      <div class="mt-5 grid grid-cols-2 sm:grid-cols-3 gap-2">
        @foreach([
          ['home', 'Mushola', 'Air wudhu pegunungan'],
          ['gate', 'Parkir dijaga', 'Motor & mobil'],
          ['shield', 'Posko SAR & P3K', 'Siaga 24 jam'],
          ['bolt', 'Ojek resmi', 'Basecamp ⇄ Pos 1'],
          ['box', 'Sewa alat', 'Tenda, matras, kompor'],
          ['radio', 'Radio jalur', $profile['sar_frequency'] ?? '149.250 MHz'],
        ] as [$icon, $title, $text])
          <div class="p-3.5 rounded-2xl bg-raised/60 ring-1 ring-line">
            <x-ui.icon :name="$icon" class="w-5 h-5 text-brand" />
            <p class="text-sm font-semibold text-ink mt-2.5">{{ $title }}</p>
            <p class="text-xs text-muted">{{ $text }}</p>
          </div>
        @endforeach
      </div>
    </div>
  </section>

  {{-- ================= FAQ ================= --}}
  <section>
    <p class="text-xs font-semibold uppercase tracking-wider text-brand">Pertanyaan umum</p>
    <h2 class="font-display font-bold text-2xl md:text-3xl text-ink mt-1 mb-4">Yang sering ditanyakan</h2>
    <div class="space-y-2">
      @foreach([
        ['Jam berapa harus check-in di basecamp?', 'Pendaki tektok (pulang hari) check-in pukul '.$trailCfg['tektok_checkin_start'].'–'.$trailCfg['tektok_checkin_end'].' WIB dan wajib kembali sebelum pukul '.$trailCfg['tektok_max_checkout_hour'].':00. Pendaki camp check-in pukul '.$trailCfg['camp_checkin_start'].'–'.$trailCfg['camp_checkin_end'].' WIB.'],
        ['Di mana saya menerima e-tiket?', 'Setelah pembayaran diverifikasi kasir, e-SIMAKSI dan info pendakian lengkap dikirim ke nomor WhatsApp ketua rombongan. Anda juga bisa membukanya kapan saja lewat menu Cek tiket.'],
        ['Kenapa logistik dihitung di gerbang?', 'Agar tidak ada sampah tertinggal di gunung. Jumlah barang seperti botol dan bungkus makanan dicatat saat naik, lalu dicocokkan dengan yang dibawa turun.'],
        ['Bagaimana data KTP saya diperlakukan?', 'Foto KTP ketua hanya dipakai untuk verifikasi petugas dan dihapus permanen otomatis setelah pendakian selesai, sesuai UU Pelindungan Data Pribadi.'],
        ['Bolehkah mendirikan tenda di mana saja?', 'Tidak. Tenda hanya boleh di area camp resmi (Pos 2 dan Pos 3). Pendaki tektok dilarang bermalam.'],
      ] as [$q, $a])
        <details class="group rounded-2xl bg-surface ring-1 ring-line open:ring-line-strong">
          <summary class="flex items-center justify-between gap-4 px-5 py-4 cursor-pointer">
            <span class="font-semibold text-ink">{{ $q }}</span>
            <x-ui.icon name="plus" class="w-5 h-5 text-muted shrink-0 transition group-open:rotate-45" />
          </summary>
          <p class="px-5 pb-5 -mt-1 text-sm text-muted leading-relaxed">{{ $a }}</p>
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
