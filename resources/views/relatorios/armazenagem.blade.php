@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Relatório de Armazenagem</h4>
        <div>
            <a href="{{ route('relatorios.armazenagem.excel', request()->query()) }}" class="btn btn-success btn-sm me-2">
                <i class="mdi mdi-file-excel"></i> Exportar Excel
            </a>
            <a href="{{ route('relatorios.armazenagem.pdf', request()->query()) }}" class="btn btn-danger btn-sm">
                <i class="mdi mdi-file-pdf"></i> Exportar PDF
            </a>
        </div>
    </div>

    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-3">
            <label>Data Início</label>
            <input type="date" name="data_inicio" class="form-control" value="{{ request('data_inicio') }}">
        </div>
        <div class="col-md-3">
            <label>Data Fim</label>
            <input type="date" name="data_fim" class="form-control" value="{{ request('data_fim') }}">
        </div>
        <div class="col-md-3">
            <label>Unidade</label>
            <select name="unidade_id" class="form-control">
                <option value="">Todas</option>
                @foreach ($unidades as $unidade)
                    <option value="{{ $unidade->id }}" {{ request('unidade_id') == $unidade->id ? 'selected' : '' }}>
                        {{ $unidade->nome }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label>Usuário</label>
            <select name="usuario_id" class="form-control">
                <option value="">Todos</option>
                @foreach ($usuarios as $usuario)
                    <option value="{{ $usuario->id_user }}" {{ request('usuario_id') == $usuario->id_user ? 'selected' : '' }}>
                        {{ $usuario->nome }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-12 align-self-end">
            <button class="btn btn-primary w-100">
                <i class="mdi mdi-filter"></i> Filtrar
            </button>
        </div>
    </form>

    <!--<div class="card mb-4">-->
    <!--    <div class="card-body">-->
    <!--        <canvas id="graficoArmazenagem" height="100"></canvas>-->
    <!--    </div>-->
    <!--</div>-->

    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="table-light">
                <tr>
                    <th>Data</th>
                    <th>Usuário</th>
                    <th>Unidade</th>
                    <th>SKU</th>
                    <th>Posição</th>
                    <th>Quantidade</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($armazenagem as $item)
                    <tr>
                        <td>{{ date('d/m/Y', strtotime($item->data_armazenagem)) }}</td>
                        <td>{{ mb_strtoupper($item->usuario_nome) }}</td>
                        <td>{{ $item->unidade_nome }}</td>
                        <td>{{ mb_strtoupper($item->sku) }}</td>
                        <td>{{ mb_strtoupper($item->endereco) }}</td>
                        <td>{{ $item->quantidade }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">Nenhum dado encontrado.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div> 


</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('graficoArmazenagem').getContext('2d');
    const chart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($grafico->pluck('nome')) !!},
            datasets: [{
                label: 'Total de Armazenagens',
                data: {!! json_encode($grafico->pluck('total')) !!},
                backgroundColor: 'rgba(75, 192, 192, 0.7)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>
@endpush
