@extends('layouts.auth')

@section('content')
    <div class="account-pages pt-2 pt-sm-5 pb-4 pb-sm-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xxl-4 col-lg-5">
                    <div class="card shadow-sm border-0">
                        <div class="card-header pt-2 pb-2 text-center bg-light border-0">
                            <a href="{{ url('/') }}" aria-label="Pagina inicial">
                                <span><img src="{{ asset('images/logo-sem-nome.png') }}" alt="Logo Systex"
                                        height="160"></span>
                            </a>
                        </div>

                        <div class="card-body p-4">
                            <div class="text-center w-75 m-auto mb-3">
                                <h4 class="text-dark text-center mt-0 fw-bold">Acesso ao sistema</h4>
                                <small class="text-muted">Entre com sua conta Microsoft corporativa.</small>
                            </div>

                            @if (session('error'))
                                <div class="alert alert-danger">{{ session('error') }}</div>
                            @endif

                            @if (session('success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    {{ session('success') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"
                                        aria-label="Fechar"></button>
                                </div>
                            @endif

                            @if ($showDeviceId)
                                <div class="device-id-box border rounded-3 p-2 mb-3 bg-light">
                                    <div class="d-flex align-items-center justify-content-between gap-2 mb-1">
                                        <small class="text-muted fw-semibold text-uppercase">ID deste dispositivo</small>
                                        <button type="button" id="copy-device-id"
                                            class="btn btn-sm btn-outline-secondary py-0">
                                            Copiar
                                        </button>
                                    </div>
                                    <input type="text" id="current-device-id"
                                        class="form-control form-control-sm font-monospace bg-white"
                                        value="{{ $deviceId }}" readonly aria-label="ID deste dispositivo"
                                        onclick="this.select()">
                                    <small id="copy-device-feedback" class="text-muted d-block mt-1">
                                        Envie este ID ao admin para liberar o acesso em /dispositivos.
                                    </small>
                                </div>
                            @endif

                            <a href="{{ route('login.microsoft') }}"
                                class="btn btn-primary d-flex align-items-center justify-content-center gap-2 w-100"
                                style="padding: 10px;">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/4/44/Microsoft_logo.svg"
                                    alt="Microsoft" width="20" height="20">
                                <span>Entrar com Microsoft</span>
                            </a>

                            <a href="https://painel-tpc.bombril.systex.com.br/app-download/app.apk"
                                class="btn btn-success d-flex align-items-center justify-content-center gap-2 w-100 mt-2"
                                download>
                                <i class="mdi mdi-android"></i>
                                <span>Baixar app Android (.APK)</span>
                            </a>

                            <div class="text-center mt-3">
                                <i class="mdi mdi-shield-check-outline text-success fs-2"></i>

                                <p class="text-muted small mb-1 fw-semibold">
                                    Autenticação corporativa segura
                                </p>

                                <div class="d-flex justify-content-center align-items-center gap-2 mt-1">
                                    <img src="https://upload.wikimedia.org/wikipedia/commons/f/fa/Microsoft_Azure.svg"
                                        alt="Microsoft Azure" style="max-width: 22px;">
                                    <span class="text-muted small">Microsoft Azure AD</span>
                                </div>

                                <p class="text-muted small mt-2 mb-0">
                                    Proteção com padrão empresarial e controle de acesso centralizado
                                </p>
                            </div>
                        </div>
                    </div>

                    <button id="btn-install" style="display: none;" class="btn btn-outline-secondary mt-3 w-100">
                        <i class="mdi mdi-download"></i> Instalar aplicativo
                    </button>

                    <div class="row mt-3">
                        <div class="col-12 text-center">
                            <p class="text-muted">&copy; {{ date('Y') }} SYSTEX Sistemas Inteligentes</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($showDeviceId)
        <script>
            (function() {
                const key = @json($deviceCookieName);
                const legacyKey = @json($legacyDeviceCookieName);
                let deviceId = @json($deviceId);

                if (!deviceId && window.crypto && typeof window.crypto.randomUUID === 'function') {
                    deviceId = window.crypto.randomUUID();
                }

                if (!deviceId) {
                    deviceId = 'web-' + Date.now().toString(36) + '-' + Math.random().toString(36).slice(2, 12);
                }

                localStorage.setItem(key, deviceId);
                localStorage.removeItem(legacyKey);

                const secure = window.location.protocol === 'https:' ? '; secure' : '';
                document.cookie = key + '=' + encodeURIComponent(deviceId) + '; path=/; max-age=31536000; samesite=lax' +
                    secure;

                const input = document.getElementById('current-device-id');
                const button = document.getElementById('copy-device-id');
                const feedback = document.getElementById('copy-device-feedback');

                if (input) {
                    input.value = deviceId;
                }

                if (button && input) {
                    button.addEventListener('click', async function() {
                        input.select();
                        input.setSelectionRange(0, input.value.length);

                        try {
                            if (navigator.clipboard && window.isSecureContext) {
                                await navigator.clipboard.writeText(input.value);
                            } else {
                                document.execCommand('copy');
                            }

                            if (feedback) {
                                feedback.textContent = 'ID copiado para a area de transferencia.';
                            }
                        } catch (error) {
                            if (feedback) {
                                feedback.textContent =
                                    'Nao foi possivel copiar automaticamente. Selecione e copie o ID.';
                            }
                        }
                    });
                }
            })();
        </script>
    @endif

    <script>
        let deferredPrompt;

        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;

            const installBtn = document.getElementById('btn-install');
            installBtn.style.display = 'inline-block';

            installBtn.addEventListener('click', () => {
                installBtn.style.display = 'none';
                if (deferredPrompt) {
                    deferredPrompt.prompt();
                }
            }, {
                once: true
            });
        });
    </script>

    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register("{{ asset('sw.js') }}").catch(() => {});
        }
    </script>
@endsection
