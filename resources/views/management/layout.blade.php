<!DOCTYPE html>
<html lang="id" class="h-full bg-[#0A0B0D] text-stone-200">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'Sistem Manajemen Operasional') — {{ \App\Models\AppSetting::get('expedition_name', 'SAMSARA EXPEDITION') }} ({{ \App\Models\AppSetting::get('mountain_name', 'Cikuray') }} {{ \App\Models\AppSetting::get('mountain_elevation', '2.821M') }})</title>
  
  <!-- Tailwind CSS Framework CDN -->
  <script src="https://www.gstatic.com/antigravity/web/dev/tailwindcss.min.js"></script>

  <!-- Google Fonts: Space Grotesk + JetBrains Mono + Inter -->
  <style>
    @import url('https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700;800&family=Inter:wght@400;500;600;700&display=swap');

    :root {
      --font-display: 'Space Grotesk', -apple-system, sans-serif;
      --font-mono: 'JetBrains Mono', monospace;
      --font-body: 'Inter', -apple-system, sans-serif;
    }

    body {
      font-family: var(--font-body);
      background-color: #0A0B0D;
      color: #E2E8F0;
      -webkit-font-smoothing: antialiased;
    }

    .font-display { font-family: var(--font-display); }
    .font-mono-tech { font-family: var(--font-mono); }

    /* Custom Technical Scrollbar */
    ::-webkit-scrollbar {
      width: 6px;
      height: 6px;
    }
    ::-webkit-scrollbar-track {
      background: #0A0B0D;
    }
    ::-webkit-scrollbar-thumb {
      background: #20242C;
      border-radius: 3px;
    }
    ::-webkit-scrollbar-thumb:hover {
      background: #333A48;
    }
  </style>
