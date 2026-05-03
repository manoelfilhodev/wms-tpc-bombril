@extends('layouts.app')

@section('content')
<div class="container">
    <h4>Relatório de Pendências de Separação</h4>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Pedido</th>
                <th>FO</th>
                <th>SKU</th>
                <th>Quantidade</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pendencias as $p)
                <tr>
                    <td>{{ $p['pedido'] }}</td>
                    <td>{{ $p['fo'] }}</td>
                    <td>{{ $p['sku'] }}</td>
                    <td>{{ $p['quantidade'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <a href="#" onclick="window.print()" class="btn btn-primary">Imprimir</a>
</div>
@endsection
