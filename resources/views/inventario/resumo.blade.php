@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-4">Resumo da Contagem - Inventário #{{ $id }}</h4>

    <div class="mb-3">
        <span class="badge bg-primary">Total de SKUs: {{ $total }}</span>
        <span class="badge bg-success">Contados: {{ $contados }}</span>
        <span class="badge bg-warning text-dark">Faltando: {{ $faltantes }}</span>
    </div>

    <div class="mb-3 d-flex gap-2">
        <a href="{{ route('inventario.exportar.excel', $id) }}" class="btn btn-outline-success">
            <i class="uil-file-exclamation"></i> Exportar Excel
        </a>
        <a href="{{ route('inventario.exportar.pdf', $id) }}" class="btn btn-outline-danger">
            <i class="uil-file"></i> Gerar PDF
        </a>
    </div>

    <table class="table table-sm table-bordered table-hover">
        <thead class="table-dark">
            <tr>
                <th>SKU</th>
                <th>Descrição</th>
                <th>Posição</th>
                <th>Sistema</th>
                <th>Físico</th>
                <th>Tipo de Ajuste</th>
                <th>Necessita Ajuste?</th>
                <th>Contado por</th>
            </tr>
        </thead>
        <tbody>
            @foreach($itens as $item)
                <tr class="{{ $item->necessita_ajuste ? 'table-warning' : 'table-success' }}">
                    <td>{{ $item->sku }}</td>
                    <td>{{ $item->descricao }}</td>
                    <td>{{ $item->posicao }}</td>
                    <td>{{ $item->quantidade_sistema }}</td>
                    <td>{{ $item->quantidade_fisica }}</td>
                    <td>{{ ucfirst($item->tipo_ajuste) }}</td>
                    <td>{{ $item->necessita_ajuste ? 'Sim' : 'Não' }}</td>
                    <td>
                        @php
                            $nome = optional(DB::table('_tb_usuarios')->where('id_user', $item->contado_por)->first())->nome;
                        @endphp
                        {{ $nome ?? '-' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
