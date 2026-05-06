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
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-sm btn-primary w-100">
                        <i class="mdi mdi-refresh me-1"></i> Gerar
                    </button>
                </div>
                <div class="col-md-2 d-flex align-items-end">
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
            <div class="report-greeting">{{ $mensagem }}</div>

            <div class="report-title">{{ mb_strtoupper($turnoAtual['label']) }}</div>
            <div class="report-date">{{ \Carbon\Carbon::parse($dataSelecionada)->format('d/m/Y') }}</div>

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
                <div class="kpi-label">BOX</div>
                <div class="kpi-value">{{ number_format($totais['box'], 0, ',', '.') }}</div>
                <div class="kpi-label">DT</div>
                <div class="kpi-value">{{ number_format($totais['dt'], 0, ',', '.') }}</div>
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
        width: min(560px, 100%);
        background: #d8d8d8;
        color: #050505;
        border: 1px solid #222;
        font-family: Arial, Helvetica, sans-serif;
        font-weight: 800;
        font-style: italic;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.16);
    }

    .report-greeting {
        background: #f3f3f3;
        color: #111;
        font-size: 18px;
        padding: 5px 10px;
        font-weight: 700;
        font-style: italic;
    }

    .report-title {
        background: #050505;
        color: #ff1010;
        text-align: center;
        font-size: 24px;
        line-height: 1.35;
        padding: 5px 8px;
    }

    .report-date {
        background: #a7aaa5;
        color: #111;
        text-align: center;
        font-size: 30px;
        line-height: 1.2;
        padding: 6px 8px;
    }

    .report-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
        font-size: 21px;
    }

    .report-table th {
        background: #050505;
        color: #ff1010;
        text-align: center;
        padding: 5px 8px;
        border: 1px solid #202020;
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
        background: #c8c8c8;
        border: 1px solid #505050;
        text-align: center;
        padding: 3px 8px;
        line-height: 1.08;
    }

    .report-table tfoot td {
        background: #050505;
        color: #ff1010;
        font-size: 30px;
        padding: 7px 8px;
    }

    .empty-row {
        height: 72px;
        color: #555;
        font-size: 18px;
    }

    .report-kpi {
        margin: 44px 2px 0;
        text-align: center;
        font-size: 20px;
    }

    .kpi-label {
        background: #b9bdc5;
        color: #2a2a2a;
        border-top: 1px solid #4b5563;
        border-bottom: 1px solid #4b5563;
        padding: 3px 8px;
        line-height: 1.1;
    }

    .kpi-value {
        background: #050505;
        color: #ff1010;
        font-size: 31px;
        padding: 3px 8px;
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
            width: 560px;
            box-shadow: none;
        }
    }
</style>
@endsection
