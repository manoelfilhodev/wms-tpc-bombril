@extends($layout)

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Separação Manual de Componentes para Kit</h4>
    </div>

    @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
    @endif

    <form method="POST" action="{{ route('separacoes.salvarLinhaManual') }}">
        @csrf
            <div class="mb-3">
  
                <input type="text"  autocapitalize="off" placeholder="Código do Item Kit" name="pedido" id="pedido" class="form-control text-uppercase" required>
                <small id="pedido-descricao" class="form-text text-muted mt-1" style="font-weight: bold; text-transform: uppercase;"></small>
                <small id="pedido-error" class="text-danger d-none">Produto não encontrado no sistema.</small>
            </div>

        
            <div class="col-12 mb-3">
                <input type="text" autocapitalize="off" name="sku" id="sku" class="form-control text-uppercase" placeholder="Código do Produto" autocomplete="off" required>
                <small id="descricao" class="form-text text-muted mt-1" style="font-weight: bold; text-transform: uppercase;"></small>
                <small id="sku-error" class="text-danger d-none">Produto não encontrado no sistema.</small>
            </div>

            <div class="col-12 mb-3">
                <input type="tel" name="quantidade" class="form-control" placeholder="QTD SEPARADA" required>
            </div>

            <div class="col-12 mb-3">
                <input type="text" autocapitalize="off" name="endereco" id="endereco" class="form-control text-uppercase" placeholder="Endereço de Separação" autocomplete="off" required>
                <small id="posicao-info" class="form-text text-muted mt-1" style="font-weight: bold; text-transform: uppercase;"></small>
                <small id="posicao-error" class="text-danger d-none">Posição não encontrada no sistema.</small>
            </div>
       

        <div class="mb-3">
            <textarea name="observacoes" class="form-control text-uppercase" placeholder="Observações"></textarea>
        </div>

        <button type="submit" class="btn btn-success w-100">Salvar Separação</button>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
let skusValidos = [];

$(document).ready(function () {
    $('#sku').on('input', function () {
        const input = $(this).val();

        if (input.length >= 2) {
            $.get("{{ route('armazenagem.buscarSkus') }}", { term: input }, function (data) {
                skusValidos = data;

                if (data.includes(input)) {
                    $('#sku-error').addClass('d-none');
                    $('button[type="submit"]').prop('disabled', false);
                } else {
                    $('#sku-error').removeClass('d-none');
                    $('button[type="submit"]').prop('disabled', true);
                }
            });

            $.get("{{ route('armazenagem.buscarDescricao') }}", { sku: input }, function (data) {
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

let posicoesValidas = [];

$('#endereco').on('input', function () {
    const input = $(this).val();

    if (input.length >= 2) {
        $.get("{{ route('armazenagem.buscarPosicoes') }}", { term: input }, function (data) {
            posicoesValidas = data;

            if (data.includes(input)) {
                $('#posicao-error').addClass('d-none');
                $('#posicao-info').text('✅ Posição válida.').css('color', 'green');
                $('button[type="submit"]').prop('disabled', false);
            } else {
                $('#posicao-error').removeClass('d-none');
                $('#posicao-info').text('❌ Posição não encontrada.').css('color', 'red');
                $('button[type="submit"]').prop('disabled', true);
            }
        });
    } else {
        $('#posicao-error').addClass('d-none');
        $('#posicao-info').text('');
        $('button[type="submit"]').prop('disabled', false);
    }
});

let pedidosValidos = [];

$('#pedido').on('input', function () {
    const input = $(this).val();

    if (input.length >= 2) {
        $.get("{{ route('armazenagem.buscarSkus') }}", { term: input }, function (data) {
            pedidosValidos = data;

            if (data.includes(input)) {
                $('#pedido-error').addClass('d-none');
                $('button[type="submit"]').prop('disabled', false);
            } else {
                $('#pedido-error').removeClass('d-none');
                $('button[type="submit"]').prop('disabled', true);
            }
        });

        $.get("{{ route('armazenagem.buscarDescricao') }}", { sku: input }, function (data) {
            $('#pedido-descricao').text(data.descricao.toUpperCase());
        }).fail(function () {
            $('#pedido-descricao').text('');
        });

    } else {
        $('#pedido-error').addClass('d-none');
        $('#pedido-descricao').text('');
        $('button[type="submit"]').prop('disabled', false);
    }
});


</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const alert = document.querySelector('.alert-success');
    if (alert) {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = 0;
            setTimeout(() => alert.remove(), 500);
        }, 3000);
    }
});
</script>
@endsection
