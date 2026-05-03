<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
    @page {
        size: A4;
        margin: 20mm;
    }
    body {
        font-family: sans-serif;
        margin: 0;
        padding: 0;
    }
    .ficha {
        width: 100%;
        height: 100%;
        padding: 10px;
        page-break-after: always;
        box-sizing: border-box;
    }
    .titulo {
        background: black;
        color: white;
        text-align: center;
        padding: 5px;
        font-size: 14px;
    }
    .bloco {
        border: 1px solid #999;
        padding: 6px;
        margin-top: 10px;
        text-align: center;
    }
    .bloco-titulo {
        background-color: #e0e0e0;
        font-weight: bold;
        padding: 4px;
        margin-bottom: 5px;
    }
    .barcode {
        margin-top: 5px;
    }
</style>

</head>
<body>

@foreach ($itens as $index => $item)
<div class="ficha">
    <div class="titulo">INVENTÁRIO CD CAJAMAR 2025</div>

    <div class="bloco">
        <div class="bloco-titulo">FICHA</div>
        <h1>{{ $index + 1 }}</h1>
        <div class="barcode">
            @php $codigoFicha = DNS1D::getBarcodePNG(strval($index + 1), 'C128'); @endphp
            <img src="data:image/png;base64,{{ $codigoFicha }}" height="40">
        </div>
    </div>
    <div class="bloco">
    <div class="bloco-titulo">DEPÓSITO</div>
    <div><h3>{{ mb_strtoupper($item->deposito ?? '-') }}</h3></div>
</div>


    <div class="bloco">
        <div class="bloco-titulo">CÓDIGO</div>
        <h3>{{ mb_strtoupper($item->sku) }}</h3>
        <div class="barcode">
            @php $codigoSku = DNS1D::getBarcodePNG($item->sku, 'C128'); @endphp
            <img src="data:image/png;base64,{{ $codigoSku }}" height="40">
        </div>
    </div>

    <div class="bloco">
        <div class="bloco-titulo">DESCRIÇÃO / UN</div>
        <div style="font-weight: bold;">{{ strtoupper($item->descricao) }}</div>
    </div>
 
    <div class="bloco">
        <div class="bloco-titulo">LOCAL</div>
        <div>{{ mb_strtoupper($item->posicao ?? '-') }}</div>
    </div> 

    <div class="bloco">
        <div class="bloco-titulo">PADRÃO</div>
        <div>-</div>
    </div>
</div>
@endforeach

</body>
</html>
 