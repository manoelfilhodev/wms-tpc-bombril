@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    @include('partials.breadcrumb-auto')
    
    <!-- Header com ícone roxo -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
            <div class="icon-wrapper me-3">
                <i class="mdi mdi-tools display-6 text-primary"></i>
            </div>
            <div>
                <h3 class="mb-1 fw-bold text-dark">Equipamentos</h3>
                <p class="text-muted mb-0 small">Gerencie os equipamentos do armazém</p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('equipamentos.export.excel') }}" class="btn btn-outline-secondary btn-sm" data-bs-toggle="tooltip" title="Exportar Excel">
                <i class="mdi mdi-file-excel"></i> Excel
            </a>
            <a href="{{ route('equipamentos.export.pdf') }}" class="btn btn-outline-secondary btn-sm" data-bs-toggle="tooltip" title="Exportar PDF">
                <i class="mdi mdi-file-pdf"></i> PDF
            </a>
            <a href="{{ route('equipamentos.create') }}" class="btn btn-primary btn-sm">
                <i class="mdi mdi-plus me-1"></i> Novo Equipamento
            </a>
        </div>
    </div>

    <!-- Card de Resumo -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <div class="row text-center justify-content-center">
                @php
                    $icones = [
                        'Empilhadeira' => 'empilhadeira.png',
                        'Televisão' => 'tv.png',
                        'Notebook' => 'notebook.png',
                        'PC' => 'pc.png',
                        'Celular' => 'celular.png',
                        'Coletor' => 'coletor.png',
                        'Máquina de limpar piso' => 'maquina_limpar_piso.png',
                        'Paleteira (2t)' => 'paleteira_2t.png',
                    ];
                @endphp

                @foreach ($icones as $tipo => $img)
                    <div class="col-6 col-sm-3 col-md-3 col-lg-2 mb-4 d-flex flex-column align-items-center">
                        <div style="width: 100px; height: 100px; border-radius: 50%; background: white; display: flex; align-items: center; justify-content: center; overflow: hidden; box-shadow: 0 2px 6px rgba(0,0,0,0.2);">
                            <img src="{{ asset('assets/equipamentos/' . $img) }}"
                                 alt="{{ $tipo }}"
                                 style="width: 80%; height: auto;">
                        </div>
                        <div class="mt-2 text-uppercase fw-bold small">{{ $tipo }}</div>
                        <div class="fw-bold fs-5">{{ $resumo[$tipo] ?? 0 }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Card de Filtros -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Tipo</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="mdi mdi-tag text-muted"></i>
                        </span>
                        <input type="text" name="tipo" class="form-control border-start-0" 
                               placeholder="Digite o tipo" value="{{ request('tipo') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Status</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="mdi mdi-check-circle text-muted"></i>
                        </span>
                        <select name="status" class="form-control border-start-0">
                            <option value="">-- Status --</option>
                            <option value="ativo" {{ request('status') == 'ativo' ? 'selected' : '' }}>Ativo</option>
                            <option value="manutenção" {{ request('status') == 'manutenção' ? 'selected' : '' }}>Manutenção</option>
                            <option value="inativo" {{ request('status') == 'inativo' ? 'selected' : '' }}>Inativo</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Localização</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="mdi mdi-map-marker text-muted"></i>
                        </span>
                        <input type="text" name="localizacao" class="form-control border-start-0" 
                               placeholder="Digite a localização" value="{{ request('localizacao') }}">
                    </div>
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="mdi mdi-magnify me-1"></i> Buscar
                    </button>
                    @if(request()->hasAny(['tipo', 'status', 'localizacao']))
                        <a href="{{ url()->current() }}" class="btn btn-outline-secondary" data-bs-toggle="tooltip" title="Limpar filtros">
                            <i class="mdi mdi-close"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Card da Tabela -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="px-4 py-3 text-muted small fw-semibold">
                                <i class="mdi mdi-tag me-1"></i> Tipo
                            </th>
                            <th class="px-4 py-3 text-muted small fw-semibold">
                                <i class="mdi mdi-information me-1"></i> Modelo
                            </th>
                            <th class="px-4 py-3 text-muted small fw-semibold">
                                <i class="mdi mdi-barcode me-1"></i> Patrimônio
                            </th>
                            <th class="px-4 py-3 text-muted small fw-semibold">
                                <i class="mdi mdi-check-circle me-1"></i> Status
                            </th>
                            <th class="px-4 py-3 text-muted small fw-semibold">
                                <i class="mdi mdi-map-marker me-1"></i> Localização
                            </th>
                            <th class="px-4 py-3 text-muted small fw-semibold">
                                <i class="mdi mdi-account me-1"></i> Responsável
                            </th>
                            <th class="px-4 py-3 text-muted small fw-semibold text-center">
                                <i class="mdi mdi-cog me-1"></i> Ações
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($equipamentos as $eq)
                            <tr class="border-bottom">
                                <td class="px-4 py-3">
                                    <span class="text-dark">{{ $eq->tipo }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-dark">{{ $eq->modelo }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="badge bg-light text-dark border">{{ $eq->patrimonio }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="badge bg-primary bg-opacity-10 text-primary">
                                        {{ ucfirst($eq->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-dark">{{ $eq->localizacao }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-dark">{{ $eq->responsavel }}</span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <a href="{{ route('equipamentos.edit', $eq->id) }}" class="btn btn-sm btn-warning">
                                        <i class="mdi mdi-pencil"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="mdi mdi-tools display-4 d-block mb-3 opacity-25"></i>
                                        <p class="mb-0">Nenhum equipamento encontrado</p>
                                        <small>Tente ajustar os filtros de busca</small>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    .icon-wrapper {
        width: 60px; height: 60px;
        display: flex; align-items: center; justify-content: center;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(102,126,234,0.3);
    }
    .icon-wrapper i { color: #fff !important; }

    .input-group-text { background-color: #f8f9fa; }
    .form-control:focus { border-color: #0d6efd; box-shadow: 0 0 0 0.2rem rgba(13,110,253,0.1); }
    .table tbody tr:hover { background-color: #f8f9fa; transition: background-color 0.2s ease; }
    .card { border-radius: 0.5rem; }
    .badge { font-weight: 500; padding: 0.35em 0.65em; }
</style>

@endsection