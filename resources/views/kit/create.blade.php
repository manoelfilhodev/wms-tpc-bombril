@extends('layouts.app')

@section('content')
<div class="container">
    <h4>Adicionar Componente ao Kit</h4>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('kits.componentes.store') }}">
        @csrf

        <div class="mb-3">
            <label for="kit_material_id" class="form-label">Kit</label>
            <select name="kit_material_id" class="form-control" required>
                <option value="">Selecione o kit</option>
                @foreach($materiais as $material)
                    <option value="{{ $material->id }}">{{ $material->sku }} - {{ $material->descricao }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="componente_material_id" class="form-label">Componente</label>
            <select name="componente_material_id" class="form-control" required>
                <option value="">Selecione o componente</option>
                @foreach($materiais as $material)
                    <option value="{{ $material->id }}">{{ $material->sku }} - {{ $material->descricao }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="quantidade_por_kit" class="form-label">Quantidade por Kit</label>
            <input type="number" step="0.001" name="quantidade_por_kit" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-success">Salvar</button>
    </form>
</div>
@endsection
