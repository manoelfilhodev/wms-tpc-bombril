@extends('layouts.app')

@section('content')
<div class="container-fluid">
  <h4 class="mb-3">Etiquetas de Produção — {{ $kit->codigo_material }} ({{ $kit->data_montagem }})</h4>

  <button onclick="window.print()" class="btn btn-primary mb-4">
    <i class="bi bi-printer"></i> Imprimir (Ctrl+P)
  </button>

  <div class="d-flex flex-wrap gap-3">
    @foreach($etiquetas as $e)
      <div class="etiqueta border p-3"
           style="width:100mm; height:80mm; border:1px solid #000; font-family:monospace; white-space:pre; page-break-inside: avoid;">
        {!! nl2br(e($e->zpl_code)) !!}
      </div>
    @endforeach
  </div>
</div>

<style>
  @media print {
    body { margin: 0; }
    button { display: none; }
    .etiqueta { page-break-after: always; }
  }
</style>
@endsection
