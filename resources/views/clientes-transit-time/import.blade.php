@extends('layouts.app')

@section('title', 'Importar Transit Time por Cliente')

@section('content')
@php
    $resumo = session('clientes_transit_time_importacao_resumo');
    $falhas = $resumo['falhas'] ?? [];
@endphp

<div class="container-fluid px-4 py-3 transit-page">
    @include('partials.breadcrumb-auto')

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div class="d-flex align-items-center">
            <div class="icon-wrapper me-3">
                <i class="mdi mdi-file-upload-outline display-6"></i>
            </div>
            <div>
                <h3 class="mb-1 fw-bold text-dark">Importar Transit Time por Cliente</h3>
                <p class="text-muted mb-0 small">Importação inicial da base fixa sem alterar a regra operacional de DTs</p>
            </div>
        </div>
        <a href="{{ route('clientes-transit-time.index') }}" class="btn btn-outline-secondary">
            <i class="mdi mdi-arrow-left me-1"></i> Voltar
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="mdi mdi-check-circle-outline me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="mdi mdi-alert-circle-outline me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="mdi mdi-alert-circle-outline me-2"></i>{{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    @endif

    <div class="row g-3">
        <div class="col-lg-5">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <h5 class="mb-3">Arquivo da base</h5>
                    <form action="{{ route('clientes-transit-time.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="arquivo" class="form-label">Planilha CSV/Excel</label>
                            <input type="file" name="arquivo" id="arquivo" class="form-control" accept=".xlsx,.xls,.csv" required>
                            <div class="form-text">Formatos aceitos: .xlsx, .xls e .csv até 10 MB.</div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="mdi mdi-upload me-1"></i> Importar Base
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <h5 class="mb-3">Colunas esperadas</h5>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <tbody>
                                <tr><td><code>Cliente</code></td><td>Obrigatório; usado como código/ID do cliente</td></tr>
                                <tr><td><code>Zona Partida</code></td><td>Opcional; preservada para regra futura</td></tr>
                                <tr><td><code>Cidade Destino</code>, <code>UF</code></td><td>Destino do cliente</td></tr>
                                <tr><td><code>Zona Transporte</code></td><td>Opcional; preservada para regra futura</td></tr>
                                <tr><td><code>CICLO INTE</code></td><td>Opcional; preservado para regra futura</td></tr>
                                <tr><td><code>DIAS_CARGA_PALETE_FECHADO</code></td><td>Obrigatório, inteiro</td></tr>
                                <tr><td><code>DIAS_CARGA_FRACIONADO</code></td><td>Obrigatório, inteiro</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="alert alert-info mt-3 mb-0 small">
                        Esta importação cria ou atualiza registros pelo código do cliente. Ela não consulta serviços externos e não altera o cálculo atual das DTs.
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($resumo)
        <div class="card shadow-sm border-0 mt-3">
            <div class="card-body">
                <h5 class="mb-3">Resumo da Importação</h5>
                <div class="row g-3 mb-3">
                    <div class="col-6 col-md">
                        <div class="border rounded p-3"><strong class="d-block fs-4">{{ $resumo['total_lidas'] ?? 0 }}</strong><span class="text-muted small">Lidas</span></div>
                    </div>
                    <div class="col-6 col-md">
                        <div class="border rounded p-3"><strong class="d-block fs-4">{{ $resumo['criadas'] ?? 0 }}</strong><span class="text-muted small">Criadas</span></div>
                    </div>
                    <div class="col-6 col-md">
                        <div class="border rounded p-3"><strong class="d-block fs-4">{{ $resumo['atualizadas'] ?? 0 }}</strong><span class="text-muted small">Atualizadas</span></div>
                    </div>
                    <div class="col-6 col-md">
                        <div class="border rounded p-3"><strong class="d-block fs-4">{{ $resumo['ignoradas'] ?? 0 }}</strong><span class="text-muted small">Ignoradas</span></div>
                    </div>
                    <div class="col-6 col-md">
                        <div class="border rounded p-3"><strong class="d-block fs-4">{{ $resumo['erros'] ?? 0 }}</strong><span class="text-muted small">Erros</span></div>
                    </div>
                </div>

                @if (! empty($falhas))
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Linha</th>
                                    <th>Erro</th>
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
        </div>
    @endif
</div>

@include('clientes-transit-time._style')
@endsection
