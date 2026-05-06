@extends('layouts.app')

@section('title', 'Report Gerencial da Operação')

@php
    $fmt = fn($v) => number_format((float) $v, 0, ',', '.');
    $fmtPct = fn($v) => number_format((float) $v, 1, ',', '.') . '%';
    $fmtMin = function ($minutos) {
        if ($minutos === null) {
            return '-';
        }

        $minutos = (float) $minutos;
        if ($minutos >= 60) {
            $horas = floor($minutos / 60);
            $resto = round($minutos % 60);
            return "{$horas}h {$resto}min";
        }

        return number_format($minutos, 1, ',', '.') . ' min';
    };
@endphp

@section('content')
<div class="container-fluid px-4 py-3 report-gerencial-page">
    @include('partials.breadcrumb-auto')

    <div class="manager-hero mb-4">
        <div>
            <span class="manager-eyebrow">Picking / Operação</span>
            <h3 class="mb-1">Report Gerencial da Operação</h3>
            <p class="mb-0">Visão executiva para acompanhamento de volume, SLA, backlog e produtividade.</p>
        </div>
        <div class="manager-period">
            <span>{{ $inicio->format('d/m/Y') }}</span>
            <small>até</small>
            <span>{{ $fim->format('d/m/Y') }}</span>
        </div>
    </div>

    <div class="manager-filter mb-4">
        <form method="GET" action="{{ route('demandas.reportGerencial') }}" class="row g-3 align-items-end">
            <div class="col-md-2">
                <label class="form-label">Data inicial</label>
                <input type="date" name="data_inicio" class="form-control form-control-sm" value="{{ $dataInicio }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Data final</label>
                <input type="date" name="data_fim" class="form-control form-control-sm" value="{{ $dataFim }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Turno</label>
                <select name="turno" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    @foreach($turnosOperacionais as $codigo => $turno)
                        <option value="{{ $codigo }}" @selected($turnoSelecionado === $codigo)>
                            {{ $turno['label'] }} - {{ $turno['periodo'] }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Separador</label>
                <input type="text" name="separador" list="separadores-list" class="form-control form-control-sm"
                    value="{{ $separadorSelecionado }}" placeholder="Todos">
                <datalist id="separadores-list">
                    @foreach($separadoresDisponiveis as $nome)
                        <option value="{{ $nome }}">
                    @endforeach
                </datalist>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary flex-fill">
                    <i class="mdi mdi-filter-outline me-1"></i> Aplicar
                </button>
                <a href="{{ route('demandas.reportGerencial') }}" class="btn btn-sm btn-outline-light">
                    <i class="mdi mdi-refresh"></i>
                </a>
            </div>
        </form>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="metric-card">
                <small>DTs finalizadas</small>
                <strong>{{ $fmt($resumo['finalizadas']) }}</strong>
                <span>{{ $fmtPct($resumo['percentual_conclusao']) }} de conclusão sobre criadas</span>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="metric-card">
                <small>Caixas / peças</small>
                <strong>{{ $fmt($resumo['pecas']) }}</strong>
                <span>{{ $fmt($resumo['skus']) }} SKUs apontados</span>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="metric-card">
                <small>SLA mesmo dia</small>
                <strong>{{ $fmtPct($resumo['sla_no_dia']) }}</strong>
                <span>{{ $fmt($resumo['finalizadas_fora_dia']) }} DTs fora da data de criação</span>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="metric-card">
                <small>Backlog aberto</small>
                <strong>{{ $fmt($resumo['backlog_aberto']) }}</strong>
                <span>{{ $fmt($resumo['em_aberto_periodo']) }} abertas do período</span>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-xl-8">
            <div class="manager-panel h-100">
                <div class="panel-title">
                    <div>
                        <h5>Evolução diária</h5>
                        <small>DTs criadas versus DTs finalizadas no período selecionado.</small>
                    </div>
                    @if($resumo['variacao_volume'] !== null)
                        <span class="trend-badge {{ $resumo['variacao_volume'] >= 0 ? 'trend-up' : 'trend-down' }}">
                            {{ $resumo['variacao_volume'] >= 0 ? '+' : '' }}{{ $fmtPct($resumo['variacao_volume']) }} vs período anterior
                        </span>
                    @else
                        <span class="trend-badge">Sem base anterior</span>
                    @endif
                </div>
                <div class="chart-box"><canvas id="chartEvolucaoGerencial"></canvas></div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="manager-panel h-100">
                <div class="panel-title">
                    <div>
                        <h5>Status gerencial</h5>
                        <small>Composição do período.</small>
                    </div>
                </div>
                <div class="chart-box chart-box-sm"><canvas id="chartStatusGerencial"></canvas></div>
                <div class="status-grid">
                    <div><span>Completas</span><strong>{{ $fmt($resumo['completas']) }}</strong></div>
                    <div><span>Parciais</span><strong>{{ $fmt($resumo['parciais']) }}</strong></div>
                    <div><span>Parcialidade</span><strong>{{ $fmtPct($resumo['percentual_parcial']) }}</strong></div>
                    <div><span>Tempo médio</span><strong>{{ $fmtMin($resumo['tempo_medio_min']) }}</strong></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-xl-8">
            <div class="manager-panel h-100">
                <div class="panel-title">
                    <div>
                        <h5>Produtividade por separador</h5>
                        <small>Ranking por volume apontado, limitado aos 15 maiores no período.</small>
                    </div>
                    <span class="trend-badge">{{ $fmt($resumo['dts_com_apontamento']) }} DTs com apontamento</span>
                </div>
                <div class="chart-box"><canvas id="chartProdutividadeGerencial"></canvas></div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="manager-panel h-100 attention-panel">
                <div class="panel-title">
                    <div>
                        <h5>Pontos de atenção</h5>
                        <small>Leitura automática dos principais indicadores.</small>
                    </div>
                </div>
                <div class="attention-list">
                    @foreach($pontosAtencao as $item)
                        <div class="attention-item">
                            <i class="mdi mdi-alert-circle-outline"></i>
                            <span>{{ $item }}</span>
                        </div>
                    @endforeach
                </div>
                <div class="executive-note">
                    <span>Maior tempo registrado</span>
                    <strong>{{ $fmtMin($resumo['tempo_max_min']) }}</strong>
                </div>
            </div>
        </div>
    </div>

    <div class="manager-panel mb-4">
        <div class="panel-title">
            <div>
                <h5>Tabela analítica de produtividade</h5>
                <small>Base para acompanhamento do gestor da operação.</small>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle manager-table mb-0">
                <thead>
                    <tr>
                        <th>Separador</th>
                        <th class="text-end">Caixas/peças</th>
                        <th class="text-end">SKUs</th>
                        <th class="text-end">DTs</th>
                        <th class="text-end">Apontamentos</th>
                        <th class="text-end">Tempo médio</th>
                        <th class="text-end">Participação</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($produtividade as $linha)
                        <tr>
                            <td>{{ $linha['separador'] }}</td>
                            <td class="text-end">{{ $fmt($linha['pecas']) }}</td>
                            <td class="text-end">{{ $fmt($linha['skus']) }}</td>
                            <td class="text-end">{{ $fmt($linha['dts']) }}</td>
                            <td class="text-end">{{ $fmt($linha['apontamentos']) }}</td>
                            <td class="text-end">{{ $fmtMin($linha['tempo_medio_min']) }}</td>
                            <td class="text-end">{{ $fmtPct($linha['participacao']) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Sem apontamentos finalizados para os filtros selecionados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mb-4">
        <a href="{{ route('demandas.relatorios') }}" class="btn btn-outline-secondary btn-sm">Voltar relatórios</a>
        <a href="{{ route('demandas.dashboardOperacional') }}" class="btn btn-outline-secondary btn-sm">Dashboard operacional</a>
    </div>
</div>

<style>
    .report-gerencial-page {
        --manager-bg: #090b10;
        --manager-panel: rgba(17, 24, 39, 0.94);
        --manager-panel-soft: rgba(31, 41, 55, 0.72);
        --manager-border: rgba(148, 163, 184, 0.18);
        --manager-text: #f8fafc;
        --manager-muted: #94a3b8;
        --manager-red: #ef4444;
        --manager-blue: #38bdf8;
        --manager-green: #22c55e;
        --manager-yellow: #f59e0b;
        color: var(--manager-text);
    }

    .manager-hero,
    .manager-filter,
    .manager-panel,
    .metric-card {
        background: var(--manager-panel);
        border: 1px solid var(--manager-border);
        box-shadow: 0 18px 44px rgba(2, 6, 23, 0.26);
    }

    .manager-hero {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        border-radius: 8px;
        padding: 24px;
    }

    .manager-hero h3,
    .panel-title h5 {
        color: var(--manager-text);
        letter-spacing: 0;
    }

    .manager-hero p,
    .panel-title small,
    .metric-card span,
    .status-grid span,
    .manager-filter .form-label {
        color: var(--manager-muted);
    }

    .manager-eyebrow {
        color: var(--manager-red);
        display: block;
        font-size: 12px;
        font-weight: 800;
        margin-bottom: 6px;
        text-transform: uppercase;
    }

    .manager-period {
        display: flex;
        align-items: center;
        gap: 10px;
        color: #fff;
        font-weight: 800;
        white-space: nowrap;
    }

    .manager-period small {
        color: var(--manager-muted);
        font-weight: 700;
    }

    .manager-filter,
    .manager-panel,
    .metric-card {
        border-radius: 8px;
        padding: 18px;
    }

    .manager-filter .form-control,
    .manager-filter .form-select {
        background-color: #0f172a;
        border-color: rgba(148, 163, 184, 0.28);
        color: #f8fafc;
    }

    .metric-card {
        min-height: 132px;
    }

    .metric-card small {
        color: var(--manager-muted);
        display: block;
        font-weight: 800;
        text-transform: uppercase;
    }

    .metric-card strong {
        color: #fff;
        display: block;
        font-size: 34px;
        line-height: 1;
        margin: 14px 0 10px;
    }

    .panel-title {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 16px;
    }

    .panel-title h5 {
        margin-bottom: 4px;
    }

    .trend-badge {
        background: var(--manager-panel-soft);
        border: 1px solid var(--manager-border);
        border-radius: 999px;
        color: #e2e8f0;
        font-size: 12px;
        font-weight: 800;
        padding: 7px 10px;
        white-space: nowrap;
    }

    .trend-up { color: #86efac; }
    .trend-down { color: #fca5a5; }

    .chart-box {
        height: 310px;
        position: relative;
    }

    .chart-box-sm {
        height: 220px;
    }

    .status-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
        margin-top: 12px;
    }

    .status-grid div,
    .attention-item,
    .executive-note {
        background: rgba(15, 23, 42, 0.72);
        border: 1px solid var(--manager-border);
        border-radius: 8px;
        padding: 12px;
    }

    .status-grid span,
    .status-grid strong {
        display: block;
    }

    .status-grid strong {
        color: #fff;
        font-size: 18px;
        margin-top: 4px;
    }

    .attention-list {
        display: grid;
        gap: 10px;
    }

    .attention-item {
        align-items: flex-start;
        color: #e2e8f0;
        display: flex;
        gap: 10px;
    }

    .attention-item i {
        color: var(--manager-yellow);
        font-size: 20px;
        line-height: 1;
    }

    .executive-note {
        margin-top: 14px;
    }

    .executive-note span,
    .executive-note strong {
        display: block;
    }

    .executive-note span {
        color: var(--manager-muted);
        font-weight: 700;
    }

    .executive-note strong {
        color: #fff;
        font-size: 26px;
        margin-top: 4px;
    }

    .manager-table {
        --bs-table-bg: transparent;
        --bs-table-striped-bg: rgba(15, 23, 42, 0.42);
        border-color: rgba(148, 163, 184, 0.16);
        color: #f8fafc;
    }

    .manager-table thead th {
        color: #cbd5e1;
        font-size: 12px;
        text-transform: uppercase;
    }

    @media (max-width: 768px) {
        .manager-hero,
        .panel-title {
            flex-direction: column;
        }

        .manager-period,
        .trend-badge {
            white-space: normal;
        }

        .chart-box {
            height: 260px;
        }
    }
</style>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const dados = @json($dadosGraficos);
    const gridColor = 'rgba(148, 163, 184, 0.18)';
    const textColor = '#cbd5e1';

    Chart.defaults.color = textColor;
    Chart.defaults.font.family = 'Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif';

    new Chart(document.getElementById('chartEvolucaoGerencial'), {
        type: 'line',
        data: {
            labels: dados.evolucao.labels,
            datasets: [
                {
                    label: 'Criadas',
                    data: dados.evolucao.criadas,
                    borderColor: '#38bdf8',
                    backgroundColor: 'rgba(56, 189, 248, 0.14)',
                    tension: 0.32,
                    fill: true
                },
                {
                    label: 'Finalizadas',
                    data: dados.evolucao.finalizadas,
                    borderColor: '#22c55e',
                    backgroundColor: 'rgba(34, 197, 94, 0.12)',
                    tension: 0.32,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } },
            scales: {
                x: { grid: { color: gridColor } },
                y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: gridColor } }
            }
        }
    });

    new Chart(document.getElementById('chartStatusGerencial'), {
        type: 'doughnut',
        data: {
            labels: dados.status.labels,
            datasets: [{
                data: dados.status.values,
                backgroundColor: ['#22c55e', '#f59e0b', '#38bdf8', '#ef4444'],
                borderColor: '#111827',
                borderWidth: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '68%',
            plugins: { legend: { position: 'bottom' } }
        }
    });

    new Chart(document.getElementById('chartProdutividadeGerencial'), {
        type: 'bar',
        data: {
            labels: dados.produtividade.labels,
            datasets: [
                {
                    label: 'Caixas/peças',
                    data: dados.produtividade.pecas,
                    backgroundColor: '#ef4444',
                    borderRadius: 6,
                    maxBarThickness: 38
                },
                {
                    label: 'DTs',
                    data: dados.produtividade.dts,
                    backgroundColor: '#38bdf8',
                    borderRadius: 6,
                    maxBarThickness: 38
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } },
            scales: {
                x: { grid: { display: false } },
                y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: gridColor } }
            }
        }
    });
});
</script>
@endpush
