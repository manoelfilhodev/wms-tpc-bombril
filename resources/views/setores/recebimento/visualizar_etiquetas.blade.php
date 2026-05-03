@extends('layouts.app')

@section('content')
<div class="container">
    <h4>Etiquetas do Recebimento</h4>
    <a href="{{ url()->previous() }}" class="btn btn-secondary mb-3">Voltar</a>

    @foreach($imagens as $img)
        <div class="mb-4 text-center">
            <img src="{{ $img }}" alt="Etiqueta" style="border:1px solid #ccc;">
        </div>
    @endforeach
</div>
@endsection
