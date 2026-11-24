@props(['variant' => 'primary', 'size' => 'md', 'href' => null, 'icon' => null, 'type' => 'button', 'block' => false])
@php
  $variants = [
    'primary' => 'bg-brand text-white hover:bg-brand-hover shadow-sm shadow-brand/20',
    'secondary' => 'bg-raised text-ink ring-1 ring-inset ring-line-strong hover:bg-hover',
    'ghost' => 'text-muted hover:text-ink hover:bg-hover',
    'danger' => 'bg-rose-600 text-white hover:bg-rose-500',
    'success' => 'bg-emerald-600 text-white hover:bg-emerald-500',
    'info' => 'bg-sky-600 text-white hover:bg-sky-500',
  ];
  $sizes = [
    'sm' => 'h-8 px-3 text-xs gap-1.5 rounded-lg',
    'md' => 'h-10 px-4 text-sm gap-2 rounded-xl',
    'lg' => 'h-12 px-5 text-[15px] gap-2 rounded-xl',
    'xl' => 'h-14 px-6 text-base gap-2.5 rounded-2xl',
  ];
  $iconSize = in_array($size, ['sm']) ? 'w-4 h-4' : 'w-[18px] h-[18px]';
  $classes = 'inline-flex items-center justify-center font-semibold transition disabled:opacity-50 disabled:pointer-events-none select-none '
    .($variants[$variant] ?? $variants['primary']).' '.($sizes[$size] ?? $sizes['md']).($block ? ' w-full' : '');
@endphp
@if($href)
  <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
    @if($icon)<x-ui.icon :name="$icon" :class="$iconSize" />@endif
    {{ $slot }}
  </a>
@else
  <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
    @if($icon)<x-ui.icon :name="$icon" :class="$iconSize" />@endif
    {{ $slot }}
  </button>
@endif
