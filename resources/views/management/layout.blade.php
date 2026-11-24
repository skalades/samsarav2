@php
  /** @var \App\Models\User $authUser */
  $authUser = auth()->user();
  $profile = [
    'expedition' => \App\Models\AppSetting::get('expedition_name', 'SAMSARA EXPEDITION'),
    'mountain' => \App\Models\AppSetting::get('mountain_name', 'Cikuray'),
    'elevation' => \App\Models\AppSetting::get('mountain_elevation', '2.821 MDPL'),
    'logo' => \App\Models\AppSetting::get('app_logo'),
  ];
  $nav = $authUser ? \App\Support\Navigation::sidebar($authUser) : [];
  $bottomNav = $authUser ? \App\Support\Navigation::bottom($authUser) : [];
  $hasDrawer = $authUser ? \App\Support\Navigation::needsDrawer($authUser) : false;
  $badges = $authUser ? \App\Support\Navigation::badges() : [];
  $levelTone = [
    'superadmin' => 'brand', 'kasir' => 'success', 'gatekeeper' => 'info', 'ranger' => 'violet',
  ][$authUser?->level()] ?? 'neutral';
  $initials = collect(explode(' ', (string) $authUser?->name))->filter()->take(2)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('');
@endphp
<!DOCTYPE html>
<html lang="id" class="h-full overflow-hidden">
<head>
  @include('management.partials.head')
  <title>@yield('title', 'Sistem Manajemen Operasional') — {{ $profile['expedition'] }}</title>
</head>
<body class="h-full overflow-hidden antialiased selection:bg-brand selection:text-white">

