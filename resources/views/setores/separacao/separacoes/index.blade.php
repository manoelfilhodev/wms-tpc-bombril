@extends('layouts.app')

@section('content')

<div class="container-fluid px-4 py-3">
    @include('partials.breadcrumb-auto')
    
    <!-- Header com ícone -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
            <div class="icon-wrapper me-3">
                <i class="mdi mdi-clipboard-play-outline display-6 text-primary"></i>
            </div>
            <div>
                <h3 class="mb-1 fw-bold text-dark">Separações em Andamento</h3>
                <p class="text-muted mb-0 small">Acompanhe e retome as separações em progresso</p>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-primary bg-opacity-10 text-primary">
                {{ isset($separacoes) ? $separacoes->count() : 0 }} em andamento
            </span>
            <a href="{{ route('pedidos.index') }}" class="btn btn-outline-secondary btn-sm" data-bs-toggle="tooltip" title="Ver pedidos pendentes">
                <i class="mdi mdi-clipboard-text-outline"></i>
            </a>
        </div>
    </div>

    <!-- Card de Filtros -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Pedido</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="mdi mdi-pound text-muted"></i>
                        </span>
                        <input type="text" name="pedido" class="form-control border-start-0" placeholder="Ex: 12345" value="{{ request('pedido') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">FO</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="mdi mdi-factory text-muted"></i>
                        </span>
                        <input type="text" name="fo" class="form-control border-start-0" placeholder="Ex: FO-001" value="{{ request('fo') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Todos</option>
                        <option value="andamento" @selected(request('status')==='andamento')>Em andamento</option>
                        <option value="finalizado" @selected(request('status')==='finalizado')>Finalizado</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="mdi mdi-magnify me-1"></i> Filtrar
                    </button>
                    @if(request()->hasAny(['pedido','fo','status']))
                        <a href="{{ url()->current() }}" class="btn btn-outline-secondary" data-bs-toggle="tooltip" title="Limpar filtros">
                            <i class="mdi mdi-close"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    @if($separacoes->isEmpty())
        <!-- Empty state -->
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5 text-muted">
                <i class="mdi mdi-clipboard-play-outline display-4 d-block mb-3 opacity-25"></i>
                <p class="mb-0">Nenhum pedido em separação no momento</p>
                <small>Vá até pedidos pendentes para iniciar uma separação</small>
            </div>
        </div>
    @else
        <!-- Card da Tabela -->
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="px-4 py-3 text-muted small fw-semibold" style="width: 180px;">
                                    <i class="mdi mdi-cog-play-outline me-1"></i> Ação
                                </th>
                                <th class="px-4 py-3 text-muted small fw-semibold">
                                    <i class="mdi mdi-factory me-1"></i> FO
                                </th>
                                <th class="px-4 py-3 text-muted small fw-semibold">
                                    <i class="mdi mdi-pound me-1"></i> Pedido
                                </th>
                                <th class="px-4 py-3 text-muted small fw-semibold">
                                    <i class="mdi mdi-calendar me-1"></i> Data
                                </th>
                                <th class="px-4 py-3 text-muted small fw-semibold">
                                    <i class="mdi mdi-timer-sand me-1"></i> Status
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($separacoes as $pedido)
                                @php
                                    $primeiroNaoConferido = $pedido->itensSeparacao->where('conferido', false)->first();
                                    $fo = $pedido->itensSeparacao->first()->fo ?? '-';
                                @endphp
                                <tr class="border-bottom">
                                    <td class="px-4 py-3">
                                        @if($primeiroNaoConferido)
                                            <a href="{{ route('separacoes.form', $primeiroNaoConferido->id) }}"
                                               class="btn btn-sm btn-primary">
                                                <i class="mdi mdi-play-circle-outline me-1"></i> Separar agora
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="badge bg-light text-dark border" title="{{ $fo }}">
                                            {{ $fo }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="fw-semibold text-dark">#PED{{ $pedido->numero_pedido }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-muted">
                                        {{ $pedido->created_at ? $pedido->created_at->format('d/m/Y H:i') : '-' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($primeiroNaoConferido)
                                            <span class="badge bg-warning text-dark">Em andamento</span>
                                        @else
                                            <span class="badge bg-success">Finalizado</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            @if(method_exists($separacoes, 'links'))
                <div class="card-footer bg-white border-top">
                    {{ $separacoes->links() }}
                </div>
            @endif
        </div>
    @endif
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