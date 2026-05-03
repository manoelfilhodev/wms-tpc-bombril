@extends($layout)
@section('content')
<div class="container">
    <h4 class="mb-4">Separar Produto do Pedido #PED - {{ $item->fo }}</h4>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <p><strong>SKU:</strong> {{ $item->sku }}</p>
            <p><strong>Quantidade a Separar:</strong> {{ $item->quantidade }}</p>
            <p><strong>Posição:</strong> {{ isset($posicao) ? mb_strtoupper($posicao->codigo_posicao) : 'Não encontrado' }}</p>

            <p><strong>FO:</strong> {{ $item->fo }}</p>
        </div>
    </div>

    <form method="POST" action="{{ route('separacoes.executar', $item->id) }}">
        @csrf

        <div class="mb-3">
            <label for="quantidade_separada">Quantidade Separada</label>
            <input type="number" name="quantidade_separada" id="quantidade_separada" class="form-control" placeholder="Informe quanto foi separado" max="{{ $item->quantidade }}" required>
        </div>

        <button type="submit" class="btn btn-success w-100">Confirmar Separação</button>
    </form>
    <a href="#" 
   class="btn btn-secondary w-100 mt-2"
   onclick="confirmarPular('{{ route('separacoes.pular', $item->id) }}')">
   Pular Posição
</a>
</div>
@endsection
@section('scripts')
<script>
function confirmarPular(url) {
    if (confirm('Tem certeza que deseja pular esta posição?')) {
        window.location.href = url;
    }
}
</script>
@endsection

