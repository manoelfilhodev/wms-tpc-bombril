<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Resumo do Dia - {{ $dataHoje }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 30px;
        }
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #ccc;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header img {
            height: 60px;
        }
        h2 {
            margin: 0;
            font-size: 18px;
            text-align: right;
            flex-grow: 1;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #aaa;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f0f0f0;
        }
        .text-right {
            text-align: right;
        }
        .assinatura {
            margin-top: 60px;
            text-align: center;
        }
        .assinatura p {
            margin: 0;
        }
        .footer {
            position: fixed;
            bottom: 10px;
            left: 30px;
            right: 30px;
            font-size: 10px;
            color: #888;
            text-align: center;
            border-top: 1px solid #ccc;
            padding-top: 5px;
        }
    </style>
</head>
<body>

    <div class="header">
        <img src="{{ public_path('logo.png') }}" alt="Logo Systex">
        <h2>Resumo do Dia – {{ $dataHoje }}</h2>
    </div>

    <table>
        <thead>
            <tr>
                <th>Setor</th>
                <th class="text-right">Quantidade</th>
            </tr>
        </thead>
        <tbody>
            @foreach($dados as $setor => $qtd)
            <tr>
                <td>{{ strtoupper(str_replace('_', ' ', $setor)) }}</td>
                <td class="text-right">{{ $qtd }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="assinatura">
        <p>_______________________________________</p>
        <p>Responsável</p>
    </div>

    <div class="footer">
        Relatório gerado em {{ now()->format('d/m/Y H:i') }} – Systex Sistemas Inteligentes
    </div>

</body>
</html>
