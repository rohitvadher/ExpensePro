const PRECACHE_NAME = 'expensepro-precache-v3';
const RUNTIME_CACHE = 'expensepro-runtime-v4';
const RUNTIME_MAX_ENTRIES = 80;

const APP_SCOPE = (() => {
    try {
        const scope = String((self.registration && self.registration.scope) || './');
        return scope.endsWith('/') ? scope : scope + '/';
    } catch (e) {
        return './';
    }
})();

const scopeUrl = (path) => new URL(path, APP_SCOPE).toString();

const STATIC_ASSETS = [
    scopeUrl('assets/icons/web/favicon.ico'),
    scopeUrl('assets/icons/web/icon-192.png'),
    scopeUrl('assets/icons/web/icon-512.png'),
    scopeUrl('assets/icons/web/icon-192-maskable.png'),
    scopeUrl('assets/icons/web/icon-512-maskable.png'),
    scopeUrl('assets/icons/web/apple-touch-icon.png'),
    scopeUrl('assets/icons/SVG/3-dots-fade.svg'),
    scopeUrl('manifest.json'),
    scopeUrl('offline/offline.html')
];

const STATIC_EXT = /\.(css|js|png|jpg|jpeg|svg|ico|woff2?|json|csv)$/;

self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
});

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(PRECACHE_NAME).then((cache) => {
            const jobs = STATIC_ASSETS.map((url) =>
                cache.add(url).catch(() => null)
            );
            return Promise.all(jobs);
        }).then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((name) => {
                    if (name !== PRECACHE_NAME && name !== RUNTIME_CACHE) {
                        return caches.delete(name);
                    }
                    return null;
                })
            );
        }).then(() => self.clients.claim())
    );
});

try {
    if (typeof self !== 'undefined'
        && self.registration
        && self.registration.navigationPreload
        && typeof self.registration.navigationPreload.enable === 'function') {
        self.registration.navigationPreload.enable().catch(() => {});
    }
} catch (e) {}

self.addEventListener('fetch', (event) => {
    const { request } = event;

    if (request.method !== 'GET') {
        return;
    }

    const url = new URL(request.url);
    const scopePath = new URL(APP_SCOPE).pathname;

    if (url.origin !== self.location.origin || !url.pathname.startsWith(scopePath)) {
        return;
    }

    if (url.pathname.includes('/api/')) {
        event.respondWith(
            fetch(request).catch(() => offlineJson())
        );
        return;
    }

    if (url.pathname.endsWith('/sw.js')) {
        event.respondWith(fetch(request).catch(() => offlinePage()));
        return;
    }

    if (request.mode === 'navigate') {
        event.respondWith(
            (async () => {
                try {
                    if (event.preloadResponse) {
                        const preloaded = await event.preloadResponse;
                        if (preloaded && preloaded.ok) {
                            return preloaded;
                        }
                    }
                } catch (e) {}

                try {
                    const networkResponse = await fetch(request);
                    return networkResponse;
                } catch (e) {
                    return offlinePage();
                }
            })()
        );
        return;
    }

    if (STATIC_EXT.test(url.pathname)) {
        event.respondWith(staleWhileRevalidate(request));
        return;
    }

    event.respondWith(fetch(request).catch(() => offlinePage()));
});

function staleWhileRevalidate(request) {
    return caches.open(RUNTIME_CACHE).then((cache) => {
        return cache.match(request).then((cachedResponse) => {
            const networkFetch = fetch(request).then((networkResponse) => {
                if (networkResponse && networkResponse.status === 200) {
                    cache.put(request, networkResponse.clone())
                        .then(() => pruneRuntime(cache))
                        .catch(() => {});
                }
                return networkResponse;
            }).catch(() => cachedResponse || offlinePage());
            return cachedResponse || networkFetch;
        });
    });
}

function pruneRuntime(cache) {
    return cache.keys().then((keys) => {
        if (keys.length <= RUNTIME_MAX_ENTRIES) return null;
        return cache.delete(keys[0])
            .then(() => pruneRuntime(cache))
            .catch(() => null);
    });
}

function offlineJson() {
    return new Response(
        JSON.stringify({ success: false, message: 'You are offline. Please reconnect and try again.', data: null, errors: null }),
        { headers: { 'Content-Type': 'application/json' } }
    );
}

function offlinePage() {
    return caches.match(scopeUrl('offline/offline.html')).then((offlineResponse) => {
        if (offlineResponse) {
            return offlineResponse;
        }
        return new Response('<!DOCTYPE html><html><body><h1>Offline</h1><p>You are offline.</p></body></html>', {
            headers: { 'Content-Type': 'text/html' }
        });
    });
}
