@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-3">Gerar Etiquetas - Transferência</h4>

    <form method="POST" action="{{ route('transferencia.etiquetas.gerar') }}">
        @csrf
        <div class="mb-3">
            <label for="transferencia_id" class="form-label">Transferência</label>
            <select name="transferencia_id" id="transferencia_id" class="form-control" required>
                <option value="">Selecione...</option>
                @foreach($transferencias as $t)
                    <option value="{{ $t->id }}">
                        {{ $t->codigo_material }} - {{ $t->quantidade_programada }} unid ({{ \Carbon\Carbon::parse($t->data_transferencia)->format('d/m/Y') }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="qtd_por_palete" class="form-label">Quantidade por Palete</label>
            <input type="number" name="qtd_por_palete" id="qtd_por_palete" class="form-control" min="1" required>
        </div>

        <button type="submit" class="btn btn-success">
            <i class="bi bi-check-circle"></i> Gerar
        </button>
    </form>
</div>
@endsection
