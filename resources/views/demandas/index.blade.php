@extends('layouts.app')

@section('content')
    @php
        $isOperator = Auth::user()?->tipo === 'operador';
    @endphp

    <div class="container-fluid px-4 py-3">
        @include('partials.breadcrumb-auto')

        <!-- Header com ícone roxo (padrão gestão de estoque) -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center">
                <div class="icon-wrapper me-3">
                    <i class="mdi mdi-truck-fast display-6"></i>
                </div>
                <div>
                    <h3 class="mb-1 fw-bold text-dark">{{ !empty($modoOperacional) ? 'DTs Picking' : 'Demandas Lançadas' }}
                    </h3>
                    <p class="text-muted mb-0 small">
                        {{ !empty($modoOperacional) ? 'Visão do ADM Operacional: apenas DTs com picking' : 'Gerencie recebimentos e expedições' }}
                    </p>
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('demandas.operacional') }}" class="btn btn-outline-danger btn-sm" data-bs-toggle="tooltip"
                    title="Ver somente DTs com sobra">
                    <i class="mdi mdi-filter-variant"></i> Operacional
                </a>
                @if (!$isOperator)
                    <a href="{{ route('demandas.dashboardOperacional') }}" class="btn btn-outline-secondary btn-sm"
                        data-bs-toggle="tooltip" title="Dashboard de produtividade">
                        <i class="mdi mdi-chart-line"></i> Dashboard
                    </a>
                    <a href="{{ route('demandas.relatorios') }}" class="btn btn-outline-secondary btn-sm"
                        data-bs-toggle="tooltip" title="Relatórios operacionais">
                        <i class="mdi mdi-file-chart-outline"></i> Relatórios
                    </a>
                @endif
                <a href="{{ route('demandas.identificacaoA4') }}" class="btn btn-outline-secondary btn-sm"
                    data-bs-toggle="tooltip" title="Imprimir identificação A4">
                    <i class="mdi mdi-printer-outline"></i> Identificação
                </a>
                @if (!$isOperator)
                    
                    <a href="{{ route('demandas.import.view') }}" class="btn btn-outline-secondary btn-sm"
                        data-bs-toggle="tooltip" title="Importar via Excel">
                        <i class="mdi mdi-file-excel"></i>
                    </a>
                    <a href="{{ route('demandas.export', request()->query()) }}" class="btn btn-outline-secondary btn-sm"
                        data-bs-toggle="tooltip" title="Exportar">
                        <i class="mdi mdi-download"></i>
                    </a>
                @endif
                <button class="btn btn-outline-secondary btn-sm" onclick="location.reload()" data-bs-toggle="tooltip"
        title="Atualizar">
    <i class="mdi mdi-refresh"></i>
