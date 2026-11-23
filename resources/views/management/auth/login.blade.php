<!DOCTYPE html>
<html lang="id" class="h-full bg-[#0A0B0D] text-stone-200">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login Petugas Operasional — SAMSARA EXPEDITION</title>
  
  <!-- Tailwind CSS Framework CDN -->
  <script src="https://www.gstatic.com/antigravity/web/dev/tailwindcss.min.js"></script>

  <!-- Google Fonts: Space Grotesk + JetBrains Mono -->
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
    }

    .font-display { font-family: var(--font-display); }
    .font-mono-tech { font-family: var(--font-mono); }
  </style>
</head>
<body class="h-full flex items-center justify-center p-4">

  <div class="w-full max-w-md space-y-6">
    
    <!-- Brand Header -->
    <div class="text-center space-y-2">
      <div class="w-12 h-12 mx-auto rounded-xl bg-[#16191F] border border-[#282E3A] flex items-center justify-center font-mono-tech text-base font-bold text-[#FF5722] shadow-xl shadow-[#FF5722]/10">
        ▲
      </div>
      <div>
        <h1 class="font-display font-black text-2xl text-white tracking-wider uppercase">{{ \App\Models\AppSetting::get('expedition_name', 'SAMSARA') }} OPS</h1>
        <p class="font-mono-tech text-xs text-stone-400 mt-0.5">SISTEM MANAJEMEN PENDAKIAN {{ strtoupper(\App\Models\AppSetting::get('mountain_name', 'CIKURAY')) }} {{ \App\Models\AppSetting::get('mountain_elevation', '2.821M') }}</p>
      </div>
    </div>

    <!-- Login Box -->
    <div class="bg-[#121418] border border-[#20242C] rounded-2xl p-6 shadow-2xl space-y-5">
      
      <div class="pb-3 border-b border-[#1E2229] flex items-center justify-between">
        <span class="font-mono-tech text-xs font-bold text-white uppercase tracking-wider">AUTENTIKASI PETUGAS</span>
        <span class="px-2 py-0.5 rounded bg-emerald-500/10 border border-emerald-500/30 text-[9px] font-mono-tech text-emerald-400 font-bold">
          SISTEM TERENKRIPSI
        </span>
      </div>

      @if (isset($errors) && $errors->any())
        <div class="p-3 bg-rose-950/60 border border-rose-500/30 rounded-lg text-xs font-mono-tech text-rose-300">
          {{ $errors->first() }}
        </div>
      @endif

      <form action="{{ route('management.login.submit') }}" method="POST" class="space-y-4">
        @csrf
        <div>
          <label class="block font-mono-tech text-[10px] text-stone-400 uppercase tracking-wider mb-1.5">EMAIL RESMI PETUGAS</label>
          <input type="email" name="email" value="{{ old('email') }}" placeholder="petugas@samsara.id" required autofocus class="w-full bg-[#181B22] border border-[#2A303C] rounded-lg px-3 py-2.5 text-xs font-mono-tech text-white focus:outline-none focus:border-[#FF5722] transition">
        </div>

        <div>
          <label class="block font-mono-tech text-[10px] text-stone-400 uppercase tracking-wider mb-1.5">KATA SANDI</label>
          <input type="password" name="password" value="" placeholder="••••••••" required class="w-full bg-[#181B22] border border-[#2A303C] rounded-lg px-3 py-2.5 text-xs font-mono-tech text-white focus:outline-none focus:border-[#FF5722] transition">
        </div>

        <button type="submit" class="w-full py-3 bg-[#FF5722] hover:bg-[#F4511E] text-black font-mono-tech font-bold text-xs uppercase tracking-wider rounded-lg transition shadow-lg shadow-[#FF5722]/20">
          MASUK KE SISTEM MANAJEMEN
        </button>
      </form>

      @if (!app()->environment('production') && config('app.allow_quick_login', false))
      <!-- Quick Role Selection (Testing Only - Disabled in Production) -->
      <div class="pt-4 border-t border-[#1E2229] space-y-2.5">
        <span class="block font-mono-tech text-[10px] text-amber-400 uppercase tracking-wider text-center">
          // MASUK CEPAT (DEVELOPMENT ONLY):
        </span>
        
        <!-- Merged Kasir All-in-One Role -->
        <a href="{{ route('management.quick_login', ['role' => 'kasir']) }}" class="block p-3 rounded-xl bg-gradient-to-r from-[#FF5722]/20 via-[#16191F] to-[#16191F] border border-[#FF5722]/50 hover:border-[#FF5722] text-left transition group">
          <div class="flex items-center justify-between font-mono-tech">
            <span class="text-xs font-bold text-white group-hover:text-[#FF5722]">KASIR OPERASIONAL LOKET</span>
            <span class="text-[9px] px-1.5 py-0.5 rounded bg-[#FF5722] text-black font-bold">ALL-IN-ONE</span>
          </div>
          <p class="font-mono-tech text-[10px] text-stone-300 mt-1">
            Kasir POS • Verifikasi SIMAKSI • Gate Check-in • Inventori Sewa Alat
          </p>
        </a>

        <div class="grid grid-cols-2 gap-2 font-mono-tech text-[10px]">
          <a href="{{ route('management.quick_login', ['role' => 'superadmin']) }}" class="p-2 text-center rounded bg-[#16191F] border border-[#262B35] text-stone-300 hover:text-[#FF5722] hover:border-[#FF5722]/40 transition">
            SUPERADMIN
          </a>
          <a href="{{ route('management.quick_login', ['role' => 'auditor']) }}" class="p-2 text-center rounded bg-[#16191F] border border-[#262B35] text-stone-300 hover:text-[#FF5722] hover:border-[#FF5722]/40 transition">
            AUDITOR SISTEM
          </a>
        </div>
      </div>
      @endif

    </div>

    <!-- Back to Public Portal -->
    <div class="text-center font-mono-tech text-xs text-stone-400">
      <a href="{{ route('portal.home') }}" class="hover:text-stone-300 transition">← Kembali ke Portal Publik SAMSARA</a>
    </div>

  </div>

</body>
</html>
