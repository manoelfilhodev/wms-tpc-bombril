@extends('layouts.app')

@section('content')
<div class="container">

    <h4>Importar Fichas de Contagem</h4>

    {{-- ALERTAS --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- FORMULÁRIO --}}
    <form action="{{ route('inventario.fichas.gerar') }}" method="POST">
        @csrf

        {{-- Seleção de lista existente --}}
        <div class="mb-3">
            <label for="referencia_existente">Selecionar lista existente (opcional):</label>
            <select name="referencia_existente" class="form-select">
                <option value="">-- Criar nova lista --</option>
                @foreach ($referencias as $ref)
                    <option value="{{ $ref->cod_referencia }}">{{ $ref->cod_referencia }} ({{ $ref->total }} fichas)</option>
                @endforeach
            </select>
        </div>

        {{-- Textarea para colar os dados --}}
        <div class="mb-3">
            <label for="lista">Cole abaixo a lista de fichas (formato: POSIÇÃO [TAB] SKU [TAB] DEP):</label>
            <textarea name="lista" class="form-control" rows="10" placeholder="Exemplo:
BLA01	3340.CO.230	AC01
BLA03	3340.CO.230	AC01
BLA05	3340.CO.230	AC01
BLA05	3340.CO.208	AC01" required></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Salvar Fichas</button>
    </form>
</div>
@endsection
