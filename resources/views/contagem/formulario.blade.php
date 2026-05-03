@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-4">Importar Lista de SKUs para Contagem</h4>

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form action="{{ route('contagem.salvar') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="lista_skus" class="form-label">Cole aqui os dados (Material, Centro, Descrição)</label>
            <textarea name="lista_skus" id="lista_skus" class="form-control" rows="15" placeholder="Exemplo:
3000.AT.003	HY09	ACOPLAMENTO OPT/STAR TURBO C/ TRIAC (AT)"></textarea>
        </div>

        <button type="submit" class="btn btn-success">Salvar Listagem</button>
    </form>
</div>
@endsection
