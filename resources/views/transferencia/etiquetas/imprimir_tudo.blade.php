@extends('layouts.app')

@section('content')
<div class="container">
    
    <div id="etiquetas">
        @foreach($apontamentos as $a)
            <div class="etiqueta">
                {{-- Título --}}
                <div class="linha titulo">DEXCO – CAJ</div>
                <div class="linha divisor"></div>

                {{-- SKU e descrição --}}
                <div class="linha destaque">SKU: {{ strtoupper($a->sku) }}</div>
                <div class="linha descricao">{{ strtoupper($a->descricao) }}</div>

                {{-- Quantidade e UID --}}
                <div class="linha">Qtd: {{ $a->quantidade }}</div>
                <div class="linha">
                    UA: {{ $a->palete_uid }}
                    <span class="etq">Etiqueta {{ $loop->iteration }}/{{ count($apontamentos) }}</span>
                </div>
                <div class="linha divisor"></div>

                {{-- Código de barras + QR Code lado a lado --}}
                <div class="linha barcode-qrcode">
                    <div class="barcode-wrap">
                        <svg class="barcode"
                            jsbarcode-format="EAN13"
                            jsbarcode-value="{{ $a->ean }}"
                            jsbarcode-textmargin="0"
                            jsbarcode-fontSize="26"
                            jsbarcode-height="70">
                        </svg>
                    </div>

                    <div class="qrcode" id="qrcode-{{ $loop->iteration }}" data-value="{{ $a->palete_uid }}"></div>
                </div>

                <div class="linha divisor"></div>

                {{-- Rodapé --}}
                <div class="linha">UA: {{ $a->palete_uid }}</div>
                <div class="linha">Impresso: {{ now()->format('d/m/Y H:i') }}</div>
            </div>
        @endforeach
    </div>

    <button class="btn btn-primary mt-3" onclick="window.print()">Imprimir Tudo</button>
</div>

{{-- ===== GOOGLE FONT ===== --}}
<link href="https://fonts.googleapis.com/css2?family=Roboto+Mono:wght@400;700&display=swap" rel="stylesheet">

{{-- ===== CSS ===== --}}
<style>
    .etiqueta {
        page-break-after: always;
        width: 150mm;
        height: 80mm;
        padding: 10px;
        margin: 10px auto;
        font-family: 'Roboto Mono', 'Courier New', monospace;
        position: relative;
        color: black;
    }
    .linha {
        margin: 4px 0;
        font-size: 16px;
    }
    .titulo {
        font-size: 24px;
        font-weight: 700;
    }
    .destaque {
        font-size: 20px;
        font-weight: 700;
    }
    .descricao {
        font-size: 16px;
        font-weight: 400;
    }
    .divisor {
        border-top: 2px solid #000;
        margin: 8px 0;
    }
    .etq {
        float: right;
        font-size: 14px;
    }
    .barcode-qrcode {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-top: 10px;
    }
    .barcode-wrap {
        flex: 1;
        text-align: center;
    }
    .barcode {
        display: block;
        margin: 0 auto;
        max-width: 100%;
    }
    .qrcode {
        width: 100px;
        height: 100px;
        margin-left: 10px;
    }
    .center {
        text-align: center;
        font-size: 14px;
    }
</style>

{{-- ===== JS ===== --}}
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>

<script>
    // Renderiza todos os códigos de barras
    JsBarcode(".barcode").init();

    // Renderiza todos os QRCodes
    document.querySelectorAll(".qrcode").forEach(function(el) {
        const value = el.getAttribute("data-value");
        new QRCode(el, {
            text: value,
            width: 80,
            height: 80
        });
    });
</script>
@endsection