<div id="appShell" class="relative h-full h-[100dvh] flex overflow-hidden bg-canvas">

  <!-- Backdrop drawer (mobile) -->
  <div id="mgmtBackdrop" onclick="toggleMgmtSidebar(false)" class="lg:hidden absolute inset-0 z-30 bg-black/70 backdrop-blur-sm opacity-0 pointer-events-none transition-opacity duration-200"></div>

  <!-- ================================================================ -->
  <!-- SIDEBAR (desktop) / DRAWER (mobile)                              -->
  <!-- ================================================================ -->
  <aside id="mgmtSidebar" class="absolute lg:relative inset-y-0 left-0 z-40 w-[84%] max-w-[300px] lg:w-[260px] lg:max-w-none -translate-x-full lg:translate-x-0 transition-transform duration-200 ease-out bg-surface border-r border-line flex flex-col shrink-0">

    <div class="h-16 px-4 flex items-center justify-between border-b border-line shrink-0">
      <a href="{{ route('management.dashboard') }}" class="flex items-center gap-2.5 min-w-0">
        @if($profile['logo'])
          <img src="{{ asset('storage/'.$profile['logo']) }}" alt="Logo" class="w-9 h-9 rounded-xl object-contain bg-raised ring-1 ring-line p-0.5">
        @else
          <span class="w-9 h-9 rounded-xl bg-brand/15 ring-1 ring-brand/30 flex items-center justify-center text-brand"><x-ui.icon name="mountain" class="w-5 h-5" /></span>
        @endif
        <div class="min-w-0">
          <p class="font-display font-bold text-[15px] text-ink leading-none truncate">SAMSARA OPS</p>
          <p class="text-[11px] text-muted mt-1 truncate">{{ $profile['mountain'] }} · {{ $profile['elevation'] }}</p>
        </div>
      </a>
      <button type="button" onclick="toggleMgmtSidebar(false)" class="lg:hidden w-9 h-9 rounded-xl bg-raised ring-1 ring-line text-muted hover:text-ink flex items-center justify-center" aria-label="Tutup menu">
        <x-ui.icon name="x" class="w-4 h-4" />
      </button>
    </div>

    @if($authUser)
      <div class="px-4 pt-4">
        <div class="flex items-center gap-3 p-3 rounded-2xl bg-raised/70 ring-1 ring-line">
          <span class="w-10 h-10 rounded-xl bg-canvas ring-1 ring-line flex items-center justify-center font-display font-bold text-sm text-ink">{{ $initials }}</span>
          <div class="min-w-0">
            <p class="text-sm font-semibold text-ink truncate">{{ $authUser->name }}</p>
            <x-ui.badge :tone="$levelTone" size="xs" class="mt-0.5">{{ $authUser->roleLabel() }}</x-ui.badge>
          </div>
        </div>
      </div>
    @endif

    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-5">
      @foreach($nav as $section)
        <div>
          <p class="px-3 mb-1.5 text-[11px] font-semibold uppercase tracking-wider text-subtle">{{ $section['title'] }}</p>
          <div class="space-y-0.5">
            @foreach($section['items'] as $item)
              @php
                $active = \App\Support\Navigation::isActive($item);
                $count = $item['badge'] ? ($badges[$item['badge']] ?? 0) : 0;
              @endphp
              <a href="{{ route($item['route'], $item['params']) }}" class="group flex items-center gap-3 h-10 px-3 rounded-xl text-sm transition {{ $active ? 'bg-brand/10 text-ink font-semibold ring-1 ring-inset ring-brand/25' : 'text-muted hover:text-ink hover:bg-hover' }}">
                <x-ui.icon :name="$item['icon']" class="w-[18px] h-[18px] shrink-0 {{ $active ? 'text-brand' : 'text-subtle group-hover:text-muted' }}" />
                <span class="flex-1 truncate">{{ $item['label'] }}</span>
                @if($count > 0)
                  <span class="min-w-[20px] h-5 px-1.5 rounded-full text-[11px] font-bold flex items-center justify-center {{ $item['badge'] === 'wa_failed' ? 'bg-rose-500 text-white' : 'bg-brand text-white' }}">{{ $count }}</span>
                @endif
              </a>
            @endforeach
          </div>
        </div>
      @endforeach
    </nav>

    <div class="p-3 border-t border-line space-y-1 shrink-0 safe-bottom">
      {{-- PWA Install Button (sidebar) --}}
      <button type="button" class="pwa-install-trigger pwa-btn-install w-full hidden items-center gap-3 h-10 px-3 rounded-xl text-sm bg-brand/10 text-brand ring-1 ring-inset ring-brand/30 hover:bg-brand hover:text-white transition group">
        <x-ui.icon name="download" class="w-[18px] h-[18px] shrink-0 text-brand group-hover:text-white" />
        <span class="font-semibold truncate">Pasang Aplikasi (PWA)</span>
      </button>
      <button type="button" onclick="toggleViewMode()" class="view-mode-toggle w-full hidden items-center gap-3 h-10 px-3 rounded-xl text-sm text-muted hover:text-ink hover:bg-hover">
        <x-ui.icon name="phone" class="w-[18px] h-[18px] text-subtle view-mode-icon-mobile" />
        <x-ui.icon name="monitor" class="w-[18px] h-[18px] text-subtle view-mode-icon-desktop hidden" />
        <span class="view-mode-label">Mode Mobile</span>
      </button>
      <a href="{{ route('portal.home') }}" target="_blank" class="flex items-center gap-3 h-10 px-3 rounded-xl text-sm text-muted hover:text-ink hover:bg-hover">
        <x-ui.icon name="external" class="w-[18px] h-[18px] text-subtle" />
        <span>Portal Publik</span>
      </a>
      <form action="{{ route('management.logout') }}" method="POST">
        @csrf
        <button type="submit" class="w-full flex items-center gap-3 h-10 px-3 rounded-xl text-sm text-muted hover:text-rose-300 hover:bg-rose-500/10">
          <x-ui.icon name="logout" class="w-[18px] h-[18px]" />
          <span>Keluar</span>
        </button>
      </form>
    </div>
  </aside>

  <!-- ================================================================ -->
  <!-- KOLOM KONTEN                                                     -->
  <!-- ================================================================ -->
  <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

    <header class="bg-canvas/90 backdrop-blur border-b border-line shrink-0 safe-top">
      <div class="h-14 lg:h-16 px-3 lg:px-6 flex items-center justify-between gap-2">
        <div class="flex items-center gap-2.5 min-w-0 flex-1">
          @if($hasDrawer)
            <button type="button" onclick="toggleMgmtSidebar(true)" class="lg:hidden w-10 h-10 shrink-0 rounded-xl bg-raised ring-1 ring-line text-ink flex items-center justify-center" aria-label="Buka menu">
              <x-ui.icon name="menu" class="w-5 h-5" />
            </button>
          @else
            <span class="lg:hidden w-9 h-9 shrink-0 rounded-xl bg-brand/15 ring-1 ring-brand/30 flex items-center justify-center text-brand"><x-ui.icon name="mountain" class="w-5 h-5" /></span>
          @endif
          <div class="min-w-0">
            <h2 class="font-display font-semibold text-[15px] lg:text-lg text-ink leading-tight truncate">@yield('page_title', 'Dashboard')</h2>
            <p class="hidden sm:block text-xs text-muted truncate">@yield('page_subtitle', $profile['mountain'].' · '.now()->translatedFormat('l, d F Y'))</p>
          </div>
        </div>

        <div class="flex items-center gap-1.5 sm:gap-2 shrink-0">
          {{-- PWA Status Jaringan Lapangan (Real-time Online/Offline) --}}
          <div class="pwa-net-badge inline-flex items-center gap-1.5 h-8 px-2 sm:px-2.5 rounded-xl border border-emerald-500/30 bg-emerald-500/10 text-emerald-400 text-[10px] sm:text-[11px] font-semibold transition" title="Status Jaringan Lapangan">
            <span class="pwa-net-dot w-2 h-2 rounded-full bg-emerald-400 shrink-0"></span>
            <span class="pwa-net-label">Online</span>
          </div>

          {{-- PWA Tombol Pasang Aplikasi (Muncul otomatis jika peramban mendukung & belum terpasang) --}}
          <button type="button" class="pwa-install-trigger pwa-btn-install hidden h-9 px-2.5 sm:px-3 rounded-xl bg-brand text-white text-xs font-semibold items-center gap-1.5 shadow-md shadow-brand/25 active:scale-95 transition" title="Pasang aplikasi SAMSARA OPS ke perangkat">
            <x-ui.icon name="download" class="w-4 h-4" />
            <span class="hidden md:inline">Install App</span>
          </button>

          <!-- Tombol Mode Mobile / Desktop (satu klik) -->
          <button type="button" onclick="toggleViewMode()" title="Ganti tampilan mobile / desktop" class="view-mode-toggle hidden h-9 px-3 rounded-xl bg-raised ring-1 ring-line text-xs font-semibold text-muted hover:text-ink items-center gap-1.5">
            <x-ui.icon name="phone" class="w-4 h-4 view-mode-icon-mobile" />
            <x-ui.icon name="monitor" class="w-4 h-4 view-mode-icon-desktop hidden" />
            <span class="view-mode-label">Mode Mobile</span>
          </button>

          @if(!app()->environment('production') && config('app.allow_quick_login', false))
            <details class="relative">
              <summary class="h-9 px-2.5 sm:px-3 rounded-xl bg-raised ring-1 ring-line text-xs font-semibold text-muted hover:text-ink flex items-center gap-1.5 cursor-pointer">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span><span class="hidden sm:inline">Ganti level</span>
              </summary>
              <div class="absolute right-0 top-full mt-2 w-60 max-w-[calc(100vw-1.5rem)] bg-surface ring-1 ring-line-strong rounded-2xl shadow-2xl p-1.5 z-50">
                @foreach(\App\Models\User::ROLES as $key => $role)
                  <a href="{{ route('management.quick_login', ['role' => $key]) }}" class="block px-3 py-2 rounded-xl hover:bg-hover">
                    <span class="block text-sm font-semibold text-ink">{{ $role['label'] }}</span>
                    <span class="block text-[11px] text-muted leading-snug">{{ $role['description'] }}</span>
                  </a>
                @endforeach
              </div>
            </details>
          @endif

          @if($authUser)
            <details class="relative">
              <summary class="w-9 h-9 lg:w-auto lg:pl-1 lg:pr-3 rounded-xl bg-raised ring-1 ring-line flex items-center gap-2 cursor-pointer">
                <span class="w-9 h-9 lg:w-7 lg:h-7 rounded-xl lg:rounded-lg bg-brand/15 text-brand flex items-center justify-center font-display font-bold text-xs">{{ $initials }}</span>
                <span class="hidden lg:block text-left leading-tight">
                  <span class="block text-xs font-semibold text-ink max-w-[140px] truncate">{{ $authUser->name }}</span>
                  <span class="block text-[10px] text-muted">{{ $authUser->roleLabel() }}</span>
                </span>
              </summary>
              <div class="absolute right-0 top-full mt-2 w-64 max-w-[calc(100vw-1.5rem)] bg-surface ring-1 ring-line-strong rounded-2xl shadow-2xl p-1.5 z-50">
                <div class="px-3 py-2.5 border-b border-line mb-1">
                  <p class="text-sm font-semibold text-ink truncate">{{ $authUser->name }}</p>
                  <p class="text-xs text-muted truncate">{{ $authUser->email }}</p>
                  <x-ui.badge :tone="$levelTone" size="xs" class="mt-1.5">{{ $authUser->roleLabel() }}</x-ui.badge>
                </div>
                <button type="button" class="pwa-install-trigger pwa-btn-install w-full hidden items-center gap-2.5 px-3 h-10 rounded-xl text-sm text-brand hover:bg-brand/10 font-semibold">
                  <x-ui.icon name="download" class="w-4 h-4 shrink-0" />
                  <span>Pasang Aplikasi (PWA)</span>
                </button>
                <button type="button" onclick="toggleViewMode()" class="w-full flex items-center gap-2.5 px-3 h-10 rounded-xl text-sm text-muted hover:text-ink hover:bg-hover">
                  <x-ui.icon name="phone" class="w-4 h-4 view-mode-icon-mobile" />
                  <x-ui.icon name="monitor" class="w-4 h-4 view-mode-icon-desktop hidden" />
                  <span class="view-mode-label">Mode Mobile</span>
                </button>
                @if(!$hasDrawer)
                  <a href="{{ route('portal.home') }}" target="_blank" class="lg:hidden flex items-center gap-2.5 px-3 h-10 rounded-xl text-sm text-muted hover:text-ink hover:bg-hover">
                    <x-ui.icon name="external" class="w-4 h-4" /> Portal Publik
                  </a>
                @endif
                <form action="{{ route('management.logout') }}" method="POST">
                  @csrf
                  <button type="submit" class="w-full flex items-center gap-2.5 px-3 h-10 rounded-xl text-sm text-rose-300 hover:bg-rose-500/10">
                    <x-ui.icon name="logout" class="w-4 h-4" /> Keluar
                  </button>
                </form>
              </div>
            </details>
          @endif
        </div>
      </div>
    </header>

    <main class="flex-1 overflow-y-auto overflow-x-hidden">
      <div class="w-full max-w-[1440px] mx-auto p-3 sm:p-4 lg:p-6 pb-8">
        <!-- Notifikasi -->
        @if(session('success') || session('error') || $errors->any())
          <div class="mb-4 space-y-2">
            @if(session('success'))
              <div class="flex items-start gap-3 rounded-2xl bg-emerald-500/10 ring-1 ring-emerald-500/25 px-4 py-3 text-sm text-emerald-200" data-flash>
                <x-ui.icon name="check" class="w-5 h-5 text-emerald-400 shrink-0 mt-0.5" />
                <div class="flex-1 min-w-0">
                  <p>{{ session('success') }}</p>
                  @if(session('checkout_completed_id'))
                    <a href="{{ route('management.gate.print_parking_pass', session('checkout_completed_id')) }}" target="_blank" class="inline-flex items-center gap-1.5 mt-2 h-8 px-3 rounded-lg bg-emerald-500 text-black text-xs font-bold"><x-ui.icon name="printer" class="w-4 h-4" /> Cetak izin keluar parkir</a>
                  @endif
                </div>
                <button onclick="this.closest('[data-flash]').remove()" class="text-emerald-300/70 hover:text-white" aria-label="Tutup"><x-ui.icon name="x" class="w-4 h-4" /></button>
              </div>
            @endif
            @if(session('error'))
              <div class="flex items-start gap-3 rounded-2xl bg-rose-500/10 ring-1 ring-rose-500/25 px-4 py-3 text-sm text-rose-200" data-flash>
                <x-ui.icon name="alert" class="w-5 h-5 text-rose-400 shrink-0 mt-0.5" />
                <p class="flex-1 min-w-0">{{ session('error') }}</p>
                <button onclick="this.closest('[data-flash]').remove()" class="text-rose-300/70 hover:text-white" aria-label="Tutup"><x-ui.icon name="x" class="w-4 h-4" /></button>
              </div>
            @endif
            @if($errors->any())
              <div class="flex items-start gap-3 rounded-2xl bg-rose-500/10 ring-1 ring-rose-500/25 px-4 py-3 text-sm text-rose-200" data-flash>
                <x-ui.icon name="alert" class="w-5 h-5 text-rose-400 shrink-0 mt-0.5" />
                <ul class="flex-1 min-w-0 list-disc pl-4 space-y-0.5">
                  @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
                </ul>
              </div>
            @endif
          </div>
        @endif

        @yield('content')
      </div>
    </main>

    @if(count($bottomNav) > 0)
      <nav class="lg:hidden relative z-30 shrink-0 bg-surface/95 backdrop-blur border-t border-line grid safe-bottom" style="grid-template-columns: repeat({{ count($bottomNav) + ($hasDrawer ? 1 : 0) }}, minmax(0, 1fr));">
        @foreach($bottomNav as $item)
          @php
            $active = \App\Support\Navigation::isActive($item);
            $count = $item['badge'] ? ($badges[$item['badge']] ?? 0) : 0;
            $isScan = !empty($item['params']['scan']);
          @endphp
          <a href="{{ route($item['route'], $item['params']) }}"
             @if($isScan && request()->routeIs($item['route']))
               onclick="if (typeof openQrScanner === 'function') { event.preventDefault(); openQrScanner(); }"
             @endif
             class="relative flex flex-col items-center justify-center gap-1 h-16 text-[11px] font-medium {{ $active ? 'text-brand' : 'text-muted' }} active:bg-hover/60 transition">
            @if($isScan)
              <span class="w-10 h-9 rounded-xl bg-brand text-white flex items-center justify-center shadow-md shadow-brand/30"><x-ui.icon name="scan" class="w-5 h-5" /></span>
              <span class="leading-none font-semibold text-brand">{{ $item['label'] }}</span>
            @else
              <x-ui.icon :name="$item['icon']" class="w-5 h-5" />
              <span class="leading-none">{{ $item['label'] }}</span>
            @endif
            @if($count > 0)
              <span class="absolute top-1.5 left-1/2 translate-x-1.5 min-w-[18px] h-[18px] px-1 rounded-full bg-brand text-white text-[10px] font-bold flex items-center justify-center ring-2 ring-surface">{{ $count > 99 ? '99+' : $count }}</span>
            @endif
            @if($active && ! $isScan)
              <span class="absolute top-0 inset-x-4 h-0.5 rounded-b bg-brand"></span>
            @endif
          </a>
        @endforeach
        @if($hasDrawer)
          <button type="button" onclick="toggleMgmtSidebar(true)" class="flex flex-col items-center justify-center gap-1 h-16 text-[11px] font-medium text-muted active:bg-hover/60 transition">
            <x-ui.icon name="menu" class="w-5 h-5" />
            <span class="leading-none">Menu</span>
          </button>
        @endif
      </nav>
    @endif
  </div>
