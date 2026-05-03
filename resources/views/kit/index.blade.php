@extends($layout)

@section('content')

<div class="container-fluid px-4 py-3">
    @include('partials.breadcrumb-auto')
    
    <!-- Header com ícone roxo -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
            <div class="icon-wrapper me-3">
                <i class="mdi mdi-package-variant-closed display-6 text-primary"></i>
            </div>
            <div>
                <h3 class="mb-1 fw-bold text-dark">Central de Produção</h3>
                <p class="text-muted mb-0 small">Gerencie programações, apontamentos e etiquetas de produção</p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="tooltip" title="Atualizar">
                <i class="mdi mdi-refresh"></i>
            </button>
        </div>
    </div>

    <!-- Grid de Cards de Ações -->
    <div class="row g-3">
        <!-- Programação Kit -->
        <div class="col-md-6 col-lg-4">
            <a href="{{ route('kit.programar') }}" class="text-decoration-none">
                <div class="card action-card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-start">
                            <div class="action-icon bg-success bg-opacity-10 text-success me-3">
                                <i class="mdi mdi-playlist-plus mdi-36px"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="card-title mb-2 text-dark fw-semibold">Programação</h5>
                                <p class="card-text text-muted small mb-0">Lançar nova programação de montagem</p>
                            </div>
                            <i class="mdi mdi-chevron-right text-muted"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Apontamentos -->
        <div class="col-md-6 col-lg-4">
            <a href="{{ route('kit.apontar') }}" class="text-decoration-none">
                <div class="card action-card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-start">
                            <div class="action-icon bg-info bg-opacity-10 text-info me-3">
                                <i class="mdi mdi-format-list-bulleted mdi-36px"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="card-title mb-2 text-dark fw-semibold">Apontamentos</h5>
                                <p class="card-text text-muted small mb-0">Registrar apontamentos</p>
                            </div>
                            <i class="mdi mdi-chevron-right text-muted"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Pendências de Apontamento -->
        <div class="col-md-6 col-lg-4">
            <a href="{{ route('kit.pendencias') }}" class="text-decoration-none">
                <div class="card action-card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-start">
                            <div class="action-icon bg-danger bg-opacity-10 text-danger me-3">
                                <i class="mdi mdi-alert-circle mdi-36px"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="card-title mb-2 text-dark fw-semibold">Pendências</h5>
                                <p class="card-text text-muted small mb-0">Apontamentos pendentes de conclusão</p>
                            </div>
                            <i class="mdi mdi-chevron-right text-muted"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Editar Programação (condicional) -->
        @if ($kits->contains(function ($kit) {
            return \Carbon\Carbon::parse($kit->data_montagem)->isToday();
        }))
            <div class="col-md-6 col-lg-4">
                <a href="{{ route('kit.editar', $kits->firstWhere('data_montagem', \Carbon\Carbon::today()->toDateString())->id) }}" class="text-decoration-none">
                    <div class="card action-card border-0 shadow-sm h-100">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-start">
                                <div class="action-icon bg-warning bg-opacity-10 text-warning me-3">
                                    <i class="mdi mdi-pencil-box-outline mdi-36px"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h5 class="card-title mb-2 text-dark fw-semibold">Editar Programação</h5>
                                    <p class="card-text text-muted small mb-0">Modificar programação de hoje</p>
                                </div>
                                <i class="mdi mdi-chevron-right text-muted"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        @endif

        <!-- Relatório de Kits (admin) -->
        @auth
            @if(Auth::user()->tipo === 'admin')
                <div class="col-md-6 col-lg-4">
                    <a href="{{ route('kit.relatorio') }}" class="text-decoration-none">
                        <div class="card action-card border-0 shadow-sm h-100">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-start">
                                    <div class="action-icon bg-primary bg-opacity-10 text-primary me-3">
                                        <i class="mdi mdi-chart-bar mdi-36px"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h5 class="card-title mb-2 text-dark fw-semibold">Relatório</h5>
                                        <p class="card-text text-muted small mb-0">Análise e indicadores de montagem</p>
                                    </div>
                                    <i class="mdi mdi-chevron-right text-muted"></i>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            @endif
        @endauth

        <!-- Etiquetas -->
        <div class="col-md-6 col-lg-4">
            <a href="{{ route('kit.etiquetas') }}" class="text-decoration-none">
                <div class="card action-card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-start">
                            <div class="action-icon bg-success bg-opacity-10 text-success me-3">
                                <i class="mdi mdi-tag-multiple mdi-36px"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="card-title mb-2 text-dark fw-semibold">Etiquetas</h5>
                                <p class="card-text text-muted small mb-0">Imprimir etiquetas de identificação</p>
                            </div>
                            <i class="mdi mdi-chevron-right text-muted"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>

<style>
    .icon-wrapper {
        width: 60px; height: 60px;
        display: flex; align-items: center; justify-content: center;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(102,126,234,0.3);
    }
    .icon-wrapper i { color: #fff !important; }

    .action-card {
        transition: all 0.3s ease;
        border-radius: 0.5rem;
        cursor: pointer;
    }
    
    .action-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.12) !important;
    }

    .action-icon {
        width: 64px;
        height: 64px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        flex-shrink: 0;
    }

    .card-title {
        font-size: 1.1rem;
    }
</style>

@endsection