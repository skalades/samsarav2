@php
  $logo = \App\Models\AppSetting::get('app_logo');
  $mountain = \App\Models\AppSetting::get('mountain_name', 'Gunung Cikuray');
  $elevation = \App\Models\AppSetting::get('mountain_elevation', '2.821 MDPL');
  $levelMeta = [
    'superadmin' => ['tone' => 'brand', 'icon' => 'shield'],
    'kasir' => ['tone' => 'success', 'icon' => 'cash'],
    'gatekeeper' => ['tone' => 'info', 'icon' => 'gate'],
    'ranger' => ['tone' => 'violet', 'icon' => 'pin'],
  ];
@endphp
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
  @include('management.partials.head')
  <meta name="robots" content="noindex, nofollow">
  <title>Masuk Petugas — {{ \App\Models\AppSetting::get('expedition_name', 'SAMSARA EXPEDITION') }}</title>
</head>
<body class="min-h-full bg-canvas antialiased">
  <div class="min-h-screen grid lg:grid-cols-2">

    {{-- Panel visual (desktop) --}}
    <div class="hidden lg:flex relative overflow-hidden bg-surface border-r border-line p-8 xl:p-12 flex-col justify-between">
      <svg class="absolute inset-x-0 bottom-0 w-full text-raised" viewBox="0 0 800 300" preserveAspectRatio="none" aria-hidden="true">
        <path fill="currentColor" d="M0 300 L0 210 L120 150 L210 190 L330 80 L420 140 L500 60 L610 170 L700 120 L800 180 L800 300 Z"/>
        <path fill="#0B0D10" opacity=".6" d="M0 300 L0 250 L150 200 L260 240 L380 170 L480 220 L600 190 L720 240 L800 220 L800 300 Z"/>
      </svg>
      <div class="relative flex items-center gap-3">
        <span class="w-10 h-10 rounded-xl bg-brand/15 ring-1 ring-brand/30 flex items-center justify-center text-brand"><x-ui.icon name="mountain" class="w-5 h-5" /></span>
        <div>
          <p class="font-display font-bold text-base text-ink leading-none">SAMSARA OPS</p>
          <p class="text-xs text-muted mt-1">{{ $mountain }} · {{ $elevation }}</p>
        </div>
      </div>
      <div class="relative max-w-md">
        <h1 class="font-display font-bold text-3xl xl:text-4xl text-ink leading-tight">Satu sistem untuk seluruh tim basecamp.</h1>
        <p class="text-sm text-muted mt-3 leading-relaxed">Kasir, Gatekeeper, dan Ranger bekerja di alur yang sama — dari verifikasi bayar, pemeriksaan logistik, sampai rombongan kembali dengan selamat.</p>
        <div class="grid grid-cols-2 gap-2.5 mt-6">
          @foreach(\App\Models\User::ROLES as $key => $role)
            <div class="p-3 rounded-xl bg-canvas/60 ring-1 ring-line">
              <x-ui.icon :name="$levelMeta[$key]['icon']" class="w-5 h-5 text-brand" />
              <p class="text-sm font-semibold text-ink mt-2">{{ $role['label'] }}</p>
            </div>
          @endforeach
        </div>
      </div>
      <p class="relative text-xs text-subtle">© {{ date('Y') }} {{ \App\Models\AppSetting::get('basecamp_name', 'Basecamp SAMSARA') }}</p>
    </div>

    {{-- Form --}}
    <div class="flex flex-col justify-center px-5 py-10 sm:px-10">
      <div class="w-full max-w-sm mx-auto">
        <div class="lg:hidden flex flex-col items-center text-center mb-8">
          @if($logo)
            <img src="{{ asset('storage/'.$logo) }}" alt="Logo" class="w-16 h-16 rounded-2xl object-contain bg-raised ring-1 ring-line p-1">
          @else
            <span class="w-16 h-16 rounded-2xl bg-brand/15 ring-1 ring-brand/30 flex items-center justify-center text-brand"><x-ui.icon name="mountain" class="w-8 h-8" /></span>
          @endif
          <p class="font-display font-bold text-2xl text-ink mt-4">SAMSARA OPS</p>
          <p class="text-sm text-muted">{{ $mountain }} · {{ $elevation }}</p>
        </div>

        <h2 class="font-display font-bold text-2xl text-ink">Masuk petugas</h2>
        <p class="text-sm text-muted mt-1">Menu yang tampil menyesuaikan level akses akun Anda.</p>

        @if (isset($errors) && $errors->any())
          <div class="mt-5 flex gap-3 rounded-2xl bg-rose-500/10 ring-1 ring-rose-500/25 px-4 py-3 text-sm text-rose-200">
            <x-ui.icon name="alert" class="w-5 h-5 text-rose-400 shrink-0" />
            <p>{{ $errors->first() }}</p>
          </div>
        @endif
        @if (session('error'))
          <div class="mt-5 flex gap-3 rounded-2xl bg-rose-500/10 ring-1 ring-rose-500/25 px-4 py-3 text-sm text-rose-200">
            <x-ui.icon name="alert" class="w-5 h-5 text-rose-400 shrink-0" />
            <p>{{ session('error') }}</p>
          </div>
        @endif

        <form action="{{ route('management.login.submit') }}" method="POST" class="mt-6 space-y-4">
          @csrf
          <div>
            <label class="label" for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="nama@samsara.id" required autofocus autocomplete="username" class="field !h-12">
          </div>
          <div>
            <label class="label" for="password">Kata sandi</label>
            <input id="password" type="password" name="password" placeholder="••••••••" required autocomplete="current-password" class="field !h-12">
          </div>
          <label class="flex items-center gap-2 text-sm text-muted">
            <input type="checkbox" name="remember" value="1" class="w-4 h-4 accent-orange-500"> Ingat saya di perangkat ini
          </label>
          <x-ui.button type="submit" size="lg" block>Masuk</x-ui.button>
        </form>

        @if (!app()->environment('production') && config('app.allow_quick_login', false))
          <div class="mt-8">
            <p class="text-xs font-semibold text-subtle uppercase tracking-wider mb-2">Masuk cepat (mode uji)</p>
            <div class="grid grid-cols-2 gap-2">
              @foreach(\App\Models\User::ROLES as $key => $role)
                <a href="{{ route('management.quick_login', ['role' => $key]) }}" class="p-3 rounded-xl bg-surface ring-1 ring-line hover:ring-brand/40">
                  <x-ui.badge :tone="$levelMeta[$key]['tone']" size="xs">{{ $role['label'] }}</x-ui.badge>
                </a>
              @endforeach
            </div>
          </div>
        @endif

        <a href="{{ route('portal.home') }}" class="mt-8 inline-flex items-center gap-1.5 text-sm text-muted hover:text-ink">
          <x-ui.icon name="chevron-right" class="w-4 h-4 rotate-180" /> Portal publik
        </a>
      </div>
    </div>
  </div>
</body>
</html>
