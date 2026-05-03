<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Reimpressão de Etiquetas</title>
    <style>
        @media print {
            .etiqueta {
                width: 80mm;
                height: 100mm;
                page-break-after: always;
                padding: 6mm;
                box-sizing: border-box;
            }
            body * {
                visibility: hidden;
            }
            .print-wrapper, .print-wrapper * {
                visibility: visible;
            }
        }

        body {
            font-family: Arial, sans-serif;
        }

        .etiqueta {
    width: 170mm;
    height: 100mm;
    padding: 3mm;
    box-sizing: border-box;
    font-family: Arial, sans-serif;
    font-size: 22px;
    font-weight: bold;
    text-transform: uppercase;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

        .etiqueta .topo {
            display: flex;
            justify-content: space-between;
            font-size: 26px;
        }

        .etiqueta .conteudo {
            line-height: 1.4;
            margin-top: 20px;
        }
    </style>
</head>
<body>
<div class="print-wrapper">
    @foreach ($etiquetas as $etiqueta)
        <div class="etiqueta">
            <div class="topo">
                <div></div>
                <div class="text-end">
                    DOCA {{ $etiqueta->doca }}<br>
                    {{ $etiqueta->fo }}
                </div>
            </div>
            <div class="conteudo">
                <div>REMESSA: {{ $etiqueta->remessa }}</div>
                <div>CLIENTE: {{ $etiqueta->cliente }}</div>
                <div>CIDADE: {{ $etiqueta->cidade }}</div>
                <div>UF: {{ $etiqueta->uf }}</div>
                <div>PRODUTO: {{ $etiqueta->produto }}</div>
                <div>QTDE PÇS: 1</div>
                <div>DATA - HORA: {{ \Carbon\Carbon::parse($etiqueta->data_gerada)->format('d/m/Y H:i:s') }}</div>
            </div>
        </div>
    @endforeach
</div>

<script>
    window.onload = () => {
        setTimeout(() => {
            window.print();
        }, 800);
    };
</script>
</body>
</html>