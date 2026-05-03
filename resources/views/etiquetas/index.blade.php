@extends('layouts.app')

@section('content')
<style>
@media print {
    .etiqueta {
        width: 150mm;
        height: 80mm;
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

    .no-print {
        display: none !important;
    }
}

.etiqueta {
    width: 170mm;
    height: 100mm;
    padding: 3mm;
    box-sizing: border-box;
    font-family: Arial, sans-serif;
    font-size: 26px;
    font-weight: bold;
    text-transform: uppercase;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
.etiqueta .topo {
    display: flex;
    justify-content: space-between;
    font-size: 32px;
}
.etiqueta .conteudo {
    margin-top: 20px;
    line-height: 1.4;
}
</style>

<div class="container-fluid print-wrapper">
    <form method="GET" action="{{ route('etiquetas.html') }}" class="no-print">
        <div class="mb-3">
            <label for="dados" class="form-label">📋 Cole os dados do Excel (tabulados)</label>
            <textarea name="dados" id="dados" rows="7" class="form-control" placeholder="FO[TAB]REMESSA[TAB]COD_CLIENTE[TAB]CLIENTE[TAB]PRODUTO[TAB]QTD[TAB]CIDADE[TAB]UF" required></textarea>
        </div>
        <div class="form-group no-print">
            <label for="modo_impressao">Modo de Impressão:</label>
            <select name="modo_impressao" class="form-control w-auto d-inline-block" required>
                <option value="" selected disabled>Selecionar Tipo de Impressão</option>
                <option value="normal">Normal (1 etiqueta por unidade)</option>
                <option value="multiplo">Por múltiplo de embalagem</option>
            </select>
        </div>
        
        <div class="d-flex justify-content-end gap-2 mt-3">
    <button type="submit" class="btn btn-success">Gerar Etiquetas</button>
    @if(count($etiquetas))
        <div class="text-center no-print">
            <button class="btn btn-secondary" onclick="window.print()">🖨️ Imprimir Etiquetas</button>
        </div>
    @endif
    <a href="{{ route('etiquetas.hydra.historico') }}" class="btn btn-outline-secondary">
        Ver Histórico
    </a>
</div>

    </form>

    <hr class="my-4 no-print" />

    @foreach ($etiquetas as $etiqueta)
        <div class="etiqueta">
    <div class="topo">
        <div></div>
        <div class="text-end">
            FO {{ $etiqueta->FO }}
        </div>
    </div>
    <div class="conteudo">
    <div style="font-size: 32px">REMESSA: {{ $etiqueta->REMESSA }}</div>
    <div>RECEBEDOR: {{ $etiqueta->RECEBEDOR }}</div>
    <div>CLIENTE: {{ $etiqueta->CLIENTE }}</div>
    <div>CIDADE: {{ $etiqueta->CIDADE }}</div>
    <div>UF: {{ $etiqueta->UF }}</div>
    <div>PRODUTO: {{ $etiqueta->PRODUTO }}</div>
    <div>QTDE PÇS: {{ $etiqueta->QTD }}</div>
    <div>DATA - HORA: {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</div>
</div>
</div>

    @endforeach

    
</div>
@endsection
