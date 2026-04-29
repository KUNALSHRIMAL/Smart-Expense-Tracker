const CACHE = 'expense-v1';
const SHELL = [
  '/money/',
  '/money/login.php',
  '/money/dashboard.php',
  '/money/add-expense.php',
  '/money/history.php',
  '/money/settings.php',
  '/money/assets/css/app.css',
];

// Install: cache the app shell
self.addEventListener('install', e => {
  self.skipWaiting();
  e.waitUntil(
    caches.open(CACHE).then(c => c.addAll(SHELL)).catch(() => {})
  );
});

// Activate: remove old caches
self.addEventListener('activate', e => {
  e.waitUntil(
    caches.keys().then(keys =>
      Promise.all(keys.filter(k => k !== CACHE).map(k => caches.delete(k)))
    ).then(() => self.clients.claim())
  );
});

// Fetch: network-first for navigations, cache-first for assets
self.addEventListener('fetch', e => {
  const url = new URL(e.request.url);

  // Skip non-GET and API requests
  if (e.request.method !== 'GET') return;
  if (url.pathname.includes('/api/'))   return;
  if (url.pathname.includes('/setup/')) return;

  if (e.request.mode === 'navigate') {
    // Network-first for PHP pages (dynamic content)
    e.respondWith(
      fetch(e.request)
        .then(res => {
          const clone = res.clone();
          caches.open(CACHE).then(c => c.put(e.request, clone));
          return res;
        })
        .catch(() => caches.match(e.request)
          .then(cached => cached || caches.match('/money/login.php'))
        )
    );
  } else {
    // Cache-first for static assets (Tailwind CDN, icons, etc.)
    e.respondWith(
      caches.match(e.request).then(cached => {
        if (cached) return cached;
        return fetch(e.request).then(res => {
          if (res.ok) {
            const clone = res.clone();
            caches.open(CACHE).then(c => c.put(e.request, clone));
          }
          return res;
        }).catch(() => new Response('Offline', {status: 503}));
      })
    );
  }
});
