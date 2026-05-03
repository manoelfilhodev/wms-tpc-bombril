
importScripts('/wms/public/js/idb.js');

const CACHE_NAME = 'wms-cache-v4';

const urlsToCache = [
  '/wms/public/',
  '/wms/public/formulario',
  '/wms/public/offline',
  '/wms/public/assets/css/icons.min.css',
  '/wms/public/assets/css/app-creative.min.css',
  '/wms/public/assets/css/app-creative-dark.min.css',
  '/wms/public/assets/js/vendor.min.js',
  '/wms/public/assets/js/app.min.js',
  '/wms/public/images/logo.png'
];

self.addEventListener('install', (event) => {
  console.log('[SW] Instalando...');
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      console.log('[SW] Cacheando arquivos essenciais...');
      return cache.addAll(urlsToCache);
    }).catch((e) => {
      console.warn('[SW] Falha ao cachear arquivos essenciais:', e);
    })
  );
});

self.addEventListener('activate', (event) => {
  console.log('[SW] Ativando...');
  event.waitUntil(
    caches.keys().then((keys) =>
      Promise.all(
        keys.map((key) => {
          if (key !== CACHE_NAME) {
            console.log('[SW] Removendo cache antigo:', key);
            return caches.delete(key);
          }
        })
      )
    )
  );
});

self.addEventListener('fetch', (event) => {
  event.respondWith(
    fetch(event.request).catch(() =>
      caches.match(event.request).then((cachedResponse) =>
        cachedResponse || caches.match('/wms/public/offline')
      )
    )
  );
});

self.addEventListener('sync', function (event) {
  if (event.tag === 'sync-formularios') {
    console.log('[SW] Iniciando sync dos formulários...');
    event.waitUntil(syncFormularios());
  }
});

async function syncFormularios() {
  const db = await idb.openDB('systex-db', 1);
  const tx = db.transaction('formularios', 'readwrite');
  const store = tx.objectStore('formularios');
  const pendentes = await store.getAll();

  let todosEnviados = true;

  for (const item of pendentes) {
    try {
      const res = await fetch('https://systex.com.br/wms/public/formulario', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(item)
      });

      if (res.ok) {
        console.log('[SW] ✔️ Formulário enviado com sucesso:', item);
      } else {
        todosEnviados = false;
        console.warn('[SW] ❌ Falha ao enviar formulário:', item);
      }

    } catch (e) {
      todosEnviados = false;
      console.error('[SW] 🛑 Erro ao sincronizar:', e);
    }
  }

  if (todosEnviados) {
    await store.clear();
    console.log('[SW] 🧹 Todos os dados foram sincronizados e removidos do IndexedDB.');

    if (Notification.permission === 'granted') {
      self.registration.showNotification("✔️ Todos os dados foram sincronizados!", {
        body: `${pendentes.length} formulários enviados com sucesso.`,
        icon: '/wms/public/images/logo.png'
      });
    }

  } else {
    console.warn('[SW] ⚠️ Nem todos os dados foram enviados. IndexedDB será mantido.');
  }
}
