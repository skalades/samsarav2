{{-- Peta jalur interaktif + perencana waktu tempuh. Param: $mapId (unik per halaman) --}}
@php
  $mapId = $mapId ?? 'trailMap';
  $trailRoute = \App\Models\AppSetting::getTrailRoute();
  $trailRoute[0]['name'] = $profile['basecamp_name'] ?? 'Basecamp SAMSARA';
@endphp

<div class="tm-block grid grid-cols-1 lg:grid-cols-5 gap-3 lg:gap-4" data-trail-map="{{ $mapId }}">

  {{-- ============ KOLOM PETA ============ --}}
  <div class="lg:col-span-3 flex flex-col gap-3 min-w-0">
    <div class="tm-frame relative rounded-2xl overflow-hidden ring-1 ring-line bg-raised" id="{{ $mapId }}Frame">
      <div id="{{ $mapId }}" class="tm-canvas w-full h-[300px] sm:h-[380px] lg:h-[500px]" role="application" aria-label="Peta interaktif jalur pendakian"></div>
      <div class="tm-loading absolute inset-0 flex flex-col items-center justify-center gap-2 text-sm text-muted pointer-events-none">
        <span class="w-6 h-6 rounded-full border-2 border-line-strong border-t-brand animate-spin"></span>Memuat peta…
      </div>

      <div class="absolute top-3 left-3 z-[500] flex rounded-xl bg-canvas/85 backdrop-blur ring-1 ring-line p-1 text-xs font-semibold">
        <button type="button" data-tm-layer="sat" class="tm-layer px-3 h-8 rounded-lg bg-brand text-white">Satelit</button>
        <button type="button" data-tm-layer="topo" class="tm-layer px-3 h-8 rounded-lg text-muted">Topografi</button>
      </div>
      <div class="absolute top-3 right-3 z-[500] flex flex-col gap-2">
        <button type="button" data-tm-full class="w-10 h-10 rounded-xl bg-canvas/85 backdrop-blur ring-1 ring-line text-ink flex items-center justify-center" aria-label="Layar penuh">
          <svg class="tm-ico-open w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24"><path d="M4 9V4h5M20 9V4h-5M4 15v5h5M20 15v5h-5"/></svg>
          <svg class="tm-ico-close w-5 h-5 hidden" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24"><path d="M6 6l12 12M18 6L6 18"/></svg>
        </button>
        <button type="button" data-tm-fit class="w-10 h-10 rounded-xl bg-canvas/85 backdrop-blur ring-1 ring-line text-ink flex items-center justify-center" aria-label="Tampilkan seluruh jalur">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3"/></svg>
        </button>
      </div>

      {{-- Ringkasan segmen aktif di atas peta --}}
      <div class="absolute bottom-3 left-3 right-14 z-[500] pointer-events-none">
        <div class="tm-overlay inline-flex max-w-full items-center gap-2 rounded-xl bg-canvas/90 backdrop-blur ring-1 ring-line px-3 py-2 text-xs text-ink"></div>
      </div>
    </div>

    {{-- Profil elevasi interaktif --}}
    <div class="rounded-2xl bg-raised/60 ring-1 ring-line p-3 select-none">
      <div class="flex items-center justify-between gap-2 text-[11px] text-muted mb-2">
        <span class="font-semibold text-ink">Profil elevasi</span>
        <span class="tm-scrub-label tabular-nums text-right">Geser grafik untuk melacak di peta</span>
      </div>
      <div class="tm-profile relative" style="touch-action: pan-y"></div>
      <div class="mt-2 flex flex-wrap gap-x-3 gap-y-1 text-[10px] text-muted">
        <span class="flex items-center gap-1"><i class="w-3 h-1.5 rounded bg-emerald-400"></i>Landai &lt;15%</span>
        <span class="flex items-center gap-1"><i class="w-3 h-1.5 rounded bg-amber-400"></i>Sedang 15–30%</span>
        <span class="flex items-center gap-1"><i class="w-3 h-1.5 rounded bg-orange-500"></i>Terjal 30–45%</span>
        <span class="flex items-center gap-1"><i class="w-3 h-1.5 rounded bg-rose-500"></i>Sangat terjal &gt;45%</span>
      </div>
    </div>
  </div>

  {{-- ============ KOLOM PANEL ============ --}}
  <div class="lg:col-span-2 flex flex-col gap-3 min-w-0">

    {{-- Pengaturan perjalanan --}}
    <div class="rounded-2xl bg-raised/60 ring-1 ring-line p-3 space-y-3">
      <div class="grid grid-cols-2 gap-1 p-1 rounded-xl bg-canvas ring-1 ring-line text-sm font-semibold">
        <button type="button" data-tm-dir="up" class="tm-dir h-9 rounded-lg bg-brand text-white flex items-center justify-center gap-1.5"><x-ui.icon name="arrow-up" class="w-4 h-4" />Naik</button>
        <button type="button" data-tm-dir="down" class="tm-dir h-9 rounded-lg text-muted flex items-center justify-center gap-1.5"><x-ui.icon name="arrow-down" class="w-4 h-4" />Turun</button>
      </div>
      <div class="grid grid-cols-[1fr_auto] gap-2">
        <label class="min-w-0 flex items-center justify-between gap-2 h-11 px-3 rounded-xl bg-canvas ring-1 ring-line text-xs text-muted">
          <span class="tm-start-label truncate">Berangkat</span>
          <input type="time" data-tm-start value="07:00" class="w-[112px] text-right bg-transparent text-ink text-sm font-semibold tabular-nums outline-none [color-scheme:dark]">
        </label>
        <label class="flex items-center gap-2 h-11 px-3 rounded-xl bg-canvas ring-1 ring-line text-xs text-ink cursor-pointer whitespace-nowrap">
          <input type="checkbox" data-tm-ojek class="accent-orange-500 w-4 h-4"> Ojek
        </label>
      </div>
      <div class="tm-summary grid grid-cols-3 gap-2"></div>
    </div>

    {{-- Segmen aktif --}}
    <div class="tm-detail rounded-2xl bg-surface ring-1 ring-line-strong p-4"></div>

    {{-- Itinerary --}}
    <div class="rounded-2xl bg-raised/60 ring-1 ring-line overflow-hidden">
      <div class="flex items-center justify-between px-4 pt-3 pb-2">
        <p class="text-xs font-semibold text-ink">Jadwal perjalanan</p>
        <p class="text-[10px] text-subtle">belum termasuk istirahat</p>
      </div>
      <ol class="tm-itin relative max-h-[320px] overflow-y-auto pb-2"></ol>
    </div>
  </div>
