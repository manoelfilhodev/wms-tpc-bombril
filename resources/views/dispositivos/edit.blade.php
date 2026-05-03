@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    @include('partials.breadcrumb-auto')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1 fw-bold text-dark">Editar Dispositivo</h3>
            <p class="text-muted mb-0 small">Atualize autorizacao e status do dispositivo</p>
        </div>
        <a href="{{ route('dispositivos.index') }}" class="btn btn-outline-secondary">
            <i class="mdi mdi-arrow-left me-1"></i> Voltar
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Erro.</strong> Verifique os campos informados.
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form action="{{ route('dispositivos.update', $dispositivo->id) }}" method="POST">
                @csrf
                @method('PUT')
                @include('dispositivos.form', ['dispositivo' => $dispositivo, 'currentDeviceId' => null])
            </form>
        </div>
    </div>
</div>
@endsection
