@props(['title' => null, 'subtitle' => null, 'icon' => null, 'padding' => true])
<section {{ $attributes->merge(['class' => 'bg-surface rounded-2xl ring-1 ring-line overflow-hidden']) }}>
  @if($title || isset($actions))
    <header class="flex items-start justify-between gap-3 px-4 sm:px-5 pt-4 {{ $padding ? '' : 'pb-3 border-b border-line' }}">
      <div class="min-w-0 flex items-start gap-2.5">
        @if($icon)
          <span class="mt-0.5 w-8 h-8 shrink-0 rounded-lg bg-raised ring-1 ring-line flex items-center justify-center text-brand"><x-ui.icon :name="$icon" class="w-4 h-4" /></span>
        @endif
        <div class="min-w-0">
          @if($title)<h3 class="font-display font-semibold text-[15px] text-ink leading-tight">{{ $title }}</h3>@endif
          @if($subtitle)<p class="text-xs text-muted mt-0.5">{{ $subtitle }}</p>@endif
        </div>
      </div>
      @isset($actions)<div class="shrink-0 flex items-center gap-2">{{ $actions }}</div>@endisset
    </header>
  @endif
  <div class="{{ $padding ? 'p-4 sm:p-5' : '' }}">
    {{ $slot }}
  </div>
</section>
