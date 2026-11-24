{{-- Tab Kondisi jalur (live dari telemetri ranger) --}}
@php
  $t = $trailCondition;
  $status = $t->status ?? 'open';
  $meta = [
    'open' => ['Jalur dibuka', 'Jalur bersih dan aman dilalui. Tetap ikuti arahan ranger di setiap pos.', 'emerald', 'check'],
    'caution' => ['Jalur waspada', 'Pendakian dibuka dengan kehati-hatian. Ikuti arahan ranger dan pantau cuaca.', 'amber', 'alert'],
    'closed' => ['Jalur ditutup', 'Pendakian ditiadakan sementara. Pantau informasi terbaru dari basecamp.', 'rose', 'x'],
  ][$status] ?? ['Jalur dibuka', 'Jalur bersih dan aman dilalui.', 'emerald', 'check'];
  $tone = [
    'emerald' => ['bg-emerald-500/10 ring-emerald-500/25', 'text-emerald-300', 'bg-emerald-400'],
    'amber' => ['bg-amber-500/10 ring-amber-500/25', 'text-amber-300', 'bg-amber-400'],
    'rose' => ['bg-rose-500/10 ring-rose-500/25', 'text-rose-300', 'bg-rose-400'],
  ][$meta[2]];
  $hotline = $profile['emergency_hotline'] ?? '+62 821-2233-4455';
  $hotlineTel = preg_replace('/[^0-9+]/', '', $hotline);
@endphp

