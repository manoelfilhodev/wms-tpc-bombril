@extends('layouts.app')

@section('content')
<div id="overlay">
    <div class="spinner"></div>
</div>
<div class="container">
    <h4>Conferir Item da Nota Fiscal: {{ $recebimento->nota_fiscal }}</h4>
    <p><strong>SKU:</strong> {{ $item->sku }}</p>
    <p><strong>Descrição:</strong> {{ $item->descricao }}</p>
    <!--<p><strong>Quantidade Esperada:</strong> {{ $item->quantidade }}</p>-->

    <form action="{{ route('setores.conferencia.enviarItemManual', $item->id) }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="mb-3">
            <label>Quantidade Conferida</label>
            <input type="number" name="qtd_conferida" class="form-control" required min="0" value="{{ old('qtd_conferida', $item->qtd_conferida) }}">
        </div>

        <div class="mb-3">
            <label>Observação</label>
            <textarea name="observacao" class="form-control" rows="2">{{ old('observacao', isset($item->observacao) ? $item->observacao : '') }}

</textarea>
        </div>

        <div class="mb-3 form-check">
            <input class="form-check-input" type="checkbox" name="avariado" id="avariadoCheck" {{ $item->avariado ? 'checked' : '' }}>
            <label class="form-check-label" for="avariadoCheck">Item Avariado</label>
        </div>

        <div class="mb-3">
            <label>Foto da Avaria (opcional)</label>
            <input type="file" name="foto_avaria" accept="image/*" class="form-control">
        </div>

        <button type="button" class="btn btn-success" onclick="validarConferencia()">Salvar Conferência</button>

        <a href="{{ route('setores.conferencia.itens', $recebimento->id) }}" class="btn btn-secondary">Voltar</a>
    </form>
</div>
<!-- Overlay de Carregamento -->
<style>
    #overlay {
        position: fixed;
        top: 0; left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.6);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }

    #overlay .spinner {
        width: 80px;
        height: 80px;
        border: 8px solid #f3f3f3;
        border-top: 8px solid #3498db;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>


<script>
function validarConferencia() {
    const form = document.querySelector('form');
    const qtdEsperada = {{ $item->quantidade }};
    const qtdConferida = parseInt(document.querySelector('input[name="qtd_conferida"]').value);

    if (isNaN(qtdConferida)) {
        alert('Informe a quantidade conferida.');
        return;
    }

    if (qtdConferida !== qtdEsperada) {
        if (confirm('Este item está com divergência. Deseja realmente continuar?')) {
            form.submit();
        } else {
            // Cancelado pelo usuário
            return;
        }
    } else {
        form.submit();
    }
}
</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const forms = document.querySelectorAll('form');

        forms.forEach(function (form) {
            form.addEventListener('submit', function () {
                document.getElementById('overlay').style.display = 'flex';
            });
        });
    });
</script>

@endsection