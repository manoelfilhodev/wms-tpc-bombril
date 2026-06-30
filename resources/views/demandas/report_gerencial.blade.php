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
    $metaSeparacao = $metasDiarias['separacao'] ?? [];
    $metaExpedicao = $metasDiarias['expedicao'] ?? [];
    $cardsPrevisibilidade = collect($previsibilidadeOperacional['cards'] ?? []);
    $classeMetaSeparacao = match ($metaSeparacao['status'] ?? 'neutral') {
        'ok' => 'success',
        'warning' => 'warning',
        'danger' => 'danger',
        default => '',
    };
    $classeMetaExpedicao = match ($metaExpedicao['status'] ?? 'neutral') {
        'ok' => 'success',
        'warning' => 'warning',
        'danger' => 'danger',
        default => '',
    };
@endphp

@section('content')
<div class="container-fluid px-4 py-3 report-onepage">
    @include('partials.breadcrumb-auto')

    <section class="onepage-sheet">
        <header class="onepage-header">
            <div>
                <span class="eyebrow">Picking / Operação</span>
                <h1>One Page Gerencial</h1>
                <p>Visão executiva do dia: meta, realizado, ritmo necessário e projeção de fechamento por setor.</p>
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

        <section class="previsibilidade-block">
            <div class="section-heading">
                <div>
                    <span class="eyebrow">Torre operacional</span>
                    <h2>Previsibilidade da expedição</h2>
                    <p>Painel operacional do dia, previsto x realizado.</p>
                </div>
                <span class="badge-soft">Data {{ $previsibilidadeOperacional['data'] ?? $fim->format('d/m/Y') }}</span>
            </div>
            <div class="previsibilidade-grid">
                @foreach($cardsPrevisibilidade as $card)
                    <article class="previs-card {{ $card['classe'] ?? '' }}">
                        <div>
                            <span>{{ $card['titulo'] }}</span>
                            <strong>{{ $fmt($card['valor'] ?? 0) }}</strong>
                            <small>{{ $card['detalhe'] ?? '' }}</small>
                        </div>
                        <b>{{ $fmtPct($card['percentual'] ?? 0) }}</b>
                    </article>
                @endforeach
            </div>
        </section>

        <section class="gauge-overview">
            <article class="gauge-card">
                <div class="gauge-head">
                    <span>Expedição disponível</span>
                    <b>{{ $fmt($metaExpedicao['realizado'] ?? 0) }} / {{ $fmt($metaExpedicao['disponibilidade'] ?? 0) }}</b>
                </div>
                <div class="gauge-wrap"><canvas id="gaugeExpedicaoRealizada"></canvas></div>
                <small>Meta operacional: {{ $fmt($metaExpedicao['meta'] ?? 0) }} veículos</small>
            </article>
            <article class="gauge-card">
                <div class="gauge-head">
                    <span>Separação disponível</span>
                    <b>{{ $fmt($metaSeparacao['realizado'] ?? 0) }} / {{ $fmt($metaSeparacao['disponibilidade'] ?? 0) }}</b>
                </div>
                <div class="gauge-wrap"><canvas id="gaugeSeparacaoRealizada"></canvas></div>
                <small>Meta operacional: {{ $fmt($metaSeparacao['meta'] ?? 0) }} caixas</small>
            </article>
            <article class="gauge-card">
                <div class="gauge-head">
                    <span>Projeção expedição</span>
                    <b>{{ $fmt($metaExpedicao['projecao_fechamento'] ?? 0) }}</b>
                </div>
                <div class="gauge-wrap"><canvas id="gaugeExpedicaoProjetada"></canvas></div>
                <small>Contra programação disponível</small>
            </article>
            <article class="gauge-card">
                <div class="gauge-head">
                    <span>Projeção separação</span>
                    <b>{{ $fmt($metaSeparacao['projecao_fechamento'] ?? 0) }}</b>
                </div>
                <div class="gauge-wrap"><canvas id="gaugeSeparacaoProjetada"></canvas></div>
                <small>Contra caixas disponíveis</small>
            </article>
            <article class="gauge-card donut-card">
                <div class="gauge-head">
                    <span>Composição do dia</span>
                    <b>{{ $fmt($previsibilidadeOperacional['cards'][0]['valor'] ?? 0) }}</b>
                </div>
                <div class="donut-wrap"><canvas id="donutComposicaoDia"></canvas></div>
                <small>Realizado, na planta e falta chegar</small>
            </article>
        </section>

        <main class="executive-grid">
            <section class="panel span-4">
                <div class="panel-title">
                    <div>
                        <h2>Projeção de produtividade 12h-00h</h2>
                        <p>Caixas separadas acumuladas contra curva ideal, projeção corrigida e meta de 11.000 caixas.</p>
                    </div>
                    <div class="badge-row">
                        <span class="badge-soft">Separado: {{ $fmt($dadosGraficos['projecaoProdutividade12h']['produzido'] ?? 0) }}</span>
                        <span class="badge-soft">Meta: {{ $fmt($dadosGraficos['projecaoProdutividade12h']['meta'] ?? 11000) }}</span>
                        @if(!empty($dadosGraficos['projecaoProdutividade12h']['previsaoConclusao']))
                            <span class="badge-soft good">Previsão: {{ $dadosGraficos['projecaoProdutividade12h']['previsaoConclusao'] }}</span>
                        @endif
                    </div>
                </div>
                <div class="chart-box chart-box-hero">
                    <canvas id="chartProjecao12h"></canvas>
                </div>
            </section>

            <section class="panel span-2">
                <div class="panel-title">
                    <div>
                        <h2>Disponibilidade diária da separação</h2>
                        <p>Caixas separadas x caixas disponíveis para {{ $metasDiarias['data'] ?? $fim->format('d/m/Y') }}. Meta fixa em paralelo. Janela {{ $metasDiarias['janela'] ?? '00:01 - 23:59' }}.</p>
                    </div>
                    <div class="badge-row">
                        <span class="badge-soft">Realizado: {{ $fmt($metaSeparacao['realizado'] ?? 0) }}</span>
                        <span class="badge-soft">Disponível: {{ $fmt($metaSeparacao['disponibilidade'] ?? 0) }}</span>
                        <span class="badge-soft">Meta: {{ $fmt($metaSeparacao['meta'] ?? 0) }}</span>
                        <span class="badge-soft {{ ($metaSeparacao['gap'] ?? 0) >= 0 ? 'good' : 'bad' }}">
                            Gap disp.: {{ ($metaSeparacao['gap_disponibilidade'] ?? 0) >= 0 ? '+' : '' }}{{ $fmt($metaSeparacao['gap_disponibilidade'] ?? 0) }}
                        </span>
                    </div>
                </div>
                <div class="chart-box chart-box-projection">
                    <canvas id="chartMetaSeparacaoDiaria"></canvas>
                </div>
                <div class="mini-metrics three-cols compact">
                    <div>
                        <span>Ritmo/hora</span>
                        <strong>{{ number_format((float) ($metaSeparacao['ritmo_hora'] ?? 0), 1, ',', '.') }}</strong>
                    </div>
                    <div>
                        <span>Disponível esperado</span>
                        <strong>{{ $fmt($metaSeparacao['esperado_agora'] ?? 0) }}</strong>
                    </div>
                    <div>
                        <span>Projeção</span>
                        <strong>{{ $fmt($metaSeparacao['projecao_fechamento'] ?? 0) }}</strong>
                    </div>
                </div>
            </section>

            <section class="panel span-2">
                <div class="panel-title">
                    <div>
                        <h2>Disponibilidade diária da expedição</h2>
                        <p>Veículos carregados x programação disponível do dia. Meta fixa em paralelo. Janela {{ $metasDiarias['janela'] ?? '00:01 - 23:59' }}.</p>
                    </div>
                    <div class="badge-row">
                        <span class="badge-soft">Carregados: {{ $fmt($metaExpedicao['realizado'] ?? 0) }}</span>
                        <span class="badge-soft">Programados: {{ $fmt($metaExpedicao['disponibilidade'] ?? 0) }}</span>
                        <span class="badge-soft">Meta: {{ $fmt($metaExpedicao['meta'] ?? 0) }}</span>
                        <span class="badge-soft {{ ($metaExpedicao['gap'] ?? 0) >= 0 ? 'good' : 'bad' }}">
                            Gap disp.: {{ ($metaExpedicao['gap_disponibilidade'] ?? 0) >= 0 ? '+' : '' }}{{ $fmt($metaExpedicao['gap_disponibilidade'] ?? 0) }}
                        </span>
                    </div>
                </div>
                <div class="chart-box chart-box-projection">
                    <canvas id="chartMetaExpedicaoDiaria"></canvas>
                </div>
                <div class="mini-metrics three-cols compact">
                    <div>
                        <span>Ritmo/hora</span>
                        <strong>{{ number_format((float) ($metaExpedicao['ritmo_hora'] ?? 0), 1, ',', '.') }}</strong>
                    </div>
                    <div>
                        <span>Programado esperado</span>
                        <strong>{{ $fmt($metaExpedicao['esperado_agora'] ?? 0) }}</strong>
                    </div>
                    <div>
                        <span>Projeção</span>
                        <strong>{{ $fmt($metaExpedicao['projecao_fechamento'] ?? 0) }}</strong>
                    </div>
                </div>
            </section>

            <section class="panel span-2">
                <div class="panel-title">
                    <div>
                        <h2>Caixas separadas por dia no mês</h2>
                        <p>Comparativo diário entre meta operacional, caixas disponíveis e realizado no picking.</p>
                    </div>
                    <span class="badge-soft">Meta {{ $fmt($resumo['meta_caixas_dia'] ?? 22500) }}/dia</span>
                </div>
                <div class="chart-box">
                    <canvas id="chartCaixasDiaMes"></canvas>
                </div>
                <div class="mini-metrics three-cols compact">
                    <div>
                        <span>Disponível mês</span>
                        <strong>{{ $fmt($dadosGraficos['caixasPorDiaMes']['disponibilidade_total'] ?? 0) }}</strong>
                    </div>
                    <div>
                        <span>Realizado mês</span>
                        <strong>{{ $fmt($resumo['caixas_mes'] ?? 0) }}</strong>
                    </div>
                    <div>
                        <span>Atend. disp.</span>
                        <strong>{{ $fmtPct(($dadosGraficos['caixasPorDiaMes']['disponibilidade_total'] ?? 0) > 0 ? (($dadosGraficos['caixasPorDiaMes']['total'] ?? 0) / ($dadosGraficos['caixasPorDiaMes']['disponibilidade_total'] ?? 1)) * 100 : 0) }}</strong>
                    </div>
                </div>
            </section>

            <section class="panel span-2">
                <div class="panel-title">
                    <div>
                        <h2>Veículos por dia no mês</h2>
                        <p>Comparativo diário entre meta operacional, veículos programados e veículos realizados.</p>
                    </div>
                    <span class="badge-soft">Meta {{ $fmt($dadosGraficos['veiculosPorDiaMes']['meta'] ?? 80) }}/dia</span>
                </div>
                <div class="chart-box">
                    <canvas id="chartVeiculosDiaMes"></canvas>
                </div>
                <div class="mini-metrics three-cols compact">
                    <div>
                        <span>Programados mês</span>
                        <strong>{{ $fmt($dadosGraficos['veiculosPorDiaMes']['total_programado'] ?? 0) }}</strong>
                    </div>
                    <div>
                        <span>Realizados mês</span>
                        <strong>{{ $fmt($dadosGraficos['veiculosPorDiaMes']['total_realizado'] ?? 0) }}</strong>
                    </div>
                    <div>
                        <span>Atend. prog.</span>
                        <strong>{{ $fmtPct(($dadosGraficos['veiculosPorDiaMes']['total_programado'] ?? 0) > 0 ? (($dadosGraficos['veiculosPorDiaMes']['total_realizado'] ?? 0) / ($dadosGraficos['veiculosPorDiaMes']['total_programado'] ?? 1)) * 100 : 0) }}</strong>
                    </div>
                </div>
            </section>

            <section class="panel span-2">
                <div class="panel-title">
                    <div>
                        <h2>Caixas de oportunidades por dia no mês</h2>
                        <p>Meta operacional x disponibilidade x realizado de caixas em demandas não programadas.</p>
                    </div>
                    <span class="badge-soft">Meta {{ $fmt($resumo['meta_caixas_oportunidade_dia'] ?? 11000) }}/dia</span>
                </div>
                <div class="chart-box">
                    <canvas id="chartCaixasOportunidadesDiaMes"></canvas>
                </div>
                <div class="mini-metrics three-cols compact">
                    <div>
                        <span>Disponível mês</span>
                        <strong>{{ $fmt($dadosGraficos['caixasOportunidadesPorDiaMes']['disponibilidade_total'] ?? 0) }}</strong>
                    </div>
                    <div>
                        <span>Realizado mês</span>
                        <strong>{{ $fmt($dadosGraficos['caixasOportunidadesPorDiaMes']['total'] ?? 0) }}</strong>
                    </div>
                    <div>
                        <span>Atend. disp.</span>
                        <strong>{{ $fmtPct(($dadosGraficos['caixasOportunidadesPorDiaMes']['disponibilidade_total'] ?? 0) > 0 ? (($dadosGraficos['caixasOportunidadesPorDiaMes']['total'] ?? 0) / ($dadosGraficos['caixasOportunidadesPorDiaMes']['disponibilidade_total'] ?? 1)) * 100 : 0) }}</strong>
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
                        <span>Separação finalizada</span>
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
                        <span>Expedição finalizada</span>
                        <strong>{{ $fmt($visaoDemanda['total_expedido'] ?? $resumo['expedidas'] ?? 0) }}</strong>
                    </div>
                    <div>
                        <span>separadas aguardando expedição</span>
                        <strong>{{ $fmt($resumo['separadas_aguardando_expedicao'] ?? 0) }}</strong>
                    </div>
                </div>
            </section>

            <section class="panel span-4">
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

    .section-heading {
        align-items: center;
        display: flex;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 12px;
    }

    .section-heading h2 {
        color: #fff;
        font-size: 18px;
        margin: 0;
    }

    .section-heading p {
        color: var(--muted);
        margin: 4px 0 0;
    }

    .previsibilidade-block {
        background: rgba(15, 23, 42, 0.62);
        border: 1px solid var(--border);
        border-radius: 8px;
        margin-bottom: 14px;
        padding: 16px;
    }

    .previsibilidade-grid {
        display: grid;
        gap: 10px;
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }

    .previs-card {
        align-items: center;
        background: rgba(15, 23, 42, 0.86);
        border: 1px solid rgba(56, 189, 248, 0.22);
        border-radius: 8px;
        display: grid;
        gap: 10px;
        grid-template-columns: minmax(0, 1fr) auto;
        min-height: 96px;
        padding: 13px;
    }

    .previs-card.ok {
        border-color: rgba(34, 197, 94, 0.36);
    }

    .previs-card.warning {
        border-color: rgba(245, 158, 11, 0.46);
    }

    .previs-card.danger {
        border-color: rgba(239, 68, 68, 0.46);
    }

    .previs-card span,
    .previs-card small {
        color: var(--muted);
        display: block;
        font-weight: 800;
        text-transform: uppercase;
    }

    .previs-card span {
        font-size: 11px;
    }

    .previs-card strong {
        color: #fff;
        display: block;
        font-size: 30px;
        line-height: 1;
        margin: 10px 0 6px;
    }

    .previs-card b {
        background: rgba(56, 189, 248, 0.12);
        border-radius: 999px;
        color: #bae6fd;
        font-size: 12px;
        padding: 6px 8px;
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

    .compact-kpis {
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }

    .gauge-overview {
        display: grid;
        gap: 12px;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        margin-bottom: 14px;
    }

    .gauge-card {
        background: var(--panel-bg);
        border: 1px solid var(--border);
        border-radius: 8px;
        box-shadow: 0 14px 34px rgba(2, 6, 23, 0.2);
        min-height: 190px;
        padding: 14px;
    }

    .gauge-head {
        align-items: flex-start;
        display: flex;
        gap: 8px;
        justify-content: space-between;
        min-height: 36px;
    }

    .gauge-head span,
    .gauge-card small {
        color: var(--muted);
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
    }

    .gauge-head b {
        color: #fff;
        font-size: 13px;
        white-space: nowrap;
    }

    .gauge-wrap {
        height: 112px;
        margin-top: 6px;
        position: relative;
    }

    .donut-wrap {
        height: 128px;
        margin-top: 2px;
        position: relative;
    }

    .gauge-card small {
        display: block;
        line-height: 1.25;
        margin-top: 6px;
        text-transform: none;
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

    .kpi-card.success {
        border-color: rgba(34, 197, 94, 0.58);
    }

    .kpi-card.warning {
        border-color: rgba(245, 158, 11, 0.58);
    }

    .kpi-card.danger {
        border-color: rgba(239, 68, 68, 0.58);
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

    .span-4 {
        grid-column: span 4;
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

    .badge-row {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        justify-content: flex-end;
    }

    .chart-box {
        height: 224px;
        position: relative;
    }

    .chart-box-projection {
        height: 285px;
    }

    .chart-box-hero {
        height: 330px;
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

        .previsibilidade-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .gauge-overview {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .span-4 {
            grid-column: span 2;
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
        .previsibilidade-grid,
        .gauge-overview,
        .attention-list {
            grid-template-columns: 1fr;
        }

        .span-2,
        .span-4 {
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
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<script nonce="{{ $cspNonce ?? '' }}">
document.addEventListener('DOMContentLoaded', function () {
    if (typeof Chart === 'undefined') {
        return;
    }

    if (typeof ChartDataLabels !== 'undefined') {
        Chart.register(ChartDataLabels);
        Chart.defaults.plugins.datalabels.display = false;
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
    const numeroValido = (value) => value !== null && value !== undefined && Number(value) > 0;
    const percent = (value, total) => Number(total || 0) > 0 ? (Number(value || 0) / Number(total || 0)) * 100 : 0;
    const percentColor = (value) => value >= 90 ? '#22c55e' : (value >= 75 ? '#f59e0b' : '#ef4444');
    const ultimoIndiceComValor = (dataset) => {
        const data = dataset?.data || [];
        for (let index = data.length - 1; index >= 0; index--) {
            if (numeroValido(data[index])) {
                return index;
            }
        }

        return -1;
    };

    Chart.defaults.color = textColor;
    Chart.defaults.font.family = 'Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif';

    const centerTextPlugin = {
        id: 'centerText',
        afterDraw(chart, args, options) {
            const text = options?.text;
            if (!text) {
                return;
            }

            const { ctx, chartArea } = chart;
            const centerX = (chartArea.left + chartArea.right) / 2;
            const centerY = options.type === 'gauge'
                ? chartArea.bottom - 10
                : (chartArea.top + chartArea.bottom) / 2;

            ctx.save();
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillStyle = options.color || '#fff';
            ctx.font = '900 24px Inter, system-ui, sans-serif';
            ctx.fillText(text, centerX, centerY);

            if (options.subtext) {
                ctx.fillStyle = '#94a3b8';
                ctx.font = '800 11px Inter, system-ui, sans-serif';
                ctx.fillText(options.subtext, centerX, centerY + 22);
            }

            ctx.restore();
        }
    };

    const gaugeNeedlePlugin = {
        id: 'gaugeNeedle',
        afterDatasetDraw(chart, args, options) {
            if (!options?.display || args.index !== 0) {
                return;
            }

            const meta = chart.getDatasetMeta(0);
            const arc = meta.data?.[0];
            if (!arc) {
                return;
            }

            const value = Math.min(120, Math.max(0, Number(options.value || 0)));
            const angle = Math.PI + (value / 120) * Math.PI;
            const cx = arc.x;
            const cy = arc.y;
            const radius = arc.outerRadius * 0.82;
            const needleX = cx + Math.cos(angle) * radius;
            const needleY = cy + Math.sin(angle) * radius;
            const { ctx } = chart;

            ctx.save();
            ctx.strokeStyle = '#f8fafc';
            ctx.fillStyle = '#f8fafc';
            ctx.lineWidth = 2;
            ctx.beginPath();
            ctx.moveTo(cx, cy);
            ctx.lineTo(needleX, needleY);
            ctx.stroke();
            ctx.beginPath();
            ctx.arc(cx, cy, 4, 0, Math.PI * 2);
            ctx.fill();
            ctx.restore();
        }
    };

    Chart.register(centerTextPlugin, gaugeNeedlePlugin);

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

    function renderGauge(id, value, total, unitLabel) {
        const canvas = document.getElementById(id);
        if (!canvas) {
            return;
        }

        const pct = percent(value, total);

        new Chart(canvas, {
            type: 'doughnut',
            data: {
                labels: ['Crítico', 'Atenção', 'Dentro do projeto'],
                datasets: [{
                    data: [75, 15, 30],
                    backgroundColor: ['#ef4444', '#f59e0b', '#22c55e'],
                    borderColor: ['rgba(255,255,255,.08)', 'rgba(255,255,255,.08)', 'rgba(255,255,255,.08)'],
                    borderWidth: 1,
                    cutout: '72%'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                rotation: -90,
                circumference: 180,
                plugins: {
                    legend: { display: false },
                    datalabels: { display: false },
                    gaugeNeedle: {
                        display: true,
                        value: pct
                    },
                    centerText: {
                        type: 'gauge',
                        text: `${pct.toFixed(1).replace('.', ',')}%`,
                        subtext: `${formatCaixas(value)} / ${formatCaixas(total)} ${unitLabel}`,
                        color: percentColor(pct)
                    },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => `${ctx.label}: ${formatCaixas(ctx.raw)}%`
                        }
                    }
                }
            }
        });
    }

    function cardValor(titulo) {
        const card = (dados.previsibilidade?.cards || []).find((item) => item.titulo === titulo);
        return Number(card?.valor || 0);
    }

    function renderComposicaoDia() {
        const canvas = document.getElementById('donutComposicaoDia');
        if (!canvas) {
            return;
        }

        const totalDia = cardValor('Demanda dia');
        const realizado = Number(dados.metasDiarias?.expedicao?.realizado ?? cardValor('Carregamento') ?? cardValor('Realizado') ?? 0);
        const naPlanta = cardValor('Na planta');
        const faltaChegar = Math.max(0, totalDia - realizado - naPlanta);

        new Chart(canvas, {
            type: 'doughnut',
            data: {
                labels: ['Realizado', 'Na planta', 'Falta chegar'],
                datasets: [{
                    data: [realizado, naPlanta, faltaChegar],
                    backgroundColor: ['#22c55e', '#38bdf8', '#f59e0b'],
                    borderColor: '#111827',
                    borderWidth: 3,
                    cutout: '64%'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 10 } },
                    datalabels: { display: false },
                    centerText: {
                        text: formatCaixas(realizado),
                        subtext: 'realizado'
                    },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => `${ctx.label}: ${formatCaixas(ctx.raw)}`
                        }
                    }
                }
            }
        });
    }

    renderGauge('gaugeExpedicaoRealizada', dados.metasDiarias?.expedicao?.realizado, dados.metasDiarias?.expedicao?.disponibilidade, 'veículos');
    renderGauge('gaugeSeparacaoRealizada', dados.metasDiarias?.separacao?.realizado, dados.metasDiarias?.separacao?.disponibilidade, 'caixas');
    renderGauge('gaugeExpedicaoProjetada', dados.metasDiarias?.expedicao?.projecao_fechamento, dados.metasDiarias?.expedicao?.disponibilidade, 'veículos');
    renderGauge('gaugeSeparacaoProjetada', dados.metasDiarias?.separacao?.projecao_fechamento, dados.metasDiarias?.separacao?.disponibilidade, 'caixas');
    renderComposicaoDia();

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

    const chartProjecao12h = document.getElementById('chartProjecao12h');
    if (chartProjecao12h && dados.projecaoProdutividade12h) {
        const projecao = dados.projecaoProdutividade12h;
        const isJanelaMeta = (item) => {
            const hora = Number(String(item?.hora || '00:00').slice(0, 2));
            return hora >= 12 && hora <= 23;
        };
        const curvaIdealMeta = (projecao.curvaIdeal || []).filter(isJanelaMeta);
        const apontamentos = projecao.apontamentos || [];
        const apontamentosMeta = apontamentos.filter(isJanelaMeta);
        const projecaoCorrigidaMeta = (projecao.projecaoCorrigida || []).filter(isJanelaMeta);
        const pontosAntesDas12 = apontamentos.filter((item) => {
            const hora = Number(String(item?.hora || '00:00').slice(0, 2));
            return hora < 12 && item?.acumulado !== null;
        });
        const baseAntesDas12 = pontosAntesDas12.length
            ? Number(pontosAntesDas12[pontosAntesDas12.length - 1].acumulado || 0)
            : 0;

        new Chart(chartProjecao12h, {
            type: 'line',
            data: {
                labels: curvaIdealMeta.map((item) => item.hora),
                datasets: [
                    {
                        label: 'Caixas separadas',
                        data: apontamentosMeta.map((item) => Math.max(0, Number(item.acumulado || 0) - baseAntesDas12)),
                        borderColor: '#2563eb',
                        backgroundColor: 'rgba(37, 99, 235, 0.14)',
                        borderWidth: 4,
                        pointRadius: 4,
                        tension: 0.25,
                        fill: false
                    },
                    {
                        label: 'Curva ideal',
                        data: curvaIdealMeta.map((item) => item.valor),
                        borderColor: '#22c55e',
                        borderDash: [6, 4],
                        borderWidth: 2,
                        pointRadius: 0,
                        tension: 0.35,
                        fill: false
                    },
                    {
                        label: 'Projeção corrigida',
                        data: projecaoCorrigidaMeta.map((item) => item.valor),
                        borderColor: '#f59e0b',
                        borderDash: [10, 5],
                        borderWidth: 2,
                        pointRadius: 0,
                        tension: 0.35,
                        fill: false
                    },
                    {
                        label: `Meta ${formatCaixas(projecao.meta || 11000)} caixas`,
                        data: curvaIdealMeta.map(() => projecao.meta || 11000),
                        borderColor: '#ef4444',
                        borderDash: [2, 2],
                        borderWidth: 2,
                        pointRadius: 0,
                        fill: false
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top' },
                    datalabels: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => `${ctx.dataset.label}: ${formatCaixas(ctx.raw)} caixas`
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { color: gridColor },
                        title: { display: true, text: 'Hora' }
                    },
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

    const chartCaixasHora = document.getElementById('chartCaixasHora');
    if (chartCaixasHora && dados.caixasPorHora) {
        new Chart(chartCaixasHora, {
            type: 'bar',
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

    function renderMetaDiariaChart(id, setor) {
        const canvas = document.getElementById(id);
        if (!canvas || !setor?.series) {
            return;
        }

        new Chart(canvas, {
            type: 'bar',
            data: {
                labels: setor.series.labels,
                datasets: [
                    {
                        type: 'bar',
                        label: 'Disponível',
                        data: setor.series.ideal,
                        backgroundColor: 'rgba(34, 197, 94, 0.32)',
                        borderColor: 'rgba(34, 197, 94, 0.74)',
                        borderWidth: 1,
                        borderRadius: 4,
                        maxBarThickness: 20
                    },
                    {
                        type: 'bar',
                        label: 'Realizado',
                        data: setor.series.real,
                        backgroundColor: 'rgba(37, 99, 235, 0.78)',
                        borderColor: '#60a5fa',
                        borderWidth: 1,
                        borderRadius: 4,
                        maxBarThickness: 20
                    },
                    {
                        type: 'line',
                        label: 'Projeção',
                        data: setor.series.projecao,
                        borderColor: '#f59e0b',
                        backgroundColor: 'rgba(245, 158, 11, 0.14)',
                        borderWidth: 3,
                        pointRadius: 3,
                        pointHoverRadius: 6,
                        tension: 0.32,
                        fill: false
                    },
                    {
                        type: 'line',
                        label: 'Meta',
                        data: setor.series.meta,
                        borderColor: '#ef4444',
                        backgroundColor: 'rgba(239, 68, 68, 0.10)',
                        borderWidth: 2,
                        borderDash: [6, 4],
                        pointRadius: 0,
                        tension: 0,
                        fill: false
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top' },
                    datalabels: {
                        display: (ctx) => {
                            if (!numeroValido(ctx.raw)) {
                                return false;
                            }

                            if (ctx.dataset.type === 'line') {
                                return ctx.dataIndex === ultimoIndiceComValor(ctx.dataset);
                            }

                            return ctx.dataIndex % 3 === 0 || ctx.dataIndex === ultimoIndiceComValor(ctx.dataset);
                        },
                        align: (ctx) => ctx.dataset.type === 'line' ? 'top' : 'end',
                        anchor: (ctx) => ctx.dataset.type === 'line' ? 'end' : 'end',
                        backgroundColor: (ctx) => ctx.dataset.type === 'line'
                            ? 'rgba(245, 158, 11, 0.18)'
                            : 'rgba(15, 23, 42, 0.82)',
                        borderColor: (ctx) => ctx.dataset.type === 'line'
                            ? 'rgba(245, 158, 11, 0.52)'
                            : 'rgba(148, 163, 184, 0.30)',
                        borderRadius: 4,
                        borderWidth: 1,
                        color: '#f8fafc',
                        clamp: true,
                        clip: false,
                        font: {
                            size: 10,
                            weight: '800'
                        },
                        formatter: (value) => formatCaixas(value),
                        offset: 4,
                        padding: {
                            top: 3,
                            right: 5,
                            bottom: 3,
                            left: 5
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => `${ctx.dataset.label}: ${formatCaixas(ctx.raw)} ${setor.unidade}`
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { color: gridColor },
                        title: { display: true, text: 'Hora' }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: gridColor },
                        ticks: { callback: (value) => formatCaixas(value) },
                        title: { display: true, text: setor.unidade.charAt(0).toUpperCase() + setor.unidade.slice(1) }
                    }
                }
            }
        });
    }

    renderMetaDiariaChart('chartMetaSeparacaoDiaria', dados.metasDiarias?.separacao);
    renderMetaDiariaChart('chartMetaExpedicaoDiaria', dados.metasDiarias?.expedicao);

    function deveMostrarRotuloMensal(ctx) {
        if (!numeroValido(ctx.raw)) {
            return false;
        }

        if (ctx.dataset.type === 'line') {
            return ctx.dataIndex === ultimoIndiceComValor(ctx.dataset);
        }

        const label = String(ctx.dataset.label || '').toLowerCase();
        const datasets = ctx.chart?.data?.datasets || [];
        const realizadoDataset = datasets.find((dataset) => String(dataset.label || '').toLowerCase().includes('realiz'));
        const disponibilidadeDataset = datasets.find((dataset) => {
            const nome = String(dataset.label || '').toLowerCase();
            return nome.includes('dispon') || nome.includes('program');
        });
        const realizado = Number(realizadoDataset?.data?.[ctx.dataIndex] || 0);
        const disponibilidade = Number(disponibilidadeDataset?.data?.[ctx.dataIndex] || 0);

        if ((label.includes('dispon') || label.includes('program')) && realizado > 0 && Math.abs(realizado - disponibilidade) <= 0.1) {
            return false;
        }

        if (label.includes('realiz')) {
            return true;
        }

        const pontosComValor = (ctx.dataset.data || []).filter(numeroValido).length;
        return pontosComValor <= 10 || ctx.dataIndex === ultimoIndiceComValor(ctx.dataset);
    }

    function renderGroupedBarChart(id, labels, datasets, yTitle, tooltipSuffix) {
        const canvas = document.getElementById(id);
        if (!canvas) {
            return;
        }

        new Chart(canvas, {
            type: 'bar',
            data: {
                labels,
                datasets: datasets.map((dataset) => ({
                    type: dataset.type || 'bar',
                    borderRadius: 5,
                    maxBarThickness: dataset.type === 'line' ? undefined : 18,
                    ...dataset
                }))
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' },
                    datalabels: {
                        display: deveMostrarRotuloMensal,
                        align: (ctx) => {
                            const label = String(ctx.dataset.label || '').toLowerCase();
                            if (ctx.dataset.type === 'line') {
                                return 'top';
                            }

                            return label.includes('realiz') ? 'end' : 'center';
                        },
                        anchor: (ctx) => ctx.dataset.type === 'line' ? 'end' : 'end',
                        backgroundColor: (ctx) => ctx.dataset.type === 'line' ? 'rgba(127, 29, 29, 0.72)' : 'rgba(15, 23, 42, 0.72)',
                        borderRadius: 4,
                        color: (ctx) => ctx.dataset.type === 'line' ? '#fecaca' : '#dbeafe',
                        clamp: true,
                        clip: false,
                        font: { size: 9, weight: '800' },
                        offset: (ctx) => ctx.dataset.type === 'line' ? 6 : 2,
                        padding: { top: 2, right: 4, bottom: 2, left: 4 },
                        formatter: (value) => formatCaixas(value)
                    },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => `${ctx.dataset.label}: ${formatCaixas(ctx.raw)} ${tooltipSuffix}`
                        }
                    }
                },
                scales: {
                    x: { grid: { display: false }, title: { display: true, text: 'Dia' } },
                    y: {
                        beginAtZero: true,
                        grid: { color: gridColor },
                        ticks: { callback: (value) => formatCaixas(value) },
                        title: { display: true, text: yTitle }
                    }
                }
            }
        });
    }

    if (dados.caixasPorDiaMes) {
        renderGroupedBarChart('chartCaixasDiaMes', dados.caixasPorDiaMes.labels, [
            {
                type: 'line',
                label: 'Meta',
                data: dados.caixasPorDiaMes.metaValues || dados.caixasPorDiaMes.labels.map(() => dados.caixasPorDiaMes.meta),
                backgroundColor: 'rgba(239, 68, 68, 0.10)',
                borderColor: 'rgba(239, 68, 68, 0.95)',
                borderWidth: 2,
                borderDash: [6, 4],
                pointRadius: 0,
                tension: 0,
                fill: false
            },
            {
                label: 'Disponível',
                data: dados.caixasPorDiaMes.disponibilidadeValues || [],
                backgroundColor: 'rgba(34, 197, 94, 0.42)',
                borderColor: 'rgba(34, 197, 94, 0.82)',
                borderWidth: 1
            },
            {
                label: 'Realizado',
                data: dados.caixasPorDiaMes.values,
                backgroundColor: 'rgba(37, 99, 235, 0.82)',
                borderColor: '#60a5fa',
                borderWidth: 1
            }
        ], 'Caixas', 'caixas');
    }

    if (dados.veiculosPorDiaMes) {
        renderGroupedBarChart('chartVeiculosDiaMes', dados.veiculosPorDiaMes.labels, [
            {
                type: 'line',
                label: 'Meta',
                data: dados.veiculosPorDiaMes.metaValues || dados.veiculosPorDiaMes.labels.map(() => dados.veiculosPorDiaMes.meta),
                backgroundColor: 'rgba(239, 68, 68, 0.10)',
                borderColor: 'rgba(239, 68, 68, 0.95)',
                borderWidth: 2,
                borderDash: [6, 4],
                pointRadius: 0,
                tension: 0,
                fill: false
            },
            {
                label: 'Programados',
                data: dados.veiculosPorDiaMes.programados,
                backgroundColor: 'rgba(34, 197, 94, 0.42)',
                borderColor: 'rgba(34, 197, 94, 0.82)',
                borderWidth: 1
            },
            {
                label: 'Realizados',
                data: dados.veiculosPorDiaMes.realizados,
                backgroundColor: 'rgba(37, 99, 235, 0.82)',
                borderColor: '#60a5fa',
                borderWidth: 1
            }
        ], 'Veículos', 'veículos');
    }

    if (dados.caixasOportunidadesPorDiaMes) {
        renderGroupedBarChart('chartCaixasOportunidadesDiaMes', dados.caixasOportunidadesPorDiaMes.labels, [
            {
                type: 'line',
                label: 'Meta',
                data: dados.caixasOportunidadesPorDiaMes.metaValues || dados.caixasOportunidadesPorDiaMes.labels.map(() => dados.caixasOportunidadesPorDiaMes.meta),
                backgroundColor: 'rgba(239, 68, 68, 0.10)',
                borderColor: 'rgba(239, 68, 68, 0.95)',
                borderWidth: 2,
                borderDash: [6, 4],
                pointRadius: 0,
                tension: 0,
                fill: false
            },
            {
                label: 'Disponível',
                data: dados.caixasOportunidadesPorDiaMes.disponibilidadeValues || [],
                backgroundColor: 'rgba(34, 197, 94, 0.42)',
                borderColor: 'rgba(34, 197, 94, 0.82)',
                borderWidth: 1
            },
            {
                label: 'Realizado',
                data: dados.caixasOportunidadesPorDiaMes.values,
                backgroundColor: 'rgba(37, 99, 235, 0.82)',
                borderColor: '#60a5fa',
                borderWidth: 1
            }
        ], 'Caixas', 'caixas');
    }

});
</script>
@endpush
