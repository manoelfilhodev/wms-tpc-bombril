@extends('layouts.app')

@section('content')

<style>
    /* ========== Estilos Base ========== */
    .icon-wrapper {
        width: 60px; height: 60px;
        display: flex; align-items: center; justify-content: center;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(102,126,234,0.3);
    }
    .icon-wrapper i { color: #fff !important; }
    
    .card { 
        border: 1px solid var(--wms-border);
        border-radius: 0.75rem; 
        box-shadow: var(--wms-shadow);
        transition: transform .08s ease, box-shadow .08s ease;
        background: var(--wms-surface);
    }
    .card:hover { 
        transform: translateY(-2px); 
        box-shadow: 0 6px 18px rgba(20,20,43,.08);
    }
    
    .card-header { 
        background: var(--wms-surface-soft) !important; 
        border-bottom: 1px solid var(--wms-border) !important; 
        font-size: 0.95rem;
        font-weight: 600;
        padding: 1rem 1.25rem;
        color: var(--wms-text);
    }
    
    .table thead th { 
        background: var(--wms-surface-soft); 
        font-weight: 600; 
        font-size: 0.875rem;
        border-bottom: 2px solid var(--wms-border);
        color: var(--wms-text);
    }
    .table tbody tr:hover { 
        background: var(--wms-surface-soft); 
        transition: background-color 0.2s ease;
    }
    
    .btn-icon { 
        display: inline-flex; 
        align-items: center; 
        gap: .5rem; 
    }
    
    .section-header {
        background: var(--wms-surface);
        border-radius: 0.75rem;
        padding: 1.25rem;
        margin-bottom: 1.5rem;
        box-shadow: var(--wms-shadow);
        border: 1px solid var(--wms-border);
    }
    
    .badge-soft { 
        background-color: #f1f5ff; 
        color: #1d4ed8; 
        font-weight: 600; 
        padding: 0.35em 0.65em; 
    }
    
    .chart-container { 
        min-height: 300px; 
        padding: 1rem;
    }
    
    .gauge-container { 
        display: flex; 
        flex-direction: column; 
        align-items: center; 
        justify-content: center; 
        padding: 1.5rem; 
    }
    
    .gauge-value { 
        font-size: 1.25rem; 
        font-weight: 700; 
        color: var(--wms-text); 
        margin-top: 1rem; 
    }
    
    .gauge-meta { 
        font-size: 0.875rem; 
        color: var(--wms-text-muted); 
        margin-top: 0.5rem; 
    }
    
    .alert { 
        border-radius: 12px; 
        border: none;
        box-shadow: 0 2px 8px rgba(20,20,43,.06);
    }
    
    .kpi-card {
        transition: all 0.2s ease;
    }
    
    .kpi-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 20px rgba(20,20,43,.1);
    }

    [data-theme="dark"] .icon-wrapper {
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.35);
    }

    [data-theme="dark"] .badge.bg-light {
        background: #22344c !important;
        color: var(--wms-text) !important;
        border-color: var(--wms-border) !important;
    }
    
    @media (max-width: 576px) {
        .stack-sm { 
            flex-direction: column; 
            gap: .5rem; 
            align-items: stretch !important; 
        }
        .stack-sm .d-flex { width: 100%; }
        .stack-sm .btn { width: 100%; justify-content: center; }
    }
</style>

