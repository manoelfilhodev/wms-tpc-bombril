@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    @include('partials.breadcrumb-auto')
    
    <!-- Header com ícone roxo -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
            <div class="icon-wrapper me-3">
                <i class="mdi mdi-account-group display-6 text-primary"></i>
            </div>
            <div>
                <h3 class="mb-1 fw-bold text-dark">Lista de Usuários</h3>
                <p class="text-muted mb-0 small">Gerencie contas e permissões do sistema</p>
            </div>
        </div>
        <a href="{{ route('usuarios.create') }}" class="btn btn-success">
            <i class="mdi mdi-account-plus-outline me-1"></i> Novo Usuário
        </a>
    </div>

    <!-- Alertas -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="mdi mdi-check-circle-outline me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    @endif

    <!-- Card de Filtros -->
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('usuarios.index') }}" class="row g-3">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="mdi mdi-magnify text-muted"></i>
                        </span>
                        <input type="text" name="search" class="form-control" 
                               placeholder="Buscar por nome ou login..." 
                               value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">Todos Status</option>
                        <option value="ativo" {{ request('status') == 'ativo' ? 'selected' : '' }}>Ativo</option>
                        <option value="inativo" {{ request('status') == 'inativo' ? 'selected' : '' }}>Inativo</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="tipo" class="form-select">
                        <option value="">Todos Níveis</option>
                        <option value="admin" {{ request('tipo') == 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="supervisor" {{ request('tipo') == 'supervisor' ? 'selected' : '' }}>Supervisor</option>
                        <option value="operador" {{ request('tipo') == 'operador' ? 'selected' : '' }}>Operador</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-fill">
                            <i class="mdi mdi-filter-outline me-1"></i> Filtrar
                        </button>
                        <a href="{{ route('usuarios.index') }}" class="btn btn-outline-secondary" title="Limpar filtros">
                            <i class="mdi mdi-refresh"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Card da Tabela -->
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="fw-semibold">Nome</th>
                            <th class="fw-semibold">Login</th>
                            <th class="fw-semibold">Unidade</th>
                            <th class="fw-semibold text-center">Status</th>
                            <th class="fw-semibold">Nível</th>
                            <th class="fw-semibold text-center" style="width: 120px;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($usuarios as $usuario)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm bg-light rounded-circle d-flex align-items-center justify-content-center me-2">
                                            <i class="mdi mdi-account text-primary"></i>
                                        </div>
                                        <span class="fw-medium">{{ $usuario->nome }}</span>
                                    </div>
                                </td>
                                <td class="text-muted">{{ mb_strtolower($usuario->email) }}</td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        {{ $usuario->unidade_id ?? 'N/A' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if($usuario->status == 'ativo')
                                        <span class="badge bg-success">
                                            <i class="mdi mdi-check-circle me-1"></i>Ativo
                                        </span>
                                    @else
                                        <span class="badge bg-danger">
                                            <i class="mdi mdi-close-circle me-1"></i>Inativo
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-primary">
                                        {{ ucfirst($usuario->tipo) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('usuarios.edit', $usuario->id_user) }}" 
                                           class="btn btn-sm btn-outline-primary" 
                                           title="Editar usuário">
                                            <i class="mdi mdi-pencil-outline"></i>
                                        </a>
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-danger" 
                                                title="Excluir usuário"
                                                onclick="confirmarExclusao({{ $usuario->id_user }}, '{{ addslashes($usuario->nome) }}')">
                                            <i class="mdi mdi-trash-can-outline"></i>
                                        </button>
                                    </div>
                                    <form id="form-delete-{{ $usuario->id_user }}" 
                                          action="{{ route('usuarios.destroy', $usuario->id_user) }}" 
                                          method="POST" 
                                          class="d-none">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="mdi mdi-account-off-outline display-4 d-block mb-3 opacity-50"></i>
                                        <p class="mb-2 fw-medium">Nenhum usuário encontrado</p>
                                        @if(request()->hasAny(['search', 'status', 'tipo']))
                                            <a href="{{ route('usuarios.index') }}" class="btn btn-sm btn-link">
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

            <!-- Paginação -->
            @if(method_exists($usuarios, 'links'))
                <div class="mt-3">
                    {{ $usuarios->appends(request()->query())->links() }}
                </div>
            @endif
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

    .avatar-sm {
        width: 36px;
        height: 36px;
    }

    .input-group-text { 
        background-color: #f8f9fa; 
        border-right: 0;
    }
    
    .input-group .form-control {
        border-left: 0;
    }
    
    .input-group .form-control:focus {
        border-color: #ced4da;
        box-shadow: none;
    }
    
    .input-group:focus-within .input-group-text {
        border-color: #0d6efd;
    }
    
    .input-group:focus-within .form-control {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13,110,253,0.1);
    }

    .form-control:focus, .form-select:focus { 
        border-color: #0d6efd; 
        box-shadow: 0 0 0 0.2rem rgba(13,110,253,0.1); 
    }
    
    .table tbody tr:hover { 
        background-color: #f8f9fa; 
        transition: background-color 0.2s ease; 
    }
    
    .card { 
        border-radius: 0.5rem; 
    }
    
    .badge { 
        font-weight: 500; 
        padding: 0.35em 0.65em; 
    }

    .btn-group .btn {
        padding: 0.25rem 0.5rem;
    }

    .table > :not(caption) > * > * {
        padding: 0.75rem 0.5rem;
    }
</style>

<script>
function confirmarExclusao(userId, userName) {
    if (confirm(`Deseja realmente excluir o usuário "${userName}"?\n\nEsta ação não pode ser desfeita.`)) {
        document.getElementById('form-delete-' + userId).submit();
    }
}
</script>

@endsection