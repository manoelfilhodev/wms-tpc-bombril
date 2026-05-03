@extends($layout)

@section('title', 'Relatório - Contagem de Itens')

@section('content')
<div class="container-fluid px-4 py-3">
    @include('partials.breadcrumb-auto')

    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div class="d-flex align-items-center">
            <div class="icon-wrapper me-3">
                <i class="mdi mdi-clipboard-text-outline display-6 text-primary"></i>
            </div>
            <div>
                <h3 class="mb-1 fw-bold text-dark">Relatório de Contagem</h3>
                <p class="text-muted mb-0 small">Histórico de contagens de itens realizadas</p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('contagem.itens.create') }}" class="btn btn-primary">
                <i class="mdi mdi-plus-circle-outline me-1"></i> Nova Contagem
            </a>
            @auth
            @if(Auth::user()->tipo === 'admin')
            <a href="{{ route('contagem.itens.excel') }}" class="btn btn-success">
                <i class="mdi mdi-file-excel-outline me-1"></i> Exportar Excel
            </a>
            @endif
            @endauth
        </div>
    </div>

    <!-- Cards de Resumo -->
    @if($contagens->count())
    <div class="row g-3 mb-4">
        @php
            $totalContagens = $contagens->count();
            $totalQuantidade = $contagens->sum('quantidade');
            $materiaisUnicos = $contagens->pluck('codigo_material')->unique()->count();
            $ultimaContagem = $contagens->first() ? \Carbon\Carbon::parse($contagens->first()->data_contagem)->format('d/m/Y') : 'N/A';
        @endphp

        <div class="col-md-3">
            <div class="card shadow-sm border-0 border-start border-4 border-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted small mb-1">Total de Contagens</p>
                            <h4 class="mb-0 fw-bold">{{ number_format($totalContagens, 0, ',', '.') }}</h4>
                        </div>
                        <div class="icon-stat bg-primary bg-opacity-10 text-primary">
                            <i class="mdi mdi-counter"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0 border-start border-4 border-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted small mb-1">Quantidade Total</p>
                            <h4 class="mb-0 fw-bold text-success">{{ number_format($totalQuantidade, 0, ',', '.') }}</h4>
                        </div>
                        <div class="icon-stat bg-success bg-opacity-10 text-success">
                            <i class="mdi mdi-package-variant"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0 border-start border-4 border-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted small mb-1">Materiais Únicos</p>
                            <h4 class="mb-0 fw-bold text-warning">{{ $materiaisUnicos }}</h4>
                        </div>
                        <div class="icon-stat bg-warning bg-opacity-10 text-warning">
                            <i class="mdi mdi-cube-outline"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0 border-start border-4 border-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted small mb-1">Última Contagem</p>
                            <h4 class="mb-0 fw-bold text-info" style="font-size: 1.3rem;">{{ $ultimaContagem }}</h4>
                        </div>
                        <div class="icon-stat bg-info bg-opacity-10 text-info">
                            <i class="mdi mdi-calendar-clock"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Card de Filtros -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="mb-0 fw-semibold">
                <i class="mdi mdi-filter-variant me-2 text-primary"></i>
                Filtros de Busca
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small text-muted mb-1">Material</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="mdi mdi-cube-outline text-muted"></i>
                        </span>
                        <select name="codigo_material" class="form-select border-start-0">
                            <option value="">-- Todos os Materiais --</option>
                            @foreach(\App\Models\ItemContagem::orderBy('descricao')->get() as $m)
                                <option value="{{ $m->codigo_material }}" 
                                    {{ request('codigo_material') == $m->codigo_material ? 'selected' : '' }}>
                                    {{ $m->codigo_material }} - {{ $m->descricao }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Data Início</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="mdi mdi-calendar-start text-muted"></i>
                        </span>
                        <input type="date" name="data_inicio" class="form-control border-start-0" value="{{ request('data_inicio') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Data Fim</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="mdi mdi-calendar-end text-muted"></i>
                        </span>
                        <input type="date" name="data_fim" class="form-control border-start-0" value="{{ request('data_fim') }}">
                    </div>
                </div>
                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="mdi mdi-magnify me-1"></i> Filtrar
                    </button>
                    @if(request()->hasAny(['codigo_material', 'data_inicio', 'data_fim']))
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
            @if($contagens->count())
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="px-4 py-3 text-muted small fw-semibold text-center">#</th>
                                <th class="px-4 py-3 text-muted small fw-semibold">
                                    <i class="mdi mdi-cube-outline me-1"></i> Material
                                </th>
                                <th class="px-4 py-3 text-muted small fw-semibold text-center">
                                    <i class="mdi mdi-counter me-1"></i> Quantidade
                                </th>
                                <th class="px-4 py-3 text-muted small fw-semibold">
                                    <i class="mdi mdi-account me-1"></i> Responsável
                                </th>
                                <th class="px-4 py-3 text-muted small fw-semibold text-center">
                                    <i class="mdi mdi-calendar-clock me-1"></i> Data/Hora
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($contagens as $item)
                                <tr class="border-bottom">
                                    <td class="px-4 py-3 text-center">
                                        <span class="badge bg-light text-dark border">{{ $item->id }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="d-flex flex-column">
                                            <span class="fw-semibold text-dark">{{ $item->material->codigo_material }}</span>
                                            <small class="text-muted">{{ $item->material->descricao }}</small>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="badge bg-primary bg-opacity-10 text-primary fs-6">
                                            {{ number_format($item->quantidade, 0, ',', '.') }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-circle me-2">
                                                <i class="mdi mdi-account"></i>
                                            </div>
                                            <span class="text-dark">{{ $item->usuario->nome ?? 'N/A' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <div class="d-flex flex-column">
                                            <span class="text-dark fw-semibold">
                                                {{ \Carbon\Carbon::parse($item->data_contagem)->format('d/m/Y') }}
                                            </span>
                                            <small class="text-muted">
                                                {{ \Carbon\Carbon::parse($item->data_contagem)->format('H:i') }}
                                            </small>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if(method_exists($contagens, 'links'))
                    <div class="card-footer bg-white border-top">
                        {{ $contagens->links() }}
                    </div>
                @endif
            @else
                <div class="text-center py-5">
                    <div class="text-muted">
                        <i class="mdi mdi-clipboard-text-off-outline display-4 d-block mb-3 opacity-25"></i>
                        <p class="mb-2 fw-semibold">Nenhuma contagem encontrada</p>
                        <small>Tente ajustar os filtros ou realize uma nova contagem</small>
                    </div>
                    <a href="{{ route('contagem.itens.create') }}" class="btn btn-primary mt-3">
                        <i class="mdi mdi-plus-circle-outline me-1"></i> Realizar Nova Contagem
                    </a>
                </div>
            @endif
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
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }
    .icon-wrapper i { color: white !important; }

    .icon-stat {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        font-size: 1.5rem;
    }

    .avatar-circle {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1rem;
    }

    .card { border-radius: 0.5rem; overflow: hidden; }
    
    .table tbody tr:hover { 
        background-color: #f8f9fa; 
        transition: background-color 0.2s ease; 
    }

    .badge { font-weight: 500; padding: 0.35em 0.65em; }

    .input-group-text {
        background-color: #f8f9fa;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.1);
    }
</style>

@endsection