</div>

@once
<script>window.SAMSARA_TRAIL = @json($trailRoute);</script>
<style>
  .tm-frame.tm-full { position: fixed; inset: 0; z-index: 80; border-radius: 0; }
  .tm-frame.tm-full .tm-canvas { height: 100dvh !important; }
  .leaflet-container { background: #181C22; font-family: 'Inter', sans-serif; }
  .leaflet-control-attribution { background: rgba(11,13,16,.75) !important; color: #6E7887; font-size: 9px; }
  .leaflet-control-attribution a { color: #A0A9B6; }
  .leaflet-control-zoom a { background: #12151A !important; color: #ECEFF3 !important; border-color: #2F3641 !important; }
  .tm-pin { display: flex; align-items: center; justify-content: center; border-radius: 9999px; font: 700 11px/1 'Inter', sans-serif; color: #fff; border: 2px solid #fff; box-shadow: 0 2px 8px rgba(0,0,0,.6); transition: transform .2s, box-shadow .2s; }
  .tm-pin.is-active { transform: scale(1.3); box-shadow: 0 0 0 6px rgba(255,87,34,.35), 0 2px 10px rgba(0,0,0,.7); }
  .tm-pin.is-dim { opacity: .75; }
  .leaflet-tooltip.tm-seg-tip { background: #0B0D10; color: #ECEFF3; border: 1px solid #FF5722; border-radius: 8px; font: 600 11px/1.2 'Inter', sans-serif; padding: 4px 8px; box-shadow: 0 4px 14px rgba(0,0,0,.5); }
  .leaflet-tooltip.tm-seg-tip::before { display: none; }
  .tm-itin-row.is-active { background: rgba(255,87,34,.08); }
  .tm-itin-row.is-active .tm-itin-name { color: #FF8A65; }
</style>
<script>
(function () {
  var LCSS = 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css';
  var LJS = 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js';
  var loading = null;
  function loadLeaflet() {
    if (window.L) return Promise.resolve();
    if (loading) return loading;
    function add(tag, attrs) { return new Promise(function (res, rej) { var e = document.createElement(tag); for (var k in attrs) e[k] = attrs[k]; e.onload = res; e.onerror = rej; document.head.appendChild(e); }); }
    loading = Promise.all([add('link', { rel: 'stylesheet', href: LCSS }), add('script', { src: LJS })]);
    return loading;
  }

  var isTouch = window.matchMedia('(pointer: coarse)').matches;
  function esc(t) { return String(t == null ? '' : t).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }); }
  function num(n) { return Math.round(n).toLocaleString('id-ID'); }
  function km(m) { return m < 1000 ? num(m) + ' m' : (m / 1000).toLocaleString('id-ID', { maximumFractionDigits: 1 }) + ' km'; }
  function dur(min) { var h = Math.floor(min / 60), m = min % 60; return h ? h + 'j' + (m ? ' ' + m + 'm' : '') : m + ' mnt'; }
  function clock(base, add) { var t = (base + add) % 1440; return String(Math.floor(t / 60)).padStart(2, '0') + ':' + String(t % 60).padStart(2, '0'); }
  function short(p) { return p.type === 'pos' ? p.code.replace('POS', 'Pos') : p.type === 'basecamp' ? 'Basecamp' : p.type === 'peak' ? 'Puncak' : p.name; }
  function label(p) { return p.type === 'basecamp' ? 'BC' : p.type === 'peak' ? '▲' : p.type === 'landmark' ? '≈' : p.code.replace(/\D/g, ''); }
  function pinColor(p) { return p.type === 'peak' ? '#FF5722' : p.type === 'basecamp' ? '#10B981' : p.camp ? '#0EA5E9' : p.type === 'landmark' ? '#475161' : '#0B0D10'; }
  function hv(a, b) {
    var r = Math.PI / 180, dLat = (b[0] - a[0]) * r, dLng = (b[1] - a[1]) * r;
    var h = Math.sin(dLat / 2) * Math.sin(dLat / 2) + Math.cos(a[0] * r) * Math.cos(b[0] * r) * Math.sin(dLng / 2) * Math.sin(dLng / 2);
    return 2 * 6371000 * Math.asin(Math.sqrt(h));
  }
  function diff(g) {
    if (g < 15) return { t: 'Landai', c: '#34D399', cls: 'text-emerald-300 bg-emerald-500/10 ring-emerald-500/30' };
    if (g < 30) return { t: 'Sedang', c: '#FBBF24', cls: 'text-amber-300 bg-amber-500/10 ring-amber-500/30' };
    if (g < 45) return { t: 'Terjal', c: '#F97316', cls: 'text-orange-300 bg-orange-500/10 ring-orange-500/30' };
    return { t: 'Sangat terjal', c: '#F43F5E', cls: 'text-rose-300 bg-rose-500/10 ring-rose-500/30' };
  }

  function TrailWidget(block) {
    var self = this;
    this.block = block;
    this.id = block.getAttribute('data-trail-map');
    this.P = window.SAMSARA_TRAIL || [];
    this.dir = 'up';
    this.ojek = false;
    this.active = 1;               // indeks titik tujuan (segmen dari active-1 → active)
    this.map = null;
    this.$ = function (s) { return block.querySelector(s); };

    // Titik profil sepanjang jalur asli: {d (m dari basecamp), e, lat, lng, seg}
    this.prof = [];
    for (var si = 1; si < this.P.length; si++) {
      var ln = this.P[si].seg.line, along = [0];
      for (var k = 1; k < ln.length; k++) along.push(along[k - 1] + hv(ln[k - 1], ln[k]));
      var scale = this.P[si].seg.dist_m / Math.max(1, along[along.length - 1]), off = this.P[si - 1].cum.dist_m;
      for (k = si === 1 ? 0 : 1; k < ln.length; k++) this.prof.push({ d: off + along[k] * scale, e: ln[k][2], lat: ln[k][0], lng: ln[k][1], seg: si });
    }

    block.querySelectorAll('[data-tm-dir]').forEach(function (b) {
      b.addEventListener('click', function () {
        self.dir = b.getAttribute('data-tm-dir');
        block.querySelectorAll('[data-tm-dir]').forEach(function (o) { var on = o === b; o.classList.toggle('bg-brand', on); o.classList.toggle('text-white', on); o.classList.toggle('text-muted', !on); });
        self.$('[data-tm-start]').value = self.dir === 'up' ? '07:00' : '06:00';
        self.$('.tm-start-label').textContent = self.dir === 'up' ? 'Berangkat' : 'Mulai turun';
        self.render();
      });
    });
    this.$('[data-tm-start]').addEventListener('input', function () { self.render(); });
    this.$('[data-tm-ojek]').addEventListener('change', function (e) { self.ojek = e.target.checked; self.render(); });
    this.$('.tm-start-label').textContent = 'Berangkat';
    this.render();
  }

  // Waktu segmen (titik i-1 ⇄ i) sesuai arah & opsi ojek
  TrailWidget.prototype.segMin = function (i) {
    var p = this.P[i];
    if (i === 1 && this.ojek && p.ojek_min) return p.ojek_min;
    return this.dir === 'up' ? p.seg.up_min : p.seg.down_min;
  };
  TrailWidget.prototype.startMin = function () {
    var v = (this.$('[data-tm-start]').value || '07:00').split(':');
    return (+v[0]) * 60 + (+v[1] || 0);
  };
  // Urutan titik sesuai arah perjalanan
  TrailWidget.prototype.order = function () {
    var idx = this.P.map(function (_, i) { return i; });
    return this.dir === 'up' ? idx : idx.reverse();
  };
  TrailWidget.prototype.etas = function () {
    var self = this, start = this.startMin(), acc = 0, out = {};
    this.order().forEach(function (i, k, arr) {
      if (k > 0) { var prev = arr[k - 1]; acc += self.segMin(Math.max(i, prev)); }
      out[i] = acc;
    });
    out.total = acc; out.start = start;
    return out;
  };

  TrailWidget.prototype.select = function (i, fly) {
    this.active = Math.min(Math.max(1, i), this.P.length - 1);
    this.render();
    if (fly !== false && this.map) {
      var ln = this.P[this.active].seg.line.map(function (q) { return [q[0], q[1]]; });
      this.map.flyToBounds(ln, { padding: [70, 70], maxZoom: 17, duration: .6 });
    }
  };

  TrailWidget.prototype.render = function () {
    var self = this, P = this.P, eta = this.etas(), up = this.dir === 'up';
    var last = P[P.length - 1], totalDist = last.cum.dist_m;

    // Ringkasan
    var endName = up ? 'Tiba di puncak' : 'Tiba di basecamp';
    this.$('.tm-summary').innerHTML = [
      ['Jarak total', km(totalDist), ''],
      ['Waktu ' + (up ? 'naik' : 'turun'), dur(eta.total), ''],
      [endName, clock(eta.start, eta.total), 'text-brand-soft'],
    ].map(function (x) {
      return '<div class="rounded-xl bg-canvas ring-1 ring-line px-2.5 py-2"><p class="text-[10px] text-muted leading-tight">' + x[0] + '</p><p class="font-display font-bold text-base text-ink tabular-nums mt-0.5 ' + x[2] + '">' + x[1] + '</p></div>';
    }).join('');

    // Segmen aktif (dari / ke sesuai arah)
    var i = this.active, s = P[i].seg, d = diff(s.grade);
    var from = up ? P[i - 1] : P[i], to = up ? P[i] : P[i - 1];
    var segIdx = up ? i : P.length - i, segTotal = P.length - 1;
    var isOjek = i === 1 && this.ojek;
    var mins = this.segMin(i);
    var gradeBar = Math.min(100, s.grade / 60 * 100);
    var facil = (to.facilities || []).map(function (f) { return '<span class="px-2 py-0.5 rounded-full text-[11px] font-semibold text-emerald-300 bg-emerald-500/10 ring-1 ring-emerald-500/25">' + esc(f) + '</span>'; }).join('');
    if (to.camp) facil = '<span class="px-2 py-0.5 rounded-full text-[11px] font-semibold text-sky-300 bg-sky-500/10 ring-1 ring-sky-500/25">' + (to.camp_recommended ? 'Camp disarankan' : 'Camp resmi') + '</span>' + facil;
    if (to.no_camp) facil += '<span class="px-2 py-0.5 rounded-full text-[11px] font-semibold text-rose-300 bg-rose-500/10 ring-1 ring-rose-500/25">Dilarang camp</span>';
    if (!facil && to.type === 'pos') facil = '<span class="px-2 py-0.5 rounded-full text-[11px] font-semibold text-rose-300 bg-rose-500/10 ring-1 ring-rose-500/25">Tanpa sumber air</span>';

    this.$('.tm-detail').innerHTML =
      '<div class="flex items-center justify-between gap-2">'
      + '<p class="text-[11px] font-semibold uppercase tracking-wider text-brand">Segmen ' + segIdx + ' dari ' + segTotal + '</p>'
      + '<div class="flex gap-1.5">'
      + '<button type="button" data-tm-step="-1" class="w-9 h-9 rounded-lg bg-raised ring-1 ring-line text-ink flex items-center justify-center disabled:opacity-30" ' + (segIdx === 1 ? 'disabled' : '') + ' aria-label="Segmen sebelumnya"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" viewBox="0 0 24 24"><path d="M15 6l-6 6 6 6"/></svg></button>'
      + '<button type="button" data-tm-step="1" class="w-9 h-9 rounded-lg bg-raised ring-1 ring-line text-ink flex items-center justify-center disabled:opacity-30" ' + (segIdx === segTotal ? 'disabled' : '') + ' aria-label="Segmen berikutnya"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" viewBox="0 0 24 24"><path d="M9 6l6 6-6 6"/></svg></button>'
      + '</div></div>'
      + '<div class="mt-2 flex items-center gap-2 min-w-0">'
      + '<span class="text-sm text-muted truncate">' + esc(short(from)) + '</span>'
      + '<svg class="w-4 h-4 text-brand shrink-0" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"/></svg>'
      + '<span class="font-display font-bold text-lg text-ink truncate">' + esc(to.type === 'pos' ? to.code.replace('POS', 'Pos') + ' · ' + to.name : to.name) + '</span>'
      + '</div>'
      + '<div class="grid grid-cols-4 gap-1.5 mt-3">'
      + [[km(s.dist_m), 'Jarak'], [isOjek ? '±' + dur(mins) : dur(mins), isOjek ? 'Ojek' : 'Waktu'], [(up ? '+' : '−') + num(Math.abs(s.gain_m)) + ' m', up ? 'Naik' : 'Turun'], [num(to.elev), 'MDPL']].map(function (x) {
          return '<div class="rounded-xl bg-raised ring-1 ring-line px-1.5 py-2 text-center"><p class="font-display font-bold text-sm sm:text-base text-ink tabular-nums leading-tight">' + x[0] + '</p><p class="text-[10px] text-muted mt-0.5">' + x[1] + '</p></div>';
        }).join('')
      + '</div>'
      + '<div class="mt-3"><div class="flex items-center justify-between text-[11px] mb-1"><span class="text-muted">Rata-rata ' + s.grade + '% · tanjakan terberat <b class="' + (s.max_grade >= 45 ? 'text-rose-300' : 'text-ink') + '">' + s.max_grade + '%</b></span><span class="px-2 py-0.5 rounded-full ring-1 font-semibold ' + d.cls + '">' + d.t + '</span></div>'
      + '<div class="h-1.5 rounded-full bg-canvas overflow-hidden"><div class="h-full rounded-full" style="width:' + gradeBar + '%;background:' + d.c + '"></div></div></div>'
      + '<div class="mt-3 flex items-center justify-between rounded-xl bg-canvas ring-1 ring-line px-3 py-2 text-xs"><span class="text-muted">Perkiraan tiba di ' + esc(short(to)) + '</span><span class="font-display font-bold text-sm text-brand-soft tabular-nums">' + clock(eta.start, eta[up ? i : i - 1]) + '</span></div>'
      + (facil ? '<div class="flex flex-wrap gap-1.5 mt-3">' + facil + '</div>' : '')
      + '<p class="text-sm text-muted mt-2.5 leading-relaxed">' + esc(to.description) + '</p>'
      + (to.type === 'landmark' || to.type === 'basecamp' ? '' : (to.photo
          ? '<img src="/storage/' + esc(to.photo) + '" alt="Foto ' + esc(to.name) + '" loading="lazy" class="mt-3 w-full aspect-video object-cover rounded-xl">'
          : '<div class="mt-3 flex items-center justify-center gap-2 rounded-xl border border-dashed border-line-strong py-3 text-[11px] text-subtle"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M3 8h3l2-3h8l2 3h3v11H3zM12 17a4 4 0 100-8 4 4 0 000 8z"/></svg>Foto ' + esc(short(to)) + ' segera hadir</div>'))
      + '<a href="https://www.google.com/maps/dir/?api=1&destination=' + to.lat + ',' + to.lng + '" target="_blank" rel="noopener" class="mt-3 inline-flex items-center gap-1 text-xs font-semibold text-brand-soft hover:text-brand">Koordinat ' + to.lat.toFixed(5) + ', ' + to.lng.toFixed(5) + ' ↗</a>';
    this.block.querySelectorAll('[data-tm-step]').forEach(function (b) {
      b.addEventListener('click', function () {
        var step = +b.getAttribute('data-tm-step');
        self.select(self.active + (up ? step : -step));
      });
    });

    // Itinerary
    var ord = this.order();
    this.$('.tm-itin').innerHTML = ord.map(function (pi, k) {
      var p = P[pi], segHtml = '';
      if (k > 0) {
        var segI = Math.max(pi, ord[k - 1]), sg = P[segI].seg, dd = diff(sg.grade), on = segI === self.active;
        var oj = segI === 1 && self.ojek;
        segHtml = '<li><button type="button" data-tm-seg="' + segI + '" class="w-full flex items-center gap-3 pl-[30px] pr-4 py-1 text-left group">'
          + '<span class="w-0.5 h-7 rounded-full shrink-0" style="background:' + dd.c + '"></span>'
          + '<span class="text-[11px] tabular-nums ' + (on ? 'text-brand-soft font-semibold' : 'text-subtle group-hover:text-muted') + '">' + km(sg.dist_m) + ' · ' + (oj ? 'ojek ±' : '') + dur(self.segMin(segI)) + ' · ' + dd.t.toLowerCase() + '</span></button></li>';
      }
      var isActiveRow = (self.dir === 'up' ? pi : pi + 1) === self.active && k > 0;
      return segHtml + '<li><button type="button" data-tm-pt="' + pi + '" class="tm-itin-row ' + (isActiveRow ? 'is-active' : '') + ' w-full flex items-center gap-3 px-4 py-1.5 text-left hover:bg-hover/60">'
        + '<span class="w-7 h-7 rounded-full flex items-center justify-center text-[10px] font-bold text-white shrink-0" style="background:' + pinColor(p) + ';box-shadow:0 0 0 2px #2F3641">' + label(p) + '</span>'
        + '<span class="flex-1 min-w-0"><span class="tm-itin-name block text-sm font-semibold text-ink truncate">' + esc(p.type === 'pos' ? p.code.replace('POS', 'Pos') + ' · ' + p.name : p.name) + '</span>'
        + '<span class="block text-[11px] text-muted tabular-nums">' + num(p.elev) + ' MDPL · km ' + ((self.dir === 'up' ? p.cum.dist_m : totalDist - p.cum.dist_m) / 1000).toLocaleString('id-ID', { minimumFractionDigits: 1, maximumFractionDigits: 1 }) +(p.camp ? ' · <span class="text-sky-300">' + (p.camp_recommended ? 'camp disarankan' : 'camp') + '</span>' : '') + (p.no_camp ? ' · <span class="text-rose-300">dilarang camp</span>' : '') + '</span></span>'
        + '<span class="font-display font-bold text-sm tabular-nums ' + (k === 0 ? 'text-muted' : 'text-ink') + '">' + clock(eta.start, eta[pi]) + '</span>'
        + '</button></li>';
    }).join('');
    this.block.querySelectorAll('[data-tm-seg]').forEach(function (b) { b.addEventListener('click', function () { self.select(+b.getAttribute('data-tm-seg')); }); });
    this.block.querySelectorAll('[data-tm-pt]').forEach(function (b) {
      b.addEventListener('click', function () {
        var pi = +b.getAttribute('data-tm-pt');
        self.select(self.dir === 'up' ? Math.max(1, pi) : Math.min(P.length - 1, pi + 1));
      });
    });

    var list = this.$('.tm-itin'), row = list.querySelector('.is-active');
    if (row && list.scrollHeight > list.clientHeight) list.scrollTop = row.offsetTop - list.clientHeight / 2;

    // Overlay ringkas di peta
    this.$('.tm-overlay').innerHTML = '<span class="w-2 h-2 rounded-full shrink-0" style="background:' + d.c + '"></span><span class="truncate"><b>' + esc(short(from)) + ' → ' + esc(short(to)) + '</b> · ' + km(s.dist_m) + ' · ' + (isOjek ? 'ojek ±' : '') + dur(mins) + '</span>';

    this.renderProfile();
    this.paintMap();
  };

  // ---------- Profil elevasi ----------
  TrailWidget.prototype.renderProfile = function () {
    var box = this.$('.tm-profile');
    var self = this, P = this.P, W = Math.max(260, Math.round(box.clientWidth || 600)), H = W < 500 ? 120 : 140, pl = 34, pr = 8, pt = 10, pb = 18;
    if (!this._profRO && 'ResizeObserver' in window) {
      var lastW = W;
      this._profRO = new ResizeObserver(function () { var w = Math.round(box.clientWidth); if (w && Math.abs(w - lastW) > 4) { lastW = w; self.renderProfile(); } });
      this._profRO.observe(box);
    }
    var total = P[P.length - 1].cum.dist_m, prof = this.prof;
    var minE = Math.floor(Math.min.apply(null, prof.map(function (q) { return q.e; })) / 250) * 250;
    var maxE = Math.ceil(Math.max.apply(null, prof.map(function (q) { return q.e; })) / 250) * 250;
    function x(d) { return pl + d / total * (W - pl - pr); }
    function y(e) { return pt + (1 - (e - minE) / (maxE - minE)) * (H - pt - pb); }
    this._prof = { x: x, y: y, W: W, pl: pl, pr: pr, total: total };
    var svg = '<svg viewBox="0 0 ' + W + ' ' + H + '" width="100%" height="' + H + '" class="block overflow-visible">';
    for (var e = minE; e <= maxE; e += 250) {
      svg += '<line x1="' + pl + '" x2="' + (W - pr) + '" y1="' + y(e) + '" y2="' + y(e) + '" stroke="#232932" stroke-dasharray="2 4" vector-effect="non-scaling-stroke"/>'
        + '<text x="' + (pl - 4) + '" y="' + (y(e) + 3) + '" text-anchor="end" font-size="9" fill="#6E7887">' + num(e) + '</text>';
    }
    for (var i = 1; i < P.length; i++) {
      var d = diff(P[i].seg.grade), on = i === this.active;
      var pts = [{ d: P[i - 1].cum.dist_m, e: P[i - 1].elev }].concat(prof.filter(function (q) { return q.seg === i; }));
      var line = pts.map(function (q) { return x(q.d).toFixed(1) + ',' + y(q.e).toFixed(1); }).join(' ');
      svg += '<polygon data-tm-seg="' + i + '" points="' + x(pts[0].d) + ',' + (H - pb) + ' ' + line + ' ' + x(pts[pts.length - 1].d) + ',' + (H - pb) + '" fill="' + d.c + '" fill-opacity="' + (on ? .45 : .14) + '" style="cursor:pointer"/>'
        + '<polyline points="' + line + '" fill="none" stroke="' + d.c + '" stroke-width="' + (on ? 3.5 : 2) + '" stroke-linejoin="round" stroke-linecap="round"/>';
    }
    P.forEach(function (p, i) {
      if (p.type === 'landmark') return;
      svg += '<text x="' + x(p.cum.dist_m) + '" y="' + (H - 4) + '" text-anchor="middle" font-size="9" font-weight="700" fill="' + (i === self.active || i === self.active - 1 ? '#FF8A65' : '#A0A9B6') + '">' + label(p) + '</text>';
    });
    svg += '<line class="tm-cursor" x1="0" x2="0" y1="' + pt + '" y2="' + (H - pb) + '" stroke="#ECEFF3" stroke-width="1" vector-effect="non-scaling-stroke" opacity="0"/></svg>';
    box.innerHTML = svg;
    box.querySelectorAll('polygon[data-tm-seg]').forEach(function (pg) { pg.addEventListener('click', function () { self.select(+pg.getAttribute('data-tm-seg')); }); });
    var svgEl = box.querySelector('svg'), cursor = box.querySelector('.tm-cursor');
    function scrub(ev) {
      var r = svgEl.getBoundingClientRect(), fx = (ev.clientX - r.left) / r.width * W;
      var dist = Math.max(0, Math.min(total, (fx - pl) / (W - pl - pr) * total));
      var j = 1; while (j < prof.length - 1 && prof[j].d < dist) j++;
      var q0 = prof[j - 1], q1 = prof[j], t = Math.max(0, Math.min(1, (dist - q0.d) / Math.max(1e-6, q1.d - q0.d)));
      var elev = q0.e + (q1.e - q0.e) * t, lat = q0.lat + (q1.lat - q0.lat) * t, lng = q0.lng + (q1.lng - q0.lng) * t;
      var a = P[q1.seg - 1], b = P[q1.seg];
      cursor.setAttribute('x1', x(dist)); cursor.setAttribute('x2', x(dist)); cursor.setAttribute('opacity', '.7');
      self.$('.tm-scrub-label').innerHTML = '<b class="text-ink">' + km(dist) + '</b> dari basecamp · <b class="text-ink">' + num(elev) + ' MDPL</b> · ' + esc(short(a)) + '→' + esc(short(b));
      if (self.map) {
        if (!self.scrubMarker) self.scrubMarker = L.circleMarker([lat, lng], { radius: 7, color: '#fff', weight: 2, fillColor: '#FF5722', fillOpacity: 1 }).addTo(self.map);
        else self.scrubMarker.setLatLng([lat, lng]);
      }
    }
    svgEl.addEventListener('pointermove', scrub);
    svgEl.addEventListener('pointerdown', scrub);
    svgEl.addEventListener('pointerleave', function () {
      cursor.setAttribute('opacity', '0');
      if (self.scrubMarker) { self.map.removeLayer(self.scrubMarker); self.scrubMarker = null; }
    });
  };

  // ---------- Peta ----------
  TrailWidget.prototype.initMap = function () {
    var self = this, P = this.P, el = document.getElementById(this.id);
    var map = this.map = L.map(el, { zoomControl: false, zoomSnap: 0.25, zoomDelta: 0.5, scrollWheelZoom: false, dragging: !isTouch, tap: true });
    if (!isTouch) map.zoomControl = L.control.zoom({ position: 'bottomright' }).addTo(map);
    this.layers = {
      sat: L.layerGroup([
        L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', { maxZoom: 18, attribution: '© Esri, Maxar, Earthstar Geographics' }),
        L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/Reference/World_Boundaries_and_Places/MapServer/tile/{z}/{y}/{x}', { maxZoom: 18, opacity: .85 })
      ]),
      topo: L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', { maxZoom: 17, attribution: '© OpenStreetMap, SRTM · © OpenTopoMap (CC-BY-SA)' })
    };
    this.layers.sat.addTo(map);
    this.block.querySelectorAll('[data-tm-layer]').forEach(function (b) {
      b.addEventListener('click', function () {
        var k = b.getAttribute('data-tm-layer');
        Object.keys(self.layers).forEach(function (n) { map.removeLayer(self.layers[n]); });
        self.layers[k].addTo(map);
        self.block.querySelectorAll('[data-tm-layer]').forEach(function (o) { var on = o === b; o.classList.toggle('bg-brand', on); o.classList.toggle('text-white', on); o.classList.toggle('text-muted', !on); });
      });
    });

    this.segLines = [];
    for (var i = 1; i < P.length; i++) {
      (function (i) {
        var ll = P[i].seg.line.map(function (q) { return [q[0], q[1]]; });
        var casing = L.polyline(ll, { color: '#0B0D10', weight: 8, opacity: .55, lineJoin: 'round' }).addTo(map);
        var line = L.polyline(ll, { color: diff(P[i].seg.grade).c, weight: 4, opacity: .9, lineCap: 'round', lineJoin: 'round' }).addTo(map);
        var hit = L.polyline(ll, { color: '#000', weight: 22, opacity: 0 }).addTo(map);
        hit.on('click', function () { self.select(i, false); });
        self.segLines[i] = { casing: casing, line: line };
      })(i);
    }
    this.markers = P.map(function (p, i) {
      var s = p.type === 'peak' || p.type === 'basecamp' ? 34 : p.type === 'landmark' ? 24 : 30;
      var m = L.marker([p.lat, p.lng], {
        icon: L.divIcon({ className: '', iconSize: [s, s], iconAnchor: [s / 2, s / 2], html: '<div class="tm-pin" style="width:' + s + 'px;height:' + s + 'px;background:' + pinColor(p) + '">' + label(p) + '</div>' }),
        title: p.code + ' ' + p.name, riseOnHover: true
      }).addTo(map);
      m.bindTooltip(esc(short(p)) + (p.type === 'pos' ? ' · ' + esc(p.name) : '') + ' · ' + num(p.elev) + ' m', { direction: 'top', offset: [0, -s / 2] });
      m.on('click', function () {
        var up = self.dir === 'up';
        self.select(up ? Math.max(1, i) : Math.min(P.length - 1, i + 1), false);
      });
      return m;
    });
    this.bounds = L.latLngBounds(this.prof.map(function (q) { return [q.lat, q.lng]; }));
    this.fit = function () { map.fitBounds(self.bounds, { padding: el.clientWidth < 500 ? [24, 24] : [40, 40] }); };
    this.fit();
    this.$('[data-tm-fit]').addEventListener('click', this.fit);
    this.$('.tm-loading').remove();
    if ('ResizeObserver' in window) new ResizeObserver(function () { map.invalidateSize(); }).observe(el);

    // Desktop: zoom pakai scroll hanya setelah peta diklik
    map.on('click', function () { if (!isTouch) map.scrollWheelZoom.enable(); });
    map.on('mouseout', function () { if (!self.full) map.scrollWheelZoom.disable(); });

    var frame = document.getElementById(this.id + 'Frame'), fullBtn = this.$('[data-tm-full]');
    function setFull(on) {
      self.full = on;
      frame.classList.toggle('tm-full', on);
      document.documentElement.style.overflow = on ? 'hidden' : '';
      fullBtn.querySelector('.tm-ico-open').classList.toggle('hidden', on);
      fullBtn.querySelector('.tm-ico-close').classList.toggle('hidden', !on);
      if (isTouch) {
        on ? map.dragging.enable() : map.dragging.disable();
        if (on && !map.zoomControl) map.zoomControl = L.control.zoom({ position: 'bottomright' }).addTo(map);
        if (!on && map.zoomControl) { map.removeControl(map.zoomControl); map.zoomControl = null; }
      }
      on ? map.scrollWheelZoom.enable() : map.scrollWheelZoom.disable();
      setTimeout(function () { map.invalidateSize(); }, 60);
    }
    fullBtn.addEventListener('click', function () { setFull(!self.full); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && self.full) setFull(false); });
    this.paintMap();
  };

  TrailWidget.prototype.paintMap = function () {
    if (!this.map) return;
    var self = this, P = this.P, a = this.active;
    for (var i = 1; i < P.length; i++) {
      var on = i === a, sl = this.segLines[i];
      sl.line.setStyle({ weight: on ? 7 : 4, opacity: on ? 1 : .6 });
      sl.casing.setStyle({ weight: on ? 12 : 8, opacity: on ? .8 : .45 });
      if (on) { sl.casing.bringToFront(); sl.line.bringToFront(); }
    }
    this.markers.forEach(function (m, i) {
      var el = m.getElement() && m.getElement().querySelector('.tm-pin');
      if (!el) return;
      var isEnd = i === a || i === a - 1;
      el.classList.toggle('is-active', i === (self.dir === 'up' ? a : a - 1));
      el.classList.toggle('is-dim', !isEnd);
    });
    if (this.segTip) this.map.removeLayer(this.segTip);
    var p1 = P[a], mins = this.segMin(a);
    var midD = P[a - 1].cum.dist_m + p1.seg.dist_m / 2, mid = this.prof.filter(function (q) { return q.seg === a; })
      .reduce(function (best, q) { return Math.abs(q.d - midD) < Math.abs(best.d - midD) ? q : best; });
    this.segTip = L.tooltip({ permanent: true, direction: 'center', className: 'tm-seg-tip', interactive: false })
      .setLatLng([mid.lat, mid.lng])
      .setContent(km(p1.seg.dist_m) + ' · ' + (a === 1 && this.ojek ? 'ojek ±' : '') + dur(mins))
      .addTo(this.map);
  };

  function boot() {
    document.querySelectorAll('[data-trail-map]').forEach(function (block) {
      var w = new TrailWidget(block);
      var io = new IntersectionObserver(function (entries) {
        if (!entries[0].isIntersecting) return;
        io.disconnect();
        loadLeaflet().then(function () { w.initMap(); }).catch(function () {
          var l = block.querySelector('.tm-loading'); if (l) l.textContent = 'Peta gagal dimuat. Periksa koneksi internet.';
        });
      }, { rootMargin: '200px' });
      io.observe(block);
    });
  }
  document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', boot) : boot();
})();
</script>
@endonce
