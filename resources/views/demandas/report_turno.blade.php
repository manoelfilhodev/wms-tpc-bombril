@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3 report-page">
    @include('partials.breadcrumb-auto')

    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <div class="d-flex align-items-center">
            <div class="icon-wrapper me-3">
                <i class="mdi mdi-whatsapp display-6"></i>
            </div>
            <div>
                <h3 class="mb-1 fw-bold text-dark">Report de Turno</h3>
                <p class="text-muted mb-0 small">Resumo simples para print e envio no WhatsApp</p>
            </div>
        </div>
        <div class="d-flex gap-2">
            @if(Auth::user()?->tipo === 'operador')
                <a href="{{ route('painel.operador') }}" class="btn btn-outline-secondary btn-sm">Voltar ao menu</a>
            @else
                <a href="{{ route('demandas.relatorios') }}" class="btn btn-outline-secondary btn-sm">Voltar relatórios</a>
            @endif
            <button type="button" class="btn btn-primary btn-sm" onclick="window.print()">
                <i class="mdi mdi-printer me-1"></i> Imprimir
            </button>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4 no-print">
        <div class="card-body">
            <form method="GET" action="{{ route('demandas.reportTurno') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Mensagem</label>
                    <input type="text" name="mensagem" class="form-control form-control-sm" value="{{ $mensagem }}" maxlength="80">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">Data do turno</label>
                    <input type="date" name="data" class="form-control form-control-sm" value="{{ $dataSelecionada }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Turno</label>
                    <select name="turno" class="form-select form-select-sm">
                        @foreach($turnosOperacionais as $codigo => $turno)
                            <option value="{{ $codigo }}" @selected($turnoSelecionado === $codigo)>
                                {{ $turno['label'] }} - {{ $turno['periodo'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">Demanda</label>
                    <select name="tipo_demanda" class="form-select form-select-sm">
                        <option value="TODAS" @selected(($tipoDemanda ?? 'TODAS') === 'TODAS')>Todas</option>
                        <option value="PROGRAMADA" @selected(($tipoDemanda ?? 'TODAS') === 'PROGRAMADA')>Programada</option>
                        <option value="OPORTUNIDADE" @selected(($tipoDemanda ?? 'TODAS') === 'OPORTUNIDADE')>Oportunidade</option>
                    </select>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-sm btn-primary w-100">
                        <i class="mdi mdi-refresh me-1"></i> Gerar
                    </button>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <a href="{{ route('demandas.reportTurno') }}" class="btn btn-sm btn-outline-secondary w-100">Hoje</a>
                </div>
            </form>
            <div class="small text-muted mt-3">
                Período considerado: {{ $inicioTurno->format('d/m/Y H:i') }} até {{ $fimTurno->format('d/m/Y H:i') }}.
            </div>
        </div>
    </div>

    <div class="report-wrap">
        <div class="shift-report" id="shiftReport">
            <div class="report-topline">
                <div class="report-greeting">{{ $mensagem }}</div>
                <div class="report-period">{{ $inicioTurno->format('H:i') }} - {{ $fimTurno->format('H:i') }}</div>
            </div>

            <div class="report-head">
                <div>
                    <div class="report-eyebrow">Report de produtividade</div>
                    <div class="report-title">{{ mb_strtoupper($turnoAtual['label']) }}</div>
                </div>
                <div class="report-date">{{ \Carbon\Carbon::parse($dataSelecionada)->format('d/m/Y') }}</div>
            </div>

            <div class="status-summary">
                <div class="status-card">
                    <span>A separar</span>
                    <strong>{{ number_format($resumoStatus['a_separar'], 0, ',', '.') }}</strong>
                    <small>Backlog: {{ number_format($resumoStatus['backlog_a_separar'], 0, ',', '.') }}</small>
                </div>
                <div class="status-card">
                    <span>Separando</span>
                    <strong>{{ number_format($resumoStatus['separando'], 0, ',', '.') }}</strong>
                    <small>Backlog: {{ number_format($resumoStatus['backlog_separando'], 0, ',', '.') }}</small>
                </div>
                <div class="status-card">
                    <span>Separado</span>
                    <strong>{{ number_format($resumoStatus['separado'], 0, ',', '.') }}</strong>
                    <small>Backlog finalizado: {{ number_format($resumoStatus['backlog_separado'], 0, ',', '.') }}</small>
                </div>
            </div>

            <div class="demand-shift-summary">
                <div class="demand-shift-card demand-programmed">
                    <div class="demand-title">Programadas</div>
                    <div class="demand-grid">
                        <div><span>Recebidas</span><strong>{{ number_format($visaoTurnoDemanda['programadas']['recebidas'] ?? 0, 0, ',', '.') }}</strong></div>
                        <div><span>Executadas</span><strong>{{ number_format($visaoTurnoDemanda['programadas']['executadas'] ?? 0, 0, ',', '.') }}</strong></div>
                        <div><span>Pendentes</span><strong>{{ number_format($visaoTurnoDemanda['programadas']['pendentes'] ?? 0, 0, ',', '.') }}</strong></div>
                    </div>
                </div>
                <div class="demand-shift-card demand-opportunity">
                    <div class="demand-title">Oportunidades</div>
                    <div class="demand-grid">
                        <div><span>Recebidas</span><strong>{{ number_format($visaoTurnoDemanda['oportunidades']['recebidas'] ?? 0, 0, ',', '.') }}</strong></div>
                        <div><span>Executadas</span><strong>{{ number_format($visaoTurnoDemanda['oportunidades']['executadas'] ?? 0, 0, ',', '.') }}</strong></div>
                        <div><span>Pendentes</span><strong>{{ number_format($visaoTurnoDemanda['oportunidades']['pendentes'] ?? 0, 0, ',', '.') }}</strong></div>
                    </div>
                </div>
                <div class="demand-shift-card demand-handoff">
                    <div class="demand-title">Passagem de Turno</div>
                    <div class="handoff-value">{{ number_format($visaoTurnoDemanda['passagem_turno'] ?? 0, 0, ',', '.') }}</div>
                    <div class="handoff-label">pendências repassadas ao próximo turno</div>
                </div>
            </div>

            <div class="capacity-shift-summary">
                <div>
                    <span>Consumo carteira</span>
                    <strong>{{ number_format($capacidadeOperacional['capacidade']['carteira_consumida_dt'] ?? 0, 0, ',', '.') }}</strong>
                </div>
                <div>
                    <span>Consumo paralelo</span>
                    <strong>{{ number_format($capacidadeOperacional['capacidade']['paralela_consumida_dt'] ?? 0, 0, ',', '.') }}</strong>
                </div>
                <div>
                    <span>Restante estimado</span>
                    <strong>{{ number_format($capacidadeOperacional['capacidade']['restante_dt'] ?? 0, 0, ',', '.') }}</strong>
                </div>
                <div>
                    <span>Risco</span>
                    <strong>{{ $capacidadeOperacional['risco']['label'] ?? '-' }}</strong>
                </div>
            </div>

            <table class="report-table">
                <thead>
                    <tr>
                        <th>Separador</th>
                        <th>Caixas</th>
                        <th>SKU</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($separadores as $linha)
                        <tr>
                            <td>{{ $linha['separador'] }}</td>
                            <td>{{ number_format($linha['pecas'], 0, ',', '.') }}</td>
                            <td>{{ number_format($linha['skus'], 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="empty-row">SEM DADOS FINALIZADOS</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <td>TOTAL</td>
                        <td>{{ number_format($totais['pecas'], 0, ',', '.') }}</td>
                        <td>{{ number_format($totais['skus'], 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>

            <div class="report-kpi">
                <div class="kpi-card">
                    <div class="kpi-label">BOX</div>
                    <div class="kpi-value">{{ number_format($totais['box'], 0, ',', '.') }}</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-label">DT</div>
                    <div class="kpi-value">{{ number_format($totais['dt'], 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .icon-wrapper {
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #111827 0%, #374151 100%);
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(17, 24, 39, 0.22);
    }

    .icon-wrapper i { color: #fff !important; }

    .report-wrap {
        display: flex;
        justify-content: center;
        padding: 18px 0 40px;
    }

    .shift-report {
        width: min(620px, 100%);
        background: #f8fafc;
        color: #111827;
        border: 1px solid #111827;
        border-radius: 6px;
        overflow: hidden;
        font-family: Arial, Helvetica, sans-serif;
        box-shadow: 0 16px 34px rgba(15, 23, 42, 0.18);
    }

    .report-topline {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        background: #ffffff;
        border-bottom: 1px solid #d1d5db;
        padding: 10px 14px;
    }

    .report-greeting {
        color: #111827;
        font-size: 18px;
        font-weight: 800;
        font-style: italic;
    }

    .report-period {
        color: #475569;
        font-size: 13px;
        font-weight: 800;
        white-space: nowrap;
    }

    .report-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        background: #0f172a;
        color: #fff;
        padding: 16px 18px;
    }

    .report-eyebrow {
        color: #cbd5e1;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .report-title {
        color: #f43f5e;
        font-size: 27px;
        line-height: 1.1;
        font-weight: 900;
        margin-top: 4px;
    }

    .report-date {
        color: #fff;
        text-align: right;
        font-size: 28px;
        line-height: 1;
        font-weight: 900;
        white-space: nowrap;
    }

    .status-summary {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1px;
        background: #cbd5e1;
        border-bottom: 1px solid #cbd5e1;
    }

    .status-card {
        background: #f8fafc;
        padding: 10px 12px;
        text-align: center;
    }

    .status-card span {
        display: block;
        color: #475569;
        font-size: 13px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.02em;
    }

    .status-card strong {
        display: block;
        color: #111827;
        font-size: 26px;
        line-height: 1;
        margin-top: 4px;
        font-weight: 900;
    }

    .status-card small {
        display: block;
        color: #64748b;
        font-size: 11px;
        line-height: 1;
        margin-top: 6px;
        font-weight: 800;
    }

    .demand-shift-summary {
        display: grid;
        grid-template-columns: 1fr 1fr .82fr;
        gap: 1px;
        background: #cbd5e1;
        border-bottom: 1px solid #cbd5e1;
    }

    .demand-shift-card {
        background: #f8fafc;
        padding: 10px 12px;
    }

    .demand-title {
        color: #111827;
        font-size: 13px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        margin-bottom: 8px;
    }

    .demand-programmed .demand-title {
        color: #1d4ed8;
    }

    .demand-opportunity .demand-title {
        color: #6d28d9;
    }

    .demand-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 8px;
    }

    .demand-grid span,
    .handoff-label {
        display: block;
        color: #64748b;
        font-size: 10px;
        font-weight: 900;
        line-height: 1.1;
        text-transform: uppercase;
    }

    .demand-grid strong,
    .handoff-value {
        display: block;
        color: #111827;
        font-size: 23px;
        line-height: 1;
        margin-top: 4px;
        font-weight: 900;
    }

    .demand-handoff {
        background: #111827;
        color: #fff;
    }

    .demand-handoff .demand-title,
    .demand-handoff .handoff-value {
        color: #fff;
    }

    .demand-handoff .handoff-label {
        color: #cbd5e1;
        margin-top: 6px;
    }

    .capacity-shift-summary {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1px;
        background: #cbd5e1;
        border-bottom: 1px solid #cbd5e1;
    }

    .capacity-shift-summary > div {
        background: #ffffff;
        padding: 10px 12px;
        text-align: center;
    }

    .capacity-shift-summary span {
        display: block;
        color: #64748b;
        font-size: 10px;
        font-weight: 900;
        line-height: 1.1;
        text-transform: uppercase;
    }

    .capacity-shift-summary strong {
        display: block;
        color: #111827;
        font-size: 18px;
        line-height: 1.05;
        margin-top: 5px;
        font-weight: 900;
        overflow-wrap: anywhere;
    }

    .report-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
        font-size: 19px;
    }

    .report-table th {
        background: #111827;
        color: #f43f5e;
        text-align: center;
        padding: 9px 10px;
        border: 1px solid #1f2937;
        font-size: 20px;
        font-weight: 900;
    }

    .report-table th:first-child,
    .report-table td:first-child {
        width: 44%;
    }

    .report-table th:nth-child(2),
    .report-table td:nth-child(2) {
        width: 24%;
    }

    .report-table th:nth-child(3),
    .report-table td:nth-child(3) {
        width: 32%;
    }

    .report-table td {
        background: #f8fafc;
        border: 1px solid #cbd5e1;
        text-align: center;
        padding: 9px 10px;
        line-height: 1.1;
        font-weight: 800;
    }

    .report-table tbody tr:nth-child(even) td {
        background: #eef2f7;
    }

    .report-table tfoot td {
        background: #0f172a;
        color: #f43f5e;
        font-size: 29px;
        padding: 10px;
        font-weight: 900;
    }

    .empty-row {
        height: 78px;
        color: #64748b;
        font-size: 18px;
        font-style: italic;
    }

    .report-kpi {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
        padding: 16px;
        background: #e5e7eb;
    }

    .kpi-card {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        text-align: center;
        overflow: hidden;
    }

    .kpi-label {
        background: #f1f5f9;
        color: #475569;
        border-bottom: 1px solid #cbd5e1;
        padding: 7px 8px;
        line-height: 1;
        font-size: 16px;
        font-weight: 900;
    }

    .kpi-value {
        color: #f43f5e;
        font-size: 34px;
        font-weight: 900;
        padding: 8px;
        line-height: 1.15;
    }

    @media print {
        body {
            background: #fff !important;
        }

        .no-print,
        .navbar-custom,
        .leftside-menu,
        footer,
        .breadcrumb,
        .button-menu-mobile,
        .end-bar {
            display: none !important;
        }

        .content-page,
        .content,
        .container-fluid,
        .report-page {
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
        }

        .report-wrap {
            padding: 0 !important;
            justify-content: flex-start;
        }

        .shift-report {
            width: 620px;
            box-shadow: none;
            border-radius: 0;
        }
    }
</style>
@endsection
