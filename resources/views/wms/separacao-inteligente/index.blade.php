@extends('layouts.app')

@section('title', 'Gerar Separação')

@section('content')
    <style>
        .wms-page { color: #f8fafc; }
        .wms-panel { background: rgba(12, 16, 24, .94); border: 1px solid rgba(255,255,255,.10); border-radius: 8px; box-shadow: 0 18px 45px rgba(0,0,0,.28); }
        .wms-page .form-control, .wms-page .form-select { background: rgba(255,255,255,.06); border-color: rgba(255,255,255,.16); color: #fff; }
        .wms-page .form-select option { color: #111827; }
        .wms-page .table { color: #f8fafc; }
        .wms-page .table td, .wms-page .table th { border-color: rgba(255,255,255,.10); vertical-align: middle; }
        .wms-muted { color: #a8b3c7; }
    </style>

    <div class="wms-page container-fluid px-4 py-3">
        @include('partials.breadcrumb-auto')

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <h3 class="mb-1 fw-bold text-dark">Gerar Separação Inteligente</h3>
                <p class="text-muted mb-0 small">Cruza DT, SKU, posição e critérios operacionais sem alterar o fluxo da demanda</p>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        <div class="row g-3">
            <div class="col-lg-5">
                <div class="wms-panel p-3 h-100">
                    <h5 class="text-white mb-3">Configuração</h5>
                    <form method="POST" action="{{ route('wms.separacao-inteligente.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="fo" class="form-label wms-muted">DT / FO</label>
                            <input
                                type="text"
                                name="fo"
                                id="fo"
                                class="form-control"
                                value="{{ old('fo') }}"
                                list="demandas_disponiveis"
                                placeholder="Digite ou cole o número da DT"
                                autocomplete="off"
                                required
                            >
                            <datalist id="demandas_disponiveis">
                                @foreach ($demandas as $demanda)
                                    <option value="{{ $demanda->fo }}">
                                        {{ (int) $demanda->total_skus_picking }} SKUs - {{ number_format((float) $demanda->total_caixas_picking, 0, ',', '.') }} caixas - {{ $demanda->cliente ?? 'Sem cliente' }}
                                    </option>
                                @endforeach
                            </datalist>
                            <div class="form-text wms-muted">
                                Apenas DTs em A_SEPARAR e ainda não geradas aparecem nas sugestões.
                            </div>
                        </div>

                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label wms-muted" for="criterio_agrupamento">Agrupamento</label>
                                <select name="criterio_agrupamento" id="criterio_agrupamento" class="form-select">
                                    <option value="folha_unica">Folha única</option>
                                    <option value="rua">Por rua</option>
                                    <option value="curva_abc">Por curva ABC</option>
                                    <option value="sku">Por SKU</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label wms-muted" for="criterio_ordenacao">Ordenação</label>
                                <select name="criterio_ordenacao" id="criterio_ordenacao" class="form-select">
                                    <option value="inteligente_recomendada" selected>Separação Inteligente Recomendada</option>
                                    <option value="inteligente">Inteligente</option>
                                    <option value="sequencia_rota">Sequência de rota</option>
                                    <option value="endereco">Endereço</option>
                                    <option value="curva_abc">Curva ABC</option>
                                    <option value="sku">SKU</option>
                                </select>
                            </div>
                        </div>

                        <div class="row g-2 mt-1">
                            <div class="col-md-4">
                                <label class="form-label wms-muted" for="equalizacao">Equalização</label>
                                <select name="equalizacao" id="equalizacao" class="form-select">
                                    <option value="0">Não</option>
                                    <option value="1">Sim</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label wms-muted" for="criterio_equalizacao">Critério</label>
                                <select name="criterio_equalizacao" id="criterio_equalizacao" class="form-select">
                                    <option value="inteligente">Inteligente</option>
                                    <option value="skus">SKUs</option>
                                    <option value="quantidade">Quantidade</option>
                                    <option value="ruas">Ruas</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label wms-muted" for="quantidade_separadores">Separadores</label>
                                <input type="number" min="1" max="50" name="quantidade_separadores" id="quantidade_separadores" class="form-control" value="{{ old('quantidade_separadores', 1) }}">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mt-3">
                            <i class="mdi mdi-auto-fix me-1"></i> Gerar Separação
                        </button>
                    </form>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="wms-panel p-3 h-100">
                    <h5 class="text-white mb-3">DTs disponíveis</h5>
                    <div class="table-responsive mb-4">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>DT</th>
                                    <th>Cliente</th>
                                    <th>SKUs</th>
                                    <th>Caixas</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($demandas->sortByDesc('total_caixas_picking')->take(12) as $demanda)
                                    <tr>
                                        <td class="fw-bold">{{ $demanda->fo }}</td>
                                        <td>{{ $demanda->cliente ?? '-' }}</td>
                                        <td>{{ (int) $demanda->total_skus_picking }}</td>
                                        <td>{{ number_format((float) $demanda->total_caixas_picking, 0, ',', '.') }}</td>
                                        <td><span class="badge bg-warning text-dark">{{ $demanda->status }}</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center wms-muted py-4">Nenhuma DT disponível para geração.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <h5 class="text-white mb-3">Últimas gerações</h5>
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>FO</th>
                                    <th>Folhas</th>
                                    <th>Itens</th>
                                    <th>Sem endereço</th>
                                    <th>Gerado em</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($geracoes as $geracao)
                                    <tr>
                                        <td>{{ $geracao->fo }}</td>
                                        <td>{{ $geracao->folhas_count }}</td>
                                        <td>{{ $geracao->total_itens }}</td>
                                        <td>{{ $geracao->itens_sem_endereco }}</td>
                                        <td>{{ optional($geracao->created_at)->format('d/m/Y H:i') }}</td>
                                        <td><a href="{{ route('wms.separacao-inteligente.show', $geracao) }}" class="btn btn-outline-light btn-sm">Visualizar</a></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center wms-muted py-4">Nenhuma geração registrada.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
