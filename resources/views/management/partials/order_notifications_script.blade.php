{{-- Notifikasi pesanan masuk real-time (polling ringan, tanpa server WebSocket) --}}
<div id="orderToastStack" class="fixed z-[60] right-3 left-3 sm:left-auto sm:w-96 bottom-20 lg:bottom-6 flex flex-col gap-2 pointer-events-none" aria-live="polite"></div>

<script>
(function () {
  const FEED_URL = @json(route('management.notifications.orders'));
  const STORE = 'samsara_order_notif_v1';
  const INTERVAL_VISIBLE = 10000;
  const INTERVAL_HIDDEN = 30000;
  const MAX_ITEMS = 30;
  const baseTitle = document.title;

  const el = {
    menu: document.getElementById('orderNotifMenu'),
    count: document.getElementById('orderNotifCount'),
    list: document.getElementById('orderNotifList'),
    readAll: document.getElementById('orderNotifReadAll'),
    sound: document.getElementById('orderNotifSoundToggle'),
    perm: document.getElementById('orderNotifPermission'),
    stack: document.getElementById('orderToastStack'),
  };

  // ---------- State (dibagi antar tab & antar halaman via localStorage) ----------
  function load() {
    try {
      const s = JSON.parse(localStorage.getItem(STORE) || '{}');
      return { cursor: s.cursor || null, items: Array.isArray(s.items) ? s.items : [], seen: Array.isArray(s.seen) ? s.seen : [], muted: !!s.muted };
    } catch (e) { return { cursor: null, items: [], seen: [], muted: false }; }
  }
  function save(s) {
    s.items = s.items.slice(0, MAX_ITEMS);
    s.seen = s.seen.slice(-200);
    try { localStorage.setItem(STORE, JSON.stringify(s)); } catch (e) {}
  }

  // ---------- Suara (Web Audio, tanpa file) ----------
  let audioCtx = null;
  function unlockAudio() {
    try {
      audioCtx = audioCtx || new (window.AudioContext || window.webkitAudioContext)();
      if (audioCtx.state === 'suspended') audioCtx.resume();
    } catch (e) {}
  }
  ['pointerdown', 'keydown', 'touchstart'].forEach(ev => document.addEventListener(ev, unlockAudio, { once: true, passive: true }));
  function chime() {
    if (load().muted || !audioCtx || audioCtx.state !== 'running') return;
    const t = audioCtx.currentTime;
    [[880, 0], [1318.5, 0.16]].forEach(([freq, offset]) => {
      const osc = audioCtx.createOscillator();
      const gain = audioCtx.createGain();
      osc.type = 'sine';
      osc.frequency.value = freq;
      gain.gain.setValueAtTime(0.0001, t + offset);
      gain.gain.exponentialRampToValueAtTime(0.25, t + offset + 0.02);
      gain.gain.exponentialRampToValueAtTime(0.0001, t + offset + 0.35);
      osc.connect(gain).connect(audioCtx.destination);
      osc.start(t + offset);
      osc.stop(t + offset + 0.4);
    });
  }

  // ---------- Notifikasi perangkat (Notification API / Service Worker PWA) ----------
  function refreshPermissionButton() {
    if (!el.perm) return;
    const show = 'Notification' in window && Notification.permission === 'default';
    el.perm.classList.toggle('hidden', !show);
  }
  el.perm?.addEventListener('click', () => {
    Notification.requestPermission().finally(refreshPermissionButton);
  });
  function systemNotify(ev) {
    if (!('Notification' in window) || Notification.permission !== 'granted') return;
    const opts = { body: ev.body, tag: ev.key, icon: '/assets/icons/icon-192.png', badge: '/assets/icons/icon-192.png', data: { url: ev.url } };
    const title = ev.title + ' · ' + ev.code;
    if (navigator.serviceWorker && navigator.serviceWorker.controller) {
      navigator.serviceWorker.ready.then(reg => reg.showNotification(title, opts)).catch(() => {});
    } else {
      try { const n = new Notification(title, opts); n.onclick = () => { window.focus(); location.href = ev.url; }; } catch (e) {}
    }
  }

  // ---------- Render ----------
  function esc(s) { const d = document.createElement('div'); d.textContent = s == null ? '' : String(s); return d.innerHTML; }
  function timeLabel(iso) {
    const d = new Date(iso);
    return isNaN(d) ? '' : d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
  }
  function tone(type) {
    return type === 'proof_uploaded'
      ? { dot: 'bg-emerald-400', ring: 'ring-emerald-500/30', label: 'text-emerald-300' }
      : { dot: 'bg-brand', ring: 'ring-brand/30', label: 'text-brand' };
  }

  function renderList() {
    const s = load();
    const unread = s.items.filter(i => !i.read).length;
    el.count.textContent = unread > 99 ? '99+' : String(unread);
    el.count.classList.toggle('hidden', unread === 0);
    el.count.classList.toggle('flex', unread > 0);
    document.title = unread > 0 ? '(' + unread + ') ' + baseTitle : baseTitle;
    if (el.sound) el.sound.textContent = 'Suara: ' + (s.muted ? 'mati' : 'aktif');

    if (!s.items.length) {
      el.list.innerHTML = '<p data-empty class="px-3.5 py-6 text-center text-xs text-muted">Belum ada pesanan baru.</p>';
      return;
    }
    el.list.innerHTML = s.items.map(i => {
      const t = tone(i.type);
      return '<a href="' + esc(i.url) + '" data-key="' + esc(i.key) + '" class="flex gap-3 px-3.5 py-3 hover:bg-hover ' + (i.read ? 'opacity-60' : '') + '">'
        + '<span class="mt-1.5 w-2 h-2 rounded-full shrink-0 ' + (i.read ? 'bg-line-strong' : t.dot) + '"></span>'
        + '<span class="min-w-0 flex-1">'
        + '<span class="flex items-center justify-between gap-2"><span class="text-xs font-semibold ' + t.label + '">' + esc(i.title) + '</span><span class="text-[10px] text-subtle shrink-0">' + esc(timeLabel(i.at)) + '</span></span>'
        + '<span class="block text-sm font-semibold text-ink truncate">' + esc(i.code) + '</span>'
        + '<span class="block text-xs text-muted truncate">' + esc(i.body) + '</span>'
        + '</span></a>';
    }).join('');
  }

  function toast(ev) {
    const t = tone(ev.type);
    const node = document.createElement('a');
    node.href = ev.url;
    node.className = 'pointer-events-auto flex items-start gap-3 rounded-2xl bg-surface ring-1 ' + t.ring + ' shadow-2xl px-4 py-3 translate-y-2 opacity-0 transition duration-200';
    node.innerHTML = '<span class="mt-0.5 w-9 h-9 rounded-xl bg-raised ring-1 ring-line flex items-center justify-center shrink-0"><span class="w-2.5 h-2.5 rounded-full animate-ping ' + t.dot + '"></span></span>'
      + '<span class="min-w-0 flex-1"><span class="block text-xs font-semibold ' + t.label + '">' + esc(ev.title) + '</span>'
      + '<span class="block text-sm font-semibold text-ink truncate">' + esc(ev.code) + '</span>'
      + '<span class="block text-xs text-muted truncate">' + esc(ev.body) + '</span></span>'
      + '<button type="button" class="text-muted hover:text-ink text-lg leading-none" aria-label="Tutup">&times;</button>';
    node.querySelector('button').addEventListener('click', e => { e.preventDefault(); e.stopPropagation(); node.remove(); });
    el.stack.prepend(node);
    requestAnimationFrame(() => node.classList.remove('translate-y-2', 'opacity-0'));
    setTimeout(() => { node.classList.add('opacity-0'); setTimeout(() => node.remove(), 250); }, 12000);
    while (el.stack.children.length > 4) el.stack.lastElementChild.remove();
  }

  function updateBadges(badges) {
    Object.entries(badges || {}).forEach(([key, value]) => {
      document.querySelectorAll('[data-nav-badge="' + key + '"]').forEach(link => {
        let span = link.querySelector('[data-badge-count]');
        if (!value) { span?.remove(); return; }
        if (!span) {
          span = document.createElement('span');
          span.setAttribute('data-badge-count', '');
          span.className = link.dataset.navBadgeStyle === 'bottom'
            ? 'absolute top-1.5 left-1/2 translate-x-1.5 min-w-[18px] h-[18px] px-1 rounded-full bg-brand text-white text-[10px] font-bold flex items-center justify-center ring-2 ring-surface'
            : 'min-w-[20px] h-5 px-1.5 rounded-full text-[11px] font-bold flex items-center justify-center bg-brand text-white';
          link.appendChild(span);
        }
        span.textContent = value > 99 ? '99+' : String(value);
      });
    });
  }

  // ---------- Polling ----------
  let timer = null;
  let busy = false;
  let stopped = false;

  async function poll() {
    if (busy || !navigator.onLine) return schedule();
    busy = true;
    try {
      const s0 = load();
      const url = FEED_URL + (s0.cursor ? '?since=' + encodeURIComponent(s0.cursor) : '');
      const res = await fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin', cache: 'no-store' });
      if (res.status === 401 || res.status === 403 || res.redirected) { stop(); return; }
      if (!res.ok) return;
      const data = await res.json();

      const s = load(); // baca ulang: tab lain mungkin baru saja memperbarui
      const fresh = (data.events || []).filter(ev => !s.seen.includes(ev.key));
      fresh.slice().reverse().forEach(ev => {
        s.seen.push(ev.key);
        s.items.unshift(Object.assign({ read: false }, ev));
      });
      s.cursor = data.cursor || s.cursor;
      save(s);

      if (fresh.length) {
        fresh.slice(0, 3).forEach(toast);
        chime();
        if (document.hidden) fresh.slice(0, 3).forEach(systemNotify);
      }
      renderList();
      updateBadges(data.badges);
    } catch (e) {
      // jaringan lapangan putus-sambung: coba lagi pada siklus berikutnya
    } finally {
      busy = false;
      schedule();
    }
  }

  function schedule() {
    clearTimeout(timer);
    if (stopped) return;
    timer = setTimeout(poll, document.hidden ? INTERVAL_HIDDEN : INTERVAL_VISIBLE);
  }
  function stop() { stopped = true; clearTimeout(timer); timer = null; }

  document.addEventListener('visibilitychange', () => { if (!document.hidden && !stopped) poll(); });
  window.addEventListener('online', () => { if (!stopped) poll(); });
  window.addEventListener('storage', e => { if (e.key === STORE) renderList(); });

  // ---------- Interaksi menu ----------
  el.list.addEventListener('click', e => {
    const a = e.target.closest('a[data-key]');
    if (!a) return;
    const s = load();
    s.items.forEach(i => { if (i.key === a.dataset.key) i.read = true; });
    save(s);
  });
  el.readAll?.addEventListener('click', () => {
    const s = load();
    s.items.forEach(i => { i.read = true; });
    save(s);
    renderList();
  });
  el.sound?.addEventListener('click', () => {
    const s = load();
    s.muted = !s.muted;
    save(s);
    renderList();
    if (!s.muted) { unlockAudio(); chime(); }
  });

  // Cursor lama (> 6 jam) dibuang agar tidak memunculkan tumpukan notifikasi basi
  (function init() {
    const s = load();
    if (s.cursor && (Date.now() - new Date(s.cursor).getTime()) > 6 * 3600 * 1000) { s.cursor = null; save(s); }
    refreshPermissionButton();
    renderList();
    poll();
  })();
})();
</script>
