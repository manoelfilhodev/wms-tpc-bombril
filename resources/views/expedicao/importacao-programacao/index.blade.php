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

        .prog-import-panel,
        .prog-import-summary {
            background: rgba(12, 16, 24, .94);
            border: 1px solid rgba(255, 255, 255, .10);
            box-shadow: 0 18px 45px rgba(0, 0, 0, .28);
        }

        .prog-import-panel,
        .prog-import-summary {
            border-radius: 8px;
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
            border-color: #667eea;
            color: #fff;
            box-shadow: 0 0 0 .2rem rgba(102, 126, 234, .18);
        }

        .prog-import-page .table {
            color: #f8fafc;
        }

        .prog-import-page .table td,
        .prog-import-page .table th {
            border-color: rgba(255, 255, 255, .10);
        }

        .prog-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .prog-actions .btn {
            font-weight: 800;
        }

        .icon-wrapper {
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(102, 126, 234, .3);
        }

        .icon-wrapper i {
            color: #fff !important;
        }
    </style>

    <div class="prog-import-page container-fluid px-4 py-3">
        @include('partials.breadcrumb-auto')

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div class="d-flex align-items-center">
                <div class="icon-wrapper me-3">
                    <i class="mdi mdi-file-upload-outline display-6"></i>
                </div>
                <div>
                    <h3 class="mb-1 fw-bold text-dark">Importar Programação</h3>
                    <p class="text-muted mb-0 small">Atualiza a base PROG por FO/DT SAP sem apagar dados existentes</p>
                </div>
            </div>

            <div class="prog-actions">
                <a href="{{ route('expedicao.apontamentos-operacionais.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="mdi mdi-timer-edit-outline me-1"></i> Apontar Expedição
                </a>

                <a href="{{ route('expedicao.previsibilidade.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="mdi mdi-monitor-dashboard me-1"></i> Painel
                </a>

                <a href="{{ route('expedicao.importacao-programacao.index') }}" class="btn btn-primary btn-sm">
                    <i class="mdi mdi-file-upload-outline me-1"></i> Importar PROG
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

                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label for="tipo_demanda" class="form-label prog-muted">Tipo de demanda</label>
                                <select name="tipo_demanda" id="tipo_demanda" class="form-select">
                                    <option value="PROGRAMADA" @selected(old('tipo_demanda', 'PROGRAMADA') === 'PROGRAMADA')>Programada</option>
                                    <option value="OPORTUNIDADE" @selected(old('tipo_demanda') === 'OPORTUNIDADE')>Oportunidade</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="origem_demanda" class="form-label prog-muted">Origem</label>
                                <select name="origem_demanda" id="origem_demanda" class="form-select">
                                    <option value="PLANILHA_MANHA" @selected(old('origem_demanda', 'PLANILHA_MANHA') === 'PLANILHA_MANHA')>Planilha manhã</option>
                                    <option value="IMPORTACAO_OPORTUNIDADE" @selected(old('origem_demanda') === 'IMPORTACAO_OPORTUNIDADE')>Importação oportunidade</option>
                                    <option value="INCLUSAO_MANUAL" @selected(old('origem_demanda') === 'INCLUSAO_MANUAL')>Inclusão manual</option>
                                </select>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
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
                        <div class="prog-stat"><strong>{{ $resumo['bloqueadas_programadas'] ?? 0 }}</strong><span>Já programadas</span></div>
                    </div>
                    <div class="col-6 col-md">
                        <div class="prog-stat"><strong>{{ $resumo['erros'] ?? 0 }}</strong><span>Erros</span></div>
                    </div>
                </div>

                @if (($resumo['bloqueadas_programadas'] ?? 0) > 0)
                    <div class="alert alert-warning border-0 small mb-3">
                        {{ $resumo['bloqueadas_programadas'] }} FO(s) já estavam como programadas e não foram reclassificadas como oportunidade.
                    </div>
                @endif

                @if (! empty($resumo['colunas_detectadas']))
                    <div class="mb-3">
                        <div class="text-uppercase fw-bold small text-muted mb-2">Colunas detectadas</div>
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
