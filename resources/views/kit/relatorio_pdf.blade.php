<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Programação de Kits</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: center; }
        th { background-color: #f2f2f2; }
        .assinatura { margin-top: 50px; text-align: center; }
        .break-page { page-break-after: always; }
        .rodape { margin-top: 30px; font-size: 10px; text-align: right; color: #666; }
    </style>
</head>
<body>
    <h2 style="text-align: center;">Relatório de Programação de Kits</h2>

    <table>
        <thead>
            <tr>
                <th>Data Montagem</th>
                <th>SKU</th>
                <th>Programado</th>
                <th>Produzido</th>
                <th>% Realizado</th>
            </tr>
        </thead>
        <tbody>
        @foreach($kits as $index => $kit)
            @if ($index > 0 && $index % 20 === 0)
                </tbody>
                </table>
                <div class="break-page"></div>
                <table>
                    <thead>
                        <tr>
                            <th>Data Montagem</th>
                            <th>SKU</th>
                            <th>Programado</th>
                            <th>Produzido</th>
                            <th>% Realizado</th>
                        </tr>
                    </thead>
                    <tbody>
            @endif
            <tr>
                <td>{{ \Carbon\Carbon::parse($kit->data_montagem)->format('d/m/Y') }}</td>
                <td>{{ strtoupper($kit->codigo_material) }}</td>
                <td>{{ $kit->quantidade_programada }}</td>
                <td>{{ $kit->quantidade_produzida ?? 0 }}</td>
                <td>
                    @php
                        $realizado = $kit->quantidade_programada > 0
                            ? round(($kit->quantidade_produzida ?? 0) / $kit->quantidade_programada * 100, 1)
                            : 0;
                    @endphp
                    {{ $realizado }}%
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <div class="observacoes" style="margin-top: 40px;">
        <h4><b>Observações:</b></h4>
        <div style="border: 1px solid #000; height: 100px; padding: 10px;"></div>
    </div>

    <div class="assinatura">
        <p>___________________________________________</p>
        <p>Responsável pelo Setor</p>
        <p>Data: ____/____/______</p>
    </div>

    <div class="rodape">
        <p>Relatório gerado em {{ now()->format('d/m/Y H:i') }}</p>
    </div>
</body>
</html>
