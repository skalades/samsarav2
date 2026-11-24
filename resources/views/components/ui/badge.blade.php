@props(['tone' => 'neutral', 'dot' => false, 'size' => 'sm'])
@php
  $tones = [
    'success' => 'bg-emerald-500/10 text-emerald-300 ring-emerald-500/25',
    'warning' => 'bg-amber-500/10 text-amber-300 ring-amber-500/25',
    'danger' => 'bg-rose-500/10 text-rose-300 ring-rose-500/25',
    'info' => 'bg-sky-500/10 text-sky-300 ring-sky-500/25',
    'brand' => 'bg-brand/10 text-brand-soft ring-brand/30',
    'violet' => 'bg-violet-500/10 text-violet-300 ring-violet-500/25',
    'neutral' => 'bg-white/5 text-muted ring-white/10',
  ];
  $dots = [
    'success' => 'bg-emerald-400', 'warning' => 'bg-amber-400', 'danger' => 'bg-rose-400',
    'info' => 'bg-sky-400', 'brand' => 'bg-brand', 'violet' => 'bg-violet-400', 'neutral' => 'bg-subtle',
  ];
  $sizes = ['xs' => 'text-[10px] px-1.5 py-0.5', 'sm' => 'text-[11px] px-2 py-0.5', 'md' => 'text-xs px-2.5 py-1'];
@endphp
<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 rounded-full font-semibold ring-1 ring-inset whitespace-nowrap '.($tones[$tone] ?? $tones['neutral']).' '.($sizes[$size] ?? $sizes['sm'])]) }}>
  @if($dot)<span class="w-1.5 h-1.5 rounded-full {{ $dots[$tone] ?? $dots['neutral'] }}"></span>@endif
  {{ $slot }}
</span>
