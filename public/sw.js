/**
 * SAMSARA EXPEDITION — Progressive Web App (PWA) Service Worker
 * Arsitektur: Modular, Scalable & High-Resilience Offline Caching
 * Versi: 1.0.0
 */

const CACHE_VERSION = 'v1.0.0';
const STATIC_CACHE_NAME = `samsara-static-${CACHE_VERSION}`;
const RUNTIME_CACHE_NAME = `samsara-runtime-${CACHE_VERSION}`;
const OFFLINE_URL = '/offline.html';

// Asset inti yang diprecache saat instalasi Service Worker
const PRECACHE_ASSETS = [
  OFFLINE_URL,
  '/manifest.webmanifest',
  '/manifest.json',
  '/favicon.ico',
  '/assets/icons/icon.svg',
  '/assets/icons/icon-192.png',
  '/assets/icons/icon-512.png',
  '/assets/icons/icon-maskable-512.png',
  '/assets/icons/apple-touch-icon.png',
  '/assets/css/main.css',
  '/assets/js/pwa.js',
  '/assets/js/currency-input.js',
];

// Domain eksternal yang diizinkan untuk di-cache (CDN Fonts, script UI)
const ALLOWED_EXTERNAL_HOSTS = [
  'fonts.googleapis.com',
  'fonts.gstatic.com',
  'www.gstatic.com',
];

/* ================================================================
   1. SIKLUS INSTALASI & PRECACHE
   ================================================================ */
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(STATIC_CACHE_NAME).then((cache) => {
      // Precache file satu per satu dengan penanganan error toleran
      return Promise.allSettled(
        PRECACHE_ASSETS.map((url) =>
          cache.add(new Request(url, { cache: 'reload' })).catch((err) => {
            console.warn(`[PWA SW] Gagal memuat precache untuk ${url}:`, err);
          })
        )
      );
    }).then(() => self.skipWaiting())
  );
});

/* ================================================================
   2. AKTIVASI & PEMBERSIHAN CACHE LAMA
   ================================================================ */
self.addEventListener('activate', (event) => {
  const currentCaches = [STATIC_CACHE_NAME, RUNTIME_CACHE_NAME];
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cacheName) => {
          if (
            cacheName.startsWith('samsara-') &&
            !currentCaches.includes(cacheName)
          ) {
            console.info(`[PWA SW] Menghapus cache lawas: ${cacheName}`);
            return caches.delete(cacheName);
          }
        })
      );
    }).then(() => self.clients.claim())
  );
});

/* ================================================================
   3. STRATEGI FETCH & PERMINTAAN JARINGAN
   ================================================================ */
self.addEventListener('fetch', (event) => {
  const { request } = event;
  const url = new URL(request.url);

  // Abaikan request non-GET (POST, PUT, DELETE langsung ke server)
  if (request.method !== 'GET') {
    return;
  }

  // Abaikan request skema khusus (chrome-extension, dsb)
  if (!url.protocol.startsWith('http')) {
    return;
  }

  // A. STRATEGI NAVIGASI HTML (Network-First dengan Fallback Offline Page)
  if (request.mode === 'navigate') {
    event.respondWith(handleNavigationRequest(request));
    return;
  }

  // B. STRATEGI ASSET STATIS (Stale-While-Revalidate)
  if (isStaticAsset(url)) {
    event.respondWith(handleStaleWhileRevalidate(request, STATIC_CACHE_NAME));
    return;
  }

  // C. STRATEGI DEFAULT GET (Network-First dengan Runtime Cache)
  event.respondWith(handleNetworkFirstWithRuntime(request));
});

/**
 * Handle navigasi HTML: Prioritaskan jaringan untuk data real-time,
 * jika gagal atau offline, tampilkan halaman cache atau offline.html.
 */
async function handleNavigationRequest(request) {
  try {
    // Berikan timeout 4 detik jika koneksi edge/2G di gunung sangat lambat
    const networkResponse = await Promise.race([
      fetch(request),
      new Promise((_, reject) =>
        setTimeout(() => reject(new Error('Network timeout')), 4000)
      ),
    ]);

    // Jika response valid, update cache runtime untuk halaman ini
    if (networkResponse && networkResponse.status === 200) {
      const cache = await caches.open(RUNTIME_CACHE_NAME);
      cache.put(request, networkResponse.clone());
    }
    return networkResponse;
  } catch (error) {
    console.warn('[PWA SW] Navigasi offline, mencoba cache halaman...', request.url);
    const cachedPage = await caches.match(request);
    if (cachedPage) {
      return cachedPage;
    }

    // Jika halaman belum pernah dicache, kembalikan offline.html
    const offlineFallback = await caches.match(OFFLINE_URL);
    if (offlineFallback) {
      return offlineFallback;
    }

    // Fallback darurat teks murni jika offline.html belum siap
    return new Response(
      '<h1>Offline - SAMSARA OPS</h1><p>Sinyal lapangan terputus. Silakan hubungkan kembali perangkat Anda.</p>',
      { headers: { 'Content-Type': 'text/html; charset=utf-8' } }
    );
  }
}

