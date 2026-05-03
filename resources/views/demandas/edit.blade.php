@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    @include('partials.breadcrumb-auto')

    <!-- Header com ícone gradiente (padrão gestão de estoque) -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
            <div class="icon-wrapper me-3">
                <i class="mdi mdi-pencil-box-outline display-6"></i>
            </div>
            <div>
                <h3 class="mb-1 fw-bold text-dark">Editar Demanda</h3>
                <p class="text-muted mb-0 small">Atualize os dados da demanda #{{ $demanda->fo }}</p>
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
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form action="{{ route('demandas.update', $demanda->id) }}" method="POST" novalidate>
                @csrf
                @method('PUT')

                <!-- Identificação -->
                <h6 class="text-muted text-uppercase small mb-3">
                    <i class="mdi mdi-information-outline me-1"></i> Identificação
                </h6>
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
                                value="{{ old('fo', $demanda->fo) }}"
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
                                value="{{ old('cliente', $demanda->cliente) }}"
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
                                <option value="RECEBIMENTO" {{ old('tipo', $demanda->tipo) == 'RECEBIMENTO' ? 'selected' : '' }}>Recebimento</option>
                                <option value="EXPEDICAO" {{ old('tipo', $demanda->tipo) == 'EXPEDICAO' ? 'selected' : '' }}>Expedição</option>
                            </select>
                            @error('tipo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <!-- Logística -->
                <h6 class="text-muted text-uppercase small mb-3">
                    <i class="mdi mdi-truck-outline me-1"></i> Logística
                </h6>
                <div class="row g-3">
                    <div class="col-12 col-md-3">
                        <label class="form-label small text-muted mb-1">Transportadora</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="mdi mdi-truck-outline text-muted"></i>
                            </span>
                            <input
                                type="text"
                                name="transportadora"
                                value="{{ old('transportadora', $demanda->transportadora) }}"
                                class="form-control border-start-0 @error('transportadora') is-invalid @enderror"
                                placeholder="Ex: ABC Logística"
                            >
                            @error('transportadora') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="col-12 col-md-3">
                        <label class="form-label small text-muted mb-1">Doca</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="mdi mdi-dock-left text-muted"></i>
                            </span>
                            <input
                                type="text"
                                name="doca"
                                value="{{ old('doca', $demanda->doca) }}"
                                class="form-control border-start-0 @error('doca') is-invalid @enderror"
                                placeholder="Ex: D1, D2..."
                            >
                            @error('doca') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="col-12 col-md-3">
                        <label class="form-label small text-muted mb-1">Veículo</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="mdi mdi-car text-muted"></i>
                            </span>
                            <input
                                type="text"
                                name="veiculo"
                                value="{{ old('veiculo', $demanda->veiculo) }}"
                                class="form-control border-start-0 @error('veiculo') is-invalid @enderror"
                                placeholder="Placa do veículo"
                            >
                            @error('veiculo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="col-12 col-md-3">
                        <label class="form-label small text-muted mb-1">Modelo Veicular</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="mdi mdi-truck-cargo-container text-muted"></i>
                            </span>
                            <input
                                type="text"
                                name="modelo_veicular"
                                value="{{ old('modelo_veicular', $demanda->modelo_veicular) }}"
                                class="form-control border-start-0 @error('modelo_veicular') is-invalid @enderror"
                                placeholder="Ex: Carreta, Toco..."
                            >
                            @error('modelo_veicular') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label small text-muted mb-1">Motorista</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="mdi mdi-account-tie text-muted"></i>
                            </span>
                            <input
                                type="text"
                                name="motorista"
                                value="{{ old('motorista', $demanda->motorista) }}"
                                class="form-control border-start-0 @error('motorista') is-invalid @enderror"
                                placeholder="Nome do motorista"
                            >
                            @error('motorista') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                                value="{{ old('hora_agendada', $demanda->hora_agendada) }}"
                                class="form-control border-start-0 @error('hora_agendada') is-invalid @enderror"
                            >
                            @error('hora_agendada') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="col-12 col-md-2">
                        <label class="form-label small text-muted mb-1">Entrada</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="mdi mdi-login text-muted"></i>
                            </span>
                            <input
                                type="time"
                                name="entrada"
                                value="{{ old('entrada', $demanda->entrada) }}"
                                class="form-control border-start-0 @error('entrada') is-invalid @enderror"
                            >
                            @error('entrada') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="col-12 col-md-2">
                        <label class="form-label small text-muted mb-1">Saída</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="mdi mdi-logout text-muted"></i>
                            </span>
                            <input
                                type="time"
                                name="saida"
                                value="{{ old('saida', $demanda->saida) }}"
                                class="form-control border-start-0 @error('saida') is-invalid @enderror"
                            >
                            @error('saida') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <!-- Métricas -->
                <h6 class="text-muted text-uppercase small mb-3">
                    <i class="mdi mdi-chart-box-outline me-1"></i> Métricas
                </h6>
                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <label class="form-label small text-muted mb-1">Quantidade</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">#</span>
                            <input
                                type="number"
                                name="quantidade"
                                value="{{ old('quantidade', $demanda->quantidade) }}"
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
                                value="{{ old('peso', $demanda->peso) }}"
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
                                value="{{ old('valor_carga', $demanda->valor_carga) }}"
                                class="form-control @error('valor_carga') is-invalid @enderror"
                                min="0"
                                placeholder="0,00"
                                inputmode="decimal"
                            >
                            @error('valor_carga') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                <!-- Ações -->
                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('demandas.index') }}" class="btn btn-light">
                        Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="mdi mdi-content-save-outline me-1"></i> Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Card de Histórico de Status -->
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <h6 class="text-muted text-uppercase small mb-3">
                <i class="mdi mdi-history me-1"></i> Histórico de Status
            </h6>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="px-4 py-3 text-muted small fw-semibold">
                                <i class="mdi mdi-flag-outline me-1"></i> Status
                            </th>
                            <th class="px-4 py-3 text-muted small fw-semibold">
                                <i class="mdi mdi-calendar me-1"></i> Data/Hora
                            </th>
                            <th class="px-4 py-3 text-muted small fw-semibold">
                                <i class="mdi mdi-account me-1"></i> Usuário
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($demanda->history ?? [] as $h)
                            <tr class="border-bottom">
                                <td class="px-4 py-3">
                                    <span class="badge bg-primary">{{ $h->status }}</span>
                                </td>
                                <td class="px-4 py-3 small">
                                    {{ $h->created_at->timezone('America/Sao_Paulo')->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-4 py-3">
                                    @if($h->user)
                                        <span class="text-dark">{{ $h->user->nome }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="mdi mdi-history display-4 d-block mb-2 opacity-25"></i>
                                        <p class="mb-0 small">Nenhum histórico registrado ainda</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
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
    .table tbody tr:hover { background-color: #f8f9fa; transition: background-color 0.2s ease; }
</style>
@endsection