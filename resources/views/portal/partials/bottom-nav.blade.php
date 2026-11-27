{{-- Navigasi bawah mobile: ringkas, ergonomis, tanpa elemen menjorok keluar --}}
<nav class="fixed bottom-0 inset-x-0 z-40 bg-surface/95 backdrop-blur-xl border-t border-line safe-pb md:hidden" aria-label="Navigasi">
  <div class="max-w-md mx-auto grid grid-cols-5 h-16 text-[11px] font-medium">
    <button type="button" onclick="App.nav.switchTab('sec-beranda')" data-tab="sec-beranda" class="nav-item flex flex-col items-center justify-center gap-1 text-muted active:bg-hover/50 transition">
      <x-ui.icon name="home" class="w-5 h-5" /><span>Beranda</span>
    </button>
    <button type="button" onclick="App.nav.switchTab('sec-telemetry')" data-tab="sec-telemetry" class="nav-item flex flex-col items-center justify-center gap-1 text-muted active:bg-hover/50 transition">
      <x-ui.icon name="mountain" class="w-5 h-5" /><span>Kondisi</span>
    </button>
    <button type="button" onclick="App.nav.switchTab('sec-booking')" data-tab="sec-booking" class="nav-item nav-cta flex flex-col items-center justify-center gap-1 text-muted active:bg-hover/50 transition">
      <span class="w-10 h-8 rounded-xl bg-brand text-white flex items-center justify-center shadow-md shadow-brand/30"><x-ui.icon name="plus" class="w-5 h-5" stroke="2.2" /></span>
      <span class="leading-none font-semibold text-brand">Pesan</span>
    </button>
    <button type="button" onclick="App.nav.switchTab('sec-waypoints')" data-tab="sec-waypoints" class="nav-item flex flex-col items-center justify-center gap-1 text-muted active:bg-hover/50 transition">
      <x-ui.icon name="pin" class="w-5 h-5" /><span>Rute</span>
    </button>
    <button type="button" onclick="App.nav.switchTab('sec-logistik')" data-tab="sec-logistik" class="nav-item flex flex-col items-center justify-center gap-1 text-muted active:bg-hover/50 transition">
      <x-ui.icon name="box" class="w-5 h-5" /><span>Layanan</span>
    </button>
  </div>
</nav>
