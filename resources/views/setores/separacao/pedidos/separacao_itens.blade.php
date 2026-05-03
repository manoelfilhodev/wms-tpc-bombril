@extends($layout)
@section('content')
<div class="container">
    <h4>Separação do Pedido #{{ $pedido->numero_pedido }}</h4>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-striped">
        <thead>
            <tr>
                <th>SKU</th>
                <th>Qtd</th>
                <th>Centro</th>
                <th>FO</th>
                <th>Conferido</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pedido->itensSeparacao as $item)
                <tr>
                    <td>{{ $item->sku }}</td>
                    <td>{{ $item->quantidade }}</td>
                    <td>{{ $item->centro }}</td>
                    <td>{{ $item->fo }}</td>
                    <td>{!! $item->conferido ? '<span class=\"badge bg-success\">Sim</span>' : '<span class=\"badge bg-secondary\">Não</span>' !!}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
