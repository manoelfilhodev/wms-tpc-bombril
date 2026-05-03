@extends($layout)

@section('content')
<div class="container">
    <h4 class="mb-4">Conferência de Recebimento</h4>
    <form method="POST" action="{{ route('recebimento.conferir') }}">
        @csrf
        <div class="row mb-3">
            <div class="col">
                <input type="text" name="nota" class="form-control" placeholder="Nº da Nota ou Pré-Nota" required>
            </div>
            <div class="col">
                <input type="text" name="fornecedor" class="form-control" placeholder="Fornecedor">
            </div>
        </div>
        <div class="row mb-3">
            <div class="col">
                <input type="text" name="produto" class="form-control" placeholder="SKU / Código de Barras" autofocus required>
            </div>
            <div class="col">
                <input type="number" name="quantidade" class="form-control" placeholder="Qtd. Conferida" required>
            </div>
        </div>
        <div class="mb-3">
            <textarea name="observacoes" class="form-control" placeholder="Observações"></textarea>
        </div>
        <button type="submit" class="btn btn-success">Conferir Item</button>
    </form>
</div>
@endsection