{{-- Lokasi resmi basecamp + peta Google Maps --}}
@php
  $mapsUrl = $profile['basecamp_maps_url'] ?? '';
  $coords = $profile['basecamp_coordinates'] ?? '-7.331855, 107.8096294';
  $bcName = $profile['basecamp_name'] ?? 'Basecamp SAMSARA';
@endphp
<section id="basecamp-location" class="rounded-3xl bg-surface ring-1 ring-line overflow-hidden">
  <div class="grid grid-cols-1 lg:grid-cols-12">
    <div class="lg:col-span-5 p-5 md:p-7 flex flex-col">
      <div class="flex flex-wrap items-center gap-2">
        <p class="text-xs font-semibold uppercase tracking-wider text-brand">Titik kumpul</p>
        <span class="inline-flex items-center gap-1.5 h-6 px-2.5 rounded-full bg-emerald-500/10 ring-1 ring-inset ring-emerald-500/25 text-[11px] font-semibold text-emerald-300">
          <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span> Gerbang buka 24 jam
        </span>
      </div>
      <h2 class="font-display font-bold text-xl md:text-2xl text-ink mt-2">Lokasi resmi {{ $bcName }}</h2>
      <p class="text-sm text-muted mt-1.5">{{ $profile['basecamp_location'] ?? 'Kecamatan Cilawu / Bayongbong, Garut, Jawa Barat' }} · {{ $profile['basecamp_elevation'] ?? '1.245 MDPL' }}</p>

      <div class="mt-4 flex items-center justify-between gap-2 p-3 rounded-xl bg-raised/60 ring-1 ring-line">
        <div class="min-w-0">
          <p class="text-[11px] text-subtle">Koordinat GPS</p>
          <p id="txtCoordinates" class="font-code text-sm text-emerald-300 truncate">{{ $coords }}</p>
        </div>
        <button type="button" onclick="navigator.clipboard.writeText(@js($coords)); const b = this; const t = b.innerText; b.innerText = 'Tersalin'; setTimeout(() => b.innerText = t, 1500);" class="h-9 px-3 rounded-lg bg-canvas ring-1 ring-line-strong text-xs font-semibold text-ink hover:bg-hover shrink-0">Salin</button>
      </div>

      <ul class="mt-4 space-y-3 text-sm text-muted">
        <li class="flex gap-3"><x-ui.icon name="gate" class="w-5 h-5 text-brand shrink-0 mt-0.5" /><span><strong class="text-ink">Kendaraan pribadi:</strong> jalan aspal sampai halaman parkir basecamp, untuk motor maupun mobil.</span></li>
        <li class="flex gap-3"><x-ui.icon name="users" class="w-5 h-5 text-brand shrink-0 mt-0.5" /><span><strong class="text-ink">Transportasi umum:</strong> dari Terminal Guntur Garut naik angkot jurusan Cikajang / Bayongbong, turun di simpang jalur Samsara, lanjut ojek lokal.</span></li>
        <li class="flex gap-3"><x-ui.icon name="bolt" class="w-5 h-5 text-brand shrink-0 mt-0.5" /><span><strong class="text-ink">Mulai mendaki:</strong> ojek resmi basecamp mengantar ke Pos 1 untuk menghemat tenaga.</span></li>
      </ul>

      <a href="{{ $mapsUrl }}" target="_blank" rel="noopener noreferrer" class="tap-active mt-6 lg:mt-auto h-12 px-5 rounded-xl bg-brand hover:bg-brand-hover text-white font-semibold text-sm flex items-center justify-center gap-2">
        <x-ui.icon name="pin" class="w-5 h-5" /> BUKA NAVIGASI GOOGLE MAPS
      </a>
    </div>

    <div class="lg:col-span-7 relative min-h-[300px] lg:min-h-[420px] bg-canvas">
      <iframe
        title="Peta Navigasi {{ $bcName }}"
        src="https://www.google.com/maps?q={{ urlencode($profile['basecamp_coordinates'] ?? '-7.331855,107.8096294') }}&hl=id&z=15&output=embed"
        class="absolute inset-0 w-full h-full border-0 opacity-90 hover:opacity-100 transition"
        allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
      <a href="{{ $mapsUrl }}" target="_blank" rel="noopener noreferrer" class="absolute bottom-3 right-3 h-9 px-3 rounded-lg bg-canvas/90 backdrop-blur ring-1 ring-line-strong text-xs font-semibold text-ink flex items-center gap-1.5 hover:bg-hover">
        Buka di aplikasi Maps <x-ui.icon name="external" class="w-3.5 h-3.5" />
      </a>
    </div>
  </div>
</section>
