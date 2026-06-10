@extends('layouts.app')

@section('title', 'SKUs WMS')

@section('content')
    <style>
        .wms-page { color: #f8fafc; }
        .wms-panel {
            background: rgba(12, 16, 24, .94);
            border: 1px solid rgba(255, 255, 255, .10);
            border-radius: 8px;
            box-shadow: 0 18px 45px rgba(0, 0, 0, .28);
        }
        .wms-page .table { color: #f8fafc; }
        .wms-page .table td,
        .wms-page .table th { border-color: rgba(255, 255, 255, .10); vertical-align: middle; }
        .wms-page .form-control {
            background: rgba(255, 255, 255, .06);
            border-color: rgba(255, 255, 255, .16);
            color: #fff;
        }
        .wms-muted { color: #a8b3c7; }
        .wms-badge {
            border-radius: 999px;
            padding: 5px 10px;
            border: 1px solid rgba(255,255,255,.12);
            background: rgba(255,255,255,.05);
            color: #dce3ef;
            font-weight: 700;
            font-size: 12px;
        }
    </style>

    <div class="wms-page container-fluid px-4 py-3">
        @include('partials.breadcrumb-auto')

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <h3 class="mb-1 fw-bold text-dark">SKUs WMS</h3>
                <p class="text-muted mb-0 small">Cadastro mestre de itens para a Separação Inteligente</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('wms.importacoes.index') }}" class="btn btn-primary btn-sm">
                    <i class="mdi mdi-file-upload-outline me-1"></i> Importar SKUs
                </a>
                <a href="{{ route('wms.posicoes.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="mdi mdi-map-marker-path me-1"></i> Posições
                </a>
            </div>
        </div>

        <div class="wms-panel p-3">
            <form method="GET" action="{{ route('wms.skus.index') }}" class="row g-2 align-items-end mb-3">
                <div class="col-md-6 col-lg-4">
                    <label for="busca" class="form-label wms-muted">Buscar por SKU, descrição, classe ou curva</label>
                    <input type="text" name="busca" id="busca" class="form-control" value="{{ $busca }}" placeholder="Ex.: 5004, PESADO, A">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">
                        <i class="mdi mdi-magnify me-1"></i> Buscar
                    </button>
                </div>
                @if ($busca !== '')
                    <div class="col-auto">
                        <a href="{{ route('wms.skus.index') }}" class="btn btn-outline-light">
                            <i class="mdi mdi-filter-remove-outline me-1"></i> Limpar
                        </a>
                    </div>
                @endif
            </form>

            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>SKU</th>
                            <th>Descrição</th>
                            <th>Peso kg</th>
                            <th>Classe peso</th>
                            <th>Classe cubagem</th>
                            <th>Curva</th>
                            <th>Status</th>
                            <th>Atualizado em</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($skus as $sku)
                            <tr>
                                <td class="fw-bold">{{ $sku->sku }}</td>
                                <td>{{ $sku->descricao ?? '-' }}</td>
                                <td>{{ $sku->peso_kg !== null ? number_format((float) $sku->peso_kg, 3, ',', '.') : '-' }}</td>
                                <td>{{ $sku->classe_peso ?? '-' }}</td>
                                <td>{{ $sku->classe_cubagem ?? '-' }}</td>
                                <td><span class="wms-badge">{{ $sku->curva_abc ?? '-' }}</span></td>
                                <td>
                                    <span class="badge {{ $sku->ativo ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $sku->ativo ? 'Ativo' : 'Inativo' }}
                                    </span>
                                </td>
                                <td>{{ optional($sku->updated_at)->format('d/m/Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center wms-muted py-4">Nenhum SKU cadastrado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $skus->links() }}
            </div>
        </div>
    </div>
@endsection
