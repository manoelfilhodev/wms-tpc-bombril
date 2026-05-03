@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    @include('partials.breadcrumb-auto')

    <!-- Header com ícone gradiente (padrão gestão de estoque) -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
            <div class="icon-wrapper me-3">
                <i class="mdi mdi-clipboard-plus-outline display-6"></i>
            </div>
            <div>
                <h3 class="mb-1 fw-bold text-dark">Lançar Nova Demanda</h3>
                <p class="text-muted mb-0 small">Crie uma demanda de Recebimento ou Expedição</p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('demandas.index') }}" class="btn btn-outline-secondary btn-sm" data-bs-toggle="tooltip" title="Voltar para a lista">
                <i class="mdi mdi-arrow-left"></i> Voltar
            </a>
        </div>
    </div>

    <!-- Alertas de erro -->
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="mdi mdi-alert-circle-outline me-2"></i>
            Existem campos que precisam da sua atenção.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            <ul class="mb-0 mt-2 small">
                @foreach ($errors->all() as $erro)
                    <li>{{ $erro }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Card do Formulário (padrão gestão de estoque) -->
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form action="{{ route('demandas.store') }}" method="POST" novalidate>
                @csrf

                <!-- Identificação -->
                <h6 class="text-muted text-uppercase small mb-3">Identificação</h6>
                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <label class="form-label small text-muted mb-1">FO <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="mdi mdi-pound text-muted"></i>
                            </span>
                            <input
                                type="text"
                                name="fo"
                                value="{{ old('fo') }}"
                                class="form-control border-start-0 @error('fo') is-invalid @enderror"
                                placeholder="Ex: 123456"
                                required
                            >
                            @error('fo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label small text-muted mb-1">Cliente <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="mdi mdi-account-outline text-muted"></i>
                            </span>
                            <input
                                type="text"
                                name="cliente"
                                value="{{ old('cliente') }}"
                                class="form-control border-start-0 @error('cliente') is-invalid @enderror"
                                placeholder="Nome do cliente"
                                required
                            >
                            @error('cliente') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label small text-muted mb-1">Tipo <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">
                                <i class="mdi mdi-compare-horizontal text-muted"></i>
                            </span>
                            <select
                                name="tipo"
                                class="form-select @error('tipo') is-invalid @enderror"
                                required
                            >
                                <option value="" disabled {{ old('tipo') ? '' : 'selected' }}>Selecione...</option>
                                <option value="RECEBIMENTO" {{ old('tipo')=='RECEBIMENTO' ? 'selected' : '' }}>Recebimento</option>
                                <option value="EXPEDICAO" {{ old('tipo')=='EXPEDICAO' ? 'selected' : '' }}>Expedição</option>
                            </select>
                            @error('tipo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <!-- Logística -->
                <h6 class="text-muted text-uppercase small mb-3">Logística</h6>
                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <label class="form-label small text-muted mb-1">Transportadora</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="mdi mdi-truck-outline text-muted"></i>
                            </span>
                            <input
                                type="text"
                                name="transportadora"
                                value="{{ old('transportadora') }}"
                                class="form-control border-start-0 @error('transportadora') is-invalid @enderror"
                                placeholder="Ex: ABC Logística"
                            >
                            @error('transportadora') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <small class="text-muted">Se não informado, pode ser definido depois.</small>
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label small text-muted mb-1">Doca</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="mdi mdi-dock-left text-muted"></i>
                            </span>
                            <input
                                type="text"
                                name="doca"
                                value="{{ old('doca') }}"
                                class="form-control border-start-0 @error('doca') is-invalid @enderror"
                                placeholder="Ex: D1, D2..."
                            >
                            @error('doca') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label small text-muted mb-1">Hora Agendada</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="mdi mdi-calendar-clock text-muted"></i>
                            </span>
                            <input
                                type="time"
                                name="hora_agendada"
                                value="{{ old('hora_agendada') }}"
                                class="form-control border-start-0 @error('hora_agendada') is-invalid @enderror"
                            >
                            @error('hora_agendada') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <!-- Métricas -->
                <h6 class="text-muted text-uppercase small mb-3">Métricas</h6>
                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <label class="form-label small text-muted mb-1">Quantidade</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">#</span>
                            <input
                                type="number"
                                name="quantidade"
                                value="{{ old('quantidade') }}"
                                class="form-control @error('quantidade') is-invalid @enderror"
                                min="0"
                                placeholder="0"
                                inputmode="numeric"
                            >
                            <span class="input-group-text">un</span>
                            @error('quantidade') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label small text-muted mb-1">Peso</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">
                                <i class="mdi mdi-weight-kilogram text-muted"></i>
                            </span>
                            <input
                                type="number"
                                step="0.01"
                                name="peso"
                                value="{{ old('peso') }}"
                                class="form-control @error('peso') is-invalid @enderror"
                                min="0"
                                placeholder="0,00"
                                inputmode="decimal"
                            >
                            <span class="input-group-text">kg</span>
                            @error('peso') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label small text-muted mb-1">Valor da Carga</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">R$</span>
                            <input
                                type="number"
                                step="0.01"
                                name="valor_carga"
                                value="{{ old('valor_carga') }}"
                                class="form-control @error('valor_carga') is-invalid @enderror"
                                min="0"
                                placeholder="0,00"
                                inputmode="decimal"
                            >
                            @error('valor_carga') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                        <small class="text-muted">Use ponto para decimais, ex: 1234.56</small>
                    </div>
                </div>

                <!-- Ações -->
                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('demandas.index') }}" class="btn btn-light">
                        Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="mdi mdi-content-save-outline me-1"></i> Salvar
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

    hr { opacity: .08; }
    .form-label { font-weight: 500; }
    input::placeholder { color: #adb5bd; }
    .input-group-text { background-color: #f8f9fa; }
    .form-control:focus { border-color: #0d6efd; box-shadow: 0 0 0 0.2rem rgba(13,110,253,0.1); }
    .card { border-radius: 0.5rem; }
</style>
@endsection