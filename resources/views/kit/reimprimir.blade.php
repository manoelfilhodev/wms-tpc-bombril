@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-3">Reimpressão de Etiqueta</h4>
    <button onclick="window.print()" class="btn btn-warning mb-3">Reimprimir</button>

    <div class="text-center">
        <img src="{{ Storage::url($etiqueta) }}" class="img-fluid" alt="Etiqueta">
    </div>
</div>
@endsection
