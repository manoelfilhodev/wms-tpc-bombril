@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h4>Gerar Link de Cadastro</h4>
    <form action="{{ route('convites.gerar') }}" method="POST">
        @csrf
        <div class="row mb-3">
            <div class="col-md-4">
                <label>Unidade</label>
                <select name="unidade" class="form-control" required>
                    <option value="">Selecione...</option>
                    @foreach($unidades as $unidade)
                        <option value="{{ $unidade->id }}">{{ $unidade->nome }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label>Nível de Acesso</label>
                <select name="nivel_padrao" class="form-control" required>
                    <option value="Expedicao">Expedição</option>
                    <option value="Recebimento">Recebimento</option>
                    <option value="Separacao">Separação</option>
                    <option value="Admin">Admin</option>
                </select>
            </div>
            <div class="col-md-4">
                <label>Validade (horas)</label>
                <input type="number" name="validade" class="form-control" value="48" required>
            </div>
        </div>
        <button class="btn btn-primary">Gerar Link</button>
    </form>

    @if(session('link_gerado'))
        <div class="alert alert-success mt-4">
            <strong>Link gerado:</strong>
            <div class="input-group mt-2">
                <input type="text" id="link" class="form-control" value="{{ session('link_gerado') }}" readonly>
                <button class="btn btn-secondary" onclick="copiarLink()">Copiar</button>
            </div>
        </div>
    @endif
</div>

<script>
    function copiarLink() {
        const input = document.getElementById("link");
        input.select();
        document.execCommand("copy");
        alert("Link copiado!");
    }
</script>
@endsection
