@csrf

@if(isset($method))
    @method($method)
@endif

<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label fw-semibold">Código do cliente <span class="text-danger">*</span></label>
        <input type="text" name="codigo_cliente" class="form-control @error('codigo_cliente') is-invalid @enderror"
               value="{{ old('codigo_cliente', $clienteTransitTime->codigo_cliente ?? '') }}" maxlength="50" required>
        @error('codigo_cliente')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-8">
        <label class="form-label fw-semibold">Nome do cliente</label>
        <input type="text" name="nome_cliente" class="form-control @error('nome_cliente') is-invalid @enderror"
               value="{{ old('nome_cliente', $clienteTransitTime->nome_cliente ?? '') }}" maxlength="150">
        @error('nome_cliente')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label fw-semibold">Zona partida</label>
        <input type="text" name="zona_partida" class="form-control @error('zona_partida') is-invalid @enderror"
               value="{{ old('zona_partida', $clienteTransitTime->zona_partida ?? '') }}" maxlength="50">
        @error('zona_partida')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label fw-semibold">Região</label>
        <input type="text" name="regiao" class="form-control @error('regiao') is-invalid @enderror"
               value="{{ old('regiao', $clienteTransitTime->regiao ?? '') }}" maxlength="100">
        @error('regiao')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-2">
        <label class="form-label fw-semibold">UF</label>
        <input type="text" name="uf" class="form-control text-uppercase @error('uf') is-invalid @enderror"
               value="{{ old('uf', $clienteTransitTime->uf ?? '') }}" maxlength="2">
        @error('uf')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">Cidade</label>
        <input type="text" name="cidade" class="form-control @error('cidade') is-invalid @enderror"
               value="{{ old('cidade', $clienteTransitTime->cidade ?? '') }}" maxlength="120">
        @error('cidade')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label fw-semibold">Zona transporte</label>
        <input type="text" name="zona_transporte" class="form-control @error('zona_transporte') is-invalid @enderror"
               value="{{ old('zona_transporte', $clienteTransitTime->zona_transporte ?? '') }}" maxlength="50">
        @error('zona_transporte')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-2">
        <label class="form-label fw-semibold">Ciclo inte</label>
        <input type="number" name="ciclo_inte" min="0" step="1"
               class="form-control @error('ciclo_inte') is-invalid @enderror"
               value="{{ old('ciclo_inte', $clienteTransitTime->ciclo_inte ?? '') }}">
        @error('ciclo_inte')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">Transit-time carga fechada (dias) <span class="text-danger">*</span></label>
        <input type="number" name="transit_time_fechada_dias" min="0" step="1"
               class="form-control @error('transit_time_fechada_dias') is-invalid @enderror"
               value="{{ old('transit_time_fechada_dias', $clienteTransitTime->transit_time_fechada_dias ?? '') }}" required>
        @error('transit_time_fechada_dias')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">Transit-time carga fracionada (dias) <span class="text-danger">*</span></label>
        <input type="number" name="transit_time_fracionada_dias" min="0" step="1"
               class="form-control @error('transit_time_fracionada_dias') is-invalid @enderror"
               value="{{ old('transit_time_fracionada_dias', $clienteTransitTime->transit_time_fracionada_dias ?? '') }}" required>
        @error('transit_time_fracionada_dias')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12">
        <div class="form-check form-switch">
            <input type="hidden" name="ativo" value="0">
            <input class="form-check-input" type="checkbox" role="switch" id="ativo" name="ativo" value="1"
                   @checked(old('ativo', $clienteTransitTime->ativo ?? true))>
            <label class="form-check-label fw-semibold" for="ativo">Cliente ativo</label>
        </div>
    </div>
</div>

<div class="card-footer bg-white border-top mt-4 px-0 pb-0">
    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">
            <i class="mdi mdi-content-save me-1"></i> Salvar
        </button>
        <a href="{{ route('clientes-transit-time.index') }}" class="btn btn-outline-secondary">
            <i class="mdi mdi-close me-1"></i> Cancelar
        </a>
    </div>
</div>
