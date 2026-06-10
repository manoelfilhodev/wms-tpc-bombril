<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <title>Folha {{ $folha->numero_folha }} - FO {{ $folha->geracao->fo }}</title>
    <style>
        body { font-family: Arial, sans-serif; color: #111; font-size: 12px; }
        h1 { margin: 0 0 4px; font-size: 20px; }
        .meta { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; margin: 14px 0; }
        .box { border: 1px solid #999; padding: 8px; }
        .box span { display: block; font-size: 10px; text-transform: uppercase; color: #555; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #999; padding: 6px; text-align: left; }
        th { background: #eee; }
        .warn { font-weight: bold; color: #9a3412; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <button class="no-print" onclick="window.print()">Imprimir</button>

    <h1>Folha de Separação</h1>
    <div>FO {{ $folha->geracao->fo }} - {{ now()->format('d/m/Y H:i') }}</div>

    <div class="meta">
        <div class="box"><span>Folha</span>{{ $folha->numero_folha }}</div>
        <div class="box"><span>Separador</span>{{ $folha->separador_numero ?? '-' }}</div>
        <div class="box"><span>Rua</span>{{ $folha->rua ?? '-' }}</div>
        <div class="box"><span>Curva</span>{{ $folha->curva_abc ?? '-' }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Ordem</th>
                <th>SKU</th>
                <th>Descrição</th>
                <th>Quantidade</th>
                <th>Curva</th>
                <th>Endereço</th>
                <th>Obs.</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($folha->itens as $item)
                <tr>
                    <td>{{ $item->ordem_separacao }}</td>
                    <td>{{ $item->sku }}</td>
                    <td>{{ $item->descricao }}</td>
                    <td>{{ number_format((float) $item->quantidade, 3, ',', '.') }}</td>
                    <td>{{ $item->curva_abc ?? '-' }}</td>
                    <td>{{ $item->endereco ?? 'SEM ENDEREÇO' }}</td>
                    <td class="{{ $item->observacao ? 'warn' : '' }}">{{ $item->observacao ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
