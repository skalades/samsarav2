{{-- Kartu ringkas rombongan: dipakai di Gate, Antrean Kasir, dan dashboard --}}
@props(['reg', 'href' => null, 'meta' => null, 'showStatus' => true])
@php $tag = $href ? 'a' : 'div'; @endphp
<{{ $tag }} @if($href) href="{{ $href }}" @endif {{ $attributes->merge(['class' => 'flex items-center gap-3 p-3 rounded-xl bg-raised/60 ring-1 ring-line '.($href ? 'hover:ring-line-strong hover:bg-hover transition active:scale-[.99]' : '')]) }}>
  <div class="w-11 h-11 shrink-0 rounded-xl bg-canvas ring-1 ring-line flex flex-col items-center justify-center leading-none">
    <span class="font-display font-bold text-base text-ink tabular-nums">{{ $reg->total_climbers }}</span>
    <span class="text-[9px] text-subtle mt-0.5">orang</span>
  </div>
  <div class="min-w-0 flex-1">
    <div class="flex items-center gap-2 min-w-0">
      <span class="font-semibold text-sm text-ink truncate">{{ $reg->leader_name }}</span>
      @if($reg->hiking_category)
        <x-ui.badge :tone="$reg->isTektok() ? 'info' : 'success'" size="xs">{{ $reg->isTektok() ? 'Tektok' : 'Camp' }}</x-ui.badge>
      @endif
    </div>
    <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5 text-[11px] text-muted mt-0.5">
      <span class="font-code text-ink/80">{{ $reg->registration_code }}</span>
      <span>·</span>
      <span>{{ $reg->hiking_date?->translatedFormat('d M') }}</span>
      @if($meta)<span>·</span><span>{{ $meta }}</span>@endif
    </div>
  </div>
  <div class="shrink-0 flex items-center gap-2">
    @if($showStatus)
      <x-ui.badge :tone="$reg->statusTone()" size="xs" class="hidden sm:inline-flex">{{ $reg->statusLabel() }}</x-ui.badge>
    @endif
    {{ $slot }}
    @if($href)<x-ui.icon name="chevron-right" class="w-4 h-4 text-subtle" />@endif
  </div>
</{{ $tag }}>
