@extends('layouts.app')

@section('content')
<div class="container-fluid">
  <h4 class="mb-3">Programações de Kits — Etiquetas</h4>

  <div class="card">
    <div class="card-body p-0">
      <table class="table table-sm table-hover">
        <thead>
          <tr>
            <th>ID</th>
            <th>Material</th>
            <th>Qtd Programada</th>
            <th>Qtd Produzida</th>
            <th>Data</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody>
          @foreach($programacoes as $p)
            <tr>
              <td>{{ $p->id }}</td>
              <td>{{ $p->codigo_material }}</td>
              <td>{{ $p->quantidade_programada }}</td>
              <td>{{ $p->quantidade_produzida ?? 0 }}</td>
              <td>{{ $p->data_montagem }}</td>
              <td>
                <a href="{{ route('kits.etiquetas.index', $p->id) }}" class="btn btn-sm btn-outline-dark">
                  <i class="bi bi-upc"></i> Ver Etiquetas
                </a>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
