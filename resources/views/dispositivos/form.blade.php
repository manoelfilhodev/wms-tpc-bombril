@php
    $deviceId = old('device_id', $dispositivo->device_id ?? $currentDeviceId);
    $tipo = old('tipo', $dispositivo->tipo ?? 'web');
    $ativo = old('ativo', isset($dispositivo) ? (string) (int) $dispositivo->ativo : '1');
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label fw-semibold">Nome do dispositivo <span class="text-danger">*</span></label>
        <input type="text" name="nome_dispositivo" class="form-control @error('nome_dispositivo') is-invalid @enderror" value="{{ old('nome_dispositivo', $dispositivo->nome_dispositivo ?? '') }}" required>
        @error('nome_dispositivo') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">Device ID <span class="text-danger">*</span></label>
        <input type="text" name="device_id" class="form-control @error('device_id') is-invalid @enderror" value="{{ $deviceId }}" required>
        @error('device_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label fw-semibold">Tipo <span class="text-danger">*</span></label>
        <select name="tipo" class="form-select @error('tipo') is-invalid @enderror" required>
            <option value="web" {{ $tipo === 'web' ? 'selected' : '' }}>WEB</option>
            <option value="app" {{ $tipo === 'app' ? 'selected' : '' }}>APP</option>
        </select>
        @error('tipo') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
        <select name="ativo" class="form-select @error('ativo') is-invalid @enderror" required>
            <option value="1" {{ $ativo === '1' ? 'selected' : '' }}>Ativo</option>
            <option value="0" {{ $ativo === '0' ? 'selected' : '' }}>Inativo</option>
        </select>
        @error('ativo') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label fw-semibold">Usuario ID</label>
        <input type="number" name="usuario_id" class="form-control @error('usuario_id') is-invalid @enderror" value="{{ old('usuario_id', $dispositivo->usuario_id ?? '') }}" placeholder="Opcional">
        @error('usuario_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label fw-semibold">Perfil permitido</label>
        <input type="text" name="perfil_permitido" class="form-control @error('perfil_permitido') is-invalid @enderror" value="{{ old('perfil_permitido', $dispositivo->perfil_permitido ?? '') }}" placeholder="Opcional">
        @error('perfil_permitido') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="card-footer bg-white border-top mt-4 px-0 pb-0">
    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">
            <i class="mdi mdi-content-save me-1"></i> Salvar
        </button>
        <a href="{{ route('dispositivos.index') }}" class="btn btn-outline-secondary">
            <i class="mdi mdi-close me-1"></i> Cancelar
        </a>
    </div>
</div>
