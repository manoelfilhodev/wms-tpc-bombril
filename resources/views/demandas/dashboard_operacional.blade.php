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
                        <input type="date" name="data" value="{{ $dataSelecionada }}"
                            class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted mb-1">Turno</label>
                        <select name="turno" class="form-select form-select-sm">
                            <option value="">Todos</option>
                            @foreach ($turnosOperacionais as $codigo => $turno)
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
                        <small class="text-muted d-block mt-2">Backlog:
                            {{ number_format($status['pendente_backlog'] ?? 0, 0, ',', '.') }}</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <small class="text-muted">Separando</small>
                        <h3 class="mb-0">{{ $status['em_separacao'] }}</h3>
                        <small class="text-muted d-block mt-2">Backlog:
                            {{ number_format($status['em_separacao_backlog'] ?? 0, 0, ',', '.') }}</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body"><small class="text-muted">Separado parcial</small>
                        <h3 class="mb-0">{{ $status['finalizado_parcial'] }}</h3>
                        <small class="text-muted d-block mt-2">Backlog finalizado:
                            {{ number_format($status['finalizado_parcial_backlog'] ?? 0, 0, ',', '.') }}</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body"><small class="text-muted">Separado completo</small>
                        <h3 class="mb-0">{{ $status['finalizado_completo'] }}</h3>
                        <small class="text-muted d-block mt-2">Backlog finalizado:
                            {{ number_format($status['finalizado_completo_backlog'] ?? 0, 0, ',', '.') }}</small>
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

        <div class="accordion dashboard-accordion mb-4" id="accordionDashPicking">
            <div class="accordion-item border-0 shadow-sm mb-3 rounded-3 overflow-hidden">
                <h2 class="accordion-header" id="headingMeta">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseMeta"
                        aria-expanded="true" aria-controls="collapseMeta">
                        <div class="w-100 d-flex justify-content-between align-items-center pe-3">
                            <div>
                                <strong>Visão da Meta — 12h às 23:59</strong>
                                <small class="d-block text-muted">Meta oficial, projeção, produção por hora e ranking da
                                    janela operacional.</small>
                            </div>
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">


                                <span class="badge bg-light text-dark border">
                                    Separado:
                                    <span class="badgeSeparadoMeta">0</span>
                                </span>
                            </div>
                        </div>
                    </button>
                </h2>
                <div id="collapseMeta" class="accordion-collapse collapse show" aria-labelledby="headingMeta"
                    data-bs-parent="#accordionDashPicking">
                    <div class="accordion-body bg-white">
                        <div class="row g-3 mb-4">
                            <div class="col-xl-6">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body">
                                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                                            <h6 class="mb-0">Projeção de Produtividade (caixas)</h6>
                                            <div class="d-flex flex-wrap gap-2">
                                                <span class="badge bg-light text-dark border">Separado:
                                                    {{ number_format($dadosGraficos['projecaoProdutividade']['produzido'] ?? 0, 0, ',', '.') }}</span>
                                                <span class="badge bg-light text-dark border">Meta:
                                                    {{ number_format($dadosGraficos['projecaoProdutividade']['meta'] ?? 11000, 0, ',', '.') }}</span>
                                                @if (!empty($dadosGraficos['projecaoProdutividade']['previsaoConclusao']))
                                                    <span class="badge bg-success">Previsão:
                                                        {{ $dadosGraficos['projecaoProdutividade']['previsaoConclusao'] }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="chart-box chart-box-wide"><canvas
                                                id="chartProjecaoProdutividade"></canvas></div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-6">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body">
                                        <div
                                            class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                                            <h6 class="mb-0">Separação por hora x operador</h6>
                                            <span class="badge bg-light text-dark border">Total:
                                                {{ number_format($dadosGraficos['separacaoHoraOperador']['total'] ?? 0, 0, ',', '.') }}</span>
                                        </div>
                                        <div class="chart-box chart-box-wide"><canvas
                                                id="chartSeparacaoHoraOperador"></canvas></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                                    <div>
                                        <h6 class="mb-0">Produção total por picker</h6>
                                        <small class="text-muted">Total de caixas separadas na janela da meta.</small>
                                    </div>
                                    <span class="badge bg-light text-dark border" id="badgeTotalProducaoPicker">Total:
                                        {{ number_format($dadosGraficos['producaoPicker']['total'] ?? 0, 0, ',', '.') }}</span>
                                </div>
                                <div class="chart-box chart-box-ranking"><canvas id="chartProducaoPicker"></canvas></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="accordion-item border-0 shadow-sm mb-3 rounded-3 overflow-hidden">
                <h2 class="accordion-header" id="headingDiaCompleto">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                        data-bs-target="#collapseDiaCompleto" aria-expanded="false" aria-controls="collapseDiaCompleto">
                        <div class="w-100 d-flex flex-wrap justify-content-between align-items-center gap-2 pe-3">
                            <div>
                                <strong>Visão do Dia Completo — 00:01 às 23:59</strong>
                                <small class="d-block text-muted">Análise operacional do dia inteiro, sem interferir na
                                    meta oficial.</small>
                            </div>
                            <span class="badge bg-light text-dark border" id="badgeTotalDiaCompletoHeader">Total: 0</span>
                        </div>
                    </button>
                </h2>
                <div id="collapseDiaCompleto" class="accordion-collapse collapse" aria-labelledby="headingDiaCompleto"
                    data-bs-parent="#accordionDashPicking">
                    <div class="accordion-body bg-white">
                        <div class="row g-3 mb-4">
                            <div class="col-xl-6">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body">
                                        <div
                                            class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                                            <h6 class="mb-0">Separação por hora x operador — dia completo</h6>
                                            <span class="badge bg-light text-dark border"
                                                id="badgeTotalHoraOperadorDiaCompleto">Total:
                                                {{ number_format($dadosGraficos['separacaoHoraOperadorDiaCompleto']['total'] ?? 0, 0, ',', '.') }}</span>
                                        </div>
                                        <div class="chart-box chart-box-wide"><canvas
                                                id="chartSeparacaoHoraOperadorDiaCompleto"></canvas></div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-6">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body">
                                        <div
                                            class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                                            <div>
                                                <h6 class="mb-0">Produção total por picker — dia completo</h6>
                                                <small class="text-muted">Ranking acumulado das 00:01 às 23:59.</small>
                                            </div>
                                            <span class="badge bg-light text-dark border"
                                                id="badgeTotalPickerDiaCompleto">Total:
                                                {{ number_format($dadosGraficos['producaoPickerDiaCompleto']['total'] ?? 0, 0, ',', '.') }}</span>
                                        </div>
                                        <div class="chart-box chart-box-wide"><canvas
                                                id="chartProducaoPickerDiaCompleto"></canvas></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-light border small text-muted mb-0">
                            Observação: esta visão depende dos datasets <code>separacaoHoraOperadorDiaCompleto</code> e
                            <code>producaoPickerDiaCompleto</code> no backend. Se eles ainda não existirem, os gráficos
                            ficam vazios.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                    <h6 class="mb-0">Apontamentos Stretch por hora</h6>
                    <span class="badge bg-light text-dark border">Total:
                        {{ $dadosGraficos['stretchPorHora']['total'] ?? 0 }}</span>
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

        .chart-box-ranking {
            height: 360px;
        }

        .dashboard-accordion .accordion-button {
            background: #fff;
            box-shadow: none;
        }

        .dashboard-accordion .accordion-button:not(.collapsed) {
            color: #111827;
            background: #fff;
        }

        .dashboard-accordion .accordion-body {
            border-top: 1px solid rgba(148, 163, 184, .18);
        }
    </style>
    <script>
        const dadosGraficos = @json($dadosGraficos);

        const baseGrid = 'rgba(148, 163, 184, 0.15)';
        const baseTicks = '#9ca3af';
        const charts = {};

        const chartColors = {
            real: '#2563EB',
            realSoft: 'rgba(37, 99, 235, 0.14)',
            ideal: '#16A34A',
            idealSoft: 'rgba(22, 163, 74, 0.14)',
            projection: '#F59E0B',
            projectionSoft: 'rgba(245, 158, 11, 0.14)',
            target: '#DC2626',
            targetSoft: 'rgba(220, 38, 38, 0.12)',
            neutral: '#64748B',
            neutralSoft: 'rgba(100, 116, 139, 0.14)',
            status: ['#0EA5E9', '#2563EB', '#F59E0B', '#16A34A'],
            turnos: ['#2563EB', '#0F766E', '#7C3AED'],
            operadores: [
                '#2563EB',
                '#0F766E',
                '#7C3AED',
                '#B45309',
                '#BE123C',
                '#0369A1',
                '#4D7C0F',
                '#475569',
                '#94A3B8'
            ]
        };

        const formatCaixas = (value) => Number(value || 0).toLocaleString('pt-BR');

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
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: (ctx) => `${ctx.dataset.label || 'Valor'}: ${formatCaixas(ctx.parsed.y ?? ctx.raw)}`
                    }
                }
            },
            scales: {
                x: {
                    ticks: {
                        color: baseTicks
                    },
                    grid: {
                        color: baseGrid
                    }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        color: baseTicks,
                        callback: (value) => formatCaixas(value)
                    },
                    grid: {
                        color: baseGrid
                    }
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

        function resizeCharts() {
            Object.values(charts).forEach((chart) => chart && chart.resize());
        }

        document.querySelectorAll('[data-bs-toggle="collapse"]').forEach((el) => {
            el.addEventListener('shown.bs.collapse', () => setTimeout(resizeCharts, 80));
        });

        function isLastVisiblePoint(ctx) {
            const data = ctx.dataset.data || [];
            let lastIndex = -1;

            data.forEach((value, index) => {
                if (value !== null && value !== undefined) {
                    lastIndex = index;
                }
            });

            return ctx.dataIndex === lastIndex;
        }

        function getRankingFromStackedDataset(stackedDataset) {
            const datasets = stackedDataset.datasets || [];

            const rankingPicker = datasets
                .map((dataset) => ({
                    picker: dataset.label,
                    caixas: (dataset.data || []).reduce((sum, value) => sum + Number(value || 0), 0)
                }))
                .filter((item) => item.caixas > 0)
                .sort((a, b) => b.caixas - a.caixas);

            return {
                labels: rankingPicker.map((item) => item.picker),
                values: rankingPicker.map((item) => item.caixas),
                total: rankingPicker.reduce((sum, item) => sum + item.caixas, 0)
            };
        }

        function setBadgeText(id, total) {
            const el = document.getElementById(id);
            if (el) el.innerText = `Total: ${formatCaixas(total || 0)}`;
        }

        function filtrarStackedPorHora(source, horaInicio = 12) {
            const labelsOriginais = source.labels || [];
            const indicesValidos = labelsOriginais
                .map((label, index) => {
                    const hora = parseInt(String(label || '00:00').split(':')[0], 10);
                    return hora >= horaInicio ? index : null;
                })
                .filter((index) => index !== null);

            const labels = indicesValidos.map((index) => labelsOriginais[index]);

            const datasets = (source.datasets || []).map((dataset) => ({
                ...dataset,
                data: indicesValidos.map((index) => Number((dataset.data || [])[index] || 0))
            }));

            const total = datasets.reduce((soma, dataset) => {
                return soma + (dataset.data || []).reduce((sub, value) => sub + Number(value || 0), 0);
            }, 0);

            return {
                labels,
                datasets,
                total
            };
        }

        const projecao = dadosGraficos.projecaoProdutividade || {};
        const horaInicioMeta = 12;

        function getHora(item) {
            return parseInt(String(item.hora || '00:00').split(':')[0], 10);
        }

        function filtrarJanelaMeta(items) {
            return (items || []).filter((item) => getHora(item) >= horaInicioMeta);
        }

        const curvaIdealMeta = filtrarJanelaMeta(projecao.curvaIdeal);
        const apontamentosTodos = projecao.apontamentos || [];
        const apontamentosMeta = filtrarJanelaMeta(apontamentosTodos);
        const projecaoCorrigidaMeta = filtrarJanelaMeta(projecao.projecaoCorrigida);

        const baseAntesDaMeta = (() => {
            const pontosAntesDas12 = apontamentosTodos.filter((item) => getHora(item) < horaInicioMeta);

            if (pontosAntesDas12.length > 0) {
                return Number(pontosAntesDas12[pontosAntesDas12.length - 1].acumulado || 0);
            }

            return Number(apontamentosMeta[0]?.acumulado || 0);
        })();

        const labelsProjecao = curvaIdealMeta.map((item) => item.hora);

        const valoresReaisProjecao = apontamentosMeta.map((item) => {
            const valor = Number(item.acumulado || 0) - baseAntesDaMeta;
            return valor > 0 ? valor : 0;
        });

        const produzidoMeta = valoresReaisProjecao.length ?
            Number(valoresReaisProjecao[valoresReaisProjecao.length - 1] || 0) :
            0;

        document.querySelectorAll('.badgeSeparadoMeta').forEach((el) => {
            el.innerText = formatCaixas(produzidoMeta);
        });

        renderChart('chartProjecaoProdutividade', {
            type: 'line',
            data: {
                labels: labelsProjecao,
                datasets: [{
                        label: 'Caixas separadas',
                        data: valoresReaisProjecao,
                        yAxisID: 'y',
                        borderColor: chartColors.real,
                        backgroundColor: chartColors.realSoft,
                        borderWidth: 4,
                        pointRadius: (ctx) => (ctx.raw || 0) > 0 ? 5 : 3,
                        pointHoverRadius: 8,
                        tension: .25,
                        fill: false
                    },
                    {
                        label: 'Curva ideal',
                        data: curvaIdealMeta.map((item) => item.valor),
                        yAxisID: 'y',
                        borderColor: chartColors.ideal,
                        borderDash: [6, 4],
                        borderWidth: 2,
                        pointRadius: 0,
                        tension: .35,
                        fill: false
                    },
                    {
                        label: 'Projeção corrigida',
                        data: projecaoCorrigidaMeta.map((item) => item.valor),
                        yAxisID: 'y',
                        borderColor: chartColors.projection,
                        borderDash: [10, 5],
                        borderWidth: 2,
                        pointRadius: 0,
                        tension: .35,
                        fill: false
                    },
                    {
                        label: 'Meta 11.000 caixas',
                        data: labelsProjecao.map(() => projecao.meta || 11000),
                        yAxisID: 'y',
                        borderColor: chartColors.target,
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
                        display: (ctx) => ctx.datasetIndex === 0 && (ctx.raw || 0) > 0,
                        align: 'top',
                        anchor: 'end',
                        color: chartColors.real,
                        backgroundColor: 'rgba(255,255,255,.95)',
                        borderColor: 'rgba(148, 163, 184, 0.35)',
                        borderWidth: 1,
                        borderRadius: 4,
                        padding: 4,
                        font: {
                            size: 10,
                            weight: '700'
                        },
                        formatter: (value) => formatCaixas(value)
                    },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => `${ctx.dataset.label}: ${formatCaixas(ctx.raw)} caixas`
                        }
                    }
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Hora',
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
                        beginAtZero: true,
                        suggestedMax: projecao.meta || 11000,
                        title: {
                            display: true,
                            text: 'Caixas',
                            color: baseTicks
                        },
                        ticks: {
                            color: baseTicks,
                            callback: (value) => formatCaixas(value)
                        },
                        grid: {
                            color: baseGrid
                        }
                    }
                }
            }
        });

        function renderSeparacaoHoraOperador(id, source) {
            const datasets = (source.datasets || []).map((dataset, index) => ({
                ...dataset,
                backgroundColor: chartColors.operadores[index % chartColors.operadores.length]
            }));

            renderChart(id, {
                type: 'bar',
                data: {
                    labels: source.labels || [],
                    datasets
                },
                options: {
                    ...commonOptions,
                    plugins: {
                        ...commonOptions.plugins,
                        datalabels: {
                            display: (ctx) => (ctx.raw || 0) > 0,
                            color: '#ffffff',
                            font: {
                                weight: '700',
                                size: 9
                            },
                            formatter: (value) => formatCaixas(value)
                        },
                        tooltip: {
                            callbacks: {
                                label: (ctx) => `${ctx.dataset.label}: ${formatCaixas(ctx.raw)} caixas`,
                                footer: (items) => {
                                    const total = items.reduce((sum, item) => sum + Number(item.raw || 0), 0);
                                    return `Total da hora: ${formatCaixas(total)} caixas`;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            stacked: true,
                            title: {
                                display: true,
                                text: 'Hora',
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
                            stacked: true,
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Caixas',
                                color: baseTicks
                            },
                            ticks: {
                                color: baseTicks,
                                callback: (value) => formatCaixas(value)
                            },
                            grid: {
                                color: baseGrid
                            }
                        }
                    }
                }
            });
        }

        function renderProducaoPicker(id, source) {
            renderChart(id, {
                type: 'bar',
                data: {
                    labels: source.labels || [],
                    datasets: [{
                        label: 'Caixas',
                        data: source.values || [],
                        backgroundColor: chartColors.real,
                        borderRadius: 6,
                        maxBarThickness: 34
                    }]
                },
                options: {
                    ...commonOptions,
                    indexAxis: 'y',
                    plugins: {
                        ...commonOptions.plugins,
                        legend: {
                            display: false
                        },
                        datalabels: {
                            display: (ctx) => (ctx.raw || 0) > 0,
                            color: '#111827',
                            backgroundColor: 'rgba(255,255,255,.95)',
                            borderColor: 'rgba(148, 163, 184, 0.35)',
                            borderWidth: 1,
                            borderRadius: 4,
                            padding: 4,
                            anchor: 'end',
                            align: 'right',
                            font: {
                                size: 10,
                                weight: '700'
                            },
                            formatter: (value) => formatCaixas(value)
                        },
                        tooltip: {
                            callbacks: {
                                label: (ctx) => `Caixas: ${formatCaixas(ctx.raw)}`
                            }
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Caixas',
                                color: baseTicks
                            },
                            ticks: {
                                color: baseTicks,
                                callback: (value) => formatCaixas(value)
                            },
                            grid: {
                                color: baseGrid
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'Picker',
                                color: baseTicks
                            },
                            ticks: {
                                color: baseTicks
                            },
                            grid: {
                                color: baseGrid
                            }
                        }
                    }
                }
            });
        }

        const separacaoHoraOperadorOriginal = dadosGraficos.separacaoHoraOperador || {
            labels: [],
            datasets: [],
            total: 0
        };

        const separacaoHoraOperador = filtrarStackedPorHora(separacaoHoraOperadorOriginal, 12);

        renderSeparacaoHoraOperador('chartSeparacaoHoraOperador', separacaoHoraOperador);

        renderSeparacaoHoraOperador('chartSeparacaoHoraOperador', separacaoHoraOperador);

        let producaoPicker = dadosGraficos.producaoPicker || {
            labels: [],
            values: [],
            total: 0
        };

        if ((!producaoPicker.values || producaoPicker.values.length === 0) && separacaoHoraOperador.datasets) {
            producaoPicker = getRankingFromStackedDataset(separacaoHoraOperador);
        }

        setBadgeText('badgeTotalProducaoPicker', producaoPicker.total);
        renderProducaoPicker('chartProducaoPicker', producaoPicker);

        const separacaoHoraOperadorDiaCompleto = dadosGraficos.separacaoHoraOperadorDiaCompleto || {
            labels: [],
            datasets: [],
            total: 0
        };

        renderSeparacaoHoraOperador('chartSeparacaoHoraOperadorDiaCompleto', separacaoHoraOperadorDiaCompleto);
        setBadgeText('badgeTotalHoraOperadorDiaCompleto', separacaoHoraOperadorDiaCompleto.total);

        let producaoPickerDiaCompleto = dadosGraficos.producaoPickerDiaCompleto || {
            labels: [],
            values: [],
            total: 0
        };

        if ((!producaoPickerDiaCompleto.values || producaoPickerDiaCompleto.values.length === 0) &&
            separacaoHoraOperadorDiaCompleto.datasets) {
            producaoPickerDiaCompleto = getRankingFromStackedDataset(separacaoHoraOperadorDiaCompleto);
        }

        setBadgeText('badgeTotalPickerDiaCompleto', producaoPickerDiaCompleto.total);
        setBadgeText('badgeTotalDiaCompletoHeader', producaoPickerDiaCompleto.total || separacaoHoraOperadorDiaCompleto
            .total);
        renderProducaoPicker('chartProducaoPickerDiaCompleto', producaoPickerDiaCompleto);

        renderChart('chartStretchHora', {
            type: 'bar',
            data: {
                labels: dadosGraficos.stretchPorHora.labels,
                datasets: [{
                        type: 'bar',
                        label: 'Apontamentos',
                        data: dadosGraficos.stretchPorHora.values,
                        backgroundColor: chartColors.real,
                        borderRadius: 4,
                        maxBarThickness: 42
                    },
                    {
                        type: 'line',
                        label: 'Meta Stretch/hora (45)',
                        data: (dadosGraficos.stretchPorHora.labels || []).map(() => 45),
                        borderColor: chartColors.target,
                        backgroundColor: chartColors.targetSoft,
                        borderDash: [5, 4],
                        borderWidth: 2,
                        pointRadius: 0,
                        tension: 0,
                        fill: false
                    }
                ]
            },
            options: {
                ...commonOptions,
                plugins: {
                    ...commonOptions.plugins,
                    legend: {
                        display: true,
                        labels: {
                            color: baseTicks
                        }
                    },
                    datalabels: {
                        display: (ctx) => ctx.datasetIndex === 0 && (ctx.raw || 0) > 0,
                        color: '#ffffff',
                        font: {
                            weight: '700',
                            size: 10
                        },
                        formatter: (value) => formatCaixas(value)
                    },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => `${ctx.dataset.label}: ${formatCaixas(ctx.raw)}`
                        }
                    }
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Hora',
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
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Apontamentos',
                            color: baseTicks
                        },
                        ticks: {
                            color: baseTicks,
                            callback: (value) => formatCaixas(value)
                        },
                        grid: {
                            color: baseGrid
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
                    backgroundColor: chartColors.status
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
                        display: (ctx) => (ctx.raw || 0) > 0,
                        color: '#ffffff',
                        font: {
                            weight: '700',
                            size: 10
                        },
                        formatter: (value, ctx) => {
                            const total = ctx.dataset.data.reduce((a, b) => a + Number(b || 0), 0) || 1;
                            const pct = ((value / total) * 100).toFixed(0);
                            return `${value} (${pct}%)`;
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => {
                                const total = ctx.dataset.data.reduce((a, b) => a + Number(b || 0), 0) || 1;
                                const pct = ((ctx.raw / total) * 100).toFixed(1);
                                return `${ctx.label}: ${ctx.raw} (${pct}%)`;
                            }
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
                        borderColor: chartColors.real,
                        backgroundColor: chartColors.realSoft,
                        fill: true,
                        tension: .35,
                        pointRadius: 3
                    },
                    {
                        label: 'Finalizadas fora da data de criação',
                        data: dadosGraficos.evolucao7.finalizadas_outro_dia,
                        borderColor: chartColors.projection,
                        backgroundColor: chartColors.projectionSoft,
                        borderDash: [6, 4],
                        fill: false,
                        tension: .35,
                        pointRadius: 3
                    }
                ]
            },
            options: {
                ...commonOptions,
                plugins: {
                    ...commonOptions.plugins,
                    datalabels: {
                        display: (ctx) => (ctx.raw || 0) > 0,
                        align: 'top',
                        anchor: 'end',
                        color: (ctx) => ctx.dataset.borderColor,
                        backgroundColor: 'rgba(255,255,255,.95)',
                        borderColor: 'rgba(148, 163, 184, 0.35)',
                        borderWidth: 1,
                        borderRadius: 4,
                        padding: 4,
                        font: {
                            size: 10,
                            weight: '700'
                        },
                        formatter: (value) => formatCaixas(value)
                    }
                }
            }
        });

        renderChart('chartTurnos', {
            type: 'bar',
            data: {
                labels: dadosGraficos.turnos.labels,
                datasets: [{
                    label: 'Separações',
                    data: dadosGraficos.turnos.values,
                    backgroundColor: chartColors.turnos,
                    borderRadius: 4,
                    maxBarThickness: 42
                }]
            },
            options: {
                ...commonOptions,
                plugins: {
                    ...commonOptions.plugins,
                    datalabels: {
                        display: (ctx) => (ctx.raw || 0) > 0,
                        color: '#ffffff',
                        font: {
                            weight: '700',
                            size: 10
                        },
                        formatter: (value) => formatCaixas(value)
                    }
                }
            }
        });

        renderChart('chartRanking', {
            type: 'bar',
            data: {
                labels: dadosGraficos.ranking.labels,
                datasets: [{
                    label: 'Separações',
                    data: dadosGraficos.ranking.values,
                    backgroundColor: chartColors.ideal,
                    borderRadius: 4,
                    maxBarThickness: 42
                }]
            },
            options: {
                ...commonOptions,
                plugins: {
                    ...commonOptions.plugins,
                    datalabels: {
                        display: (ctx) => (ctx.raw || 0) > 0,
                        color: '#ffffff',
                        font: {
                            weight: '700',
                            size: 10
                        },
                        formatter: (value) => formatCaixas(value)
                    }
                }
            }
        });
    </script>
@endsection
