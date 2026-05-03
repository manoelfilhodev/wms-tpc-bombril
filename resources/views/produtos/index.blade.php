@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    @include('partials.breadcrumb-auto')
    
    <!-- Header com ícone roxo -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
            <div class="icon-wrapper me-3">
                <i class="mdi mdi-package-variant display-6 text-primary"></i>
            </div>
            <div>
                <h3 class="mb-1 fw-bold text-dark">Produtos</h3>
                <p class="text-muted mb-0 small">Gerencie o cadastro de produtos</p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('produtos.create') }}" class="btn btn-primary btn-sm">
                <i class="mdi mdi-plus me-1"></i> Novo Produto
            </a>
        </div>
    </div>

    {{-- Mensagem de sucesso --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="mdi mdi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Card de Filtros -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('produtos.index') }}" class="row g-3">
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">SKU</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="mdi mdi-barcode-scan text-muted"></i>
                        </span>
                        <input type="text" name="sku" value="{{ request('sku') }}" class="form-control border-start-0" placeholder="Digite o SKU">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">EAN</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="mdi mdi-barcode text-muted"></i>
                        </span>
                        <input type="text" name="ean" value="{{ request('ean') }}" class="form-control border-start-0" placeholder="Digite o EAN">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-muted mb-1">Descrição</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="mdi mdi-text-search text-muted"></i>
                        </span>
                        <input type="text" name="descricao" value="{{ request('descricao') }}" class="form-control border-start-0" placeholder="Buscar descrição">
                    </div>
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="mdi mdi-magnify me-1"></i> Buscar
                    </button>
                    @if(request()->hasAny(['sku', 'ean', 'descricao']))
                        <a href="{{ route('produtos.index') }}" class="btn btn-outline-secondary" data-bs-toggle="tooltip" title="Limpar filtros">
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
                                <i class="mdi mdi-barcode-scan me-1"></i> EAN
                            </th>
                            <th class="px-4 py-3 text-muted small fw-semibold">
                                <i class="mdi mdi-package-variant me-1"></i> Descrição
                            </th>
                            <th class="px-4 py-3 text-muted small fw-semibold text-end">
                                <i class="mdi mdi-counter me-1"></i> Estoque
                            </th>
                            <th class="px-4 py-3 text-muted small fw-semibold text-end">
                                <i class="mdi mdi-view-grid me-1"></i> Lastro
                            </th>
                            <th class="px-4 py-3 text-muted small fw-semibold text-end">
                                <i class="mdi mdi-layers me-1"></i> Camada
                            </th>
                            <th class="px-4 py-3 text-muted small fw-semibold text-end">
                                <i class="mdi mdi-package-variant-closed me-1"></i> Paletização
                            </th>
                            <th class="px-4 py-3 text-muted small fw-semibold text-center">
                                <i class="mdi mdi-cog me-1"></i> Ações
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($produtos as $produto)
                            <tr class="border-bottom">
                                <td class="px-4 py-3">
                                    <span class="badge bg-light text-dark border">{{ mb_strtoupper($produto->sku) }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-dark">{{ $produto->ean }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-dark">{{ $produto->descricao }}</span>
                                </td>
                                <td class="px-4 py-3 text-end">
                                    <span class="fw-semibold text-dark">{{ $produto->quantidade_estoque }}</span>
                                </td>
                                <td class="px-4 py-3 text-end">
                                    <span class="text-dark">{{ $produto->lastro }}</span>
                                </td>
                                <td class="px-4 py-3 text-end">
                                    <span class="text-dark">{{ $produto->camada }}</span>
                                </td>
                                <td class="px-4 py-3 text-end">
                                    <span class="text-dark">{{ $produto->paletizacao }}</span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <a href="{{ route('produtos.edit', $produto->id) }}" class="btn btn-sm btn-warning">
                                        <i class="mdi mdi-pencil"></i>
                                    </a>
                                    <form action="{{ route('produtos.destroy', $produto->id) }}" method="POST" style="display:inline-block">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Excluir este produto?')">
                                            <i class="mdi mdi-delete"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="mdi mdi-package-variant-closed display-4 d-block mb-3 opacity-25"></i>
                                        <p class="mb-0">Nenhum produto encontrado</p>
                                        <small>Tente ajustar os filtros de busca</small>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        @if ($produtos instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="card-footer bg-white border-top">
                {{ $produtos->links() }}
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