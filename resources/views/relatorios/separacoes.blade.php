@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    @include('partials.breadcrumb-auto')

    <!-- Header com ícone e ações -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
            <div class="icon-wrapper me-3">
                <i class="mdi mdi-chart-bar-stacked display-6 text-primary"></i>
            </div>
            <div>
                <h3 class="mb-1 fw-bold text-dark">Relatório de Separações</h3>
                <p class="text-muted mb-0 small">Filtre, analise e exporte os dados de separação</p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('relatorios.separacoes.excel', request()->query()) }}" 
               class="btn btn-success btn-sm" data-bs-toggle="tooltip" title="Exportar para Excel">
                <i class="mdi mdi-file-excel me-1"></i> Excel
            </a>
            <a href="{{ route('relatorios.separacoes.pdf', request()->query()) }}" 
               class="btn btn-danger btn-sm" data-bs-toggle="tooltip" title="Exportar para PDF">
                <i class="mdi mdi-file-pdf me-1"></i> PDF
            </a>
        </div>
    </div>

    <!-- Card de Filtros -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Data Início</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="mdi mdi-calendar-start text-muted"></i>
                        </span>
                        <input type="date" name="data_inicio" class="form-control border-start-0" value="{{ request('data_inicio') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Data Fim</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="mdi mdi-calendar-end text-muted"></i>
                        </span>
                        <input type="date" name="data_fim" class="form-control border-start-0" value="{{ request('data_fim') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Unidade</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light">
                            <i class="mdi mdi-office-building-marker-outline text-muted"></i>
                        </span>
                        <select name="unidade_id" class="form-select">
                            <option value="">Todas</option>
                            @foreach ($unidades as $unidade)
                                <option value="{{ $unidade->id }}" @selected(request('unidade_id') == $unidade->id)>
                                    {{ $unidade->nome }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Usuário</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light">
                            <i class="mdi mdi-account-badge-outline text-muted"></i>
                        </span>
                        <select name="usuario_id" class="form-select">
                            <option value="">Todos</option>
                            @foreach ($usuarios as $usuario)
                                <option value="{{ $usuario->id_user }}" @selected(request('usuario_id') == $usuario->id_user)>
                                    {{ mb_strtoupper($usuario->nome) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-12 d-flex align-items-end gap-2">
                    <button class="btn btn-primary">
                        <i class="mdi mdi-filter me-1"></i> Filtrar
                    </button>
                    @if(request()->hasAny(['data_inicio','data_fim','unidade_id','usuario_id']))
                        <a href="{{ url()->current() }}" class="btn btn-outline-secondary" data-bs-toggle="tooltip" title="Limpar filtros">
                            <i class="mdi mdi-close"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Card de Gráfico (opcional) -->
    {{-- Descomente para usar o gráfico
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <canvas id="graficoSeparacoes" height="100"></canvas>
        </div>
    </div>
    --}}

    <!-- Card da Tabela -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="px-4 py-3 text-muted small fw-semibold"><i class="mdi mdi-calendar me-1"></i> Data</th>
                            <th class="px-4 py-3 text-muted small fw-semibold"><i class="mdi mdi-account me-1"></i> Usuário</th>
                            <th class="px-4 py-3 text-muted small fw-semibold"><i class="mdi mdi-office-building-marker-outline me-1"></i> Unidade</th>
                            <th class="px-4 py-3 text-muted small fw-semibold"><i class="mdi mdi-barcode me-1"></i> SKU</th>
                            <th class="px-4 py-3 text-muted small fw-semibold"><i class="mdi mdi-map-marker me-1"></i> Posição</th>
                            <th class="px-4 py-3 text-muted small fw-semibold text-end"><i class="mdi mdi-counter me-1"></i> Quantidade</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($separacoes as $item)
                            <tr class="border-bottom">
                                <td class="px-4 py-3">{{ \Carbon\Carbon::parse($item->data_separacao)->format('d/m/Y') }}</td>
                                <td class="px-4 py-3">{{ mb_strtoupper($item->usuario_nome) }}</td>
                                <td class="px-4 py-3">{{ $item->unidade_nome }}</td>
                                <td class="px-4 py-3">
                                    <span class="badge bg-light text-dark border">{{ mb_strtoupper($item->sku) }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="badge bg-primary bg-opacity-10 text-primary">
                                        <i class="mdi mdi-map-marker-outline me-1"></i>{{ mb_strtoupper($item->endereco) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-end">
                                    <span class="fw-semibold text-dark">{{ number_format($item->quantidade, 0, ',', '.') }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="mdi mdi-clipboard-text-off-outline display-4 d-block mb-3 opacity-25"></i>
                                        <p class="mb-0">Nenhum dado encontrado</p>
                                        <small>Tente ajustar os filtros</small>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
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
    .form-control:focus, .form-select:focus { border-color: #0d6efd; box-shadow: 0 0 0 0.2rem rgba(13,110,253,0.1); }
    .table tbody tr:hover { background-color: #f8f9fa; transition: background-color 0.2s ease; }
    .card { border-radius: 0.5rem; }
    .badge { font-weight: 500; padding: 0.35em 0.65em; }
</style>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Descomente o card de gráfico acima para ativar este script
    const canvas = document.getElementById('graficoSeparacoes');
    if (canvas) {
        const ctx = canvas.getContext('2d');
        const chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($grafico->pluck('nome') ?? []) !!},
                datasets: [{
                    label: 'Total de Separações',
                    data: {!! json_encode($grafico->pluck('total') ?? []) !!},
                    backgroundColor: 'rgba(54, 162, 235, 0.7)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1,
                    borderRadius: 4,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: true },
                    tooltip: { mode: 'index', intersect: false }
                },
                scales: {
                    y: { beginAtZero: true, grid: { display: true } },
                    x: { grid: { display: false } }
                }
            }
        });
    }
</script>
@endpush