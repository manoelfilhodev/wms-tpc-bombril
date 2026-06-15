@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    @include('partials.breadcrumb-auto')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
            <div class="icon-wrapper me-3">
                <i class="mdi mdi-account-group display-6 text-primary"></i>
            </div>
            <div>
                <h3 class="mb-1 fw-bold text-dark">Lista de Separadores</h3>
                <p class="text-muted mb-0 small">Gerencie separadores usados na distribuição operacional</p>
            </div>
        </div>
        <a href="{{ route('separadores.create') }}" class="btn btn-success">
            <i class="mdi mdi-account-plus-outline me-1"></i> Novo Separador
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="mdi mdi-check-circle-outline me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('separadores.index') }}" class="row g-3">
                <div class="col-md-9">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="mdi mdi-magnify text-muted"></i>
                        </span>
                        <input type="text" name="search" class="form-control"
                               placeholder="Buscar por nome, chapa, cargo ou turno..."
                               value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-fill">
                            <i class="mdi mdi-filter-outline me-1"></i> Filtrar
                        </button>
                        <a href="{{ route('separadores.index') }}" class="btn btn-outline-secondary" title="Limpar filtros">
                            <i class="mdi mdi-refresh"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="fw-semibold">Nome</th>
                            <th class="fw-semibold">Chapa</th>
                            <th class="fw-semibold">Cargo</th>
                            <th class="fw-semibold">Turno</th>
                            <th class="fw-semibold text-center" style="width: 120px;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($separadores as $separador)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm bg-light rounded-circle d-flex align-items-center justify-content-center me-2">
                                            <i class="mdi mdi-account text-primary"></i>
                                        </div>
                                        <span class="fw-medium">{{ $separador->nome }}</span>
                                    </div>
                                </td>
                                <td><span class="badge bg-light text-dark border">{{ $separador->chapa }}</span></td>
                                <td class="text-muted">{{ $separador->cargo ?: '-' }}</td>
                                <td class="text-muted">{{ $separador->turno ?: '-' }}</td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('separadores.edit', $separador) }}"
                                           class="btn btn-sm btn-outline-primary"
                                           title="Editar separador">
                                            <i class="mdi mdi-pencil-outline"></i>
                                        </a>
                                        <button type="button"
                                                class="btn btn-sm btn-outline-danger"
                                                title="Excluir separador"
                                                onclick="confirmarExclusaoSeparador({{ $separador->id }}, '{{ addslashes($separador->nome) }}')">
                                            <i class="mdi mdi-trash-can-outline"></i>
                                        </button>
                                    </div>
                                    <form id="form-delete-separador-{{ $separador->id }}"
                                          action="{{ route('separadores.destroy', $separador) }}"
                                          method="POST"
                                          class="d-none">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="mdi mdi-account-off-outline display-4 d-block mb-3 opacity-50"></i>
                                        <p class="mb-2 fw-medium">Nenhum separador encontrado</p>
                                        @if(request()->filled('search'))
                                            <a href="{{ route('separadores.index') }}" class="btn btn-sm btn-link">
                                                <i class="mdi mdi-filter-remove-outline me-1"></i>Limpar filtros
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $separadores->appends(request()->query())->links() }}
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
    .avatar-sm { width: 36px; height: 36px; }
    .input-group-text { background-color: #f8f9fa; border-right: 0; }
    .input-group .form-control { border-left: 0; }
    .form-control:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13,110,253,0.1);
    }
    .table tbody tr:hover { background-color: #f8f9fa; transition: background-color 0.2s ease; }
    .card { border-radius: 0.5rem; }
    .badge { font-weight: 500; padding: 0.35em 0.65em; }
</style>

<script>
function confirmarExclusaoSeparador(separadorId, separadorNome) {
    if (confirm(`Deseja realmente excluir o separador "${separadorNome}"?\n\nEsta ação não remove distribuições já registradas.`)) {
        document.getElementById('form-delete-separador-' + separadorId).submit();
    }
}
</script>
@endsection
