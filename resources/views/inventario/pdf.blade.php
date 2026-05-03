<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Inventário #{{ $id }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 4px; text-align: left; }
        th { background-color: #eee; }
    </style>
</head>
<body>
    <h3>Resumo do Inventário #{{ $id }}</h3>

    <p><strong>Total de SKUs:</strong> {{ $total }}<br>
    <strong>Contados:</strong> {{ $contados }}<br>
    <strong>Faltando:</strong> {{ $faltantes }}</p>

    <table>
        <thead>
    <tr>
        <th>SKU</th>
        <th>Descrição</th>
        <th>Posição</th>
        <th>Sistema</th>
        <th>Físico</th>
        <th>Tipo</th>
        <th>Ajuste?</th>
        <th>Usuário</th>
        <th>Data</th>
    </tr>
</thead>
<tbody>
    @foreach($itens as $item)
        @php
            $usuario = optional(DB::table('_tb_usuarios')->where('id_user', $item->contado_por)->first())->nome;
        @endphp
        <tr>
            <td>{{ $item->sku }}</td>
            <td>{{ $item->descricao }}</td>
            <td>{{ $item->posicao }}</td>
            <td>{{ $item->quantidade_sistema }}</td>
            <td>{{ $item->quantidade_fisica }}</td>
            <td>{{ $item->tipo_ajuste }}</td>
            <td>{{ $item->necessita_ajuste ? 'Sim' : 'Não' }}</td>
            <td>{{ $usuario ?? '-' }}</td>
            <td>
    @if($item->updated_at)
        {{ \Carbon\Carbon::parse($item->updated_at)->format('d/m/Y H:i') }}
    @else
        -
    @endif
</td>

        </tr>
    @endforeach
</tbody>

    </table>

    <p style="margin-top: 30px;">Relatório gerado em {{ now()->format('d/m/Y H:i') }}</p>
</body>
</html>
