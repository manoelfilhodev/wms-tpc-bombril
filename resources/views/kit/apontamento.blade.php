@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h4 class="mb-4">Apontamento de Produção de Kits</h4>

    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-body"> 

            <form method="POST" action="{{ route('kit.store') }}">
                @csrf
@if(session('warning'))
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        {{ session('warning') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
                <div class="mb-3">
                    <label for="codigo_material" class="form-label">Código do Kit</label>
                    <input type="text" autocapitalize="off" name="codigo_material" id="codigo_material" class="form-control text-uppercase" required autocomplete="off">
                    <small id="descricao" class="form-text text-muted mt-1" style="font-weight: bold; text-transform: uppercase;"></small>
                    <small id="sku-error" class="text-danger d-none">Código não encontrado no sistema.</small>
                </div>


                <div class="mb-3">
                    <label for="quantidade" class="form-label">Quantidade Montada</label>
                    <input type="number" name="quantidade" id="quantidade" class="form-control" required min="1">
                </div>

                <div class="mb-3">
                    <label for="data_montagem" class="form-label">Data da Montagem</label>
                    <input type="date" name="data_montagem" id="data_montagem" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>

                <div class="mb-3">
                    <label for="observacao" class="form-label">Observação (opcional)</label>
                    <textarea name="observacao" id="observacao" class="form-control" rows="3" placeholder="Ex: Kit reprocessado, faltando item X, etc."></textarea>
                </div>

                <button type="submit" class="btn btn-success">Salvar Apontamento</button>
            </form>

        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
let skusValidos = [];

$(document).ready(function () {
    $('#codigo_material').on('input', function () {
        const input = $(this).val();

        if (input.length >= 2) {
            $.get("{{ route('kit.buscarSkus') }}", { term: input }, function (data) {
                skusValidos = data;

                if (data.includes(input)) {
                    $('#sku-error').addClass('d-none');
                    $('button[type="submit"]').prop('disabled', false);
                } else {
                    $('#sku-error').removeClass('d-none');
                    $('button[type="submit"]').prop('disabled', true);
                }
            });

            $.get("{{ route('kit.buscarDescricao') }}", { sku: input }, function (data) {
                $('#descricao').text(data.descricao.toUpperCase());
            }).fail(function () {
                $('#descricao').text('');
            });
        } else {
            $('#sku-error').addClass('d-none');
            $('button[type="submit"]').prop('disabled', false);
        }
    });
});
</script>

@endsection