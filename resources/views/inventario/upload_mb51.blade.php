@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Importar MB51 (Etapa 1)</h3>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form action="{{ route('inventario.mb51.salvar_temp') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
            <label for="arquivo" class="form-label">Arquivo MB51 (.xlsx)</label>
            <input type="file" class="form-control" name="arquivo" required>
        </div>
        <button class="btn btn-primary">Salvar Temporariamente</button>
    </form>
</div>
@endsection
