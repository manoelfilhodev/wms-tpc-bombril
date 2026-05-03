@extends($layout)

@section('content')
<div class="container">
    <h4>Criar Novo Pedido com Lista de Itens</h4>

    <form method="POST" action="{{ route('pedidos.store') }}">
        @csrf
        <div class="form-group">
            <label>Cole aqui os dados do pedido (CENTRO FO SKU QTDE):</label>
            <textarea name="itens_texto" rows="12" class="form-control" placeholder="D088 6100536850 KP.470.17 7&#10;D088 6100536850 KP.470.17 3" required></textarea>
        </div>
        <button type="submit" class="btn btn-success mt-3">Salvar Pedido</button>
    </form>
</div>
@endsection
