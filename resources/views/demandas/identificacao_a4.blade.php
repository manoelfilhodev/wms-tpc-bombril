@extends('layouts.app')

@section('content')
@php
    $dataFormatada = $dados['data'] ? \Carbon\Carbon::parse($dados['data'])->format('d/m/Y') : '';
    $isBox = ($dados['tipo'] ?? 'dt') === 'box';
    $temDados = $isBox
        ? $dados['box'] !== ''
        : ($dados['dt'] !== '' || $dados['pallets'] !== '' || $dados['conferente'] !== '');
@endphp

<div class="container-fluid px-4 py-3 identificacao-page">
    @include('partials.breadcrumb-auto')

    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <div class="d-flex align-items-center">
            <div class="icon-wrapper me-3">
                <i class="mdi mdi-printer-outline display-6"></i>
            </div>
            <div>
                <h3 class="mb-1 fw-bold text-dark">Identificação A4</h3>
                <p class="text-muted mb-0 small">Duas vias iguais na folha para cortar no meio</p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('demandas.operacional') }}" class="btn btn-outline-secondary btn-sm">Voltar operacional</a>
            <button type="button" class="btn btn-primary btn-sm" onclick="window.print()" @disabled(!$temDados)>
                <i class="mdi mdi-printer me-1"></i> Imprimir
            </button>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4 no-print">
        <div class="card-body">
            <div class="accordion identificacao-accordion" id="identificacaoAccordion">
                <div class="accordion-item border rounded-3 overflow-hidden mb-2">
                    <h2 class="accordion-header" id="headingDt">
                        <button class="accordion-button py-2 @if($isBox) collapsed @endif" type="button"
                            data-bs-toggle="collapse" data-bs-target="#collapseDt"
                            aria-expanded="{{ $isBox ? 'false' : 'true' }}" aria-controls="collapseDt">
                            <i class="mdi mdi-file-document-outline me-2"></i> Identificação DT / Pallets
                        </button>
                    </h2>
                    <div id="collapseDt" class="accordion-collapse collapse @if(!$isBox) show @endif"
                        aria-labelledby="headingDt" data-bs-parent="#identificacaoAccordion">
                        <div class="accordion-body">
                            <form method="GET" action="{{ route('demandas.identificacaoA4') }}" class="row g-3">
                                <input type="hidden" name="tipo" value="dt">
                                <div class="col-md-3">
                                    <label class="form-label small text-muted mb-1">DT</label>
                                    <input type="text" name="dt" class="form-control form-control-sm"
                                        value="{{ $dados['dt'] }}" placeholder="Ex.: 251309435">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small text-muted mb-1">Pallets</label>
                                    <input type="text" name="pallets" class="form-control form-control-sm"
                                        value="{{ $dados['pallets'] }}" placeholder="Ex.: 21">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small text-muted mb-1">Data</label>
                                    <input type="date" name="data" class="form-control form-control-sm"
                                        value="{{ $dados['data'] }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small text-muted mb-1">Conferente</label>
                                    <input type="text" name="conferente" class="form-control form-control-sm"
                                        value="{{ $dados['conferente'] }}" placeholder="Ex.: MARIA">
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="submit" class="btn btn-sm btn-primary w-100">
                                        <i class="mdi mdi-eye-outline me-1"></i> Gerar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="accordion-item border rounded-3 overflow-hidden">
                    <h2 class="accordion-header" id="headingBox">
                        <button class="accordion-button py-2 @if(!$isBox) collapsed @endif" type="button"
                            data-bs-toggle="collapse" data-bs-target="#collapseBox"
                            aria-expanded="{{ $isBox ? 'true' : 'false' }}" aria-controls="collapseBox">
                            <i class="mdi mdi-package-variant-closed me-2"></i> Identificação BOX
                        </button>
                    </h2>
                    <div id="collapseBox" class="accordion-collapse collapse @if($isBox) show @endif"
                        aria-labelledby="headingBox" data-bs-parent="#identificacaoAccordion">
                        <div class="accordion-body">
                            <form method="GET" action="{{ route('demandas.identificacaoA4') }}" class="row g-3">
                                <input type="hidden" name="tipo" value="box">
                                <div class="col-md-4">
                                    <label class="form-label small text-muted mb-1">Número do BOX</label>
                                    <input type="text" name="box" class="form-control form-control-sm"
                                        value="{{ $dados['box'] }}" placeholder="Ex.: 7" required>
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="submit" class="btn btn-sm btn-primary w-100">
                                        <i class="mdi mdi-eye-outline me-1"></i> Gerar BOX
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="small text-muted mt-3">
                A impressão usa A4 em retrato com duas identificações repetidas, uma em cada metade da folha.
            </div>
        </div>
    </div>

    <div class="sheet-preview">
        <div class="a4-sheet">
            @for($i = 0; $i < 2; $i++)
                @if($isBox)
                    <section class="id-copy box-copy">
                        <div class="box-identificacao">
                            <div class="box-title">BOX</div>
                            <div class="box-number">{{ $dados['box'] }}</div>
                        </div>
                    </section>
                @else
                    <section class="id-copy">
                        <table class="id-table">
                            <tbody>
                                <tr>
                                    <th>DT:</th>
                                    <td>{{ $dados['dt'] }}</td>
                                </tr>
                                <tr>
                                    <th>PALLETS</th>
                                    <td>{{ $dados['pallets'] }}</td>
                                </tr>
                                <tr>
                                    <th>DATA</th>
                                    <td>{{ $dataFormatada }}</td>
                                </tr>
                                <tr>
                                    <th>CONFERENTE</th>
                                    <td>{{ $dados['conferente'] }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </section>
                @endif
            @endfor
        </div>
    </div>
</div>

<style>
    .icon-wrapper {
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #111827 0%, #4b5563 100%);
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(17, 24, 39, 0.22);
    }

    .icon-wrapper i { color: #fff !important; }

    .identificacao-accordion .accordion-button {
        font-weight: 700;
        color: #1f2937;
        background: #fff;
    }

    .identificacao-accordion .accordion-button:not(.collapsed) {
        color: #0d6efd;
        background: #f8fbff;
        box-shadow: inset 0 -1px 0 rgba(13, 110, 253, 0.12);
    }

    .sheet-preview {
        display: flex;
        justify-content: center;
        padding: 8px 0 40px;
        overflow: auto;
    }

    .a4-sheet {
        width: 210mm;
        height: 297mm;
        background: #fff;
        color: #20242a;
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.18);
        display: flex;
        flex-direction: column;
    }

    .id-copy {
        width: 210mm;
        height: 148.5mm;
        padding: 7mm 7mm;
        display: flex;
        align-items: stretch;
        justify-content: center;
        position: relative;
    }

    .id-copy:first-child {
        border-bottom: 1px dashed #777;
    }

    .box-copy {
        align-items: center;
        padding: 10mm 5mm;
    }

    .box-identificacao {
        width: 100%;
        height: 112mm;
        border: 1px solid #111;
        display: grid;
        grid-template-rows: 1fr 1fr;
        font-family: Arial, Helvetica, sans-serif;
        font-weight: 900;
        color: #333;
        line-height: 1;
    }

    .box-title,
    .box-number {
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
    }

    .box-title {
        border-bottom: 1px solid #111;
        font-size: 92px;
    }

    .box-number {
        font-size: 88px;
    }

    .id-table {
        width: 100%;
        height: 132mm;
        border-collapse: collapse;
        table-layout: fixed;
        font-family: Arial, Helvetica, sans-serif;
        font-weight: 900;
    }

    .id-table th,
    .id-table td {
        border: 1px solid #777;
        vertical-align: middle;
    }

    .id-table th {
        width: 23mm;
        text-align: center;
        padding: 0 1mm;
        font-size: 11px;
        color: #111;
        white-space: nowrap;
    }

    .id-table td {
        text-align: center;
        color: #20242a;
        line-height: 1;
        letter-spacing: 1px;
        word-break: break-word;
        overflow: hidden;
    }

    .id-table tr:nth-child(1) { height: 42mm; }
    .id-table tr:nth-child(2) { height: 30mm; }
    .id-table tr:nth-child(3) { height: 30mm; }
    .id-table tr:nth-child(4) { height: 30mm; }

    .id-table tr:nth-child(1) td { font-size: clamp(56px, 19mm, 104px); }
    .id-table tr:nth-child(2) td { font-size: clamp(48px, 17mm, 92px); }
    .id-table tr:nth-child(3) td { font-size: clamp(44px, 15mm, 78px); }
    .id-table tr:nth-child(4) td { font-size: clamp(46px, 16mm, 86px); }

    @page {
        size: A4 portrait;
        margin: 0;
    }

    @media print {
        body {
            background: #fff !important;
        }

        .no-print,
        .navbar-custom,
        .leftside-menu,
        footer,
        .breadcrumb,
        .button-menu-mobile,
        .end-bar {
            display: none !important;
        }

        .content-page,
        .content,
        .container-fluid,
        .identificacao-page,
        .sheet-preview {
            margin: 0 !important;
            padding: 0 !important;
            width: 210mm !important;
            min-width: 210mm !important;
            max-width: 210mm !important;
            overflow: visible !important;
        }

        .a4-sheet {
            width: 210mm !important;
            height: 297mm !important;
            box-shadow: none !important;
        }

        .id-copy {
            break-inside: avoid;
            page-break-inside: avoid;
        }
    }
</style>
@endsection
