@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    @include('partials.breadcrumb-auto')

    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div class="d-flex align-items-center">
            <div class="icon-wrapper me-3">
                <i class="mdi mdi-clipboard-list-outline display-6 text-primary"></i>
            </div>
            <div>
                <h3 class="mb-1 fw-bold text-dark">Inventários</h3>
                <p class="text-muted mb-0 small">Acompanhe o status e o progresso das contagens</p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ url()->current() }}" class="btn btn-outline-secondary btn-sm">
                <i class="mdi mdi-refresh"></i>
            </a>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small text-muted mb-1">Status</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="mdi mdi-filter-variant text-muted"></i>
                        </span>
                        <select name="status" class="form-select border-start-0">
                            <option value="">Todos os Status</option>
                            <option value="aberta" {{ request('status') == 'aberta' ? 'selected' : '' }}>Aberta</option>
                            <option value="contando" {{ request('status') == 'contando' ? 'selected' : '' }}>Contando</option>
                            <option value="contado" {{ request('status') == 'contado' ? 'selected' : '' }}>Contado</option>
                            <option value="concluida" {{ request('status') == 'concluida' ? 'selected' : '' }}>Concluída</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-8 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="mdi mdi-magnify me-1"></i> Filtrar
                    </button>
                    @if(request()->has('status') && request('status') !== null && request('status') !== '')
                        <a href="{{ url()->current() }}" class="btn btn-outline-secondary" data-bs-toggle="tooltip" title="Limpar filtros">
                            <i class="mdi mdi-close"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Tabela -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="px-4 py-3 text-muted small fw-semibold">ID</th>
                            <th class="px-4 py-3 text-muted small fw-semibold">Código</th>
                            <th class="px-4 py-3 text-muted small fw-semibold">Data</th>
                            <th class="px-4 py-3 text-muted small fw-semibold">Status</th>
                            <th class="px-4 py-3 text-muted small fw-semibold">Criador</th>
                            <th class="px-4 py-3 text-muted small fw-semibold text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($inventarios as $inv)
                            @php
                                $progress = (int) $inv->progresso;
                                $progressClass = $progress == 100 ? 'bg-success' : ($progress >= 50 ? 'bg-info' : 'bg-warning');
                                $badge = $progress == 100
                                    ? ['class' => 'bg-success', 'icon' => 'mdi-check-circle', 'text' => 'Completo']
                                    : ($progress >= 50
                                        ? ['class' => 'bg-info', 'icon' => 'mdi-progress-clock', 'text' => 'Em andamento']
                                        : ['class' => 'bg-warning text-dark', 'icon' => 'mdi-clock-outline', 'text' => 'Iniciado']);
                            @endphp
                            <tr class="border-bottom">
                                <td class="px-4 py-3">
                                    <span class="text-dark fw-semibold">#{{ $inv->id }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="badge bg-light text-dark border">{{ $inv->cod_requisicao }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    {{ \Carbon\Carbon::parse($inv->data_requisicao)->format('d/m/Y') }}
                                </td>
                                <td class="px-4 py-3" style="min-width: 260px;">
                                    <div class="d-flex flex-column">
                                        <div>
                                            <span class="badge {{ $badge['class'] }}">
                                                <i class="mdi {{ $badge['icon'] }} me-1"></i> {{ $badge['text'] }}
                                            </span>
                                            <small class="text-muted ms-2" title="{{ $inv->contados }} de {{ $inv->total_itens }} contados">
                                                {{ $progress }}%
                                            </small>
                                        </div>
                                        <div class="progress mt-2" style="height: 8px;">
                                            <div class="progress-bar {{ $progressClass }}"
                                                 role="progressbar"
                                                 style="width: {{ $progress }}%;"
                                                 aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100">
                                            </div>
                                        </div>
                                        <small class="text-muted mt-1">{{ $inv->contados }} de {{ $inv->total_itens }} itens</small>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    {{ $inv->usuario_criador }}
                                </td>
                                <td class="px-4 py-3 text-end">
                                    <div class="d-inline-flex gap-2">
                                        @if($inv->status === 'contando')
                                            <a href="{{ route('contar.inventario', ['idInventario' => $inv->id]) }}" class="btn btn-primary btn-sm">
                                                <i class="mdi mdi-play-circle-outline me-1"></i>
                                                Iniciar/Continuar
                                            </a>
                                        @elseif($inv->status === 'concluida')
                                            <a href="{{ route('inventario.validacao', $inv->id) }}" class="btn btn-success btn-sm">
                                                <i class="mdi mdi-check-decagram-outline me-1"></i>
                                                Ver Validação
                                            </a>
                                        @else
                                            <span class="text-muted small d-flex align-items-center">
                                                <i class="mdi mdi-timer-sand-empty me-1"></i> Aguardando
                                            </span>
                                        @endif

                                        <a href="{{ route('inventario.resumo', $inv->id) }}" class="btn btn-outline-info btn-sm" title="Ver resumo">
                                            <i class="mdi mdi-eye-outline"></i>
                                        </a>

                                        @if($inv->status === 'contando' && $inv->total_itens == $inv->contados)
                                            <form action="{{ route('inventario.efetivar', $inv->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Deseja efetivar esse inventário?')">
                                                @csrf
                                                <button class="btn btn-success btn-sm" title="Efetivar inventário">
                                                    <i class="mdi mdi-check-circle-outline"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="mdi mdi-clipboard-text-off-outline display-4 d-block mb-3 opacity-25"></i>
                                        <p class="mb-0">Nenhum inventário encontrado</p>
                                        <small>Tente ajustar os filtros</small>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if(method_exists($inventarios, 'links'))
            <div class="card-footer bg-white border-top">
                {{ $inventarios->links() }}
            </div>
        @endif
    </div>
</div>

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
    .icon-wrapper i { color: white !important; }
    .table tbody tr:hover { background-color: #f8f9fa; transition: background-color 0.2s ease; }
    .card { border-radius: 0.5rem; overflow: hidden; }
    .badge { font-weight: 500; padding: 0.35em 0.65em; }
    .progress { background-color: #eef2f7; }
</style>
@endsection