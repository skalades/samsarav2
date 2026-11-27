@props(['title', 'subtitle' => null, 'eyebrow' => null])
<div {{ $attributes->merge(['class' => 'flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3 mb-4 lg:mb-5']) }}>
  <div class="min-w-0">
    @if($eyebrow)<p class="text-[11px] font-semibold uppercase tracking-wider text-brand mb-0.5">{{ $eyebrow }}</p>@endif
    <h1 class="font-display font-bold text-lg sm:text-xl lg:text-[22px] text-ink leading-tight">{{ $title }}</h1>
    @if($subtitle)<p class="text-xs sm:text-sm text-muted mt-1">{{ $subtitle }}</p>@endif
  </div>
  @isset($actions)
    <div class="flex flex-wrap items-center gap-2 shrink-0">{{ $actions }}</div>
  @endisset
</div>
