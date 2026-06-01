<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>{{ $titulo }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #111827;
            font-size: 9px;
            margin: 18px;
        }

        h1 {
            font-size: 18px;
            margin: 0 0 4px;
        }

        .meta {
            color: #4b5563;
            margin-bottom: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #1f2937;
            color: #fff;
            text-align: left;
            padding: 6px 5px;
            font-size: 8px;
        }

        td {
            border-bottom: 1px solid #e5e7eb;
            padding: 5px;
            vertical-align: top;
        }

        tr:nth-child(even) td {
            background: #f9fafb;
        }

        .empty {
            border: 1px solid #e5e7eb;
            padding: 16px;
            text-align: center;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <h1>{{ $titulo }}</h1>
    <div class="meta">
        Período: {{ $periodo }} |
        Demanda: {{ $tipoDemanda }} |
        Gerado em: {{ $geradoEm }}
    </div>

    @if($rows->isEmpty())
        <div class="empty">Nenhum registro encontrado para os filtros selecionados.</div>
    @else
        <table>
            <thead>
                <tr>
                    @foreach($headings as $heading)
                        <th>{{ $heading }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $row)
                    <tr>
                        @foreach($headings as $heading)
                            <td>{{ $row[$heading] ?? '-' }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>
