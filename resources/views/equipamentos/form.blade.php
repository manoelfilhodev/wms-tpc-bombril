@php
    $tipos = [
        'Empilhadeira',
        'Televisão',
        'Notebook',
        'PC',
        'Celular',
        'Coletor',
        'Máquina de limpar piso',
        'Paleteira (2t)',
    ];
@endphp

<div class="row">
    <div class="col-md-6">
    <label>Tipo de Equipamento</label>
    <select name="tipo" class="form-control" required>
        <option value="">-- Selecione --</option>
        @foreach ($tipos as $tipo)
            <option value="{{ $tipo }}" {{ old('tipo', $equipamento->tipo ?? '') == $tipo ? 'selected' : '' }}>
                {{ $tipo }}
            </option>
        @endforeach
    </select>
</div>
    <div class="col-md-6">
        <label>Modelo</label>
        <input type="text" name="modelo" class="form-control" value="{{ old('modelo', $equipamento->modelo ?? '') }}" required>
    </div>
    <div class="col-md-6">
        <label>Patrimônio</label>
        <input type="text" name="patrimonio" class="form-control" value="{{ old('patrimonio', $equipamento->patrimonio ?? '') }}" required>
    </div>
    <div class="col-md-6">
        <label>Número de Série</label>
        <input type="text" name="numero_serie" class="form-control" value="{{ old('numero_serie', $equipamento->numero_serie ?? '') }}" required>
    </div>
    <div class="col-md-6">
        <label>Status</label>
        <select name="status" class="form-control" required>
            @foreach(['ativo', 'manutenção', 'inativo'] as $status)
                <option value="{{ $status }}" {{ old('status', $equipamento->status ?? '') == $status ? 'selected' : '' }}>
                    {{ ucfirst($status) }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label>Localização</label>
        <input type="text" name="localizacao" class="form-control" value="{{ old('localizacao', $equipamento->localizacao ?? '') }}" required>
    </div>
    <div class="col-md-6">
        <label>Responsável</label>
        <input type="text" name="responsavel" class="form-control" value="{{ old('responsavel', $equipamento->responsavel ?? '') }}" required>
    </div>
    <div class="col-md-6">
        <label>Data de Aquisição</label>
        <input type="date" name="data_aquisicao" class="form-control" value="{{ old('data_aquisicao', $equipamento->data_aquisicao ?? '') }}" required>
    </div>
    <div class="col-12">
        <label>Observações</label>
        <textarea name="observacoes" class="form-control">{{ old('observacoes', $equipamento->observacoes ?? '') }}</textarea>
    </div>
</div>
