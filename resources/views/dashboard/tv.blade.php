@extends('layouts.tv')

@section('content')
<style>
  :root {
    --tv-bg: #05070c;
    --tv-surface: rgba(15, 23, 42, .88);
    --tv-surface-strong: rgba(17, 24, 39, .96);
    --tv-border: rgba(148, 163, 184, .16);
    --tv-text: #f8fafc;
    --tv-muted: #9ca3af;
    --tv-soft: #cbd5e1;
    --tv-accent: #ef4444;
  }

  * { box-sizing: border-box; }
  body {
    background:
      radial-gradient(circle at 15% 0%, rgba(239, 68, 68, .12), transparent 28%),
      linear-gradient(180deg, #070b14 0%, var(--tv-bg) 52%, #03050a 100%);
    color: var(--tv-text);
    font-family: "Segoe UI", sans-serif;
    overflow: hidden;
  }

  .tv-header {
    height: 11vh;
    min-height: 92px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap: 24px;
    padding: 18px 32px;
    background: rgba(3, 5, 10, .88);
    border-bottom:1px solid var(--tv-border);
  }
  .tv-title-kicker {
    color: var(--tv-muted);
    font-size: .8rem;
    font-weight: 800;
    letter-spacing: .14em;
    text-transform: uppercase;
  }
  .tv-header h1 {
    margin: 3px 0 0;
    color: var(--tv-text);
    font-size: clamp(1.7rem, 2.5vw, 2.6rem);
    font-weight: 800;
    letter-spacing: 0;
  }
  .tv-header-meta {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    justify-content: flex-end;
  }
  .tv-pill {
    border: 1px solid var(--tv-border);
    background: rgba(15, 23, 42, .72);
    border-radius: 999px;
    color: var(--tv-soft);
    font-size: .9rem;
    font-weight: 800;
    padding: 9px 13px;
    white-space: nowrap;
  }
  .tv-pill strong { color: #fff; }
  .tv-live {
    display: inline-flex;
    align-items: center;
    gap: 8px;
  }
  .tv-live::before {
    content: "";
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: var(--tv-accent);
    box-shadow: 0 0 0 4px rgba(239, 68, 68, .14);
  }

  #carousel { height: 89vh; position: relative; }
  .slide { display:none; height:100%; padding:18px 24px 24px; }
  .slide.active { display:block; }
  .grid { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-bottom:14px; }
  .kpi {
    min-height: 116px;
    background: linear-gradient(180deg, rgba(17, 24, 39, .96), rgba(8, 13, 24, .96));
    border: 1px solid var(--tv-border);
    border-radius: 8px;
    padding: 16px 18px;
    box-shadow: 0 16px 38px rgba(0, 0, 0, .22);
  }
  .kpi .label {
    color: var(--tv-soft);
    font-size: .88rem;
    font-weight: 800;
    letter-spacing: .04em;
    text-transform: uppercase;
  }
  .kpi .value {
    margin-top: 4px;
    color: #fff;
    font-size: clamp(2rem, 3vw, 3.2rem);
    font-weight: 900;
    line-height: 1;
  }
  .kpi .backlog { margin-top:10px; color:var(--tv-muted); font-size:.86rem; font-weight: 700; }
  .kpi .backlog strong { color:#e5e7eb; font-size:1rem; }
  .panel {
    position: relative;
    overflow: hidden;
    background: linear-gradient(180deg, var(--tv-surface), rgba(8, 13, 24, .94));
    border: 1px solid var(--tv-border);
    border-radius: 8px;
    padding: 18px;
    height: calc(100% - 10px);
    box-shadow: 0 18px 48px rgba(0, 0, 0, .28);
  }
  .panel::before {
    content: "";
    position: absolute;
    inset: 0 0 auto;
    height: 2px;
    background: linear-gradient(90deg, var(--tv-accent), rgba(203, 213, 225, .25), transparent);
    opacity: .85;
  }
  .panel h3 {
    margin: 0 0 14px;
    color: #f1f5f9;
    font-size: clamp(1.05rem, 1.35vw, 1.45rem);
    font-weight: 850;
    letter-spacing: 0;
  }
  .panel .panel-subtitle {
    color: var(--tv-muted);
    display: block;
    font-size: .9rem;
    font-weight: 700;
    margin-top: 3px;
  }
  .panel canvas { height: 60vh !important; }
  .mini-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:14px; height:70vh; }
  .mini-grid .panel canvas { height: 28vh !important; }
  .slide-nav {
    position:absolute;
    top:50%;
    transform:translateY(-50%);
    background: rgba(3, 5, 10, .78);
    color:#fff;
    border: 1px solid rgba(255, 255, 255, .14);
    font-size:2rem;
    width:48px;
    height:64px;
    cursor:pointer;
  }
  #prev-slide { left:0; border-radius:0 8px 8px 0; }
  #next-slide { right:0; border-radius:8px 0 0 8px; }
  .slide-progress {
    position:absolute;
    left:50%;
    bottom: 10px;
    transform:translateX(-50%);
    display:flex;
    gap: 8px;
  }
  .slide-dot {
    width: 28px;
    height: 4px;
    border-radius: 999px;
    background: rgba(148, 163, 184, .24);
  }
  .slide-dot.active { background: var(--tv-accent); }

  @media (max-width: 1100px) {
    body { overflow: auto; }
    .tv-header { height: auto; min-height: 0; align-items: flex-start; flex-direction: column; }
    #carousel { height: auto; min-height: 89vh; }
    .slide { min-height: 89vh; }
    .grid, .mini-grid { grid-template-columns: 1fr 1fr; height: auto; }
    .mini-grid .panel { min-height: 340px; }
    .panel canvas, .mini-grid .panel canvas { height: 300px !important; }
  }
</style>

<div class="tv-header">
  <div>
    <div class="tv-title-kicker">Operação WMS</div>
    <h1>Painel TV • Separação Picking</h1>
  </div>
  <div class="tv-header-meta">
    <span class="tv-pill tv-live">Ao vivo</span>
    <span class="tv-pill">Mês corrente: <strong>{{ $periodoMesLabel }}</strong></span>
    <span class="tv-pill">Atualizado: <strong>{{ now()->format('H:i') }}</strong></span>
  </div>
</div>

<div id="carousel">
  <section class="slide active">
    <div class="grid">
      <div class="kpi"><div class="label">A separar</div><div class="value">{{ $status['a_separar'] }}</div><div class="backlog">Backlog: <strong>{{ $status['backlog_a_separar'] }}</strong></div></div>
      <div class="kpi"><div class="label">Separando</div><div class="value">{{ $status['separando'] }}</div><div class="backlog">Backlog: <strong>{{ $status['backlog_separando'] }}</strong></div></div>
      <div class="kpi"><div class="label">Separado parcial</div><div class="value">{{ $status['separado_parcial'] }}</div><div class="backlog">Backlog finalizado hoje: <strong>{{ $status['backlog_finalizado_parcial_hoje'] }}</strong></div></div>
      <div class="kpi"><div class="label">Separado completo</div><div class="value">{{ $status['separado_completo'] }}</div><div class="backlog">Backlog finalizado hoje: <strong>{{ $status['backlog_finalizado_completo_hoje'] }}</strong></div></div>
    </div>
    <div class="mini-grid">
      <div class="panel"><h3>Status do dia</h3><canvas id="miniStatus"></canvas></div>
      <div class="panel"><h3>Top separadores do dia</h3><canvas id="miniRanking"></canvas></div>
      <div class="panel"><h3>Finalizações no mês corrente<span class="panel-subtitle">{{ $periodoMesLabel }}</span></h3><canvas id="miniMes"></canvas></div>
      <div class="panel"><h3>Volume por turno do dia</h3><canvas id="miniTurno"></canvas></div>
    </div>
  </section>

  <section class="slide">
    <div class="panel"><h3>Top separadores do dia (peças separadas)</h3><canvas id="chartRanking"></canvas></div>
  </section>

  <section class="slide">
    <div class="panel"><h3>Peças separadas por colaborador hoje</h3><canvas id="chartPecasColaboradorDia"></canvas></div>
  </section>

  <section class="slide">
    <div class="panel"><h3>Peças separadas por colaborador no mês corrente<span class="panel-subtitle">{{ $periodoMesLabel }}</span></h3><canvas id="chartPecasColaboradorAcumulado"></canvas></div>
  </section>

  <section class="slide">
    <div class="panel"><h3>Top pickers do dia (SKUs separados)</h3><canvas id="chartRankingSkus"></canvas></div>
  </section>

  <section class="slide">
    <div class="panel"><h3>Finalizações por dia no mês corrente<span class="panel-subtitle">{{ $periodoMesLabel }}</span></h3><canvas id="chartMes"></canvas></div>
  </section>

  <section class="slide">
    <div class="panel"><h3>Finalizações por turno do dia + tempo médio do dia: {{ number_format($tempoMedioMin,1,',','.') }} min</h3><canvas id="chartTurno"></canvas></div>
  </section>

  <button id="prev-slide" class="slide-nav">&#8249;</button>
  <button id="next-slide" class="slide-nav">&#8250;</button>
  <div class="slide-progress" aria-hidden="true"></div>
</div>
@endsection

@section('scripts')
<script nonce="{{ $cspNonce ?? '' }}">
const statusData = {!! json_encode([
  (int) ($status['a_separar'] ?? 0),
  (int) ($status['separando'] ?? 0),
  (int) ($status['separado_parcial'] ?? 0),
  (int) ($status['separado_completo'] ?? 0),
]) !!};
const rankingLabels = {!! json_encode(collect($ranking)->pluck('nome')->values()->all()) !!};
const rankingValues = {!! json_encode(collect($ranking)->pluck('total')->map(function ($v) { return (int) $v; })->values()->all()) !!};
const pecasColaborador = @json($pecasPorColaborador);
const rankingSkusLabels = {!! json_encode(collect($rankingSkus)->pluck('nome')->values()->all()) !!};
const rankingSkusValues = {!! json_encode(collect($rankingSkus)->pluck('total')->map(function ($v) { return (int) $v; })->values()->all()) !!};
const diasMes = @json($diasMes);
const separacoesDia = @json($separacoesDia);
const parciaisDia = @json($parciaisDia);
const turnoLabels = @json($turnoLabels);
const turnoValues = @json($turnoValues);
const formatNumber = (value) => Number(value || 0).toLocaleString('pt-BR');
const shortLabel = (label, max = 28) => {
  const text = String(label || '');
  return text.length > max ? `${text.slice(0, max - 1)}…` : text;
};

const baseOpts = {
  responsive: true,
  maintainAspectRatio: false,
  animation: false,
  plugins: {
    legend: { position: 'bottom', labels: { color: '#d3ddf4', font: { weight: '700' } } },
    datalabels: {
      color: '#f8fbff',
      font: { weight: '800', size: 12 },
      anchor: 'end',
      align: 'top',
      formatter: (value) => Number(value) > 0 ? formatNumber(value) : ''
    },
    tooltip: {
      callbacks: {
        label: (ctx) => `${ctx.dataset.label || 'Valor'}: ${formatNumber(ctx.parsed.x ?? ctx.parsed.y ?? ctx.raw)}`
      }
    }
  },
  scales: {
    x: { title: { display: true, text: 'Período/Operador', color: '#9db0d2' }, ticks: { color: '#cbd5e1', font: { weight: '700' } }, grid: { color: 'rgba(148,163,184,.12)' } },
    y: { title: { display: true, text: 'Quantidade', color: '#9db0d2' }, ticks: { color: '#cbd5e1', font: { weight: '700' }, callback: (value) => formatNumber(value) }, grid: { color: 'rgba(148,163,184,.12)' }, beginAtZero: true }
  }
};

function mk(id, cfg){ new Chart(document.getElementById(id), { ...cfg, plugins: [ChartDataLabels] }); }

mk('miniStatus', {
  type:'doughnut',
  data:{ labels:['A separar','Separando','Parcial','Completo'], datasets:[{ data:statusData, backgroundColor:['#94a3b8','#ef4444','#f59e0b','#22c55e'], borderColor:'#0f172a', borderWidth:3 }] },
  options:{
    responsive:true,
    maintainAspectRatio:false,
    animation:false,
    plugins:{
      legend:{ position:'bottom', labels:{ color:'#d3ddf4', font: { weight: '700' } }},
      datalabels:{
        color:'#ffffff',
        formatter: (value, ctx) => {
          const total = ctx.dataset.data.reduce((a,b) => a + b, 0) || 1;
          const pct = ((value / total) * 100).toFixed(0);
          return value > 0 ? `${value} (${pct}%)` : '';
        }
      }
    }
  }
});
mk('miniRanking', { type:'bar', data:{ labels:rankingLabels, datasets:[{ label:'Peças', data:rankingValues, backgroundColor:'#e5e7eb' }] }, options:baseOpts });
mk('miniMes', { type:'line', data:{ labels:diasMes, datasets:[{ label:'Finalizadas no mês', data:separacoesDia, borderColor:'#22c55e', backgroundColor:'rgba(34,197,94,.18)', fill:true, tension:.25 },{ label:'Parciais no mês', data:parciaisDia, borderColor:'#ef4444', backgroundColor:'rgba(239,68,68,.14)', fill:true, tension:.25 }] }, options:baseOpts });
mk('miniTurno', { type:'bar', data:{ labels:turnoLabels, datasets:[{ label:'DTs finalizadas hoje', data:turnoValues, backgroundColor:['#e5e7eb','#ef4444','#94a3b8'] }] }, options:baseOpts });

mk('chartRanking', {
  type:'bar',
  data:{
    labels:rankingLabels,
    datasets:[{ label:'Peças distribuídas', data:rankingValues, backgroundColor:'#e5e7eb' }]
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
        title: { display: true, text: 'Separador', color: '#9db0d2' },
        ticks: { ...baseOpts.scales.x.ticks, callback: function(value) { return shortLabel(this.getLabelForValue(value), 34); } }
      }
    },
    plugins: {
      ...baseOpts.plugins,
      datalabels: {
        color:'#f8fbff',
        font:{ weight:'700', size:12 },
        anchor:'end',
        align:'right',
        formatter:(value) => Number(value) > 0 ? formatNumber(value) : ''
      }
    }
  }
});
mk('chartPecasColaboradorDia', {
  type:'bar',
  data:{
    labels:pecasColaborador.dia.labels,
    datasets:[{ label:'Hoje', data:pecasColaborador.dia.values, backgroundColor:'#e5e7eb', maxBarThickness:40 }]
  },
  options:{
    ...baseOpts,
    indexAxis: 'y',
    scales: {
      x: {
        ...baseOpts.scales.y,
        title: { display: true, text: 'Peças separadas', color: '#9db0d2' }
      },
      y: {
        ...baseOpts.scales.x,
        title: { display: true, text: 'Colaborador', color: '#9db0d2' },
        ticks: { ...baseOpts.scales.x.ticks, callback: function(value) { return shortLabel(this.getLabelForValue(value), 34); } }
      }
    },
    plugins: {
      ...baseOpts.plugins,
      datalabels: {
        color:'#f8fbff',
        font:{ weight:'700', size:12 },
        anchor:'end',
        align:'right',
        formatter:(value) => Number(value) > 0 ? formatNumber(value) : ''
      }
    }
  }
});
mk('chartPecasColaboradorAcumulado', {
  type:'bar',
  data:{
    labels:pecasColaborador.acumulado.labels,
    datasets:[{ label:'Mês corrente', data:pecasColaborador.acumulado.values, backgroundColor:'#ef4444', maxBarThickness:40 }]
  },
  options:{
    ...baseOpts,
    indexAxis: 'y',
    scales: {
      x: {
        ...baseOpts.scales.y,
        title: { display: true, text: 'Peças separadas', color: '#9db0d2' }
      },
      y: {
        ...baseOpts.scales.x,
        title: { display: true, text: 'Colaborador', color: '#9db0d2' },
        ticks: { ...baseOpts.scales.x.ticks, callback: function(value) { return shortLabel(this.getLabelForValue(value), 34); } }
      }
    },
    plugins: {
      ...baseOpts.plugins,
      datalabels: {
        color:'#f8fbff',
        font:{ weight:'700', size:12 },
        anchor:'end',
        align:'right',
        formatter:(value) => Number(value) > 0 ? formatNumber(value) : ''
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
        title: { display: true, text: 'Picker', color: '#9db0d2' },
        ticks: { ...baseOpts.scales.x.ticks, callback: function(value) { return shortLabel(this.getLabelForValue(value), 34); } }
      }
    },
    plugins: {
      ...baseOpts.plugins,
      datalabels: {
        color:'#f8fbff',
        font:{ weight:'700', size:12 },
        anchor:'end',
        align:'right',
        formatter:(value) => Number(value) > 0 ? formatNumber(value) : ''
      }
    }
  }
});
mk('chartMes', { type:'line', data:{ labels:diasMes, datasets:[{ label:'Finalizadas no mês', data:separacoesDia, borderColor:'#22c55e', backgroundColor:'rgba(34,197,94,.18)', fill:true, tension:.25 },{ label:'Parciais no mês', data:parciaisDia, borderColor:'#ef4444', backgroundColor:'rgba(239,68,68,.14)', fill:true, tension:.25 }] }, options:baseOpts });
mk('chartTurno', {
  type:'bar',
  data:{
    labels:turnoLabels,
    datasets:[{
      label:'DTs finalizadas hoje',
      data:turnoValues,
      backgroundColor:['#e5e7eb','#ef4444','#94a3b8'],
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
        formatter:(value) => Number(value) > 0 ? formatNumber(value) : ''
      }
    },
    scales: {
      x: {
        ...baseOpts.scales.x,
        title: { display: true, text: 'Turno', color: '#9db0d2' }
      },
      y: {
        ...baseOpts.scales.y,
        title: { display: true, text: 'DTs finalizadas hoje', color: '#9db0d2' },
        ticks: { color: '#c2d0ed', precision: 0, stepSize: 1 }
      }
    }
  }
});

const slides = document.querySelectorAll('.slide');
const progress = document.querySelector('.slide-progress');
slides.forEach(() => {
  const dot = document.createElement('span');
  dot.className = 'slide-dot';
  progress.appendChild(dot);
});
const dots = document.querySelectorAll('.slide-dot');
let i = 0;
function show(n){
  slides.forEach(s=>s.classList.remove('active'));
  dots.forEach(d=>d.classList.remove('active'));
  slides[n].classList.add('active');
  dots[n]?.classList.add('active');
}
function next(){ i = (i+1)%slides.length; show(i); }
function prev(){ i = (i-1+slides.length)%slides.length; show(i); }
document.getElementById('next-slide').addEventListener('click', next);
document.getElementById('prev-slide').addEventListener('click', prev);
show(i);
setInterval(next, 25000);
setTimeout(() => window.location.reload(), 5 * 60 * 1000);
</script>
@endsection
