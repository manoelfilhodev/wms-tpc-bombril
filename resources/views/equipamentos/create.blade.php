@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    @include('partials.breadcrumb-auto')
    
    <!-- Header com ícone roxo -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
            <div class="icon-wrapper me-3">
                <i class="mdi mdi-tools display-6 text-primary"></i>
            </div>
            <div>
                <h3 class="mb-1 fw-bold text-dark">Novo Equipamento</h3>
                <p class="text-muted mb-0 small">Cadastre um novo equipamento no sistema</p>
            </div>
        </div>
    </div>

    <!-- Card do Formulário -->
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form action="{{ route('equipamentos.store') }}" method="POST">
                @csrf
                @include('equipamentos.form')
                <div class="card-footer bg-white border-top">
                    <button type="submit" class="btn btn-primary">
                        <i class="mdi mdi-content-save me-1"></i> Salvar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .icon-wrapper {
        width: 60px; height: 60px;
        display: flex; align-items: center; justify-content: center;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(102,126,234,0.3);
    }
    .icon-wrapper i { color: #fff !important; }

    .input-group-text { background-color: #f8f9fa; }
    .form-control:focus { border-color: #0d6efd; box-shadow: 0 0 0 0.2rem rgba(13,110,253,0.1); }
    .table tbody tr:hover { background-color: #f8f9fa; transition: background-color 0.2s ease; }
    .card { border-radius: 0.5rem; }
    .badge { font-weight: 500; padding: 0.35em 0.65em; }
</style>

@endsection