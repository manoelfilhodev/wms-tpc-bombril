@extends($layout)

@section('content')
<div class="container">
    <h4>Adicionar Item à NF {{ $recebimento->nota_fiscal }}</h4>

    <form action="{{ route('recebimento.itens.store', $recebimento->id) }}" method="POST">
        @csrf

        <div class="mb-3">
            <label>SKU</label>
            <input type="text" name="sku" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Descrição</label>
            <input type="text" name="descricao" class="form-control">
        </div>

        <div class="mb-3">
            <label>Quantidade</label>
            <input type="number" name="quantidade" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Status</label>
            <select name="status" class="form-select" required>
                <option value="pendente">Pendente</option>
                <option value="conferido">Conferido</option>
                <option value="armazenado">Armazenado</option>
            </select>
        </div>

        <button type="submit" class="btn btn-success">Salvar Item</button>
    </form>
</div>
@endsection
