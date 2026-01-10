// Service Worker for Roary PWA
const CACHE_NAME = 'roary-v6';
const API_CACHE_NAME = 'roary-api-v2';
const urlsToCache = [
  '/public/css/pico.min.css',
  '/public/fonts/JetBrainsMono-Regular.woff2',
  '/public/fonts/JetBrainsMono-Bold.woff2',
  '/manifest.json'
];

// Install event - cache static resources
self.addEventListener('install', (event) => {
  console.log('[ServiceWorker] Install');
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => {
        console.log('[ServiceWorker] Caching app shell');
        return cache.addAll(urlsToCache);
      })
      .then(() => self.skipWaiting())
  );
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
  console.log('[ServiceWorker] Activate');
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cacheName) => {
          if (cacheName !== CACHE_NAME && cacheName !== API_CACHE_NAME) {
            console.log('[ServiceWorker] Removing old cache:', cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
  return self.clients.claim();
});

// Fetch event - serve from cache/network based on resource type
self.addEventListener('fetch', (event) => {
  if (!event.request.url.startsWith('http')) {
    return;
  }

  // Never intercept POST/PUT/DELETE - always go to network
  if (event.request.method !== 'GET') {
    return;
  }

  const url = new URL(event.request.url);
  const isStaticAsset = url.pathname.startsWith('/public/') || url.pathname === '/manifest.json';
  const isJavaScript = url.pathname.endsWith('.js');
  const isApiRequest = url.pathname.startsWith('/api/');

  if (isApiRequest) {
    // API requests: network-first with session-aware caching
    event.respondWith(
      fetch(event.request.clone())
        .then((response) => {
          if (response && response.status === 200) {
            const responseToCache = response.clone();
            caches.open(API_CACHE_NAME).then((cache) => {
              cache.put(event.request, responseToCache);
            });
          }
          return response;
        })
        .catch(() => {
          return caches.match(event.request).then((cachedResponse) => {
            if (cachedResponse) {
              return cachedResponse.clone().json().then(data => {
                if (data.success === false && data.requiresAuth) {
                  return new Response(JSON.stringify({
                    success: false,
                    message: 'Authentication required',
                    requiresAuth: true
                  }), {
                    status: 401,
                    headers: { 'Content-Type': 'application/json' }
                  });
                }
                return cachedResponse;
              }).catch(() => cachedResponse);
            }
            
            return new Response(JSON.stringify({
              success: false,
              message: 'Offline - Please login to view content',
              requiresAuth: true
            }), {
              status: 401,
              headers: { 'Content-Type': 'application/json' }
            });
          });
        })
    );
  } else if (isJavaScript) {
    // JavaScript files: network-first (always fresh code)
    event.respondWith(
      fetch(event.request)
        .then((response) => {
          if (response && response.status === 200) {
            const responseToCache = response.clone();
            caches.open(CACHE_NAME).then((cache) => {
              cache.put(event.request, responseToCache);
            });
          }
          return response;
        })
        .catch(() => {
          return caches.match(event.request);
        })
    );
  } else if (isStaticAsset) {
    // Static assets: cache-first (fast, updated on cache version bump)
    event.respondWith(
      caches.match(event.request)
        .then((response) => {
          if (response) {
            return response;
          }

          return fetch(event.request).then((response) => {
            if (!response || response.status !== 200 || response.type !== 'basic') {
              return response;
            }

            const responseToCache = response.clone();
            caches.open(CACHE_NAME).then((cache) => {
              cache.put(event.request, responseToCache);
            });

            return response;
          });
        })
        .catch(() => {
          return new Response('Offline - Vital resource unavailable', {
            status: 503,
            statusText: 'Service Unavailable',
            headers: new Headers({
              'Content-Type': 'text/plain'
            })
          });
        })
    );
  } else {
    // Dynamic pages: network-first (always fresh when online, cached for offline)
    event.respondWith(
      fetch(event.request)
        .then((response) => {
          if (response && response.status === 200 && !response.redirected) {
            const responseToCache = response.clone();
            caches.open(CACHE_NAME).then((cache) => {
              cache.put(event.request, responseToCache);
            });
          }
          return response;
        })
        .catch(() => {
          return caches.match(event.request).then((cachedResponse) => {
            if (cachedResponse) {
              return cachedResponse;
            }
            
            return new Response('Offline - Roary has no up-to-date cache for this page', {
              status: 503,
              statusText: 'Service Unavailable',
              headers: new Headers({
                'Content-Type': 'text/html'
              })
            });
          });
        })
    );
  }
});

// Message handler for cache invalidation
self.addEventListener('message', (event) => {
  if (event.data && event.data.type === 'CLEAR_API_CACHE') {
    event.waitUntil(
      Promise.all([
        caches.delete(API_CACHE_NAME),
        caches.open(CACHE_NAME).then(cache => {
          return cache.keys().then(keys => {
            return Promise.all(
              keys.filter(req => {
                const url = new URL(req.url);
                return url.pathname === '/' || url.pathname === '/index.php' || url.pathname === '/index';
              }).map(req => cache.delete(req))
            );
          });
        })
      ]).then(() => {
        console.log('[ServiceWorker] API and page cache cleared');
      })
    );
  }
});
