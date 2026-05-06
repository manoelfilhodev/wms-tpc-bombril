@php
    $segments = Request::segments();
    $title = ucfirst(end($segments));
    $isOperatorLayout = Auth::check() && Auth::user()->tipo === 'operador';
@endphp

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8" />
    <title>@yield('title', $title) | WMS 4.0</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Sistema WMS" name="description" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.ico') }}">

    <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/app-creative.min.css') }}" rel="stylesheet" type="text/css" id="light-style" />
    <link href="{{ asset('assets/css/app-creative-dark.min.css') }}" rel="stylesheet" type="text/css" id="dark-style" />
    <link href="{{ asset('assets/css/wms-ui.css') }}" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    @env('local')
        @vite('resources/js/app.js')
    @endenv

    <style>
        .content::before { content: none !important; }
        .content > svg { display: none !important; }
        .sidebar-toggle, .vertical-menu-toggle, .menu-toggle { display: none !important; }

        .top-bar-operador {
            background-color: #111827;
            padding: 0.75rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .top-bar-operador span { color: #fff; font-weight: bold; }
        .top-bar-operador .btn { font-size: 0.9rem; }

        body.operator-fullscreen .content-page {
            margin-left: 0 !important;
            width: 100% !important;
        }

        body.operator-fullscreen .content {
            min-height: calc(100vh - 70px);
        }

        body.operator-fullscreen .container-fluid {
            max-width: 100%;
        }

        body.operator-fullscreen .footer {
            left: 0 !important;
        }
    </style>

    @yield('head')
</head>

<body class="loading {{ $isOperatorLayout ? 'operator-fullscreen' : '' }}" data-layout-config='{"leftSideBarTheme":"dark","layoutBoxed":false,"leftSidebarCondensed":false,"darkMode":false}'>
    @include('partials.dev-environment-badge')

    <div class="wrapper">
        @auth
            @if(! $isOperatorLayout)
                @include('partials.sidebar')
            @endif
        @endauth

        <div class="content-page">
            <div class="content">
                @auth
                    @if(! $isOperatorLayout)
                        @include('partials.header')
                    @else
                        <div class="top-bar-operador">
                            <span>Systex WMS</span>
                            <a href="{{ route('painel.operador') }}" class="btn btn-outline-light btn-sm">
                                <i class="bi bi-arrow-left"></i> Voltar ao Inicio
                            </a>
                        </div>
                    @endif
                @endauth

                <div class="container-fluid mt-3">
                    @yield('content')
                </div>
            </div>

            @include('partials.footer')
        </div>
    </div>

    <script src="{{ asset('assets/js/vendor.min.js') }}"></script>
    <script src="{{ asset('assets/js/app.min.js') }}"></script>
    <script>
        function initTooltips() {
            const nodes = document.querySelectorAll('[data-bs-toggle="tooltip"]');
            nodes.forEach(function (node) {
                const existing = bootstrap.Tooltip.getInstance(node);
                if (existing) {
                    existing.dispose();
                }

                const tip = new bootstrap.Tooltip(node, {
                    trigger: 'hover',
                    container: 'body',
                    boundary: document.body
                });

                node.addEventListener('mouseleave', function () {
                    tip.hide();
                    node.blur();
                });
            });
        }

        function hideAllTooltips() {
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (node) {
                const tip = bootstrap.Tooltip.getInstance(node);
                if (tip) {
                    tip.hide();
                }
                node.blur();
            });
        }
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const storedTheme = localStorage.getItem('darkMode') === 'true';
            const html = document.documentElement;
            const body = document.body;
            const lightStyle = document.getElementById('light-style');
            const darkStyle = document.getElementById('dark-style');
            const defaultConfig = {
                leftSideBarTheme: 'dark',
                layoutBoxed: false,
                leftSidebarCondensed: false,
                darkMode: false
            };

            function applyTheme(isDark) {
                const config = { ...defaultConfig, darkMode: isDark };
                const serialized = JSON.stringify(config);
                html.setAttribute('data-layout-config', serialized);
                body.setAttribute('data-layout-config', serialized);
                html.setAttribute('data-theme', isDark ? 'dark' : 'light');
                body.setAttribute('data-theme', isDark ? 'dark' : 'light');
                lightStyle.disabled = isDark;
                darkStyle.disabled = !isDark;
            }

            applyTheme(storedTheme);
            initTooltips();
            document.addEventListener('click', hideAllTooltips);
            document.addEventListener('scroll', hideAllTooltips, true);
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    hideAllTooltips();
                }
            });

            const darkModeToggle = document.getElementById('toggle-dark-mode');
            if (darkModeToggle) {
                darkModeToggle.addEventListener('click', function () {
                    const isDark = html.getAttribute('data-theme') !== 'dark';
                    localStorage.setItem('darkMode', String(isDark));
                    applyTheme(isDark);
                    initTooltips();
                });
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/idb@7/build/umd.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('meu-formulario');
            const msg = document.getElementById('mensagem');

            if (!form || !msg || typeof idb === 'undefined') {
                return;
            }

            const openDB = () => idb.openDB('systex-db', 1, {
                upgrade(db) {
                    if (!db.objectStoreNames.contains('formularios')) {
                        db.createObjectStore('formularios', { autoIncrement: true });
                    }
                }
            });

            form.addEventListener('submit', async function (e) {
                e.preventDefault();

                const dados = {
                    produto: form.produto.value,
                    quantidade: form.quantidade.value,
                    criado_em: new Date().toISOString()
                };

                try {
                    const res = await fetch('/formulario', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(dados)
                    });

                    if (!res.ok) {
                        throw new Error('Falha ao enviar');
                    }

                    msg.innerHTML = '<div class="alert alert-success">Enviado com sucesso!</div>';
                    form.reset();
                } catch (err) {
                    const db = await openDB();
                    const tx = db.transaction('formularios', 'readwrite');
                    await tx.store.add(dados);
                    await tx.done;

                    if ('serviceWorker' in navigator && 'SyncManager' in window) {
                        const reg = await navigator.serviceWorker.ready;
                        await reg.sync.register('sync-formularios');
                    }

                    msg.innerHTML = '<div class="alert alert-warning">Voce esta offline. Dados salvos localmente e serao enviados depois.</div>';
                    form.reset();
                }
            });
        });
    </script>

    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function () {
                navigator.serviceWorker.register("{{ asset('service-worker.js') }}").catch(function () {});
            });
        }
    </script>

    <script>
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission().then(function () {});
        }
    </script>

    @yield('scripts')
    @stack('scripts')
</body>
</html>
