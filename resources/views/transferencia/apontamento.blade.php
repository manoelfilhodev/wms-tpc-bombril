@extends('layouts.app')


@section('content')
<div class="container">
    <h4 class="mb-3 d-flex align-items-center justify-content-between">
        <span>Apontamento de Transferências</span>
        
    </h4>
    

    {{-- Mensagens de retorno --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            {{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Form de apontamento --}}
    <form method="POST" action="{{ route('transferencia.apontar') }}">
        @csrf
        <div class="mb-3">
            <label for="palete_uid" class="form-label">Escaneie o QR Code ou digite o código</label>
            <input type="text" name="palete_uid" id="palete_uid"
                   class="form-control" autofocus required>
        </div>
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-circle"></i> Apontar
        </button>
    </form>

    {{-- Últimos apontamentos --}}
    <hr>
    <h5 class="mt-4">Últimos Apontamentos</h5>
    <table class="table table-sm table-striped">
        <thead>
            <tr>
                <th>Palete UID</th>
                <th>Cód. Material</th>
                <th>Qtd</th>
                <th>Status</th>
                <th>Apontado Por</th>
                <th>Atualizado em</th>
            </tr>
        </thead>
        <tbody>
            @forelse($apontamentos as $a)
                <tr>
                    <td>{{ $a->palete_uid }}</td>
                    <td>{{ $a->codigo_material }}</td>
                    <td>{{ $a->quantidade }}</td>
                    <td>
                        <span class="badge bg-{{ $a->status == 'APONTADO' ? 'success' : 'secondary' }}">
                            {{ $a->status }}
                        </span>
                    </td>
                    <td>{{ mb_strtoupper($a->apontadoPor->nome ?? '-') }}</td>
                    <td>{{ \Carbon\Carbon::parse($a->updated_at)->format('d/m/Y H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">Nenhum apontamento realizado ainda.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    {{-- Resumo por SKU --}}
@if($skuResumo->count())
    <div class="card my-4">
        <div class="card-header bg-light">
            <strong>Resumo de Apontamentos por SKU (Hoje)</strong>
        </div>
        <div class="card-body p-0">
            <table class="table table-sm table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th>SKU</th>
                        <th>Quantidade Total</th>
                        <th>Etiquetas (Paletes)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($skuResumo as $item)
                        <tr>
                            <td>{{ $item->sku }}</td>
                            <td>{{ $item->total_quantidade }}</td>
                            <td>{{ $item->total_etiquetas }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <th>Total Geral</th>
                        <th>{{ $totalGeral['quantidade'] }}</th>
                        <th>{{ $totalGeral['etiquetas'] }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
@endif
</div>

@endsection
