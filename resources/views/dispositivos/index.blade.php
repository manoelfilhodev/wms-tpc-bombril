@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    @include('partials.breadcrumb-auto')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
            <div class="icon-wrapper me-3">
                <i class="mdi mdi-monitor-cellphone display-6 text-primary"></i>
            </div>
            <div>
                <h3 class="mb-1 fw-bold text-dark">Dispositivos Autorizados</h3>
                <p class="text-muted mb-0 small">Controle de acesso operacional por dispositivo</p>
            </div>
        </div>
        <a href="{{ route('dispositivos.create') }}" class="btn btn-success">
            <i class="mdi mdi-plus-circle-outline me-1"></i> Novo Dispositivo
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
            <form method="GET" action="{{ route('dispositivos.index') }}" class="row g-3">
                <div class="col-md-5">
                    <input type="text" name="search" class="form-control" placeholder="Buscar por nome, device ID ou usuario" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="tipo" class="form-select">
                        <option value="">Todos tipos</option>
                        <option value="web" {{ request('tipo') === 'web' ? 'selected' : '' }}>WEB</option>
                        <option value="app" {{ request('tipo') === 'app' ? 'selected' : '' }}>APP</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="ativo" class="form-select">
                        <option value="">Todos status</option>
                        <option value="1" {{ request('ativo') === '1' ? 'selected' : '' }}>Ativo</option>
                        <option value="0" {{ request('ativo') === '0' ? 'selected' : '' }}>Inativo</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="mdi mdi-filter-outline me-1"></i> Filtrar
                    </button>
                    <a href="{{ route('dispositivos.index') }}" class="btn btn-outline-secondary" title="Limpar filtros">
                        <i class="mdi mdi-refresh"></i>
                    </a>
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
                            <th>Dispositivo</th>
                            <th>Device ID</th>
                            <th>Tipo</th>
                            <th>Usuario</th>
                            <th>Perfil</th>
                            <th>Status</th>
                            <th>Ultimo acesso</th>
                            <th class="text-center" style="width: 140px;">Acoes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dispositivos as $dispositivo)
                            <tr>
                                <td class="fw-semibold">{{ $dispositivo->nome_dispositivo }}</td>
                                <td><code>{{ $dispositivo->device_id }}</code></td>
                                <td><span class="badge bg-primary">{{ strtoupper($dispositivo->tipo) }}</span></td>
                                <td>{{ $dispositivo->usuario_nome ?? 'Todos operacionais' }}</td>
                                <td>{{ $dispositivo->perfil_permitido ?: 'Qualquer operacional' }}</td>
                                <td>
                                    <span class="badge {{ $dispositivo->ativo ? 'bg-success' : 'bg-danger' }}">
                                        {{ $dispositivo->ativo ? 'Ativo' : 'Inativo' }}
                                    </span>
                                </td>
                                <td>{{ $dispositivo->ultimo_acesso_em ? \Illuminate\Support\Carbon::parse($dispositivo->ultimo_acesso_em)->format('d/m/Y H:i') : '-' }}</td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <a href="{{ route('dispositivos.edit', $dispositivo->id) }}" class="btn btn-sm btn-outline-primary" title="Editar">
                                            <i class="mdi mdi-pencil-outline"></i>
                                        </a>
                                        <form action="{{ route('dispositivos.toggle', $dispositivo->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-outline-secondary" title="Ativar/desativar">
                                                <i class="mdi mdi-power"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="mdi mdi-monitor-off display-4 d-block mb-3 opacity-50"></i>
                                    Nenhum dispositivo autorizado encontrado
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $dispositivos->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>

<style>
    .icon-wrapper {
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #111827;
        border-radius: 8px;
    }
    .icon-wrapper i { color: #fff !important; }
</style>
@endsection
