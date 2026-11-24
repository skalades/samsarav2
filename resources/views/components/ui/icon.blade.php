@props(['name', 'class' => 'w-5 h-5', 'stroke' => '1.8'])
<svg {{ $attributes->merge(['class' => $class]) }} fill="none" stroke="currentColor" stroke-width="{{ $stroke }}" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true"><path d="{{ \App\Support\Icons::path($name) }}"/></svg>
