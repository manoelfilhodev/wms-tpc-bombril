@extends('layouts.app')

@section('title', 'Importar SKU x Posições')

@section('content')
    @php
        $resumo = session('wms_sku_posicoes_resumo');
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
                <h3 class="mb-1 fw-bold text-dark">Importar SKU x Posições</h3>
                <p class="text-muted mb-0 small">Cria ou atualiza a amarração entre SKUs e posições já cadastradas</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('wms.sku-posicoes.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="mdi mdi-format-list-bulleted me-1"></i> Vínculos
                </a>
                <a href="{{ route('wms.importacoes.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="mdi mdi-file-upload-outline me-1"></i> Importações WMS
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
            <div class="col-lg-5">
                <div class="wms-panel p-3 h-100">
                    <h5 class="text-white mb-2">Arquivo de posições com SKU</h5>
                    <p class="wms-muted small mb-3">
                        Use a base <code>base-posicoes-pick.xlsx</code> ou uma planilha enxuta com
                        <code>RUA</code>, <code>POSIÇÃO</code> e <code>SKU</code>. Linhas sem SKU serão ignoradas.
                    </p>
                    <form action="{{ route('wms.sku-posicoes.importar.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <label for="arquivo" class="form-label wms-muted">Planilha SKU x posição</label>
                        <input type="file" name="arquivo" id="arquivo" class="form-control mb-3" accept=".xlsx,.xls,.csv" required>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="mdi mdi-upload me-1"></i> Importar vínculos
                        </button>
                    </form>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="wms-panel p-3 h-100">
                    <h5 class="text-white mb-3">Mapeamento</h5>
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <span class="wms-chip"><i class="mdi mdi-barcode"></i> SKU → cadastro mestre</span>
                        <span class="wms-chip"><i class="mdi mdi-map-marker-outline"></i> RUA/POSICAO/POSIÇÃO → posição</span>
                        <span class="wms-chip"><i class="mdi mdi-counter"></i> QTD → qtd. padrão</span>
                    </div>
                    <p class="wms-muted mb-0">
                        A importação usa chave única por <code>sku_id + posicao_id</code>. Ela não gera folhas de separação,
                        não cria SKUs ou posições ausentes e aceita <code>POSIÇÃO</code> como endereço completo quando vier no formato <code>PA 059 1</code>.
                    </p>
                </div>
            </div>
        </div>

        @if ($resumo)
            <div class="wms-summary p-3 mt-3">
                <h5 class="text-white mb-3">Resumo da Importação</h5>
                <div class="row g-3 mb-3">
                    <div class="col-6 col-md-3 col-xl">
                        <div class="wms-stat"><strong>{{ $resumo['total_lido'] ?? 0 }}</strong><span>Total lido</span></div>
                    </div>
                    <div class="col-6 col-md-3 col-xl">
                        <div class="wms-stat"><strong>{{ $resumo['vinculos_criados'] ?? 0 }}</strong><span>Criados</span></div>
                    </div>
                    <div class="col-6 col-md-3 col-xl">
                        <div class="wms-stat"><strong>{{ $resumo['vinculos_atualizados'] ?? 0 }}</strong><span>Atualizados</span></div>
                    </div>
                    <div class="col-6 col-md-3 col-xl">
                        <div class="wms-stat"><strong>{{ $resumo['linhas_ignoradas_sem_sku'] ?? 0 }}</strong><span>Sem SKU</span></div>
                    </div>
                    <div class="col-6 col-md-3 col-xl">
                        <div class="wms-stat"><strong>{{ $resumo['skus_nao_encontrados'] ?? 0 }}</strong><span>SKU ausente</span></div>
                    </div>
                    <div class="col-6 col-md-3 col-xl">
                        <div class="wms-stat"><strong>{{ $resumo['posicoes_nao_encontradas'] ?? 0 }}</strong><span>Posição ausente</span></div>
                    </div>
                    <div class="col-6 col-md-3 col-xl">
                        <div class="wms-stat"><strong>{{ $resumo['erros'] ?? 0 }}</strong><span>Erros</span></div>
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
                                    <th>Ocorrência</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach (array_slice($falhas, 0, 30) as $falha)
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
