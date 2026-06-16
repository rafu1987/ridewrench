const CACHE_NAME = 'ridewrench-static-v1'
const OFFLINE_URL = '/offline.html'

const STATIC_ASSETS = [
  OFFLINE_URL,
  '/manifest.webmanifest',
  '/images/favicon/icon-192x192.png',
  '/images/favicon/icon-512x512.png'
]

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches
      .open(CACHE_NAME)
      .then((cache) => cache.addAll(STATIC_ASSETS))
      .then(() => self.skipWaiting())
  )
})

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches
      .keys()
      .then((keys) => Promise.all(keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key))))
      .then(() => self.clients.claim())
  )
})

self.addEventListener('fetch', (event) => {
  const request = event.request
  const url = new URL(request.url)

  if (request.method !== 'GET') {
    return
  }

  if (url.origin !== self.location.origin) {
    return
  }

  if (url.pathname.startsWith('/build/')) {
    event.respondWith(
      caches.open(CACHE_NAME).then(async (cache) => {
        const cached = await cache.match(request)

        if (cached) {
          return cached
        }

        const response = await fetch(request)
        cache.put(request, response.clone())

        return response
      })
    )

    return
  }

  if (request.mode === 'navigate') {
    event.respondWith(fetch(request).catch(() => caches.match(OFFLINE_URL)))
  }
})
