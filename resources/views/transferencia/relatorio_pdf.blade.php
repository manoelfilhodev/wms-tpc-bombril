<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Relatório de Transferências</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; }
        th { background: #f2f2f2; }
    </style>
</head>
<body>
    <h3>Relatório de Transferências</h3>
    <table>
        <thead>
            <tr>
                <th>SKU</th>
                <th>Qtd Programada</th>
                <th>Qtd Produzida</th>
                <th>Data</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transferencias as $t)
                <tr>
                    <td>{{ $t->codigo_material }}</td>
                    <td>{{ $t->quantidade_programada }}</td>
                    <td>{{ $t->quantidade_produzida }}</td>
                    <td>{{ \Carbon\Carbon::parse($t->data_transferencia)->format('d/m/Y') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
