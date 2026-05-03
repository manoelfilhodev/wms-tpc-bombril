@extends($layout)

@section('title', 'Painel Operacional')

@section('head')
<style>
    .operator-hub {
        min-height: calc(100vh - 185px);
        padding: clamp(0.75rem, 2.5vw, 2rem) 0;
        color: #f8fafc;
    }

    .operator-hub .container {
        max-width: 1480px;
    }

    .operator-hub-shell {
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(148, 163, 184, 0.20);
        border-radius: 22px;
        background:
            radial-gradient(circle at top left, rgba(59, 141, 227, 0.18), transparent 34%),
            linear-gradient(135deg, rgba(18, 31, 49, 0.98), rgba(11, 20, 33, 0.96));
        box-shadow: 0 24px 70px rgba(0, 0, 0, 0.30);
    }

    .operator-hub-shell::after {
        content: "";
        position: absolute;
        inset: auto -12% -35% 42%;
        height: 320px;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.10), transparent 68%);
        pointer-events: none;
    }

    .operator-hub-content {
        position: relative;
        z-index: 1;
        padding: clamp(1.25rem, 3.4vw, 2.75rem);
    }

    .operator-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 1rem;
        padding: 0.35rem 0.75rem;
        border: 1px solid rgba(59, 141, 227, 0.34);
        border-radius: 999px;
        background: rgba(59, 141, 227, 0.12);
        color: #bcd7ff;
        font-size: 0.76rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .operator-title {
        margin: 0;
        color: #ffffff;
        font-size: clamp(2rem, 4vw, 3.6rem);
        font-weight: 800;
        letter-spacing: -0.045em;
    }

    .operator-subtitle {
        max-width: 780px;
        margin: 0.9rem 0 0;
        color: #cbd5e1;
        font-size: 1rem;
        line-height: 1.65;
    }

    .operator-card {
        display: flex;
        min-height: 198px;
        height: 100%;
        padding: 1.25rem;
        border: 1px solid rgba(148, 163, 184, 0.22);
        border-radius: 18px;
        background: rgba(18, 31, 49, 0.78);
        color: #f8fafc;
        box-shadow: 0 16px 42px rgba(0, 0, 0, 0.20);
        text-decoration: none;
        transition: transform 0.18s ease, border-color 0.18s ease, background 0.18s ease, box-shadow 0.18s ease;
    }

    .operator-card:hover,
    .operator-card:focus {
        transform: translateY(-4px);
        border-color: rgba(59, 141, 227, 0.62);
        background: rgba(23, 38, 58, 0.95);
        box-shadow: 0 22px 54px rgba(0, 0, 0, 0.28);
        color: #ffffff;
    }

    .operator-card-inner {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        width: 100%;
    }

    .operator-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 54px;
        height: 54px;
        margin-bottom: 1.2rem;
        border: 1px solid rgba(59, 141, 227, 0.24);
        border-radius: 16px;
        background: rgba(59, 141, 227, 0.16);
        color: #75b8ff;
        font-size: 1.75rem;
    }

    .operator-card h3 {
        margin: 0;
        color: #ffffff;
        font-size: 1.3rem;
        font-weight: 800;
    }

    .operator-card p {
        margin: 0.65rem 0 1.2rem;
        color: #cbd5e1;
        line-height: 1.55;
    }

    .operator-card-action {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        color: #75b8ff;
        font-weight: 700;
    }

    .operator-user-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.55rem;
        padding: 0.65rem 0.9rem;
        border: 1px solid rgba(148, 163, 184, 0.24);
        border-radius: 14px;
        background: rgba(18, 31, 49, 0.84);
        color: #dbeafe;
        font-weight: 700;
    }

    .operator-user-badge i {
        color: #75b8ff;
    }

    @media (max-width: 767.98px) {
        .operator-card {
            min-height: 178px;
        }
    }
</style>
@endsection

