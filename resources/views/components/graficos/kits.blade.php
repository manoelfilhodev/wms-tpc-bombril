@once
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const kitsHoje = @json($kitsHoje);
    const kitLabels = Object.keys(kitsHoje).filter(k => k !== 'TOTAL');
    const fontColor = '#aaa';

    if (kitLabels.length > 0) {
        new Chart(document.getElementById('chartKits'), {
            type: 'bar',
            data: {
                labels: kitLabels,
                datasets: [
                    {
                        type: 'bar',
                        label: 'Programado',
                        data: kitLabels.map(k => kitsHoje[k].programado),
                        backgroundColor: '#2980b9',
                        yAxisID: 'y',
                    },
                    {
                        type: 'bar',
                        label: 'Produzido',
                        data: kitLabels.map(k => kitsHoje[k].produzido),
                        backgroundColor: '#e67e22',
                        yAxisID: 'y',
                    },
                    {
                        type: 'line',
                        label: '% Execução',
                        data: kitLabels.map(k => {
                            const kit = kitsHoje[k];
                            if (kit.programado === 0) return 0;
                            return ((kit.produzido / kit.programado) * 100).toFixed(1);
                        }),
                        borderColor: '#27ae60',
                        backgroundColor: '#27ae60',
                        yAxisID: 'y1',
                        tension: 0.3,
                        datalabels: {
                            formatter: value => value + '%',
                            anchor: 'end',
                            align: 'top',
                            color: fontColor,
                            font: { weight: 'bold', size: 10 }
                        }
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: {
                        labels: { color: fontColor },
                        padding: 20
                    },
                    datalabels: {
                        color: fontColor,
                        font: { weight: 'bold', size: 10 },
                        anchor: 'end',
                        align: 'top',
                        display: ctx => ctx.dataset.type !== 'line',
                        formatter: (value) => value
                    }
                },
                layout: {
                    padding: { top: 20, bottom: 10, left: 10, right: 10 }
                },
                scales: {
                    x: { ticks: { color: fontColor } },
                    y: {
                        position: 'left',
                        title: { display: true, text: 'Quantidade', color: fontColor },
                        ticks: { color: fontColor },
                        beginAtZero: true,
                        suggestedMax: 1.2 * Math.max(...kitLabels.map(k => kitsHoje[k].programado))
                    },
                    y1: {
                        position: 'right',
                        title: { display: true, text: '% Execução', color: fontColor },
                        ticks: {
                            color: fontColor,
                            callback: val => val + '%'
                        },
                        beginAtZero: true,
                        suggestedMax: 110,
                        grid: { drawOnChartArea: false }
                    }
                }
            },
            plugins: [ChartDataLabels]
        });
    }
});
</script>
@endpush
@endonce



<div class="bg-gray-100 rounded-lg p-4 shadow">
    <!--<h3 class="text-white text-lg font-semibold mb-2">Produção de Kits - Hoje</h3>-->
    <canvas id="chartKits" height="300"></canvas>
</div>
