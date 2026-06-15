@extends('layouts.app')

@section('title', 'Consulta de Timelines')

@section('content')
    @php
        if (! function_exists('dtTimeValida')) {
            function dtTimeValida($data) {
                return $data && \Carbon\Carbon::parse($data)->gte(\App\Models\Demanda::DATA_OPERACIONAL_MINIMA);
            }
        }

        if (! function_exists('statusTimelineDt')) {
            function statusTimelineDt($dt) {
                if (dtTimeValida($dt->saida_veiculo_em)) return ['label' => 'Com saída', 'class' => 'ok'];
                if (dtTimeValida($dt->carregamento_finalizado_em)) return ['label' => 'Carregada', 'class' => 'loaded'];
                if (dtTimeValida($dt->carregamento_iniciado_em)) return ['label' => 'Carregando', 'class' => 'active'];
                if (dtTimeValida($dt->conferencia_finalizada_em)) return ['label' => 'Aguard. carga', 'class' => 'active'];
                if (dtTimeValida($dt->conferencia_iniciada_em)) return ['label' => 'Conferindo', 'class' => 'active'];
                if (dtTimeValida($dt->separacao_finalizada_em)) return ['label' => 'Separada', 'class' => 'active'];
                if (dtTimeValida($dt->separacao_iniciada_em)) return ['label' => 'Separando', 'class' => 'active'];
                return ['label' => 'A separar', 'class' => 'pending'];
            }
        }
    @endphp

    <style>
        .timeline-list-page { color: #f8fafc; }
        .timeline-list-panel {
            background: rgba(12, 16, 24, .94);
            border: 1px solid rgba(255, 255, 255, .10);
            border-radius: 8px;
            box-shadow: 0 18px 45px rgba(0, 0, 0, .24);
        }
        .timeline-list-muted { color: #a8b3c7; }
        .timeline-list-page .form-control,
        .timeline-list-page .form-select {
            background: rgba(255, 255, 255, .06);
            border-color: rgba(255, 255, 255, .16);
            color: #fff;
        }
        .timeline-list-page .form-select option { background: #111827; color: #f8fafc; }
        .timeline-kpi {
            min-height: 82px;
            padding: 14px;
            border: 1px solid rgba(148, 163, 184, .18);
            border-radius: 8px;
            background: rgba(15, 23, 42, .72);
        }
        .timeline-kpi.ok { border-color: rgba(34, 197, 94, .28); }
        .timeline-kpi.loaded { border-color: rgba(14, 165, 233, .32); }
        .timeline-kpi-label {
            color: #a8b3c7;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
        }
        .timeline-kpi-value {
            margin-top: 6px;
            color: #fff;
            font-size: 28px;
            font-weight: 900;
            line-height: 1;
        }
        .timeline-table { color: #f8fafc; }
        .timeline-table th {
            color: #cbd5e1;
            font-size: 12px;
            text-transform: uppercase;
            border-color: rgba(255, 255, 255, .10);
            white-space: nowrap;
        }
        .timeline-table td {
            border-color: rgba(255, 255, 255, .08);
            vertical-align: middle;
        }
        .timeline-dt { font-size: 19px; font-weight: 900; color: #fff; }
        .timeline-pill {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 5px 9px;
            font-size: 11px;
            font-weight: 900;
            text-transform: uppercase;
            background: #475569;
            color: #dbeafe;
        }
        .timeline-pill.ok { background: #1f9d4c; color: #fff; }
        .timeline-pill.loaded { background: #0369a1; color: #fff; }
        .timeline-pill.active { background: #2563eb; color: #fff; }
        .timeline-pill.pending { background: #64748b; color: #fff; }
        .icon-wrapper {
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0ea5e9 0%, #2563eb 100%);
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(14, 165, 233, .26);
        }
        .icon-wrapper i { color: #fff !important; }
    </style>

    <div class="timeline-list-page container-fluid px-4 py-3">
        @include('partials.breadcrumb-auto')

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div class="d-flex align-items-center">
                <div class="icon-wrapper me-3">
                    <i class="mdi mdi-timeline-clock-outline display-6"></i>
                </div>
                <div>
                    <h3 class="mb-1 fw-bold text-dark">Timeline das DTs</h3>
                    <p class="text-muted mb-0 small">Consulta futura de DTs em andamento, carregadas e com saída de veículo.</p>
                </div>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('expedicao.previsibilidade.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="mdi mdi-monitor-dashboard me-1"></i> Painel
                </a>
                <a href="{{ route('expedicao.saida-veiculos.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="mdi mdi-exit-run me-1"></i> Saída de veículos
                </a>
            </div>
        </div>

        <div class="timeline-list-panel p-3 mb-3">
            <div class="row g-3 align-items-end">
                <div class="col-xl-7">
                    <form method="GET" class="row g-2 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label timeline-list-muted">Buscar DTs, destino ou cliente</label>
                            <input type="text" name="busca" class="form-control" value="{{ $busca }}" placeholder="Ex.: 251311087, 251311088">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label timeline-list-muted">Status</label>
                            <select name="status" class="form-select">
                                <option value="TODAS" @selected($status === 'TODAS')>Todas</option>
                                <option value="EM_ANDAMENTO" @selected($status === 'EM_ANDAMENTO')>Em andamento</option>
                                <option value="CARREGADAS" @selected($status === 'CARREGADAS')>Carregadas</option>
                                <option value="COM_SAIDA" @selected($status === 'COM_SAIDA')>Com saída</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label timeline-list-muted">Demanda</label>
                            <select name="tipo_demanda" class="form-select">
                                <option value="TODAS" @selected($tipoDemanda === 'TODAS')>Todas</option>
                                <option value="PROGRAMADA" @selected($tipoDemanda === 'PROGRAMADA')>Programada</option>
                                <option value="OPORTUNIDADE" @selected($tipoDemanda === 'OPORTUNIDADE')>Oportunidade</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-fill">
                                <i class="mdi mdi-filter-outline"></i>
                            </button>
                            <a href="{{ route('expedicao.timeline-dts.index') }}" class="btn btn-outline-light">
                                <i class="mdi mdi-close"></i>
                            </a>
                        </div>
                    </form>
                </div>

                <div class="col-xl-5">
                    <div class="row g-2">
                        <div class="col-3"><div class="timeline-kpi"><div class="timeline-kpi-label">Total</div><div class="timeline-kpi-value">{{ $resumo['total'] ?? 0 }}</div></div></div>
                        <div class="col-3"><div class="timeline-kpi"><div class="timeline-kpi-label">Em andamento</div><div class="timeline-kpi-value">{{ $resumo['em_andamento'] ?? 0 }}</div></div></div>
                        <div class="col-3"><div class="timeline-kpi loaded"><div class="timeline-kpi-label">Carregadas</div><div class="timeline-kpi-value">{{ $resumo['carregadas'] ?? 0 }}</div></div></div>
                        <div class="col-3"><div class="timeline-kpi ok"><div class="timeline-kpi-label">Com saída</div><div class="timeline-kpi-value">{{ $resumo['com_saida'] ?? 0 }}</div></div></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="timeline-list-panel p-2">
            <div class="table-responsive">
                <table class="table timeline-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>DT</th>
                            <th>Destino / Cliente</th>
                            <th>Status</th>
                            <th>Separação</th>
                            <th>Carregamento</th>
                            <th>Saída</th>
                            <th>Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($dts as $dt)
                            @php
                                $statusLinha = statusTimelineDt($dt);
                                $tipo = $dt->tipo_demanda ?: 'OPORTUNIDADE';
                            @endphp
                            <tr>
                                <td>
                                    <div class="timeline-dt">{{ $dt->dt_sap ?: $dt->fo }}</div>
                                    <div class="timeline-list-muted small">FO {{ $dt->fo }}</div>
                                    <span class="timeline-pill mt-1">{{ ucfirst(strtolower($tipo)) }}</span>
                                </td>
                                <td>
                                    <div class="fw-bold text-white">{{ $dt->cidade_destino ? $dt->cidade_destino.'/'.$dt->uf_destino : '-' }}</div>
                                    <div class="timeline-list-muted small">{{ $dt->cliente ?? '-' }}</div>
                                </td>
                                <td><span class="timeline-pill {{ $statusLinha['class'] }}">{{ $statusLinha['label'] }}</span></td>
                                <td>
                                    <div class="text-white fw-bold">{{ dtTimeValida($dt->separacao_finalizada_em) ? \Carbon\Carbon::parse($dt->separacao_finalizada_em)->format('d/m/Y H:i') : '-' }}</div>
                                    <div class="timeline-list-muted small">fim da separação</div>
                                </td>
                                <td>
                                    <div class="text-white fw-bold">{{ dtTimeValida($dt->carregamento_finalizado_em) ? \Carbon\Carbon::parse($dt->carregamento_finalizado_em)->format('d/m/Y H:i') : '-' }}</div>
                                    <div class="timeline-list-muted small">fim do carregamento</div>
                                </td>
                                <td>
                                    <div class="text-white fw-bold">{{ dtTimeValida($dt->saida_veiculo_em) ? \Carbon\Carbon::parse($dt->saida_veiculo_em)->format('d/m/Y H:i') : '-' }}</div>
                                    <div class="timeline-list-muted small">saída do veículo</div>
                                </td>
                                <td>
                                    <a href="{{ route('expedicao.timeline-dts.show', $dt->fo) }}" class="btn btn-outline-light btn-sm">
                                        <i class="mdi mdi-timeline-clock-outline me-1"></i> Ver timeline
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center timeline-list-muted py-4">
                                    Nenhuma DT encontrada para os filtros selecionados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-3">
                {{ $dts->links() }}
            </div>
        </div>
    </div>
@endsection
