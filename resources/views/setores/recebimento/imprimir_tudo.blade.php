<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Imprimir Etiquetas</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .etiqueta {
            border: 1px solid #000;
            padding: 10px;
            margin-bottom: 10px;
            width: 300px;
            page-break-inside: avoid;
        }
    </style>
</head>
<body onload="window.print()">
    @foreach($etiquetas as $etq)
        <div class="etiqueta">
            <strong>SKU:</strong> {{ $etq['sku'] }}<br>
            <strong>Descrição:</strong> {{ $etq['descricao'] }}<br>
            <strong>EAN:</strong> {{ $etq['ean'] }}<br>
            <strong>Palete:</strong> {{ $etq['palete'] }}/{{ $etq['total_paletes'] }}
        </div>
    @endforeach
</body>
</html>