</button>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="mdi mdi-check-circle-outline me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="mdi mdi-alert-circle-outline me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (!empty($modoOperacional) && !empty($resumoOperacional))
            <div class="alert alert-secondary border-0 shadow-sm small d-flex flex-wrap gap-3 align-items-center"
                role="status">
                <span>DTs geradas no período: <strong>{{ $resumoOperacional['geradas'] }}</strong></span>
                <span>Entram no picking: <strong>{{ $resumoOperacional['picking'] }}</strong></span>
                @if ($resumoOperacional['fora_picking'] > 0)
                    <span>Fora do picking: <strong>{{ $resumoOperacional['fora_picking'] }}</strong></span>
                @endif
                @if (($resumoOperacional['finalizadas_fora_data_criacao'] ?? 0) > 0)
                    <span>Finalizadas fora da data de criação:
                        <strong>{{ $resumoOperacional['finalizadas_fora_data_criacao'] }}</strong></span>
                @endif
            </div>
        @endif

        <!-- Card de Filtros (padrão gestão de estoque) -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('demandas.index') }}" class="row g-3 demanda-filter-form align-items-end" autocomplete="off">
                    @if (!empty($modoOperacional))
                        <input type="hidden" name="somente_sobra" value="1">
                    @endif
                    <div class="col-12 {{ !empty($modoOperacional) ? 'col-lg-3' : 'col-lg-2' }}">
                        <label class="form-label small text-muted mb-1">DT</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="mdi mdi-pound text-muted"></i>
                            </span>
                            <input type="text" name="fo" class="form-control border-start-0"
                                placeholder="Digite a DT" value="{{ request('fo') }}">
                        </div>
                    </div>
                    @if (empty($modoOperacional))
                        <div class="col-12 col-lg-3">
                            <label class="form-label small text-muted mb-1">Transportadora</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="mdi mdi-truck-outline text-muted"></i>
                                </span>
                                <input type="text" name="transportadora" class="form-control border-start-0"
                                    placeholder="Nome da transportadora" value="{{ request('transportadora') }}">
                            </div>
                        </div>
                    @endif
                    @php
                        $statusSelecionados = request()->query->has('status')
                            ? collect((array) request()->query('status'))->filter()->values()->all()
                            : [];
                    @endphp
                    <div class="col-12 {{ !empty($modoOperacional) ? 'col-lg-3' : 'col-lg-2' }}">
                        <label class="form-label small text-muted mb-1">Data Início</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="mdi mdi-calendar-start text-muted"></i>
                            </span>
                            <input type="date" name="data_inicio" class="form-control border-start-0"
                                value="{{ request('data_inicio') }}">
                        </div>
                    </div>
                    <div class="col-12 {{ !empty($modoOperacional) ? 'col-lg-3' : 'col-lg-2' }}">
                        <label class="form-label small text-muted mb-1">Data Fim</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="mdi mdi-calendar-end text-muted"></i>
                            </span>
                            <input type="date" name="data_fim" class="form-control border-start-0"
                                value="{{ request('data_fim') }}">
                        </div>
                    </div>
                    <div class="col-12 col-lg-3">
                        <label class="form-label small text-muted mb-1">Ordenar</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">
                                <i class="mdi mdi-sort"></i>
                            </span>
                            <select name="ordem" class="form-select">
                                <option value="mais_novas" @selected(request('ordem', 'mais_novas') === 'mais_novas')>Mais novas</option>
                                <option value="mais_antigas" @selected(request('ordem') === 'mais_antigas')>Mais antigas</option>
                                <option value="dt_asc" @selected(request('ordem') === 'dt_asc')>DT menor primeiro</option>
                                <option value="dt_desc" @selected(request('ordem') === 'dt_desc')>DT maior primeiro</option>
                                <option value="itens_desc" @selected(request('ordem') === 'itens_desc')>Mais itens</option>
                                <option value="itens_asc" @selected(request('ordem') === 'itens_asc')>Menos itens</option>
                                <option value="picking_desc" @selected(request('ordem') === 'picking_desc')>Mais peças</option>
                                <option value="picking_asc" @selected(request('ordem') === 'picking_asc')>Menos peças</option>
                                <option value="saldo_desc" @selected(request('ordem') === 'saldo_desc')>Maior saldo</option>
                                <option value="saldo_asc" @selected(request('ordem') === 'saldo_asc')>Menor saldo</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="filter-status-row">
                            <div class="filter-status-main">
                                <label class="form-label small text-muted mb-1">Status</label>
                                <div class="status-filter-group" aria-label="Status">
                                    <input type="checkbox" class="btn-check" name="status[]" id="status-a-separar"
                                        value="A_SEPARAR" autocomplete="off" @checked(in_array('A_SEPARAR', $statusSelecionados, true))>
                                    <label class="status-filter-chip" for="status-a-separar">
                                        <span class="status-dot status-dot-info"></span>
                                        A separar
                                    </label>

                                    <input type="checkbox" class="btn-check" name="status[]" id="status-separando"
                                        value="SEPARANDO" autocomplete="off" @checked(in_array('SEPARANDO', $statusSelecionados, true))>
                                    <label class="status-filter-chip" for="status-separando">
                                        <span class="status-dot status-dot-primary"></span>
                                        Separando
                                    </label>

                                    <input type="checkbox" class="btn-check" name="status[]" id="status-separado-parcial"
                                        value="SEPARADO_PARCIAL" autocomplete="off" @checked(in_array('SEPARADO_PARCIAL', $statusSelecionados, true))>
                                    <label class="status-filter-chip" for="status-separado-parcial">
                                        <span class="status-dot status-dot-warning"></span>
                                        Parcial
                                    </label>

                                    <input type="checkbox" class="btn-check" name="status[]" id="status-separado"
                                        value="SEPARADO" autocomplete="off" @checked(in_array('SEPARADO', $statusSelecionados, true))>
                                    <label class="status-filter-chip" for="status-separado">
                                        <span class="status-dot status-dot-success"></span>
                                        Separado
                                    </label>
                                </div>
                            </div>
                            <div class="filter-actions">
                                <button type="submit" class="btn btn-rosa" data-bs-toggle="tooltip"
                                    title="Aplicar filtros">
                                    <i class="mdi mdi-magnify"></i>
                                </button>
                                @if (request()->hasAny(['fo', 'transportadora', 'status', 'data_inicio', 'data_fim', 'ordem']))
                                    <a href="{{ !empty($modoOperacional) ? route('demandas.operacional') : route('demandas.index') }}"
                                        class="btn btn-outline-secondary" data-bs-toggle="tooltip" title="Limpar filtros">
                                        <i class="mdi mdi-close"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Card da Tabela (padrão gestão de estoque) -->
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <form action="{{ route('demandas.updateMultiple') }}" method="POST">
                    @csrf
                    @method('PATCH')

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="px-4 py-3">
                                        <input type="checkbox" id="checkAll" class="form-check-input">
                                    </th>
                                    <th class="px-4 py-3 text-muted small fw-semibold">
                                        <i class="mdi mdi-pound me-1"></i> DT
                                    </th>
                                    <th class="px-4 py-3 text-muted small fw-semibold">
                                        <i class="mdi mdi-map-marker-outline me-1"></i> Stage
                                    </th>
                                    @if (empty($modoOperacional))
                                        <th class="px-4 py-3 text-muted small fw-semibold">
                                            <i class="mdi mdi-truck-outline me-1"></i> Transportadora
                                        </th>
                                    @endif
                                    <th class="px-4 py-3 text-muted small fw-semibold text-center">Itens c/ sobra</th>
                                    <th class="px-4 py-3 text-muted small fw-semibold">
                                        <i class="mdi mdi-flag-outline me-1"></i> Status
                                    </th>
                                    <th class="px-4 py-3 text-muted small fw-semibold text-center">Picking</th>
                                    <th class="px-4 py-3 text-muted small fw-semibold text-center">Distribuição</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $cores = [
                                        'GERAR' => 'secondary',
                                        'A_SEPARAR' => 'info',
                                        'SEPARANDO' => 'primary',
                                        'A_CONFERIR' => 'warning',
                                        'CONFERINDO' => 'primary',
                                        'CONFERIDO' => 'success',
                                        'A_CARREGAR' => 'warning',
                                        'CARREGANDO' => 'primary',
                                        'CARREGADO' => 'success',
                                        'FATURANDO' => 'danger',
                                        'LIBERADO' => 'success',
                                    ];
                                @endphp

                                @forelse($demandas as $d)
                                    <tr class="border-bottom">
                                        @php
                                            $totalPicking = (int) round((float) ($d->total_pecas_picking ?? 0));
                                            $totalDistribuido = (int) ($d->total_pecas_distribuidas ?? 0);
                                            $restante = max(0, $totalPicking - $totalDistribuido);
                                            $percentual =
                                                $totalPicking > 0
                                                    ? min(100, (int) round(($totalDistribuido / $totalPicking) * 100))
                                                    : 0;

                                            $statusDinamico = 'A SEPARAR';
                                            $statusDinamicoCor = 'info';
                                            if ($d->separacao_finalizada_em) {
                                                if ($d->separacao_resultado === 'PARCIAL') {
                                                    $statusDinamico = 'SEPARADO PARCIAL';
                                                    $statusDinamicoCor = 'warning';
                                                } else {
                                                    $statusDinamico = 'SEPARADO';
                                                    $statusDinamicoCor = 'success';
                                                }
                                            } elseif ($totalDistribuido > 0 || $d->separacao_iniciada_em) {
                                                $statusDinamico = 'SEPARANDO';
                                                $statusDinamicoCor = 'primary';
                                            }

                                            $podeFinalizar =
                                                !$d->separacao_finalizada_em &&
                                                $statusDinamico === 'SEPARANDO' &&
                                                $totalPicking > 0 &&
                                                $totalDistribuido >= $totalPicking;
                                        @endphp
                                        <td class="px-4 py-3">
                                            <input type="checkbox" name="ids[]" value="{{ $d->id }}"
                                                class="form-check-input">
                                        </td>
                                        <td class="px-4 py-3 fw-semibold">
                                            <a href="#" data-bs-toggle="modal"
                                                data-bs-target="#modalDistribuicao{{ $d->id }}">{{ $d->fo }}</a>
                                        </td>
                                        <td class="px-4 py-3" style="min-width:220px;">
                                            <div class="input-group input-group-sm">
                                                <input type="text" name="stage" form="stageForm{{ $d->id }}"
                                                    class="form-control" value="{{ $d->stage }}" maxlength="100"
                                                    placeholder="Stage" aria-label="Stage da DT {{ $d->fo }}">
                                                <button type="submit" form="stageForm{{ $d->id }}"
                                                    class="btn btn-outline-primary" data-bs-toggle="tooltip"
                                                    title="Salvar Stage">
                                                    <i class="mdi mdi-content-save-outline"></i>
                                                </button>
                                            </div>
                                        </td>
                                        @if (empty($modoOperacional))
                                            <td class="px-4 py-3">{{ $d->transportadora }}</td>
                                        @endif
                                        <td class="px-4 py-3 text-center">
                                            <span
                                                class="badge bg-light text-dark border">{{ $d->total_itens_com_sobra ?? 0 }}</span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="badge bg-{{ $statusDinamicoCor }}">
                                                {{ $statusDinamico }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <div class="small text-muted mb-1">Peças a separar</div>
                                            <div class="fw-semibold">{{ $totalPicking }}</div>
                                        </td>
                                        <td class="px-4 py-3 text-center" style="min-width:220px;">
                                            <div class="small mb-1">{{ $totalDistribuido }}/{{ $totalPicking }} peças
                                                ({{ $percentual }}%)
                                            </div>
                                            <div class="progress" style="height:8px;">
                                                <div class="progress-bar bg-success" role="progressbar"
                                                    style="width: {{ $percentual }}%;"></div>
                                            </div>
                                            <div class="small text-muted mt-1">Restante: {{ $restante }}</div>
                                        </td>
                                    </tr>
                                @empty
                                    @php
                                        $statusLabelsFiltro = [
                                            'A_SEPARAR' => 'A separar',
                                            'SEPARANDO' => 'Separando',
                                            'SEPARADO_PARCIAL' => 'Separado parcial',
                                            'SEPARADO' => 'Separado',
                                        ];
                                        $statusFiltroTexto = collect($statusSelecionados)
                                            ->map(fn ($status) => $statusLabelsFiltro[$status] ?? $status)
                                            ->implode(', ');
                                    @endphp
                                    <tr>
                                        <td colspan="{{ !empty($modoOperacional) ? 7 : 8 }}" class="text-center py-5">
                                            <div class="text-muted">
                                                <i
                                                    class="mdi mdi-package-variant-closed display-4 d-block mb-3 opacity-25"></i>
                                                @if ($statusFiltroTexto !== '')
                                                    <p class="mb-0">Nenhuma DT encontrada com status {{ $statusFiltroTexto }} neste período.</p>
                                                    <small>Altere o status, ajuste as datas ou limpe os filtros.</small>
                                                @else
                                                    <p class="mb-0">Nenhuma demanda encontrada</p>
                                                    <small>Tente ajustar os filtros ou criar uma nova demanda</small>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </form>
                @foreach ($demandas as $d)
                    <form id="stageForm{{ $d->id }}" action="{{ route('demandas.updateStage', $d->id) }}"
                        method="POST" class="d-none">
                        @csrf
                        @method('PATCH')
                    </form>
                @endforeach
            </div>

            @if ($demandas->hasPages())
                <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center">
                    <small class="text-muted">
                        Mostrando {{ $demandas->firstItem() }} a {{ $demandas->lastItem() }} de {{ $demandas->total() }}
                        registros
                    </small>
                    {{ $demandas->links() }}
                </div>
            @endif
        </div>
    </div>

    @foreach ($demandas as $d)
        @php
            $totalSkusPicking = (int) ($d->total_skus_picking ?? 0);
            $totalPicking = (int) round((float) ($d->total_pecas_picking ?? 0));
            $totalDistribuido = (int) ($d->total_pecas_distribuidas ?? 0);
            $totalSkusDistribuidos = (int) $d->distribuicoes->sum('quantidade_skus');
            $restante = max(0, $totalPicking - $totalDistribuido);
            $skusRestantes = max(0, $totalSkusPicking - $totalSkusDistribuidos);
        @endphp
        <div class="modal fade" id="modalDistribuicao{{ $d->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Distribuição da DT {{ $d->fo }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3 mb-3">
                            <div class="col-md-3">
                                <div class="small text-muted">Total Picking</div>
                                <div class="fw-semibold">{{ $totalPicking }}</div>
                            </div>
                            <div class="col-md-3">
                                <div class="small text-muted">Qtd SKUs</div>
                                <div class="fw-semibold">{{ $totalSkusPicking }}</div>
                            </div>
                            <div class="col-md-3">
                                <div class="small text-muted">Já Distribuído</div>
                                <div class="fw-semibold">{{ $totalDistribuido }}</div>
                            </div>
                            <div class="col-md-3">
                                <div class="small text-muted">Saldo</div>
                                <div class="fw-semibold">{{ $restante }} peças / {{ $skusRestantes }} SKUs</div>
                            </div>
                        </div>

                        @php
                            $inicio = $d->separacao_iniciada_em
                                ? \Carbon\Carbon::parse($d->separacao_iniciada_em)
                                : null;
                            $fim = $d->separacao_finalizada_em
                                ? \Carbon\Carbon::parse($d->separacao_finalizada_em)
                                : null;
                        @endphp
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <div class="small text-muted">Início da separação</div>
                                <div class="fw-semibold">{{ $inicio ? $inicio->format('d/m/Y H:i:s') : '-' }}</div>
                            </div>
                            <div class="col-md-4">
                                <div class="small text-muted">Fim da separação</div>
                                <div class="fw-semibold">{{ $fim ? $fim->format('d/m/Y H:i:s') : '-' }}</div>
                            </div>
                            <div class="col-md-4">
                                <div class="small text-muted">Tempo da separação</div>
                                <div class="fw-semibold">
                                    @if ($inicio && $fim)
                                        {{ $inicio->diff($fim)->format('%H:%I:%S') }}
                                    @elseif($inicio && !$fim)
                                        Em andamento
                                    @else
                                        -
                                    @endif
                                </div>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('demandas.distribuir', $d->id) }}" class="row g-2 mb-3">
                            @csrf
                            <div class="col-md-5">
                                <label class="form-label small text-muted mb-1">Nome do separador</label>
                                <select name="separador_nome" class="form-select form-select-sm separador-select"
                                    required>
                                    <option value=""></option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small text-muted mb-1">Qtd peças</label>
                                <input type="number" name="quantidade_pecas" min="1" max="{{ $restante }}"
                                    class="form-control form-control-sm" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small text-muted mb-1">Qtd SKUs</label>
                                <input type="number" name="quantidade_skus" min="1" max="{{ $skusRestantes }}"
                                    class="form-control form-control-sm" required>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-sm btn-primary w-100">Distribuir</button>
                            </div>
                        </form>

                        @php
                            $distribuicoesPorSeparador = $d->distribuicoes
                                ->groupBy('separador_nome')
                                ->map(function ($itens) {
                                    $inicio = $itens->min('created_at');
                                    $fim = $itens->whereNotNull('finalizado_em')->max('finalizado_em');
                                    $resultado = $itens->whereNotNull('resultado')->last()?->resultado;
                                    return [
                                        'separador_nome' => $itens->first()->separador_nome,
                                        'quantidade_pecas' => (int) $itens->sum('quantidade_pecas'),
                                        'quantidade_skus' => (int) $itens->sum('quantidade_skus'),
                                        'inicio' => $inicio ? \Carbon\Carbon::parse($inicio) : null,
                                        'fim' => $fim ? \Carbon\Carbon::parse($fim) : null,
                                        'resultado' => $resultado,
                                    ];
                                })
                                ->values();
                        @endphp

                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>Separador</th>
                                        <th class="text-end">Qtd peças</th>
                                        <th class="text-end">Qtd SKUs</th>
                                        <th class="text-end">Início</th>
                                        <th class="text-end">Fim</th>
                                        <th class="text-end">Tempo</th>
                                        <th class="text-end">Status</th>
                                        <th class="text-end">Ação</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($distribuicoesPorSeparador as $dist)
                                        <tr>
                                            <td>{{ $dist['separador_nome'] }}</td>
                                            <td class="text-end">{{ $dist['quantidade_pecas'] }}</td>
                                            <td class="text-end">{{ $dist['quantidade_skus'] }}</td>
                                            <td class="text-end">{{ $dist['inicio']?->format('d/m/Y H:i') ?? '-' }}</td>
                                            <td class="text-end">{{ $dist['fim']?->format('d/m/Y H:i') ?? '-' }}</td>
                                            <td class="text-end">
                                                @if ($dist['inicio'] && $dist['fim'])
                                                    {{ $dist['inicio']->diff($dist['fim'])->format('%H:%I:%S') }}
                                                @elseif($dist['inicio'])
                                                    Em andamento
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                @if ($dist['fim'])
                                                    @if ($dist['resultado'] === 'PARCIAL')
                                                        <span class="badge bg-warning">Parcial</span>
                                                    @else
                                                        <span class="badge bg-success">Completa</span>
                                                    @endif
                                                @else
                                                    <span class="badge bg-primary">Separando</span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                @if (!$dist['fim'])
                                                    <form action="{{ route('demandas.finalizarSeparador', $d->id) }}"
                                                        method="POST" class="d-inline-flex gap-1">
                                                        @csrf
                                                        <input type="hidden" name="separador_nome"
                                                            value="{{ $dist['separador_nome'] }}">
                                                        <button type="submit" name="resultado" value="PARCIAL"
                                                            class="btn btn-sm btn-warning">Parcial</button>
                                                        <button type="submit" name="resultado" value="COMPLETA"
                                                            class="btn btn-sm btn-success">Completa</button>
                                                    </form>
                                                @else
                                                    <span class="text-muted small">Finalizado</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center text-muted">Sem distribuição registrada.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach


    <script>
        document.getElementById('checkAll')?.addEventListener('change', function() {
            document.querySelectorAll('input[name="ids[]"]').forEach(cb => cb.checked = this.checked);
        });

        window.addEventListener('pageshow', function() {
            const params = new URLSearchParams(window.location.search);
            const hasStatusFilter = params.has('status') || params.has('status[]');

            if (!hasStatusFilter) {
                document.querySelectorAll('.status-filter-group input[name="status[]"]').forEach(function(input) {
                    input.checked = false;
                });
            }
        });
    </script>

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        $('.modal').on('shown.bs.modal', function() {
            const modal = $(this);
            const select = modal.find('.separador-select');

            if (select.hasClass('select2-hidden-accessible')) {
                select.select2('destroy');
            }

            select.select2({
                width: '100%',
                placeholder: 'Digite o nome ou chapa do separador',
                dropdownParent: modal,
                minimumInputLength: 2,
                ajax: {
                    url: "{{ route('usuarios.buscar') }}",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term || ''
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data.map(function(separador) {
                                return {
                                    id: separador.nome,
                                    text: separador.nome + ' - ' + separador.chapa
                                };
                            })
                        };
                    },
                    cache: false
                },
                language: {
                    inputTooShort: function() {
                        return 'Digite pelo menos 2 caracteres';
                    },
                    searching: function() {
                        return 'Buscando...';
                    },
                    noResults: function() {
                        return 'Nenhum separador encontrado';
                    }
                }
            });
        });
    });
