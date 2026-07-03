const CACHE_VERSION = 'kerjaku-v1';
const STATIC_CACHE = `${CACHE_VERSION}-static`;
const RUNTIME_CACHE = `${CACHE_VERSION}-runtime`;
const API_CACHE = `${CACHE_VERSION}-api`;

const PRECACHE_URLS = [
    '/manifest.json',
    '/offline.html',
    '/icons/icon-192.png',
    '/icons/icon-512.png',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(STATIC_CACHE)
            .then((cache) => cache.addAll(PRECACHE_URLS))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(
                keys
                    .filter((key) => key.startsWith('kerjaku-') && !key.startsWith(CACHE_VERSION))
                    .map((key) => caches.delete(key))
            ))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    if (request.method !== 'GET' || url.origin !== self.location.origin) {
        return;
    }

    // Built assets are content-hashed — safe to cache aggressively (cache-first).
    if (url.pathname.startsWith('/build/') || url.pathname.startsWith('/icons/')) {
        event.respondWith(cacheFirst(request, STATIC_CACHE));
        return;
    }

    // API data — network-first so it's fresh online, cached fallback offline.
    if (url.pathname.startsWith('/api/')) {
        event.respondWith(networkFirst(request, API_CACHE));
        return;
    }

    // Page navigations — network-first with offline fallback page.
    if (request.mode === 'navigate') {
        event.respondWith(
            networkFirst(request, RUNTIME_CACHE).catch(() => caches.match('/offline.html'))
        );
        return;
    }

    event.respondWith(cacheFirst(request, RUNTIME_CACHE));
});

async function cacheFirst(request, cacheName) {
    const cached = await caches.match(request);
    if (cached) {
        return cached;
    }

    const response = await fetch(request);
    if (response.ok) {
        const cache = await caches.open(cacheName);
        cache.put(request, response.clone());
    }

    return response;
}

async function networkFirst(request, cacheName) {
    const cache = await caches.open(cacheName);

    try {
        const response = await fetch(request);
        if (response.ok) {
            cache.put(request, response.clone());
        }

        return response;
    } catch (error) {
        const cached = await cache.match(request);
        if (cached) {
            return cached;
        }

        throw error;
    }
}

self.addEventListener('push', (event) => {
    if (! event.data) return;

    const payload = event.data.json();

    event.waitUntil(
        self.registration.showNotification(payload.title ?? 'KerjaKu', {
            body: payload.body,
            icon: '/icons/icon-192.png',
            badge: '/icons/icon-96.png',
            data: payload.data ?? {},
        })
    );
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    const targetUrl = event.notification.data?.url ?? '/';

    event.waitUntil(
        clients.matchAll({ type: 'window' }).then((windowClients) => {
            for (const client of windowClients) {
                if (client.url.includes(targetUrl) && 'focus' in client) {
                    return client.focus();
                }
            }
            if (clients.openWindow) {
                return clients.openWindow(targetUrl);
            }
        })
    );
});
