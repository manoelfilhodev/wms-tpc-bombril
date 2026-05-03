@extends('layouts.app')

@section('content')

<div class="container-fluid px-4 py-3">
    @include('partials.breadcrumb-auto')
    
    <!-- Header com ícone -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
            <div class="icon-wrapper me-3">
                <i class="mdi mdi-clipboard-check-outline display-6 text-primary"></i>
            </div>
            <div>
                <h3 class="mb-1 fw-bold text-dark">Conferência de Recebimentos</h3>
                <p class="text-muted mb-0 small">Gerencie e confira os recebimentos pendentes</p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary btn-sm" onclick="location.reload()" data-bs-toggle="tooltip" title="Atualizar">
                <i class="mdi mdi-refresh"></i>
            </button>
            <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="tooltip" title="Filtros">
                <i class="mdi mdi-filter-variant"></i>
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="mdi mdi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Card com Estatísticas Rápidas -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-warning">
                    <i class="mdi mdi-clock-outline"></i>
                </div>
                <div class="stat-content">
                    <h6 class="stat-label">Pendentes</h6>
                    <h3 class="stat-value">{{ $recebimentos->count() }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-primary">
                    <i class="mdi mdi-truck-delivery"></i>
                </div>
                <div class="stat-content">
                    <h6 class="stat-label">Hoje</h6>
                    <h3 class="stat-value">{{ $recebimentos->where('data_recebimento', '>=', now()->startOfDay())->count() }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-success">
                    <i class="mdi mdi-check-all"></i>
                </div>
                <div class="stat-content">
                    <h6 class="stat-label">Conferidos Hoje</h6>
                    <h3 class="stat-value">0</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-info">
                    <i class="mdi mdi-chart-line"></i>
                </div>
                <div class="stat-content">
                    <h6 class="stat-label">Média Diária</h6>
                    <h3 class="stat-value">--</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Card Principal com Tabela -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-0 pt-4 pb-3">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="fw-semibold mb-0">
                    <i class="mdi mdi-format-list-bulleted text-primary me-2"></i>
                    Recebimentos Pendentes
                </h5>
                <div class="input-group" style="max-width: 300px;">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="mdi mdi-magnify text-muted"></i>
                    </span>
                    <input type="text" class="form-control border-start-0 bg-light" placeholder="Buscar NF, fornecedor..." id="searchInput">
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="tabelaRecebimentos">
                    <thead class="table-light">
                        <tr>
                            <th class="px-4 py-3">
                                <i class="mdi mdi-file-document-outline me-1"></i>NF
                            </th>
                            <th class="py-3">
                                <i class="mdi mdi-domain me-1"></i>Fornecedor
                            </th>
                            <th class="py-3">
                                <i class="mdi mdi-truck me-1"></i>Transportadora
                            </th>
                            <th class="py-3">
                                <i class="mdi mdi-calendar me-1"></i>Data
                            </th>
                            <th class="py-3 text-center">
                                <i class="mdi mdi-information-outline me-1"></i>Status
                            </th>
                            <th class="py-3 text-center">Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recebimentos as $rec)
                            <tr class="recebimento-row">
                                <td class="px-4 py-3">
                                    <span class="fw-semibold text-dark">{{ $rec->nota_fiscal }}</span>
                                </td>
                                <td class="py-3">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle me-2">
                                            {{ strtoupper(substr($rec->fornecedor, 0, 2)) }}
                                        </div>
                                        <span class="text-dark">{{ $rec->fornecedor }}</span>
                                    </div>
                                </td>
                                <td class="py-3">
                                    <span class="text-muted">{{ $rec->transportadora }}</span>
                                </td>
                                <td class="py-3">
                                    <span class="text-muted">
                                        <i class="mdi mdi-calendar-blank me-1"></i>
                                        {{ \Carbon\Carbon::parse($rec->data_recebimento)->format('d/m/Y') }}
                                    </span>
                                    <br>
                                    <small class="text-muted">
                                        {{ \Carbon\Carbon::parse($rec->data_recebimento)->format('H:i') }}
                                    </small>
                                </td>
                                <td class="py-3 text-center">
                                    <span class="badge-status badge-warning">
                                        <i class="mdi mdi-clock-outline me-1"></i>
                                        {{ ucfirst($rec->status) }}
                                    </span>
                                </td>
                                <td class="py-3 text-center">
                                    <a href="{{ route('setores.conferencia.itens', $rec->id) }}" 
                                       class="btn btn-primary btn-sm btn-action"
                                       data-bs-toggle="tooltip" 
                                       title="Iniciar conferência">
                                        <i class="mdi mdi-play-circle-outline me-1"></i>
                                        Iniciar Conferência
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="empty-state">
                                        <i class="mdi mdi-package-variant-closed mdi-48px text-muted mb-3"></i>
                                        <h6 class="text-muted">Nenhum recebimento pendente</h6>
                                        <p class="text-muted small mb-0">Todos os recebimentos foram conferidos</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($recebimentos->count() > 0)
        <div class="card-footer bg-light border-0 py-3">
            <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    Mostrando {{ $recebimentos->count() }} recebimento(s) pendente(s)
                </small>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline-secondary" disabled>
                        <i class="mdi mdi-chevron-left"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-secondary" disabled>
                        <i class="mdi mdi-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>
        @endif
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

    .card {
        border-radius: 0.75rem;
        transition: all 0.3s ease;
    }

    /* Stat Cards */
    .stat-card {
        background: #fff;
        border-radius: 0.75rem;
        padding: 1.25rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        border: 1px solid #f0f0f0;
    }

    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 16px rgba(0,0,0,0.12);
    }

    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: #fff;
    }

    .stat-content {
        flex: 1;
    }

    .stat-label {
        font-size: 0.75rem;
        color: #6c757d;
        margin-bottom: 0.25rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .stat-value {
        font-size: 1.75rem;
        font-weight: 700;
        color: #212529;
        margin: 0;
    }

    /* Table Styles */
    .table thead th {
        font-weight: 600;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #495057;
        border-bottom: 2px solid #dee2e6;
    }

    .table tbody tr {
        transition: all 0.2s ease;
    }

    .table tbody tr:hover {
        background-color: #f8f9fa;
        transform: scale(1.01);
    }

    .avatar-circle {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.75rem;
    }

    .badge-status {
        padding: 0.5rem 1rem;
        border-radius: 2rem;
        font-weight: 500;
        font-size: 0.75rem;
        display: inline-flex;
        align-items: center;
    }

    .badge-warning {
        background: #fff3cd;
        color: #856404;
        border: 1px solid #ffeaa7;
    }

    .btn-action {
        border-radius: 0.5rem;
        font-weight: 500;
        transition: all 0.3s ease;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
    }

    .btn-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102,126,234,0.4);
    }

    .empty-state {
        padding: 2rem;
    }

    .empty-state i {
        opacity: 0.3;
    }

    .input-group-text {
        border-radius: 0.5rem 0 0 0.5rem;
    }

    .input-group .form-control {
        border-radius: 0 0.5rem 0.5rem 0;
    }

    .input-group .form-control:focus {
        box-shadow: none;
        border-color: #667eea;
    }

    .btn {
        border-radius: 0.5rem;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-outline-secondary:hover {
        background: #6c757d;
        color: #fff;
    }
</style>

<script>
// Busca em tempo real
document.getElementById('searchInput').addEventListener('keyup', function() {
    const searchValue = this.value.toLowerCase();
    const rows = document.querySelectorAll('.recebimento-row');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchValue) ? '' : 'none';
    });
});

// Inicializar tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>

@endsection