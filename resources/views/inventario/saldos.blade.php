@extends('layouts.app')

@section('content')

<div class="container-fluid px-4 py-3">
    @include('partials.breadcrumb-auto')
    
    <!-- Header com ícone roxo -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
            <div class="icon-wrapper me-3">
                <i class="mdi mdi-warehouse display-6 text-primary"></i>
            </div>
            <div>
                <h3 class="mb-1 fw-bold text-dark">Saldo de Estoque</h3>
                <p class="text-muted mb-0 small">Consulte o saldo atual de SKUs por posição</p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="tooltip" title="Exportar">
                <i class="mdi mdi-download"></i> Exportar
            </button>
            <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="tooltip" title="Atualizar">
                <i class="mdi mdi-refresh"></i>
            </button>
        </div>
    </div>

    <!-- Card de Filtros -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">SKU</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="mdi mdi-barcode-scan text-muted"></i>
                        </span>
                        <input type="text" name="sku" class="form-control border-start-0" 
                               placeholder="Digite o SKU" value="{{ request('sku') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Descrição</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="mdi mdi-text-search text-muted"></i>
                        </span>
                        <input type="text" name="descricao" class="form-control border-start-0" 
                               placeholder="Buscar descrição" value="{{ request('descricao') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Posição</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="mdi mdi-map-marker text-muted"></i>
                        </span>
                        <input type="text" name="posicao" class="form-control border-start-0" 
                               placeholder="Ex: A-01-01" value="{{ request('posicao') }}">
                    </div>
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="mdi mdi-magnify me-1"></i> Buscar
                    </button>
                    @if(request()->hasAny(['sku', 'descricao', 'posicao']))
                        <a href="{{ url()->current() }}" class="btn btn-outline-secondary" data-bs-toggle="tooltip" title="Limpar filtros">
                            <i class="mdi mdi-close"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Card da Tabela -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="px-4 py-3 text-muted small fw-semibold">
                                <i class="mdi mdi-barcode me-1"></i> SKU
                            </th>
                            <th class="px-4 py-3 text-muted small fw-semibold">
                                <i class="mdi mdi-package-variant me-1"></i> Descrição
                            </th>
                            <th class="px-4 py-3 text-muted small fw-semibold">
                                <i class="mdi mdi-map-marker me-1"></i> Posição
                            </th>
                            <th class="px-4 py-3 text-muted small fw-semibold text-end">
                                <i class="mdi mdi-counter me-1"></i> Quantidade
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($saldos as $s)
                            <tr class="border-bottom">
                                <td class="px-4 py-3">
                                    <span class="badge bg-light text-dark border">{{ $s->sku }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-dark">{{ $s->descricao }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="badge bg-primary bg-opacity-10 text-primary">
                                        <i class="mdi mdi-map-marker-outline me-1"></i>{{ $s->codigo_posicao }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-end">
                                    <span class="fw-semibold text-dark">{{ number_format($s->quantidade, 0, ',', '.') }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="mdi mdi-package-variant-closed display-4 d-block mb-3 opacity-25"></i>
                                        <p class="mb-0">Nenhum saldo encontrado</p>
                                        <small>Tente ajustar os filtros de busca</small>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        @if(method_exists($saldos, 'links'))
            <div class="card-footer bg-white border-top">
                {{ $saldos->links() }}
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

    .input-group-text { background-color: #f8f9fa; }
    .form-control:focus { border-color: #0d6efd; box-shadow: 0 0 0 0.2rem rgba(13,110,253,0.1); }
    .table tbody tr:hover { background-color: #f8f9fa; transition: background-color 0.2s ease; }
    .card { border-radius: 0.5rem; }
    .badge { font-weight: 500; padding: 0.35em 0.65em; }
</style>

@endsection