@extends('layouts.app')

@section('content')

<div class="container-fluid px-4 py-3">
    @include('partials.breadcrumb-auto')
    
    <!-- Header com ícone roxo -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
            <div class="icon-wrapper me-3">
                <i class="mdi mdi-map-marker display-6 text-primary"></i>
            </div>
            <div>
                <h3 class="mb-1 fw-bold text-dark">Gerenciamento de Posições</h3>
                <p class="text-muted mb-0 small">Cadastre e gerencie as posições do armazém</p>
            </div>
        </div>
    </div>

    <!-- Card de Criação -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="POST" action="{{ route('inventario.posicoes.salvar') }}" class="row g-3">
                @csrf
                <div class="col-md-8">
                    <label class="form-label small text-muted mb-1">Nova Posição</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="mdi mdi-map-marker-plus text-muted"></i>
                        </span>
                        <input name="codigo_posicao" class="form-control border-start-0" placeholder="Código da nova posição" required>
                    </div>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button class="btn btn-primary w-100">
                        <i class="mdi mdi-plus me-1"></i> Criar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Card de Filtros -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small text-muted mb-1">Código da Posição</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="mdi mdi-map-marker text-muted"></i>
                        </span>
                        <input type="text" name="codigo" class="form-control border-start-0" 
                               placeholder="Digite o código" value="{{ request('codigo') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-muted mb-1">Status</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="mdi mdi-check-circle text-muted"></i>
                        </span>
                        <select name="status" class="form-control border-start-0">
                            <option value="">Todos os status</option>
                            <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Ativa</option>
                            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inativa</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="mdi mdi-magnify me-1"></i> Buscar
                    </button>
                    @if(request()->hasAny(['codigo', 'status']))
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
                                <i class="mdi mdi-map-marker me-1"></i> Posição
                            </th>
                            <th class="px-4 py-3 text-muted small fw-semibold">
                                <i class="mdi mdi-check-circle me-1"></i> Status
                            </th>
                            <th class="px-4 py-3 text-muted small fw-semibold">
                                <i class="mdi mdi-office-building me-1"></i> Unidade
                            </th>
                            <th class="px-4 py-3 text-muted small fw-semibold">
                                <i class="mdi mdi-calendar me-1"></i> Criado em
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($posicoes as $p)
                            <tr class="border-bottom">
                                <td class="px-4 py-3">
                                    <span class="badge bg-light text-dark border">{{ $p->codigo_posicao }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="badge bg-{{ $p->status ? 'success' : 'secondary' }}">
                                        {{ $p->status ? 'Ativa' : 'Inativa' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-dark">{{ $p->unidade->nome ?? '---' }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-dark">{{ \Carbon\Carbon::parse($p->created_at)->format('d/m/Y H:i') }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="mdi mdi-map-marker-off display-4 d-block mb-3 opacity-25"></i>
                                        <p class="mb-0">Nenhuma posição encontrada</p>
                                        <small>Tente ajustar os filtros de busca</small>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
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

    .input-group-text { background-color: #f8f9fa; }
    .form-control:focus { border-color: #0d6efd; box-shadow: 0 0 0 0.2rem rgba(13,110,253,0.1); }
    .table tbody tr:hover { background-color: #f8f9fa; transition: background-color 0.2s ease; }
    .card { border-radius: 0.5rem; }
    .badge { font-weight: 500; padding: 0.35em 0.65em; }
</style>

@endsection