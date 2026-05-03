@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-4">Selecionar Etiquetas para Reimpressão - FO {{ $fo }}</h4>

    <form action="{{ route('etiquetas.hydra.imprimir') }}" method="POST" target="_blank">
        @csrf
        <div class="row">
            @foreach ($etiquetas as $etiqueta)
                <div class="col-md-4">
                    <div class="card mb-3 p-2 border">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="ids[]" value="{{ $etiqueta->id }}" id="etiqueta{{ $etiqueta->id }}">
                            <label class="form-check-label" for="etiqueta{{ $etiqueta->id }}">
                                Imprimir
                            </label>
                        </div>
                        <div style="font-size: 12px; line-height: 1.4; text-transform: uppercase; font-weight: bold;">
                            <div class="d-flex justify-content-between">
                                <div>{{ $etiqueta->recebedor }}</div>
                                <div>DOCA {{ $etiqueta->doca }}</div>
                            </div>
                            <div class="text-end">{{ $etiqueta->fo }}</div>
                            <div>REMESSA: {{ $etiqueta->remessa }}</div>
                            <div>CLIENTE: {{ $etiqueta->cliente }}</div>
                            <div>CIDADE: {{ $etiqueta->cidade }}</div>
                            <div>UF: {{ $etiqueta->uf }}</div>
                            <div>PRODUTO: {{ $etiqueta->produto }}</div>
                            <div>QTDE PÇS: 1</div>
                            <div>DATA - HORA: {{ \Carbon\Carbon::parse($etiqueta->data_gerada)->format('d/m/Y H:i:s') }}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="text-end mt-4">
            <button type="submit" class="btn btn-primary">
                <i class="mdi mdi-printer me-1"></i> Imprimir Selecionadas
            </button>
        </div>
    </form>
</div>
@endsection
