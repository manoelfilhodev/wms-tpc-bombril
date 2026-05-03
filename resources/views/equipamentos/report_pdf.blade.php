<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Relatório de Equipamentos</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; }
        th { background-color: #f0f0f0; }
    </style>
</head>
<body>
    <h2>Relatório de Equipamentos</h2>
    <table>
        <thead>
            <tr>
                <th>Tipo</th>
                <th>Modelo</th>
                <th>Status</th>
                <th>Localização</th>
                <th>Responsável</th>
                <th>Data Aquisição</th>
            </tr>
        </thead>
        <tbody>
            @foreach($equipamentos as $eq)
            <tr>
                <td>{{ $eq->tipo }}</td>
                <td>{{ $eq->modelo }}</td>
                <td>{{ $eq->status }}</td>
                <td>{{ $eq->localizacao }}</td>
                <td>{{ $eq->responsavel }}</td>
                <td>{{ $eq->data_aquisicao }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
