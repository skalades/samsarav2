{{-- Modal responsif: bottom-sheet di mobile, dialog terpusat di tablet/laptop --}}
@props([
  'id',
  'title',
  'eyebrow' => null,
  'maxWidth' => 'sm:max-w-lg',
  'onclose' => null,
  'bodyClass' => 'p-4 sm:p-5 space-y-4',
])
@php
  $closeJs = $onclose ?? "document.getElementById('{$id}')?.classList.add('hidden')";
@endphp
<div id="{{ $id }}" class="hidden fixed inset-0 z-50 bg-black/75 backdrop-blur-sm [&:not(.hidden)]:flex items-end sm:items-center justify-center sm:p-4" role="dialog" aria-modal="true" onclick="if(event.target === this) { {{ $closeJs }} }">
  <div class="w-full {{ $maxWidth }} bg-surface ring-1 ring-line-strong rounded-t-3xl sm:rounded-2xl shadow-2xl max-h-[92dvh] flex flex-col overflow-hidden">
    <div class="px-4 sm:px-5 py-3.5 border-b border-line flex items-center justify-between gap-3 shrink-0 bg-surface">
      <div class="min-w-0">
        @if($eyebrow)<p class="text-[11px] font-semibold uppercase tracking-wider text-brand leading-none mb-1">{{ $eyebrow }}</p>@endif
        <h3 class="font-display font-bold text-base sm:text-lg text-ink truncate" id="{{ $id }}Title">{{ $title }}</h3>
      </div>
      <button type="button" onclick="{{ $closeJs }}" class="w-9 h-9 shrink-0 rounded-full bg-raised ring-1 ring-line text-muted hover:text-ink flex items-center justify-center active:scale-95 transition" aria-label="Tutup">
        <x-ui.icon name="x" class="w-4 h-4" />
      </button>
    </div>

    <div class="flex-1 overflow-y-auto overscroll-y-contain {{ $bodyClass }}">
      {{ $slot }}
    </div>

    @isset($footer)
      <div class="px-4 sm:px-5 py-3 border-t border-line bg-surface shrink-0 safe-bottom flex items-center justify-end gap-2">
        {{ $footer }}
      </div>
    @endisset
  </div>
</div>
