// แซ่บกลางซอย — Service Worker v1.0
const CACHE_NAME = 'saep-klang-soi-v1';

// ไฟล์ที่ cache ไว้สำหรับโหลดเร็ว
const STATIC_ASSETS = [
  '/index.php',
  '/login.php',
  '/logo.png',
  '/manifest.json',
];

// ติดตั้ง SW — cache static assets
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => {
      return cache.addAll(STATIC_ASSETS).catch(() => {});
    })
  );
  self.skipWaiting();
});

// Activate — ลบ cache เก่า
self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(keys =>
      Promise.all(keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k)))
    )
  );
  self.clients.claim();
});

// Fetch — Network First (เหมาะกับ POS ที่ต้องข้อมูลสด)
self.addEventListener('fetch', event => {
  // ข้าม API calls — ต้องการข้อมูลสดเสมอ
  if (event.request.url.includes('/api/')) return;

  event.respondWith(
    fetch(event.request)
      .then(response => {
        // Cache static files
        if (event.request.method === 'GET' && response.status === 200) {
          const clone = response.clone();
          caches.open(CACHE_NAME).then(cache => cache.put(event.request, clone));
        }
        return response;
      })
      .catch(() => {
        // Offline fallback
        return caches.match(event.request) || caches.match('/index.php');
      })
  );
});
