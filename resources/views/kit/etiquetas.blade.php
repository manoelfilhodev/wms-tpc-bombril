@extends('layouts.app')

@section('content')
<div class="container-fluid">
  <h4 class="mb-3">Etiquetas de Produção — {{ $kit->codigo_material }} ({{ $kit->data_montagem }})</h4>

  {{-- Botão imprimir todas --}}
  <form method="POST" action="{{ route('kits.etiquetas.preview', $kit->id) }}" class="mb-3">
    @csrf
    {{-- truque: enviar todos os IDs como hidden quando clicar em imprimir todas --}}
    @foreach($etiquetas as $e)
      <input type="hidden" name="ids[]" value="{{ $e->id }}">
    @endforeach
    <button class="btn btn-primary"><i class="bi bi-printer"></i> Imprimir todas</button>
  </form>

  {{-- Formulário imprimir selecionadas --}}
  <form method="POST" action="{{ route('kits.etiquetas.preview', $kit->id) }}">
    @csrf
    <table class="table table-sm">
      <thead>
        <tr>
          <th><input type="checkbox" id="checkAll"></th>
          <th>#</th>
          <th>Código</th>
          <th>Qtd</th>
          <th>UID</th>
        </tr>
      </thead>
      <tbody>
        @foreach($etiquetas as $i => $e)
          <tr>
            <td><input type="checkbox" name="ids[]" value="{{ $e->id }}"></td>
            <td>{{ $i+1 }}</td>
            <td>{{ $e->codigo_material }}</td>
            <td>{{ $e->quantidade }}</td>
            <td>{{ $e->palete_uid }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
    <button class="btn btn-success mt-3"><i class="bi bi-printer"></i> Imprimir selecionadas</button>
  </form>
</div>

<script>
document.getElementById('checkAll').addEventListener('change', function() {
  document.querySelectorAll('input[name="ids[]"]').forEach(ch => ch.checked = this.checked);
});
</script>
@endsection
