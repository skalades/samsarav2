{{-- Tab Rute & pos — dirender dari pengaturan jalur (sama dengan data ranger & Beranda) --}}
@php
  $trailCfg = \App\Models\AppSetting::getTrailConfig();
  $baseElev = (string) ($profile['basecamp_elevation'] ?? '1.450 MDPL');
  $route = array_merge(
    [['code' => 'POS 00', 'name' => ($profile['basecamp_name'] ?? 'Basecamp SAMSARA'), 'elevation' => $baseElev, 'camp' => false, 'water' => false,
      'description' => 'Registrasi SIMAKSI, pemeriksaan di gerbang, briefing ranger, dan parkir kendaraan.']],
    collect($trailCfg['checkpoints'])->map(fn ($p, $k) => [
      'code' => $k === 'PUNCAK' ? 'PUNCAK' : str_replace('_', ' ', $k),
      'name' => $k === 'PUNCAK' ? ($profile['peak_name'] ?? $p['name']) : preg_replace('/^[^(]*\(|\)$/', '', $p['name']),
      'elevation' => $p['elevation'], 'camp' => $p['can_camp'], 'water' => $p['water'] ?? false, 'description' => $p['description'] ?? '',
    ])->values()->all()
  );
  $num = fn ($e) => (int) preg_replace('/\D/', '', $e);
  $gain = max(0, $num($route[count($route) - 1]['elevation']) - $num($route[0]['elevation']));
  $lastIdx = count($route) - 1;
@endphp
<div id="sec-waypoints" class="hidden space-y-4 md:space-y-6">

  @include('portal.partials.tab-heading', ['eyebrow' => 'Rute & pos', 'title' => 'Dari basecamp sampai puncak', 'text' => 'Pos resmi yang dipantau ranger.'])

  <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
    @foreach([
      [$route[0]['elevation'], 'Elevasi awal', 'Basecamp', 'text-ink'],
      [$route[$lastIdx]['elevation'], 'Elevasi puncak', $profile['peak_name'] ?? 'Puncak', 'text-brand-soft'],
      ['+'.number_format($gain, 0, ',', '.').' m', 'Total tanjakan', 'Gain elevasi', 'text-emerald-300'],
      [count($route).' titik', 'Pos dipantau', 'Basecamp s/d puncak', 'text-sky-300'],
    ] as [$val, $label, $hint, $color])
      <div class="rounded-2xl bg-surface ring-1 ring-line p-4">
        <p class="text-xs text-muted">{{ $label }}</p>
        <p class="font-display font-bold text-lg md:text-xl {{ $color }} mt-1.5 tabular-nums">{{ $val }}</p>
        <p class="text-[11px] text-subtle mt-1">{{ $hint }}</p>
      </div>
    @endforeach
  </div>

  <div id="waypointContainer" data-static="1" class="max-w-3xl mx-auto">
    <ol class="relative">
      @foreach($route as $i => $pt)
        @php $isLast = $i === $lastIdx; @endphp
        <li class="relative flex gap-4 {{ $isLast ? '' : 'pb-5' }}">
          @unless($isLast)<span class="absolute left-[15px] top-8 bottom-0 w-0.5 bg-line-strong" aria-hidden="true"></span>@endunless
          <span class="relative z-10 w-8 h-8 shrink-0 rounded-full flex items-center justify-center {{ $isLast ? 'bg-brand text-white' : 'bg-raised ring-2 ring-line-strong text-muted' }}">
            @if($isLast)<x-ui.icon name="mountain" class="w-4 h-4" />@else<span class="text-[11px] font-bold tabular-nums">{{ $i }}</span>@endif
          </span>
          <div class="flex-1 min-w-0 rounded-2xl bg-surface ring-1 {{ $isLast ? 'ring-brand/30' : 'ring-line' }} p-4">
            <div class="flex items-start justify-between gap-3">
              <div class="min-w-0">
                <p class="font-code text-[11px] font-semibold {{ $isLast ? 'text-brand-soft' : 'text-subtle' }}">{{ $pt['code'] }}</p>
                <h4 class="font-display font-bold text-base text-ink mt-0.5 leading-snug">{{ $pt['name'] }}</h4>
              </div>
              <span class="shrink-0 font-display font-bold text-ink tabular-nums">{{ $pt['elevation'] }}</span>
            </div>
            @if($pt['water'] || $pt['camp'])
              <div class="flex flex-wrap items-center gap-1.5 mt-2.5">
                @if($pt['water'])<x-ui.badge tone="info">Sumber air</x-ui.badge>@endif
                @if($pt['camp'])<x-ui.badge tone="success">Area camp</x-ui.badge>@endif
              </div>
            @endif
            <p class="text-sm text-muted mt-2.5 leading-relaxed">{{ $pt['description'] }}</p>
          </div>
        </li>
      @endforeach
    </ol>
  </div>
</div>
