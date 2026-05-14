@props([
    'titulo',
    'previsto' => null,
    'realizado' => null,
    'desvio' => null,
    'status' => 'SEM_REALIZADO',
])

@php
    $statusClass = match ($status) {
        'DENTRO_PREVISTO' => 'op-card-ok',
        'FORA_PREVISTO' => 'op-card-danger',
        default => 'op-card-pending',
    };

    
@endphp

<div class="op-etapa-card {{ $statusClass }}">
    <div class="op-etapa-title">{{ $titulo }}</div>

    <div class="op-etapa-grid">
        <div>
            <small>Previsto</small>
            <strong>{{ $previsto !== null ? floor(abs($previsto) / 60) . ':' . str_pad(abs($previsto) % 60, 2, '0', STR_PAD_LEFT) . ':00' : '-' }}</strong>
        </div>

        <div>
            <small>Realizado</small>
            <strong>{{ $realizado !== null ? floor(abs($realizado) / 60) . ':' . str_pad(abs($realizado) % 60, 2, '0', STR_PAD_LEFT) . ':00' : '-' }}</strong>
        </div>
    </div>

    <div class="mt-2">
        @if ($status === 'FORA_PREVISTO')
            <span class="badge bg-danger">
                ++{{ floor(abs($desvio) / 60) . ':' . str_pad(abs($desvio) % 60, 2, '0', STR_PAD_LEFT) . ':00' }} atraso
            </span>
        @elseif ($status === 'DENTRO_PREVISTO')
            <span class="badge bg-success">
                Dentro do previsto
            </span>
        @else
            <span class="badge bg-secondary">
                Pendente
            </span>
        @endif
    </div>
</div>