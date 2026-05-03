<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Logs</title>
    <style>
    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 11px;
        margin: 0;
        padding: 20px;
    }
    .header {
        text-align: center;
        margin-bottom: 15px;
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

    td:nth-child(4), /* Dados */
    td:nth-child(6)  /* Navegador */ {
        max-width: 180px;
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
        <img src="{{ public_path('images/logo.png') }}" class="logo" alt="Logo Systex">
        <div class="title">Relatório de Logs de Usuários</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Usuário</th>
                <th>Unidade</th>
                <th>Ação</th>
                <th>Dados</th>
                <th>IP</th>
                <th>Navegador</th>
                <th>Data</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($logs as $log)
                <tr>
                    <td>{{ $log->usuario->nome ?? '---' }}</td>
                    <td>{{ $log->unidade->nome ?? '---' }}</td>
                    <td>{{ $log->acao }}</td>
                    <td>{{ $log->dados }}</td>
                    <td>{{ $log->ip_address }}</td>
                    <td>{{ $log->navegador }}</td>
                    <td>{{ \Carbon\Carbon::parse($log->created_at)->format('d/m/Y H:i') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Gerado em {{ date('d/m/Y H:i') }}
    </div>

</body>
</html>
