@extends($layout)

@section('content')
<div class="container-fluid">
    <h4 class="mb-4">Pré-visualização das Etiquetas de Produção</h4>

    <div class="mb-3">
        <button onclick="window.print()" class="btn btn-primary">
            <i class="bi bi-printer"></i> Imprimir (Ctrl+P)
        </button>
        <a href="{{ url()->previous() }}" class="btn btn-secondary">
            Voltar
        </a>
    </div>

    <div class="row">
        @forelse($imagens as $img)
            <div class="col-12 mb-4 d-flex justify-content-center">
                <div class="border p-3" style="background:#fff;">
                    <img src="{{ $img }}" class="img-fluid" style="max-width:400px;">
                </div>
            </div>
        @empty
            <p class="text-muted">Nenhuma etiqueta disponível.</p>
        @endforelse
    </div>
</div>
@endsection