</script>

    <style>
        .icon-wrapper {
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        .icon-wrapper i {
            color: #fff !important;
        }

        .input-group-text {
            background-color: #f8f9fa;
        }

        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.1);
        }

        .table tbody tr:hover {
            background-color: #f8f9fa;
            transition: background-color 0.2s ease;
        }

        .card {
            border-radius: 0.5rem;
        }

        .badge {
            font-weight: 500;
            padding: 0.35em 0.65em;
        }

        /* Select2 dentro do modal dark */
        .select2-container--default .select2-selection--single {
            height: 34px;
            background-color: #13243a;
            border: 1px solid #263a55;
            border-radius: 8px;
            display: flex;
            align-items: center;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #fff;
            line-height: 34px;
            padding-left: 12px;
            font-size: 0.875rem;
        }

        .select2-container--default .select2-selection--single .select2-selection__placeholder {
            color: #9ca3af;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 34px;
            right: 8px;
        }

        .select2-dropdown {
            background-color: #101f33;
            border: 1px solid #263a55;
            border-radius: 8px;
            overflow: hidden;
        }

        .select2-search--dropdown {
            padding: 8px;
            background-color: #101f33;
        }

        .select2-container--default .select2-search--dropdown .select2-search__field {
            background-color: #0b1728;
            color: #fff;
            border: 1px solid #31445f;
            border-radius: 6px;
            outline: none;
            padding: 6px 10px;
        }

        .select2-results__option {
            color: #e5e7eb;
            padding: 8px 12px;
            font-size: 0.875rem;
        }

        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #2563eb;
            color: #fff;
        }

        .select2-container--default .select2-results__option[aria-selected=true] {
            background-color: #1f3654;
            color: #fff;
        }

        .select2-results__message {
            color: #9ca3af;
        }
       
        .filter-status-row {
            display: flex;
            align-items: flex-end;
            gap: 12px;
            padding-top: 2px;
        }

        .filter-status-main {
            flex: 1 1 auto;
            min-width: 0;
        }

        .filter-actions {
            display: flex;
            flex: 0 0 auto;
            gap: 8px;
        }

        .filter-actions .btn {
            min-width: 48px;
            min-height: 38px;
        }

        .filter-actions .btn-rosa {
            min-width: 138px;
        }

        .status-filter-group {
            display: grid;
            grid-template-columns: repeat(4, minmax(120px, 1fr));
            gap: 8px;
            min-height: 38px;
        }

        .status-filter-chip {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            min-height: 38px;
            padding: 0 10px;
            border: 1px solid #d7dfec;
            border-radius: 6px;
            background: #fff;
            color: #44546a;
            cursor: pointer;
            font-size: 0.78rem;
            font-weight: 700;
            line-height: 1;
            transition: border-color 0.15s ease, box-shadow 0.15s ease, color 0.15s ease, background-color 0.15s ease;
            user-select: none;
            white-space: nowrap;
        }

        .status-filter-chip:hover {
            border-color: #9fb2cc;
            color: #1f2937;
            background: #f8fafc;
        }

        .btn-check:checked + .status-filter-chip {
            border-color: #ff4da6;
            background: rgba(255, 77, 166, 0.09);
            box-shadow: 0 0 0 2px rgba(255, 77, 166, 0.12);
            color: #111827;
        }

        .btn-check:focus + .status-filter-chip {
            border-color: #ff4da6;
            box-shadow: 0 0 0 3px rgba(255, 77, 166, 0.16);
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            flex: 0 0 8px;
        }

        .status-dot-info { background: #0dcaf0; }
        .status-dot-primary { background: #0d6efd; }
        .status-dot-warning { background: #ffc107; }
        .status-dot-success { background: #198754; }

        @media (max-width: 991.98px) {
            .filter-status-row {
                align-items: stretch;
                flex-direction: column;
            }

            .filter-actions .btn,
            .filter-actions .btn-rosa {
                flex: 1 1 0;
                min-width: 0;
            }
        }

        @media (max-width: 767.98px) {
            .status-filter-group {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 479.98px) {
            .status-filter-group {
                grid-template-columns: 1fr;
            }
        }

        .btn-rosa {
            background: linear-gradient(135deg, #ff4da6, #ff1a75);
            border: none;
            color: #fff;
        }

        .btn-rosa:hover {
            background: linear-gradient(135deg, #ff1a75, #e6005c);
            box-shadow: 0 0 10px rgba(255, 77, 166, 0.6);
        }
    </style>
@endsection
