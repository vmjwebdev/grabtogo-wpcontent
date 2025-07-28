const CACHE_NAME = 'grabtogo-v1.0.1';
const STATIC_CACHE = 'grabtogo-static-v1';
const DYNAMIC_CACHE = 'grabtogo-dynamic-v1';

// Files to cache for offline use
const STATIC_FILES = [
  '/',
  '/wp-content/themes/listeo-child/assets/css/custom.css',
  '/wp-content/themes/listeo-child/assets/js/custom.js',
  '/wp-content/themes/listeo-child/manifest.json',
  'https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap'
];

// Install event - cache static files
self.addEventListener('install', event => {
  console.log('Service Worker installing...');
  event.waitUntil(
    caches.open(STATIC_CACHE)
      .then(cache => {
        console.log('Caching static files');
        return cache.addAll(STATIC_FILES);
      })
      .catch(err => console.log('Failed to cache static files:', err))
  );
  self.skipWaiting();
});

// Activate event - clean up old caches
self.addEventListener('activate', event => {
  console.log('Service Worker activating...');
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames
          .filter(cacheName => {
            return cacheName !== STATIC_CACHE && cacheName !== DYNAMIC_CACHE;
          })
          .map(cacheName => caches.delete(cacheName))
      );
    })
  );
  self.clients.claim();
});

// Fetch event - serve from cache when offline
self.addEventListener('fetch', event => {
  // Only handle GET requests
  if (event.request.method !== 'GET') {
    return;
  }

  // Skip caching for admin, API calls, and external requests
  if (
    event.request.url.includes('/wp-admin/') ||
    event.request.url.includes('/wp-json/') ||
    event.request.url.includes('admin-ajax.php') ||
    !event.request.url.startsWith(self.location.origin)
  ) {
    return;
  }

  event.respondWith(
    caches.match(event.request)
      .then(response => {
        // Return cached version if available
        if (response) {
          console.log('Serving from cache:', event.request.url);
          return response;
        }

        // Otherwise fetch from network and cache
        return fetch(event.request)
          .then(fetchResponse => {
            // Check if response is valid
            if (!fetchResponse || fetchResponse.status !== 200 || fetchResponse.type !== 'basic') {
              return fetchResponse;
            }

            // Clone the response as it can only be consumed once
            const responseToCache = fetchResponse.clone();

            // Cache the response
            caches.open(DYNAMIC_CACHE)
              .then(cache => {
                // Only cache HTML, CSS, JS, and images
                if (
                  event.request.url.includes('.css') ||
                  event.request.url.includes('.js') ||
                  event.request.url.includes('.png') ||
                  event.request.url.includes('.jpg') ||
                  event.request.url.includes('.jpeg') ||
                  event.request.url.includes('.gif') ||
                  event.request.url.includes('.svg') ||
                  event.request.headers.get('accept').includes('text/html')
                ) {
                  cache.put(event.request, responseToCache);
                }
              });

            return fetchResponse;
          })
          .catch(error => {
            console.log('Fetch failed, serving offline page:', error);
            
            // Return offline page for navigation requests
            if (event.request.headers.get('accept').includes('text/html')) {
              return caches.match('/offline.html') || 
                     new Response('You are offline. Please check your connection.', {
                       headers: { 'Content-Type': 'text/html' }
                     });
            }
            
            // Return placeholder for images
            if (event.request.headers.get('accept').includes('image')) {
              return new Response(
                '<svg width="400" height="300" xmlns="http://www.w3.org/2000/svg"><rect width="100%" height="100%" fill="#f0f0f0"/><text x="50%" y="50%" text-anchor="middle" dy=".3em" fill="#666">Image unavailable offline</text></svg>',
                { headers: { 'Content-Type': 'image/svg+xml' } }
              );
            }
          });
      })
  );
});

// Background sync for uploading stories when back online
self.addEventListener('sync', event => {
  console.log('Background sync triggered:', event.tag);
  
  if (event.tag === 'upload-story') {
    event.waitUntil(
      // Get queued stories from IndexedDB and upload them
      uploadQueuedStories()
    );
  }
});

// Push notification handling
self.addEventListener('push', event => {
  console.log('Push notification received:', event);
  
  const options = {
    body: event.data ? event.data.text() : 'New offers available near you!',
    icon: '/wp-content/themes/listeo-child/assets/icons/icon-192x192.png',
    badge: '/wp-content/themes/listeo-child/assets/icons/badge-72x72.png',
    vibrate: [200, 100, 200],
    data: {
      url: '/'
    },
    actions: [
      {
        action: 'view',
        title: 'View Offers'
      },
      {
        action: 'close',
        title: 'Close'
      }
    ]
  };

  event.waitUntil(
    self.registration.showNotification('GrabToGo', options)
  );
});

// Notification click handling
self.addEventListener('notificationclick', event => {
  console.log('Notification clicked:', event);
  
  event.notification.close();
  
  if (event.action === 'view') {
    event.waitUntil(
      clients.openWindow(event.notification.data.url || '/')
    );
  }
});

// Helper function to upload queued stories
async function uploadQueuedStories() {
  try {
    // This would integrate with IndexedDB to get queued stories
    // and attempt to upload them when back online
    console.log('Attempting to upload queued stories...');
    
    // Implementation would go here to:
    // 1. Get stories from IndexedDB queue
    // 2. Attempt to upload each one
    // 3. Remove from queue on success
    
  } catch (error) {
    console.error('Failed to upload queued stories:', error);
  }
}

// Cache size management
async function limitCacheSize(cacheName, maxItems) {
  const cache = await caches.open(cacheName);
  const keys = await cache.keys();
  
  if (keys.length > maxItems) {
    // Delete oldest entries
    const keysToDelete = keys.slice(0, keys.length - maxItems);
    await Promise.all(keysToDelete.map(key => cache.delete(key)));
  }
}

// Clean up cache periodically
self.addEventListener('message', event => {
  if (event.data && event.data.type === 'CLEANUP_CACHE') {
    event.waitUntil(
      Promise.all([
        limitCacheSize(DYNAMIC_CACHE, 50), // Keep last 50 dynamic items
        limitCacheSize(STATIC_CACHE, 20)   // Keep last 20 static items
      ])
    );
  }
});

console.log('GrabToGo Service Worker loaded successfully');