@extends('layouts.app')

@section('title', 'Apontar Expedição')

@section('content')
    <style>
        .exp-ops-page {
            color: #f8fafc;
        }

        .exp-ops-filter,
        .exp-ops-table-wrap {
            background: rgba(12, 16, 24, .94);
            border: 1px solid rgba(255, 255, 255, .10);
            border-radius: 8px;
            box-shadow: 0 18px 45px rgba(0, 0, 0, .24);
        }

        .exp-ops-muted {
            color: #a8b3c7;
        }

        .exp-ops-page .form-control,
        .exp-ops-page .form-select {
            background: rgba(255, 255, 255, .06);
            border-color: rgba(255, 255, 255, .16);
            color: #fff;
        }

        .exp-ops-page .form-control:focus,
        .exp-ops-page .form-select:focus {
            background: rgba(255, 255, 255, .08);
            border-color: #667eea;
            color: #fff;
            box-shadow: 0 0 0 .2rem rgba(102, 126, 234, .18);
        }

        .exp-ops-page .form-select option {
            background: #111827;
            color: #f8fafc;
        }

        .exp-ops-queue-summary {
            display: grid;
            grid-template-columns: repeat(6, minmax(96px, 1fr));
            gap: 10px;
        }

        .exp-ops-queue-card {
            min-height: 74px;
            padding: 12px;
            border: 1px solid rgba(148, 163, 184, .18);
            border-radius: 8px;
            background: rgba(15, 23, 42, .72);
        }

        .exp-ops-queue-card.done {
            border-color: rgba(34, 197, 94, .28);
            background: linear-gradient(135deg, rgba(21, 128, 61, .18), rgba(15, 23, 42, .72));
        }

        .exp-ops-queue-card.active {
            border-color: rgba(59, 130, 246, .34);
            background: linear-gradient(135deg, rgba(37, 99, 235, .18), rgba(15, 23, 42, .72));
        }

        .exp-ops-queue-card.waiting {
            border-color: rgba(245, 158, 11, .30);
            background: linear-gradient(135deg, rgba(146, 64, 14, .16), rgba(15, 23, 42, .72));
        }

        .exp-ops-queue-label {
            color: #a8b3c7;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .04em;
            line-height: 1.2;
            text-transform: uppercase;
        }

        .exp-ops-queue-value {
            margin-top: 6px;
            color: #fff;
            font-size: 23px;
            font-weight: 900;
            line-height: 1;
        }

        .exp-ops-queue-note {
            margin-top: 5px;
            color: #8fb5d7;
            font-size: 11px;
        }

        .exp-ops-page input[type="datetime-local"]::-webkit-calendar-picker-indicator {
            filter: invert(1);
        }

        .exp-ops-table {
            color: #f8fafc;
        }

        .exp-ops-table > :not(caption) > * > * {
            background: transparent;
        }

        .exp-ops-table th {
            color: #cbd5e1;
            font-size: 12px;
            text-transform: uppercase;
            border-color: rgba(255, 255, 255, .10);
            white-space: nowrap;
        }

        .exp-ops-table td {
            border-color: rgba(255, 255, 255, .08);
            vertical-align: middle;
        }

        .exp-ops-dt {
            font-size: 18px;
            font-weight: 800;
            color: #fff;
        }

        .exp-ops-pill {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 5px 9px;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            background: #7d8793;
            color: #fff;
        }

        .exp-ops-pill.ok {
            background: #1f9d4c;
        }

        .exp-ops-pill.working {
            background: #2563eb;
            color: #eff6ff;
            box-shadow: 0 0 16px rgba(37, 99, 235, .22);
        }

        .exp-ops-pill.warn {
            background: #d97706;
        }

        .exp-ops-pill.programmed {
            background: #475569;
            color: #dbeafe;
        }

        .exp-ops-pill.opportunity {
            background: #6d28d9;
            color: #f5f3ff;
        }

        .exp-ops-actions {
            min-width: 340px;
        }

        .exp-ops-action-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin-bottom: 8px;
        }

        .exp-ops-action-row .btn {
            white-space: nowrap;
            font-weight: 800;
        }

        .exp-ops-action-row .btn:disabled {
            border-color: rgba(148, 163, 184, .24);
            background: rgba(148, 163, 184, .08);
            color: #94a3b8;
            opacity: 1;
        }

        .exp-ops-edit-toggle {
            font-weight: 800;
        }

        .exp-ops-edit-panel {
            border: 1px solid rgba(245, 158, 11, .22);
            border-radius: 8px;
            background: rgba(245, 158, 11, .06);
            padding: 10px;
        }

        .exp-ops-shortcuts {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .exp-ops-shortcuts .btn {
            font-weight: 800;
        }

        .icon-wrapper {
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(102, 126, 234, .3);
        }

        .icon-wrapper i {
            color: #fff !important;
        }

        [data-theme="light"] .exp-ops-page {
            color: #172033;
        }

        [data-theme="light"] .exp-ops-page .text-dark,
        [data-theme="light"] .exp-ops-page .text-white {
            color: #172033 !important;
        }

        [data-theme="light"] .exp-ops-page .text-muted,
        [data-theme="light"] .exp-ops-muted {
            color: #5f6f86 !important;
        }

        [data-theme="light"] .exp-ops-filter,
        [data-theme="light"] .exp-ops-table-wrap {
            background: #ffffff;
            border-color: rgba(15, 23, 42, .10);
            box-shadow: 0 18px 42px rgba(15, 23, 42, .10);
        }

        [data-theme="light"] .exp-ops-filter {
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        }

        [data-theme="light"] .exp-ops-page .form-control,
        [data-theme="light"] .exp-ops-page .form-select {
            background: #f8fafc;
            border-color: #cbd5e1;
            color: #0f172a;
        }

        [data-theme="light"] .exp-ops-page .form-control::placeholder {
            color: #64748b;
        }

        [data-theme="light"] .exp-ops-page .form-control:focus,
        [data-theme="light"] .exp-ops-page .form-select:focus {
            background: #ffffff;
            border-color: #2563eb;
            color: #0f172a;
            box-shadow: 0 0 0 .2rem rgba(37, 99, 235, .14);
        }

        [data-theme="light"] .exp-ops-page .form-select option {
            background: #ffffff;
            color: #0f172a;
        }

        [data-theme="light"] .exp-ops-page input[type="datetime-local"]::-webkit-calendar-picker-indicator {
            filter: none;
        }

        [data-theme="light"] .exp-ops-queue-card {
            background: #f8fafc;
            border-color: rgba(59, 130, 246, .18);
        }

        [data-theme="light"] .exp-ops-queue-card.done {
            background: linear-gradient(135deg, rgba(22, 163, 74, .12), #ffffff);
            border-color: rgba(22, 163, 74, .34);
        }

        [data-theme="light"] .exp-ops-queue-card.active {
            background: linear-gradient(135deg, rgba(37, 99, 235, .12), #ffffff);
            border-color: rgba(37, 99, 235, .34);
        }

        [data-theme="light"] .exp-ops-queue-card.waiting {
            background: linear-gradient(135deg, rgba(217, 119, 6, .12), #ffffff);
            border-color: rgba(217, 119, 6, .32);
        }

        [data-theme="light"] .exp-ops-queue-label {
            color: #334155;
        }

        [data-theme="light"] .exp-ops-queue-value {
            color: #0f172a;
        }

        [data-theme="light"] .exp-ops-queue-note {
            color: #2563eb;
        }

        [data-theme="light"] .exp-ops-table {
            color: #172033;
        }

        [data-theme="light"] .exp-ops-table thead th {
            background: #e8eef7;
            border-color: #d6deea;
            color: #0f172a;
        }

        [data-theme="light"] .exp-ops-table td {
            background: #ffffff;
            border-color: #e2e8f0;
            color: #172033;
        }

        [data-theme="light"] .exp-ops-table tbody tr:hover td {
            background: #f8fafc;
        }

        [data-theme="light"] .exp-ops-dt {
            color: #0f172a;
        }

        [data-theme="light"] .exp-ops-action-row .btn:disabled {
            background: #f1f5f9;
            border-color: #d6deea;
            color: #8a97aa;
        }

        [data-theme="light"] .exp-ops-page .btn-outline-light {
            border-color: #cbd5e1;
            color: #334155;
            background: #ffffff;
        }

        [data-theme="light"] .exp-ops-page .btn-outline-light:hover,
        [data-theme="light"] .exp-ops-page .btn-outline-light:focus {
            border-color: #2563eb;
            color: #1d4ed8;
            background: #eff6ff;
        }

        [data-theme="light"] .exp-ops-edit-panel {
            background: #fffbeb;
            border-color: rgba(217, 119, 6, .28);
        }

        @media (max-width: 992px) {
            .exp-ops-queue-summary {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 520px) {
            .exp-ops-queue-summary {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="exp-ops-page container-fluid px-4 py-3">
        @include('partials.breadcrumb-auto')

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div class="d-flex align-items-center">
                <div class="icon-wrapper me-3">
                    <i class="mdi mdi-truck-fast-outline display-6"></i>
                </div>
                <div>
                    <h3 class="mb-1 fw-bold text-dark">Apontar Expedição</h3>
                    <p class="text-muted mb-0 small">Lançamento operacional dos tempos reais da expedição</p>
                </div>
            </div>

            <div class="exp-ops-shortcuts">
                <a href="{{ route('expedicao.apontamentos-operacionais.index') }}" class="btn btn-primary btn-sm">
                    <i class="mdi mdi-timer-edit-outline me-1"></i> Apontar Expedição
                </a>

                <a href="{{ route('expedicao.previsibilidade.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="mdi mdi-monitor-dashboard me-1"></i> Painel
                </a>

                <a href="{{ route('expedicao.saida-veiculos.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="mdi mdi-exit-run me-1"></i> Saída Veículo
                </a>

                <a href="{{ route('expedicao.timeline-dts.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="mdi mdi-timeline-clock-outline me-1"></i> Timeline DTs
                </a>

                <a href="{{ route('expedicao.importacao-programacao.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="mdi mdi-file-upload-outline me-1"></i> Importar PROG
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (($errors ?? null) && $errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ $errors->first() }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="exp-ops-filter p-3 mb-3">
            <div class="row g-3 align-items-end">
                <div class="col-xl-6">
                    <form method="GET" class="row g-2 align-items-end">
                        <div class="col-md-5">
                            <label for="busca" class="form-label exp-ops-muted">Buscar DT, destino ou cliente</label>
                            <input type="text" name="busca" id="busca" class="form-control" value="{{ $busca }}" placeholder="Ex.: 251311087">
                        </div>
                        <div class="col-md-3">
                            <label for="status" class="form-label exp-ops-muted">Status</label>
                            <select name="status" id="status" class="form-select">
                                <option value="todos" @selected($status === 'todos')>Aguardando expedição</option>
                                <option value="conferencia_pendente" @selected($status === 'conferencia_pendente')>Conferência pendente</option>
                                <option value="carregamento_pendente" @selected($status === 'carregamento_pendente')>Carregamento pendente</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="tipo_demanda" class="form-label exp-ops-muted">Demanda</label>
                            <select name="tipo_demanda" id="tipo_demanda" class="form-select">
                                <option value="TODAS" @selected(($tipoDemanda ?? 'TODAS') === 'TODAS')>Todas</option>
                                <option value="PROGRAMADA" @selected(($tipoDemanda ?? 'TODAS') === 'PROGRAMADA')>Programada</option>
                                <option value="OPORTUNIDADE" @selected(($tipoDemanda ?? 'TODAS') === 'OPORTUNIDADE')>Oportunidade</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-fill">
                                <i class="mdi mdi-filter-outline me-1"></i> Filtrar
                            </button>
                            <a href="{{ route('expedicao.apontamentos-operacionais.index') }}" class="btn btn-outline-light">
                                <i class="mdi mdi-close"></i>
                            </a>
                        </div>
                    </form>
                </div>

                <div class="col-xl-6">
                    <div class="exp-ops-queue-summary">
                        <div class="exp-ops-queue-card">
                            <div class="exp-ops-queue-label">Na fila</div>
                            <div class="exp-ops-queue-value">{{ $resumoFila['em_fila'] ?? 0 }}</div>
                            <div class="exp-ops-queue-note">Separadas</div>
                        </div>
                        <div class="exp-ops-queue-card waiting">
                            <div class="exp-ops-queue-label">Aguard. conf.</div>
                            <div class="exp-ops-queue-value">{{ $resumoFila['aguardando_conferencia'] ?? 0 }}</div>
                            <div class="exp-ops-queue-note">A apontar</div>
                        </div>
                        <div class="exp-ops-queue-card active">
                            <div class="exp-ops-queue-label">Conferindo</div>
                            <div class="exp-ops-queue-value">{{ $resumoFila['conferindo'] ?? 0 }}</div>
                            <div class="exp-ops-queue-note">Em andamento</div>
                        </div>
                        <div class="exp-ops-queue-card waiting">
                            <div class="exp-ops-queue-label">Aguard. carga</div>
                            <div class="exp-ops-queue-value">{{ $resumoFila['aguardando_carregamento'] ?? 0 }}</div>
                            <div class="exp-ops-queue-note">Conf. ok</div>
                        </div>
                        <div class="exp-ops-queue-card active">
                            <div class="exp-ops-queue-label">Carregando</div>
                            <div class="exp-ops-queue-value">{{ $resumoFila['carregando'] ?? 0 }}</div>
                            <div class="exp-ops-queue-note">Em andamento</div>
                        </div>
                        <div class="exp-ops-queue-card done">
                            <div class="exp-ops-queue-label">Finalizadas</div>
                            <div class="exp-ops-queue-value">{{ $resumoFila['finalizadas'] ?? 0 }}</div>
                            <div class="exp-ops-queue-note">Fora da fila</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="exp-ops-table-wrap p-2">
            <div class="table-responsive">
                <table class="table exp-ops-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>DT</th>
                            <th>Destino</th>
                            <th>Agenda</th>
                            <th>Conferência</th>
                            <th>Carregamento</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($programacoes as $programacao)
                            @php
                                $demanda = $programacao->demanda;
                                $conferenciaOk = $demanda?->conferencia_finalizada_em;
                                $carregamentoOk = $demanda?->carregamento_finalizado_em;
                            @endphp
                            <tr>
                                <td>
                                    <div class="exp-ops-dt">{{ $programacao->fo }}</div>
                                    <div class="exp-ops-muted small">{{ $programacao->tipo_carga ?? '-' }}</div>
                                    <span class="exp-ops-pill {{ $programacao->tipo_demanda === 'OPORTUNIDADE' ? 'opportunity' : 'programmed' }} mt-1">
                                        {{ $programacao->tipo_demanda_label }}
                                    </span>
                                    @if (! $demanda)
                                        <span class="exp-ops-pill warn mt-1">Sem explosão</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-bold text-white">{{ $programacao->cidade_destino }}/{{ $programacao->uf_destino }}</div>
                                    <div class="exp-ops-muted small">{{ $programacao->cliente ?? '-' }}</div>
                                </td>
                                <td>
                                    <div class="text-white fw-bold">{{ optional($programacao->agenda_entrega_em)->format('d/m H:i') ?? '-' }}</div>
                                    <div class="exp-ops-muted small">Programado</div>
                                </td>
                                <td class="exp-ops-actions">
                                    @include('expedicao.apontamentos-operacionais.partials.etapa', [
                                        'programacao' => $programacao,
                                        'demanda' => $demanda,
                                        'etapa' => 'conferencia',
                                        'label' => 'Conferência',
                                        'inicio' => $demanda?->conferencia_iniciada_em,
                                        'fim' => $demanda?->conferencia_finalizada_em,
                                        'finalizado' => $conferenciaOk,
                                    ])
                                </td>
                                <td class="exp-ops-actions">
                                    @include('expedicao.apontamentos-operacionais.partials.etapa', [
                                        'programacao' => $programacao,
                                        'demanda' => $demanda,
                                        'etapa' => 'carregamento',
                                        'label' => 'Carregamento',
                                        'inicio' => $demanda?->carregamento_iniciado_em,
                                        'fim' => $demanda?->carregamento_finalizado_em,
                                        'finalizado' => $carregamentoOk,
                                    ])
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center exp-ops-muted py-4">
                                    Nenhuma DT separada aguardando expedição.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-3">
                {{ $programacoes->links() }}
            </div>
        </div>
    </div>
@endsection
