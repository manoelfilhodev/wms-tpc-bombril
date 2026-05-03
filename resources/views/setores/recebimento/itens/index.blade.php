@extends($layout)
@section('content')
<div class="container">
    <h4>Itens do Recebimento - NF {{ $recebimento->nota_fiscal }}</h4>

    <a href="{{ route('recebimento.itens.create', $recebimento->id) }}" class="btn btn-primary float-end mb-3">Novo Item</a>

    @if(session('success'))
        <div class="alert alert-success mt-3">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>SKU</th>
                <th>Descrição</th>
                <th>Quantidade</th>
                <th>Status</th>
                <th>Usuário</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($itens as $item)
            <tr>
                <td>{{ $item->sku }}</td>
                <td>{{ $item->descricao }}</td>
                <td>{{ $item->quantidade }}</td>
                <td><span class="badge bg-info">{{ ucfirst($item->status) }}</span></td>
                <td>{{ $item->usuario->nome ?? '---' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
