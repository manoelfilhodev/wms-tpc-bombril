@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-3">Impressão de Todas as Etiquetas</h4>
    <button onclick="window.print()" class="btn btn-primary mb-3">Imprimir</button>

    <div class="row">
        @foreach($etiquetas_png as $file)
            <div class="col-md-3 mb-3 text-center">
                <img src="{{ Storage::url($file) }}" class="img-thumbnail" alt="Etiqueta">
            </div>
        @endforeach
    </div>
</div>
@endsection
