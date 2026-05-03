<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Relatório de Armazenagem</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo {
            width: 120px;
            margin-bottom: 5px;
        }
        .title {
            font-size: 16px;
            font-weight: bold;
            margin-top: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            table-layout: fixed;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 6px;
            text-align: left;
            vertical-align: top;
            word-break: break-word;
            white-space: normal;
        }
        th {
            background-color: #f0f0f0;
        }
        .footer {
            margin-top: 25px;
            text-align: right;
            font-size: 10px;
            color: #888;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ public_path('images/logo.png') }}" class="logo">
        <div class="title">Relatório de Armazenagem</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Data</th>
                <th>Usuário</th>
                <th>Unidade</th>
                <th>SKU</th>
                <th>Posição</th>
                <th>Quantidade</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($dados as $item)
                <tr>
                    <td>{{ date('d/m/Y', strtotime($item->data_armazenagem)) }}</td>
                    <td>{{ mb_strtoupper($item->usuario_nome) }}</td>
                    <td>{{ $item->unidade_nome }}</td>
                    <td>{{ mb_strtoupper($item->sku) }}</td>
                    <td>{{ mb_strtoupper($item->endereco) }}</td>
                    <td>{{ $item->quantidade }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Gerado em {{ date('d/m/Y H:i') }}
    </div>
</body>
</html>
