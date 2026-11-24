@props(['icon' => 'inbox', 'title' => 'Belum ada data', 'text' => null])
<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center text-center py-10 px-4']) }}>
  <span class="w-12 h-12 rounded-2xl bg-raised ring-1 ring-line flex items-center justify-center text-subtle mb-3">
    <x-ui.icon :name="$icon" class="w-6 h-6" />
  </span>
  <p class="text-sm font-semibold text-ink">{{ $title }}</p>
  @if($text)<p class="text-xs text-muted mt-1 max-w-xs">{{ $text }}</p>@endif
  {{ $slot }}
</div>
