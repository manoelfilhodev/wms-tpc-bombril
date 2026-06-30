@extends('layouts.app')

@section('title', 'Novo Transit Time por Cliente')

@section('content')
<div class="container-fluid px-4 py-3 transit-page">
    @include('partials.breadcrumb-auto')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
            <div class="icon-wrapper me-3">
                <i class="mdi mdi-truck-plus-outline display-6"></i>
            </div>
            <div>
                <h3 class="mb-1 fw-bold text-dark">Novo Transit Time por Cliente</h3>
                <p class="text-muted mb-0 small">Cadastre a base fixa de transit-time fornecida pelo cliente</p>
            </div>
        </div>
        <a href="{{ route('clientes-transit-time.index') }}" class="btn btn-outline-secondary">
            <i class="mdi mdi-arrow-left me-1"></i> Voltar
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="mdi mdi-alert-circle-outline me-2"></i>{{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form action="{{ route('clientes-transit-time.store') }}" method="POST">
                @include('clientes-transit-time._form')
            </form>
        </div>
    </div>
</div>

@include('clientes-transit-time._style')
@endsection
