@extends($layout)

@section('content')
<div class="container-fluid">
    <h4 class="mb-4">
        Etiquetas de Produção — {{ $kit->codigo_material }} ({{ $kit->data_montagem }})
    </h4>

    {{-- Mensagens de sucesso/erro --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="mb-3">
        {{-- Botão imprimir todas --}}
        <form method="POST" action="{{ route('kits.etiquetas.imprimir_tudo', $kit->id) }}" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-printer"></i> Imprimir todas
            </button>
        </form>
    </div>

    {{-- Tabela de etiquetas --}}
    <form method="POST" action="{{ route('kits.etiquetas.imprimir_selecionadas', $kit->id) }}">
        @csrf
        <table class="table table-striped table-hover align-middle">
            <thead>
                <tr>
                    <th><input type="checkbox" id="checkAll"></th>
                    <th>#</th>
                    <th>Código</th>
                    <th>Qtd</th>
                    <th>UID</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($etiquetas as $i => $e)
                    <tr>
                        <td>
                            <input type="checkbox" name="ids[]" value="{{ $e->id }}">
                        </td>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $e->codigo_material }}</td>
                        <td>{{ $e->quantidade }}</td>
                        <td>{{ $e->palete_uid }}</td>
                        <td>
                            <a href="{{ route('kits.etiquetas.reimprimir', $e->id) }}"
                               target="_blank" class="btn btn-sm btn-outline-dark">
                                <i class="bi bi-upc"></i> Reimprimir
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">
                            Nenhuma etiqueta gerada para este kit.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Botão imprimir selecionadas --}}
        <button type="submit" class="btn btn-success">
            <i class="bi bi-printer"></i> Imprimir selecionadas
        </button>
    </form>
</div>

{{-- Script para selecionar todos os checkboxes --}}
<script>
    document.getElementById('checkAll').addEventListener('click', function(e) {
        let checkboxes = document.querySelectorAll('input[name="ids[]"]');
        checkboxes.forEach(cb => cb.checked = e.target.checked);
    });
</script>
@endsection
