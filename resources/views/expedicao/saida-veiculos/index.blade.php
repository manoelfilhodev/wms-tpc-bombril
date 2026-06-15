@extends('layouts.app')

@section('title', 'Saída de Veículos')

@section('content')
    <style>
        .exit-page { color: #f8fafc; }
        .exit-panel {
            background: rgba(12, 16, 24, .94);
            border: 1px solid rgba(255, 255, 255, .10);
            border-radius: 8px;
            box-shadow: 0 18px 45px rgba(0, 0, 0, .24);
        }
        .exit-muted { color: #a8b3c7; }
        .exit-page .form-control,
        .exit-page .form-select {
            background: rgba(255, 255, 255, .06);
            border-color: rgba(255, 255, 255, .16);
            color: #fff;
        }
        .exit-page .form-select option { background: #111827; color: #f8fafc; }
        .exit-card {
            min-height: 76px;
            padding: 12px;
            border: 1px solid rgba(148, 163, 184, .18);
            border-radius: 8px;
            background: rgba(15, 23, 42, .72);
        }
        .exit-card.done {
            border-color: rgba(34, 197, 94, .28);
            background: linear-gradient(135deg, rgba(21, 128, 61, .18), rgba(15, 23, 42, .72));
        }
        .exit-card-label {
            color: #a8b3c7;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .04em;
            text-transform: uppercase;
        }
        .exit-card-value {
            margin-top: 6px;
            color: #fff;
            font-size: 26px;
            font-weight: 900;
            line-height: 1;
        }
        .exit-table { color: #f8fafc; }
        .exit-table th {
            color: #cbd5e1;
            font-size: 12px;
            text-transform: uppercase;
            border-color: rgba(255, 255, 255, .10);
            white-space: nowrap;
        }
        .exit-table td {
            border-color: rgba(255, 255, 255, .08);
            vertical-align: middle;
        }
        .exit-dt { font-size: 18px; font-weight: 900; color: #fff; }
        .exit-pill {
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
        .exit-pill.ok { background: #1f9d4c; color: #fff; }
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

    <div class="exit-page container-fluid px-4 py-3">
        @include('partials.breadcrumb-auto')

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div class="d-flex align-items-center">
                <div class="icon-wrapper me-3">
                    <i class="mdi mdi-exit-run display-6"></i>
                </div>
                <div>
                    <h3 class="mb-1 fw-bold text-dark">Saída de Veículos</h3>
                    <p class="text-muted mb-0 small">Fechamento final da DT após carregamento</p>
                </div>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('expedicao.timeline-dts.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="mdi mdi-timeline-clock-outline me-1"></i> Timeline DTs
                </a>
                <a href="{{ route('expedicao.apontamentos-operacionais.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="mdi mdi-timer-edit-outline me-1"></i> Apontar Expedição
                </a>
                <a href="{{ route('expedicao.previsibilidade.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="mdi mdi-monitor-dashboard me-1"></i> Painel
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

        <div class="exit-panel p-3 mb-3">
            <div class="row g-3 align-items-end">
                <div class="col-xl-7">
                    <form method="GET" class="row g-2 align-items-end">
                        <div class="col-md-5">
                            <label for="busca" class="form-label exit-muted">Buscar DTs, destino ou cliente</label>
                            <input type="text" name="busca" id="busca" class="form-control" value="{{ $busca }}" placeholder="Ex.: 251311087, 251311088">
                        </div>
                        <div class="col-md-3">
                            <label for="status" class="form-label exit-muted">Status</label>
                            <select name="status" id="status" class="form-select">
                                <option value="PENDENTES" @selected($status === 'PENDENTES')>Aguardando saída</option>
                                <option value="FINALIZADAS" @selected($status === 'FINALIZADAS')>Saída registrada</option>
                                <option value="TODAS" @selected($status === 'TODAS')>Todas</option>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-fill">
                                <i class="mdi mdi-filter-outline me-1"></i> Filtrar
                            </button>
                            <a href="{{ route('expedicao.saida-veiculos.index') }}" class="btn btn-outline-light">
                                <i class="mdi mdi-close"></i>
                            </a>
                        </div>
                    </form>
                </div>

                <div class="col-xl-5">
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="exit-card">
                                <div class="exit-card-label">Aguardando saída</div>
                                <div class="exit-card-value">{{ $resumo['pendentes'] ?? 0 }}</div>
                                <div class="exit-muted small mt-1">Carregamento finalizado</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="exit-card done">
                                <div class="exit-card-label">Ciclo fechado</div>
                                <div class="exit-card-value">{{ $resumo['finalizadas'] ?? 0 }}</div>
                                <div class="exit-muted small mt-1">Saída registrada</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="exit-panel p-2">
            <div class="table-responsive">
                <table class="table exit-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>DT</th>
                            <th>Destino</th>
                            <th>Carregamento</th>
                            <th>Saída</th>
                            <th>Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($programacoes as $programacao)
                            @php
                                $demanda = $programacao->demanda;
                                $saidaRegistrada = $demanda?->saida_veiculo_em;
                            @endphp
                            <tr>
                                <td>
                                    <div class="exit-dt">{{ $programacao->dt_sap ?: $programacao->fo }}</div>
                                    <div class="exit-muted small">FO {{ $programacao->fo }}</div>
                                    <span class="exit-pill mt-1">{{ $programacao->tipo_demanda_label }}</span>
                                </td>
                                <td>
                                    <div class="fw-bold text-white">{{ $programacao->cidade_destino }}/{{ $programacao->uf_destino }}</div>
                                    <div class="exit-muted small">{{ $programacao->cliente ?? '-' }}</div>
                                </td>
                                <td>
                                    <div class="text-white fw-bold">{{ optional($demanda?->carregamento_finalizado_em)->format('d/m/Y H:i') ?? '-' }}</div>
                                    <div class="exit-muted small">Carga concluída</div>
                                </td>
                                <td>
                                    @if ($saidaRegistrada)
                                        <span class="exit-pill ok">Saída registrada</span>
                                        <div class="text-white fw-bold mt-1">{{ optional($saidaRegistrada)->format('d/m/Y H:i') }}</div>
                                    @else
                                        <span class="exit-pill">Aguardando saída</span>
                                    @endif
                                </td>
                                <td style="min-width: 260px;">
                                    <div class="d-flex flex-wrap gap-2">
                                        <a href="{{ route('expedicao.saida-veiculos.show', $programacao->fo) }}" class="btn btn-outline-light btn-sm">
                                            <i class="mdi mdi-timeline-clock-outline me-1"></i> Timeline
                                        </a>
                                        @if (! $saidaRegistrada)
                                            <form method="POST" action="{{ route('expedicao.programacoes.saida-veiculo.store', $programacao->fo) }}">
                                                @csrf
                                                <button type="submit" class="btn btn-success btn-sm">
                                                    <i class="mdi mdi-check-decagram-outline me-1"></i> Registrar saída
                                                </button>
                                            </form>
                                        @elseif ($podeEditar)
                                            <a href="{{ route('expedicao.saida-veiculos.show', $programacao->fo) }}#editar-saida" class="btn btn-outline-warning btn-sm">
                                                <i class="mdi mdi-pencil-outline me-1"></i> Editar
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center exit-muted py-4">
                                    Nenhuma DT encontrada para saída de veículo.
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
