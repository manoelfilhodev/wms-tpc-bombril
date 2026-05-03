@extends($layout)

@section('content')
<div class="container-fluid">
    <h4 class="mb-4">Programar Transferência</h4>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('transferencia.storeProgramacao') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="codigo_material" class="form-label">SKU</label>
                    <input type="text" name="codigo_material" class="form-control" placeholder="Digite o SKU" required>
                </div>

                <div class="mb-3">
                    <label for="quantidade_programada" class="form-label">Quantidade Programada</label>
                    <input type="number" name="quantidade_programada" class="form-control" min="1" required>
                </div>

                <div class="mb-3">
                    <label for="data_transferencia" class="form-label">Data</label>
                    <input type="date" name="data_transferencia" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-success">
                    <i class="mdi mdi-check"></i> Salvar Programação
                </button>
                <a href="{{ route('transferencia.index') }}" class="btn btn-secondary">
                    <i class="mdi mdi-arrow-left"></i> Voltar
                </a>
            </form>
        </div>
    </div>
</div>
@endsection
