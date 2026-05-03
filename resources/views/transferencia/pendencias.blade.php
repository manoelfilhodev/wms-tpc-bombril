@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-3">Pendências de Transferência</h4>

    @if($pendencias->isEmpty())
        <div class="alert alert-success">Nenhuma pendência encontrada 🎉</div>
    @else
        <table class="table table-sm table-striped">
            <thead>
                <tr>
                    <th>Palete UID</th>
                    <th>Cód. Material</th>
                    <th>Qtd</th>
                    <th>Status</th>
                    <th>Data</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pendencias as $p)
                    <tr>
                        <td>{{ $p->palete_uid }}</td>
                        <td>{{ $p->codigo_material }}</td>
                        <td>{{ $p->quantidade }}</td>
                        <td><span class="badge bg-warning text-dark">{{ $p->status }}</span></td>
                        <td>{{ \Carbon\Carbon::parse($p->data)->format('d/m/Y') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
