@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Editar Equipamento</h1>
    <form action="{{ route('equipamentos.update', $equipamento->id) }}" method="POST">
        @csrf
        @method('PUT')
        @include('equipamentos.form', ['equipamento' => $equipamento])
        <button type="submit" class="btn btn-primary mt-3">Atualizar</button>
    </form>
</div>
@endsection
