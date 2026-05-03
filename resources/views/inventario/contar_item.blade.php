@extends('layouts.app')

@section('content')
<div class="container">
    <h4>Contar Item do Inventário #{{ $inventarioId }}</h4>

    <form action="{{ route('contar_item.salvar', [$inventarioId, $item->id]) }}" method="POST">
        @csrf

        <div class="mb-3">
            <label class="form-label">SKU:</label>
            <input type="text" class="form-control" value="{{ $item->sku }}" disabled>
        </div>

        <div class="mb-3">
            <label class="form-label">Descrição:</label>
            <textarea class="form-control" rows="2" disabled>{{ $item->descricao }}</textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Posição:</label>
            <input type="text" name="posicao" class="form-control" placeholder="Digite a posição"
                value="{{ old('posicao', $item->posicao) }}" {{ $item->posicao ? '' : 'required' }}>
        </div>

        <div class="mb-3">
            <label class="form-label">Quantidade Contada:</label>
            <input type="number" name="quantidade_fisica" class="form-control" required>
        </div>

        <div class="d-flex justify-content-between">
            <button type="submit" class="btn btn-success">Salvar e Próximo</button>
            <a href="{{ url()->previous() }}" class="btn btn-secondary">Voltar</a>
        </div>
    </form>
</div>
@endsection
