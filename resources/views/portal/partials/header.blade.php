{{-- Header portal: logo, tab desktop, tombol cek tiket --}}
@php
  $tabs = [
    'sec-beranda' => 'Beranda',
    'sec-booking' => 'Reservasi',
    'sec-telemetry' => 'Kondisi jalur',
    'sec-waypoints' => 'Rute & pos',
    'sec-logistik' => 'Layanan',
  ];
@endphp
<header class="sticky top-0 z-40 bg-canvas/85 backdrop-blur-xl border-b border-line safe-top">
  <div class="max-w-5xl mx-auto h-14 md:h-16 px-3.5 sm:px-6 flex items-center justify-between gap-2.5 sm:gap-4">
    <a href="#" onclick="App.nav.switchTab('sec-beranda'); return false;" class="flex items-center gap-2.5 min-w-0">
      @if(!empty($profile['app_logo']))
        <img src="{{ asset('storage/'.$profile['app_logo']) }}" alt="Logo" class="w-9 h-9 md:w-10 md:h-10 shrink-0 rounded-xl object-contain bg-raised ring-1 ring-line p-0.5">
      @else
        <span class="w-9 h-9 md:w-10 md:h-10 shrink-0 rounded-xl bg-brand/15 ring-1 ring-brand/30 flex items-center justify-center text-brand"><x-ui.icon name="mountain" class="w-5 h-5" /></span>
      @endif
      <span class="leading-tight min-w-0">
        <span class="block font-display font-bold text-sm md:text-[15px] text-ink truncate">{{ $profile['expedition_name'] ?? 'SAMSARA EXPEDITION' }}</span>
        <span class="block text-[11px] text-muted truncate">{{ $profile['mountain_name'] ?? 'Gunung Cikuray' }} · {{ $profile['mountain_elevation'] ?? '2.821 MDPL' }}</span>
      </span>
    </a>

    <nav class="hidden md:flex items-center gap-1" aria-label="Navigasi utama">
      @foreach($tabs as $id => $label)
        <button type="button" onclick="App.nav.switchTab('{{ $id }}')" data-tab="{{ $id }}" class="tab-btn h-9 px-3 rounded-xl text-[13px] font-semibold text-muted hover:text-ink transition whitespace-nowrap">{{ $label }}</button>
      @endforeach
    </nav>

    <div class="flex items-center gap-2 shrink-0">
      <button type="button" onclick="App.modal.open('ticketModal')" class="tap-active h-9 md:h-10 px-3 sm:px-3.5 rounded-xl bg-raised ring-1 ring-line-strong text-xs sm:text-sm font-semibold text-ink flex items-center gap-1.5 hover:bg-hover whitespace-nowrap">
        <x-ui.icon name="ticket" class="w-4 h-4 text-brand shrink-0" /><span>Cek tiket</span>
      </button>
      <button type="button" onclick="App.nav.switchTab('sec-booking')" class="tap-active hidden md:flex h-10 px-4 rounded-xl bg-brand hover:bg-brand-hover text-white text-sm font-semibold items-center gap-2 shadow-lg shadow-brand/20 whitespace-nowrap">
        Pesan SIMAKSI
      </button>
    </div>
  </div>
</header>
