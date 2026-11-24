{{-- Judul seragam untuk tab portal --}}
<div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-2">
  <div>
    <p class="text-xs font-semibold uppercase tracking-wider text-brand">{{ $eyebrow }}</p>
    <h2 class="font-display font-bold text-2xl md:text-3xl text-ink mt-1">{{ $title }}</h2>
  </div>
  @if(!empty($text))<p class="text-sm text-muted sm:text-right">{{ $text }}</p>@endif
</div>
