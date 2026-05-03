@extends('layouts.app')

@section('content')
<div class="container-fluid">
<a href="{{ route('expedicao.relatorio.pdf') }}" class="btn btn-danger" target="_blank">
    <i class="bi bi-filetype-pdf"></i> Gerar Relatório PDF
</a>
    <!-- KPIs Mensais -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">TMSep (média)</h6>
                    <h3>{{ $temposMensal ? $temposMensal->tmsep : '-' }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">TMConf (média)</h6>
                    <h3>{{ $temposMensal ? $temposMensal->tmconf : '-' }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">TMCarr (média)</h6>
                    <h3>{{ $temposMensal ? $temposMensal->tmcarr : '-' }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">TMGP (média)</h6>
                    <h3>{{ $temposMensal ? $temposMensal->tmgp : '-' }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- KPIs de Volume -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">Volume (Qtd)</h6>
                    <h3>{{ $volumeMensal ? number_format($volumeMensal->qtd_total) : 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">Peso Total</h6>
                    <h3>{{ $volumeMensal ? number_format($volumeMensal->peso_total, 0, ',', '.') : 0 }} kg</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">Demandas</h6>
                    <h3>{{ $volumeMensal ? $volumeMensal->qtd_demandas : 0 }}</h3>
                </div>
            </div>
        </div>
    </div>

    <h4 class="mb-4">Dashboard Expedição</h4>

    <!-- Volume expedido -->
    <div class="card mb-4">
        <div class="card-body">
            <h5>Volume expedido por dia</h5>
            <canvas id="graficoVolume"></canvas>
        </div>
    </div>

    <!-- FOs liberadas -->
    <div class="card mb-4">
        <div class="card-body">
            <h5>FOs liberadas por dia</h5>
            <canvas id="graficoFOs"></canvas>
        </div>
    </div>
    
    <!-- Volume por Transportadora -->
<div class="card mb-4">
    <div class="card-body">
        <h5>Volume por Transportadora (Mensal)</h5>
        <canvas id="graficoTransportadora"></canvas>
    </div>
</div>

<!-- Volume por Motorista -->
<div class="card mb-4">
    <div class="card-body">
        <h5>Volume por Motorista (Mensal)</h5>
        <canvas id="graficoMotorista"></canvas>
    </div>
</div>

    <!-- Tempos médios -->
    <div class="card mb-4">
        <div class="card-body">
            <h5>Tempos médios por etapa</h5>
            <canvas id="graficoTempos"></canvas>
        </div>
    </div>
    
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
<script>
    // Dados do PHP para JS
    const volume = @json($volume);
    const fos = @json($fosLiberadas);
    const tempos = @json($tempos); // já vem formatados (ex: "2h 15min")

    // Volume expedido
    new Chart(document.getElementById('graficoVolume'), {
        type: 'bar',
        data: {
            labels: volume.map(v => v.dia),
            datasets: [
                { label: 'Quantidade', data: volume.map(v => v.qtd), backgroundColor: '#007bff' },
                { label: 'Peso (kg)', data: volume.map(v => v.peso), backgroundColor: '#28a745' }
            ]
        },
        options: {
            plugins: {
                datalabels: {
                    anchor: 'end',
                    align: 'end',
                    formatter: (val) => val ? val.toLocaleString() : '',
                    color: '#000',
                    font: { weight: 'bold' }
                }
            }
        },
        plugins: [ChartDataLabels]
    });

    // FOs liberadas
    new Chart(document.getElementById('graficoFOs'), {
        type: 'line',
        data: {
            labels: fos.map(f => f.dia),
            datasets: [
                { label: 'FOs Liberadas', data: fos.map(f => f.total), borderColor: '#ff5722', fill: false }
            ]
        },
        options: {
            plugins: {
                datalabels: {
                    anchor: 'end',
                    align: 'top',
                    formatter: (val) => val ? val.toLocaleString() : '',
                    color: '#000',
                    font: { weight: 'bold' }
                }
            }
        },
        plugins: [ChartDataLabels]
    });

    // Tempos médios (já formatados como string)
    new Chart(document.getElementById('graficoTempos'), {
        type: 'line',
        data: {
            labels: tempos.map(t => t.dia),
            datasets: [
                { label: 'Separação', data: tempos.map(t => t.tmsep), borderColor: '#007bff', fill: false },
                { label: 'Conferência', data: tempos.map(t => t.tmconf), borderColor: '#ffc107', fill: false },
                { label: 'Carregamento', data: tempos.map(t => t.tmcarr), borderColor: '#28a745', fill: false },
                { label: 'Geral', data: tempos.map(t => t.tmgp), borderColor: '#dc3545', fill: false }
            ]
        },
        options: {
            plugins: {
                datalabels: {
                    anchor: 'end',
                    align: 'top',
                    color: '#000',
                    font: { weight: 'bold' }
                }
            },
            parsing: false, // necessário porque os dados são strings
        },
        plugins: [ChartDataLabels]
    });
    
// Volume por Transportadora (barra horizontal)
new Chart(document.getElementById('graficoTransportadora'), {
    type: 'bar',
    data: {
        labels: @json($volumeTransportadora->pluck('transportadora')),
        datasets: [
            { 
                label: 'Quantidade', 
                data: @json($volumeTransportadora->pluck('qtd_total')), 
                backgroundColor: '#007bff' 
            },
            { 
                label: 'Peso (kg)', 
                data: @json($volumeTransportadora->pluck('peso_total')), 
                backgroundColor: '#28a745' 
            }
        ]
    },
    options: {
        indexAxis: 'y', // 👈 transforma em barra horizontal
        responsive: true,
        plugins: {
            datalabels: {
                anchor: 'end',
                align: 'right',
                color: '#000',
                font: { weight: 'bold' },
                formatter: (val) => val ? val.toLocaleString() : ''
            }
        },
        scales: {
            x: {
                beginAtZero: true
            }
        }
    },
    plugins: [ChartDataLabels]
});


// Volume por Motorista (barra horizontal)
new Chart(document.getElementById('graficoMotorista'), {
    type: 'bar',
    data: {
        labels: @json($volumeMotorista->pluck('motorista')),
        datasets: [
            { 
                label: 'Quantidade', 
                data: @json($volumeMotorista->pluck('qtd_total')), 
                backgroundColor: '#ffc107' 
            },
            { 
                label: 'Peso (kg)', 
                data: @json($volumeMotorista->pluck('peso_total')), 
                backgroundColor: '#17a2b8' 
            }
        ]
    },
    options: {
        indexAxis: 'y', // 👈 transforma em barra horizontal
        responsive: true,
        plugins: {
            datalabels: {
                anchor: 'end',
                align: 'right',
                color: '#000',
                font: { weight: 'bold' },
                formatter: (val) => val ? val.toLocaleString() : ''
            }
        },
        scales: {
            x: {
                beginAtZero: true
            }
        }
    },
    plugins: [ChartDataLabels]
});


</script>
@endpush
