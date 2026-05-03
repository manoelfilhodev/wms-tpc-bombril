const CACHE_NAME = 'systex-wms-v1'; // 👈 Altere esse valor a cada nova versão

self.addEventListener('install', event => {
    console.log('[SW] Instalando nova versão');
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => {
            return cache.addAll([
                '/',
                '/wms/public/painel-operador',
                '/wms/public/manifest.webmanifest',
                '/wms/public/assets/css/app-creative-dark.min.css',
                '/wms/public/icons/systex-icon-192x192.png',
                '/wms/public/icons/systex-icon-512x512.png'
                // Adicione arquivos que deseja cachear
            ]);
        })
    );
    self.skipWaiting();
});

self.addEventListener('activate', event => {
    console.log('[SW] Limpando versões antigas');
    event.waitUntil(
        caches.keys().then(keys => {
            return Promise.all(
                keys.filter(key => key !== CACHE_NAME)
                    .map(key => caches.delete(key))
            );
        })
    );
    return self.clients.claim();
});
