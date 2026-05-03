@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Importar MB51</h3>

    {{-- Alerta de Sucesso --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
            <strong>Sucesso:</strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    @endif

    {{-- Alerta de Erro --}}
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
            <strong>Erro:</strong> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    @endif

    <form action="{{ route('inventario.importar') }}" method="POST" enctype="multipart/form-data" class="mt-4">
        @csrf
        <div class="mb-3">
            <label for="arquivo" class="form-label">Arquivo MB51 (.xlsx)</label>
            <input type="file" class="form-control" name="arquivo" required>
        </div>
        <button class="btn btn-primary">Importar</button>
    </form>
</div>
@endsection
