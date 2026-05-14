@extends('layouts.app')

@section('title', 'Apontar Expedição')

@section('content')
    <style>
        .exp-ops-page {
            color: #f8fafc;
        }

        .exp-ops-header,
        .exp-ops-filter,
        .exp-ops-table-wrap {
            background: rgba(12, 16, 24, .94);
            border: 1px solid rgba(255, 255, 255, .10);
            border-radius: 8px;
            box-shadow: 0 18px 45px rgba(0, 0, 0, .24);
        }

        .exp-ops-kicker {
            color: #ef4444;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
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
            border-color: #ef4444;
            color: #fff;
            box-shadow: 0 0 0 .2rem rgba(239, 68, 68, .18);
        }

        .exp-ops-page .form-select option {
            color: #111827;
        }

        .exp-ops-page input[type="datetime-local"]::-webkit-calendar-picker-indicator {
            filter: invert(1);
        }

        .exp-ops-table {
            color: #f8fafc;
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

        .exp-ops-pill.warn {
            background: #d97706;
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
    </style>

    <div class="exp-ops-page">
        @include('partials.breadcrumb-auto')

        <div class="exp-ops-header p-3 mb-3">
            <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
                <div>
                    <div class="exp-ops-kicker mb-2">Administração da Expedição</div>
                    <h3 class="text-white fw-bold mb-1">Apontar Conferência e Carregamento</h3>
                    <p class="exp-ops-muted mb-0">Tela operacional temporária para lançar tempos reais enquanto a automação não entra.</p>
                </div>
                <a href="{{ route('expedicao.previsibilidade.index') }}" class="btn btn-outline-light btn-sm">
                    <i class="mdi mdi-monitor-dashboard me-1"></i> Ver Painel
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
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label for="busca" class="form-label exp-ops-muted">Buscar DT, destino ou cliente</label>
                    <input type="text" name="busca" id="busca" class="form-control" value="{{ $busca }}" placeholder="Ex.: 251311087">
                </div>
                <div class="col-md-4">
                    <label for="status" class="form-label exp-ops-muted">Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="todos" @selected($status === 'todos')>Todos</option>
                        <option value="conferencia_pendente" @selected($status === 'conferencia_pendente')>Conferência pendente</option>
                        <option value="carregamento_pendente" @selected($status === 'carregamento_pendente')>Carregamento pendente</option>
                        <option value="finalizadas" @selected($status === 'finalizadas')>Conferência e carregamento finalizados</option>
                        <option value="sem_explosao" @selected($status === 'sem_explosao')>Sem explosão</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-danger flex-fill">
                        <i class="mdi mdi-filter-outline me-1"></i> Filtrar
                    </button>
                    <a href="{{ route('expedicao.apontamentos-operacionais.index') }}" class="btn btn-outline-light">
                        <i class="mdi mdi-close"></i>
                    </a>
                </div>
            </form>
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
                                    Nenhuma programação encontrada.
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