<div id="sec-telemetry" class="hidden space-y-4 md:space-y-6">

  @include('portal.partials.tab-heading', ['eyebrow' => 'Kondisi jalur', 'title' => 'Telemetri lapangan hari ini', 'text' => 'Diperbarui ranger basecamp'.($t?->updated_at ? ' · '.$t->updated_at->diffForHumans() : '').'.'])

  {{-- Status utama --}}
  <div class="rounded-3xl ring-1 p-5 md:p-7 flex items-start gap-4 {{ $tone[0] }}">
    <span class="w-12 h-12 shrink-0 rounded-2xl bg-canvas/60 ring-1 ring-line flex items-center justify-center {{ $tone[1] }}"><x-ui.icon :name="$meta[3]" class="w-6 h-6" /></span>
    <div class="min-w-0">
      <p class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider {{ $tone[1] }}">
        <span class="relative flex w-2 h-2"><span class="absolute inline-flex w-full h-full rounded-full {{ $tone[2] }} opacity-60 animate-ping"></span><span class="relative inline-flex w-2 h-2 rounded-full {{ $tone[2] }}"></span></span>
        Status jalur
      </p>
      <p id="telemetryTrailStatus" class="font-display font-bold text-2xl md:text-3xl text-ink mt-1">{{ $meta[0] }}</p>
      <p class="text-sm text-muted mt-1">{{ $meta[1] }}</p>
    </div>
  </div>

  {{-- Metrik --}}
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
    @foreach([
      ['telemetryWaterStatus', 'Mata air Pos 2', $t ? $t->pos2_water_status : 'Melimpah (12 L/mnt)', 'Sumber air terakhir sebelum puncak', 'refresh', 'text-sky-300'],
      ['telemetryNightTemp', 'Suhu dini hari', $t ? $t->night_temp_celsius : '9°C', 'Bawa jaket windproof & sleeping bag', 'mountain', 'text-amber-300'],
      ['telemetrySoilStatus', 'Kondisi tanah', $t ? $t->soil_condition : 'Padat & kering', 'Grip sepatu trekking optimal', 'pin', 'text-ink'],
      [null, 'Cuaca', $t ? $t->weather_condition : 'Cerah berawan', $t && $t->wind_speed ? 'Angin '.$t->wind_speed : 'Pantau perubahan cuaca', 'bolt', 'text-ink'],
    ] as [$id, $label, $value, $hint, $icon, $color])
      <div class="rounded-2xl bg-surface ring-1 ring-line p-4">
        <div class="flex items-center justify-between">
          <span class="text-xs font-medium text-muted">{{ $label }}</span>
          <x-ui.icon :name="$icon" class="w-4 h-4 text-subtle" />
        </div>
        <p @if($id) id="{{ $id }}" @endif class="font-display font-bold text-lg {{ $color }} mt-2 leading-snug">{{ $value }}</p>
        <p class="text-[11px] text-subtle mt-1.5 leading-snug">{{ $hint }}</p>
      </div>
    @endforeach
  </div>

  @if($t?->ranger_notes)
    <div class="rounded-2xl bg-surface ring-1 ring-line p-4 md:p-5 flex gap-3">
      <x-ui.icon name="radio" class="w-5 h-5 text-brand shrink-0 mt-0.5" />
      <div>
        <p class="text-sm font-semibold text-ink">Catatan ranger</p>
        <p class="text-sm text-muted mt-0.5 leading-relaxed">{{ $t->ranger_notes }}</p>
      </div>
    </div>
  @endif

  <div class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-4">
    {{-- Darurat --}}
    <div class="rounded-3xl bg-surface ring-1 ring-line p-5 md:p-6">
      <span class="w-11 h-11 rounded-xl bg-rose-500/10 ring-1 ring-rose-500/25 text-rose-300 flex items-center justify-center"><x-ui.icon name="shield" class="w-5 h-5" /></span>
      <h3 class="font-display font-bold text-lg text-ink mt-4">Komunikasi darurat SAR</h3>
      <dl class="mt-3 divide-y divide-line text-sm">
        <div class="flex items-center justify-between gap-3 py-2.5"><dt class="text-muted">Frekuensi radio HT</dt><dd class="font-code text-ink">{{ $profile['sar_frequency'] ?? '149.250 MHz' }}</dd></div>
        <div class="flex items-center justify-between gap-3 py-2.5"><dt class="text-muted">Hotline ranger</dt><dd><a href="tel:{{ $hotlineTel }}" class="font-semibold text-brand-soft hover:text-brand">{{ $hotline }}</a></dd></div>
        <div class="flex items-center justify-between gap-3 py-2.5"><dt class="text-muted">Evakuasi medis</dt><dd class="text-emerald-300 font-semibold">Pos 00 Basecamp</dd></div>
      </dl>
      <a href="tel:{{ $hotlineTel }}" class="tap-active mt-4 h-12 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-semibold text-sm flex items-center justify-center gap-2"><x-ui.icon name="phone" class="w-5 h-5" /> Hubungi ranger</a>
    </div>

    {{-- Keselamatan --}}
    <div class="rounded-3xl bg-surface ring-1 ring-line p-5 md:p-6">
      <span class="w-11 h-11 rounded-xl bg-amber-500/10 ring-1 ring-amber-500/25 text-amber-300 flex items-center justify-center"><x-ui.icon name="alert" class="w-5 h-5" /></span>
      <h3 class="font-display font-bold text-lg text-ink mt-4">Panduan keselamatan</h3>
      <ul class="mt-3 space-y-2.5 text-sm text-muted">
        <li class="flex gap-2.5"><x-ui.icon name="check" class="w-4 h-4 text-emerald-400 shrink-0 mt-0.5" /> Lapor di gerbang sebelum naik dan setelah turun.</li>
        <li class="flex gap-2.5"><x-ui.icon name="check" class="w-4 h-4 text-emerald-400 shrink-0 mt-0.5" /> Dilarang membuat api unggun di area semak kering.</li>
        <li class="flex gap-2.5"><x-ui.icon name="check" class="w-4 h-4 text-emerald-400 shrink-0 mt-0.5" />
          <span>Bawa kembali seluruh sampah kemasan
            @if(!empty($tariffs['trash_deposit_enabled'])) untuk mencairkan jaminan sampah Rp {{ number_format($tariffs['trash_deposit_amount'] ?? 50000, 0, ',', '.') }}.
            @else — jumlahnya dicocokkan di gerbang saat turun.
            @endif
          </span>
        </li>
        <li class="flex gap-2.5"><x-ui.icon name="check" class="w-4 h-4 text-emerald-400 shrink-0 mt-0.5" /> Tenda hanya di area camp resmi.</li>
      </ul>
    </div>
  </div>
</div>