</head>
<body class="h-full flex flex-col antialiased selection:bg-[#FF5722] selection:text-black">

  <div class="flex-1 flex overflow-hidden">
    
    <!-- ======================================================== -->
    <!-- SIDEBAR NAVIGATION                                       -->
    <!-- ======================================================== -->
    <aside class="w-64 bg-[#0E1013] border-r border-[#1E2229] flex flex-col flex-shrink-0">
      
      <!-- Logo / Expedition Header -->
      <div class="p-4 border-b border-[#1E2229] flex items-center justify-between">
        <a href="{{ route('management.dashboard') }}" class="flex items-center gap-2.5">
          @if(\App\Models\AppSetting::get('app_logo'))
            <img src="{{ asset('storage/' . \App\Models\AppSetting::get('app_logo')) }}" alt="Logo" class="w-8 h-8 rounded object-contain bg-[#16191F] border border-[#262B35] p-0.5">
          @else
            <div class="w-8 h-8 rounded bg-[#16191F] border border-[#262B35] flex items-center justify-center font-mono-tech text-xs font-bold text-[#FF5722]">
              ▲
            </div>
          @endif
          <div>
            <span class="font-display font-black text-sm tracking-wider text-white block leading-none">SAMSARA OPS</span>
            <span class="font-mono-tech text-[9px] text-stone-400">{{ strtoupper(\App\Models\AppSetting::get('mountain_name', 'CIKURAY')) }} {{ \App\Models\AppSetting::get('mountain_elevation', '2.821M') }}</span>
          </div>
        </a>
        <span class="px-1.5 py-0.5 rounded bg-emerald-500/10 border border-emerald-500/30 text-[9px] font-mono-tech text-emerald-400 font-bold">
          LIVE
        </span>
      </div>

      <!-- Navigation Menu -->
      <nav class="flex-1 overflow-y-auto p-3 space-y-1 font-mono-tech text-xs">
        
        <div class="px-2 pt-2 pb-1 text-[10px] text-stone-400 uppercase tracking-wider font-bold">
          // OPERASIONAL UTAMA
        </div>

        <a href="{{ route('management.dashboard') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ request()->routeIs('management.dashboard') ? 'bg-[#FF5722] text-black font-bold' : 'text-stone-300 hover:bg-[#16191F] hover:text-white' }}">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
          <span>Dashboard</span>
        </a>

        <a href="{{ route('management.simaksi.index') }}" class="flex items-center justify-between px-3 py-2 rounded-lg transition {{ request()->routeIs('management.simaksi.*') ? 'bg-[#FF5722] text-black font-bold' : 'text-stone-300 hover:bg-[#16191F] hover:text-white' }}">
          <div class="flex items-center gap-2.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <span>Verifikasi SIMAKSI</span>
          </div>
          @php $pendingCount = \App\Models\SimaksiRegistration::whereIn('status', ['pending', 'needs_revision', 'submitted'])->count(); @endphp
          @if($pendingCount > 0)
            <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold {{ request()->routeIs('management.simaksi.*') ? 'bg-black text-[#FF5722]' : 'bg-[#FF5722] text-black' }}">
              {{ $pendingCount }}
            </span>
          @endif
        </a>

        <a href="{{ route('management.quota.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ request()->routeIs('management.quota.*') ? 'bg-[#FF5722] text-black font-bold' : 'text-stone-300 hover:bg-[#16191F] hover:text-white' }}">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
          <span>Kuota & Kalender</span>
        </a>

        <div class="px-2 pt-4 pb-1 text-[10px] text-stone-400 uppercase tracking-wider font-bold">
          // KASIR & LOGISTIK
        </div>

        <a href="{{ route('management.pos.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ request()->routeIs('management.pos.index') ? 'bg-[#FF5722] text-black font-bold' : 'text-stone-300 hover:bg-[#16191F] hover:text-white' }}">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
          <span>Kasir POS Terpadu</span>
        </a>

        <a href="{{ route('management.pos.history') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ request()->routeIs('management.pos.history') ? 'bg-[#FF5722] text-black font-bold' : 'text-stone-300 hover:bg-[#16191F] hover:text-white' }}">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          <span>Riwayat Transaksi POS</span>
        </a>

        <a href="{{ route('management.pos.expenses.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ request()->routeIs('management.pos.expenses.*') ? 'bg-[#FF5722] text-black font-bold' : 'text-stone-300 hover:bg-[#16191F] hover:text-white' }}">
          <svg class="w-4 h-4 {{ request()->routeIs('management.pos.expenses.*') ? 'text-black' : 'text-rose-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
          <span>Kas Keluar Operasional</span>
        </a>

        <a href="{{ route('management.inventory.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ request()->routeIs('management.inventory.*') ? 'bg-[#FF5722] text-black font-bold' : 'text-stone-300 hover:bg-[#16191F] hover:text-white' }}">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
          <span>Inventori & Sewa Alat</span>
        </a>

        <div class="px-2 pt-4 pb-1 text-[10px] text-stone-400 uppercase tracking-wider font-bold">
          // TRANSPORT & OJEK RESMI
        </div>

        <a href="{{ route('management.ojek.index') }}" class="flex items-center justify-between px-3 py-2 rounded-lg transition {{ request()->routeIs('management.ojek.index') ? 'bg-[#FF5722] text-black font-bold' : 'text-stone-300 hover:bg-[#16191F] hover:text-white' }}">
          <div class="flex items-center gap-2.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            <span>Dispatch & Antrean</span>
          </div>
          @php $waitingTripsCount = \App\Models\OjekTrip::where('trip_status', 'voucher_issued')->count(); @endphp
          @if($waitingTripsCount > 0)
            <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold {{ request()->routeIs('management.ojek.index') ? 'bg-black text-[#FF5722]' : 'bg-[#FF5722] text-black' }}">
              {{ $waitingTripsCount }}
            </span>
          @endif
        </a>

        <a href="{{ route('management.ojek.drivers') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ request()->routeIs('management.ojek.drivers') ? 'bg-[#FF5722] text-black font-bold' : 'text-stone-300 hover:bg-[#16191F] hover:text-white' }}">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
          <span>Data Driver Ojek</span>
        </a>

        <a href="{{ route('management.ojek.settlements') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ request()->routeIs('management.ojek.settlements') ? 'bg-[#FF5722] text-black font-bold' : 'text-stone-300 hover:bg-[#16191F] hover:text-white' }}">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
          <span>Rekonsiliasi Payout</span>
        </a>

        <div class="px-2 pt-4 pb-1 text-[10px] text-stone-400 uppercase tracking-wider font-bold">
          // LAPANGAN & AUDIT
        </div>

        <a href="{{ route('management.gate.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ request()->routeIs('management.gate.*') ? 'bg-[#FF5722] text-black font-bold' : 'text-stone-300 hover:bg-[#16191F] hover:text-white' }}">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
          <span>Gate Check-in & Sampah</span>
        </a>

        <a href="{{ route('management.reports.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ request()->routeIs('management.reports.*') ? 'bg-[#FF5722] text-black font-bold' : 'text-stone-300 hover:bg-[#16191F] hover:text-white' }}">
          <svg class="w-4 h-4 {{ request()->routeIs('management.reports.*') ? 'text-black' : 'text-emerald-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
          <span>Laporan & Analitik</span>
        </a>

        <a href="{{ route('management.audit.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ request()->routeIs('management.audit.*') ? 'bg-[#FF5722] text-black font-bold' : 'text-stone-300 hover:bg-[#16191F] hover:text-white' }}">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
          <span>Audit Log Operasional</span>
        </a>

        @if(in_array(auth()->user()?->role, ['superadmin', 'admin']))
          <div class="px-2 pt-4 pb-1 text-[10px] text-stone-400 uppercase tracking-wider font-bold">
            // PENGATURAN SISTEM
          </div>

          <a href="{{ route('management.settings.tariffs') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ request()->routeIs('management.settings.tariffs*') ? 'bg-[#FF5722] text-black font-bold' : 'text-stone-300 hover:bg-[#16191F] hover:text-white' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <span>Tarif & Kebijakan</span>
          </a>
        @endif

      </nav>

      <!-- Portal Publik Shortcut -->
      <div class="p-3 border-t border-[#1E2229]">
        <a href="{{ route('portal.home') }}" target="_blank" class="flex items-center justify-between px-3 py-2 rounded bg-[#14171E] border border-[#222731] text-[11px] font-mono-tech text-stone-300 hover:text-white transition">
          <span>Buka Portal Publik</span>
          <svg class="w-3.5 h-3.5 text-[#FF5722]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
        </a>
      </div>

    </aside>

    <!-- ======================================================== -->
    <!-- MAIN CONTENT AREA                                        -->
    <!-- ======================================================== -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden bg-[#0A0B0D]">
      
      <!-- Top Header Bar -->
      <header class="h-14 bg-[#0E1013] border-b border-[#1E2229] px-6 flex items-center justify-between flex-shrink-0">
        
        <!-- Breadcrumbs / Page Title -->
        <div class="flex items-center gap-3">
          <h2 class="font-display font-bold text-base text-white">
            @yield('page_title', 'Dashboard Operasional')
          </h2>
          <span class="text-stone-400 text-xs font-mono-tech">|</span>
          <span class="text-xs text-stone-400 font-mono-tech">
            @yield('page_subtitle', 'Pusat Kendali Ekspedisi ' . \App\Models\AppSetting::get('mountain_name', 'Cikuray'))
          </span>
        </div>

        <!-- User Identity & Quick Role Switcher -->
        <div class="flex items-center gap-3">
          
          <!-- Role Switcher Quick Pill (For dev & testing only, disabled in production) -->
          @if(!app()->environment('production') || config('app.allow_quick_login', false))
          <div class="relative group">
            <button class="px-2.5 py-1 rounded bg-[#16191F] border border-[#262B35] font-mono-tech text-[10px] text-stone-300 hover:text-white flex items-center gap-1.5">
              <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
              <span>GANTI ROLE</span>
              <svg class="w-3 h-3 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div class="hidden group-hover:block absolute right-0 top-full mt-1 w-48 bg-[#121418] border border-[#222731] rounded-lg shadow-2xl py-1 z-50 font-mono-tech text-xs">
              <div class="px-3 py-1 text-[9px] text-stone-400 uppercase border-b border-[#1E2229]">Pilih Role Petugas</div>
              <a href="{{ route('management.quick_login', ['role' => 'kasir']) }}" class="block px-3 py-2 text-stone-200 hover:bg-[#1E2229] hover:text-[#FF5722] font-bold">
                Kasir Operasional Loket (All-in-One)
                <span class="block text-[9px] font-normal text-stone-400">SIMAKSI, POS, Gate, Inventori</span>
              </a>
              <a href="{{ route('management.quick_login', ['role' => 'superadmin']) }}" class="block px-3 py-1.5 text-stone-300 hover:bg-[#1E2229] hover:text-[#FF5722]">Superadmin (Full Config)</a>
              <a href="{{ route('management.quick_login', ['role' => 'auditor']) }}" class="block px-3 py-1.5 text-stone-300 hover:bg-[#1E2229] hover:text-[#FF5722]">Auditor Sistem</a>
            </div>
          </div>
          @endif

          <!-- Active Staff Pill -->
          @if(Auth::check())
            <div class="flex items-center gap-2 pl-2 border-l border-[#1E2229]">
              <div class="text-right">
                <span class="block text-xs font-bold text-white leading-tight">{{ Auth::user()->name }}</span>
                <span class="block text-[9px] font-mono-tech text-[#FF5722] uppercase tracking-wider">{{ Auth::user()->role }}</span>
              </div>
              <form action="{{ route('management.logout') }}" method="POST">
                @csrf
                <button type="submit" title="Keluar dari sistem" class="p-1.5 rounded bg-[#16191F] hover:bg-rose-900/30 text-stone-400 hover:text-rose-400 transition">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                </button>
              </form>
            </div>
          @endif

        </div>

      </header>

      <!-- Alerts / Feedback Flash Messages -->
      @if(session('success'))
        <div class="bg-emerald-950/80 border-b border-emerald-500/30 px-6 py-2.5 flex items-center justify-between text-xs font-mono-tech text-emerald-300">
          <div class="flex items-center gap-3">
            <svg class="w-4 h-4 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('success') }}</span>
            @if(session('checkin_completed_id'))
              <a href="{{ route('management.gate.print_climber_pass', session('checkin_completed_id')) }}?print=1" target="_blank" class="px-2.5 py-1 bg-emerald-400 hover:bg-emerald-300 text-black font-bold rounded text-[10px] uppercase shadow transition flex items-center gap-1">
                🖨️ Cetak Surat Jalan Pendaki (Struk Saku)
              </a>
            @endif
            @if(session('checkin_has_ojek'))
              <a href="{{ route('management.gate.print_ojek_voucher', session('checkin_registration_id')) }}" target="_blank" class="px-2.5 py-1 bg-amber-400 hover:bg-amber-300 text-black font-bold rounded text-[10px] uppercase shadow transition">
                🖨️ Cetak Voucher Ojek Resmi
              </a>
            @endif
            @if(session('checkout_completed_id'))
              <a href="{{ route('management.gate.print_parking_pass', session('checkout_completed_id')) }}" target="_blank" class="px-2.5 py-1 bg-cyan-400 hover:bg-cyan-300 text-black font-bold rounded text-[10px] uppercase shadow transition">
                🖨️ Cetak Izin Keluar Parkir
              </a>
            @endif
          </div>
          <button onclick="this.parentElement.remove()" class="text-stone-400 hover:text-white">✕</button>
        </div>
      @endif

      @if(session('error'))
        <div class="bg-rose-950/80 border-b border-rose-500/30 px-6 py-2.5 flex items-center justify-between text-xs font-mono-tech text-rose-300">
          <div class="flex items-center gap-2">
            <svg class="w-4 h-4 text-rose-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('error') }}</span>
          </div>
          <button onclick="this.parentElement.remove()" class="text-stone-400 hover:text-white">✕</button>
        </div>
      @endif

      <!-- Body Content Scroll Area -->
      <main class="flex-1 overflow-y-auto p-6">
        @yield('content')
      </main>

    </div>

  </div>

  <!-- ======================================================== -->
  <!-- MODAL: PREVIEW BUKTI PEMBAYARAN                          -->
  <!-- ======================================================== -->
  <div id="paymentProofModal" class="hidden fixed inset-0 z-50 bg-black/85 backdrop-blur-md flex items-center justify-center p-3 sm:p-4" onclick="if(event.target === this) closeProofModal()">
    <div class="bg-[#121418] border border-[#262B35] rounded-2xl w-full max-w-xl max-h-[92vh] flex flex-col shadow-2xl overflow-hidden font-mono-tech">
      
      <!-- Top Modal Header -->
      <div class="p-4 border-b border-[#1E2229] bg-[#121418] flex items-center justify-between shrink-0">
        <div class="flex items-center gap-2.5">
          <div class="w-2.5 h-2.5 rounded-sm bg-cyan-400"></div>
          <div>
            <span class="text-[10px] text-cyan-400 font-bold tracking-widest uppercase block leading-none">// VERIFIKASI KASIR</span>
            <h3 class="font-display font-bold text-sm sm:text-base text-white leading-tight">BUKTI TRANSFER / PEMBAYARAN</h3>
          </div>
        </div>
        <button type="button" onclick="closeProofModal()" class="w-8 h-8 rounded-full bg-[#181B22] border border-[#282D37] text-stone-400 hover:text-white flex items-center justify-center font-bold text-sm transition">
          ✕
        </button>
      </div>

      <!-- Metadata Strip -->
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 px-4 py-2.5 bg-[#0E1013] border-b border-[#1E2229] text-[10px]">
        <div>
          <span class="text-stone-500 uppercase block">KODE REGISTRASI:</span>
          <span id="proofModalCode" class="text-white font-bold block truncate">-</span>
        </div>
        <div>
          <span class="text-stone-500 uppercase block">KETUA ROMBONGAN:</span>
          <span id="proofModalLeader" class="text-stone-300 font-bold block truncate">-</span>
        </div>
        <div>
          <span class="text-stone-500 uppercase block">TOTAL NOMINAL:</span>
          <span id="proofModalAmount" class="text-[#FF5722] font-bold block">-</span>
        </div>
        <div>
          <span class="text-stone-500 uppercase block">METODE:</span>
          <span id="proofModalMethod" class="text-emerald-400 font-bold block uppercase">-</span>
        </div>
      </div>

      <!-- Image Display Scroll Body -->
      <div class="flex-1 overflow-auto p-4 flex items-center justify-center bg-[#07080A]">
        <img id="proofModalImage" src="" alt="Bukti Pembayaran" class="max-h-[60vh] w-auto max-w-full object-contain rounded-lg border border-[#1E2229] shadow-lg">
      </div>

      <!-- Bottom Action Bar -->
      <div class="p-3 border-t border-[#1E2229] bg-[#121418] flex items-center justify-between gap-3 text-xs shrink-0">
        <a id="proofModalRawLink" href="#" target="_blank" class="px-3 py-1.5 rounded-lg bg-[#181B22] hover:bg-[#222733] border border-[#2A303C] text-cyan-400 hover:text-cyan-300 font-bold text-[11px] inline-flex items-center gap-1.5 transition">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
          <span>Buka Resolusi Penuh (Tab Baru)</span>
        </a>
        <button type="button" onclick="closeProofModal()" class="px-4 py-1.5 rounded-lg bg-[#222731] hover:bg-[#2E3544] text-stone-200 font-bold text-[11px] transition">
          Tutup [ESC]
        </button>
      </div>

    </div>
  </div>

  <script>
    function showProofModal(imageUrl, code, leader, amount, method) {
      const modal = document.getElementById('paymentProofModal');
      const img = document.getElementById('proofModalImage');
      const codeEl = document.getElementById('proofModalCode');
      const leaderEl = document.getElementById('proofModalLeader');
      const amountEl = document.getElementById('proofModalAmount');
      const methodEl = document.getElementById('proofModalMethod');
      const rawLink = document.getElementById('proofModalRawLink');

      if (img) img.src = imageUrl;
      if (codeEl) codeEl.innerText = code || '-';
      if (leaderEl) leaderEl.innerText = leader || '-';
      if (amountEl) amountEl.innerText = amount ? 'Rp ' + amount : '-';
      if (methodEl) methodEl.innerText = method || 'TRANSFER';
      if (rawLink) rawLink.href = imageUrl;

      if (modal) {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
      }
    }

    function closeProofModal() {
      const modal = document.getElementById('paymentProofModal');
      if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
      }
    }

    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        closeProofModal();
      }
    });
  </script>

</body>
</html>
