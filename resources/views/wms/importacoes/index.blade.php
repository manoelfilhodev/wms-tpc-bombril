@extends('layouts.app')

@section('title', 'Importações WMS')

@section('content')
    @php
        $resumo = session('wms_importacao_resumo');
        $tipo = session('wms_importacao_tipo');
        $falhas = $resumo['falhas'] ?? [];
    @endphp

    <style>
        .wms-page { color: #f8fafc; }
        .wms-panel,
        .wms-summary {
            background: rgba(12, 16, 24, .94);
            border: 1px solid rgba(255, 255, 255, .10);
            border-radius: 8px;
            box-shadow: 0 18px 45px rgba(0, 0, 0, .28);
        }
        .wms-page .form-control {
            background: rgba(255, 255, 255, .06);
            border-color: rgba(255, 255, 255, .16);
            color: #fff;
        }
        .wms-muted { color: #a8b3c7; }
        .wms-stat {
            border-radius: 8px;
            padding: 16px;
            background: rgba(255, 255, 255, .05);
            border: 1px solid rgba(255, 255, 255, .10);
        }
        .wms-stat strong {
            display: block;
            color: #fff;
            font-size: 26px;
            line-height: 1;
        }
        .wms-stat span {
            color: #a8b3c7;
            font-size: 12px;
            text-transform: uppercase;
            font-weight: 700;
        }
        .wms-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border-radius: 999px;
            border: 1px solid rgba(255, 255, 255, .12);
            color: #dce3ef;
            padding: 8px 12px;
            background: rgba(255, 255, 255, .04);
            font-size: 13px;
        }
        .wms-page .table { color: #f8fafc; }
        .wms-page .table td,
        .wms-page .table th { border-color: rgba(255, 255, 255, .10); }
    </style>

    <div class="wms-page container-fluid px-4 py-3">
        @include('partials.breadcrumb-auto')

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <h3 class="mb-1 fw-bold text-dark">Importações WMS</h3>
                <p class="text-muted mb-0 small">Carga dos cadastros mestres de SKUs e posições de picking</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('wms.skus.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="mdi mdi-package-variant-closed me-1"></i> SKUs
                </a>
                <a href="{{ route('wms.posicoes.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="mdi mdi-map-marker-path me-1"></i> Posições
                </a>
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

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="mdi mdi-alert-circle-outline me-2"></i>{{ $errors->first() }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row g-3">
            <div class="col-lg-6">
                <div class="wms-panel p-3 h-100">
                    <h5 class="text-white mb-2">Base de SKUs</h5>
                    <p class="wms-muted small mb-3">Mapeia ITEM, PESO ITEM [Kg], CLASSE PESO, CLASSE CUBAGEM e CURVA.</p>
                    <form action="{{ route('wms.importacoes.skus') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <label for="arquivo_skus" class="form-label wms-muted">Planilha de SKUs</label>
                        <input type="file" name="arquivo" id="arquivo_skus" class="form-control mb-3" accept=".xlsx,.xls,.csv" required>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="mdi mdi-upload me-1"></i> Importar base de SKUs
                        </button>
                    </form>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="wms-panel p-3 h-100">
                    <h5 class="text-white mb-2">Base de Posições</h5>
                    <p class="wms-muted small mb-3">Mapeia BLOCO, RUA, POSICAO, POSICÃO, LADO e STATUS. SKU/QTD/CURVA são ignorados nesta etapa.</p>
                    <form action="{{ route('wms.importacoes.posicoes') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <label for="arquivo_posicoes" class="form-label wms-muted">Planilha de posições</label>
                        <input type="file" name="arquivo" id="arquivo_posicoes" class="form-control mb-3" accept=".xlsx,.xls,.csv" required>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="mdi mdi-upload me-1"></i> Importar base de posições
                        </button>
                    </form>
                </div>
            </div>
        </div>

        @if ($resumo)
            <div class="wms-summary p-3 mt-3">
                <h5 class="text-white mb-3">Resumo da Importação{{ $tipo ? ' - ' . $tipo : '' }}</h5>
                <div class="row g-3 mb-3">
                    <div class="col-6 col-md">
                        <div class="wms-stat"><strong>{{ $resumo['total_lido'] ?? 0 }}</strong><span>Total lido</span></div>
                    </div>
                    <div class="col-6 col-md">
                        <div class="wms-stat"><strong>{{ $resumo['total_criado'] ?? 0 }}</strong><span>Total criado</span></div>
                    </div>
                    <div class="col-6 col-md">
                        <div class="wms-stat"><strong>{{ $resumo['total_atualizado'] ?? 0 }}</strong><span>Total atualizado</span></div>
                    </div>
                    <div class="col-6 col-md">
                        <div class="wms-stat"><strong>{{ $resumo['total_ignorado'] ?? 0 }}</strong><span>Total ignorado</span></div>
                    </div>
                    <div class="col-6 col-md">
                        <div class="wms-stat"><strong>{{ $resumo['erros_encontrados'] ?? 0 }}</strong><span>Erros encontrados</span></div>
                    </div>
                </div>

                @if (! empty($resumo['colunas_detectadas']))
                    <div class="mb-3">
                        <div class="text-uppercase fw-bold small text-muted mb-2">Colunas detectadas</div>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach ($resumo['colunas_detectadas'] as $coluna)
                                <span class="wms-chip">{{ $coluna }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if (! empty($falhas))
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Linha</th>
                                    <th>Erro</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach (array_slice($falhas, 0, 20) as $falha)
                                    <tr>
                                        <td>{{ $falha['linha'] ?? '-' }}</td>
                                        <td>{{ $falha['erro'] ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        @endif
    </div>
@endsection
