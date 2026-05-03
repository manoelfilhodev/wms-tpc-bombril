@extends($layout)

@section('content')
<div class="container">
    <h4>Separação do Pedido #{{ $pedido->numero_pedido }}</h4>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('pendencias'))
    <div class="alert alert-warning">
        Alguns SKUs não foram gerados por falta de saldo. 
        <a href="{{ route('separacoes.pendencias') }}" class="btn btn-sm btn-outline-dark ms-2">Gerar Relatório de Pendências</a>
    </div>
@endif

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>SKU</th>
                <th>Quantidade</th>
                <th>Centro</th>
                <th>FO</th>
                <th>Conferido</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pedido->itensSeparacao as $item)
                <tr>
                    <td>{{ strtoupper($item->sku) }}</td>
                    <td>{{ $item->quantidade }}</td>
                    <td>{{ $item->centro }}</td>
                    <td>{{ $item->fo }}</td>
                    <td>
                        @if($item->conferido)
                            <span class="badge bg-success">Sim</span>
                        @else
                            <span class="badge bg-secondary">Não</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
