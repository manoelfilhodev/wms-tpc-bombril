@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    @include('partials.breadcrumb-auto')
    
    <!-- Header com ícone roxo -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
            <div class="icon-wrapper me-3">
                <i class="mdi mdi-history display-6 text-primary"></i>
            </div>
            <div>
                <h3 class="mb-1 fw-bold text-dark">Logs de Usuários</h3>
                <p class="text-muted mb-0 small">Histórico completo de ações realizadas no sistema</p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('logs.export.excel', request()->query()) }}" class="btn btn-success btn-sm shadow-sm">
                <i class="mdi mdi-file-excel me-1"></i> Excel
            </a>
            <a href="{{ route('logs.export.pdf', request()->query()) }}" class="btn btn-danger btn-sm shadow-sm">
                <i class="mdi mdi-file-pdf me-1"></i> PDF
            </a>
        </div>
    </div>

    <!-- Card de Filtros -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="mb-0 fw-semibold text-dark">
                <i class="mdi mdi-filter-variant me-2 text-primary"></i>Filtros de Pesquisa
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Usuário</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="mdi mdi-account-outline text-muted"></i>
                        </span>
                        <input type="text" name="usuario" class="form-control border-start-0" 
                               placeholder="Nome do usuário" value="{{ request('usuario') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Ação</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="mdi mdi-lightning-bolt-outline text-muted"></i>
                        </span>
                        <input type="text" name="acao" class="form-control border-start-0" 
                               placeholder="Ação realizada" value="{{ request('acao') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Data</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="mdi mdi-calendar-outline text-muted"></i>
                        </span>
                        <input type="date" name="data" class="form-control border-start-0" 
                               value="{{ request('data') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Unidade</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="mdi mdi-office-building-outline text-muted"></i>
                        </span>
                        <select name="unidade" class="form-select border-start-0">
                            <option value="">Todas as unidades</option>
                            @foreach ($unidades as $unidade)
                                <option value="{{ $unidade->nome }}" {{ request('unidade') == $unidade->nome ? 'selected' : '' }}>
                                    {{ $unidade->nome }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="mdi mdi-magnify me-1"></i> Buscar Logs
                    </button>
                    <a href="{{ route('logs.index') }}" class="btn btn-outline-secondary px-4 ms-2">
                        <i class="mdi mdi-refresh me-1"></i> Limpar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Card da Tabela -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="mb-0 fw-semibold text-dark">
                <i class="mdi mdi-table me-2 text-primary"></i>Registros Encontrados
                <span class="badge bg-primary ms-2">{{ $logs->total() }}</span>
            </h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="border-0 text-muted small fw-semibold">
                                <i class="mdi mdi-account me-1"></i>Usuário
                            </th>
                            <th class="border-0 text-muted small fw-semibold">
                                <i class="mdi mdi-office-building me-1"></i>Unidade
                            </th>
                            <th class="border-0 text-muted small fw-semibold">
                                <i class="mdi mdi-lightning-bolt me-1"></i>Ação
                            </th>
                            <th class="border-0 text-muted small fw-semibold">
                                <i class="mdi mdi-information me-1"></i>Dados
                            </th>
                            <th class="border-0 text-muted small fw-semibold">
                                <i class="mdi mdi-ip me-1"></i>IP
                            </th>
                            <th class="border-0 text-muted small fw-semibold">
                                <i class="mdi mdi-web me-1"></i>Navegador
                            </th>
                            <th class="border-0 text-muted small fw-semibold">
                                <i class="mdi mdi-clock-outline me-1"></i>Data/Hora
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $log)
                            <tr class="log-row">
                                <td class="py-3">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle me-2">
                                            {{ strtoupper(substr($log->usuario->nome ?? 'U', 0, 1)) }}
                                        </div>
                                        <span class="fw-medium text-dark">{{ mb_strtoupper($log->usuario->nome ?? '---') }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">{{ $log->unidade->nome ?? '---' }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary">{{ mb_strtoupper($log->acao) }}</span>
                                </td>
                                <td style="max-width: 300px;">
                                    <small class="text-muted text-truncate d-block">{{ $log->dados }}</small>
                                </td>
                                <td>
                                    <code class="small">{{ $log->ip_address }}</code>
                                </td>
                                <td style="max-width: 200px;">
                                    <small class="text-muted text-truncate d-block">{{ $log->navegador }}</small>
                                </td>
                                <td>
                                    <small class="text-muted">{{ \Carbon\Carbon::parse($log->created_at)->format('d/m/Y H:i') }}</small>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <i class="mdi mdi-alert-circle-outline display-4 text-muted d-block mb-2"></i>
                                    <span class="text-muted">Nenhum log encontrado com os filtros aplicados.</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($logs->hasPages())
        <div class="card-footer bg-white border-top">
            <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    Mostrando {{ $logs->firstItem() }} a {{ $logs->lastItem() }} de {{ $logs->total() }} registros
                </small>
                {{ $logs->links() }}
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

    .avatar-circle {
        width: 32px; height: 32px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        color: white; font-weight: 600; font-size: 12px;
    }

    .input-group-text { 
        background-color: #f8f9fa; 
        border-right: 0;
    }
    
    .form-control:focus, .form-select:focus { 
        border-color: #667eea; 
        box-shadow: 0 0 0 0.2rem rgba(102,126,234,0.15); 
    }
    
    .log-row:hover { 
        background-color: #f8f9fa; 
        transition: background-color 0.2s ease; 
    }
    
    .card { border-radius: 0.5rem; }
    
    .badge { 
        font-weight: 500; 
        padding: 0.35em 0.65em; 
        font-size: 0.75rem;
    }

    .bg-primary-subtle {
        background-color: rgba(102,126,234,0.1) !important;
    }

    .text-primary {
        color: #667eea !important;
    }

    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #5568d3 0%, #65408b 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(102,126,234,0.3);
    }

    code {
        background-color: #f8f9fa;
        padding: 2px 6px;
        border-radius: 4px;
        color: #e83e8c;
    }
</style>

@endsection