/**
 * Handle asset statis (CSS, JS, Fonts, Images) dengan Stale-While-Revalidate
 */
async function handleStaleWhileRevalidate(request, cacheName) {
  const cache = await caches.open(cacheName);
  const cachedResponse = await cache.match(request);

  const fetchPromise = fetch(request)
    .then((networkResponse) => {
      if (networkResponse && networkResponse.status === 200) {
        cache.put(request, networkResponse.clone());
      }
      return networkResponse;
    })
    .catch(() => cachedResponse);

  return cachedResponse || fetchPromise;
}

/**
 * Handle permintaan umum dengan Network-First
 */
async function handleNetworkFirstWithRuntime(request) {
  try {
    const networkResponse = await fetch(request);
    if (networkResponse && networkResponse.status === 200) {
      const cache = await caches.open(RUNTIME_CACHE_NAME);
      cache.put(request, networkResponse.clone());
    }
    return networkResponse;
  } catch (err) {
    const cachedResponse = await caches.match(request);
    if (cachedResponse) {
      return cachedResponse;
    }
    throw err;
  }
}

/**
 * Deteksi apakah URL merupakan asset statis
 */
function isStaticAsset(url) {
  const isAllowedExternal = ALLOWED_EXTERNAL_HOSTS.includes(url.hostname);
  const isLocalAsset =
    url.pathname.startsWith('/assets/') ||
    url.pathname.endsWith('.css') ||
    url.pathname.endsWith('.js') ||
    url.pathname.endsWith('.woff2') ||
    url.pathname.endsWith('.woff') ||
    url.pathname.endsWith('.ttf') ||
    url.pathname.endsWith('.png') ||
    url.pathname.endsWith('.jpg') ||
    url.pathname.endsWith('.jpeg') ||
    url.pathname.endsWith('.svg') ||
    url.pathname.endsWith('.ico');

  return isAllowedExternal || isLocalAsset;
}

/* ================================================================
   4. PENANGANAN PESAN (IPC)
   ================================================================ */
self.addEventListener('message', (event) => {
  if (!event.data) return;

  switch (event.data.type) {
    case 'SKIP_WAITING':
      self.skipWaiting();
      break;

    case 'CLEAR_CACHE':
      event.waitUntil(
        caches.keys().then((keys) => Promise.all(keys.map((k) => caches.delete(k))))
      );
      break;

    case 'PING':
      event.ports[0]?.postMessage({ status: 'PONG', version: CACHE_VERSION });
      break;
  }
});

/* ================================================================
   5. NOTIFIKASI PUSH LAPANGAN (OPERASIONAL)
   ================================================================ */
self.addEventListener('push', (event) => {
  let data = {
    title: 'SAMSARA OPS Alert',
    body: 'Pembaruan operasional dari Basecamp.',
    icon: '/assets/icons/icon-192.png',
    badge: '/assets/icons/icon-192.png',
    data: { url: '/management' },
  };

  if (event.data) {
    try {
      data = Object.assign(data, event.data.json());
    } catch (e) {
      data.body = event.data.text();
    }
  }

  const options = {
    body: data.body,
    icon: data.icon,
    badge: data.badge,
    vibrate: [100, 50, 100],
    data: data.data,
    actions: [
      { action: 'open', title: 'Buka Sistem' },
      { action: 'close', title: 'Tutup' },
    ],
  };

  event.waitUntil(self.registration.showNotification(data.title, options));
});

self.addEventListener('notificationclick', (event) => {
  event.notification.close();
  const targetUrl = event.notification.data?.url || '/management';

  if (event.action === 'close') {
    return;
  }

  event.waitUntil(
    clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
      for (const client of clientList) {
        if (client.url.includes('/management') && 'focus' in client) {
          return client.focus();
        }
      }
      if (clients.openWindow) {
        return clients.openWindow(targetUrl);
      }
    })
  );
});
