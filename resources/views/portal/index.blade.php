<!DOCTYPE html>
<html lang="id" class="scroll-smooth bg-[#0A0B0D]">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ $profile['expedition_name'] ?? 'SAMSARA EXPEDITION' }} — {{ $profile['mountain_name'] ?? 'Gunung Cikuray' }} {{ $profile['mountain_elevation'] ?? '2.821M' }} | Portal Resmi</title>
  <meta name="description" content="Portal resmi SIMAKSI pendakian {{ $profile['mountain_name'] ?? 'Gunung Cikuray' }} via {{ $profile['basecamp_name'] ?? 'Basecamp SAMSARA' }}. Cek kuota harian live, reservasi tiket masuk, logistik ojek trail, dan penitipan parkir.">
  
  <!-- Tailwind CSS Framework CDN -->
  <script src="https://www.gstatic.com/antigravity/web/dev/tailwindcss.min.js"></script>
  
  <!-- Modular Custom CSS (Tokens, Typography, Safe-Area) -->
  <link rel="stylesheet" href="{{ asset('assets/css/main.css') }}">
</head>
<body class="min-h-screen flex flex-col selection:bg-[#FF5722] selection:text-black pb-20 md:pb-6">

  <!-- ======================================================== -->
  <!-- 1. TECHNICAL TOP BAR (RESPONSIVE DESKTOP & MOBILE)       -->
  <!-- ======================================================== -->
  <header class="sticky top-0 z-40 bg-[#0A0B0D]/95 backdrop-blur-md border-b border-[#1E2229] px-4 py-3">
    <div class="max-w-5xl mx-auto flex items-center justify-between gap-4">
      <!-- Brand & Elevation Monogram -->
      <a href="#" onclick="App.nav.switchTab('sec-beranda'); return false;" class="flex items-center gap-2.5 shrink-0">
        @if(!empty($profile['app_logo']))
          <img src="{{ asset('storage/' . $profile['app_logo']) }}" alt="Logo" class="w-8 h-8 rounded object-contain bg-[#16191F] border border-[#262B35] p-0.5">
        @else
          <div class="w-8 h-8 rounded bg-[#16191F] border border-[#262B35] flex items-center justify-center font-mono-tech text-xs font-bold text-[#FF5722]">
            ▲
          </div>
        @endif
        <div>
          <span class="font-display font-black text-sm tracking-widest text-white block leading-none">{{ $profile['expedition_name'] ?? 'SAMSARA' }}</span>
          <span class="font-mono-tech text-[9px] text-stone-400 tracking-wider">{{ strtoupper($profile['mountain_name'] ?? 'CIKURAY') }} {{ $profile['mountain_elevation'] ?? '2.821M' }}</span>
        </div>
      </a>

      <!-- Desktop Segmented Tabs (Visible on md and up) -->
      <div class="hidden md:flex items-center gap-1 p-1 bg-[#121418] border border-[#20242C] rounded-xl font-mono-tech text-xs">
        <button type="button" onclick="App.nav.switchTab('sec-beranda')" data-tab="sec-beranda" class="tab-btn px-3 py-1.5 rounded-lg bg-[#FF5722] text-black font-bold border border-[#FF5722] transition shadow-lg shadow-[#FF5722]/20">
          BERANDA
        </button>
        <button type="button" onclick="App.nav.switchTab('sec-booking')" data-tab="sec-booking" class="tab-btn px-3 py-1.5 rounded-lg bg-[#121418] hover:bg-[#181B22] text-stone-400 font-bold border border-[#222731] transition">
          RESERVASI
        </button>
        <button type="button" onclick="App.nav.switchTab('sec-telemetry')" data-tab="sec-telemetry" class="tab-btn px-3 py-1.5 rounded-lg bg-[#121418] hover:bg-[#181B22] text-stone-400 font-bold border border-[#222731] transition">
          KONDISI JALUR
        </button>
        <button type="button" onclick="App.nav.switchTab('sec-waypoints')" data-tab="sec-waypoints" class="tab-btn px-3 py-1.5 rounded-lg bg-[#121418] hover:bg-[#181B22] text-stone-400 font-bold border border-[#222731] transition">
          RUTE POS
        </button>
        <button type="button" onclick="App.nav.switchTab('sec-logistik')" data-tab="sec-logistik" class="tab-btn px-3 py-1.5 rounded-lg bg-[#121418] hover:bg-[#181B22] text-stone-400 font-bold border border-[#222731] transition">
          LOGISTIK
        </button>
      </div>

      <!-- Quick Action: Cek Tiket -->
      <button onclick="App.modal.open('ticketModal')" class="tap-active px-3 py-1.5 rounded-lg bg-[#16191F] hover:bg-[#1E232E] border border-[#282D37] text-stone-200 font-mono-tech text-xs font-bold flex items-center gap-1.5 transition shrink-0">
        <svg class="w-3.5 h-3.5 text-[#FF5722]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
        <span>CEK TIKET</span>
      </button>
    </div>
  </header>

  <!-- ======================================================== -->
  <!-- MOBILE SEGMENTED TABS BAR (VISIBLE ON MOBILE ONLY)       -->
  <!-- ======================================================== -->
  <div class="md:hidden px-4 pt-3 max-w-5xl mx-auto w-full">
    <div class="grid grid-cols-5 gap-1 p-1 bg-[#121418] border border-[#20242C] rounded-xl font-mono-tech text-[10px]">
      <button type="button" onclick="App.nav.switchTab('sec-beranda')" data-tab="sec-beranda" class="tab-btn py-1.5 rounded-lg bg-[#FF5722] text-black font-bold text-center border border-[#FF5722] transition">
        BERANDA
      </button>
      <button type="button" onclick="App.nav.switchTab('sec-booking')" data-tab="sec-booking" class="tab-btn py-1.5 rounded-lg bg-[#121418] text-stone-400 font-bold text-center border border-[#222731] transition">
        RESERVASI
      </button>
      <button type="button" onclick="App.nav.switchTab('sec-telemetry')" data-tab="sec-telemetry" class="tab-btn py-1.5 rounded-lg bg-[#121418] text-stone-400 font-bold text-center border border-[#222731] transition">
        KONDISI
      </button>
      <button type="button" onclick="App.nav.switchTab('sec-waypoints')" data-tab="sec-waypoints" class="tab-btn py-1.5 rounded-lg bg-[#121418] text-stone-400 font-bold text-center border border-[#222731] transition">
        POS
      </button>
      <button type="button" onclick="App.nav.switchTab('sec-logistik')" data-tab="sec-logistik" class="tab-btn py-1.5 rounded-lg bg-[#121418] text-stone-400 font-bold text-center border border-[#222731] transition">
        LOGISTIK
      </button>
    </div>
  </div>

  <!-- ======================================================== -->
  <!-- MAIN CONTENT CONTAINER (MAX-W-5XL WIDESCREEN & MOBILE)   -->
  <!-- ======================================================== -->
  <main class="flex-1 max-w-5xl mx-auto w-full px-4 pt-3 space-y-4">

    <!-- ======================================================== -->
    <!-- DRAFT RECOVERY BANNER (ANTI-TERCLOSE RESUME)             -->
    <!-- ======================================================== -->
    <div id="draftRecoveryBanner" class="hidden p-3.5 rounded-xl bg-[#14171E] border border-[#FF5722]/50 shadow-xl space-y-2 font-mono-tech text-xs">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
          <span class="relative flex h-2 w-2">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#FF5722] opacity-75"></span>
            <span class="relative inline-flex rounded-full h-2 w-2 bg-[#FF5722]"></span>
          </span>
          <span class="text-white font-bold text-[11px] tracking-wider">// DRAF PENDAFTARAN TERSIMPAN</span>
        </div>
        <button type="button" onclick="App.draft.discard()" class="text-stone-400 hover:text-rose-400 text-[10px] underline">
          HAPUS DRAF
        </button>
      </div>
      <p id="draftRecoveryInfo" class="text-[10px] text-stone-300 leading-relaxed">
        Ditemukan sesi pendaftaran sebelumnya yang belum selesai. Anda dapat melanjutkan pengisian atau pembayaran langsung.
      </p>
      <div class="flex items-center gap-2 pt-0.5">
        <button type="button" onclick="App.draft.resume()" class="flex-1 py-2 rounded-lg bg-[#FF5722] hover:bg-[#F4511E] text-black font-bold text-xs uppercase tracking-wider flex items-center justify-center gap-1.5 transition">
          <span>LANJUTKAN PROSES</span>
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
        </button>
        <button type="button" onclick="App.draft.dismiss()" class="px-3 py-2 rounded-lg bg-[#1F242E] text-stone-300 text-xs hover:bg-[#282F3D] transition">
          NANTI
        </button>
      </div>
    </div>

    <!-- ======================================================== -->
    <!-- TAB 0: BERANDA / WELCOME PAGE & RINGKASAN (#sec-beranda) -->
    <!-- ======================================================== -->
    <div id="sec-beranda" class="space-y-5">
      
      <!-- 1. GRAND HERO WELCOME CARD -->
      <div class="relative rounded-2xl overflow-hidden bg-gradient-to-br from-[#141820] via-[#101216] to-[#0A0B0D] border border-[#262C38] p-5 sm:p-7 shadow-2xl space-y-4">
        <!-- Live Status & Telemetry Badges Strip -->
        <div class="flex flex-wrap items-center gap-2 font-mono-tech text-[10px]">
          <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 font-bold">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            <span>JALUR {{ $trailCondition ? strtoupper($trailCondition->status) : 'TERBUKA NORMAL' }}</span>
          </div>
          <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-[#181C24] border border-[#2A303C] text-stone-300">
            <span>KUOTA HARI INI:</span>
            <strong class="text-[#FF5722] font-bold">{{ $todayQuota->remaining_quota }} SISA</strong>
            <span class="text-stone-500">/ {{ $todayQuota->max_quota }}</span>
          </div>
          <div class="hidden sm:inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-[#181C24] border border-[#2A303C] text-stone-300">
            <span>SUHU PUNCAK:</span>
            <strong class="text-amber-300 font-bold">{{ $trailCondition ? $trailCondition->summit_temp_celsius . '°C' : '11°C' }}</strong>
          </div>
          <div class="hidden md:inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-[#181C24] border border-[#2A303C] text-stone-400">
            <span>SAR 24 JAM:</span>
            <strong class="text-white">SIAGA PENUH</strong>
          </div>
        </div>

        <!-- Main Headline & Narrative -->
        <div class="space-y-2 pt-1 max-w-3xl">
          <div class="flex items-center gap-2">
            <span class="px-2 py-0.5 rounded text-[9px] font-mono-tech font-bold bg-[#FF5722]/15 text-[#FF5722] border border-[#FF5722]/30 uppercase tracking-widest">
              PORTAL RESMI PENDAKIAN // GARUT, JAWA BARAT
            </span>
          </div>
          <h1 class="font-display font-black text-2xl sm:text-4xl text-white tracking-tight uppercase leading-tight">
            EKSPEDISI ATAP GARUT <br class="hidden sm:inline">
            <span class="text-[#FF5722]">{{ strtoupper($profile['mountain_name'] ?? 'GUNUNG CIKURAY') }} {{ $profile['mountain_elevation'] ?? '2.821 MDPL' }}</span>
          </h1>
          <p class="text-xs sm:text-sm text-stone-300 leading-relaxed font-sans pt-1">
            Selamat datang di portal informasi resmi dan reservasi mandiri pendakian {{ $profile['mountain_name'] ?? 'Gunung Cikuray' }} via <strong>{{ $profile['basecamp_name'] ?? 'Basecamp SAMSARA' }}</strong>. Jalur terawat yang menyajikan panorama perkebunan teh yang luas, keteduhan hutan lumut asri, serta magisnya samudera awan dan fenomena bayangan piramida segitiga saat matahari terbit.
          </p>
        </div>

        <!-- Action Buttons (Hero CTA) -->
        <div class="flex flex-wrap items-center gap-3 pt-2 font-mono-tech text-xs">
          <button type="button" onclick="App.nav.switchTab('sec-booking')" class="tap-active px-5 py-2.5 rounded-xl bg-[#FF5722] hover:bg-[#F4511E] text-black font-extrabold uppercase tracking-wider flex items-center gap-2 transition shadow-lg shadow-[#FF5722]/25">
            <span>DAFTAR SIMAKSI SEKARANG</span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
          </button>
          <button type="button" onclick="App.nav.switchTab('sec-telemetry')" class="tap-active px-4 py-2.5 rounded-xl bg-[#181C24] hover:bg-[#202632] border border-[#2D3340] text-stone-200 font-bold uppercase tracking-wider flex items-center gap-2 transition">
            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 00-9.78 2.096A4.001 4.001 0 003 15z"/></svg>
            <span>KONDISI CUACA & JALUR</span>
          </button>
          <button type="button" onclick="App.modal.open('ticketModal')" class="tap-active px-4 py-2.5 rounded-xl bg-[#181C24] hover:bg-[#202632] border border-[#2D3340] text-stone-200 font-bold uppercase tracking-wider flex items-center gap-2 transition">
            <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            <span>CEK STATUS E-TIKET</span>
          </button>
        </div>
      </div>

      <!-- 2. EXPEDITION KEY SPECS HUD (4-COLUMN GRID) -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-3 font-mono-tech">
        <div class="p-3.5 rounded-xl bg-[#121418] border border-[#1E2229] space-y-1">
          <span class="text-[9px] text-stone-400 uppercase tracking-wider block">// KETINGGIAN PUNCAK</span>
          <div class="flex items-baseline gap-1">
            <span class="text-xl sm:text-2xl font-black text-white">{{ $profile['mountain_elevation_num'] ?? '2.821' }}</span>
            <span class="text-xs text-[#FF5722] font-bold">MDPL</span>
          </div>
          <span class="text-[10px] text-stone-400 block">Atap Tertinggi ke-4 Jawa Barat</span>
        </div>

        <div class="p-3.5 rounded-xl bg-[#121418] border border-[#1E2229] space-y-1">
          <span class="text-[9px] text-stone-400 uppercase tracking-wider block">// KUOTA KONSERVASI</span>
          <div class="flex items-baseline gap-1">
            <span class="text-xl sm:text-2xl font-black text-white">{{ $todayQuota->max_quota ?? ($profile['default_daily_quota'] ?? 150) }}</span>
            <span class="text-xs text-emerald-400 font-bold">ORG/HARI</span>
          </div>
          <span class="text-[10px] text-stone-400 block">Batas Ketat Ekosistem Hutan</span>
        </div>

        <div class="p-3.5 rounded-xl bg-[#121418] border border-[#1E2229] space-y-1">
          <span class="text-[9px] text-stone-400 uppercase tracking-wider block">// PANJANG JALUR</span>
          <div class="flex items-baseline gap-1">
            <span class="text-xl sm:text-2xl font-black text-white">6,2</span>
            <span class="text-xs text-cyan-400 font-bold">KILOMETER</span>
          </div>
          <span class="text-[10px] text-stone-400 block">Trek Relatif Efisien & Terbina</span>
        </div>

        <div class="p-3.5 rounded-xl bg-[#121418] border border-[#1E2229] space-y-1">
          <span class="text-[9px] text-stone-400 uppercase tracking-wider block">// ESTIMASI TEMPUH</span>
          <div class="flex items-baseline gap-1">
            <span class="text-xl sm:text-2xl font-black text-white">6 - 8</span>
            <span class="text-xs text-amber-400 font-bold">JAM</span>
          </div>
          <span class="text-[10px] text-stone-400 block">Basecamp s/d Puncak Tower</span>
        </div>
      </div>

      <!-- 3. KEUNGGULAN JALUR SAMSARA (3 FEATURE CARDS) -->
      <div class="space-y-2.5">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2">
            <div class="w-2 h-2 rounded-sm bg-[#FF5722]"></div>
            <h2 class="font-mono-tech font-bold text-xs uppercase tracking-wider text-white">
              PESONA & KEUNGGULAN JALUR SAMSARA
            </h2>
          </div>
          <span class="font-mono-tech text-[10px] text-stone-400">EKSPEDISI RESMI</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-3.5 font-mono-tech">
          <!-- Card 1 -->
          <div class="p-4 rounded-xl bg-[#121418] border border-[#1E2229] hover:border-[#2C3342] transition space-y-2.5">
            <div class="w-8 h-8 rounded-lg bg-amber-500/10 border border-amber-500/30 flex items-center justify-center text-amber-400">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v2m0 14v2m9-9h-2M5 12H3m15.364-6.364l-1.414 1.414M7.05 16.95l-1.414 1.414M18.364 18.364l-1.414-1.414M7.05 7.05L5.636 5.636M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </div>
            <h3 class="font-display font-bold text-sm text-white leading-snug">
              Lautan Awan & Sunrise Segitiga Cikuray
            </h3>
            <p class="text-[11px] text-stone-400 leading-relaxed font-sans">
              Gunung Cikuray berbentuk kerucut simetris sempurna. Pada fajar pagi, bayangan segitiga raksasa Cikuray terproyeksi spektakuler di atas hamparan samudera awan putih Garut.
            </p>
          </div>

          <!-- Card 2 -->
          <div class="p-4 rounded-xl bg-[#121418] border border-[#1E2229] hover:border-[#2C3342] transition space-y-2.5">
            <div class="w-8 h-8 rounded-lg bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-center text-emerald-400">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
            </div>
            <h3 class="font-display font-bold text-sm text-white leading-snug">
              Hutan Lumut & Sumber Air Alami Pos 2
            </h3>
            <p class="text-[11px] text-stone-400 leading-relaxed font-sans">
              Menyusuri koridor vegetasi hutan lumut pegunungan yang teduh, sejuk, dan asri. Terdapat mata air pegunungan alami yang melimpah dan jernih di Pos 2 untuk perbekalan pendaki.
            </p>
          </div>

          <!-- Card 3 -->
          <div class="p-4 rounded-xl bg-[#121418] border border-[#1E2229] hover:border-[#2C3342] transition space-y-2.5">
            <div class="w-8 h-8 rounded-lg bg-[#FF5722]/10 border border-[#FF5722]/30 flex items-center justify-center text-[#FF5722]">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <h3 class="font-display font-bold text-sm text-white leading-snug">
              Shuttle Ojek Kebun Teh & SAR 24 Jam
            </h3>
            <p class="text-[11px] text-stone-400 leading-relaxed font-sans">
              Hemat stamina 2 jam perjalanan melalui bentang kebun teh menuju Pos 1 dengan armada ojek resmi. Dilengkapi pos komunikasi radio jalur dan tim rescue SAR standby 24 jam.
            </p>
          </div>
        </div>
      </div>

      <!-- 4. ALUR 4 LANGKAH REGISTRASI MANDIRI (HOW IT WORKS) -->
      <div class="p-5 rounded-2xl bg-[#121418] border border-[#1E2229] space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-[#1E2229]">
          <div class="flex items-center gap-2">
            <div class="w-2.5 h-2.5 rounded-sm bg-[#FF5722]"></div>
            <div>
              <span class="font-mono-tech text-[10px] text-[#FF5722] font-bold tracking-widest uppercase block leading-none">PANDUAN PENDAKIAN</span>
              <h2 class="font-display font-bold text-base text-white leading-tight">ALUR 4 LANGKAH RESERVASI SIMAKSI MANDIRI</h2>
            </div>
          </div>
          <button type="button" onclick="App.nav.switchTab('sec-booking')" class="text-[11px] font-mono-tech text-[#FF5722] hover:underline flex items-center gap-1 font-bold">
            <span>MULAI RESERVASI</span> →
          </button>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 font-mono-tech">
          <!-- Step 1 -->
          <div class="p-3.5 rounded-xl bg-[#16191F] border border-[#222731] space-y-1.5">
            <div class="flex items-center justify-between">
              <span class="w-6 h-6 rounded-md bg-[#FF5722]/20 text-[#FF5722] font-black text-xs flex items-center justify-center border border-[#FF5722]/30">1</span>
              <span class="text-[9px] text-stone-500 uppercase tracking-widest">LANGKAH 1</span>
            </div>
            <h4 class="font-bold text-xs text-white">Cek Jadwal & Kuota</h4>
            <p class="text-[10px] text-stone-400 font-sans leading-relaxed">
              Pantau sisa kuota harian secara live ({{ $profile['default_daily_quota'] ?? 150 }} slot/hari). Tentukan tanggal mulai naik dan estimasi turun rombongan.
            </p>
          </div>

          <!-- Step 2 -->
          <div class="p-3.5 rounded-xl bg-[#16191F] border border-[#222731] space-y-1.5">
            <div class="flex items-center justify-between">
              <span class="w-6 h-6 rounded-md bg-[#FF5722]/20 text-[#FF5722] font-black text-xs flex items-center justify-center border border-[#FF5722]/30">2</span>
              <span class="text-[9px] text-stone-500 uppercase tracking-widest">LANGKAH 2</span>
            </div>
            <h4 class="font-bold text-xs text-white">Manifest & Unggah KTP</h4>
            <p class="text-[10px] text-stone-400 font-sans leading-relaxed">
              Daftar solo ({{ $profile['min_climbers_per_group'] ?? 1 }} orang) hingga {{ $profile['max_climbers_per_group'] ?? 15 }} orang. KTP dikompresi otomatis & <strong>dihapus permanen dari server saat check-out</strong> (Privasi PDP).
            </p>
          </div>

          <!-- Step 3 -->
          <div class="p-3.5 rounded-xl bg-[#16191F] border border-[#222731] space-y-1.5">
            <div class="flex items-center justify-between">
              <span class="w-6 h-6 rounded-md bg-[#FF5722]/20 text-[#FF5722] font-black text-xs flex items-center justify-center border border-[#FF5722]/30">3</span>
              <span class="text-[9px] text-stone-500 uppercase tracking-widest">LANGKAH 3</span>
            </div>
            <h4 class="font-bold text-xs text-white">Pembayaran & E-Tiket</h4>
            <p class="text-[10px] text-stone-400 font-sans leading-relaxed">
              Transfer resmi dengan verifikasi kasir akurat. Terima kode booking unik dan QR Code e-tiket pendakian yang siap diunduh.
            </p>
          </div>

          <!-- Step 4 -->
          <div class="p-3.5 rounded-xl bg-[#16191F] border border-[#222731] space-y-1.5">
            <div class="flex items-center justify-between">
              <span class="w-6 h-6 rounded-md bg-[#FF5722]/20 text-[#FF5722] font-black text-xs flex items-center justify-center border border-[#FF5722]/30">4</span>
              <span class="text-[9px] text-stone-500 uppercase tracking-widest">LANGKAH 4</span>
            </div>
            <h4 class="font-bold text-xs text-white">Tap-In di Gerbang Basecamp</h4>
            <p class="text-[10px] text-stone-400 font-sans leading-relaxed">
              Tunjukkan QR Code di gerbang masuk basecamp untuk validasi cepat, ikuti briefing keselamatan, dan mulai pendakian.
            </p>
          </div>
        </div>
      </div>

      <!-- 5. WAYPOINT RUTE RINGKAS (BASECAMP S/D PUNCAK) -->
      <div class="p-5 rounded-2xl bg-[#121418] border border-[#1E2229] space-y-3 font-mono-tech">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2">
            <div class="w-2 h-2 rounded-sm bg-[#FF5722]"></div>
            <h3 class="font-bold text-xs uppercase tracking-wider text-white">
              TITIK POS & ELEVASI PENDAKIAN SAMSARA
            </h3>
          </div>
          <button type="button" onclick="App.nav.switchTab('sec-waypoints')" class="text-[10px] text-[#FF5722] hover:underline font-bold">
            LIHAT DETAIL ELEVASI POS →
          </button>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-6 gap-2 text-[11px] text-center pt-1">
          <div class="p-2.5 rounded-lg bg-[#16191F] border border-[#222731]">
            <span class="font-bold text-white block">Basecamp</span>
            <span class="text-[#FF5722] text-[10px] block">1.450 MDPL</span>
            <span class="text-stone-400 text-[9px] block">Registrasi & Parkir</span>
          </div>
          <div class="p-2.5 rounded-lg bg-[#16191F] border border-[#222731]">
            <span class="font-bold text-white block">Pos 1</span>
            <span class="text-[#FF5722] text-[10px] block">1.700 MDPL</span>
            <span class="text-stone-400 text-[9px] block">Drop Shuttle Ojek</span>
          </div>
          <div class="p-2.5 rounded-lg bg-[#16191F] border border-[#222731]">
            <span class="font-bold text-white block">Pos 2</span>
            <span class="text-cyan-400 text-[10px] block">1.950 MDPL</span>
            <span class="text-cyan-300 text-[9px] block">Mata Air Alami</span>
          </div>
          <div class="p-2.5 rounded-lg bg-[#16191F] border border-[#222731]">
            <span class="font-bold text-white block">Pos 3</span>
            <span class="text-[#FF5722] text-[10px] block">2.300 MDPL</span>
            <span class="text-stone-400 text-[9px] block">Hutan Lumut</span>
          </div>
          <div class="p-2.5 rounded-lg bg-[#16191F] border border-[#222731]">
            <span class="font-bold text-white block">Pos 4</span>
            <span class="text-[#FF5722] text-[10px] block">2.650 MDPL</span>
            <span class="text-stone-400 text-[9px] block">Area Camp Utama</span>
          </div>
          <div class="p-2.5 rounded-lg bg-[#FF5722]/10 border border-[#FF5722]/30 text-[#FF5722]">
            <span class="font-black text-white block">PUNCAK</span>
            <span class="text-[#FF5722] text-[10px] font-bold block">{{ $profile['mountain_elevation'] ?? '2.821 MDPL' }}</span>
            <span class="text-stone-300 text-[9px] block">Tower & Sunset</span>
          </div>
        </div>
      </div>

      <!-- 6. FASILITAS BASECAMP SAMSARA (6-GRID) -->
      <div class="space-y-2.5">
        <div class="flex items-center justify-between font-mono-tech">
          <div class="flex items-center gap-2">
            <div class="w-2 h-2 rounded-sm bg-[#FF5722]"></div>
            <h3 class="font-bold text-xs uppercase tracking-wider text-white">
              FASILITAS BASECAMP 24 JAM
            </h3>
          </div>
          <span class="text-[10px] text-stone-400">KENYAMANAN PENDAKI</span>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2.5 font-mono-tech">
          <!-- Facility 1: Mushola -->
          <div class="p-3 rounded-xl bg-[#121418] border border-[#1E2229] space-y-2">
            <div class="w-7 h-7 rounded-lg bg-amber-500/10 border border-amber-500/30 flex items-center justify-center text-amber-400">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            </div>
            <div>
              <span class="font-bold text-xs text-stone-200 block">Mushola 24 Jam</span>
              <span class="text-[9px] text-stone-400 block">Air wudhu pegunungan</span>
            </div>
          </div>

          <!-- Facility 2: Parkir -->
          <div class="p-3 rounded-xl bg-[#121418] border border-[#1E2229] space-y-2">
            <div class="w-7 h-7 rounded-lg bg-cyan-500/10 border border-cyan-500/30 flex items-center justify-center text-cyan-400">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/></svg>
            </div>
            <div>
              <span class="font-bold text-xs text-stone-200 block">Parkir Aman</span>
              <span class="text-[9px] text-stone-400 block">Motor & mobil dijaga</span>
            </div>
          </div>

          <!-- Facility 3: P3K & SAR -->
          <div class="p-3 rounded-xl bg-[#121418] border border-[#1E2229] space-y-2">
            <div class="w-7 h-7 rounded-lg bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-center text-emerald-400">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            </div>
            <div>
              <span class="font-bold text-xs text-stone-200 block">Posko P3K SAR</span>
              <span class="text-[9px] text-stone-400 block">Siaga darurat 24 jam</span>
            </div>
          </div>

          <!-- Facility 4: Shuttle Ojek -->
          <div class="p-3 rounded-xl bg-[#121418] border border-[#1E2229] space-y-2">
            <div class="w-7 h-7 rounded-lg bg-[#FF5722]/10 border border-[#FF5722]/30 flex items-center justify-center text-[#FF5722]">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <div>
              <span class="font-bold text-xs text-stone-200 block">Shuttle Ojek</span>
              <span class="text-[9px] text-stone-400 block">Basecamp ⇄ Pos 1</span>
            </div>
          </div>

          <!-- Facility 5: Toilet -->
          <div class="p-3 rounded-xl bg-[#121418] border border-[#1E2229] space-y-2">
            <div class="w-7 h-7 rounded-lg bg-sky-500/10 border border-sky-500/30 flex items-center justify-center text-sky-400">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
            </div>
            <div>
              <span class="font-bold text-xs text-stone-200 block">Toilet & Bilas</span>
              <span class="text-[9px] text-stone-400 block">Air bersih pegunungan</span>
            </div>
          </div>

          <!-- Facility 6: SAR Medis -->
          <div class="p-3 rounded-xl bg-[#121418] border border-[#1E2229] space-y-2">
            <div class="w-7 h-7 rounded-lg bg-rose-500/10 border border-rose-500/30 flex items-center justify-center text-rose-400">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
              <span class="font-bold text-xs text-stone-200 block">SAR & Medis</span>
              <span class="text-[9px] text-stone-400 block">P3K, oksigen & rescue</span>
            </div>
          </div>
        </div>
      </div>

      <!-- 7. SOP & ETIKA KONSERVASI LINGKUNGAN -->
      <div class="p-4 rounded-xl bg-[#121418] border border-[#202530] font-mono-tech space-y-2 text-xs">
        <div class="flex items-center gap-2">
          <div class="w-5 h-5 rounded bg-amber-500/15 border border-amber-500/30 flex items-center justify-center text-amber-400 shrink-0">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
          </div>
          <span class="font-bold text-white uppercase tracking-wider text-[11px] font-mono-tech">// SOP & PRINSIP KONSERVASI PENDAKIAN SAMSARA</span>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-2.5 text-[10px] text-stone-300 font-sans">
          <div class="p-2 rounded bg-[#161920] border border-[#222733] space-y-1">
            <strong class="text-emerald-400 font-mono-tech block">1. ZERO WASTE (SAMPAH DIBAWA TURUN)</strong>
            <span>Seluruh bungkus logistik, botol plastik, dan puntung rokok wajib dibawa kembali ke pos basecamp untuk diverifikasi petugas.</span>
          </div>
          <div class="p-2 rounded bg-[#161920] border border-[#222733] space-y-1">
            <strong class="text-amber-400 font-mono-tech block">2. KESIAPSIAGAAN MEDIS & KTP</strong>
            <span>Wajib mencantumkan kontak darurat keluarga di rumah dan riwayat penyakit bawaan agar tim SAR dapat bertindak sigap jika darurat.</span>
          </div>
          <div class="p-2 rounded bg-[#161920] border border-[#222733] space-y-1">
            <strong class="text-cyan-400 font-mono-tech block">3. LINDUNGI EKOSISTEM HUTAN LUMUT</strong>
            <span>Dilarang membuat api unggun di sembarang tempat, merusak dahan tanaman lumut purba, atau memetik bunga edelweiss langka.</span>
          </div>
        </div>
      </div>

      <!-- 8. BOTTOM CALL-TO-ACTION (CTA) BANNER -->
      <div class="p-6 rounded-2xl bg-gradient-to-r from-[#181C24] via-[#14171E] to-[#181C24] border border-[#FF5722]/40 text-center space-y-3 shadow-xl">
        <span class="font-mono-tech text-[10px] text-[#FF5722] font-bold uppercase tracking-widest block">
          // SIAP MENAPAKI ATAP MEGAH GARUT?
        </span>
        <h2 class="font-display font-black text-xl sm:text-2xl text-white tracking-tight uppercase">
          AMANKAN KUOTA & PERMIT PENDAKIAN ANDA SEKARANG
        </h2>
        <p class="text-xs text-stone-400 max-w-xl mx-auto font-sans leading-relaxed">
          Pendaftaran online mandiri dibuka setiap saat dengan konfirmasi kuota transparan. Dapatkan pengalaman mendaki yang aman, nyaman, dan bertanggung jawab.
        </p>
        <div class="pt-1 font-mono-tech">
          <button type="button" onclick="App.nav.switchTab('sec-booking')" class="tap-active px-6 py-3 rounded-xl bg-[#FF5722] hover:bg-[#F4511E] text-black font-extrabold text-xs uppercase tracking-wider inline-flex items-center gap-2 transition shadow-lg shadow-[#FF5722]/30">
            <span>MULAI RESERVASI PERMIT SEKARANG</span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
          </button>
        </div>
      </div>

    </div>

    <!-- ======================================================== -->
    <!-- TAB 1: RESERVASI & TIKET SIMAKSI (#sec-booking)          -->
    <!-- ======================================================== -->
    <div id="sec-booking" class="hidden space-y-4">
      <div class="grid grid-cols-1 md:grid-cols-12 gap-5 items-start">
        
        <!-- Left Column: Hero, Quota HUD, Quick Highlights (md:col-span-7) -->
        <div class="md:col-span-7 space-y-3.5">
          <!-- Quota HUD Banner -->
          <div class="p-3.5 rounded-xl bg-[#121418] border border-[#1E2229] flex items-center justify-between font-mono-tech text-xs shadow-lg">
            <div class="flex items-center gap-2">
              <span class="relative flex h-2.5 w-2.5">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
              </span>
              <span class="text-stone-300 text-xs font-bold tracking-wider">KUOTA HARIAN RESMI</span>
            </div>
            <div class="flex items-center gap-2">
              <span id="heroRemainingQuota" class="text-[#FF5722] font-bold text-base">{{ $todayQuota->remaining_quota }} TERSISA</span>
              <span class="text-stone-500 text-xs">/ {{ $todayQuota->max_quota }}</span>
            </div>
          </div>

          <!-- Headline Card -->
          <div class="p-4 rounded-xl bg-[#121418] border border-[#1E2229] space-y-2">
            <div class="flex items-center gap-2">
              <span class="px-2 py-0.5 rounded text-[9px] font-mono-tech font-bold bg-[#FF5722]/15 text-[#FF5722] border border-[#FF5722]/30 uppercase">
                JALUR {{ strtoupper($profile['expedition_name'] ?? 'SAMSARA') }}
              </span>
              <span class="font-mono-tech text-[10px] text-stone-400">PUNCAK {{ $profile['mountain_elevation'] ?? '2.821 MDPL' }}</span>
            </div>
            <h1 class="font-display font-black text-xl sm:text-2xl text-white tracking-tight uppercase leading-tight">
              EXPEDITION PERMIT & LOGISTIK PENDAKIAN
            </h1>
            <p class="text-xs text-stone-400 leading-relaxed">
              Sistem reservasi mandiri izin SIMAKSI resmi {{ $profile['mountain_name'] ?? 'Gunung Cikuray' }} via {{ $profile['basecamp_name'] ?? 'Basecamp Samsara' }}. Lengkap dengan kompresi foto KTP otomatis, opsi shuttle ojek kebun teh, dan jaminan keamanan tim SAR 24 jam.
            </p>

            <!-- Quick Mini Telemetry Pills -->
            <div class="grid grid-cols-3 gap-2 pt-2 border-t border-[#1E2229] text-center font-mono-tech">
              <div onclick="App.nav.switchTab('sec-telemetry')" class="p-2 rounded bg-[#16191F] hover:bg-[#1C2028] border border-[#222731] cursor-pointer transition">
                <span class="text-[9px] text-stone-400 block uppercase">STATUS JALUR</span>
                <span class="text-[11px] font-bold text-emerald-400 block truncate">{{ $trailCondition ? strtoupper($trailCondition->status) : 'TERBUKA' }}</span>
              </div>
              <div onclick="App.nav.switchTab('sec-telemetry')" class="p-2 rounded bg-[#16191F] hover:bg-[#1C2028] border border-[#222731] cursor-pointer transition">
                <span class="text-[9px] text-stone-400 block uppercase">AIR POS 2</span>
                <span class="text-[11px] font-bold text-cyan-400 block truncate">MELIMPAH</span>
              </div>
              <div onclick="App.nav.switchTab('sec-telemetry')" class="p-2 rounded bg-[#16191F] hover:bg-[#1C2028] border border-[#222731] cursor-pointer transition">
                <span class="text-[9px] text-stone-400 block uppercase">SUHU DINI HARI</span>
                <span class="text-[11px] font-bold text-amber-300 block truncate">{{ $trailCondition ? $trailCondition->night_temp_celsius . '°C' : '9°C' }}</span>
              </div>
            </div>
          </div>

          <!-- Quick Actions Bar (Cek Status Tiket & Lacak) -->
          <div class="p-3.5 rounded-xl bg-[#101216] border border-[#202530] flex flex-col sm:flex-row items-center justify-between gap-3 font-mono-tech text-xs">
            <div class="flex items-center gap-2.5">
              <div class="w-8 h-8 rounded-lg bg-[#FF5722]/10 border border-[#FF5722]/30 flex items-center justify-center text-[#FF5722] shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
              </div>
              <div>
                <span class="font-bold text-white block text-[11px]">SUDAH PUNYA KODE TIKET?</span>
                <span class="text-[10px] text-stone-400 block">Lacak izin, bukti transfer, atau perbaikan berkas Anda.</span>
              </div>
            </div>
            <button type="button" onclick="App.modal.open('ticketModal')" class="tap-active w-full sm:w-auto px-3.5 py-2 rounded-lg bg-[#181C24] hover:bg-[#202632] border border-[#2E3544] text-[#FF5722] font-bold text-xs uppercase tracking-wider transition shrink-0">
              CEK STATUS TIKET →
            </button>
          </div>
        </div>

        <!-- Right Column: Kalkulator Reservasi (md:col-span-5) -->
        <div class="md:col-span-5">
          <div class="bg-[#121418] border border-[#20242C] rounded-xl p-4 space-y-3.5 shadow-xl">
            <div class="flex items-center justify-between pb-3 border-b border-[#1E2229]">
              <div class="flex items-center gap-2">
                <div class="w-2 h-2 rounded-sm bg-[#FF5722]"></div>
                <h2 class="font-mono-tech font-bold text-xs uppercase tracking-wider text-white">KALKULATOR RESERVASI</h2>
              </div>
              <span class="font-mono-tech text-[10px] text-stone-400">INSTANT QUOTE</span>
            </div>

            <!-- Input 1: Tanggal Pendakian -->
            <div>
              <label for="bookDate" class="block font-mono-tech text-[10px] text-stone-400 uppercase tracking-wider mb-1">
                TANGGAL PENDAKIAN
              </label>
              <input type="date" id="bookDate" value="{{ date('Y-m-d') }}" min="{{ date('Y-m-d') }}" class="w-full bg-[#181B22] border border-[#2A303C] rounded-lg px-3 py-2 text-xs font-mono-tech text-white focus:outline-none focus:border-[#FF5722] transition">
            </div>

            <!-- Input 2: Jumlah Anggota Rombongan (Stepper) -->
            <div>
              <div class="flex justify-between items-center mb-1">
                <label class="font-mono-tech text-[10px] text-stone-400 uppercase tracking-wider">
                  JUMLAH PENDAKI (SOLO / ROMBONGAN)
                </label>
                <span id="climberCountLabel" class="font-mono-tech text-xs font-bold text-[#FF5722]">4 ORANG</span>
              </div>
              <div class="flex items-center bg-[#181B22] border border-[#2A303C] rounded-lg overflow-hidden">
                <button type="button" onclick="App.booking.adjustClimbers(-1)" class="tap-active w-12 py-2.5 bg-[#20242C] text-white hover:bg-[#282E38] font-mono-tech font-bold text-base transition flex items-center justify-center">
                  -
                </button>
                <div id="climberCountBox" class="flex-1 text-center font-mono-tech font-bold text-sm text-white py-2">
                  4
                </div>
                <button type="button" onclick="App.booking.adjustClimbers(1)" class="tap-active w-12 py-2.5 bg-[#20242C] text-white hover:bg-[#282E38] font-mono-tech font-bold text-base transition flex items-center justify-center">
                  +
                </button>
              </div>
              <p class="text-[9px] font-mono-tech text-stone-500 mt-1">*Mendukung pendakian solo ({{ $profile['min_climbers_per_group'] ?? 1 }} orang) hingga rombongan (maks. {{ $profile['max_climbers_per_group'] ?? 15 }} orang).</p>
            </div>

            <!-- Input 3: Tambahan Layanan Logistik (Multi-Unit Stepper) -->
            <div class="space-y-2 pt-1 border-t border-[#1E2229]">
              <span class="block font-mono-tech text-[10px] text-stone-400 uppercase tracking-wider">LAYANAN LOGISTIK BASECAMP</span>
              
              <!-- Service 1: Shuttle Ojek Stepper -->
              <div class="p-2.5 rounded-lg bg-[#16191F] border border-[#222731] space-y-1.5">
                <div class="flex items-center justify-between">
                  <div>
                    <span class="block text-xs font-bold text-stone-200">Shuttle Ojek Trail</span>
                    <span class="text-[9px] font-mono-tech text-stone-400">{{ $tariffs['ojek_route_name'] ?? 'Basecamp → Pos 1' }} (Rp {{ number_format($tariffs['ojek_fee_per_trip'], 0, ',', '.') }} / armada)</span>
                  </div>
                  <span id="bookOjekTotalLabel" class="font-mono-tech text-xs font-bold text-[#FF5722]">Rp 0</span>
                </div>

                <div class="flex items-center justify-between pt-1">
                  <span class="text-[9px] font-mono-tech text-stone-500">1 Motor = 1 Pendaki</span>
                  
                  <div class="flex items-center bg-[#181B22] border border-[#2A303C] rounded-lg overflow-hidden">
                    <button type="button" onclick="App.booking.adjustOjek(-1)" class="tap-active w-8 py-1 bg-[#20242C] text-white hover:bg-[#282E38] font-mono-tech font-bold text-xs transition flex items-center justify-center">-</button>
                    <div id="bookOjekCountBox" class="w-9 text-center font-mono-tech font-bold text-xs text-white py-1">0</div>
                    <button type="button" onclick="App.booking.adjustOjek(1)" class="tap-active w-8 py-1 bg-[#20242C] text-white hover:bg-[#282E38] font-mono-tech font-bold text-xs transition flex items-center justify-center">+</button>
                  </div>
                </div>
              </div>

              <!-- Service 2: Parkir Kendaraan Pribadi (Checkbox Toggle & Multi-Vehicle Stepper) -->
              <div class="p-2.5 rounded-lg bg-[#16191F] border border-[#222731] space-y-2">
                <label class="flex items-center justify-between cursor-pointer select-none">
                  <div class="flex items-center gap-2.5">
                    <input type="checkbox" id="bookHasVehicle" onchange="App.booking.toggleVehicle(this.checked)" class="w-4 h-4 rounded bg-[#181B22] border-[#2A303C] text-[#FF5722] focus:ring-0 focus:ring-offset-0 cursor-pointer">
                    <div>
                      <span class="block text-xs font-bold text-stone-200">Bawa Kendaraan Pribadi?</span>
                      <span class="text-[9px] font-mono-tech text-stone-400">Titip parkir aman 24 jam basecamp</span>
                    </div>
                  </div>
                  <span id="bookParkingTotalLabel" class="font-mono-tech text-xs font-bold text-cyan-400">Rp 0</span>
                </label>

                <!-- Pilihan Kendaraan & Jumlah (Muncul jika diceklis) -->
                <div id="bookVehicleOptions" class="hidden pt-2 border-t border-[#1E2229] space-y-2">
                  <!-- Motor -->
                  <div class="flex items-center justify-between bg-[#121418] p-2 rounded-lg border border-[#1E2229]">
                    <div>
                      <span class="block text-[11px] font-bold text-stone-200">Sepeda Motor</span>
                      <span class="text-[9px] font-mono-tech text-stone-400">Rp {{ number_format($tariffs['parking_motor_fee'], 0, ',', '.') }} / unit</span>
                    </div>
                    <div class="flex items-center bg-[#181B22] border border-[#2A303C] rounded-lg overflow-hidden">
                      <button type="button" onclick="App.booking.adjustMotorcycle(-1)" class="tap-active w-7 py-1 bg-[#20242C] text-white hover:bg-[#282E38] font-mono-tech font-bold text-xs flex items-center justify-center">-</button>
                      <div id="bookMotorCountBox" class="w-8 text-center font-mono-tech font-bold text-xs text-white py-1">0</div>
                      <button type="button" onclick="App.booking.adjustMotorcycle(1)" class="tap-active w-7 py-1 bg-[#20242C] text-white hover:bg-[#282E38] font-mono-tech font-bold text-xs flex items-center justify-center">+</button>
                    </div>
                  </div>

                  <!-- Mobil -->
                  <div class="flex items-center justify-between bg-[#121418] p-2 rounded-lg border border-[#1E2229]">
                    <div>
                      <span class="block text-[11px] font-bold text-stone-200">Mobil Pribadi</span>
                      <span class="text-[9px] font-mono-tech text-stone-400">Rp {{ number_format($tariffs['parking_car_fee'], 0, ',', '.') }} / unit</span>
                    </div>
                    <div class="flex items-center bg-[#181B22] border border-[#2A303C] rounded-lg overflow-hidden">
                      <button type="button" onclick="App.booking.adjustCar(-1)" class="tap-active w-7 py-1 bg-[#20242C] text-white hover:bg-[#282E38] font-mono-tech font-bold text-xs flex items-center justify-center">-</button>
                      <div id="bookCarCountBox" class="w-8 text-center font-mono-tech font-bold text-xs text-white py-1">0</div>
                      <button type="button" onclick="App.booking.adjustCar(1)" class="tap-active w-7 py-1 bg-[#20242C] text-white hover:bg-[#282E38] font-mono-tech font-bold text-xs flex items-center justify-center">+</button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Kebijakan Deposit Sampah Info Pill -->
              @if($tariffs['trash_deposit_enabled'])
                <div class="flex items-center justify-between text-[10px] px-2.5 py-1.5 rounded-lg bg-emerald-500/10 border border-emerald-500/20 text-emerald-400">
                  <div class="flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>Deposit Sampah (Refundable):</span>
                  </div>
                  <span class="font-bold">Rp {{ number_format($tariffs['trash_deposit_amount'], 0, ',', '.') }}</span>
                </div>
              @else
                <div class="flex items-center justify-between text-[10px] px-2.5 py-1.5 rounded-lg bg-stone-800/40 border border-[#202530] text-stone-400">
                  <div class="flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-stone-500"></span>
                    <span>Deposit Sampah:</span>
                  </div>
                  <span class="text-stone-300 font-bold uppercase text-[9px]">Nonaktif / Bebas Biaya</span>
                </div>
              @endif
            </div>

            <!-- Price Breakdown Card -->
            <div class="p-3 rounded-lg bg-[#0E1013] border border-[#1A1E26] space-y-1 font-mono-tech text-xs">
              <div class="flex justify-between text-stone-400 text-[11px]">
                <span id="txtSimaksiSub">SIMAKSI (4 Org):</span>
                <span id="valSimaksiSub" class="text-stone-200">Rp 100.000</span>
              </div>
              <div class="flex justify-between text-stone-400 text-[11px]">
                <span id="txtOjekSub">Ojek (0 Armada):</span>
                <span id="valOjekSub" class="text-stone-200">Rp 0</span>
              </div>
              <div class="flex justify-between text-stone-400 text-[11px] hidden" id="rowParkirSub">
                <span id="txtParkirSub">Parkir Kendaraan:</span>
                <span id="valParkirSub" class="text-cyan-400 font-bold">Rp 0</span>
              </div>
              <div class="pt-2 border-t border-[#1E2229] flex justify-between items-center text-sm">
                <span class="font-bold text-white">TOTAL ESTIMASI:</span>
                <span id="valTotalSub" class="font-bold text-[#FF5722] text-base">Rp 100.000</span>
              </div>
            </div>

            <!-- CTA Book Button -->
            <button type="button" onclick="App.booking.openRegistrationModal()" class="tap-active w-full py-3 bg-[#FF5722] hover:bg-[#F4511E] text-black font-mono-tech font-bold text-xs uppercase tracking-wider rounded-lg transition shadow-lg shadow-[#FF5722]/20 flex items-center justify-center gap-2">
              <span>DAFTAR PERMIT SIMAKSI SEKARANG</span>
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </button>
          </div>
        </div>

      </div>
    </div>

    <!-- ======================================================== -->
    <!-- TAB 2: TELEMETRY & KONDISI JALUR (#sec-telemetry)        -->
    <!-- ======================================================== -->
    <div id="sec-telemetry" class="hidden space-y-4">
      <div class="flex items-center justify-between pb-2 border-b border-[#1E2229]">
        <div class="flex items-center gap-2">
          <span class="w-2.5 h-2.5 rounded-sm bg-[#FF5722]"></span>
          <h2 class="font-mono-tech font-bold text-xs uppercase tracking-wider text-white">LIVE TELEMETRY LAPANGAN & IKLIM CIKURAY</h2>
        </div>
        <span class="font-mono-tech text-[10px] text-stone-400">POS SAR SAMSARA</span>
      </div>

      <!-- Telemetry 4 Cards Grid -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <!-- Status Jalur -->
        <div class="p-3.5 rounded-xl bg-[#121418] border border-[#1E2229]">
          <span class="font-mono-tech text-[10px] text-stone-400 uppercase block">STATUS JALUR</span>
          <span id="telemetryTrailStatus" class="font-mono-tech text-sm font-bold text-emerald-400 mt-1 block">
            {{ $trailCondition ? strtoupper($trailCondition->status) : 'TERBUKA (NORMAL)' }}
          </span>
          <span class="text-[10px] text-stone-400 block mt-1.5 leading-snug">Jalur bersih dari pohon tumbang & aman dilalui</span>
        </div>

        <!-- Sumber Air Pos 2 -->
        <div class="p-3.5 rounded-xl bg-[#121418] border border-[#1E2229]">
          <span class="font-mono-tech text-[10px] text-stone-400 uppercase block">MATA AIR POS 2</span>
          <span id="telemetryWaterStatus" class="font-mono-tech text-sm font-bold text-cyan-400 mt-1 block">
            {{ $trailCondition ? strtoupper($trailCondition->pos2_water_status) : 'MELIMPAH (12 L/mnt)' }}
          </span>
          <span class="text-[10px] text-stone-400 block mt-1.5 leading-snug">Sumber air terakhir sebelum vegetasi puncak</span>
        </div>

        <!-- Suhu Shelter Camp -->
        <div class="p-3.5 rounded-xl bg-[#121418] border border-[#1E2229]">
          <span class="font-mono-tech text-[10px] text-stone-400 uppercase block">SUHU DINI HARI</span>
          <span id="telemetryNightTemp" class="font-mono-tech text-sm font-bold text-amber-300 mt-1 block">
            {{ $trailCondition ? $trailCondition->night_temp_celsius . '°C' : '9°C (Dini Hari)' }}
          </span>
          <span class="text-[10px] text-stone-400 block mt-1.5 leading-snug">Wajib bawa jaket windproof & sleeping bag</span>
        </div>

        <!-- Kondisi Tanah -->
        <div class="p-3.5 rounded-xl bg-[#121418] border border-[#1E2229]">
          <span class="font-mono-tech text-[10px] text-stone-400 uppercase block">KONDISI TANAH</span>
          <span id="telemetrySoilStatus" class="font-mono-tech text-sm font-bold text-stone-200 mt-1 block">
            {{ $trailCondition ? strtoupper($trailCondition->soil_condition) : 'PADAT & KERING' }}
          </span>
          <span class="text-[10px] text-stone-400 block mt-1.5 leading-snug">Grip sepatu gunung standar trekking optimal</span>
        </div>
      </div>

      <!-- Radio Darurat & SOP SAR Basecamp -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="p-4 rounded-xl bg-[#121418] border border-[#1E2229] space-y-2 font-mono-tech text-xs">
          <span class="text-[#FF5722] font-bold block uppercase tracking-wider">// KOMUNIKASI DARURAT SAR:</span>
          <div class="space-y-1.5 text-stone-300">
            <div class="flex justify-between items-center py-1 border-b border-[#1E2229]">
              <span class="text-stone-400">Frekuensi Radio HT:</span>
              <span class="text-white font-bold">{{ $profile['sar_frequency'] ?? '149.250 MHz' }} (Basecamp)</span>
            </div>
            <div class="flex justify-between items-center py-1 border-b border-[#1E2229]">
              <span class="text-stone-400">Call Center Ranger:</span>
              <a href="tel:{{ $profile['emergency_hotline'] ?? '+6282122334455' }}" class="text-[#FF5722] font-bold hover:underline">{{ $profile['emergency_hotline'] ?? '+62 821-2233-4455' }}</a>
            </div>
            <div class="flex justify-between items-center py-1">
              <span class="text-stone-400">Pos Evakuasi Medis:</span>
              <span class="text-emerald-400 font-bold">Pos 00 Basecamp</span>
            </div>
          </div>
        </div>

        <div class="p-4 rounded-xl bg-[#121418] border border-[#1E2229] space-y-2 text-xs">
          <span class="text-white font-bold block font-mono-tech uppercase tracking-wider">// PANDUAN KESELAMATAN PENDAKI:</span>
          <p class="text-stone-400 leading-relaxed text-[11px]">
            Setiap pendaki wajib melapor di Pos Gerbang sebelum naik dan setelah turun. Dilarang membuat api unggun di area semak kering dan wajib membawa kembali seluruh sampah kemasan ke basecamp
            @if(!empty($tariffs['trash_deposit_enabled']))
              untuk mencairkan jaminan sampah Rp {{ number_format($tariffs['trash_deposit_amount'] ?? 50000, 0, ',', '.') }}.
            @else
              untuk menjaga kelestarian ekosistem kawasan konservasi.
            @endif
          </p>
        </div>
      </div>
    </div>

    <!-- ======================================================== -->
    <!-- TAB 3: TACTICAL WAYPOINT LOG (#sec-waypoints)            -->
    <!-- ======================================================== -->
    <div id="sec-waypoints" class="hidden space-y-4">
      <div class="flex items-center justify-between pb-2 border-b border-[#1E2229]">
        <div class="flex items-center gap-2">
          <span class="w-2.5 h-2.5 rounded-sm bg-[#FF5722]"></span>
          <h2 class="font-mono-tech font-bold text-xs uppercase tracking-wider text-white">TACTICAL WAYPOINT LOG (BASECAMP → PUNCAK)</h2>
        </div>
        <span class="font-mono-tech text-[10px] text-stone-400">TOTAL 7.8 KM • 8 POS</span>
      </div>

      <!-- Elevation Profile Summary Bar -->
      <div class="p-3.5 bg-[#121418] border border-[#1E2229] rounded-xl grid grid-cols-2 sm:grid-cols-4 gap-3 text-center font-mono-tech text-xs">
        <div>
          <span class="text-[9px] text-stone-400 block uppercase">ELEVASI AWAL</span>
          <span class="font-bold text-white text-sm">{{ $profile['basecamp_elevation'] ?? '1.450 MDPL' }}</span>
          <span class="text-[9px] text-stone-500 block">Pos 00 Basecamp</span>
        </div>
        <div>
          <span class="text-[9px] text-stone-400 block uppercase">ELEVASI PUNCAK</span>
          <span class="font-bold text-[#FF5722] text-sm">{{ $profile['mountain_elevation'] ?? '2.821 MDPL' }}</span>
          <span class="text-[9px] text-stone-500 block">{{ $profile['peak_name'] ?? 'Puncak Triangulasi' }}</span>
        </div>
        <div>
          <span class="text-[9px] text-stone-400 block uppercase">GAIN ELEVASI</span>
          <span class="font-bold text-emerald-400 text-sm">+1.371 M</span>
          <span class="text-[9px] text-stone-500 block">Total Tanjakan</span>
        </div>
        <div>
          <span class="text-[9px] text-stone-400 block uppercase">ESTIMASI WAKTU</span>
          <span class="font-bold text-cyan-400 text-sm">6 - 7 JAM</span>
          <span class="text-[9px] text-stone-500 block">Pace Normal</span>
        </div>
      </div>

      <!-- Waypoints List Container -->
      <div id="waypointContainer" class="space-y-0 relative max-w-2xl mx-auto pt-2">
        <!-- Rendered by App.renderWaypoints() -->
      </div>
    </div>

    <!-- ======================================================== -->
    <!-- TAB 4: LAYANAN LOGISTIK & FASILITAS (#sec-logistik)      -->
    <!-- ======================================================== -->
    <div id="sec-logistik" class="hidden space-y-4">
      <div class="flex items-center justify-between pb-2 border-b border-[#1E2229]">
        <div class="flex items-center gap-2">
          <span class="w-2.5 h-2.5 rounded-sm bg-[#FF5722]"></span>
          <h2 class="font-mono-tech font-bold text-xs uppercase tracking-wider text-white">LAYANAN LOGISTIK & TARIF RESMI BASECAMP</h2>
        </div>
        <span class="font-mono-tech text-[10px] text-stone-400">TRANSPARAN & RESMI</span>
      </div>

      <!-- Logistics 4 Services Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Service 1: Shuttle Ojek Trail -->
        <div class="p-4 rounded-xl bg-[#121418] border border-[#1E2229] space-y-2">
          <div class="flex justify-between items-start">
            <div>
              <h4 class="font-display font-bold text-sm text-white">Shuttle Ojek Trail {{ $profile['basecamp_name'] ?? 'Samsara' }}</h4>
              <span class="font-mono-tech text-[10px] text-stone-400">Rute: {{ $tariffs['ojek_route_name'] ?? 'Basecamp (1.450M) → Pos 1 (1.780M)' }}</span>
            </div>
            <span class="font-mono-tech text-xs font-bold text-[#FF5722]">Rp {{ number_format($tariffs['ojek_fee_per_trip'], 0, ',', '.') }} / trip</span>
          </div>
          <p class="text-xs text-stone-400 leading-relaxed">
            Memotong jalan makadam berbatu perkebunan teh sepanjang 2.1 KM. Menghemat tenaga dan waktu tempuh hingga 45 menit sebelum memasuki trek hutan.
          </p>
        </div>

        <!-- Service 2: Porter Lokal -->
        <div class="p-4 rounded-xl bg-[#121418] border border-[#1E2229] space-y-2">
          <div class="flex justify-between items-start">
            <div>
              <h4 class="font-display font-bold text-sm text-white">Porter Tim Lokal Bersertifikasi</h4>
              <span class="font-mono-tech text-[10px] text-stone-400">Beban Maksimal: 20 KG per Porter</span>
            </div>
            <span class="font-mono-tech text-xs font-bold text-stone-200">Rp {{ number_format($tariffs['porter_fee_per_trip'] ?? 350000, 0, ',', '.') }} / trip (2H1M)</span>
          </div>
          <p class="text-xs text-stone-400 leading-relaxed">
            Warga lokal berpengalaman dengan kemampuan navigasi gunung. Membantu angkut logistik tenda, air, dan peralatan masak ke Pos 4 Camp.
          </p>
        </div>

        <!-- Service 4: Penitipan Kendaraan Basecamp -->
        <div class="p-4 rounded-xl bg-[#121418] border border-[#1E2229] space-y-2">
          <div class="flex justify-between items-start">
            <div>
              <h4 class="font-display font-bold text-sm text-white">Parkir & Penitipan Kendaraan 24 Jam</h4>
              <span class="font-mono-tech text-[10px] text-stone-400">Area Beratap & Pengawasan CCTV</span>
            </div>
            <span class="font-mono-tech text-xs font-bold text-stone-200">Motor Rp {{ number_format($tariffs['parking_motor_fee'], 0, ',', '.') }} • Mobil Rp {{ number_format($tariffs['parking_car_fee'], 0, ',', '.') }}</span>
          </div>
          <p class="text-xs text-stone-400 leading-relaxed">
            Keamanan kendaraan Anda terjamin selama mendaki dengan pos jaga ranger 24 jam dan penitipan helm/jaket gratis.
          </p>
        </div>
      </div>

      <!-- Action: Quick switch back to booking -->
      <div class="p-4 bg-[#121418] border border-[#202530] rounded-xl flex flex-col sm:flex-row items-center justify-between gap-3 text-center sm:text-left">
        <div>
          <span class="font-bold text-white text-xs block">SIAP MENDAKI CIKURAY BERSAMA SAMSARA?</span>
          <span class="text-[11px] text-stone-400">Pesan tiket SIMAKSI online dan sertakan layanan shuttle ojek atau parkir kendaraan langsung.</span>
        </div>
        <button type="button" onclick="App.nav.switchTab('sec-booking')" class="tap-active px-4 py-2.5 bg-[#FF5722] hover:bg-[#F4511E] text-black font-bold font-mono-tech text-xs uppercase rounded-lg transition shrink-0">
          BUAT RESERVASI SEKARANG →
        </button>
      </div>
    </div>

    <!-- ======================================================== -->
    <!-- BASECAMP FOOTER INFO                                     -->
    <!-- ======================================================== -->
    <footer class="pt-6 pb-4 border-t border-[#1E2229] text-center space-y-1.5 font-mono-tech text-[10px] text-stone-400">
      <div class="flex items-center justify-center gap-1.5 text-stone-300">
        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
        <span>POST SAR {{ strtoupper($profile['basecamp_name'] ?? 'BASECAMP SAMSARA') }} AKTIF 24 JAM</span>
      </div>
      <p>Radio Darurat: {{ $profile['sar_frequency'] ?? '149.250 MHz' }} • WhatsApp Admin: {{ $profile['admin_whatsapp_display'] ?? '+62 821-2233-4455' }}</p>
      <p class="text-stone-500">© {{ date('Y') }} {{ strtoupper($profile['expedition_name'] ?? 'SAMSARA EXPEDITION') }}. LINDUNGI KELESTARIAN HUTAN {{ strtoupper($profile['mountain_name'] ?? 'CIKURAY') }}.</p>
    </footer>

  </main>

  <!-- ======================================================== -->
  <!-- 7. FIXED MOBILE BOTTOM NAVIGATION BAR (MOBILE ONLY)      -->
  <!-- ======================================================== -->
  <nav class="fixed bottom-0 inset-x-0 z-50 bg-[#0E1013]/95 backdrop-blur-lg border-t border-[#1E2229] safe-pb md:hidden">
    <div class="max-w-md mx-auto grid grid-cols-5 h-14 text-center font-mono-tech">
      
      <!-- Nav Item 1: Booking -->
      <button type="button" onclick="App.nav.switchTab('sec-booking')" data-tab="sec-booking" class="nav-item flex flex-col items-center justify-center text-[#FF5722] active:bg-[#151820] transition">
        <svg class="w-4 h-4 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        <span class="text-[9px] font-bold tracking-tight">PESAN</span>
      </button>

      <!-- Nav Item 2: Telemetry -->
      <button type="button" onclick="App.nav.switchTab('sec-telemetry')" data-tab="sec-telemetry" class="nav-item flex flex-col items-center justify-center text-stone-400 active:bg-[#151820] transition">
        <svg class="w-4 h-4 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
        <span class="text-[9px] font-bold tracking-tight">KONDISI</span>
      </button>

      <!-- Nav Item 3: Waypoints -->
      <button type="button" onclick="App.nav.switchTab('sec-waypoints')" data-tab="sec-waypoints" class="nav-item flex flex-col items-center justify-center text-stone-400 active:bg-[#151820] transition">
        <svg class="w-4 h-4 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
        <span class="text-[9px] font-bold tracking-tight">POS</span>
      </button>

      <!-- Nav Item 4: Logistik -->
      <button type="button" onclick="App.nav.switchTab('sec-logistik')" data-tab="sec-logistik" class="nav-item flex flex-col items-center justify-center text-stone-400 active:bg-[#151820] transition">
        <svg class="w-4 h-4 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
        <span class="text-[9px] font-bold tracking-tight">ALAT</span>
      </button>

      <!-- Nav Item 5: Tiket Lookup Modal -->
      <button type="button" onclick="App.modal.open('ticketModal')" class="nav-item flex flex-col items-center justify-center text-stone-400 active:bg-[#151820] transition">
        <svg class="w-4 h-4 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
        <span class="text-[9px] font-bold tracking-tight">TIKET</span>
      </button>

    </div>
  </nav>

  <!-- ======================================================== -->
  <!-- MODAL 1: MULTI-STEP REGISTRATION MODAL                   -->
  <!-- ======================================================== -->
  <div id="regModal" class="hidden fixed inset-0 z-50 bg-black/85 backdrop-blur-md flex items-center justify-center p-3 sm:p-4">
    <div class="bg-[#121418] border border-[#262B35] rounded-2xl w-full max-w-md max-h-[90vh] flex flex-col shadow-2xl overflow-hidden">
      
      <!-- Top Modal Header (Sticky) -->
      <div class="p-4 pb-3 border-b border-[#1E2229] bg-[#121418] flex-shrink-0">
        <div class="flex items-center justify-between mb-3">
          <div class="flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-sm bg-[#FF5722]"></span>
            <div>
              <span class="font-mono-tech text-[10px] text-[#FF5722] font-bold tracking-widest uppercase block leading-none">SISTEM MANDIRI SIMAKSI</span>
              <h3 class="font-display font-bold text-base text-white leading-tight">RESERVASI & PERMIT PENDAKIAN</h3>
            </div>
          </div>
          <button type="button" onclick="App.modal.close('regModal')" class="tap-active w-8 h-8 rounded-full bg-[#181B22] border border-[#282D37] text-stone-400 hover:text-white flex items-center justify-center font-bold text-sm">
            ✕
          </button>
        </div>

        <!-- Step Indicator & Progress Bar -->
        <div class="space-y-1.5">
          <div class="flex items-center justify-between font-mono-tech text-[10px]">
            <span id="wizardStepLabel" class="text-white font-bold tracking-wider">LANGKAH 1/4: JADWAL & LOGISTIK</span>
            <span id="wizardStepPercent" class="text-[#FF5722] font-bold">25%</span>
          </div>
          
          <!-- Progress Bar Track -->
          <div class="w-full h-1.5 bg-[#1C2028] rounded-full overflow-hidden">
            <div id="wizardProgressBar" class="h-full bg-[#FF5722] transition-all duration-300 rounded-full" style="width: 25%;"></div>
          </div>

          <!-- Step Badges (Compact) -->
          <div class="grid grid-cols-4 gap-1 pt-1 font-mono-tech text-[9px] text-center">
            <button type="button" id="stepPill1" onclick="App.wizard.goTo(1)" class="py-1 rounded bg-[#FF5722]/15 text-[#FF5722] border border-[#FF5722]/40 font-bold transition">1. JADWAL</button>
            <button type="button" id="stepPill2" onclick="App.wizard.goTo(2)" class="py-1 rounded bg-[#181B22] text-stone-500 border border-[#222731] font-medium transition">2. KETUA</button>
            <button type="button" id="stepPill3" onclick="App.wizard.goTo(3)" class="py-1 rounded bg-[#181B22] text-stone-500 border border-[#222731] font-medium transition">3. ANGGOTA</button>
            <button type="button" id="stepPill4" onclick="App.wizard.goTo(4)" class="py-1 rounded bg-[#181B22] text-stone-500 border border-[#222731] font-medium transition">4. BAYAR</button>
          </div>
        </div>
      </div>

      <!-- Scrollable Wizard Steps Body -->
      <div class="flex-1 overflow-y-auto p-4 space-y-4 font-mono-tech">
        
        <!-- ============================================== -->
        <!-- STEP 1: JADWAL & LOGISTIK                      -->
        <!-- ============================================== -->
        <div id="wizardStep1" class="space-y-4">
          <div class="p-2.5 rounded-lg bg-[#16191F] border border-[#222731] text-[11px] text-stone-300 leading-relaxed font-mono-tech">
            <span class="text-[#FF5722] font-bold block mb-0.5">// PANDUAN JADWAL & KUOTA:</span>
            Pilih tanggal mulai pendakian. Kuota harian dibatasi max {{ $profile['default_daily_quota'] ?? 150 }} orang per hari sesuai daya dukung konservasi.
          </div>

          <!-- Kategori Pendakian: Tektok vs Camp -->
          <div>
            <label class="block text-[10px] text-stone-400 uppercase mb-1">KATEGORI PENDAKIAN</label>
            <div class="grid grid-cols-2 gap-2">
              <label id="catTektokLabel" class="relative flex flex-col p-2.5 rounded-xl border border-[#2A303C] bg-[#16191F] cursor-pointer hover:border-cyan-400/50 transition">
                <div class="flex items-center gap-1.5 font-bold text-xs text-cyan-400">
                  <input type="radio" name="wzCategory" value="tektok" class="accent-cyan-400" onchange="App.wizard.setCategory('tektok')">
                  <span>TEKTOK (1 HARI)</span>
                </div>
                <span class="text-[9px] text-stone-400 mt-1 leading-tight">Buka Gerbang: 03:00 - 07:00 WIB. Turun hari yang sama (tanpa tenda).</span>
              </label>

              <label id="catCampLabel" class="relative flex flex-col p-2.5 rounded-xl border border-emerald-500/50 bg-emerald-500/10 cursor-pointer hover:border-emerald-400 transition">
                <div class="flex items-center gap-1.5 font-bold text-xs text-emerald-400">
                  <input type="radio" name="wzCategory" value="camp" checked class="accent-emerald-400" onchange="App.wizard.setCategory('camp')">
                  <span>CAMP (BERMALAM)</span>
                </div>
                <span class="text-[9px] text-stone-400 mt-1 leading-tight">Buka Gerbang: Mulai 07:00 WIB. Bebas camp di Pos 2 atau Pos 3.</span>
              </label>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-2.5">
            <div>
              <label class="block text-[10px] text-stone-400 uppercase mb-1">Tanggal Naik</label>
              <input type="date" id="wzHikingDate" min="{{ date('Y-m-d') }}" onchange="App.wizard.onDateChange()" class="w-full bg-[#181B22] border border-[#2A303C] rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-[#FF5722]">
            </div>
            <div>
              <label id="wzReturnDateLabel" class="block text-[10px] text-stone-400 uppercase mb-1">Estimasi Turun</label>
              <input type="date" id="wzReturnDate" min="{{ date('Y-m-d') }}" class="w-full bg-[#181B22] border border-[#2A303C] rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-[#FF5722]">
            </div>
          </div>

          <!-- Pilihan Rencana Camp (Hanya Muncul jika Kategori Camp) -->
          <div id="wzCampsiteBox" class="p-2.5 rounded-lg bg-[#14171E] border border-[#222731] space-y-1.5">
            <div class="flex items-center justify-between">
              <span class="text-[10px] text-stone-300 font-bold uppercase">RENCANA AREA CAMP RESMI</span>
              <span class="text-[9px] text-emerald-400">*Bebas fleksibel di pos</span>
            </div>
            <div class="grid grid-cols-2 gap-2 text-xs text-stone-200">
              <label class="p-2 rounded bg-[#181B22] border border-[#2A303C] flex items-center gap-2 cursor-pointer hover:border-emerald-500/40">
                <input type="radio" name="wzCampsite" value="POS_2" checked class="accent-emerald-400">
                <span>Pos 2 (Mata Air)</span>
              </label>
              <label class="p-2 rounded bg-[#181B22] border border-[#2A303C] flex items-center gap-2 cursor-pointer hover:border-emerald-500/40">
                <input type="radio" name="wzCampsite" value="POS_3" class="accent-emerald-400">
                <span>Pos 3 (Camp Atas)</span>
              </label>
            </div>
            <p class="text-[9px] text-stone-400 leading-tight">Pos 2 dan Pos 3 adalah tempat camp resmi yang dijaga ranger. Di luar kedua pos ini dilarang mendirikan tenda.</p>
          </div>

          <!-- Stepper Rombongan -->
          <div>
            <div class="flex justify-between items-center mb-1">
              <label class="text-[10px] text-stone-400 uppercase">Jumlah Pendaki</label>
              <span id="wzClimberCountLabel" class="text-xs font-bold text-[#FF5722]">4 ORANG</span>
            </div>
            <div class="flex items-center bg-[#181B22] border border-[#2A303C] rounded-lg overflow-hidden">
              <button type="button" onclick="App.wizard.adjustClimbers(-1)" class="tap-active w-12 py-2.5 bg-[#20242C] text-white hover:bg-[#282E38] font-bold text-base transition flex items-center justify-center">-</button>
              <div id="wzClimberCountBox" class="flex-1 text-center font-bold text-sm text-white py-2">4</div>
              <button type="button" onclick="App.wizard.adjustClimbers(1)" class="tap-active w-12 py-2.5 bg-[#20242C] text-white hover:bg-[#282E38] font-bold text-base transition flex items-center justify-center">+</button>
            </div>
            <p class="text-[9px] text-stone-500 mt-1">*Mendukung pendakian solo ({{ $profile['min_climbers_per_group'] ?? 1 }} orang) hingga rombongan (maks. {{ $profile['max_climbers_per_group'] ?? 15 }} orang).</p>
          </div>

          <!-- Layanan Tambahan Logistik (Multi-Unit Stepper) -->
          <div class="space-y-2 pt-1 border-t border-[#1E2229]">
            <span class="block text-[10px] text-stone-400 uppercase">PILIHAN LOGISTIK BASECAMP</span>
            
            <!-- Service 1: Shuttle Ojek Stepper -->
            <div class="p-3 rounded-lg bg-[#16191F] border border-[#222731] space-y-2">
              <div class="flex items-center justify-between">
                <div>
                  <span class="block text-xs font-bold text-stone-200">Shuttle Ojek Trail</span>
                  <span class="text-[9px] text-stone-400">{{ $tariffs['ojek_route_name'] ?? 'Basecamp → Pos 1' }} (Rp {{ number_format($tariffs['ojek_fee_per_trip'], 0, ',', '.') }} / armada)</span>
                </div>
                <span id="wzOjekTotalLabel" class="text-xs font-bold text-[#FF5722]">Rp 0</span>
              </div>

              <div class="flex items-center justify-between pt-1">
                <span class="text-[9px] text-stone-500">1 Motor = 1 Pendaki / Tas</span>
                
                <div class="flex items-center bg-[#181B22] border border-[#2A303C] rounded-lg overflow-hidden">
                  <button type="button" onclick="App.wizard.adjustOjek(-1)" class="tap-active w-8 py-1.5 bg-[#20242C] text-white hover:bg-[#282E38] font-bold text-sm transition flex items-center justify-center">-</button>
                  <div id="wzOjekCountBox" class="w-10 text-center font-bold text-xs text-white py-1">0</div>
                  <button type="button" onclick="App.wizard.adjustOjek(1)" class="tap-active w-8 py-1.5 bg-[#20242C] text-white hover:bg-[#282E38] font-bold text-sm transition flex items-center justify-center">+</button>
                </div>
              </div>
            </div>

            <!-- Service 2: Parkir Kendaraan Pribadi (Checkbox Toggle & Multi-Vehicle Stepper) -->
            <div class="p-3 rounded-lg bg-[#16191F] border border-[#222731] space-y-2">
              <label class="flex items-center justify-between cursor-pointer select-none">
                <div class="flex items-center gap-2.5">
                  <input type="checkbox" id="wzHasVehicle" onchange="App.wizard.toggleVehicle(this.checked)" class="w-4 h-4 rounded bg-[#181B22] border-[#2A303C] text-[#FF5722] focus:ring-0 focus:ring-offset-0 cursor-pointer">
                  <div>
                    <span class="block text-xs font-bold text-stone-200">Membawa Kendaraan Pribadi</span>
                    <span class="text-[9px] font-mono-tech text-stone-400">Titip parkir resmi basecamp 24 jam</span>
                  </div>
                </div>
                <span id="wzParkingTotalLabel" class="font-mono-tech text-xs font-bold text-cyan-400">Rp 0</span>
              </label>

              <!-- Pilihan Kendaraan & Stepper (Muncul jika diceklis) -->
              <div id="wzVehicleOptions" class="hidden pt-2 border-t border-[#1E2229] space-y-2">
                <!-- Motor -->
                <div class="flex items-center justify-between bg-[#121418] p-2 rounded-lg border border-[#1E2229]">
                  <div>
                    <span class="block text-[11px] font-bold text-stone-200">Sepeda Motor</span>
                    <span class="text-[9px] font-mono-tech text-stone-400">Rp {{ number_format($tariffs['parking_motor_fee'], 0, ',', '.') }} / unit</span>
                  </div>
                  <div class="flex items-center bg-[#181B22] border border-[#2A303C] rounded-lg overflow-hidden">
                    <button type="button" onclick="App.wizard.adjustMotorcycle(-1)" class="tap-active w-7 py-1 bg-[#20242C] text-white hover:bg-[#282E38] font-bold text-xs flex items-center justify-center">-</button>
                    <div id="wzMotorCountBox" class="w-8 text-center font-bold text-xs text-white py-1">0</div>
                    <button type="button" onclick="App.wizard.adjustMotorcycle(1)" class="tap-active w-7 py-1 bg-[#20242C] text-white hover:bg-[#282E38] font-bold text-xs flex items-center justify-center">+</button>
                  </div>
                </div>

                <!-- Mobil -->
                <div class="flex items-center justify-between bg-[#121418] p-2 rounded-lg border border-[#1E2229]">
                  <div>
                    <span class="block text-[11px] font-bold text-stone-200">Mobil Pribadi</span>
                    <span class="text-[9px] font-mono-tech text-stone-400">Rp {{ number_format($tariffs['parking_car_fee'], 0, ',', '.') }} / unit</span>
                  </div>
                  <div class="flex items-center bg-[#181B22] border border-[#2A303C] rounded-lg overflow-hidden">
                    <button type="button" onclick="App.wizard.adjustCar(-1)" class="tap-active w-7 py-1 bg-[#20242C] text-white hover:bg-[#282E38] font-bold text-xs flex items-center justify-center">-</button>
                    <div id="wzCarCountBox" class="w-8 text-center font-bold text-xs text-white py-1">0</div>
                    <button type="button" onclick="App.wizard.adjustCar(1)" class="tap-active w-7 py-1 bg-[#20242C] text-white hover:bg-[#282E38] font-bold text-xs flex items-center justify-center">+</button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Cost preview card -->
          <div class="p-3 rounded-lg bg-[#0E1013] border border-[#1A1E26] space-y-1 text-xs">
            <div class="flex justify-between text-stone-400 text-[11px]">
              <span id="wzTxtSimaksiSub">SIMAKSI (4 Org @ Rp 25.000):</span>
              <span id="wzValSimaksiSub" class="text-stone-200">Rp 100.000</span>
            </div>
            <div class="flex justify-between text-stone-400 text-[11px]">
              <span id="wzTxtOjekSub">Shuttle Ojek (0 Armada):</span>
              <span id="wzValOjekSub" class="text-stone-200">Rp 0</span>
            </div>
            <div class="flex justify-between text-stone-400 text-[11px] hidden" id="wzRowParkirSub">
              <span id="wzTxtParkirSub">Parkir Kendaraan:</span>
              <span id="wzValParkirSub" class="text-cyan-400 font-bold">Rp 0</span>
            </div>
            <div class="pt-2 border-t border-[#1E2229] flex justify-between items-center text-sm">
              <span class="font-bold text-white">ESTIMASI TOTAL:</span>
              <span id="wzValTotalSub" class="font-bold text-[#FF5722] text-base">Rp 100.000</span>
            </div>
          </div>

          <button type="button" onclick="App.wizard.validateStep1()" class="tap-active w-full py-3 bg-[#FF5722] hover:bg-[#F4511E] text-black font-bold text-xs uppercase tracking-wider rounded-lg transition flex items-center justify-center gap-2 shadow-lg shadow-[#FF5722]/20">
            <span>LANJUT: DATA KETUA [2/4]</span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
          </button>
        </div>

        <!-- ============================================== -->
        <!-- STEP 2: IDENTITAS KETUA & KONTAK DARURAT       -->
        <!-- ============================================== -->
        <div id="wizardStep2" class="hidden space-y-3.5">
          <div class="p-2.5 rounded-lg bg-[#16191F] border border-[#222731] text-[11px] text-stone-300 leading-relaxed font-mono-tech">
            <span class="text-[#FF5722] font-bold block mb-0.5">// IDENTITAS KETUA PENANGGUNG JAWAB:</span>
            Wajib mengisi NIK KTP 16-digit asli dan nomor WhatsApp aktif untuk verifikasi di pos gerbang basecamp.
          </div>

          <div>
            <label class="block text-[10px] text-stone-400 uppercase mb-1">Nama Lengkap Ketua (Sesuai KTP) <span class="text-[#FF5722]">*</span></label>
            <input type="text" id="wzLeaderName" placeholder="Contoh: Arif Rahmansyah" class="w-full bg-[#181B22] border border-[#2A303C] rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-[#FF5722]">
          </div>

          <div class="grid grid-cols-2 gap-2.5">
            <div>
              <label class="block text-[10px] text-stone-400 uppercase mb-1">NIK KTP (16 Digit) <span class="text-[#FF5722]">*</span></label>
              <input type="text" id="wzLeaderNik" maxlength="16" placeholder="320501xxxxxxxxxx" class="w-full bg-[#181B22] border border-[#2A303C] rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-[#FF5722]">
            </div>
            <div>
              <label class="block text-[10px] text-stone-400 uppercase mb-1">No. WhatsApp Aktif <span class="text-[#FF5722]">*</span></label>
              <input type="tel" id="wzLeaderPhone" placeholder="0812xxxxxxxx" class="w-full bg-[#181B22] border border-[#2A303C] rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-[#FF5722]">
            </div>
          </div>

          <div>
            <label class="block text-[10px] text-stone-400 uppercase mb-1">
              Alamat Lengkap Domisili (Sesuai KTP) <span class="text-[#FF5722]">*</span>
            </label>
            <textarea id="wzLeaderAddress" rows="2" placeholder="Contoh: Jl. Merdeka No. 45, RT 02/RW 05, Kel. Tarogong, Kec. Tarogong Kidul, Kab. Garut, Jawa Barat" class="w-full bg-[#181B22] border border-[#2A303C] rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-[#FF5722] resize-none font-mono-tech leading-relaxed"></textarea>
            <input type="hidden" id="wzLeaderCity" value="">
          </div>

          <!-- Upload Foto / Scan KTP Asli Ketua (dengan Auto Image Compression) -->
          <div class="space-y-1.5">
            <div class="flex justify-between items-center">
              <label class="block text-[10px] text-stone-400 uppercase">
                Foto / Scan KTP Asli Ketua / Solo <span class="text-[#FF5722]">* (Wajib Verifikasi)</span>
              </label>
              <span id="wzKtpCompressBadge" class="text-[9px] font-mono-tech text-emerald-400 hidden">// KOMPRESI OTOMATIS AKTIF</span>
            </div>

            <div onclick="document.getElementById('wzLeaderKtpInput').click()" class="border-2 border-dashed border-[#2E3544] hover:border-[#FF5722] bg-[#121418] hover:bg-[#151820] rounded-xl p-3 text-center cursor-pointer transition">
              <input type="file" id="wzLeaderKtpInput" accept="image/*,application/pdf" class="hidden" onchange="App.wizard.handleKtpSelect(event)">
              
              <div id="wzKtpPlaceholder" class="space-y-1 py-1">
                <svg class="w-6 h-6 mx-auto text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>
                <div class="text-xs font-bold text-stone-200">Ambil Foto / Unggah KTP Asli</div>
                <div class="text-[9px] text-stone-400">JPG, PNG, WebP • Auto-compress ringan & hemat kuota data</div>
              </div>

              <!-- Preview State -->
              <div id="wzKtpPreviewBox" class="hidden flex items-center justify-between gap-3 text-left">
                <div class="flex items-center gap-2.5 truncate">
                  <img id="wzKtpPreviewImg" src="" alt="KTP Ketua" class="w-12 h-8 object-cover rounded border border-[#2E3544]">
                  <div class="truncate">
                    <span id="wzKtpFileName" class="text-xs font-bold text-white block truncate">ktp_ketua.jpg</span>
                    <span id="wzKtpFileSize" class="text-[10px] text-emerald-400 font-mono-tech block">210 KB (Hemat 92%)</span>
                  </div>
                </div>
                <button type="button" onclick="event.stopPropagation(); App.wizard.removeKtpFile()" class="tap-active p-1.5 rounded bg-rose-500/10 text-rose-400 hover:bg-rose-500/20 border border-rose-500/30 text-[10px] font-bold">
                  HAPUS
                </button>
              </div>
            </div>
            
            <p class="text-[9px] text-stone-400 leading-tight">Foto KTP digunakan verifikator untuk mencocokkan identitas sebelum izin pendakian disetujui.</p>

            <!-- Disclaimer Perlindungan Data Pribadi (PDP) KTP Auto-Purge -->
            <div class="p-2.5 rounded-lg bg-[#0E1116] border border-emerald-500/30 flex items-start gap-2 text-[10px] leading-relaxed">
              <svg class="w-4 h-4 text-emerald-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
              <div class="space-y-0.5">
                <span class="text-emerald-400 font-bold uppercase block text-[9px] font-mono-tech tracking-wider">// DISCLAIMER PRIVASI & PERLINDUNGAN DATA (PDP):</span>
                <p class="text-stone-300 text-[10px] leading-relaxed">
                  Foto KTP hanya digunakan sementara untuk validasi identitas & izin SIMAKSI. Sesuai prinsip Perlindungan Data Pribadi (PDP), <strong class="text-emerald-300">seluruh berkas foto fisik KTP akan dihapus otomatis dan dimusnahkan secara permanen dari server</strong> segera setelah pendakian selesai (saat check-out di gerbang pos).
                </p>
              </div>
            </div>
          </div>

          <!-- Riwayat Penyakit Bawaan & Kondisi Medis Ketua -->
          <div>
            <div class="flex justify-between items-center mb-1">
              <label class="block text-[10px] text-stone-400 uppercase">
                Riwayat Penyakit Bawaan / Alergi Ketua
              </label>
              <span class="text-[9px] text-stone-500 font-mono-tech">// SOP MEDIS SAR</span>
            </div>
            <input type="text" id="wzLeaderMedical" value="Tidak Ada" placeholder="Contoh: Tidak Ada / Asma, Alergi Dingin, Jantung, dsb." class="w-full bg-[#181B22] border border-[#2A303C] rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-[#FF5722] transition">
            <span class="text-[9px] text-stone-500 block mt-1">Wajib diisi jujur untuk kesiapsiagaan tim evakuasi SAR SAMSARA di jalur gunung.</span>
          </div>

          <!-- Emergency Contact Subcard -->
          <div class="p-3 bg-[#101216] border border-[#242A36] rounded-xl space-y-2.5">
            <div class="flex items-center gap-1.5 text-[#FF5722]">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
              <span class="text-[10px] font-bold uppercase tracking-wider">KONTAK DARURAT (KELUARGA DI RUMAH)</span>
            </div>
            <p class="text-[9px] text-stone-400 leading-tight">Wajib mencantumkan nomor keluarga yang TIDAK ikut mendaki untuk SOP darurat SAR SAMSARA.</p>
            
            <div class="grid grid-cols-2 gap-2">
              <div>
                <label class="block text-[9px] text-stone-400 uppercase mb-1">Nama Kontak Darurat <span class="text-[#FF5722]">*</span></label>
                <input type="text" id="wzEmergName" placeholder="Contoh: Siti Fatimah" class="w-full bg-[#181B22] border border-[#2A303C] rounded-lg px-2.5 py-1.5 text-xs text-white focus:outline-none focus:border-[#FF5722]">
              </div>
              <div>
                <label class="block text-[9px] text-stone-400 uppercase mb-1">Hubungan</label>
                <select id="wzEmergRel" class="w-full bg-[#181B22] border border-[#2A303C] rounded-lg px-2 py-1.5 text-xs text-white focus:outline-none focus:border-[#FF5722]">
                  <option value="Orang Tua">Orang Tua</option>
                  <option value="Pasangan">Pasangan (Suami/Istri)</option>
                  <option value="Saudara Kandung">Saudara Kandung</option>
                  <option value="Keluarga / Kerabat">Keluarga / Kerabat</option>
                </select>
              </div>
            </div>

            <div>
              <label class="block text-[9px] text-stone-400 uppercase mb-1">No. Telp / WhatsApp Darurat <span class="text-[#FF5722]">*</span></label>
              <input type="tel" id="wzEmergPhone" placeholder="08xxxxxxxxxx" class="w-full bg-[#181B22] border border-[#2A303C] rounded-lg px-2.5 py-1.5 text-xs text-white focus:outline-none focus:border-[#FF5722]">
            </div>
          </div>

          <div class="grid grid-cols-2 gap-2.5 pt-1">
            <button type="button" onclick="App.wizard.goTo(1)" class="tap-active py-2.5 bg-[#181B22] border border-[#282D37] text-stone-300 font-bold text-xs uppercase tracking-wider rounded-lg transition hover:bg-[#202530]">
              ← KEMBALI
            </button>
            <button type="button" onclick="App.wizard.validateStep2()" class="tap-active py-2.5 bg-[#FF5722] text-black font-bold text-xs uppercase tracking-wider rounded-lg transition hover:bg-[#F4511E] flex items-center justify-center gap-1.5">
              <span>MANIFEST [3/4]</span>
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </button>
          </div>
        </div>

        <!-- ============================================== -->
        <!-- STEP 3: MANIFEST ANGGOTA ROMBONGAN             -->
        <!-- ============================================== -->
        <div id="wizardStep3" class="hidden space-y-3.5">
          <div class="p-2.5 rounded-lg bg-[#16191F] border border-[#222731] text-[11px] text-stone-300 leading-relaxed font-mono-tech">
            <span class="text-[#FF5722] font-bold block mb-0.5">// MANIFEST KESELAMATAN ANGGOTA:</span>
            Seluruh nama & NIK anggota diinput untuk registrasi manifest resmi & jaminan asuransi pendakian.
          </div>

          <!-- Leader Card (Member #1 Summary) -->
          <div class="p-2.5 rounded-lg bg-[#111317] border border-[#222731] flex items-center justify-between text-xs">
            <div>
              <div class="flex items-center gap-2">
                <span class="px-1.5 py-0.5 rounded text-[9px] bg-[#FF5722]/15 text-[#FF5722] border border-[#FF5722]/30 font-bold" id="wzLeaderRoleTag">ANGGOTA #1</span>
                <span class="font-bold text-white" id="wzLeaderSummaryName">Ketua Tim</span>
              </div>
              <span class="text-[10px] text-stone-400 block mt-0.5" id="wzLeaderSummaryNik">NIK: -</span>
            </div>
            <span class="text-[9px] text-emerald-400 font-bold font-mono-tech" id="wzLeaderRoleBadge">[KETUA TIM]</span>
          </div>

          <!-- Dynamic Member Cards Container (#2 s.d. #N) -->
          <div id="wzMembersList" class="space-y-3">
            <!-- Dynamically generated via App.wizard.renderMembers() -->
          </div>

          <div class="grid grid-cols-2 gap-2.5 pt-1">
            <button type="button" onclick="App.wizard.goTo(2)" class="tap-active py-2.5 bg-[#181B22] border border-[#282D37] text-stone-300 font-bold text-xs uppercase tracking-wider rounded-lg transition hover:bg-[#202530]">
              ← KEMBALI
            </button>
            <button type="button" onclick="App.wizard.validateStep3()" class="tap-active py-2.5 bg-[#FF5722] text-black font-bold text-xs uppercase tracking-wider rounded-lg transition hover:bg-[#F4511E] flex items-center justify-center gap-1.5">
              <span>PEMBAYARAN [4/4]</span>
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </button>
          </div>
        </div>

        <!-- ============================================== -->
        <!-- STEP 4: PEMBAYARAN & UPLOAD BUKTI BAYAR        -->
        <!-- ============================================== -->
        <div id="wizardStep4" class="hidden space-y-4">
          <!-- Total Payment Callout with Unique Code -->
          <div class="p-3 rounded-xl bg-[#0E1013] border border-[#262B35] space-y-1.5">
            <div class="flex items-center justify-between text-xs text-stone-400">
              <span>Subtotal SIMAKSI & Logistik:</span>
              <span id="wzSubtotalPay" class="text-stone-300 font-bold">Rp 100.000</span>
            </div>
            <div id="wzParkingPayRow" class="hidden flex items-center justify-between text-xs text-cyan-400">
              <span>Biaya Parkir Kendaraan:</span>
              <span id="wzParkingPay" class="font-bold font-mono-tech">Rp 0</span>
            </div>
            <div id="wzUniqueCodeRow" class="flex items-center justify-between text-xs text-amber-400">
              <span class="flex items-center gap-1.5">
                <span>Kode Unik Verifikasi:</span>
                <span class="text-[8px] px-1.5 py-0.2 rounded bg-amber-500/20 text-amber-300 font-bold uppercase">Manual Admin</span>
              </span>
              <span id="wzUniqueCodePay" class="font-bold font-mono-tech">+Rp 348</span>
            </div>
            <div class="pt-2 border-t border-[#1E2229] flex items-center justify-between">
              <div>
                <span id="wzTotalLabel" class="text-[10px] text-stone-400 block uppercase">TOTAL TRANSFER</span>
                <span id="wzFinalTotal" class="text-xl font-bold text-[#FF5722]">Rp 100.348</span>
              </div>
              <button type="button" id="btnCopyNominal" onclick="App.wizard.copyTransferAmount()" class="tap-active px-2.5 py-1.5 rounded bg-[#1C2028] hover:bg-[#282E3A] border border-[#2E3544] text-[10px] text-stone-300 hover:text-white flex items-center gap-1.5 font-bold">
                <svg class="w-3.5 h-3.5 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                <span>SALIN NOMINAL</span>
              </button>
            </div>
            <p id="wzUniqueNote" class="text-[9px] text-amber-300/90 pt-1 leading-tight border-t border-[#1E2229]/60">
              *Penting: Mohon transfer <strong>tepat hingga 3 digit terakhir</strong> agar admin/kasir dapat memverifikasi mutasi rekening bank secara instan.
            </p>
          </div>

          <!-- Payment Method Options -->
          <div class="space-y-2">
            <label class="block text-[10px] text-stone-400 uppercase">PILIH METODE PEMBAYARAN</label>
            
            <div class="grid grid-cols-3 gap-2">
              <label class="payment-method-card p-2 rounded-lg bg-[#16191F] border border-[#FF5722] cursor-pointer text-center block transition" id="lblMethodQris">
                <input type="radio" name="wzPaymentMethod" value="qris" checked onchange="App.wizard.onMethodChange('qris')" class="hidden">
                <span class="text-xs font-bold text-white block">QRIS</span>
                <span class="text-[8px] text-stone-400 block mt-0.5">E-Wallet & Bank</span>
              </label>

              <label class="payment-method-card p-2 rounded-lg bg-[#16191F] border border-[#222731] cursor-pointer text-center block transition" id="lblMethodTransfer">
                <input type="radio" name="wzPaymentMethod" value="transfer" onchange="App.wizard.onMethodChange('transfer')" class="hidden">
                <span class="text-xs font-bold text-stone-300 block">TRANSFER</span>
                <span class="text-[8px] text-stone-400 block mt-0.5">BCA / Mandiri</span>
              </label>

              <label class="payment-method-card p-2 rounded-lg bg-[#16191F] border border-[#222731] cursor-pointer text-center block transition" id="lblMethodCash">
                <input type="radio" name="wzPaymentMethod" value="cash_pos" onchange="App.wizard.onMethodChange('cash_pos')" class="hidden">
                <span class="text-xs font-bold text-stone-300 block">TUNAI</span>
                <span class="text-[8px] text-stone-400 block mt-0.5">Kasir Loket BC</span>
              </label>
            </div>
          </div>

          <!-- Method Details Panel: QRIS -->
          <div id="methodDetailQris" class="p-3 bg-[#101216] border border-[#222731] rounded-xl space-y-2 text-center">
            <span class="text-[10px] text-stone-400 block">SCAN QRIS RESMI {{ strtoupper($profile['basecamp_name'] ?? 'SAMSARA GARUT') }}:</span>
            <!-- Visual QRIS Container -->
            <div class="w-40 h-40 mx-auto bg-white p-2 rounded-lg flex items-center justify-center shadow-md">
              @if(!empty($profile['payment_qris_image']))
                <a href="{{ asset('storage/' . $profile['payment_qris_image']) }}" target="_blank" title="Klik untuk perbesar QRIS">
                  <img src="{{ asset('storage/' . $profile['payment_qris_image']) }}" alt="QRIS Resmi {{ $profile['payment_qris_name'] ?? 'Basecamp' }}" class="w-full h-full object-contain cursor-pointer">
                </a>
              @else
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=160x160&data=00020101021226600016ID.SAMSARA.GARUT01189360099900000000015204581253033605802ID5918SAMSARA+CIKURAY6005GARUT6304A1B2" alt="QRIS Samsara" class="w-full h-full object-contain" onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'text-black text-[10px] font-bold\'>[QRIS RESMI]<br>NMID: {{ $profile['payment_qris_nmid'] ?? 'ID102026110099' }}</div>'">
              @endif
            </div>
            <div class="text-[9px] text-stone-400 space-y-0.5">
              <span class="text-stone-300 block font-bold">NMID: {{ $profile['payment_qris_nmid'] ?? 'ID102026110099' }} — {{ $profile['payment_qris_name'] ?? 'PT SAMSARA ADVENTURE GARUT' }}</span>
              <span>Mendukung: GoPay, OVO, Dana, ShopeePay, BCA, Livin, Mandiri, dll.</span>
              @if(!empty($profile['payment_qris_image']))
                <a href="{{ asset('storage/' . $profile['payment_qris_image']) }}" target="_blank" class="text-[#FF5722] hover:underline block text-[9px] pt-0.5 font-bold">[LIHAT / SIMPAN QRIS FULL RESOLUSI]</a>
              @endif
            </div>
          </div>

          <!-- Method Details Panel: Transfer Bank -->
          <div id="methodDetailTransfer" class="hidden p-3 bg-[#101216] border border-[#222731] rounded-xl space-y-2 text-xs">
            <span class="text-[10px] text-stone-400 block uppercase">REKENING RESMI {{ strtoupper($profile['expedition_name'] ?? 'EXPEDITION') }}:</span>
            <div class="p-2.5 bg-[#16191F] border border-[#2A303C] rounded-lg flex items-center justify-between">
              <div>
                <span class="text-[9px] text-stone-400 block">{{ $profile['payment_bank_name'] ?? 'BANK CENTRAL ASIA (BCA)' }}</span>
                <span class="text-sm font-bold text-white tracking-wider">{{ $profile['payment_bank_account'] ?? '810-0982-111' }}</span>
                <span class="text-[9px] text-stone-400 block">a.n. {{ $profile['payment_bank_holder'] ?? 'PT SAMSARA ADVENTURE GARUT' }}</span>
              </div>
              <button type="button" onclick="App.wizard.copyAccount('{{ $profile['payment_bank_account_raw'] ?? '8100982111' }}', '{{ $profile['payment_bank_name'] ?? 'BCA' }}')" class="tap-active px-2.5 py-1.5 bg-[#202530] text-[#FF5722] hover:bg-[#282E38] border border-[#FF5722]/30 rounded text-[10px] font-bold">
                SALIN REK
              </button>
            </div>
          </div>

          <!-- Method Details Panel: Cash Loket -->
          <div id="methodDetailCash" class="hidden p-3 bg-[#101216] border border-[#222731] rounded-xl space-y-1 text-xs text-stone-300">
            <span class="text-[10px] text-[#FF5722] font-bold block uppercase">PEMBAYARAN TUNAI DI BASECAMP:</span>
            <p class="text-[11px] leading-relaxed text-stone-400">
              Kode registrasi Anda akan berstatus <strong>MENUNGGU VERIFIKASI (PENDING)</strong>. Pembayaran dilakukan langsung tunai di meja Kasir POS Basecamp sebelum verifikasi fisik di Gate Check-in.
            </p>
          </div>

          <!-- File Upload: Bukti Pembayaran -->
          <div id="wzProofUploadContainer" class="space-y-1.5">
            <div class="flex items-center justify-between">
              <label class="block text-[10px] text-stone-400 uppercase">
                UPLOAD BUKTI BAYAR <span id="wzProofRequiredBadge" class="text-[#FF5722]">* (WAJIB UNTUK QRIS/TRANSFER)</span>
              </label>
              <span class="text-[9px] text-stone-500">Maks. 5 MB</span>
            </div>

            <!-- Upload Area -->
            <div id="wzDropzone" onclick="document.getElementById('wzPaymentProofInput').click()" class="border-2 border-dashed border-[#2A303C] hover:border-[#FF5722] rounded-xl p-3 text-center cursor-pointer transition bg-[#16191F]/50">
              <input type="file" id="wzPaymentProofInput" accept="image/*,application/pdf" class="hidden" onchange="App.wizard.handleFileSelect(event)">
              
              <div id="wzUploadPlaceholder" class="space-y-1 py-1">
                <svg class="w-6 h-6 mx-auto text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span class="text-xs text-stone-300 block font-bold">Pilih Foto Struk / Screenshot Bukti</span>
                <span class="text-[9px] text-stone-500 block">Format: JPG, PNG, WEBP, atau PDF</span>
              </div>

              <!-- Preview State (Initially Hidden) -->
              <div id="wzUploadPreviewBox" class="hidden flex items-center gap-3 text-left">
                <img id="wzUploadPreviewImg" src="" alt="Bukti Transfer" class="w-12 h-12 object-cover rounded border border-[#2E3544]">
                <div class="flex-1 min-w-0">
                  <span id="wzUploadFileName" class="text-xs font-bold text-white block truncate">bukti_transfer.jpg</span>
                  <span id="wzUploadFileSize" class="text-[10px] text-stone-400 block">240 KB</span>
                </div>
                <button type="button" onclick="event.stopPropagation(); App.wizard.removeFile()" class="tap-active p-1.5 rounded bg-rose-500/10 text-rose-400 hover:bg-rose-500/20 border border-rose-500/30 text-[10px] font-bold">
                  HAPUS
                </button>
              </div>
            </div>
          </div>

          <!-- SOP Checkbox Agreement -->
          <label class="flex items-start gap-2.5 p-2.5 rounded-lg bg-[#14161C] border border-[#222731] cursor-pointer">
            <input type="checkbox" id="wzAgreeCheck" checked class="mt-0.5 w-4 h-4 rounded bg-[#20242C] border-stone-600 text-[#FF5722] focus:ring-0">
            <span class="text-[10px] text-stone-300 leading-relaxed">
              Saya menyatakan seluruh data identitas rombongan adalah sah & rombongan siap mematuhi SOP Keselamatan SAMSARA
              @if(!empty($tariffs['trash_deposit_enabled']))
                serta menyiapkan <strong>Deposit Sampah Rp {{ number_format($tariffs['trash_deposit_amount'] ?? 50000, 0, ',', '.') }}</strong> di loket (dikembalikan saat turun membawa kantong sampah).
              @else
                serta menjaga kebersihan jalur pendakian (bebas biaya deposit sampah).
              @endif
            </span>
          </label>

          <!-- Error Alert Banner inside modal -->
          <div id="wzErrorAlert" class="hidden p-3 rounded-lg bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs"></div>

          <div class="grid grid-cols-2 gap-2.5 pt-1">
            <button type="button" onclick="App.wizard.goTo(3)" class="tap-active py-2.5 bg-[#181B22] border border-[#282D37] text-stone-300 font-bold text-xs uppercase tracking-wider rounded-lg transition hover:bg-[#202530]">
              ← KEMBALI
            </button>
            <button type="button" id="btnSubmitRegistration" onclick="App.wizard.submit()" class="tap-active py-2.5 bg-[#FF5722] text-black font-bold text-xs uppercase tracking-wider rounded-lg transition hover:bg-[#F4511E] flex items-center justify-center gap-1.5 shadow-lg shadow-[#FF5722]/20">
              <span id="txtSubmitBtn">KIRIM & BUAT TIKET</span>
              <svg id="spinnerSubmitBtn" class="hidden animate-spin w-4 h-4 text-black" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
            </button>
          </div>
        </div>

        <!-- ============================================== -->
        <!-- STEP 5: HASIL TIKET RESMI TERBIT               -->
        <!-- ============================================== -->
        <div id="wizardStep5" class="hidden text-center py-2 space-y-4">
          <div class="w-14 h-14 mx-auto rounded-full bg-emerald-500/20 border-2 border-emerald-500 flex items-center justify-center text-emerald-400">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
          </div>
          
          <div>
            <span id="wzResultStatusBadge" class="text-[10px] text-amber-400 font-bold px-2.5 py-0.5 bg-amber-500/10 border border-amber-500/30 rounded uppercase tracking-wider font-mono-tech">
              MENUNGGU VERIFIKASI KASIR
            </span>
            <h4 class="font-display font-black text-xl text-white mt-1.5">RESERVASI BERHASIL DIREKAM</h4>
            <p class="text-xs text-stone-400 mt-1 max-w-xs mx-auto">
              Simpan kode registrasi SIMAKSI berikut untuk ditunjukkan ke petugas loket Basecamp SAMSARA.
            </p>
          </div>

          <!-- Ticket Box -->
          <div class="p-3.5 bg-[#16191F] border border-[#2A303C] rounded-xl text-left font-mono-tech space-y-2 text-xs">
            <div class="flex justify-between items-center border-b border-[#222731] pb-2">
              <span class="text-stone-400 text-[10px] uppercase">KODE SIMAKSI:</span>
              <div class="flex items-center gap-1.5">
                <span id="wzResultCode" class="text-base font-bold text-[#FF5722] tracking-wider">SMK-202611-005</span>
                <button type="button" onclick="App.wizard.copyResultCode()" class="tap-active px-2 py-0.5 rounded bg-[#222731] text-stone-300 text-[9px] hover:text-white">
                  SALIN
                </button>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-2 text-[11px] text-stone-300 py-1">
              <div>
                <span class="text-stone-500 text-[9px] block">KETUA TIM:</span>
                <span id="wzResultLeader" class="font-bold text-white">-</span>
              </div>
              <div>
                <span class="text-stone-500 text-[9px] block">ROMBONGAN:</span>
                <span id="wzResultClimbers" class="font-bold text-white">-</span>
              </div>
              <div>
                <span class="text-stone-500 text-[9px] block">TANGGAL PENDAKIAN:</span>
                <span id="wzResultDate" class="text-white">-</span>
              </div>
              <div>
                <span class="text-stone-500 text-[9px] block">TOTAL BIAYA:</span>
                <span id="wzResultTotal" class="text-emerald-400 font-bold">-</span>
              </div>
            </div>

            <div class="p-2 bg-[#0E1013] rounded-lg border border-[#1E2229] flex items-center justify-between text-[10px]">
              <span class="text-stone-400">METODE PEMBAYARAN:</span>
              <span id="wzResultMethod" class="text-stone-200 font-bold uppercase">QRIS (BUKTI TERUNGGAH)</span>
            </div>
          </div>

          <!-- WhatsApp Admin Notification Banner -->
          <div class="p-2.5 rounded-lg bg-[#25D366]/10 border border-[#25D366]/30 text-left space-y-1 font-mono-tech">
            <div class="flex items-center gap-1.5 text-[#25D366] text-xs font-bold">
              <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.007c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86.174.086.275.072.376-.044.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.043.072.043.419-.101.824z"/></svg>
              <span>WHATSAPP ADMIN TERBUKA OTOMATIS</span>
            </div>
            <p class="text-[10px] text-stone-300 leading-tight">
              Rincian permohonan & total transfer Anda telah disiapkan. Jika WhatsApp belum terbuka otomatis, silakan klik tombol di bawah:
            </p>
          </div>

          <div class="p-2.5 rounded-lg bg-[#14171E] border border-[#202530] text-[10px] text-stone-400 text-left space-y-1 font-mono-tech">
            <span class="text-stone-300 font-bold block">// PANDUAN SAAT TIBA DI BASECAMP:</span>
            <ul class="list-disc pl-4 space-y-0.5">
              <li>Tunjukkan kode registrasi ini ke <strong>Kasir Loket</strong> Basecamp.</li>
              <li>Bawa fisik <strong>KTP Asli</strong> ketua tim untuk verifikasi manifest.</li>
              @if(!empty($tariffs['trash_deposit_enabled']))
                <li>Serahkan <strong>Deposit Sampah Rp {{ number_format($tariffs['trash_deposit_amount'] ?? 50000, 0, ',', '.') }}</strong> (dikembalikan saat turun membawa kantong sampah).</li>
              @else
                <li>Rombongan wajib menjaga kebersihan jalur (bebas biaya uang jaminan sampah).</li>
              @endif
            </ul>
          </div>

          <!-- Direct Ticket Access & Download Buttons -->
          <div class="grid grid-cols-2 gap-2 pt-1 font-mono-tech">
            <a id="btnViewDigitalTicket" href="#" target="_blank" class="tap-active py-2.5 px-3 bg-cyan-600 hover:bg-cyan-500 text-white font-bold text-xs uppercase tracking-wider rounded-lg transition flex items-center justify-center gap-1.5 shadow-lg shadow-cyan-600/20">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
              <span>SURAT JALAN DIGITAL</span>
            </a>

            <a id="btnDownloadTicketPdf" href="#" target="_blank" class="tap-active py-2.5 px-3 bg-[#FF5722] hover:bg-[#F4511E] text-black font-bold text-xs uppercase tracking-wider rounded-lg transition flex items-center justify-center gap-1.5 shadow-lg shadow-[#FF5722]/20">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
              <span>UNDUH PDF RESMI</span>
            </a>
          </div>

          <!-- Action Buttons -->
          <div class="space-y-2 pt-1 font-mono-tech">
            <a id="btnOpenWhatsApp" href="#" target="_blank" class="tap-active w-full py-3 bg-[#25D366] hover:bg-[#20BD5A] text-black font-bold text-xs uppercase tracking-wider rounded-lg transition flex items-center justify-center gap-2 shadow-lg shadow-[#25D366]/20">
              <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.007c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86.174.086.275.072.376-.044.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.043.072.043.419-.101.824z"/></svg>
              <span>BUKA CHAT WHATSAPP ADMIN</span>
            </a>

            <button type="button" id="btnCopyWhatsAppText" onclick="App.wizard.copyWhatsAppText()" class="tap-active w-full py-2.5 bg-[#181C24] hover:bg-[#202632] border border-[#2E3544] hover:border-[#25D366] text-stone-200 hover:text-white font-bold text-xs uppercase tracking-wider rounded-lg transition flex items-center justify-center gap-2">
              <svg class="w-4 h-4 text-[#25D366]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
              <span id="txtCopyWhatsApp">SALIN FORMAT PESAN WA</span>
            </button>

            <button type="button" onclick="App.modal.close('regModal')" class="tap-active w-full py-2 bg-[#121418] border border-[#2A303C] text-stone-400 hover:text-white font-bold text-xs uppercase tracking-wider rounded-lg transition">
              TUTUP & LIHAT BERANDA
            </button>
          </div>
        </div>

      </div>
    </div>
  </div>

  <!-- ======================================================== -->
  <!-- MODAL 2: DEDICATED TICKET TRACKING & PROOF UPLOAD        -->
  <!-- ======================================================== -->
  <div id="ticketModal" class="hidden fixed inset-0 z-50 bg-black/85 backdrop-blur-md flex items-center justify-center p-3 sm:p-4">
    <div class="bg-[#121418] border border-[#262B35] rounded-2xl w-full max-w-md max-h-[90vh] flex flex-col shadow-2xl overflow-hidden font-mono-tech">
      
      <!-- Top Modal Header (Sticky) -->
      <div class="p-4 pb-3 border-b border-[#1E2229] bg-[#121418] flex-shrink-0">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2">
            <div class="w-2.5 h-2.5 rounded-sm bg-[#FF5722]"></div>
            <div>
              <span class="text-[10px] text-[#FF5722] font-bold tracking-widest uppercase block leading-none">// SISTEM PELACAK MANDIRI</span>
              <h3 class="font-display font-bold text-base text-white leading-tight">STATUS SIMAKSI & TIKET</h3>
            </div>
          </div>
          <button type="button" onclick="App.modal.close('ticketModal')" class="tap-active w-8 h-8 rounded-full bg-[#181B22] border border-[#282D37] text-stone-400 hover:text-white flex items-center justify-center font-bold text-sm">
            ✕
          </button>
        </div>
        <p class="text-[10px] text-stone-400 mt-1 leading-tight">
          Lacak verifikasi permit dan unggah bukti transfer susulan jika sempat terclose saat bayar.
        </p>
      </div>

      <!-- Scrollable Tracking Body -->
      <div class="flex-1 overflow-y-auto p-4 space-y-4">
        
        <!-- Search Input Bar -->
        <div class="space-y-1.5">
          <label for="searchTicketInput" class="block text-[10px] text-stone-400 uppercase">
            CARI KODE REGISTRASI ATAU NO. WHATSAPP KETUA
          </label>
          <div class="flex gap-2">
            <input type="text" id="searchTicketInput" value="SMK-202611-001" placeholder="Contoh: SMK-202611-001 atau 0812..." onkeydown="if(event.key==='Enter') App.ticket.search()" class="flex-1 bg-[#181B22] border border-[#2A303C] rounded-lg px-3 py-2 text-xs text-white uppercase focus:outline-none focus:border-[#FF5722] transition">
            <button type="button" id="btnSearchTicket" onclick="App.ticket.search()" class="px-4 py-2 bg-[#FF5722] hover:bg-[#F4511E] text-black font-bold text-xs rounded-lg tap-active transition flex items-center gap-1.5">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
              <span>LACAK</span>
            </button>
          </div>

          <!-- Quick Chip Suggestions -->
          <div id="ticketQuickChips" class="flex flex-wrap gap-1.5 pt-1 text-[9px]">
            <span class="text-stone-500 py-0.5">Contoh Cepat:</span>
            <button type="button" onclick="App.ticket.quickSearch('SMK-202611-001')" class="px-2 py-0.5 rounded bg-[#181B22] border border-[#282D37] text-stone-300 hover:border-[#FF5722]">
              SMK-202611-001
            </button>
            <button type="button" onclick="App.ticket.quickSearch('SMK-202611-002')" class="px-2 py-0.5 rounded bg-[#181B22] border border-[#282D37] text-stone-300 hover:border-[#FF5722]">
              SMK-202611-002
            </button>
            <span id="recentTicketChipContainer"></span>
          </div>
        </div>

        <!-- Dynamic Result Container -->
        <div id="ticketSearchResult">
          <!-- Populated dynamically by App.ticket.search() -->
        </div>

      </div>
    </div>
  </div>

  <!-- ======================================================== -->
  <!-- MODAL 3: IMAGE PREVIEW LIGHTBOX MODAL                    -->
  <!-- ======================================================== -->
  <div id="imagePreviewModal" class="hidden fixed inset-0 z-50 bg-black/90 backdrop-blur-md flex items-center justify-center p-3 sm:p-4" onclick="if(event.target === this) App.modal.close('imagePreviewModal')">
    <div class="bg-[#121418] border border-[#262B35] rounded-2xl w-full max-w-xl max-h-[92vh] flex flex-col shadow-2xl overflow-hidden font-mono-tech">
      <div class="p-4 border-b border-[#1E2229] flex items-center justify-between shrink-0">
        <div class="flex items-center gap-2">
          <div class="w-2.5 h-2.5 rounded-sm bg-[#FF5722]"></div>
          <h3 id="imagePreviewTitle" class="font-display font-bold text-sm text-white">PREVIEW DOKUMEN</h3>
        </div>
        <button type="button" onclick="App.modal.close('imagePreviewModal')" class="w-8 h-8 rounded-full bg-[#181B22] border border-[#282D37] text-stone-400 hover:text-white flex items-center justify-center font-bold text-sm transition">
          ✕
        </button>
      </div>
      <div class="flex-1 overflow-auto p-4 flex items-center justify-center bg-[#07080A]">
        <img id="imagePreviewImg" src="" alt="Preview" class="max-h-[62vh] w-auto max-w-full object-contain rounded-lg border border-[#1E2229]">
      </div>
      <div class="p-3 border-t border-[#1E2229] flex items-center justify-between text-xs">
        <a id="imagePreviewRawLink" href="#" target="_blank" class="text-cyan-400 hover:underline text-[11px] flex items-center gap-1 font-bold">
          <span>Buka Resolusi Penuh ↗</span>
        </a>
        <button type="button" onclick="App.modal.close('imagePreviewModal')" class="px-4 py-1.5 rounded-lg bg-[#222731] hover:bg-[#2E3544] text-stone-200 font-bold text-[11px]">
          Tutup [ESC]
        </button>
      </div>
    </div>
  </div>

  <!-- Live Pricing, Profile & Telemetry Configuration Injection -->
  <script>
    window.SAMSARA_LIVE_PRICING = @json($tariffs ?? \App\Models\AppSetting::getTariffs());
    window.SAMSARA_EXPEDITION_PROFILE = @json($profile ?? \App\Models\AppSetting::getExpeditionProfile());
    window.SAMSARA_LIVE_TELEMETRY = @json($trailCondition ?? null);
  </script>

  <!-- Modular JavaScript Scripts -->
  <script src="{{ asset('assets/js/data.js') }}"></script>
  <script src="{{ asset('assets/js/app.js') }}"></script>
</body>
</html>
