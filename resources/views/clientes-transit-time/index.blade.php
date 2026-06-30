@extends('layouts.app')

@section('title', 'Transit Time por Cliente')

@section('content')
<div class="container-fluid px-4 py-3 transit-page">
    @include('partials.breadcrumb-auto')

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div class="d-flex align-items-center">
            <div class="icon-wrapper me-3">
                <i class="mdi mdi-map-clock-outline display-6"></i>
            </div>
            <div>
                <h3 class="mb-1 fw-bold text-dark">Transit Time por Cliente</h3>
                <p class="text-muted mb-0 small">WMS > Parâmetros Logísticos > Transit Time por Cliente</p>
            </div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('clientes-transit-time.import.form') }}" class="btn btn-outline-primary">
                <i class="mdi mdi-file-upload-outline me-1"></i> Importar
            </a>
            <a href="{{ route('clientes-transit-time.create') }}" class="btn btn-success">
                <i class="mdi mdi-plus me-1"></i> Novo Cliente
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="mdi mdi-check-circle-outline me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="mdi mdi-alert-circle-outline me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('clientes-transit-time.index') }}" class="row g-3">
                <div class="col-lg-8">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="mdi mdi-magnify text-muted"></i>
                        </span>
                        <input type="text" name="search" class="form-control"
                               placeholder="Buscar por código, nome, zona, UF ou cidade"
                               value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-lg-2">
                    <select name="status" class="form-select">
                        <option value="">Todos</option>
                        <option value="ativo" @selected(request('status') === 'ativo')>Ativos</option>
                        <option value="inativo" @selected(request('status') === 'inativo')>Inativos</option>
                    </select>
                </div>
                <div class="col-lg-2">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-fill">
                            <i class="mdi mdi-filter-outline me-1"></i> Filtrar
                        </button>
                        <a href="{{ route('clientes-transit-time.index') }}" class="btn btn-outline-secondary" title="Limpar filtros">
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
                            <th>Código</th>
                            <th>Cliente</th>
                            <th>Zona partida</th>
                            <th>UF/Cidade</th>
                            <th>Zona transporte</th>
                            <th class="text-center">Fechada</th>
                            <th class="text-center">Fracionada</th>
                            <th class="text-center">Status</th>
                            <th class="text-center" style="width: 130px;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($clientes as $cliente)
                            <tr>
                                <td><span class="badge bg-light text-dark border">{{ $cliente->codigo_cliente }}</span></td>
                                <td class="fw-semibold">{{ $cliente->nome_cliente ?: '-' }}</td>
                                <td class="text-muted">{{ $cliente->zona_partida ?: '-' }}</td>
                                <td class="text-muted">{{ $cliente->uf ?: '--' }}{{ $cliente->cidade ? ' / ' . $cliente->cidade : '' }}</td>
                                <td class="text-muted">{{ $cliente->zona_transporte ?: '-' }}</td>
                                <td class="text-center">{{ $cliente->transit_time_fechada_dias }} dia(s)</td>
                                <td class="text-center">{{ $cliente->transit_time_fracionada_dias }} dia(s)</td>
                                <td class="text-center">
                                    @if($cliente->ativo)
                                        <span class="badge bg-success">Ativo</span>
                                    @else
                                        <span class="badge bg-secondary">Inativo</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('clientes-transit-time.edit', $cliente) }}"
                                           class="btn btn-sm btn-outline-primary" title="Editar">
                                            <i class="mdi mdi-pencil-outline"></i>
                                        </a>
                                        <form action="{{ route('clientes-transit-time.toggle', $cliente) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                    class="btn btn-sm {{ $cliente->ativo ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                                    title="{{ $cliente->ativo ? 'Inativar' : 'Reativar' }}"
                                                    onclick="return confirm('Confirmar alteração de status deste cliente?')">
                                                <i class="mdi {{ $cliente->ativo ? 'mdi-toggle-switch-off-outline' : 'mdi-toggle-switch-outline' }}"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="mdi mdi-database-search-outline display-4 d-block mb-3 opacity-50"></i>
                                        <p class="mb-2 fw-medium">Nenhum cliente encontrado</p>
                                        @if(request()->filled('search') || request()->filled('status'))
                                            <a href="{{ route('clientes-transit-time.index') }}" class="btn btn-sm btn-link">
                                                <i class="mdi mdi-filter-remove-outline me-1"></i> Limpar filtros
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
                {{ $clientes->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>

@include('clientes-transit-time._style')
@endsection
