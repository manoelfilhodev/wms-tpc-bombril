@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-4">Relatório de Programação e Produção de Kits</h4>

    <form method="GET" action="{{ route('kit.relatorio') }}" class="mb-4">
    <div class="row g-3 align-items-end">
        <div class="col-md-3">
            <label for="data_inicio" class="form-label">Data Início</label>
            <input type="date" name="data_inicio" class="form-control" value="{{ request('data_inicio') }}">
        </div>

        <div class="col-md-3">
            <label for="data_fim" class="form-label">Data Fim</label>
            <input type="date" name="data_fim" class="form-control" value="{{ request('data_fim') }}">
        </div>

        <div class="col-md-3">
            <label for="sku" class="form-label">SKU</label>
            <input type="text" name="sku" class="form-control" value="{{ request('sku') }}" placeholder="Digite o SKU">
        </div>

        <div class="col-md-3 d-flex gap-2 flex-wrap">
            <button type="submit" class="btn btn-primary w-100">Filtrar</button>
            <a href="{{ route('kit.relatorio.pdf', request()->all()) }}" class="btn btn-danger w-100">Exportar PDF</a>
            <a href="{{ route('kit.relatorio.excel', request()->all()) }}" class="btn btn-success w-100">Exportar Excel</a>
        </div>
    </div>
</form>


    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle text-center">
            <thead class="table-dark">
                <tr>
                    <th>Data Montagem</th>
                    <th>SKU</th>
                    <th>Programado</th>
                    <th>Produzido</th>
                    <th>% Realizado</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($kits as $kit)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($kit->data_montagem)->format('d/m/Y') }}</td>
                        <td>{{ strtoupper($kit->codigo_material) }}</td>
                        <td>{{ $kit->quantidade_programada }}</td>
                        <td>{{ $kit->quantidade_produzida ?? 0 }}</td>
                        <td>
                            @php
                                $realizado = $kit->quantidade_programada > 0
                                    ? round(($kit->quantidade_produzida ?? 0) / $kit->quantidade_programada * 100, 1)
                                    : 0;
                            @endphp
                            {{ $realizado }}%
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">Nenhum registro encontrado no período.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    
</div>
@endsection