<div class="container-fluid px-4 py-3">
    @include('partials.breadcrumb-auto')
    
    <!-- ========== Header Principal ========== -->
    <div class="section-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="d-flex align-items-center">
                <div class="icon-wrapper me-3">
                    <i class="mdi mdi-view-dashboard display-6"></i>
                </div>
                <div>
                    <h3 class="mb-1 fw-bold text-dark">Painel de Controle</h3>
                    <p class="text-muted mb-0 small">Visão geral das operações em tempo real</p>
                </div>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-secondary btn-sm btn-icon" data-bs-toggle="tooltip" title="Atualizar dados">
                    <i class="mdi mdi-refresh"></i> Atualizar
                </button>
                <button class="btn btn-outline-secondary btn-sm btn-icon" data-bs-toggle="tooltip" title="Configurações">
                    <i class="mdi mdi-cog"></i>
                </button>
            </div>
        </div>
    </div>

    {{-- ================== NOTIFICAÇÕES ================== --}}
    @if(!empty($notificacoes))
        <div class="row mb-4">
            <div class="col-12">
                @foreach($notificacoes as $notificacao)
                    <div class="alert alert-warning d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3" role="alert">
                        <div class="d-flex align-items-start gap-3 flex-grow-1">
                            <div class="icon-wrapper" style="width: 40px; height: 40px; background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);">
                                <i class="mdi mdi-alert-circle"></i>
                            </div>
                            <div>
                                <div class="fw-semibold mb-1">
                                    {{ $notificacao->tipo ?? 'Aviso' }}
                                    <span class="ms-2 badge badge-soft">{{ \Carbon\Carbon::parse($notificacao->created_at)->diffForHumans() }}</span>
                                </div>
                                <div class="text-dark">{{ $notificacao->mensagem }}</div>
                            </div>
                        </div>
                        <form action="{{ route('notificacoes.ler', $notificacao->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <button class="btn btn-sm btn-outline-secondary btn-icon" title="Marcar como lida">
                                <i class="mdi mdi-check-circle"></i> Lida
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ================== DEMANDAS DO DIA ================== --}}
    <div class="section-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 stack-sm">
            <div class="d-flex align-items-center">
                <i class="mdi mdi-truck-delivery text-primary me-2" style="font-size: 1.5rem;"></i>
                <h5 class="mb-0 fw-semibold text-dark">Demandas do Dia</h5>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-success btn-sm btn-icon" onclick="gerarImagemERedirecionar2()">
                    <i class="mdi mdi-whatsapp"></i> Enviar Report Expedição
                </button>
                <a href="{{ route('expedicao.relatorio.pdf') }}" class="btn btn-outline-danger btn-sm btn-icon" target="_blank">
                    <i class="mdi mdi-file-pdf-box"></i> PDF
                </a>
            </div>
        </div>
    </div>

    <div id="divRelatorio2">
        <div class="row g-3 mb-4">
            <div class="col-xl-3 col-sm-6">
                <div class="kpi-card">
                    <x-dashboard.card title="Qtd Total Veículos" icon="bi-file-earmark-text"
                        value="{{ $demandasHoje['resumo']['total'] ?? 0 }}" color="dark" />
                </div>
            </div>
            <div class="col-xl-3 col-sm-6">
                <div class="kpi-card">
                    <x-dashboard.card title="Qtd Veículos em Processo" icon="bi-truck"
                        value="{{ $demandasHoje['resumo']['veiculos'] ?? 0 }}" color="primary" />
                </div>
            </div>
            <div class="col-xl-3 col-sm-6">
                <div class="kpi-card">
                    <x-dashboard.card title="Peças" icon="bi-box-seam"
                        value="{{ $demandasHoje['resumo']['pecas'] ?? 0 }}" color="success" />
                </div>
            </div>
            <div class="col-xl-3 col-sm-6">
                <div class="kpi-card">
                    <x-dashboard.card title="Peso Total (kg)" icon="bi-weight"
                        value="{{ number_format($demandasHoje['resumo']['peso'] ?? 0, 1, ',', '.') }}" color="warning" />
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            {{-- Demandas por Status --}}
            <div class="col-xl-6 col-12">
                <div class="card">
                    <div class="card-header">
                        <i class="mdi mdi-chart-donut text-primary me-2"></i>
                        Demandas por Status
                    </div>
                    <div class="card-body chart-container">
                        <div id="grafico-demandas-status"></div>
                    </div>
                </div>
            </div>

            {{-- Veículos por Transportadora --}}
            <div class="col-xl-6 col-12">
                <div class="card">
                    <div class="card-header">
                        <i class="mdi mdi-truck-fast text-primary me-2"></i>
                        Veículos por Transportadora
                    </div>
                    <div class="card-body chart-container">
                        <div id="grafico-transportadoras"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ================== PRODUÇÃO ================== --}}
    <div class="section-header mt-5">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 stack-sm">
            <div class="d-flex align-items-center">
                <i class="mdi mdi-factory text-success me-2" style="font-size: 1.5rem;"></i>
                <h5 class="mb-0 fw-semibold text-dark">Produção</h5>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-success btn-sm btn-icon" onclick="gerarImagemERedirecionar()">
                    <i class="mdi mdi-whatsapp"></i> Enviar Report Kits
                </button>
                <a href="{{ route('relatorios.producao') }}" class="btn btn-outline-danger btn-sm btn-icon" target="_blank">
                    <i class="mdi mdi-file-pdf-box"></i> PDF
                </a>
            </div>
        </div>
    </div>

    <div id="divRelatorio">
        {{-- Tabela de Produção --}}
        <div class="row g-4 mb-4">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <i class="mdi mdi-clipboard-list text-primary me-2"></i>
                        Produção (Hoje)
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-3">
                                            <i class="mdi mdi-package-variant me-1"></i> Kit
                                        </th>
                                        <th class="px-4 py-3 text-center">
                                            <i class="mdi mdi-calendar-check me-1"></i> Programado
                                        </th>
                                        <th class="px-4 py-3 text-center">
                                            <i class="mdi mdi-check-circle me-1"></i> Produzido
                                        </th>
                                        <th class="px-4 py-3 text-center d-none d-md-table-cell">
                                            <i class="mdi mdi-chart-line me-1"></i> Execução (%)
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $totalProgramado = 0; $totalProduzido = 0; @endphp
                                    @foreach($kitsHoje as $kit => $info)
                                        @if($kit !== 'TOTAL')
                                            <tr class="border-bottom">
                                                <td class="px-4 py-3">
                                                    <span class="badge bg-light text-dark border fw-semibold">{{ $kit }}</span>
                                                </td>
                                                <td class="px-4 py-3 text-center">
                                                    <span class="text-dark">{{ $info['programado'] }}</span>
                                                </td>
                                                <td class="px-4 py-3 text-center">
                                                    <span class="fw-semibold text-dark">{{ $info['produzido'] }}</span>
                                                </td>
                                                <td class="px-4 py-3 text-center d-none d-md-table-cell">
                                                    @php
                                                        $exec = $info['programado'] > 0 ? round(($info['produzido'] / $info['programado']) * 100) : 0;
                                                        $badgeClass = $exec >= 100 ? 'bg-success' : ($exec >= 80 ? 'bg-warning text-dark' : 'bg-danger');
                                                    @endphp
                                                    <span class="badge {{ $badgeClass }}">{{ $exec }}%</span>
                                                </td>
                                            </tr>
                                            @php
                                                $totalProgramado += $info['programado'];
                                                $totalProduzido += $info['produzido'];
                                            @endphp
                                        @endif
                                    @endforeach
                                    <tr class="fw-bold bg-light">
                                        <td class="px-4 py-3">
                                            <span class="badge bg-dark">TOTAL</span>
                                        </td>
                                        <td class="px-4 py-3 text-center">{{ $totalProgramado }}</td>
                                        <td class="px-4 py-3 text-center">{{ $totalProduzido }}</td>
                                        <td class="px-4 py-3 text-center d-none d-md-table-cell">
                                            @php
                                                $execT = $totalProgramado > 0 ? round(($totalProduzido / $totalProgramado) * 100) : 0;
                                                $badgeClassT = $execT >= 100 ? 'bg-success' : ($execT >= 80 ? 'bg-warning text-dark' : 'bg-danger');
                                            @endphp
                                            <span class="badge {{ $badgeClassT }}">{{ $execT }}%</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Gráfico de Kits --}}
        <div class="row g-4 mb-4">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header">
                        <i class="mdi mdi-chart-bar text-primary me-2"></i>
                        Gráfico de Produção
                    </div>
                    <div class="card-body">
                        @include('components.graficos.kits', ['kitsHoje' => $kitsHoje])
                    </div>
                </div>
            </div>
        </div>

        {{-- Produtividade por Hora --}}
        <div class="row g-4 mb-4">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header">
                        <i class="mdi mdi-clock-outline text-primary me-2"></i>
                        Produtividade por Hora (Hoje)
                    </div>
                    <div class="card-body chart-container">
                        <div id="grafico-produtividade-hora"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Projeção e Velocidade --}}
        <div class="row g-4 mb-4">
            <div class="col-xl-9 col-12">
                <div class="card h-100">
                    <div class="card-header">
                        <i class="mdi mdi-chart-timeline-variant text-primary me-2"></i>
                        Projeção de Produtividade
                    </div>
                    <div class="card-body">
                        <canvas id="graficoProdutividade" height="120"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-12">
                <div class="card h-100">
                    <div class="card-header text-center">
                        <i class="mdi mdi-speedometer text-primary me-2"></i>
                        Velocidade de Produção
                    </div>
                    <div class="card-body gauge-container">
                        <canvas id="gaugeVelocidade" width="180" height="180"></canvas>
                        <div id="gaugeValor" class="gauge-value"></div>
                        <small id="gaugeMeta" class="gauge-meta text-center"></small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ================== RESUMO DO DIA ================== --}}
    <div class="row g-4 mt-4">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <i class="mdi mdi-file-document-outline text-primary me-2"></i>
                    Resumo do Dia
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="px-4 py-3">
                                        <i class="mdi mdi-tag me-1"></i> Setor
                                    </th>
                                    <th class="px-4 py-3 text-end">
                                        <i class="mdi mdi-counter me-1"></i> Quantidade (peças)
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($resumoDia as $setor => $qtd)
                                    <tr class="border-bottom">
                                        <td class="px-4 py-3">
                                            <span class="badge bg-primary bg-opacity-10 text-primary text-uppercase">
                                                {{ str_replace('_', ' ', $setor) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-end">
                                            <span class="fw-semibold text-dark">{{ number_format($qtd, 0, ',', '.') }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

{{-- ================== SCRIPTS ================== --}}
@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    <script>
        // ========== Funções genéricas de gráficos ==========
        function renderBarChart(id, label, nomes, quantidades, color) {
            const options = {
                chart: { 
                    type: 'bar', 
                    height: 300,
                    toolbar: { show: false },
                    fontFamily: 'inherit'
                },
                series: [{ name: label, data: quantidades }],
                xaxis: { 
                    categories: nomes,
                    labels: { style: { colors: '#64748b', fontSize: '12px' } }
                },
                yaxis: {
                    labels: { style: { colors: '#64748b', fontSize: '12px' } }
                },
                colors: [color],
                plotOptions: { 
                    bar: { 
                        horizontal: false, 
                        columnWidth: '50%', 
                        borderRadius: 6,
                        dataLabels: { position: 'top' }
                    } 
                },
                dataLabels: {
                    enabled: true,
                    offsetY: -20,
                    style: { fontSize: '11px', colors: ['#334155'] }
                },
                grid: { borderColor: '#eef1f5' },
                tooltip: {
                    y: { formatter: (val) => val }
                }
            };
            new ApexCharts(document.querySelector(id), options).render();
        }

        function renderGrafico(id, label, data, dias, color) {
            const options = {
                chart: { 
                    type: 'line', 
                    height: 300,
                    toolbar: { show: false },
                    fontFamily: 'inherit'
                },
                series: [{ name: label, data: data }],
                xaxis: { 
                    categories: dias, 
                    title: { text: 'Dia do Mês', style: { color: '#64748b' } },
                    labels: { style: { colors: '#64748b', fontSize: '12px' } }
                },
                yaxis: { 
                    title: { text: 'Qtd.', style: { color: '#64748b' } },
                    labels: { style: { colors: '#64748b', fontSize: '12px' } }
                },
                stroke: { curve: 'smooth', width: 3 },
                colors: [color],
                markers: { size: 5, hover: { size: 7 } },
                grid: { borderColor: '#eef1f5' },
                tooltip: {
                    y: { formatter: (val) => val }
                }
            };
            new ApexCharts(document.querySelector(id), options).render();
        }

        document.addEventListener("DOMContentLoaded", function() {
            // Ranking (se existir)
            @if(isset($rankingOperadores))
            renderBarChart('#grafico-ranking-armazenagem', 'Armazenagem',
                @json(collect($rankingOperadores['armazenagem'])->pluck('nome')),
                @json(collect($rankingOperadores['armazenagem'])->pluck('total')), '#3b82f6');

            renderBarChart('#grafico-ranking-separacao', 'Separação',
                @json(collect($rankingOperadores['separacao'])->pluck('nome')),
                @json(collect($rankingOperadores['separacao'])->pluck('total')), '#10b981');
            @endif

            // Gráficos mensais (se existir)
            @if(isset($dadosMensais))
            renderGrafico('#grafico-armazenagem', 'Armazenagem', @json($dadosMensais['armazenagem']),
                @json($dadosMensais['dias']), '#3b82f6');
            renderGrafico('#grafico-separacao', 'Separação', @json($dadosMensais['separacao']),
                @json($dadosMensais['dias']), '#10b981');
            renderGrafico('#grafico-paletes', 'Paletes', @json($dadosMensais['paletes']),
                @json($dadosMensais['dias']), '#f97316');
            @endif

            // ========== Demandas por Status (Donut) ==========
            new ApexCharts(document.querySelector("#grafico-demandas-status"), {
                chart: { 
                    type: 'donut', 
                    height: 300,
                    fontFamily: 'inherit'
                },
                series: @json(collect($demandasHoje['por_status'])->pluck('total')),
                labels: @json(collect($demandasHoje['por_status'])->pluck('status')),
                colors: ['#0d6efd','#ffc107','#198754','#dc3545','#6610f2','#20c997'],
                legend: { 
                    position: 'bottom',
                    labels: { colors: '#334155' }
                },
                dataLabels: {
                    enabled: true,
                    formatter: (val) => `${Math.round(val)}%`,
                    style: { fontSize: '12px' }
                },
                plotOptions: {
                    pie: {
                        donut: {
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'Total',
                                    color: '#334155'
                                }
                            }
                        }
                    }
                },
                tooltip: {
                    y: { formatter: (val) => val }
                }
            }).render();

            // ========== Veículos por Transportadora ==========
            new ApexCharts(document.querySelector("#grafico-transportadoras"), {
                chart: { 
                    type: 'bar', 
                    height: 300,
                    toolbar: { show: false },
                    fontFamily: 'inherit'
                },
                series: [{ 
                    name: 'Veículos', 
                    data: @json(collect($demandasHoje['por_transportadora'])->pluck('total')) 
                }],
                xaxis: { 
                    categories: @json(collect($demandasHoje['por_transportadora'])->pluck('transportadora')),
                    labels: { 
                        style: { colors: '#64748b', fontSize: '11px' },
                        rotate: -45
                    }
                },
                yaxis: {
                    labels: { style: { colors: '#64748b', fontSize: '12px' } }
                },
                colors: ['#3b82f6'],
                plotOptions: {
                    bar: {
                        columnWidth: '45%',
                        borderRadius: 6,
                        dataLabels: { position: 'top' }
                    }
                },
                dataLabels: {
                    enabled: true,
                    offsetY: -20,
                    style: { fontSize: '11px', colors: ['#334155'] }
                },
                grid: { borderColor: '#eef1f5' },
                tooltip: {
                    y: { formatter: (val) => val }
                }
            }).render();

            // ========== Produtividade por Hora ==========
            const horas = @json(collect($produtividadeHora)->pluck('hora'));
            const produzidos = @json(collect($produtividadeHora)->pluck('produzido'));
            new ApexCharts(document.querySelector("#grafico-produtividade-hora"), {
                chart: { 
                    type: 'line', 
                    height: 300,
                    toolbar: { show: false },
                    fontFamily: 'inherit'
                },
                series: [
                    { name: 'Produzido', type: 'column', data: produzidos },
                    { name: 'Meta', type: 'line', data: new Array(horas.length).fill(94) }
                ],
                xaxis: { 
                    categories: horas, 
                    title: { text: 'Hora do Dia', style: { color: '#64748b' } },
                    labels: { style: { colors: '#64748b', fontSize: '12px' } }
                },
                yaxis: { 
                    title: { text: 'Qtd Produzida', style: { color: '#64748b' } }, 
                    min: 0,
                    labels: { style: { colors: '#64748b', fontSize: '12px' } }
                },
                colors: ['#0d6efd', '#dc3545'],
                stroke: { curve: 'smooth', width: [0, 3] },
                plotOptions: { 
                    bar: { 
                        columnWidth: '40%', 
                        borderRadius: 4 
                    } 
                },
                dataLabels: {
                    enabled: true,
                    enabledOnSeries: [0],
                    formatter: function (val) { return val; },
                    style: { colors: ['#334155'], fontSize: '11px' },
                    offsetY: -10
                },
                grid: { borderColor: '#eef1f5' },
                legend: {
                    labels: { colors: '#334155' }
                },
                markers: {
                    size: [0, 4]
                },
                tooltip: {
                    y: { formatter: (val) => val }
                }
            }).render();
        });

        // ========== Exportar imagem e abrir WhatsApp ==========
        function gerarImagemERedirecionar() {
            const div = document.getElementById("divRelatorio");
            html2canvas(div, { scale: 2, backgroundColor: '#ffffff' }).then(canvas => {
                const agora = new Date();
                const dataHora = agora.toLocaleString('sv-SE').replace(' ', '_').replace(/:/g, '-');
                const nomeArquivo = `report-kit_${dataHora}.png`;

                const link = document.createElement('a');
                link.href = canvas.toDataURL("image/png");
                link.download = nomeArquivo;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);

                setTimeout(() => {
                    window.open('https://chat.whatsapp.com/FQl5hULSOqy7RCXL5PenLd', '_blank');
                }, 1000);
            });
        }
        
        function gerarImagemERedirecionar2() {
            const div = document.getElementById("divRelatorio2");
            html2canvas(div, { scale: 2, backgroundColor: '#ffffff' }).then(canvas => {
                const agora = new Date();
                const dataHora = agora.toLocaleString('sv-SE').replace(' ', '_').replace(/:/g, '-');
                const nomeArquivo = `report-expedicao_${dataHora}.png`;

                const link = document.createElement('a');
                link.href = canvas.toDataURL("image/png");
                link.download = nomeArquivo;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);

                setTimeout(() => {
                    window.open('https://chat.whatsapp.com/JpF5jqoVoBZF678kPHJz2o', '_blank');
                }, 1000);
            });
        }
    </script>

    {{-- ========== Gráfico de Projeção de Produtividade (Chart.js) ========== --}}
    <script>
    document.addEventListener("DOMContentLoaded", function () {
        let ctx = document.getElementById('graficoProdutividade').getContext('2d');

        let chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [
                    {
                        label: 'Produção Real',
                        data: [],
                        borderColor: '#0d6efd',
                        backgroundColor: 'rgba(13, 110, 253, 0.1)',
                        borderWidth: 3,
                        tension: 0.3,
                        fill: true,
                        pointRadius: 3,
                        pointHoverRadius: 6
                    },
                    {
                        label: 'Curva Ideal',
                        data: [],
                        borderColor: '#198754',
                        borderDash: [5, 5],
                        borderWidth: 2,
                        tension: 0.3,
                        fill: false,
                        pointRadius: 0
                    },
                    {
                        label: 'Projeção Corrigida',
                        data: [],
                        borderColor: '#fd7e14',
                        borderDash: [10, 5],
                        borderWidth: 2,
                        tension: 0.3,
                        fill: false,
                        pointRadius: 0
                    },
                    {
                        label: 'Meta',
                        data: [],
                        borderColor: '#dc3545',
                        borderDash: [2, 2],
                        borderWidth: 2,
                        fill: false,
                        pointRadius: 0
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                animation: {
                    duration: 400,
                    easing: 'easeOutCubic'
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                return ctx.dataset.label + ': ' + ctx.formattedValue;
                            }
                        }
                    },
                    legend: {
                        labels: {
                            color: '#334155',
                            font: { size: 12 }
                        }
                    }
                },
                scales: {
                    y: { 
                        beginAtZero: true,
                        grid: { color: '#eef1f5' },
                        ticks: { color: '#64748b' }
                    },
                    x: {
                        grid: { color: '#eef1f5' },
                        ticks: { color: '#64748b' }
                    }
                }
            }
        });

        async function atualizarGrafico() {
            try {
                const resp = await fetch("{{ url('/api/dashboard/projecao-produtividade') }}");
                const data = await resp.json();

                // Labels (horas)
                chart.data.labels = data.curvaIdeal.map(c => c.hora);

                // Produção real
                chart.data.datasets[0].data = data.apontamentos.map(a => a.acumulado);

                // Curva ideal
                chart.data.datasets[1].data = data.curvaIdeal.map(c => c.valor);

                // Projeção corrigida
                chart.data.datasets[2].data = data.projecaoCorrigida.map(p => p.valor);

                // Meta (linha horizontal)
                chart.data.datasets[3].data = chart.data.labels.map(() => data.meta);

                chart.update();
            } catch (err) {
                console.error("Erro ao atualizar gráfico:", err);
            }
        }

        // Atualiza a cada 5 minutos
        setInterval(atualizarGrafico, 300000);
        atualizarGrafico();
    });
    </script>

    {{-- ========== Gauge de Velocidade de Produção ========== --}}
    <script>
    document.addEventListener("DOMContentLoaded", async function () {
        const ctx = document.getElementById('gaugeVelocidade').getContext('2d');

        try {
            const resp = await fetch("{{ url('/api/dashboard/projecao-produtividade') }}");
            const data = await resp.json();

            const velAtual = data.velocidadeAtual || 0;
            const velNecessaria = data.velocidadeNecessaria || 0;
            const status = data.statusProdutividade; // "ok", "atencao" ou "baixo"

            // Define cor com base no status
            let cor;
            switch (status) {
                case 'ok': cor = '#28a745'; break;     // verde
                case 'atencao': cor = '#ffc107'; break; // amarelo
                default: cor = '#dc3545';              // vermelho
            }

            // Percentual de progresso
            let progresso = 0;
            if (velNecessaria > 0) {
                progresso = Math.min(velAtual / velNecessaria, 1);
            } else if (data.produzido >= data.meta) {
                progresso = 1; // já atingiu a meta
            }

            // Renderiza gauge
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    datasets: [{
                        data: [progresso * 100, 100 - (progresso * 100)],
                        backgroundColor: [cor, '#e5e7eb'],
                        borderWidth: 0
                    }]
                },
                options: {
                    rotation: -90,      // inicia em cima
                    circumference: 180, // semi-círculo
                    cutout: '75%',
                    plugins: {
                        legend: { display: false },
                        tooltip: { enabled: false }
                    }
                }
            });

            // Texto abaixo do gauge
            if (velNecessaria === 0 && data.produzido >= data.meta) {
                document.getElementById('gaugeValor').innerHTML = "🎉 Meta atingida!";
                document.getElementById('gaugeMeta').innerHTML = `${data.produzido}/${data.meta} paletes`;
            } else {
                document.getElementById('gaugeValor').innerHTML = `📊 ${velAtual.toFixed(2)} paletes/h`;
                document.getElementById('gaugeMeta').innerHTML = `🎯 Meta: ${velNecessaria.toFixed(2)} paletes/h<br>(${Math.round(progresso * 100)}%)`;
            }

        } catch (err) {
            console.error("Erro ao carregar gauge:", err);
            document.getElementById('gaugeValor').innerHTML = "Erro ao carregar";
            document.getElementById('gaugeMeta').innerHTML = "";
        }
    });
    </script>

@endsection
