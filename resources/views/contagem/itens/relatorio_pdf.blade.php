<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relat¨®rio de Contagem de Itens</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .header, .footer { text-align: center; }
        .header img { max-height: 50px; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: center; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ public_path('logo.png') }}" alt="Logo">
        <h2>Relat¨®rio de Contagem de Itens</h2>
        <p>Gerado em {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Material</th>
                <th>Quantidade</th>
                <th>Respons¨¢vel</th>
                <th>Data</th>
            </tr>
        </thead>
        <tbody>
            @foreach($contagens as $item)
                <tr>
                    <td>{{ $item->id }}</td>
                    <td>{{ $item->material->codigo_material }} - {{ $item->material->descricao }}</td>
                    <td>{{ $item->quantidade }}</td>
                    <td>{{ $item->usuario->nome ?? 'N/A' }}</td>
                    <td>{{ \Carbon\Carbon::parse($item->data_contagem)->format('d/m/Y H:i') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Respons¨¢vel: {{ auth()->user()->nome }}</p>
    </div>
</body>
</html>
