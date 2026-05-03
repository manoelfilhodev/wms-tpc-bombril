@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-3">Visualizar Etiqueta</h4>

    <div class="card">
        <div class="card-body text-center">
            <img src="{{ asset('storage/etiquetas_png/transferencias/' . $etiqueta->transferencia_id . '/transferencia_' . $etiqueta->id . '.png') }}" 
                 alt="Etiqueta" class="img-fluid mb-3">

            <div>
                <a href="{{ route('transferencia.etiquetas.preview', $etiqueta->id) }}" class="btn btn-primary">
                    <i class="bi bi-printer"></i> Imprimir
                </a>
                <a href="{{ route('transferencia.etiquetas.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Voltar
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
