@extends('layouts.app')

@section('title', 'SKU x Posições WMS')

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
                <h3 class="mb-1 fw-bold text-dark">SKU x Posições</h3>
                <p class="text-muted mb-0 small">Amarração mestre entre itens e posições de picking</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('wms.sku-posicoes.importar') }}" class="btn btn-primary btn-sm">
                    <i class="mdi mdi-file-upload-outline me-1"></i> Importar vínculo
                </a>
                <a href="{{ route('wms.skus.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="mdi mdi-package-variant-closed me-1"></i> SKUs
                </a>
                <a href="{{ route('wms.posicoes.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="mdi mdi-map-marker-path me-1"></i> Posições
                </a>
            </div>
        </div>

        <div class="wms-panel p-3">
            <form method="GET" action="{{ route('wms.sku-posicoes.index') }}" class="row g-2 align-items-end mb-3">
                <div class="col-md-3">
                    <label for="sku" class="form-label wms-muted">SKU ou descrição</label>
                    <input type="text" name="sku" id="sku" class="form-control" value="{{ $filtros['sku'] }}" placeholder="Ex.: 5004">
                </div>
                <div class="col-md-2">
                    <label for="rua" class="form-label wms-muted">Rua</label>
                    <input type="text" name="rua" id="rua" class="form-control" value="{{ $filtros['rua'] }}" placeholder="Ex.: PA">
                </div>
                <div class="col-md-3">
                    <label for="endereco" class="form-label wms-muted">Endereço</label>
                    <input type="text" name="endereco" id="endereco" class="form-control" value="{{ $filtros['endereco'] }}" placeholder="Ex.: PA 059 1">
                </div>
                <div class="col-md-2">
                    <label for="curva_abc" class="form-label wms-muted">Curva ABC</label>
                    <input type="text" name="curva_abc" id="curva_abc" class="form-control" value="{{ $filtros['curva_abc'] }}" placeholder="A, B, C">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">
                        <i class="mdi mdi-magnify me-1"></i> Filtrar
                    </button>
                </div>
                @if (array_filter($filtros))
                    <div class="col-auto">
                        <a href="{{ route('wms.sku-posicoes.index') }}" class="btn btn-outline-light">
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
                            <th>Curva</th>
                            <th>Rua</th>
                            <th>Posição</th>
                            <th>Endereço</th>
                            <th>Lado</th>
                            <th>Qtd. padrão</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($vinculos as $vinculo)
                            <tr>
                                <td class="fw-bold">{{ $vinculo->skuCadastro?->sku ?? $vinculo->sku }}</td>
                                <td>{{ $vinculo->skuCadastro?->descricao ?? '-' }}</td>
                                <td><span class="wms-badge">{{ $vinculo->skuCadastro?->curva_abc ?? '-' }}</span></td>
                                <td>{{ $vinculo->posicao?->rua ?? '-' }}</td>
                                <td>{{ $vinculo->posicao?->posicao ?? '-' }}</td>
                                <td>{{ $vinculo->endereco ?? $vinculo->posicao?->endereco ?? '-' }}</td>
                                <td>{{ $vinculo->posicao?->lado ?? '-' }}</td>
                                <td>{{ $vinculo->qtd_padrao !== null ? number_format((float) $vinculo->qtd_padrao, 3, ',', '.') : '-' }}</td>
                                <td>
                                    <span class="badge {{ $vinculo->ativo ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $vinculo->ativo ? 'Ativo' : 'Inativo' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center wms-muted py-4">Nenhum vínculo SKU x posição cadastrado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $vinculos->links() }}
            </div>
        </div>
    </div>
@endsection
