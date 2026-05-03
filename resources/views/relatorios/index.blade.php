@extends('layouts.app')

@section('content')

<div class="container-fluid px-4 py-3">
    @include('partials.breadcrumb-auto')
    
    <!-- Header com ícone roxo -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
            <div class="icon-wrapper me-3">
                <i class="mdi mdi-chart-box-outline display-6 text-primary"></i>
            </div>
            <div>
                <h3 class="mb-1 fw-bold text-dark">Relatórios por Setor</h3>
                <p class="text-muted mb-0 small">Acesse relatórios detalhados de cada operação</p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="tooltip" title="Atualizar">
                <i class="mdi mdi-refresh"></i>
            </button>
        </div>
    </div>

    <!-- Cards de Relatórios -->
    <div class="row g-4">
        <!-- Relatório de Separações -->
        <div class="col-md-4">
            <a href="{{ route('relatorios.separacoes') }}" class="text-decoration-none">
                <div class="card report-card shadow-sm border-0 h-100">
                    <div class="card-body text-center p-4">
                        <div class="report-icon-wrapper mx-auto mb-3">
                            <i class="mdi mdi-package-variant-closed mdi-48px"></i>
                        </div>
                        <h5 class="fw-semibold text-dark mb-2">Relatório de Separações</h5>
                        <p class="text-muted small mb-0">Acompanhe produtividade e performance do picking</p>
                        <div class="mt-3">
                            <span class="badge bg-success bg-opacity-10 text-success">
                                <i class="mdi mdi-check-circle me-1"></i>Disponível
                            </span>
                        </div>
                    </div>
                    <div class="card-footer bg-light border-0 text-center py-2">
                        <small class="text-primary fw-semibold">
                            Acessar relatório <i class="mdi mdi-arrow-right"></i>
                        </small>
                    </div>
                </div>
            </a>
        </div>

        <!-- Relatório de Armazenagem -->
        <div class="col-md-4">
            <div class="card report-card report-card-disabled shadow-sm border-0 h-100">
                <div class="card-body text-center p-4">
                    <div class="report-icon-wrapper mx-auto mb-3">
                        <i class="mdi mdi-warehouse mdi-48px"></i>
                    </div>
                    <h5 class="fw-semibold text-dark mb-2">Relatório de Armazenagem</h5>
                    <p class="text-muted small mb-0">Ocupação, endereçamento e movimentações</p>
                    <div class="mt-3">
                        <span class="badge bg-secondary bg-opacity-10 text-secondary">
                            <i class="mdi mdi-clock-outline me-1"></i>Em breve
                        </span>
                    </div>
                </div>
                <div class="card-footer bg-light border-0 text-center py-2">
                    <small class="text-muted">
                        Em desenvolvimento
                    </small>
                </div>
            </div>
        </div>
        
        <!-- Relatório de Kits -->
        <div class="col-md-4">
            <div class="card report-card report-card-disabled shadow-sm border-0 h-100">
                <div class="card-body text-center p-4">
                    <div class="report-icon-wrapper mx-auto mb-3">
                        <i class="mdi mdi-chart-bar mdi-48px"></i>
                    </div>
                    <h5 class="fw-semibold text-dark mb-2">Relatório de Kits</h5>
                    <p class="text-muted small mb-0">Montagem, composição e rastreabilidade</p>
                    <div class="mt-3">
                        <span class="badge bg-secondary bg-opacity-10 text-secondary">
                            <i class="mdi mdi-clock-outline me-1"></i>Em breve
                        </span>
                    </div>
                </div>
                <div class="card-footer bg-light border-0 text-center py-2">
                    <small class="text-muted">
                        Em desenvolvimento
                    </small>
                </div>
            </div>
        </div>

        <!-- Adicione mais setores conforme necessário -->
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

    .report-card {
        border-radius: 0.75rem;
        transition: all 0.3s ease;
        cursor: pointer;
        overflow: hidden;
    }

    .report-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.12) !important;
    }

    .report-card-disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }

    .report-card-disabled:hover {
        transform: none;
        box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075) !important;
    }

    .report-icon-wrapper {
        width: 80px;
        height: 80px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #667eea15 0%, #764ba215 100%);
        border-radius: 50%;
        transition: all 0.3s ease;
    }

    .report-card:hover .report-icon-wrapper {
        background: linear-gradient(135deg, #667eea25 0%, #764ba225 100%);
        transform: scale(1.05);
    }

    .report-icon-wrapper i {
        color: #667eea;
    }

    .card-footer {
        border-radius: 0 0 0.75rem 0.75rem;
    }

    .badge {
        font-weight: 500;
        padding: 0.4em 0.8em;
        font-size: 0.75rem;
    }

    a:hover {
        text-decoration: none;
    }
</style>

@endsection