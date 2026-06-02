@extends('layouts.app')

@section('title', 'One Page Gerencial')

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

    $topProdutividade = $produtividade->take(5)->values();
    $riscoLabel = $capacidadeOperacional['risco']['label'] ?? '-';
    $riscoClasse = str_contains(mb_strtoupper($riscoLabel), 'ALTO') ? 'danger' : (str_contains(mb_strtoupper($riscoLabel), 'MÉDIO') || str_contains(mb_strtoupper($riscoLabel), 'MEDIO') ? 'warning' : 'success');
@endphp

@section('content')
<div class="container-fluid px-4 py-3 report-onepage">
    @include('partials.breadcrumb-auto')

    <section class="onepage-sheet">
        <header class="onepage-header">
            <div>
                <span class="eyebrow">Picking / Operação</span>
                <h1>One Page Gerencial</h1>
                <p>Resumo executivo separado por etapa: picking/separação e expedição/carregamento.</p>
            </div>
            <div class="header-side">
                <div class="period-box">
                    <span>{{ $inicio->format('d/m/Y') }}</span>
                    <small>até</small>
                    <span>{{ $fim->format('d/m/Y') }}</span>
                </div>
                <div class="header-actions export-hidden">
                    <button type="button" class="btn btn-sm btn-outline-light" id="btnCopiarWhatsapp">
                        <i class="mdi mdi-content-copy me-1"></i> Copiar resumo
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-light" id="btnCompartilharOnepage">
                        <i class="mdi mdi-image-outline me-1"></i> Imagem
                    </button>
                    <a class="btn btn-sm btn-success" id="btnAbrirWhatsapp" target="_blank" rel="noopener">
                        <i class="mdi mdi-whatsapp me-1"></i> WhatsApp
                    </a>
                </div>
                <span class="copy-feedback export-hidden" id="copyFeedback" aria-live="polite"></span>
            </div>
        </header>

        <form method="GET" action="{{ route('demandas.reportGerencial') }}" class="executive-filter export-hidden">
            <div>
                <label>Inicial</label>
                <input type="date" name="data_inicio" value="{{ $dataInicio }}">
            </div>
            <div>
                <label>Final</label>
                <input type="date" name="data_fim" value="{{ $dataFim }}">
            </div>
            <div>
                <label>Turno</label>
                <select name="turno">
                    <option value="">Todos</option>
                    @foreach($turnosOperacionais as $codigo => $turno)
                        <option value="{{ $codigo }}" @selected($turnoSelecionado === $codigo)>
                            {{ $turno['label'] }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label>Demanda</label>
                <select name="tipo_demanda">
                    <option value="TODAS" @selected(($tipoDemanda ?? 'TODAS') === 'TODAS')>Todas</option>
                    <option value="PROGRAMADA" @selected(($tipoDemanda ?? 'TODAS') === 'PROGRAMADA')>Programada</option>
                    <option value="OPORTUNIDADE" @selected(($tipoDemanda ?? 'TODAS') === 'OPORTUNIDADE')>Oportunidade</option>
                </select>
            </div>
            <div>
                <label>Separador</label>
                <input type="text" name="separador" list="separadores-list" value="{{ $separadorSelecionado }}" placeholder="Todos">
                <datalist id="separadores-list">
                    @foreach($separadoresDisponiveis as $nome)
                        <option value="{{ $nome }}">
                    @endforeach
                </datalist>
            </div>
            <button type="submit" class="btn btn-sm btn-primary">
                <i class="mdi mdi-filter-outline me-1"></i> Aplicar
            </button>
        </form>

        <div class="kpi-row">
            <article class="kpi-card primary">
                <span>Separação finalizada</span>
                <strong>{{ $fmt($resumo['separadas'] ?? $resumo['finalizadas']) }}</strong>
                <small>{{ $fmtPct($resumo['percentual_conclusao']) }} de conclusão no picking</small>
            </article>
            <article class="kpi-card expedition">
                <span>Expedição finalizada</span>
                <strong>{{ $fmt($resumo['expedidas'] ?? 0) }}</strong>
                <small>{{ $fmt($resumo['separadas_aguardando_expedicao'] ?? 0) }} separadas aguardando expedição</small>
            </article>
            <article class="kpi-card">
                <span>Caixas / peças</span>
                <strong>{{ $fmt($resumo['pecas']) }}</strong>
                <small>Volume apontado na separação</small>
            </article>
            <article class="kpi-card">
                <span>SLA separação</span>
                <strong>{{ $fmtPct($resumo['sla_no_dia']) }}</strong>
                <small>{{ $fmt($resumo['finalizadas_fora_dia']) }} DTs separadas fora do dia</small>
            </article>
            <article class="kpi-card">
                <span>Backlog separação</span>
                <strong>{{ $fmt($resumo['backlog_aberto']) }}</strong>
                <small>{{ $fmt($resumo['em_aberto_periodo']) }} abertas no período</small>
            </article>
            <article class="kpi-card risk {{ $riscoClasse }}">
                <span>Risco operacional</span>
                <strong>{{ $riscoLabel }}</strong>
                <small>{{ $fmt($capacidadeOperacional['risco']['backlog_projetado'] ?? 0) }} backlog previsto</small>
            </article>
        </div>

        <main class="executive-grid">
            <section class="panel span-2">
                <div class="panel-title">
                    <div>
                        <h2>Evolução do período</h2>
                        <p>DTs criadas x DTs separadas. Este gráfico não representa expedição finalizada.</p>
                    </div>
                    @if($resumo['variacao_volume'] !== null)
                        <span class="badge-soft {{ $resumo['variacao_volume'] >= 0 ? 'good' : 'bad' }}">
                            {{ $resumo['variacao_volume'] >= 0 ? '+' : '' }}{{ $fmtPct($resumo['variacao_volume']) }} vs período anterior
                        </span>
                    @else
                        <span class="badge-soft">Sem base anterior</span>
                    @endif
                </div>
                <div class="chart-box">
                    <canvas id="chartEvolucaoGerencial"></canvas>
                </div>
            </section>

            <section class="panel span-2">
                <div class="panel-title">
                    <div>
                        <h2>Caixas separadas por hora</h2>
                        <p>Volume apontado no picking em {{ $dadosGraficos['caixasPorHora']['data'] ?? $fim->format('d/m/Y') }} versus meta média horária.</p>
                    </div>
                    <span class="badge-soft">Meta {{ $fmt($resumo['meta_caixas_hora'] ?? 1000) }}/hora</span>
                </div>
                <div class="chart-box">
                    <canvas id="chartCaixasHora"></canvas>
                </div>
                <div class="mini-metrics three-cols compact">
                    <div>
                        <span>Total do dia</span>
                        <strong>{{ $fmt($resumo['caixas_dia_kpi'] ?? 0) }}</strong>
                    </div>
                    <div>
                        <span>Média/hora produtiva</span>
                        <strong>{{ $fmt($resumo['media_caixas_hora'] ?? 0) }}</strong>
                    </div>
                    <div>
                        <span>Meta média</span>
                        <strong>{{ $fmt($resumo['meta_caixas_hora'] ?? 1000) }}</strong>
                    </div>
                </div>
            </section>

            <section class="panel span-2">
                <div class="panel-title">
                    <div>
                        <h2>Caixas separadas por dia no mês</h2>
                        <p>Histórico mensal do picking com linha de meta diária.</p>
                    </div>
                    <span class="badge-soft">Meta {{ $fmt($resumo['meta_caixas_dia'] ?? 22500) }}/dia</span>
                </div>
                <div class="chart-box">
                    <canvas id="chartCaixasDiaMes"></canvas>
                </div>
                <div class="mini-metrics three-cols compact">
                    <div>
                        <span>Total mês</span>
                        <strong>{{ $fmt($resumo['caixas_mes'] ?? 0) }}</strong>
                    </div>
                    <div>
                        <span>Média diária</span>
                        <strong>{{ $fmt($resumo['media_caixas_dia_mes'] ?? 0) }}</strong>
                    </div>
                    <div>
                        <span>Meta diária</span>
                        <strong>{{ $fmt($resumo['meta_caixas_dia'] ?? 22500) }}</strong>
                    </div>
                </div>
            </section>

            <section class="panel span-2">
                <div class="panel-title">
                    <div>
                        <h2>Caixas de oportunidades por dia no mês</h2>
                        <p>Histórico mensal do picking considerando somente demandas não programadas.</p>
                    </div>
                    <span class="badge-soft">Meta {{ $fmt($resumo['meta_caixas_dia'] ?? 22500) }}/dia</span>
                </div>
                <div class="chart-box">
                    <canvas id="chartCaixasOportunidadesDiaMes"></canvas>
                </div>
                <div class="mini-metrics three-cols compact">
                    <div>
                        <span>Total mês</span>
                        <strong>{{ $fmt($dadosGraficos['caixasOportunidadesPorDiaMes']['total'] ?? 0) }}</strong>
                    </div>
                    <div>
                        <span>Média diária</span>
                        <strong>{{ $fmt($dadosGraficos['caixasOportunidadesPorDiaMes']['media'] ?? 0) }}</strong>
                    </div>
                    <div>
                        <span>Meta diária</span>
                        <strong>{{ $fmt($resumo['meta_caixas_dia'] ?? 22500) }}</strong>
                    </div>
                </div>
            </section>

            <section class="panel">
                <div class="panel-title">
                    <div>
                        <h2>Atendimento separação</h2>
                        <p>Programação e oportunidades concluídas no picking.</p>
                    </div>
                </div>
                <div class="mini-metrics">
                    <div>
                        <span>Programado</span>
                        <strong>{{ $fmt($visaoDemanda['programadas_total'] ?? 0) }}</strong>
                    </div>
                    <div>
                        <span>Separadas</span>
                        <strong>{{ $fmt($visaoDemanda['programadas_separadas'] ?? $visaoDemanda['programadas_executadas'] ?? 0) }}</strong>
                    </div>
                    <div>
                        <span>% separação</span>
                        <strong>{{ $fmtPct($visaoDemanda['programadas_atendimento'] ?? 0) }}</strong>
                    </div>
                    <div>
                        <span>Oport. separadas</span>
                        <strong>{{ $fmt($visaoDemanda['oportunidades_separadas'] ?? $visaoDemanda['oportunidades_executadas'] ?? 0) }}</strong>
                    </div>
                </div>
            </section>

            <section class="panel">
                <div class="panel-title">
                    <div>
                        <h2>Atendimento expedição</h2>
                        <p>Conclusão real por carregamento/saída.</p>
                    </div>
                </div>
                <div class="mini-metrics">
                    <div>
                        <span>Programadas expedidas</span>
                        <strong>{{ $fmt($visaoDemanda['programadas_expedidas'] ?? 0) }}</strong>
                    </div>
                    <div>
                        <span>Oport. expedidas</span>
                        <strong>{{ $fmt($visaoDemanda['oportunidades_expedidas'] ?? 0) }}</strong>
                    </div>
                    <div>
                        <span>Total expedido</span>
                        <strong>{{ $fmt($visaoDemanda['total_expedido'] ?? $resumo['expedidas'] ?? 0) }}</strong>
                    </div>
                    <div>
                        <span>Aguardando expedição</span>
                        <strong>{{ $fmt($resumo['separadas_aguardando_expedicao'] ?? 0) }}</strong>
                    </div>
                </div>
            </section>

            <section class="panel">
                <div class="panel-title">
                    <div>
                        <h2>Capacidade</h2>
                        <p>Consumo e folga da operação no período.</p>
                    </div>
                </div>
                <div class="capacity-block">
                    <div class="capacity-value">
                        <span>Consumida</span>
                        <strong>{{ $fmtPct($capacidadeOperacional['capacidade']['geral_consumida_percentual'] ?? 0) }}</strong>
                    </div>
                    <div class="progress executive-progress" role="progressbar" aria-valuenow="{{ $capacidadeOperacional['capacidade']['geral_consumida_percentual'] ?? 0 }}" aria-valuemin="0" aria-valuemax="100">
                        <div class="progress-bar" style="width: {{ min(100, max(0, $capacidadeOperacional['capacidade']['geral_consumida_percentual'] ?? 0)) }}%"></div>
                    </div>
                    <div class="mini-metrics two-cols">
                        <div>
                            <span>Consumida</span>
                            <strong>{{ $fmt($capacidadeOperacional['capacidade']['geral_consumida_dt'] ?? 0) }}</strong>
                        </div>
                        <div>
                            <span>Restante</span>
                            <strong>{{ $fmt($capacidadeOperacional['capacidade']['restante_dt'] ?? 0) }}</strong>
                        </div>
                    </div>
                </div>
            </section>

            <section class="panel">
                <div class="panel-title">
                    <div>
                        <h2>Status separação</h2>
                        <p>Composição do resultado do picking.</p>
                    </div>
                </div>
                <div class="chart-box small">
                    <canvas id="chartStatusGerencial"></canvas>
                </div>
                <div class="mini-metrics two-cols compact">
                    <div>
                        <span>Parcialidade</span>
                        <strong>{{ $fmtPct($resumo['percentual_parcial']) }}</strong>
                    </div>
                    <div>
                        <span>Tempo médio</span>
                        <strong>{{ $fmtMin($resumo['tempo_medio_min']) }}</strong>
                    </div>
                </div>
            </section>

            <section class="panel">
                <div class="panel-title">
                    <div>
                        <h2>Top produtividade</h2>
                        <p>5 maiores separadores por volume.</p>
                    </div>
                </div>
                <div class="ranking-list">
                    @forelse($topProdutividade as $idx => $linha)
                        <div class="ranking-item">
                            <span>{{ $idx + 1 }}</span>
                            <div>
                                <strong>{{ $linha['separador'] }}</strong>
                                <small>{{ $fmt($linha['dts']) }} DTs | {{ $fmtPct($linha['participacao']) }} participação</small>
                            </div>
                            <b>{{ $fmt($linha['pecas']) }}</b>
                        </div>
                    @empty
                        <div class="empty-state">Sem apontamentos finalizados para os filtros selecionados.</div>
                    @endforelse
                </div>
            </section>

            <section class="panel span-2">
                <div class="panel-title">
                    <div>
                        <h2>Pontos de atenção para decisão</h2>
                        <p>Leitura objetiva dos desvios que precisam de acompanhamento.</p>
                    </div>
                    <span class="badge-soft">Maior tempo: {{ $fmtMin($resumo['tempo_max_min']) }}</span>
                </div>
                <div class="attention-list">
                    @foreach($pontosAtencao->take(4) as $item)
                        <div class="attention-item">
                            <i class="mdi mdi-alert-circle-outline"></i>
                            <span>{{ $item }}</span>
                        </div>
                    @endforeach
                </div>
            </section>
        </main>

        <textarea id="whatsappResumo" class="visually-hidden" readonly>{{ $mensagemWhatsapp }}</textarea>
    </section>
</div>

<style>
    .report-onepage {
        --page-bg: #090d14;
        --panel-bg: rgba(15, 23, 42, 0.94);
        --panel-soft: rgba(30, 41, 59, 0.72);
        --border: rgba(148, 163, 184, 0.2);
        --text: #f8fafc;
        --muted: #94a3b8;
        --red: #ef4444;
        --blue: #38bdf8;
        --green: #22c55e;
        --yellow: #f59e0b;
        color: var(--text);
    }

    .onepage-sheet {
        background: linear-gradient(180deg, rgba(15, 23, 42, 0.7), rgba(9, 13, 20, 0.96));
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 18px;
    }

    .onepage-header,
    .executive-filter,
    .kpi-row,
    .executive-grid,
    .panel-title,
    .header-actions {
        display: grid;
        gap: 12px;
    }

    .onepage-header {
        align-items: start;
        grid-template-columns: minmax(0, 1fr) auto;
        margin-bottom: 14px;
    }

    .eyebrow {
        color: var(--red);
        display: block;
        font-size: 11px;
        font-weight: 800;
        margin-bottom: 5px;
        text-transform: uppercase;
    }

    .onepage-header h1,
    .panel-title h2 {
        color: var(--text);
        letter-spacing: 0;
        margin: 0;
    }

    .onepage-header h1 {
        font-size: 30px;
        line-height: 1.05;
    }

    .onepage-header p,
    .panel-title p,
    .kpi-card span,
    .kpi-card small,
    .executive-filter label,
    .mini-metrics span,
    .ranking-item small,
    .capacity-value span {
        color: var(--muted);
    }

    .onepage-header p {
        margin: 6px 0 0;
        max-width: 760px;
    }

    .header-side {
        align-items: flex-end;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .header-actions {
        grid-template-columns: repeat(3, auto);
    }

    .period-box {
        align-items: center;
        background: var(--panel-bg);
        border: 1px solid var(--border);
        border-radius: 8px;
        display: flex;
        gap: 9px;
        padding: 9px 12px;
        white-space: nowrap;
    }

    .period-box span {
        color: #fff;
        font-weight: 800;
    }

    .period-box small {
        color: var(--muted);
        font-weight: 700;
    }

    .copy-feedback {
        color: #86efac;
        font-size: 12px;
        font-weight: 800;
        min-height: 16px;
    }

    .executive-filter {
        align-items: end;
        background: rgba(15, 23, 42, 0.72);
        border: 1px solid var(--border);
        border-radius: 8px;
        grid-template-columns: 150px 150px 150px 170px minmax(180px, 1fr) 110px;
        margin-bottom: 14px;
        padding: 12px;
    }

    .executive-filter label {
        display: block;
        font-size: 11px;
        font-weight: 800;
        margin-bottom: 4px;
        text-transform: uppercase;
    }

    .executive-filter input,
    .executive-filter select {
        background: #0f172a;
        border: 1px solid rgba(148, 163, 184, 0.28);
        border-radius: 6px;
        color: var(--text);
        height: 34px;
        padding: 6px 9px;
        width: 100%;
    }

    .kpi-row {
        grid-template-columns: repeat(6, minmax(0, 1fr));
        margin-bottom: 14px;
    }

    .kpi-card,
    .panel {
        background: var(--panel-bg);
        border: 1px solid var(--border);
        border-radius: 8px;
        box-shadow: 0 14px 34px rgba(2, 6, 23, 0.2);
    }

    .kpi-card {
        min-height: 112px;
        padding: 16px;
    }

    .kpi-card.primary {
        border-color: rgba(56, 189, 248, 0.48);
    }

    .kpi-card.expedition {
        border-color: rgba(34, 197, 94, 0.48);
    }

    .kpi-card.risk.danger {
        border-color: rgba(239, 68, 68, 0.58);
    }

    .kpi-card.risk.warning {
        border-color: rgba(245, 158, 11, 0.58);
    }

    .kpi-card.risk.success {
        border-color: rgba(34, 197, 94, 0.48);
    }

    .kpi-card span,
    .kpi-card small {
        display: block;
        font-weight: 800;
    }

    .kpi-card span {
        font-size: 11px;
        text-transform: uppercase;
    }

    .kpi-card strong {
        color: #fff;
        display: block;
        font-size: 30px;
        line-height: 1;
        margin: 14px 0 9px;
    }

    .kpi-card.risk strong {
        font-size: 24px;
    }

    .executive-grid {
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }

    .panel {
        min-height: 250px;
        padding: 16px;
    }

    .span-2 {
        grid-column: span 2;
    }

    .panel-title {
        align-items: start;
        grid-template-columns: minmax(0, 1fr) auto;
        margin-bottom: 10px;
    }

    .panel-title h2 {
        font-size: 16px;
    }

    .panel-title p {
        font-size: 12px;
        margin: 4px 0 0;
    }

    .badge-soft {
        background: var(--panel-soft);
        border: 1px solid var(--border);
        border-radius: 999px;
        color: #e2e8f0;
        font-size: 12px;
        font-weight: 800;
        padding: 7px 10px;
        white-space: nowrap;
    }

    .badge-soft.good {
        color: #86efac;
    }

    .badge-soft.bad {
        color: #fca5a5;
    }

    .chart-box {
        height: 224px;
        position: relative;
    }

    .chart-box.small {
        height: 150px;
    }

    .mini-metrics {
        display: grid;
        gap: 10px;
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .mini-metrics.two-cols {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .mini-metrics.three-cols {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .mini-metrics.compact {
        margin-top: 10px;
    }

    .mini-metrics div,
    .attention-item,
    .ranking-item,
    .empty-state {
        background: rgba(15, 23, 42, 0.74);
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 11px;
    }

    .mini-metrics span,
    .mini-metrics strong {
        display: block;
    }

    .mini-metrics strong {
        color: #fff;
        font-size: 20px;
        margin-top: 4px;
    }

    .capacity-block {
        display: grid;
        gap: 14px;
    }

    .capacity-value strong {
        color: #fff;
        display: block;
        font-size: 42px;
        line-height: 1;
        margin-top: 4px;
    }

    .executive-progress {
        background: rgba(148, 163, 184, 0.18);
        height: 10px;
    }

    .executive-progress .progress-bar {
        background: linear-gradient(90deg, var(--blue), var(--green));
    }

    .ranking-list,
    .attention-list {
        display: grid;
        gap: 9px;
    }

    .ranking-item {
        align-items: center;
        display: grid;
        gap: 10px;
        grid-template-columns: 28px minmax(0, 1fr) auto;
    }

    .ranking-item > span {
        align-items: center;
        background: rgba(56, 189, 248, 0.14);
        border: 1px solid rgba(56, 189, 248, 0.28);
        border-radius: 999px;
        color: #bae6fd;
        display: flex;
        font-weight: 900;
        height: 28px;
        justify-content: center;
        width: 28px;
    }

    .ranking-item strong,
    .ranking-item small {
        display: block;
    }

    .ranking-item strong {
        color: #fff;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .ranking-item b {
        color: #fff;
        font-size: 18px;
    }

    .attention-list {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .attention-item {
        align-items: flex-start;
        color: #e2e8f0;
        display: flex;
        gap: 10px;
        min-height: 58px;
    }

    .attention-item i {
        color: var(--yellow);
        font-size: 20px;
        line-height: 1;
    }

    .empty-state {
        color: var(--muted);
        text-align: center;
    }

    @media print {
        .navbar,
        .breadcrumb,
        .executive-filter,
        .header-actions,
        .copy-feedback {
            display: none !important;
        }

        .report-onepage {
            background: #fff;
            color: #111827;
            padding: 0 !important;
        }

        .onepage-sheet,
        .panel,
        .kpi-card {
            box-shadow: none;
        }
    }

    @media (max-width: 1400px) {
        .kpi-row {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .executive-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 992px) {
        .onepage-header,
        .panel-title {
            grid-template-columns: 1fr;
        }

        .header-side {
            align-items: flex-start;
        }

        .executive-filter {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .kpi-row,
        .executive-grid,
        .attention-list {
            grid-template-columns: 1fr;
        }

        .span-2 {
            grid-column: span 1;
        }
    }

    @media (max-width: 576px) {
        .report-onepage {
            padding-left: 12px !important;
            padding-right: 12px !important;
        }

        .onepage-sheet {
            padding: 12px;
        }

        .executive-filter,
        .header-actions,
        .mini-metrics {
            grid-template-columns: 1fr;
        }

        .onepage-header h1 {
            font-size: 25px;
        }

        .chart-box {
            height: 210px;
        }
    }
</style>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<script nonce="{{ $cspNonce ?? '' }}">
document.addEventListener('DOMContentLoaded', function () {
    if (typeof Chart === 'undefined') {
        return;
    }

    const dados = @json($dadosGraficos);
    const mensagemWhatsapp = @json($mensagemWhatsapp);
    const gridColor = 'rgba(148, 163, 184, 0.18)';
    const textColor = '#cbd5e1';
    const resumoTextarea = document.getElementById('whatsappResumo');
    const btnCopiarWhatsapp = document.getElementById('btnCopiarWhatsapp');
    const btnAbrirWhatsapp = document.getElementById('btnAbrirWhatsapp');
    const btnCompartilharOnepage = document.getElementById('btnCompartilharOnepage');
    const copyFeedback = document.getElementById('copyFeedback');
    const formatCaixas = (value) => new Intl.NumberFormat('pt-BR', { maximumFractionDigits: 0 }).format(value || 0);

    Chart.defaults.color = textColor;
    Chart.defaults.font.family = 'Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif';

    if (btnAbrirWhatsapp) {
        btnAbrirWhatsapp.href = 'https://wa.me/?text=' + encodeURIComponent(mensagemWhatsapp);
    }

    function showCopyFeedback(message) {
        if (!copyFeedback) {
            return;
        }

        copyFeedback.textContent = message;
        window.setTimeout(function () {
            copyFeedback.textContent = '';
        }, 2600);
    }

    async function copyWhatsappText() {
        const text = resumoTextarea ? resumoTextarea.value : mensagemWhatsapp;

        try {
            if (navigator.clipboard && window.isSecureContext) {
                await navigator.clipboard.writeText(text);
            } else if (resumoTextarea) {
                resumoTextarea.classList.remove('visually-hidden');
                resumoTextarea.focus();
                resumoTextarea.select();
                document.execCommand('copy');
                resumoTextarea.classList.add('visually-hidden');
            }

            showCopyFeedback('Resumo copiado.');
        } catch (error) {
            showCopyFeedback('Não foi possível copiar automaticamente.');
        }
    }

    if (btnCopiarWhatsapp) {
        btnCopiarWhatsapp.addEventListener('click', copyWhatsappText);
    }

    function baixarImagem(blob, nomeArquivo) {
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = nomeArquivo;
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.setTimeout(() => URL.revokeObjectURL(link.href), 1200);
    }

    async function compartilharOnepage() {
        const alvo = document.querySelector('.onepage-sheet');

        if (!alvo || typeof html2canvas === 'undefined') {
            showCopyFeedback('Não foi possível gerar a imagem.');
            return;
        }

        btnCompartilharOnepage.disabled = true;
        showCopyFeedback('Gerando imagem...');

        try {
            alvo.classList.add('exporting-onepage');
            await new Promise((resolve) => window.setTimeout(resolve, 250));

            const canvas = await html2canvas(alvo, {
                backgroundColor: '#090d14',
                scale: Math.min(2, window.devicePixelRatio || 1),
                useCORS: true,
                ignoreElements: (element) => element.classList?.contains('export-hidden')
            });

            canvas.toBlob(async function (blob) {
                alvo.classList.remove('exporting-onepage');

                if (!blob) {
                    showCopyFeedback('Não foi possível gerar a imagem.');
                    btnCompartilharOnepage.disabled = false;
                    return;
                }

                const nomeArquivo = `onepage-gerencial-{{ $fim->format('Y-m-d') }}.png`;
                const arquivo = new File([blob], nomeArquivo, { type: 'image/png' });

                try {
                    if (navigator.canShare && navigator.canShare({ files: [arquivo] })) {
                        await navigator.share({
                            title: 'One Page Gerencial',
                            text: mensagemWhatsapp,
                            files: [arquivo]
                        });
                        showCopyFeedback('Imagem pronta para envio.');
                    } else {
                        baixarImagem(blob, nomeArquivo);
                        if (navigator.clipboard && window.isSecureContext) {
                            await navigator.clipboard.writeText(mensagemWhatsapp);
                        }
                        window.open('https://wa.me/?text=' + encodeURIComponent(mensagemWhatsapp), '_blank', 'noopener');
                        showCopyFeedback('Imagem baixada e resumo copiado.');
                    }
                } catch (error) {
                    showCopyFeedback('Compartilhamento cancelado.');
                } finally {
                    btnCompartilharOnepage.disabled = false;
                }
            }, 'image/png', 0.95);
        } catch (error) {
            alvo.classList.remove('exporting-onepage');
            btnCompartilharOnepage.disabled = false;
            showCopyFeedback('Falha ao gerar imagem.');
        }
    }

    if (btnCompartilharOnepage) {
        btnCompartilharOnepage.addEventListener('click', compartilharOnepage);
    }

    const chartEvolucao = document.getElementById('chartEvolucaoGerencial');
    if (chartEvolucao) {
        new Chart(chartEvolucao, {
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
                        label: 'Separadas',
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
    }

    const chartCaixasHora = document.getElementById('chartCaixasHora');
    if (chartCaixasHora && dados.caixasPorHora) {
        new Chart(chartCaixasHora, {
            data: {
                labels: dados.caixasPorHora.labels,
                datasets: [
                    {
                        type: 'bar',
                        label: 'Caixas',
                        data: dados.caixasPorHora.values,
                        backgroundColor: '#2563eb',
                        borderRadius: 5,
                        maxBarThickness: 26
                    },
                    {
                        type: 'line',
                        label: `Meta ${formatCaixas(dados.caixasPorHora.meta)}/hora`,
                        data: dados.caixasPorHora.labels.map(() => dados.caixasPorHora.meta),
                        borderColor: '#ef4444',
                        borderDash: [6, 4],
                        borderWidth: 2,
                        pointRadius: 0,
                        tension: 0
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => `${ctx.dataset.label}: ${formatCaixas(ctx.raw)} caixas`
                        }
                    }
                },
                scales: {
                    x: { grid: { display: false }, title: { display: true, text: 'Hora' } },
                    y: {
                        beginAtZero: true,
                        grid: { color: gridColor },
                        ticks: { callback: (value) => formatCaixas(value) },
                        title: { display: true, text: 'Caixas' }
                    }
                }
            }
        });
    }

    const chartCaixasDiaMes = document.getElementById('chartCaixasDiaMes');
    if (chartCaixasDiaMes && dados.caixasPorDiaMes) {
        new Chart(chartCaixasDiaMes, {
            data: {
                labels: dados.caixasPorDiaMes.labels,
                datasets: [
                    {
                        type: 'bar',
                        label: 'Caixas por dia',
                        data: dados.caixasPorDiaMes.values,
                        backgroundColor: '#2563eb',
                        borderRadius: 5,
                        maxBarThickness: 24
                    },
                    {
                        type: 'line',
                        label: `Meta ${formatCaixas(dados.caixasPorDiaMes.meta)}/dia`,
                        data: dados.caixasPorDiaMes.labels.map(() => dados.caixasPorDiaMes.meta),
                        borderColor: '#ef4444',
                        borderDash: [6, 4],
                        borderWidth: 2,
                        pointRadius: 0,
                        tension: 0
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => `${ctx.dataset.label}: ${formatCaixas(ctx.raw)} caixas`
                        }
                    }
                },
                scales: {
                    x: { grid: { display: false }, title: { display: true, text: 'Dia' } },
                    y: {
                        beginAtZero: true,
                        grid: { color: gridColor },
                        ticks: { callback: (value) => formatCaixas(value) },
                        title: { display: true, text: 'Caixas' }
                    }
                }
            }
        });
    }

    const chartCaixasOportunidadesDiaMes = document.getElementById('chartCaixasOportunidadesDiaMes');
    if (chartCaixasOportunidadesDiaMes && dados.caixasOportunidadesPorDiaMes) {
        new Chart(chartCaixasOportunidadesDiaMes, {
            data: {
                labels: dados.caixasOportunidadesPorDiaMes.labels,
                datasets: [
                    {
                        type: 'bar',
                        label: 'Caixas de oportunidades',
                        data: dados.caixasOportunidadesPorDiaMes.values,
                        backgroundColor: '#2563eb',
                        borderRadius: 5,
                        maxBarThickness: 24
                    },
                    {
                        type: 'line',
                        label: `Meta ${formatCaixas(dados.caixasOportunidadesPorDiaMes.meta)}/dia`,
                        data: dados.caixasOportunidadesPorDiaMes.labels.map(() => dados.caixasOportunidadesPorDiaMes.meta),
                        borderColor: '#ef4444',
                        borderDash: [6, 4],
                        borderWidth: 2,
                        pointRadius: 0,
                        tension: 0
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => `${ctx.dataset.label}: ${formatCaixas(ctx.raw)} caixas`
                        }
                    }
                },
                scales: {
                    x: { grid: { display: false }, title: { display: true, text: 'Dia' } },
                    y: {
                        beginAtZero: true,
                        grid: { color: gridColor },
                        ticks: { callback: (value) => formatCaixas(value) },
                        title: { display: true, text: 'Caixas' }
                    }
                }
            }
        });
    }

    const chartStatus = document.getElementById('chartStatusGerencial');
    if (chartStatus) {
        new Chart(chartStatus, {
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
    }
});
</script>
@endpush