</div>

@include('management.partials.proof_modal')

<script>
  function toggleMgmtSidebar(open) {
    document.getElementById('mgmtSidebar')?.classList.toggle('translate-x-0', open);
    document.getElementById('mgmtSidebar')?.classList.toggle('-translate-x-full', !open);
    const backdrop = document.getElementById('mgmtBackdrop');
    if (backdrop) { backdrop.classList.toggle('opacity-0', !open); backdrop.classList.toggle('pointer-events-none', !open); }
  }

  // Satu tombol: Mode Mobile <-> Mode Desktop
  function toggleViewMode() {
    const next = window.SAMSARA_VIEW_MODE === 'mobile' ? 'auto' : 'mobile';
    try { localStorage.setItem('samsara_view_mode', next); } catch (e) {}
    window.location.reload();
  }

  (function initViewModeToggle() {
    const forced = window.SAMSARA_VIEW_MODE === 'mobile';
    document.querySelectorAll('.view-mode-label').forEach(el => el.textContent = forced ? 'Mode Desktop' : 'Mode Mobile');
    document.querySelectorAll('.view-mode-icon-mobile').forEach(el => el.classList.toggle('hidden', forced));
    document.querySelectorAll('.view-mode-icon-desktop').forEach(el => el.classList.toggle('hidden', !forced));
    const update = () => {
      // Di ponsel asli tampilan sudah otomatis mobile; tombol hanya di layar lebar atau saat mode dipaksa
      const show = forced || window.innerWidth >= 1024;
      document.querySelectorAll('.view-mode-toggle').forEach(btn => { btn.classList.toggle('hidden', !show); btn.classList.toggle('flex', show); });
    };
    update();
    window.addEventListener('resize', update);
  })();

  // Kunci scroll window/document agar appShell dan bottom nav tidak pernah terdorong naik
  window.addEventListener('scroll', function () {
    if (window.scrollY !== 0 || window.scrollX !== 0) {
      window.scrollTo(0, 0);
    }
  }, { passive: true });

  document.addEventListener('keydown', e => { if (e.key === 'Escape') { closeProofModal(); toggleMgmtSidebar(false); } });

  // Tutup dropdown <details> saat klik di luar
  document.addEventListener('click', e => {
    document.querySelectorAll('details[open]').forEach(d => { if (!d.contains(e.target)) d.removeAttribute('open'); });
  });
</script>
@stack('scripts')
</body>
</html>
