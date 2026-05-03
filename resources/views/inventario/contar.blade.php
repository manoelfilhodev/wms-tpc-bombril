@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-4">Contagem - Inventário #{{ $idInventario }}</h4>
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
    </div> 
@endif

    <div class="mb-3">
        <span class="badge bg-primary">Total de SKUs: {{ count($itensComPosicao) + count($itensSemPosicao) }}</span>
        <span class="badge bg-success">Com Posição: {{ count($itensComPosicao) }}</span>
        <span class="badge bg-warning text-dark">Sem Posição: {{ count($itensSemPosicao) }}</span>
    </div>

    <div class="table-responsive">
        <table class="table table-sm table-bordered table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Posição</th>
                    <th>SKU</th>
                    <th>Descrição</th>
                    <th>Físico</th>
                    <th>Status</th>
                    <th style="min-width: 120px;">Ação</th>
                </tr>
            </thead>
            <tbody>
                @foreach($itensComPosicao as $item)
                <tr>
                    <td>{{ strtoupper($item->posicao) }}</td>
                    <td>{{ strtoupper($item->sku) }}</td>
                    <td>{{ $item->descricao }}</td>
                    <td>{{ $item->quantidade_fisica ?? '-' }}</td>
                    <td>
                        @if($item->quantidade_fisica !== null)
                            <span class="badge bg-success">Contado</span>
                        @else
                            <span class="badge bg-secondary">Pendente</span>
                        @endif
                    </td>
                    <td>
                        @if($item->quantidade_fisica !== null)
                            <button class="btn btn-outline-info btn-sm w-100"
                                data-bs-toggle="modal"
                                data-bs-target="#detalhesModal_{{ $item->id }}">
                                Detalhes
                            </button>
                        @else
                            <a href="{{ url("/inventario/contar/$idInventario/$item->id") }}"
                               class="btn btn-outline-primary btn-sm w-100"
                               style="min-width: 100px;">
                               Contar
                            </a>
                        @endif
                    </td>
                </tr>
                @endforeach

                @foreach($itensSemPosicao as $item)
                <tr>
                    <td><span class="text-muted">-</span></td>
                    <td>{{ strtoupper($item->sku) }}</td>
                    <td>{{ $item->descricao }}</td>
                    <td>{{ $item->quantidade_fisica ?? '-' }}</td>
                    <td>
                        @if($item->quantidade_fisica !== null)
                            <span class="badge bg-success">Contado</span>
                        @else
                            <span class="badge bg-secondary">Pendente</span>
                        @endif
                    </td>
                    <td>
                        @if($item->quantidade_fisica !== null)
                            <button class="btn btn-outline-info btn-sm w-100"
                                data-bs-toggle="modal"
                                data-bs-target="#detalhesModal_{{ $item->id }}">
                                Detalhes
                            </button>
                        @else
                            <a href="{{ url("/inventario/contar/$idInventario/$item->id") }}"
                               class="btn btn-warning btn-sm w-100"
                               style="min-width: 100px;">
                               Mapear & Contar
                            </a>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Modais de detalhes --}}
@foreach(array_merge($itensComPosicao->all(), $itensSemPosicao->all()) as $item)
    @if($item->quantidade_fisica !== null)
        <div class="modal fade" id="detalhesModal_{{ $item->id }}" tabindex="-1" aria-labelledby="detalhesLabel_{{ $item->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="detalhesLabel_{{ $item->id }}">Detalhes da Contagem</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>
                    <div class="modal-body">
                        <p><strong>SKU:</strong> {{ $item->sku }}</p>
                        <p><strong>Descrição:</strong> {{ $item->descricao }}</p>
                        <p><strong>Posição:</strong> {{ $item->posicao ?? '-' }}</p>
                        <p><strong>Quantidade Contada:</strong> {{ $item->quantidade_fisica }}</p>
                        <p><strong>Usuário:</strong> {{ $item->nome_usuario ?? 'N/D' }}</p>
                        <p><strong>Data/Hora:</strong> {{ \Carbon\Carbon::parse($item->updated_at)->format('d/m/Y H:i') }}</p>
                    </div>
                    <div class="modal-footer">
                        <a href="{{ url("/inventario/contar/$idInventario/$item->id") }}" class="btn btn-warning">Editar</a>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endforeach

@endsection
