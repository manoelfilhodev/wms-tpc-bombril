@extends($layout)

@section('content')
<div class="container-fluid">
    <h4 class="mb-4">
        {{ isset($etiqueta) ? 'Editar Etiqueta' : 'Nova Etiqueta' }}
    </h4>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" 
          action="{{ isset($etiqueta) 
                        ? route('kits.etiquetas.update', $etiqueta->id) 
                        : route('kits.etiquetas.store', $kit->id) }}">
        @csrf
        @if(isset($etiqueta))
            @method('PUT')
        @endif

        <div class="mb-3">
            <label class="form-label">SKU</label>
            <input type="text" name="sku" class="form-control" 
                   value="{{ old('sku', $etiqueta->sku ?? '') }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">EAN</label>
            <input type="text" name="ean" class="form-control" 
                   value="{{ old('ean', $etiqueta->ean ?? '') }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Descrição</label>
            <input type="text" name="descricao" class="form-control" 
                   value="{{ old('descricao', $etiqueta->descricao ?? '') }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Quantidade</label>
            <input type="number" name="quantidade" class="form-control" 
                   value="{{ old('quantidade', $etiqueta->quantidade ?? 1) }}" min="1" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Unidade de Armazenamento (UA)</label>
            <input type="text" name="ua" class="form-control" 
                   value="{{ old('ua', $etiqueta->ua ?? '') }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Nº da Etiqueta</label>
            <input type="number" name="numero_etiqueta" class="form-control" 
                   value="{{ old('numero_etiqueta', $etiqueta->numero_etiqueta ?? 1) }}" min="1" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Total de Etiquetas</label>
            <input type="number" name="total_etiquetas" class="form-control" 
                   value="{{ old('total_etiquetas', $etiqueta->total_etiquetas ?? 1) }}" min="1" required>
        </div>

        <button type="submit" class="btn btn-primary">
            {{ isset($etiqueta) ? 'Atualizar' : 'Salvar' }}
        </button>
        <a href="{{ route('kits.etiquetas.index', $kit->id) }}" class="btn btn-outline-secondary">Cancelar</a>
    </form>
</div>
@endsection
