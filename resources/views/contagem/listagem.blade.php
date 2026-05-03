@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-4">Contagem de SKUs - Lista #{{ $id_lista }}</h4>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('contagem.atualizar') }}">
        @csrf
        <input type="hidden" name="id_lista" value="{{ $id_lista }}">

        <table class="table table-bordered table-sm">
            <thead>
                <tr>
                    <th>Material</th>
                    <th>Centro</th>
                    <th>Descrição</th>
                    <th>Quantidade Contada</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($dados as $item)
                    <tr>
                        <td>{{ $item->material }}</td>
                        <td>{{ $item->centro }}</td>
                        <td>{{ $item->descricao }}</td>
                        <td>
                            <input type="number" name="quantidade[{{ $item->id }}]" value="{{ $item->quantidade }}" min="0" class="form-control">
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <button class="btn btn-primary mt-3">Salvar Contagem</button>
    </form>
</div>
@endsection
