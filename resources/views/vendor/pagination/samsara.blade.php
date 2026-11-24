@if ($paginator->hasPages())
  <nav role="navigation" aria-label="Navigasi halaman" class="flex items-center justify-between gap-3">
    <p class="text-xs text-muted">
      {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} dari {{ $paginator->total() }}
    </p>
    <div class="flex items-center gap-1">
      @if ($paginator->onFirstPage())
        <span class="h-9 px-3 rounded-lg text-sm text-subtle flex items-center">‹</span>
      @else
        <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="h-9 px-3 rounded-lg text-sm text-muted hover:text-ink hover:bg-hover flex items-center" aria-label="Sebelumnya">‹</a>
      @endif

      @foreach ($elements as $element)
        @if (is_string($element))
          <span class="hidden sm:flex h-9 px-2 text-sm text-subtle items-center">{{ $element }}</span>
        @endif
        @if (is_array($element))
          @foreach ($element as $page => $url)
            @if ($page == $paginator->currentPage())
              <span aria-current="page" class="h-9 min-w-[36px] px-2 rounded-lg text-sm font-semibold bg-brand text-white flex items-center justify-center">{{ $page }}</span>
            @else
              <a href="{{ $url }}" class="hidden sm:flex h-9 min-w-[36px] px-2 rounded-lg text-sm text-muted hover:text-ink hover:bg-hover items-center justify-center">{{ $page }}</a>
            @endif
          @endforeach
        @endif
      @endforeach

      @if ($paginator->hasMorePages())
        <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="h-9 px-3 rounded-lg text-sm text-muted hover:text-ink hover:bg-hover flex items-center" aria-label="Berikutnya">›</a>
      @else
        <span class="h-9 px-3 rounded-lg text-sm text-subtle flex items-center">›</span>
      @endif
    </div>
  </nav>
@endif
