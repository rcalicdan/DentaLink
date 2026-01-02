const CACHE_NAME = 'no-cache-v1';

self.addEventListener('install', event => {
  self.skipWaiting();
});

self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cacheName => caches.delete(cacheName))
      );
    })
  );
  return self.clients.claim();
});

self.addEventListener('fetch', event => {
  event.respondWith(
    fetch(event.request)
      .catch(() => {
        return new Response(
          '<h1>No Internet Connection</h1><p>This app requires an internet connection to work.</p>',
          {
            headers: { 'Content-Type': 'text/html' }
          }
        );
      })
  );
});