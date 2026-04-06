const CACHE_NAME = 'invoicekit-v2';
const STATIC_ASSETS = [
    '/manifest.json',
    '/icons/icon-192.png',
    '/icons/icon-512.png',
];

// ── Install: pre-cache static assets ─────────────────────────────────────────
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => cache.addAll(STATIC_ASSETS))
    );
    self.skipWaiting();
});

// ── Activate: remove old caches ───────────────────────────────────────────────
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(
                keys.filter((k) => k !== CACHE_NAME).map((k) => caches.delete(k))
            )
        )
    );
    self.clients.claim();
});

// ── Fetch: cache-first for build assets, network-first for pages ──────────────
self.addEventListener('fetch', (event) => {
    const url = new URL(event.request.url);

    // Only handle same-origin GET requests
    if (event.request.method !== 'GET' || url.origin !== self.location.origin) {
        return;
    }

    // Cache-first strategy for Vite build assets (hashed filenames)
    if (url.pathname.startsWith('/build/')) {
        event.respondWith(
            caches.match(event.request).then(
                (cached) => cached || fetch(event.request).then((response) => {
                    if (response.ok) {
                        const clone = response.clone();
                        caches.open(CACHE_NAME).then((cache) => cache.put(event.request, clone));
                    }
                    return response;
                })
            )
        );
        return;
    }

    // Cache-first for icons and manifest
    if (url.pathname.startsWith('/icons/') || url.pathname === '/manifest.json') {
        event.respondWith(
            caches.match(event.request).then((cached) => cached || fetch(event.request))
        );
        return;
    }

    // Let navigations pass through natively — calling fetch() on navigate-mode
    // requests inside a service worker can produce ERR_FAILED when the navigation
    // originates from a cross-origin redirect (e.g. returning from Stripe checkout).
    // Pages are not meaningfully cached anyway, so there is no offline benefit lost.
    if (event.request.mode === 'navigate') {
        return;
    }

    // Network-first for all other requests (Livewire pages, API calls)
    event.respondWith(
        fetch(event.request).catch(() => caches.match(event.request))
    );
});

// ── Push: display notification ────────────────────────────────────────────────
self.addEventListener('push', (event) => {
    let data = {};
    try {
        data = event.data ? event.data.json() : {};
    } catch (e) {
        data = { title: 'InvoiceKit', body: event.data ? event.data.text() : '' };
    }

    const title = data.title || 'InvoiceKit';
    const options = {
        body: data.body || '',
        icon: '/icons/icon-192.png',
        badge: '/icons/icon-192.png',
        data: { url: data.action || '/' },
        vibrate: [200, 100, 200],
        requireInteraction: false,
    };

    event.waitUntil(self.registration.showNotification(title, options));
});

// ── Notification click: open target URL ──────────────────────────────────────
self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    const targetUrl = event.notification.data?.url || '/dashboard';

    event.waitUntil(
        self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clients) => {
            for (const client of clients) {
                if (client.url === targetUrl && 'focus' in client) {
                    return client.focus();
                }
            }
            return self.clients.openWindow(targetUrl);
        })
    );
});
