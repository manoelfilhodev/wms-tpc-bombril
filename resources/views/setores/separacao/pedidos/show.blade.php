@extends($layout)

@section('content')
<div class="container">
    <h4>Pedido {{ $pedido->numero_pedido }} - FO: {{ $pedido->itens->first()->fo ?? 'N/D' }}</h4>


    <p><strong>Status:</strong> {{ ucfirst($pedido->status) }}</p>
    <p><strong>Unidade:</strong> {{ $pedido->unidade_id }}</p>

    <h5>Itens do Pedido:</h5>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>SKU</th>
                <th>Qtde</th>
                <th>Centro</th>
                <th>FO</th>
                <th>Conferido</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pedido->itens as $item)
                <tr>
                    <td>{{ $item->sku }}</td>
                    <td>{{ $item->quantidade }}</td>
                    <td>{{ $item->centro }}</td>
                    <td>{{ $item->fo }}</td>
                    <td>{!! $item->conferido ? '✅' : '❌' !!}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
