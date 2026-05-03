@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-3">Confirmação de Produçã<optgroup></optgroup></h4>

    <div class="card mb-3">
        <div class="card-body">
            <p><strong>ID:</strong> {{ $kit->id }}</p>
            <p><strong>Nome:</strong> {{ $kit->nome }}</p>
            <p><strong>Descrição:</strong> {{ $kit->descricao ?? '-' }}</p>
            <p><strong>Status:</strong> {{ $kit->status }}</p>
        </div>
    </div>

    <div class="mt-3">
        <a href="{{ route('kit.etiquetas.gerar', $kit->id) }}" class="btn btn-primary">
            Gerar Etiqueta
        </a>
        <a href="{{ route('kit.index') }}" class="btn btn-secondary">
            Cancelar
        </a>
    </div>
</div>
@endsection
