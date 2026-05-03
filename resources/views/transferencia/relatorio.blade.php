@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-3">Relatório de Transferências</h4>

    <form method="GET" action="{{ route('transferencia.relatorio') }}" class="row g-3 mb-3">
        <div class="col-md-3">
            <label class="form-label">Data Início</label>
            <input type="date" name="data_inicio" class="form-control" value="{{ request('data_inicio') }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">Data Fim</label>
            <input type="date" name="data_fim" class="form-control" value="{{ request('data_fim') }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">SKU</label>
            <input type="text" name="sku" class="form-control" value="{{ request('sku') }}">
        </div>
        <div class="col-md-3 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">Filtrar</button>
        </div>
    </form>

    @if($transferencias->isEmpty())
        <div class="alert alert-info">Nenhum registro encontrado.</div>
    @else
        <table class="table table-sm table-bordered">
            <thead class="table-light">
                <tr>
                    <th>SKU</th>
                    <th>Qtd Programada</th>
                    <th>Qtd Apontada</th>
                    <th>Data</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transferencias as $t)
                    <tr>
                        <td>{{ $t->codigo_material }}</td>
                        <td>{{ $t->quantidade_programada }}</td>
                        <td>{{ $t->quantidade_apontada }}</td>
                        <td>{{ \Carbon\Carbon::parse($t->data_transferencia)->format('d/m/Y') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="d-flex gap-2">
            <a href="{{ route('transferencia.relatorio.pdf', request()->all()) }}" class="btn btn-danger">
                <i class="bi bi-file-earmark-pdf"></i> Exportar PDF
            </a>
            <a href="{{ route('transferencia.relatorio.excel', request()->all()) }}" class="btn btn-success">
                <i class="bi bi-file-earmark-excel"></i> Exportar Excel
            </a>
        </div>
    @endif
</div>
@endsection
