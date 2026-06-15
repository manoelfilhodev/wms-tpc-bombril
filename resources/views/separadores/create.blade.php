@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    @include('partials.breadcrumb-auto')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
            <div class="icon-wrapper me-3">
                <i class="mdi mdi-account-plus display-6 text-primary"></i>
            </div>
            <div>
                <h3 class="mb-1 fw-bold text-dark">Novo Separador</h3>
                <p class="text-muted mb-0 small">Cadastre um separador para distribuição operacional</p>
            </div>
        </div>
        <a href="{{ route('separadores.index') }}" class="btn btn-outline-secondary">
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
            <form action="{{ route('separadores.store') }}" method="POST">
                @csrf

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nome <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="mdi mdi-account-outline"></i></span>
                            <input type="text" name="nome" class="form-control @error('nome') is-invalid @enderror"
                                   value="{{ old('nome') }}" placeholder="Digite o nome do separador" required>
                            @error('nome')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Chapa <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="mdi mdi-card-account-details-outline"></i></span>
                            <input type="text" name="chapa" class="form-control @error('chapa') is-invalid @enderror"
                                   value="{{ old('chapa') }}" placeholder="Ex: 12345" required>
                            @error('chapa')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Cargo</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="mdi mdi-briefcase-outline"></i></span>
                            <input type="text" name="cargo" class="form-control @error('cargo') is-invalid @enderror"
                                   value="{{ old('cargo') }}" placeholder="Ex: Separador">
                            @error('cargo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Turno</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="mdi mdi-clock-outline"></i></span>
                            <input type="text" name="turno" class="form-control @error('turno') is-invalid @enderror"
                                   value="{{ old('turno') }}" placeholder="Ex: 1º turno">
                            @error('turno')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="card-footer bg-white border-top mt-4 px-0 pb-0">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="mdi mdi-content-save me-1"></i> Cadastrar
                        </button>
                        <a href="{{ route('separadores.index') }}" class="btn btn-outline-secondary">
                            <i class="mdi mdi-close me-1"></i> Cancelar
                        </a>
                    </div>
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
    .input-group-text { background-color: #f8f9fa; border-right: 0; }
    .input-group .form-control { border-left: 0; }
    .form-control:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13,110,253,0.1);
    }
    .card { border-radius: 0.5rem; }
    .card-footer {
        background-color: transparent !important;
        border-top: 1px solid #e9ecef !important;
        padding-top: 1rem;
    }
</style>
@endsection
