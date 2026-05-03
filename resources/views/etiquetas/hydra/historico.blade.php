@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-4">Histórico de Etiquetas - Hydra Metais</h4>

    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-3">
            <input type="text" name="fo" value="{{ request('fo') }}" class="form-control" placeholder="FO">
        </div>
        <div class="col-md-3">
            <input type="text" name="produto" value="{{ request('produto') }}" class="form-control" placeholder="Produto">
        </div>
        <div class="col-md-3">
            <input type="text" name="cliente" value="{{ request('cliente') }}" class="form-control" placeholder="Cliente">
        </div>
        <div class="col-md-3">
            <input type="date" name="data" value="{{ request('data') }}" class="form-control">
        </div>
        <div class="col-md-12 text-end">
            <button type="submit" class="btn btn-primary">Filtrar</button>
        </div>
    </form>

    <form action="{{ route('etiquetas.hydra.imprimir') }}" method="POST" target="_blank">
    @csrf

    <div class="text-start mb-2">
        <button type="submit" class="btn btn-primary">
            <i class="mdi mdi-printer me-1"></i> Imprimir Selecionadas
        </button>
    </div>

    <table class="table table-striped">
        <thead>
            <tr>
                <th><input type="checkbox" id="checkAll"></th>
                <th>Data</th>
                <th>FO</th>
                <th>Produto</th>
                <th>Cliente</th>
                <th>Cidade</th>
                <th>UF</th>
                <th>Qtd</th>
                
            </tr>
        </thead>
        <tbody>
            @foreach($etiquetas as $etiqueta)
                <tr>
                    <td><input type="checkbox" name="ids[]" value="{{ $etiqueta->id }}"></td>
                    <td>{{ \Carbon\Carbon::parse($etiqueta->data_gerada)->format('d/m/Y H:i') }}</td>
                    <td>{{ $etiqueta->fo }}</td>
                    <td>{{ $etiqueta->produto }}</td>
                    <td>{{ $etiqueta->cliente }}</td>
                    <td>{{ $etiqueta->cidade }}</td>
                    <td>{{ $etiqueta->uf }}</td>
                    <td>{{ $etiqueta->qtd }}</td>
                    
                </tr>
            @endforeach
        </tbody>
    </table>
</form>


     <div class="mt-3">
        {{ $etiquetas->withQueryString()->links('pagination::bootstrap-5') }}
    </div>
</div>
<script>
    document.getElementById('checkAll').addEventListener('change', function () {
        const checkboxes = document.querySelectorAll('input[name="ids[]"]');
        checkboxes.forEach(cb => cb.checked = this.checked);
    });
</script>

@endsection
