<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Painel do Operador') | WMS 4.0</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=no">
    <meta content="Sistema WMS" name="description" />
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.ico') }}">

    <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/app-creative.min.css') }}" rel="stylesheet" type="text/css" id="light-style" />
    <link href="{{ asset('assets/css/app-creative-dark.min.css') }}" rel="stylesheet" type="text/css" id="dark-style" />
    <link href="{{ asset('assets/css/wms-ui.css') }}" rel="stylesheet" type="text/css" />
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <meta name="theme-color" content="#111827">
    @env('local')
        @vite('resources/js/app.js')
    @endenv

    <style>
        :root {
            --operator-bg: #0b1421;
            --operator-surface: #121f31;
            --operator-surface-2: #17263a;
            --operator-border: rgba(148, 163, 184, 0.18);
            --operator-text: #f8fafc;
            --operator-muted: #9fb1cc;
            --operator-blue: #3b8de3;
            --operator-blue-soft: rgba(59, 141, 227, 0.16);
        }

        body {
            background:
                radial-gradient(circle at 10% 0%, rgba(59, 141, 227, 0.12), transparent 30%),
                linear-gradient(180deg, #0b1421 0%, #09111d 100%);
            color: var(--operator-text);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .top-bar-operador {
            min-height: 64px;
            background: rgba(11, 20, 33, 0.96) !important;
            border-bottom: 1px solid var(--operator-border);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.16) !important;
        }

        .top-bar-operador .btn {
            border-color: rgba(148, 163, 184, 0.46);
            color: #dbeafe;
            background: rgba(18, 31, 49, 0.78);
        }

        .top-bar-operador .btn:hover,
        .top-bar-operador .btn:focus {
            border-color: rgba(59, 141, 227, 0.78);
            color: #ffffff;
            background: rgba(59, 141, 227, 0.18);
        }

        footer {
            background-color: #09111d;
            border-top: 1px solid var(--operator-border);
            padding: 0.75rem 1rem;
            text-align: center;
            color: var(--operator-muted);
            font-size: 0.9rem;
            margin-top: auto;
        }

        .content-wrapper {
            padding: clamp(1rem, 2.5vw, 2rem) 20px 40px;
        }
    </style>

    @yield('head')
</head>
<body data-layout-config='{"leftSideBarTheme":"dark","layoutBoxed":false,"leftSidebarCondensed":false,"darkMode":true}'>
    @include('partials.dev-environment-badge')

    <div class="top-bar-operador d-flex justify-content-between align-items-center px-3 py-2 shadow-sm bg-dark">
        <div>
            <button onclick="history.back()" class="btn btn-outline-light btn-sm d-flex align-items-center" title="Voltar para a pagina anterior">
                <i class="mdi mdi-arrow-left me-1 fs-5"></i> Voltar
            </button>
        </div>
        <div>
            <a href="{{ route('painel.operador') }}" class="btn btn-outline-light btn-sm d-flex align-items-center" title="Voltar ao menu">
                <i class="mdi mdi-home-outline me-1 fs-5"></i> Menu
            </a>
        </div>
        <div>
            <a href="{{ route('logout') }}" class="btn btn-outline-light btn-sm d-flex align-items-center" title="Sair do sistema">
                <i class="mdi mdi-logout me-1 fs-5"></i> Sair
            </a>
        </div>
    </div>

    <div class="content-wrapper container-fluid">
        @yield('content')
    </div>

    <footer>
        @auth
            <div><b>Usuario logado:</b> {{ Auth::user()->nome }} | {{ ucfirst(Auth::user()->tipo) }}</div>
            <div class="text-muted mt-2 small">
                Versao app: {{ config('app.app_version') }}
            </div>
        @endauth
        <div class="mt-2" style="font-size: 0.8rem; color: #888;">
            Desenvolvido por <strong>Systex</strong>
        </div>
    </footer>

    <script src="{{ asset('assets/js/vendor.min.js') }}"></script>
    <script src="{{ asset('assets/js/app.min.js') }}"></script>
    <script>
        localStorage.setItem('data-layout-config', JSON.stringify({
            leftSideBarTheme: 'dark',
            layoutBoxed: false,
            leftSidebarCondensed: false,
            darkMode: true
        }));
    </script>

    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register("{{ asset('service-worker.js') }}").catch(() => {});
            });
        }
    </script>

    @yield('scripts')
</body>
</html>
