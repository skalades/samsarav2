{{-- Navigasi bawah mobile: tombol Pesan di tengah --}}
<nav class="fixed bottom-0 inset-x-0 z-40 bg-surface/95 backdrop-blur-xl border-t border-line safe-pb md:hidden" aria-label="Navigasi">
  <div class="max-w-md mx-auto grid grid-cols-5 h-16 text-[11px] font-medium">
    <button type="button" onclick="App.nav.switchTab('sec-beranda')" data-tab="sec-beranda" class="nav-item flex flex-col items-center justify-center gap-1 text-muted">
      <x-ui.icon name="home" class="w-[22px] h-[22px]" /><span>Beranda</span>
    </button>
    <button type="button" onclick="App.nav.switchTab('sec-telemetry')" data-tab="sec-telemetry" class="nav-item flex flex-col items-center justify-center gap-1 text-muted">
      <x-ui.icon name="mountain" class="w-[22px] h-[22px]" /><span>Kondisi</span>
    </button>
    <button type="button" onclick="App.nav.switchTab('sec-booking')" data-tab="sec-booking" class="nav-item nav-cta flex flex-col items-center justify-center gap-0.5 text-muted">
      <span class="w-12 h-12 -mt-6 rounded-2xl bg-brand text-white flex items-center justify-center shadow-lg shadow-brand/30 ring-4 ring-canvas"><x-ui.icon name="plus" class="w-6 h-6" stroke="2.2" /></span>
      <span>Pesan</span>
    </button>
    <button type="button" onclick="App.nav.switchTab('sec-waypoints')" data-tab="sec-waypoints" class="nav-item flex flex-col items-center justify-center gap-1 text-muted">
      <x-ui.icon name="pin" class="w-[22px] h-[22px]" /><span>Rute</span>
    </button>
    <button type="button" onclick="App.modal.open('ticketModal')" class="nav-item flex flex-col items-center justify-center gap-1 text-muted">
      <x-ui.icon name="ticket" class="w-[22px] h-[22px]" /><span>Tiket</span>
    </button>
  </div>
</nav>
