@extends('layouts.tv')

@section('content')
<style>
  body { background: #070b14; color: #e8eefc; font-family: "Segoe UI", sans-serif; overflow: hidden; }
  .tv-header { height: 10vh; display:flex; align-items:center; justify-content:center; background:#05070d; border-bottom:1px solid rgba(255,255,255,.08); }
  .tv-header h1 { margin:0; font-size:2rem; letter-spacing:.5px; }
  #carousel { height: 90vh; position: relative; }
  .slide { display:none; height:100%; padding:16px 24px; }
  .slide.active { display:block; }
  .grid { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-bottom:14px; }
  .kpi { background:linear-gradient(180deg,#111a2d,#0b1322); border:1px solid rgba(255,255,255,.07); border-radius:12px; padding:14px; }
  .kpi .label { color:#9db0d2; font-size:.9rem; }
  .kpi .value { font-size:2rem; font-weight:700; }
  .panel { background:linear-gradient(180deg,#10192b,#0a1220); border:1px solid rgba(255,255,255,.07); border-radius:12px; padding:12px; height: calc(100% - 10px); }
  .panel h3 { margin:2px 0 10px; font-size:1.1rem; color:#c9d7f2; }
  .panel canvas { height: 60vh !important; }
  .mini-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:14px; height:70vh; }
  .mini-grid .panel canvas { height: 28vh !important; }
  .slide-nav { position:absolute; top:50%; transform:translateY(-50%); background:rgba(0,0,0,.45); color:#fff; border:0; font-size:2rem; width:48px; height:64px; cursor:pointer; }
  #prev-slide { left:0; border-radius:0 8px 8px 0; }
  #next-slide { right:0; border-radius:8px 0 0 8px; }
</style>

<div class="tv-header">
  <h1>Painel TV • Separação Picking</h1>
</div>

<div id="carousel">
  <section class="slide active">
    <div class="grid">
      <div class="kpi"><div class="label">A separar</div><div class="value">{{ $status['a_separar'] }}</div></div>
      <div class="kpi"><div class="label">Separando</div><div class="value">{{ $status['separando'] }}</div></div>
      <div class="kpi"><div class="label">Separado parcial</div><div class="value">{{ $status['separado_parcial'] }}</div></div>
      <div class="kpi"><div class="label">Separado completo</div><div class="value">{{ $status['separado_completo'] }}</div></div>
    </div>
    <div class="mini-grid">
      <div class="panel"><h3>Status geral</h3><canvas id="miniStatus"></canvas></div>
      <div class="panel"><h3>Top separadores (7 dias)</h3><canvas id="miniRanking"></canvas></div>
      <div class="panel"><h3>Finalizações no mês</h3><canvas id="miniMes"></canvas></div>
      <div class="panel"><h3>Volume por turno</h3><canvas id="miniTurno"></canvas></div>
    </div>
  </section>

  <section class="slide">
    <div class="panel"><h3>Top separadores (quantidade distribuída)</h3><canvas id="chartRanking"></canvas></div>
  </section>

  <section class="slide">
    <div class="panel"><h3>Top pickers (SKUs separados)</h3><canvas id="chartRankingSkus"></canvas></div>
  </section>

  <section class="slide">
    <div class="panel"><h3>Finalizações por dia no mês (completo x parcial)</h3><canvas id="chartMes"></canvas></div>
  </section>

  <section class="slide">
    <div class="panel"><h3>Distribuição por turno + tempo médio geral: {{ number_format($tempoMedioMin,1,',','.') }} min</h3><canvas id="chartTurno"></canvas></div>
  </section>

  <button id="prev-slide" class="slide-nav">&#8249;</button>
  <button id="next-slide" class="slide-nav">&#8250;</button>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
<script>
const statusData = {!! json_encode([
  (int) ($status['a_separar'] ?? 0),
  (int) ($status['separando'] ?? 0),
  (int) ($status['separado_parcial'] ?? 0),
  (int) ($status['separado_completo'] ?? 0),
]) !!};
const rankingLabels = {!! json_encode(collect($ranking)->pluck('nome')->values()->all()) !!};
const rankingValues = {!! json_encode(collect($ranking)->pluck('total')->map(function ($v) { return (int) $v; })->values()->all()) !!};
const rankingSkusLabels = {!! json_encode(collect($rankingSkus)->pluck('nome')->values()->all()) !!};
const rankingSkusValues = {!! json_encode(collect($rankingSkus)->pluck('total')->map(function ($v) { return (int) $v; })->values()->all()) !!};
const diasMes = @json($diasMes);
const separacoesDia = @json($separacoesDia);
const parciaisDia = @json($parciaisDia);
const turnoLabels = @json($turnoLabels);
const turnoValues = @json($turnoValues);

const baseOpts = {
  responsive: true,
  maintainAspectRatio: false,
  animation: false,
  plugins: {
    legend: { position: 'bottom', labels: { color: '#d3ddf4' } },
    datalabels: {
      color: '#f8fbff',
      font: { weight: '700', size: 11 },
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
    x: { title: { display: true, text: 'Período/Operador', color: '#9db0d2' }, ticks: { color: '#c2d0ed' }, grid: { color: 'rgba(255,255,255,.08)' } },
    y: { title: { display: true, text: 'Quantidade', color: '#9db0d2' }, ticks: { color: '#c2d0ed' }, grid: { color: 'rgba(255,255,255,.08)' }, beginAtZero: true }
  }
};

function mk(id, cfg){ new Chart(document.getElementById(id), { ...cfg, plugins: [ChartDataLabels] }); }

mk('miniStatus', {
  type:'doughnut',
  data:{ labels:['A separar','Separando','Parcial','Completo'], datasets:[{ data:statusData, backgroundColor:['#38bdf8','#f59e0b','#fb7185','#22c55e'] }] },
  options:{
    responsive:true,
    maintainAspectRatio:false,
    animation:false,
    plugins:{
      legend:{ position:'bottom', labels:{ color:'#d3ddf4' }},
      datalabels:{
        color:'#ffffff',
        formatter: (value, ctx) => {
          const total = ctx.dataset.data.reduce((a,b) => a + b, 0) || 1;
          const pct = ((value / total) * 100).toFixed(0);
          return `${value} (${pct}%)`;
        }
      }
    }
  }
});
mk('miniRanking', { type:'bar', data:{ labels:rankingLabels, datasets:[{ label:'Peças', data:rankingValues, backgroundColor:'#60a5fa' }] }, options:baseOpts });
mk('miniMes', { type:'line', data:{ labels:diasMes, datasets:[{ label:'Finalizadas', data:separacoesDia, borderColor:'#22c55e', backgroundColor:'rgba(34,197,94,.2)', fill:true, tension:.25 },{ label:'Parciais', data:parciaisDia, borderColor:'#fb7185', backgroundColor:'rgba(251,113,133,.2)', fill:true, tension:.25 }] }, options:baseOpts });
mk('miniTurno', { type:'bar', data:{ labels:turnoLabels, datasets:[{ label:'DTs', data:turnoValues, backgroundColor:['#38bdf8','#818cf8','#f59e0b'] }] }, options:baseOpts });

mk('chartRanking', {
  type:'bar',
  data:{
    labels:rankingLabels,
    datasets:[{ label:'Peças distribuídas', data:rankingValues, backgroundColor:'#3b82f6' }]
  },
  options:{
    ...baseOpts,
    indexAxis: 'y',
    scales: {
      x: {
        ...baseOpts.scales.y,
        title: { display: true, text: 'Peças distribuídas', color: '#9db0d2' }
      },
      y: {
        ...baseOpts.scales.x,
        title: { display: true, text: 'Separador', color: '#9db0d2' }
      }
    },
    plugins: {
      ...baseOpts.plugins,
      datalabels: {
        color:'#f8fbff',
        font:{ weight:'700', size:12 },
        anchor:'end',
        align:'right',
        formatter:(value) => value
      }
    }
  }
});
mk('chartRankingSkus', {
  type:'bar',
  data:{
    labels:rankingSkusLabels,
    datasets:[{ label:'SKUs separados', data:rankingSkusValues, backgroundColor:'#22c55e' }]
  },
  options:{
    ...baseOpts,
    indexAxis: 'y',
    scales: {
      x: {
        ...baseOpts.scales.y,
        title: { display: true, text: 'SKUs separados', color: '#9db0d2' }
      },
      y: {
        ...baseOpts.scales.x,
        title: { display: true, text: 'Picker', color: '#9db0d2' }
      }
    },
    plugins: {
      ...baseOpts.plugins,
      datalabels: {
        color:'#f8fbff',
        font:{ weight:'700', size:12 },
        anchor:'end',
        align:'right',
        formatter:(value) => value
      }
    }
  }
});
mk('chartMes', { type:'line', data:{ labels:diasMes, datasets:[{ label:'Finalizadas', data:separacoesDia, borderColor:'#22c55e', backgroundColor:'rgba(34,197,94,.2)', fill:true, tension:.25 },{ label:'Parciais', data:parciaisDia, borderColor:'#fb7185', backgroundColor:'rgba(251,113,133,.2)', fill:true, tension:.25 }] }, options:baseOpts });
mk('chartTurno', {
  type:'bar',
  data:{
    labels:turnoLabels,
    datasets:[{
      label:'DTs iniciadas',
      data:turnoValues,
      backgroundColor:['#38bdf8','#818cf8','#f59e0b'],
      maxBarThickness: 110
    }]
  },
  options:{
    ...baseOpts,
    plugins: {
      ...baseOpts.plugins,
      legend: { display: true, position: 'bottom', labels: { color: '#d3ddf4' } },
      datalabels: {
        color:'#f8fbff',
        font:{ weight:'700', size:14 },
        anchor:'end',
        align:'top',
        formatter:(value) => value > 0 ? value : ''
      }
    },
    scales: {
      x: {
        ...baseOpts.scales.x,
        title: { display: true, text: 'Turno', color: '#9db0d2' }
      },
      y: {
        ...baseOpts.scales.y,
        title: { display: true, text: 'DTs iniciadas', color: '#9db0d2' },
        ticks: { color: '#c2d0ed', precision: 0, stepSize: 1 }
      }
    }
  }
});

const slides = document.querySelectorAll('.slide');
let i = 0;
function show(n){ slides.forEach(s=>s.classList.remove('active')); slides[n].classList.add('active'); }
function next(){ i = (i+1)%slides.length; show(i); }
function prev(){ i = (i-1+slides.length)%slides.length; show(i); }
document.getElementById('next-slide').addEventListener('click', next);
document.getElementById('prev-slide').addEventListener('click', prev);
setInterval(next, 25000);
</script>
@endsection
