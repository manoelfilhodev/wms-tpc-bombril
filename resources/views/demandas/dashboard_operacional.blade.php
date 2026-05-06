@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4 py-3">
        @include('partials.breadcrumb-auto')

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="mb-1 fw-bold text-dark">Dashboard Operacional Picking</h3>
                <p class="text-muted mb-0 small">Visão em tempo real do Picking</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('demandas.relatorios') }}" class="btn btn-outline-secondary btn-sm">Relatórios</a>
                <a href="{{ route('demandas.operacional') }}" class="btn btn-outline-secondary btn-sm">Voltar operacional</a>
                <a href="{{ route('demandas.dashboardOperacional') }}" class="btn btn-outline-secondary btn-sm">Atualizar</a>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('demandas.dashboardOperacional') }}" class="row g-2">
                    <div class="col-md-3">
                        <label class="form-label small text-muted mb-1">Data operacional</label>
                        <input type="date" name="data" value="{{ $dataSelecionada }}" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted mb-1">Turno</label>
                        <select name="turno" class="form-select form-select-sm">
                            <option value="">Todos</option>
                            @foreach($turnosOperacionais as $codigo => $turno)
                                <option value="{{ $codigo }}" @selected($turnoSelecionado === $codigo)>
                                    {{ $turno['label'] }} - {{ $turno['periodo'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-sm btn-primary w-100">Aplicar</button>
                    </div>
                </form>
                <div class="d-flex flex-wrap gap-3 mt-3 small text-muted">
                    <span>DTs geradas na data: <strong class="text-body">{{ $resumoOperacional['geradas'] }}</strong></span>
                    <span>Entram no picking: <strong class="text-body">{{ $resumoOperacional['picking'] }}</strong></span>
                    @if ($resumoOperacional['fora_picking'] > 0)
                        <span>Fora do picking: <strong
                                class="text-body">{{ $resumoOperacional['fora_picking'] }}</strong></span>
                    @endif
                    @if (($resumoOperacional['finalizadas_fora_data_criacao'] ?? 0) > 0)
                        <span>Finalizadas fora da data de criação: <strong
                                class="text-body">{{ $resumoOperacional['finalizadas_fora_data_criacao'] }}</strong></span>
                    @endif
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body"><small class="text-muted">A separar</small>
                        <h3 class="mb-0">{{ $status['pendente'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <small class="text-muted">Separando</small>
                        <h3 class="mb-0">{{ $status['em_separacao'] }}</h3>
                        @if ($separandoOutrasDatas->isNotEmpty())
                            <small class="text-muted d-block mt-2">
                                @foreach ($separandoOutrasDatas as $grupo)
                                    Data de criação {{ $grupo['data'] }}: {{ $grupo['total'] }} em
                                    andamento{{ !$loop->last ? ' | ' : '' }}
                                @endforeach
                            </small>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body"><small class="text-muted">Separado parcial</small>
                        <h3 class="mb-0">{{ $status['finalizado_parcial'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body"><small class="text-muted">Separado completo</small>
                        <h3 class="mb-0">{{ $status['finalizado_completo'] }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body d-flex justify-content-between align-items-center">
                <small class="text-muted d-block">Tempo médio geral</small>
                <h4 class="mb-0">{{ $tempoMedioMin !== null ? $tempoMedioMin . ' min' : '-' }}</h4>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                    <h6 class="mb-0">Projeção de Produtividade (caixas)</h6>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge bg-light text-dark border">
                            Separado: {{ number_format($dadosGraficos['projecaoProdutividade']['produzido'] ?? 0, 0, ',', '.') }}
                        </span>
                        <span class="badge bg-light text-dark border">
                            Meta: {{ number_format($dadosGraficos['projecaoProdutividade']['meta'] ?? 11000, 0, ',', '.') }}
                        </span>
                        @if (!empty($dadosGraficos['projecaoProdutividade']['previsaoConclusao']))
                            <span class="badge bg-success">
                                Previsão: {{ $dadosGraficos['projecaoProdutividade']['previsaoConclusao'] }}
                            </span>
                        @endif
                    </div>
                </div>
                <div class="chart-box chart-box-wide"><canvas id="chartProjecaoProdutividade"></canvas></div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                    <h6 class="mb-0">Apontamentos Stretch por hora</h6>
                    <span class="badge bg-light text-dark border">
                        Total: {{ $dadosGraficos['stretchPorHora']['total'] ?? 0 }}
                    </span>
                </div>
                <div class="chart-box chart-box-wide"><canvas id="chartStretchHora"></canvas></div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h6 class="mb-3">Status das DTs</h6>
                        <div class="chart-box"><canvas id="chartStatus"></canvas></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h6 class="mb-3">Evolução por data de criação (7 dias)</h6>
                        <div class="chart-box"><canvas id="chartEvolucao"></canvas></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h6 class="mb-3">Comparativo por turno</h6>
                        <div class="chart-box"><canvas id="chartTurnos"></canvas></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h6 class="mb-3">Top separadores</h6>
                        <div class="chart-box"><canvas id="chartRanking"></canvas></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h6 class="mb-3">Ranking por separador</h6>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Separador</th>
                                <th class="text-end">Separações</th>
                                <th class="text-end">Tempo médio (min)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ranking as $item)
                                <tr>
                                    <td>{{ $item->separador_nome }}</td>
                                    <td class="text-end">{{ $item->total_separacoes }}</td>
                                    <td class="text-end">{{ number_format((float) $item->tempo_medio_min, 1, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">Sem dados finalizados ainda.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
    <style>
        .chart-box {
            position: relative;
            height: 260px;
            overflow: hidden;
        }

        .chart-box-wide {
            height: 300px;
        }
    </style>
    <script>
        const dadosGraficos = @json($dadosGraficos);
        const baseGrid = 'rgba(148, 163, 184, 0.15)';
        const baseTicks = '#9ca3af';
        const charts = {};

        const commonOptions = {
            responsive: true,
            maintainAspectRatio: false,
            animation: false,
            plugins: {
                legend: {
                    labels: {
                        color: baseTicks
                    }
                },
                datalabels: {
                    color: '#e5e7eb',
                    font: {
                        weight: '700',
                        size: 10
                    },
                    anchor: 'end',
                    align: 'top',
                    formatter: (value) => value
                },
                tooltip: {
                    callbacks: {
                        label: (ctx) => `${ctx.dataset.label || 'Valor'}: ${ctx.parsed.y ?? ctx.raw}`
                    }
                }
            },
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'Categoria',
                        color: baseTicks
                    },
                    ticks: {
                        color: baseTicks
                    },
                    grid: {
                        color: baseGrid
                    }
                },
                y: {
                    title: {
                        display: true,
                        text: 'Quantidade',
                        color: baseTicks
                    },
                    ticks: {
                        color: baseTicks
                    },
                    grid: {
                        color: baseGrid
                    },
                    beginAtZero: true
                }
            }
        };

        function renderChart(id, config) {
            const canvas = document.getElementById(id);
            if (!canvas) return;
            if (charts[id]) charts[id].destroy();
            charts[id] = new Chart(canvas, {
                ...config,
                plugins: [ChartDataLabels]
            });
        }

        const projecao = dadosGraficos.projecaoProdutividade || {};
        const labelsProjecao = (projecao.curvaIdeal || []).map((item) => item.hora);
        const formatCaixas = (value) => Number(value || 0).toLocaleString('pt-BR');

        renderChart('chartProjecaoProdutividade', {
            type: 'line',
            data: {
                labels: labelsProjecao,
                datasets: [
                    {
                        label: 'Caixas separadas',
                        data: (projecao.apontamentos || []).map((item) => item.acumulado),
                        borderColor: '#2563eb',
                        backgroundColor: 'rgba(37, 99, 235, 0.12)',
                        borderWidth: 3,
                        pointRadius: 3,
                        tension: .35,
                        fill: true
                    },
                    {
                        label: 'Curva ideal',
                        data: (projecao.curvaIdeal || []).map((item) => item.valor),
                        borderColor: '#16a34a',
                        borderDash: [6, 4],
                        borderWidth: 2,
                        pointRadius: 0,
                        tension: .35,
                        fill: false
                    },
                    {
                        label: 'Projeção corrigida',
                        data: (projecao.projecaoCorrigida || []).map((item) => item.valor),
                        borderColor: '#f59e0b',
                        borderDash: [10, 5],
                        borderWidth: 2,
                        pointRadius: 0,
                        tension: .35,
                        fill: false
                    },
                    {
                        label: 'Meta 11.000 caixas',
                        data: labelsProjecao.map(() => projecao.meta || 11000),
                        borderColor: '#ef4444',
                        borderDash: [2, 2],
                        borderWidth: 2,
                        pointRadius: 0,
                        fill: false
                    }
                ]
            },
            options: {
                ...commonOptions,
                plugins: {
                    ...commonOptions.plugins,
                    datalabels: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => `${ctx.dataset.label}: ${formatCaixas(ctx.raw)} caixas`
                        }
                    }
                },
                scales: {
                    ...commonOptions.scales,
                    x: {
                        ...commonOptions.scales.x,
                        title: {
                            display: true,
                            text: 'Hora',
                            color: baseTicks
                        }
                    },
                    y: {
                        ...commonOptions.scales.y,
                        title: {
                            display: true,
                            text: 'Caixas',
                            color: baseTicks
                        },
                        ticks: {
                            color: baseTicks,
                            callback: (value) => formatCaixas(value)
                        },
                        suggestedMax: projecao.meta || 11000
                    }
                }
            }
        });

        renderChart('chartStretchHora', {
            type: 'bar',
            data: {
                labels: dadosGraficos.stretchPorHora.labels,
                datasets: [{
                    label: 'Apontamentos',
                    data: dadosGraficos.stretchPorHora.values,
                    backgroundColor: '#38bdf8',
                    borderRadius: 4,
                    maxBarThickness: 42
                }]
            },
            options: {
                ...commonOptions,
                plugins: {
                    ...commonOptions.plugins,
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => `Apontamentos: ${ctx.parsed.y ?? ctx.raw}`
                        }
                    }
                },
                scales: {
                    ...commonOptions.scales,
                    x: {
                        ...commonOptions.scales.x,
                        title: {
                            display: true,
                            text: 'Hora',
                            color: baseTicks
                        }
                    }
                }
            }
        });

        renderChart('chartStatus', {
            type: 'doughnut',
            data: {
                labels: dadosGraficos.status.labels,
                datasets: [{
                    data: dadosGraficos.status.values,
                    backgroundColor: ['#38bdf8', '#3b82f6', '#f59e0b', '#22c55e']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: baseTicks
                        }
                    },
                    datalabels: {
                        color: '#e5e7eb',
                        formatter: (value, ctx) => {
                            const total = ctx.dataset.data.reduce((a, b) => a + b, 0) || 1;
                            const pct = ((value / total) * 100).toFixed(0);
                            return `${value} (${pct}%)`;
                        }
                    }
                }
            }
        });

        renderChart('chartEvolucao', {
            type: 'line',
            data: {
                labels: dadosGraficos.evolucao7.labels,
                datasets: [{
                        label: 'Finalizadas no dia',
                        data: dadosGraficos.evolucao7.finalizadas_no_dia,
                        borderColor: '#60a5fa',
                        backgroundColor: 'rgba(96,165,250,.2)',
                        fill: true,
                        tension: .35
                    },
                    {
                        label: 'Finalizadas fora da data de criação',
                        data: dadosGraficos.evolucao7.finalizadas_outro_dia,
                        borderColor: '#f59e0b',
                        backgroundColor: 'rgba(245,158,11,.12)',
                        borderDash: [6, 4],
                        fill: false,
                        tension: .35
                    }
                ]
            },
            options: commonOptions
        });

        renderChart('chartTurnos', {
            type: 'bar',
            data: {
                labels: dadosGraficos.turnos.labels,
                datasets: [{
                    label: 'Separações',
                    data: dadosGraficos.turnos.values,
                    backgroundColor: ['#38bdf8', '#3b82f6', '#6366f1']
                }]
            },
            options: commonOptions
        });

        renderChart('chartRanking', {
            type: 'bar',
            data: {
                labels: dadosGraficos.ranking.labels,
                datasets: [{
                    label: 'Separações',
                    data: dadosGraficos.ranking.values,
                    backgroundColor: '#22c55e'
                }]
            },
            options: commonOptions
        });
    </script>
@endsection
