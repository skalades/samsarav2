{{-- Tab Rute & pos — titik dari survei GPS (AppSetting::getTrailRoute) --}}
@php
  $route = \App\Models\AppSetting::getTrailRoute();
  $route[0]['name'] = $profile['basecamp_name'] ?? 'Basecamp SAMSARA';
  $lastIdx = count($route) - 1;
  $gain = $route[$lastIdx]['elev'] - $route[0]['elev'];
  $posCount = collect($route)->where('type', 'pos')->count();
  $mdpl = fn ($e) => number_format($e, 0, ',', '.').' MDPL';
  $km = fn ($m) => $m < 1000 ? $m.' m' : number_format($m / 1000, 1, ',', '.').' km';
  $dur = fn ($min) => ($min >= 60 ? intdiv($min, 60).'j'.($min % 60 ? ' '.($min % 60).'m' : '') : $min.' mnt');
  $diff = fn ($g) => $g < 15 ? ['Landai', 'text-emerald-300'] : ($g < 30 ? ['Sedang', 'text-amber-300'] : ($g < 45 ? ['Terjal', 'text-orange-300'] : ['Sangat terjal', 'text-rose-300']));
  $last = $route[$lastIdx];
@endphp
<div id="sec-waypoints" class="hidden space-y-4 md:space-y-6">

  @include('portal.partials.tab-heading', ['eyebrow' => 'Rute & pos', 'title' => 'Dari basecamp sampai puncak', 'text' => 'Peta jalur, ketinggian, dan fasilitas setiap pos.'])

  <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
    @foreach([
      [$mdpl($route[0]['elev']), 'Elevasi awal', 'Basecamp', 'text-ink'],
      [$mdpl($route[$lastIdx]['elev']), 'Elevasi puncak', 'Puncak Cikuray', 'text-brand-soft'],
      ['±'.$km($last['cum']['dist_m']), 'Panjang jalur', '+'.number_format($gain, 0, ',', '.').' m elevasi', 'text-emerald-300'],
      ['±'.$dur($last['cum']['up_min']), 'Estimasi naik', 'Turun ±'.$dur($last['cum']['down_min']), 'text-sky-300'],
    ] as [$val, $label, $hint, $color])
      <div class="rounded-2xl bg-surface ring-1 ring-line p-4">
        <p class="text-xs text-muted">{{ $label }}</p>
        <p class="font-display font-bold text-lg md:text-xl {{ $color }} mt-1.5 tabular-nums">{{ $val }}</p>
        <p class="text-[11px] text-subtle mt-1">{{ $hint }}</p>
      </div>
    @endforeach
  </div>

  <section class="rounded-3xl bg-surface ring-1 ring-line p-3 md:p-5">
    @include('portal.partials.trail-map', ['mapId' => 'trailMapRoute'])
  </section>

  <div class="rounded-2xl bg-sky-500/10 ring-1 ring-sky-500/25 p-4 text-sm text-sky-100 leading-relaxed">
    <strong class="text-sky-200">Air & camp:</strong> air melimpah, toilet, mushola, dan shelter tersedia di Pos 1, 2, dan 3. Tenda hanya boleh di Pos 2 dan Pos 3, <strong class="text-sky-200">disarankan di Pos 3</strong> karena paling dekat ke puncak. <strong class="text-rose-300">Puncak dilarang camp</strong>: tinggalkan tenda dan perlengkapan di Pos 3, summit attack cukup membawa daypack. Pos 3 juga sumber air terakhir, isi penuh botol sebelum lanjut.
    <span class="block mt-1.5 text-xs text-sky-200/70">Jarak dan tanjakan diukur dari track GPS jalur. Waktu tempuh adalah estimasi untuk pendaki ber-carrier, belum termasuk istirahat; tiap rombongan bisa berbeda.</span>
  </div>

  <div id="waypointContainer" data-static="1" class="max-w-3xl mx-auto">
    <ol class="relative">
      @foreach($route as $i => $pt)
        @php $isLast = $i === $lastIdx; $isLandmark = $pt['type'] === 'landmark'; @endphp
        <li class="relative flex gap-3 md:gap-4 {{ $isLast ? '' : 'pb-4' }}">
          @unless($isLast)<span class="absolute left-[15px] top-8 bottom-0 w-0.5 bg-line-strong" aria-hidden="true"></span>@endunless
          <span class="relative z-10 w-8 h-8 shrink-0 rounded-full flex items-center justify-center text-[11px] font-bold
            {{ $isLast ? 'bg-brand text-white' : ($pt['type'] === 'basecamp' ? 'bg-emerald-500 text-white' : ($pt['camp'] ? 'bg-sky-500 text-white' : 'bg-raised ring-2 ring-line-strong text-muted')) }}">
            @if($isLast)
              <x-ui.icon name="mountain" class="w-4 h-4" />
            @else
              {{ $pt['type'] === 'basecamp' ? 'BC' : ($isLandmark ? '≈' : preg_replace('/\D/', '', $pt['code'])) }}
            @endif
          </span>
          <div class="flex-1 min-w-0 rounded-2xl {{ $isLandmark ? 'bg-transparent border border-dashed border-line-strong py-2.5 px-4' : 'bg-surface ring-1 p-4' }} {{ $isLast ? 'ring-brand/30' : ($isLandmark ? '' : 'ring-line') }}">
            <div class="flex items-start justify-between gap-3">
              <div class="min-w-0">
                <p class="font-code text-[11px] font-semibold {{ $isLast ? 'text-brand-soft' : 'text-subtle' }}">{{ $pt['code'] }}</p>
                <h4 class="font-display font-bold {{ $isLandmark ? 'text-sm' : 'text-base' }} text-ink mt-0.5 leading-snug">{{ $pt['name'] }}</h4>
              </div>
              <span class="shrink-0 font-display font-bold text-sm md:text-base text-ink tabular-nums">{{ $mdpl($pt['elev']) }}</span>
            </div>
            @if($pt['camp'] || count($pt['facilities']) || !empty($pt['no_camp']))
              <div class="flex flex-wrap items-center gap-1.5 mt-2.5">
                @if(!empty($pt['camp_recommended']))
                  <x-ui.badge tone="info">Camp disarankan</x-ui.badge>
                @elseif($pt['camp'])
                  <x-ui.badge tone="info">Camp resmi</x-ui.badge>
                @endif
                @if(!empty($pt['no_camp']))
                  <x-ui.badge tone="danger">Dilarang camp</x-ui.badge>
                @endif
                @foreach($pt['facilities'] as $f)<x-ui.badge tone="success">{{ $f }}</x-ui.badge>@endforeach
              </div>
            @endif
            <p class="text-sm text-muted mt-2 leading-relaxed">{{ $pt['description'] }}</p>
            @if($pt['seg'])
              @php [$dl, $dc] = $diff($pt['seg']['grade']); @endphp
              <div class="mt-3 grid grid-cols-2 sm:grid-cols-4 gap-1.5 text-center">
                <div class="rounded-lg bg-raised ring-1 ring-line py-1.5"><p class="text-sm font-bold text-ink tabular-nums">{{ $km($pt['seg']['dist_m']) }}</p><p class="text-[10px] text-muted">dari {{ $route[$i - 1]['type'] === 'pos' ? ucfirst(strtolower($route[$i - 1]['code'])) : $route[$i - 1]['name'] }}</p></div>
                <div class="rounded-lg bg-raised ring-1 ring-line py-1.5"><p class="text-sm font-bold text-ink tabular-nums">±{{ $dur($pt['seg']['up_min']) }}</p><p class="text-[10px] text-muted">waktu naik{{ !empty($pt['ojek_min']) ? ' (ojek ±'.$pt['ojek_min'].'m)' : '' }}</p></div>
                <div class="rounded-lg bg-raised ring-1 ring-line py-1.5"><p class="text-sm font-bold text-ink tabular-nums">±{{ $dur($pt['seg']['down_min']) }}</p><p class="text-[10px] text-muted">waktu turun</p></div>
                <div class="rounded-lg bg-raised ring-1 ring-line py-1.5"><p class="text-sm font-bold {{ $dc }}">{{ $dl }}</p><p class="text-[10px] text-muted">rata-rata {{ $pt['seg']['grade'] }}% · maks {{ $pt['seg']['max_grade'] }}%</p></div>
              </div>
              <p class="text-[11px] text-subtle mt-1.5">Kumulatif dari basecamp: {{ $km($pt['cum']['dist_m']) }} · ±{{ $dur($pt['cum']['up_min']) }} jalan kaki</p>
            @endif
            @if(in_array($pt['type'], ['pos', 'peak']))
              @if($pt['photo'])
                <img src="{{ asset('storage/'.$pt['photo']) }}" alt="Foto {{ $pt['code'] }} {{ $pt['name'] }}" loading="lazy" class="mt-3 w-full aspect-video object-cover rounded-xl">
              @else
                <div class="mt-3 flex items-center gap-2 rounded-xl border border-dashed border-line-strong px-3 py-2 text-xs text-subtle">
                  <x-ui.icon name="camera" class="w-4 h-4" /> Foto pos segera hadir
                </div>
              @endif
            @endif
          </div>
        </li>
      @endforeach
    </ol>
  </div>
</div>
