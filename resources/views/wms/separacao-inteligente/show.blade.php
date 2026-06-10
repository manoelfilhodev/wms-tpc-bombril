@extends('layouts.app')

@section('title', 'Separação Gerada')

@section('content')
    <style>
        .wms-page { color: #f8fafc; }
        .wms-panel { background: rgba(12, 16, 24, .94); border: 1px solid rgba(255,255,255,.10); border-radius: 8px; box-shadow: 0 18px 45px rgba(0,0,0,.28); }
        .wms-page .table { color: #f8fafc; }
        .wms-page .table td, .wms-page .table th { border-color: rgba(255,255,255,.10); vertical-align: middle; }
        .wms-stat { border-radius: 8px; padding: 15px; background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.10); }
        .wms-stat strong { display: block; font-size: 25px; color: #fff; line-height: 1; }
        .wms-stat span { color: #a8b3c7; font-size: 12px; text-transform: uppercase; font-weight: 700; }
        .wms-muted { color: #a8b3c7; }
    </style>

    <div class="wms-page container-fluid px-4 py-3">
        @include('partials.breadcrumb-auto')

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <h3 class="mb-1 fw-bold text-dark">Separação Inteligente - FO {{ $geracao->fo }}</h3>
                <p class="text-muted mb-0 small">Geração #{{ $geracao->id }} criada em {{ optional($geracao->created_at)->format('d/m/Y H:i') }}</p>
            </div>
            <a href="{{ route('wms.separacao-inteligente.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="mdi mdi-arrow-left me-1"></i> Voltar
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="row g-3 mb-3">
            <div class="col-6 col-md"><div class="wms-stat"><strong>{{ $geracao->fo }}</strong><span>FO</span></div></div>
            <div class="col-6 col-md"><div class="wms-stat"><strong>{{ $geracao->total_itens }}</strong><span>Total de itens</span></div></div>
            <div class="col-6 col-md"><div class="wms-stat"><strong>{{ $geracao->total_skus }}</strong><span>Total de SKUs</span></div></div>
            <div class="col-6 col-md"><div class="wms-stat"><strong>{{ $geracao->total_ruas }}</strong><span>Total de ruas</span></div></div>
            <div class="col-6 col-md"><div class="wms-stat"><strong>{{ $geracao->folhas->count() }}</strong><span>Total de folhas</span></div></div>
            <div class="col-6 col-md"><div class="wms-stat"><strong>{{ $geracao->itens_sem_endereco }}</strong><span>Sem endereço</span></div></div>
            <div class="col-6 col-md"><div class="wms-stat"><strong>{{ $geracao->folhas->pluck('separador_numero')->filter()->unique()->count() }}</strong><span>Separadores</span></div></div>
        </div>

        <div class="wms-panel p-3 mb-3">
            <h5 class="text-white mb-3">Folhas</h5>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Folha</th>
                            <th>Separador</th>
                            <th>Rua</th>
                            <th>Curva</th>
                            <th>Total SKUs</th>
                            <th>Total Quantidade</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($geracao->folhas as $folha)
                            <tr>
                                <td>{{ $folha->numero_folha }}</td>
                                <td>{{ $folha->separador_numero ?? '-' }}</td>
                                <td>{{ $folha->rua ?? '-' }}</td>
                                <td>{{ $folha->curva_abc ?? '-' }}</td>
                                <td>{{ $folha->total_skus }}</td>
                                <td>{{ number_format((float) $folha->total_quantidade, 3, ',', '.') }}</td>
                                <td><span class="badge bg-success">{{ $folha->status }}</span></td>
                                <td class="d-flex flex-wrap gap-1">
                                    <a href="{{ route('wms.separacao-inteligente.imprimir', $folha) }}" target="_blank" rel="noopener" class="btn btn-outline-light btn-sm">
                                        <i class="mdi mdi-eye-outline me-1"></i> Visualizar
                                    </a>
                                    <a href="{{ route('wms.separacao-inteligente.imprimir', $folha) }}" target="_blank" rel="noopener" class="btn btn-outline-light btn-sm">
                                        <i class="mdi mdi-printer me-1"></i> Imprimir
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @php
            $inconsistencias = $geracao->itens->filter(fn ($item) => filled($item->observacao));
        @endphp

        @if ($inconsistencias->isNotEmpty())
            <div class="wms-panel p-3">
                <h5 class="text-white mb-3">Inconsistências</h5>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>SKU</th>
                                <th>Descrição</th>
                                <th>Quantidade</th>
                                <th>Observação</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($inconsistencias as $item)
                                <tr>
                                    <td>{{ $item->sku }}</td>
                                    <td>{{ $item->descricao }}</td>
                                    <td>{{ number_format((float) $item->quantidade, 3, ',', '.') }}</td>
                                    <td><span class="badge bg-warning text-dark">{{ $item->observacao }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
@endsection
