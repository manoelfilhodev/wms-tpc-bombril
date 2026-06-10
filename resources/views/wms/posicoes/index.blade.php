@extends('layouts.app')

@section('title', 'Posições WMS')

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
    </style>

    <div class="wms-page container-fluid px-4 py-3">
        @include('partials.breadcrumb-auto')

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <h3 class="mb-1 fw-bold text-dark">Posições de Picking</h3>
                <p class="text-muted mb-0 small">Cadastro mestre de endereços distintos, sem vínculo com SKU nesta etapa</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('wms.importacoes.index') }}" class="btn btn-primary btn-sm">
                    <i class="mdi mdi-file-upload-outline me-1"></i> Importar Posições
                </a>
                <a href="{{ route('wms.skus.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="mdi mdi-package-variant-closed me-1"></i> SKUs
                </a>
            </div>
        </div>

        <div class="wms-panel p-3">
            <form method="GET" action="{{ route('wms.posicoes.index') }}" class="row g-2 align-items-end mb-3">
                <div class="col-md-6 col-lg-4">
                    <label for="busca" class="form-label wms-muted">Buscar por rua, posição, endereço, lado ou status</label>
                    <input type="text" name="busca" id="busca" class="form-control" value="{{ $busca }}" placeholder="Ex.: PA, PA 059 1, IMPAR">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">
                        <i class="mdi mdi-magnify me-1"></i> Buscar
                    </button>
                </div>
                @if ($busca !== '')
                    <div class="col-auto">
                        <a href="{{ route('wms.posicoes.index') }}" class="btn btn-outline-light">
                            <i class="mdi mdi-filter-remove-outline me-1"></i> Limpar
                        </a>
                    </div>
                @endif
            </form>

            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Bloco</th>
                            <th>Rua</th>
                            <th>Posição</th>
                            <th>Endereço</th>
                            <th>Lado</th>
                            <th>Seq. rota</th>
                            <th>Status</th>
                            <th>Ativo</th>
                            <th>Atualizado em</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($posicoes as $posicao)
                            <tr>
                                <td>{{ $posicao->bloco ?? '-' }}</td>
                                <td class="fw-bold">{{ $posicao->rua }}</td>
                                <td>{{ $posicao->posicao }}</td>
                                <td>{{ $posicao->endereco }}</td>
                                <td>{{ $posicao->lado ?? '-' }}</td>
                                <td>{{ $posicao->sequencia_rota ?? '-' }}</td>
                                <td><span class="badge bg-info">{{ $posicao->status ?? '-' }}</span></td>
                                <td>
                                    <span class="badge {{ $posicao->ativo ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $posicao->ativo ? 'Sim' : 'Não' }}
                                    </span>
                                </td>
                                <td>{{ optional($posicao->updated_at)->format('d/m/Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center wms-muted py-4">Nenhuma posição cadastrada.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $posicoes->links() }}
            </div>
        </div>
    </div>
@endsection
