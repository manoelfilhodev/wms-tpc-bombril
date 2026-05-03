@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    @include('partials.breadcrumb-auto')

    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div class="d-flex align-items-center">
            <div class="icon-wrapper me-3">
                <i class="mdi mdi-clipboard-check-outline display-6 text-primary"></i>
            </div>
            <div>
                <h3 class="mb-1 fw-bold text-dark">Validação da Contagem</h3>
                <p class="text-muted mb-0 small">
                    Inventário <span class="badge bg-primary">#{{ $id_inventario }}</span>
                </p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                <i class="mdi mdi-printer me-1"></i> Imprimir
            </button>
            <button class="btn btn-outline-success btn-sm">
                <i class="mdi mdi-file-excel me-1"></i> Exportar
            </button>
        </div>
    </div>

    <!-- Cards de Resumo -->
    <div class="row g-3 mb-4">
        @php
            $total = count($itens);
            $comAjuste = collect($itens)->where('necessita_ajuste', true)->count();
            $semAjuste = $total - $comAjuste;
            $totalDiferenca = collect($itens)->sum('diferenca');
        @endphp

        <div class="col-md-3">
            <div class="card shadow-sm border-0 border-start border-4 border-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted small mb-1">Total de Itens</p>
                            <h4 class="mb-0 fw-bold">{{ $total }}</h4>
                        </div>
                        <div class="icon-stat bg-primary bg-opacity-10 text-primary">
                            <i class="mdi mdi-package-variant"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0 border-start border-4 border-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted small mb-1">Sem Divergência</p>
                            <h4 class="mb-0 fw-bold text-success">{{ $semAjuste }}</h4>
                        </div>
                        <div class="icon-stat bg-success bg-opacity-10 text-success">
                            <i class="mdi mdi-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0 border-start border-4 border-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted small mb-1">Com Divergência</p>
                            <h4 class="mb-0 fw-bold text-warning">{{ $comAjuste }}</h4>
                        </div>
                        <div class="icon-stat bg-warning bg-opacity-10 text-warning">
                            <i class="mdi mdi-alert-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0 border-start border-4 border-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted small mb-1">Diferença Total</p>
                            <h4 class="mb-0 fw-bold text-info">{{ $totalDiferenca > 0 ? '+' : '' }}{{ $totalDiferenca }}</h4>
                        </div>
                        <div class="icon-stat bg-info bg-opacity-10 text-info">
                            <i class="mdi mdi-delta"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros Rápidos -->
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body py-2">
            <div class="d-flex gap-2 align-items-center">
                <small class="text-muted me-2">Filtro rápido:</small>
                <button class="btn btn-sm btn-outline-secondary" onclick="filtrarTabela('todos')">
                    <i class="mdi mdi-all-inclusive me-1"></i> Todos
                </button>
                <button class="btn btn-sm btn-outline-warning" onclick="filtrarTabela('divergencia')">
                    <i class="mdi mdi-alert me-1"></i> Com Divergência
                </button>
                <button class="btn btn-sm btn-outline-success" onclick="filtrarTabela('ok')">
                    <i class="mdi mdi-check me-1"></i> Sem Divergência
                </button>
            </div>
        </div>
    </div>

    <!-- Tabela -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0" id="tabelaValidacao">
                    <thead class="bg-dark text-white">
                        <tr>
                            <th class="px-4 py-3 small fw-semibold">
                                <i class="mdi mdi-barcode me-1"></i> SKU
                            </th>
                            <th class="px-4 py-3 small fw-semibold">
                                <i class="mdi mdi-text me-1"></i> Descrição
                            </th>
                            <th class="px-4 py-3 small fw-semibold">
                                <i class="mdi mdi-map-marker me-1"></i> Posição
                            </th>
                            <th class="px-4 py-3 small fw-semibold text-center">
                                <i class="mdi mdi-database me-1"></i> Sistema
                            </th>
                            <th class="px-4 py-3 small fw-semibold text-center">
                                <i class="mdi mdi-counter me-1"></i> Físico
                            </th>
                            <th class="px-4 py-3 small fw-semibold text-center">
                                <i class="mdi mdi-delta me-1"></i> Diferença
                            </th>
                            <th class="px-4 py-3 small fw-semibold text-center">
                                <i class="mdi mdi-cog me-1"></i> Ajuste
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($itens as $item)
                            <tr class="border-bottom linha-item {{ $item->necessita_ajuste ? 'linha-divergencia' : 'linha-ok' }}">
                                <td class="px-4 py-3">
                                    <span class="badge bg-light text-dark border">{{ $item->sku }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-dark">{{ $item->descricao }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="badge bg-primary bg-opacity-10 text-primary">
                                        <i class="mdi mdi-map-marker-outline me-1"></i>{{ $item->posicao }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="fw-semibold text-dark">{{ number_format($item->quantidade_sistema, 0, ',', '.') }}</span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="fw-semibold text-dark">{{ number_format($item->quantidade_fisica, 0, ',', '.') }}</span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @php
                                        $diff = $item->diferenca;
                                        $diffClass = $diff > 0 ? 'text-success' : ($diff < 0 ? 'text-danger' : 'text-muted');
                                        $diffIcon = $diff > 0 ? 'mdi-arrow-up' : ($diff < 0 ? 'mdi-arrow-down' : 'mdi-minus');
                                    @endphp
                                    <span class="fw-bold {{ $diffClass }}">
                                        <i class="mdi {{ $diffIcon }} me-1"></i>
                                        {{ $diff > 0 ? '+' : '' }}{{ number_format($diff, 0, ',', '.') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($item->necessita_ajuste)
                                        <span class="badge bg-warning text-dark">
                                            <i class="mdi mdi-wrench me-1"></i>{{ $item->tipo_ajuste }}
                                        </span>
                                    @else
                                        <span class="badge bg-success">
                                            <i class="mdi mdi-check me-1"></i>OK
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="mdi mdi-clipboard-text-off-outline display-4 d-block mb-3 opacity-25"></i>
                                        <p class="mb-0">Nenhum item encontrado</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Ações -->
    <div class="d-flex gap-2 mt-4">
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
            <i class="mdi mdi-arrow-left me-1"></i> Voltar ao Dashboard
        </a>
        <button class="btn btn-primary">
            <i class="mdi mdi-check-all me-1"></i> Aprovar Ajustes
        </button>
    </div>
</div>

<style>
    .icon-wrapper {
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }
    .icon-wrapper i { color: white !important; }

    .icon-stat {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        font-size: 1.5rem;
    }

    .card { border-radius: 0.5rem; overflow: hidden; }
    
    .table tbody tr:hover { 
        background-color: #f8f9fa; 
        transition: background-color 0.2s ease; 
    }

    .linha-divergencia {
        background-color: #fff3cd20;
    }

    .linha-ok {
        background-color: #d1e7dd20;
    }

    .badge { font-weight: 500; padding: 0.35em 0.65em; }

    @media print {
        .btn, .card-body.py-2 { display: none !important; }
    }
</style>

<script>
    function filtrarTabela(tipo) {
        const linhas = document.querySelectorAll('.linha-item');
        
        linhas.forEach(linha => {
            if (tipo === 'todos') {
                linha.style.display = '';
            } else if (tipo === 'divergencia') {
                linha.style.display = linha.classList.contains('linha-divergencia') ? '' : 'none';
            } else if (tipo === 'ok') {
                linha.style.display = linha.classList.contains('linha-ok') ? '' : 'none';
            }
        });
    }
</script>

@endsection