@extends('layouts.app')

@section('content')

<div class="container-fluid px-4 py-3">
    @include('partials.breadcrumb-auto')
    
    <!-- Header com ícone -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
            <div class="icon-wrapper me-3">
                <i class="mdi mdi-clipboard-list display-6 text-primary"></i>
            </div>
            <div>
                <h3 class="mb-1 fw-bold text-dark">Central de Separação</h3>
                <p class="text-muted mb-0 small">Inicie, acompanhe e analise as separações</p>
            </div>
        </div>
        <div class="d-flex gap-2">
            @auth
                @if (Auth::user()->tipo === 'admin')
                    <a href="{{ route('pedidos.create') }}" class="btn btn-primary btn-sm" data-bs-toggle="tooltip" title="Inserir novo pedido">
                        <i class="mdi mdi-playlist-plus me-1"></i> Novo Pedido
                    </a>
                @endif
            @endauth
            <a href="{{ route('relatorios.separacoes') }}" class="btn btn-outline-secondary btn-sm" data-bs-toggle="tooltip" title="Relatórios de separação">
                <i class="mdi mdi-chart-bar"></i>
            </a>
        </div>
    </div>

    <!-- Card de Ações Rápidas (cards clicáveis) -->
    <div class="row g-3 mb-4">
        <!-- Pedidos Pendentes -->
        <div class="col-12 col-sm-6 col-lg-4">
            <a href="{{ route('pedidos.index') }}" class="text-decoration-none">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="me-3 position-relative">
                            <div class="icon-wrapper sm">
                                <i class="mdi mdi-play-circle-outline display-6"></i>
                            </div>
                            @if (!empty($pedidosPendentes) && $pedidosPendentes > 0)
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                    {{ $pedidosPendentes }}
                                </span>
                            @endif
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1 text-dark fw-semibold">Pedidos Pendentes</h6>
                            <small class="text-muted">Inicie a separação dos pedidos aguardando</small>
                        </div>
                        <i class="mdi mdi-chevron-right text-muted"></i>
                    </div>
                </div>
            </a>
        </div>

        <!-- Separações em Andamento -->
        <div class="col-12 col-sm-6 col-lg-4">
            <a href="{{ route('separacoes.index') }}" class="text-decoration-none">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="me-3">
                            <div class="icon-wrapper sm gradient-info">
                                <i class="mdi mdi-format-list-bulleted display-6"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1 text-dark fw-semibold">Separações em Andamento</h6>
                            <small class="text-muted">Visualize e continue atividades abertas</small>
                        </div>
                        <i class="mdi mdi-chevron-right text-muted"></i>
                    </div>
                </div>
            </a>
        </div>

        <!-- Relatório de Separações -->
        <div class="col-12 col-sm-6 col-lg-4">
            <a href="{{ route('relatorios.separacoes') }}" class="text-decoration-none">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="me-3">
                            <div class="icon-wrapper sm gradient-primary">
                                <i class="mdi mdi-chart-bar display-6"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1 text-dark fw-semibold">Relatório de Separações</h6>
                            <small class="text-muted">KPIs e produtividade da operação</small>
                        </div>
                        <i class="mdi mdi-chevron-right text-muted"></i>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Card de Filtros (placeholder) -->
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
                        <option value="pendente" @selected(request('status')==='pendente')>Pendente</option>
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

    <!-- Card da Lista (ex.: últimos pedidos pendentes ou em andamento) -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="px-4 py-3 text-muted small fw-semibold"><i class="mdi mdi-pound me-1"></i> Pedido</th>
                            <th class="px-4 py-3 text-muted small fw-semibold"><i class="mdi mdi-factory me-1"></i> FO</th>
                            <th class="px-4 py-3 text-muted small fw-semibold"><i class="mdi mdi-calendar me-1"></i> Data</th>
                            <th class="px-4 py-3 text-muted small fw-semibold"><i class="mdi mdi-timer-sand me-1"></i> Status</th>
                            <th class="px-4 py-3 text-muted small fw-semibold text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pedidos ?? [] as $pedido)
                            <tr class="border-bottom">
                                <td class="px-4 py-3">
                                    <span class="fw-semibold text-dark">#PED{{ $pedido->numero_pedido }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="badge bg-light text-dark border">{{ $pedido->itens->first()->fo ?? 'N/D' }}</span>
                                </td>
                                <td class="px-4 py-3 text-muted">
                                    {{ \Carbon\Carbon::parse($pedido->created_at)->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="badge bg-secondary">Pendente</span>
                                </td>
                                <td class="px-4 py-3 text-end">
                                    <form method="POST" action="{{ route('separacoes.iniciar', $pedido->id) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-warning">
                                            <i class="mdi mdi-play-circle-outline me-1"></i> Iniciar
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="mdi mdi-clipboard-text-outline display-4 d-block mb-3 opacity-25"></i>
                                        <p class="mb-0">Nenhum pedido pendente encontrado</p>
                                        <small>Tente ajustar os filtros ou inserir um novo pedido</small>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if(isset($pedidos) && method_exists($pedidos, 'links'))
            <div class="card-footer bg-white border-top">
                {{ $pedidos->links() }}
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
    .icon-wrapper.sm { width: 48px; height: 48px; border-radius: 10px; }
    .icon-wrapper i { color: #fff !important; }

    .input-group-text { background-color: #f8f9fa; }
    .form-control:focus { border-color: #0d6efd; box-shadow: 0 0 0 0.2rem rgba(13,110,253,0.1); }
    .table tbody tr:hover { background-color: #f8f9fa; transition: background-color 0.2s ease; }
    .card { border-radius: 0.5rem; }
    .badge { font-weight: 500; padding: 0.35em 0.65em; }
</style>

@endsection