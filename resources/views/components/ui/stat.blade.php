@props(['label', 'value', 'hint' => null, 'tone' => 'neutral', 'icon' => null, 'href' => null])
@php
  $accent = [
    'success' => 'text-emerald-400 bg-emerald-500/10', 'warning' => 'text-amber-400 bg-amber-500/10',
    'danger' => 'text-rose-400 bg-rose-500/10', 'info' => 'text-sky-400 bg-sky-500/10',
    'brand' => 'text-brand bg-brand/10', 'violet' => 'text-violet-300 bg-violet-500/10', 'neutral' => 'text-muted bg-white/5',
  ][$tone] ?? 'text-muted bg-white/5';
  $tag = $href ? 'a' : 'div';
@endphp
<{{ $tag }} @if($href) href="{{ $href }}" @endif {{ $attributes->merge(['class' => 'group block bg-surface rounded-2xl ring-1 ring-line p-3.5 sm:p-4 '.($href ? 'hover:ring-line-strong hover:bg-hover/40 transition' : '')]) }}>
  <div class="flex items-start justify-between gap-2">
    <span class="text-xs font-medium text-muted leading-snug">{{ $label }}</span>
    @if($icon)
      <span class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 {{ $accent }}"><x-ui.icon :name="$icon" class="w-4 h-4" /></span>
    @endif
  </div>
  <div class="mt-2 font-display font-bold text-xl sm:text-2xl text-ink tabular-nums leading-none">{{ $value }}</div>
  @if($hint)<div class="mt-1.5 text-[11px] text-subtle leading-snug">{{ $hint }}</div>@endif
  {{ $slot }}
</{{ $tag }}>