@section('content')
<div class="operator-hub">
    <div class="container">
        <section class="operator-hub-shell">
            <div class="operator-hub-content">
                <span class="operator-eyebrow">
                    <i class="mdi mdi-view-dashboard-outline"></i>
                    Acesso operacional
                </span>

                <div class="row align-items-end g-3 mb-4 mb-lg-5">
                    <div class="col-lg-8">
                        <h1 class="operator-title">Painel do Operador</h1>
                        <p class="operator-subtitle">
                            Acesso rapido as rotinas liberadas para operacao: separacao,
                            painel de acompanhamento, stretch, identificacao e report.
                        </p>
                    </div>
                    <div class="col-lg-4 text-lg-end">
                        @auth
                            <span class="operator-user-badge">
                                <i class="mdi mdi-account-circle-outline"></i>
                                {{ Auth::user()->nome }} | {{ ucfirst(Auth::user()->tipo) }}
                            </span>
                        @endauth
                    </div>
                </div>

                <div class="row g-3 g-xl-4">
                    <div class="col-12 col-lg-4">
                        <a href="{{ route('demandas.operacional') }}" class="operator-card">
                            <span class="operator-card-inner">
                                <span>
                                    <span class="operator-icon">
                                        <i class="mdi mdi-format-list-checks"></i>
                                    </span>
                                    <h3>Separacao</h3>
                                    <p>Consultar DTs em picking e executar a rotina operacional liberada.</p>
                                </span>
                                <span class="operator-card-action">
                                    Acessar separacao
                                    <i class="mdi mdi-arrow-right"></i>
                                </span>
                            </span>
                        </a>
                    </div>

                    <div class="col-12 col-lg-4">
                        <a href="{{ route('painel.tv') }}" target="_blank" rel="noopener" class="operator-card">
                            <span class="operator-card-inner">
                                <span>
                                    <span class="operator-icon">
                                        <i class="mdi mdi-television-play"></i>
                                    </span>
                                    <h3>Painel TV</h3>
                                    <p>Acompanhar a operacao em uma tela dedicada para monitoramento.</p>
                                </span>
                                <span class="operator-card-action">
                                    Abrir painel
                                    <i class="mdi mdi-open-in-new"></i>
                                </span>
                            </span>
                        </a>
                    </div>

                    <div class="col-12 col-lg-4">
                        <a href="{{ route('stretch.apontar') }}" class="operator-card">
                            <span class="operator-card-inner">
                                <span>
                                    <span class="operator-icon">
                                        <i class="mdi mdi-barcode-scan"></i>
                                    </span>
                                    <h3>Paletes Stretch</h3>
                                    <p>Registrar apontamentos de paletes stretch com leitura ou digitacao.</p>
                                </span>
                                <span class="operator-card-action">
                                    Apontar palete
                                    <i class="mdi mdi-arrow-right"></i>
                                </span>
                            </span>
                        </a>
                    </div>

                    <div class="col-12 col-lg-4">
                        <a href="{{ route('demandas.identificacaoA4') }}" class="operator-card">
                            <span class="operator-card-inner">
                                <span>
                                    <span class="operator-icon">
                                        <i class="mdi mdi-printer"></i>
                                    </span>
                                    <h3>Identificacao</h3>
                                    <p>Gerar identificacao A4 para DTs e apoiar a sinalizacao da area.</p>
                                </span>
                                <span class="operator-card-action">
                                    Abrir identificacao
                                    <i class="mdi mdi-arrow-right"></i>
                                </span>
                            </span>
                        </a>
                    </div>

                    <div class="col-12 col-lg-4">
                        <a href="{{ route('demandas.reportTurno') }}" class="operator-card">
                            <span class="operator-card-inner">
                                <span>
                                    <span class="operator-icon">
                                        <i class="mdi mdi-whatsapp"></i>
                                    </span>
                                    <h3>Report</h3>
                                    <p>Abrir o report de turno para conferencia, print e compartilhamento.</p>
                                </span>
                                <span class="operator-card-action">
                                    Abrir report
                                    <i class="mdi mdi-arrow-right"></i>
                                </span>
                            </span>
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection
