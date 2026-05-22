@extends('layouts.app')

@section('content')
@php
    $queryBase = array_filter([
        'data_inicio' => $filtros['data_inicio'] ?? null,
        'data_fim' => $filtros['data_fim'] ?? null,
        'tipo_demanda' => $filtros['tipo_demanda'] ?? 'TODAS',
    ], fn ($value) => $value !== null && $value !== '');
@endphp

<div class="container-fluid px-4 py-3 relatorios-operacionais">
    @include('partials.breadcrumb-auto')

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div class="d-flex align-items-center">
            <div class="icon-wrapper me-3">
                <i class="mdi mdi-file-chart-outline display-6"></i>
            </div>
            <div>
                <div class="report-eyebrow">Central operacional</div>
                <h3 class="mb-1 fw-bold text-white">Relatórios Operacionais</h3>
                <p class="text-muted mb-0 small">Exportações para gestão, liderança e acompanhamento do ciclo da DT.</p>
            </div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('demandas.reportGerencial') }}" class="btn btn-primary btn-sm">
                <i class="mdi mdi-view-dashboard-outline me-1"></i> Report gerencial
            </a>
            <a href="{{ route('demandas.reportTurno') }}" class="btn btn-outline-light btn-sm">
                <i class="mdi mdi-whatsapp me-1"></i> Report de turno
            </a>
            <a href="{{ route('demandas.dashboardOperacional') }}" class="btn btn-outline-light btn-sm">Dashboard</a>
        </div>
    </div>

    <form method="GET" action="{{ route('demandas.relatorios') }}" class="report-panel mb-3">
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Data inicial</label>
                <input type="date" name="data_inicio" class="form-control" value="{{ $filtros['data_inicio'] ?? '' }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Data final</label>
                <input type="date" name="data_fim" class="form-control" value="{{ $filtros['data_fim'] ?? '' }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Demanda</label>
                <select name="tipo_demanda" class="form-select">
                    <option value="TODAS" @selected(($filtros['tipo_demanda'] ?? 'TODAS') === 'TODAS')>Todas</option>
                    <option value="PROGRAMADA" @selected(($filtros['tipo_demanda'] ?? 'TODAS') === 'PROGRAMADA')>Programada</option>
                    <option value="OPORTUNIDADE" @selected(($filtros['tipo_demanda'] ?? 'TODAS') === 'OPORTUNIDADE')>Oportunidade</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-fill">
                    <i class="mdi mdi-filter-outline me-1"></i> Aplicar
                </button>
                <a href="{{ route('demandas.relatorios') }}" class="btn btn-outline-light">Limpar</a>
            </div>
        </div>
    </form>

    <div class="row g-3">
        <div class="col-md-3">
            <div class="metric-card">
                <small>DTs Picking</small>
                <strong>{{ $total }}</strong>
            </div>
        </div>
        <div class="col-md-3">
            <div class="metric-card">
                <small>Separação parcial</small>
                <strong>{{ $parcial }}</strong>
            </div>
        </div>
        <div class="col-md-3">
            <div class="metric-card">
                <small>Separação completa</small>
                <strong>{{ $completa }}</strong>
            </div>
        </div>
        <div class="col-md-3">
            <div class="metric-card">
                <small>Em aberto na separação</small>
                <strong>{{ $abertas }}</strong>
            </div>
        </div>
    </div>

    <div class="report-panel mt-4">
        <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-3">
            <div>
                <h5 class="mb-1 text-white">Exportações disponíveis</h5>
                <p class="text-muted small mb-0">Excel para análise e PDF para envio/arquivo. Os filtros acima entram nos dois formatos.</p>
            </div>
            <div class="tempo-medio">
                <span>Tempo médio da separação</span>
                <strong>{{ $tempoMedioMin !== null ? $tempoMedioMin.' min' : '-' }}</strong>
            </div>
        </div>

        <div class="row g-3">
            @foreach($relatoriosExportaveis as $slug => $relatorio)
                <div class="col-xl-4 col-md-6">
                    <div class="export-card h-100">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div>
                                <span class="status-pill">{{ $relatorio['status'] }}</span>
                                <h6>{{ $relatorio['titulo'] }}</h6>
                            </div>
                            <i class="mdi mdi-file-export-outline export-icon"></i>
                        </div>
                        <p>{{ $relatorio['descricao'] }}</p>
                        <div class="d-flex gap-2 mt-auto">
                            <a
                                href="{{ route('demandas.relatorios.export', ['tipo' => $slug, 'formato' => 'excel'] + $queryBase) }}"
                                class="btn btn-success btn-sm flex-fill"
                            >
                                <i class="mdi mdi-file-excel-outline me-1"></i> Excel
                            </a>
                            <a
                                href="{{ route('demandas.relatorios.export', ['tipo' => $slug, 'formato' => 'pdf'] + $queryBase) }}"
                                class="btn btn-outline-light btn-sm flex-fill"
                            >
                                <i class="mdi mdi-file-pdf-box me-1"></i> PDF
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="report-panel mt-4">
        <h5 class="mb-2 text-white">Telas operacionais conectadas</h5>
        <div class="quick-links">
            <a href="{{ route('demandas.reportGerencial') }}">Onepage gerencial</a>
            <a href="{{ route('demandas.reportTurno') }}">Resumo WhatsApp</a>
            <a href="{{ route('demandas.identificacaoA4') }}">Identificação A4</a>
            <a href="{{ route('expedicao.saida-veiculos.index') }}">Saída de veículos</a>
        </div>
    </div>
</div>

<style>
    .relatorios-operacionais {
        color: #f8fafc;
    }

    .icon-wrapper {
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #3b82f6 0%, #7c3aed 100%);
        border-radius: 12px;
        box-shadow: 0 4px 16px rgba(59, 130, 246, 0.35);
    }

    .icon-wrapper i {
        color: #fff !important;
    }

    .report-eyebrow {
        color: #38bdf8;
        font-size: .72rem;
        font-weight: 800;
        letter-spacing: 0;
        text-transform: uppercase;
    }

    .report-panel,
    .metric-card,
    .export-card {
        background: rgba(15, 23, 42, .92);
        border: 1px solid rgba(148, 163, 184, .22);
        border-radius: 8px;
        box-shadow: 0 12px 30px rgba(0, 0, 0, .18);
    }

    .report-panel {
        padding: 1rem;
    }

    .report-panel .form-label {
        color: #bfdbfe;
        font-size: .78rem;
        font-weight: 700;
    }

    .report-panel .form-control,
    .report-panel .form-select {
        background: rgba(2, 6, 23, .65);
        border-color: rgba(148, 163, 184, .28);
        color: #fff;
    }

    .metric-card {
        padding: 1rem;
    }

    .metric-card small,
    .tempo-medio span {
        display: block;
        color: #94a3b8;
        font-size: .78rem;
        font-weight: 700;
    }

    .metric-card strong {
        display: block;
        color: #fff;
        font-size: 1.7rem;
        line-height: 1.1;
    }

    .tempo-medio {
        min-width: 220px;
        padding: .75rem 1rem;
        border: 1px solid rgba(56, 189, 248, .28);
        border-radius: 8px;
        background: rgba(14, 165, 233, .08);
    }

    .tempo-medio strong {
        color: #fff;
        font-size: 1.1rem;
    }

    .export-card {
        display: flex;
        flex-direction: column;
        gap: .75rem;
        padding: 1rem;
    }

    .export-card h6 {
        margin: .35rem 0 0;
        color: #fff;
        font-weight: 800;
    }

    .export-card p {
        color: #cbd5e1;
        font-size: .86rem;
        margin: 0;
    }

    .status-pill {
        display: inline-flex;
        align-items: center;
        border: 1px solid rgba(34, 197, 94, .35);
        border-radius: 999px;
        color: #86efac;
        background: rgba(34, 197, 94, .08);
        font-size: .68rem;
        font-weight: 800;
        padding: .18rem .5rem;
    }

    .export-icon {
        color: #60a5fa;
        font-size: 1.5rem;
    }

    .quick-links {
        display: flex;
        flex-wrap: wrap;
        gap: .75rem;
    }

    .quick-links a {
        color: #bfdbfe;
        border: 1px solid rgba(191, 219, 254, .25);
        border-radius: 8px;
        padding: .55rem .75rem;
        text-decoration: none;
    }
</style>
@endsection
