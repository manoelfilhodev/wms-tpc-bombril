@extends('layouts.app')

@section('title', 'Importar Programação')

@section('content')
    @php
        $resumo = session('importacao_programacao_resumo');
        $falhas = $resumo['falhas'] ?? [];
    @endphp

    <style>
        .prog-import-page {
            color: #f8fafc;
        }

        .prog-import-hero,
        .prog-import-panel,
        .prog-import-summary {
            background: rgba(12, 16, 24, .94);
            border: 1px solid rgba(255, 255, 255, .10);
            box-shadow: 0 18px 45px rgba(0, 0, 0, .28);
        }

        .prog-import-hero {
            border-radius: 8px;
            padding: 22px;
        }

        .prog-import-panel,
        .prog-import-summary {
            border-radius: 8px;
        }

        .prog-kicker {
            color: #ef4444;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .prog-title {
            color: #fff;
            font-weight: 800;
            letter-spacing: 0;
        }

        .prog-muted {
            color: #a8b3c7;
        }

        .prog-chip {
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

        .prog-stat {
            border-radius: 8px;
            padding: 16px;
            background: rgba(255, 255, 255, .05);
            border: 1px solid rgba(255, 255, 255, .10);
        }

        .prog-stat strong {
            display: block;
            color: #fff;
            font-size: 26px;
            line-height: 1;
        }

        .prog-stat span {
            color: #a8b3c7;
            font-size: 12px;
            text-transform: uppercase;
            font-weight: 700;
        }

        .prog-import-page .form-control {
            background: rgba(255, 255, 255, .06);
            border-color: rgba(255, 255, 255, .16);
            color: #fff;
        }

        .prog-import-page .form-control:focus {
            background: rgba(255, 255, 255, .08);
            border-color: #ef4444;
            color: #fff;
            box-shadow: 0 0 0 .2rem rgba(239, 68, 68, .18);
        }

        .prog-import-page .table {
            color: #f8fafc;
        }

        .prog-import-page .table td,
        .prog-import-page .table th {
            border-color: rgba(255, 255, 255, .10);
        }
    </style>

    <div class="prog-import-page">
        @include('partials.breadcrumb-auto')

        <div class="prog-import-hero mb-3">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                <div>
                    <div class="prog-kicker mb-2">Inteligência Operacional Preditiva</div>
                    <h2 class="prog-title mb-2">Importar Programação da Expedição</h2>
                    <p class="prog-muted mb-0">
                        Atualiza a base PROG por FO/DT SAP sem apagar dados existentes e registra falhas por linha.
                    </p>
                </div>
                <a href="{{ route('expedicao.previsibilidade.index') }}" class="btn btn-outline-light btn-sm">
                    <i class="mdi mdi-arrow-left me-1"></i> Previsibilidade
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
                <div class="prog-import-panel p-3 h-100">
                    <h5 class="text-white mb-3">Arquivo PROG</h5>
                    <form action="{{ route('expedicao.importacao-programacao.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label for="arquivo" class="form-label prog-muted">Planilha de programação</label>
                            <input type="file" name="arquivo" id="arquivo" class="form-control" accept=".xlsx,.xls,.csv,.xlsb" required>
                            <div class="form-text prog-muted">
                                Formatos aceitos: .xlsx, .xls, .csv e .xlsb até 10 MB.
                            </div>
                        </div>

                        <button type="submit" class="btn btn-danger w-100">
                            <i class="mdi mdi-upload me-1"></i> Importar Programação
                        </button>
                    </form>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="prog-import-panel p-3 h-100">
                    <h5 class="text-white mb-3">Mapeamento Operacional</h5>
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <span class="prog-chip"><i class="mdi mdi-key-outline"></i> Doc. Transporte → FO</span>
                        <span class="prog-chip"><i class="mdi mdi-calendar-clock"></i> Agenda → entrega</span>
                        <span class="prog-chip"><i class="mdi mdi-map-marker-outline"></i> Cidade/UF → destino</span>
                        <span class="prog-chip"><i class="mdi mdi-timeline-clock-outline"></i> Marcos → demanda</span>
                    </div>
                    <p class="prog-muted mb-2">
                        A importação usa <strong class="text-white">updateOrCreate por FO</strong> em
                        <code>_tb_expedicao_programacoes</code>. Valores vazios não sobrescrevem campos existentes.
                    </p>
                    <p class="prog-muted mb-0">
                        Para arquivos <code>.xlsb</code>, caso o ambiente não tenha parser compatível, salve a aba
                        <strong class="text-white">PROG</strong> como <code>.xlsx</code> ou <code>.csv</code> e envie novamente.
                    </p>
                </div>
            </div>
        </div>

        @if ($resumo)
            <div class="prog-import-summary p-3 mt-3">
                <h5 class="text-white mb-3">Resumo da Importação</h5>
                <div class="row g-3 mb-3">
                    <div class="col-6 col-md">
                        <div class="prog-stat"><strong>{{ $resumo['total_lidas'] ?? 0 }}</strong><span>Lidas</span></div>
                    </div>
                    <div class="col-6 col-md">
                        <div class="prog-stat"><strong>{{ $resumo['criadas'] ?? 0 }}</strong><span>Criadas</span></div>
                    </div>
                    <div class="col-6 col-md">
                        <div class="prog-stat"><strong>{{ $resumo['atualizadas'] ?? 0 }}</strong><span>Atualizadas</span></div>
                    </div>
                    <div class="col-6 col-md">
                        <div class="prog-stat"><strong>{{ $resumo['ignoradas'] ?? 0 }}</strong><span>Ignoradas</span></div>
                    </div>
                    <div class="col-6 col-md">
                        <div class="prog-stat"><strong>{{ $resumo['erros'] ?? 0 }}</strong><span>Erros</span></div>
                    </div>
                </div>

                @if (! empty($resumo['colunas_detectadas']))
                    <div class="mb-3">
                        <div class="prog-kicker mb-2">Colunas detectadas</div>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach (array_slice($resumo['colunas_detectadas'], 0, 28) as $coluna)
                                <span class="prog-chip">{{ $coluna }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($falhas)
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 110px;">Linha</th>
                                    <th>Falha</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach (array_slice($falhas, 0, 20) as $falha)
                                    <tr>
                                        <td>{{ $falha['linha'] }}</td>
                                        <td>{{ $falha['erro'] }}</td>
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
