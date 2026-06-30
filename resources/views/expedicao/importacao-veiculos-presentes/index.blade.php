@extends('layouts.app')

@section('title', 'Importar Veiculos Presentes')

@section('content')
    @php
        $resumo = session('importacao_veiculos_presentes_resumo');
    @endphp

    <style>
        .vehicle-import-page { color: #f8fafc; }
        .vehicle-import-panel,
        .vehicle-import-summary {
            background: rgba(12, 16, 24, .94);
            border: 1px solid rgba(255, 255, 255, .10);
            border-radius: 8px;
            box-shadow: 0 18px 45px rgba(0, 0, 0, .28);
        }
        .vehicle-muted { color: #a8b3c7; }
        .vehicle-chip {
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
        .vehicle-stat {
            border-radius: 8px;
            padding: 16px;
            background: rgba(255, 255, 255, .05);
            border: 1px solid rgba(255, 255, 255, .10);
        }
        .vehicle-stat strong {
            display: block;
            color: #fff;
            font-size: 26px;
            line-height: 1;
        }
        .vehicle-stat span {
            color: #a8b3c7;
            font-size: 12px;
            text-transform: uppercase;
            font-weight: 700;
        }
        .vehicle-import-page .form-control {
            background: rgba(255, 255, 255, .06);
            border-color: rgba(255, 255, 255, .16);
            color: #fff;
        }
        .vehicle-import-page .form-control:focus {
            background: rgba(255, 255, 255, .08);
            border-color: #667eea;
            color: #fff;
            box-shadow: 0 0 0 .2rem rgba(102, 126, 234, .18);
        }
        .vehicle-import-page .table {
            color: #f8fafc;
        }
        .vehicle-import-page .table td,
        .vehicle-import-page .table th {
            border-color: rgba(255, 255, 255, .10);
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
        .icon-wrapper i { color: #fff !important; }
    </style>

    <div class="vehicle-import-page container-fluid px-4 py-3">
        @include('partials.breadcrumb-auto')

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div class="d-flex align-items-center">
                <div class="icon-wrapper me-3">
                    <i class="mdi mdi-truck-check-outline display-6"></i>
                </div>
                <div>
                    <h3 class="mb-1 fw-bold text-dark">Importar Veiculos Presentes</h3>
                    <p class="text-muted mb-0 small">Atualiza a base relatorio-saida-dts-veiculos-presentes usada no painel de previsibilidade</p>
                </div>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('expedicao.previsibilidade.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="mdi mdi-monitor-dashboard me-1"></i> Painel
                </a>
                <a href="{{ route('expedicao.importacao-programacao.index') }}" class="btn btn-outline-secondary btn-sm">
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
                <div class="vehicle-import-panel p-3 h-100">
                    <h5 class="text-white mb-3">Arquivo XLSX</h5>
                    <form action="{{ route('expedicao.importacao-veiculos-presentes.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label for="arquivo" class="form-label vehicle-muted">Relatorio de saida de DTs / veiculos presentes</label>
                            <input type="file" name="arquivo" id="arquivo" class="form-control" accept=".xlsx" required>
                            <div class="form-text vehicle-muted">
                                Formato aceito: .xlsx até 10 MB. A nova importação substitui a base anterior.
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="mdi mdi-upload me-1"></i> Importar Veiculos Presentes
                        </button>
                    </form>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="vehicle-import-panel p-3 h-100">
                    <h5 class="text-white mb-3">Como a base e usada</h5>
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <span class="vehicle-chip"><i class="mdi mdi-key-outline"></i> Doc Transporte -> FO/DT</span>
                        <span class="vehicle-chip"><i class="mdi mdi-truck-outline"></i> Placa Veiculo</span>
                        <span class="vehicle-chip"><i class="mdi mdi-account-outline"></i> Motorista</span>
                        <span class="vehicle-chip"><i class="mdi mdi-login"></i> Entrada</span>
                        <span class="vehicle-chip"><i class="mdi mdi-logout"></i> Saida</span>
                    </div>
                    <p class="vehicle-muted mb-3">
                        O painel de previsibilidade cruza esta planilha com as programacoes por FO/DT SAP.
                        Quando existe entrada sem saida, a DT aparece como veiculo na planta. Quando existe saida,
                        o sistema considera o veiculo como ja liberado.
                    </p>

                    @if ($arquivoAtual)
                        <div class="vehicle-stat">
                            <span>Arquivo ativo</span>
                            <strong class="fs-5 mt-1">{{ $arquivoAtual['nome'] }}</strong>
                            <div class="vehicle-muted small mt-2">
                                Atualizado em {{ $arquivoAtual['atualizado_em'] }} · {{ $arquivoAtual['tamanho_kb'] }} KB
                            </div>
                        </div>
                    @else
                        <div class="alert alert-warning border-0 mb-0">
                            Nenhum arquivo ativo encontrado para veiculos presentes.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        @if ($resumo)
            <div class="vehicle-import-summary p-3 mt-3">
                <h5 class="text-white mb-3">Resumo da Importacao</h5>
                <div class="row g-3 mb-3">
                    <div class="col-6 col-md">
                        <div class="vehicle-stat"><strong>{{ $resumo['total_lidas'] ?? 0 }}</strong><span>Lidas</span></div>
                    </div>
                    <div class="col-6 col-md">
                        <div class="vehicle-stat"><strong>{{ $resumo['na_planta'] ?? 0 }}</strong><span>Na planta</span></div>
                    </div>
                    <div class="col-6 col-md">
                        <div class="vehicle-stat"><strong>{{ $resumo['saidos'] ?? 0 }}</strong><span>Saidos</span></div>
                    </div>
                    <div class="col-6 col-md">
                        <div class="vehicle-stat"><strong>{{ $resumo['sem_movimento'] ?? 0 }}</strong><span>Sem movimento</span></div>
                    </div>
                </div>

                @if (! empty($resumo['amostra']))
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>FO/DT</th>
                                    <th>Placa</th>
                                    <th>Motorista</th>
                                    <th>Entrada</th>
                                    <th>Saida</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($resumo['amostra'] as $item)
                                    <tr>
                                        <td>{{ $item['fo'] }}</td>
                                        <td>{{ $item['placa'] }}</td>
                                        <td>{{ $item['motorista'] }}</td>
                                        <td>{{ $item['entrada_em'] ?? '-' }}</td>
                                        <td>{{ $item['saida_em'] ?? '-' }}</td>
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
