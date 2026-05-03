@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-3">Editar Programação de Transferência</h4>

    <form method="POST" action="{{ route('transferencia.updateProgramacao', $transferencia->id) }}">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="quantidade_programada" class="form-label">Quantidade Programada</label>
            <input type="number" name="quantidade_programada" id="quantidade_programada"
                   class="form-control" value="{{ $transferencia->quantidade_programada }}" min="1" required>
        </div>

        <div class="mb-3">
            <label for="data_transferencia" class="form-label">Data da Transferência</label>
            <input type="date" name="data_transferencia" id="data_transferencia"
                   class="form-control" value="{{ $transferencia->data_transferencia }}" required>
        </div>

        <button type="submit" class="btn btn-warning">
            <i class="bi bi-pencil"></i> Atualizar
        </button>
        <a href="{{ route('transferencia.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection
