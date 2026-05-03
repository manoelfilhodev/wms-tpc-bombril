@extends($layout)
@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Separação de Produto</h4>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('separacoes.store') }}">
        @csrf

        <input type="hidden" name="pedido_id" value="{{ $item->pedido_id }}">
        <input type="hidden" name="sku" value="{{ $item->sku }}">
        <input type="hidden" name="posicao" value="{{ $posicao->codigo_posicao ?? 'N/A' }}">
        <input type="hidden" name="fo" value="{{ $item->fo }}">
        <input type="hidden" name="unidade_id" value="{{ Auth::user()->unidade_id }}">
        <input type="hidden" name="usuario_id" value="{{ Auth::id() }}">

        <div class="row mb-2">
            <div class="col-12 mb-3">
                <input type="text" class="form-control text-uppercase" value="{{ $item->sku }}" readonly>
                <small class="form-text text-muted mt-1" style="font-weight: bold;">SKU do produto</small>
            </div>

            <div class="col-12 mb-3">
                <input type="number" name="quantidade_separada" class="form-control" placeholder="Quantidade Separada" max="{{ $item->quantidade }}" required>
                <small class="form-text text-muted mt-1" style="font-weight: bold;">Quantidade esperada: {{ $item->quantidade }}</small>
            </div>

            <div class="col-12 mb-3">
                <input type="text" class="form-control text-uppercase" value="{{ $posicao->codigo_posicao ?? 'POSIÇÃO NÃO ENCONTRADA' }}" readonly>
                <small class="form-text text-muted mt-1" style="font-weight: bold;">Posição sugerida</small>
            </div>
        </div>

        <div class="mb-3">
            <textarea name="observacoes" class="form-control text-uppercase" placeholder="Observações (opcional)"></textarea>
        </div>

        <button type="submit" class="btn btn-success w-100">Confirmar Separação</button>
    </form>

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
</div>
@endsection
