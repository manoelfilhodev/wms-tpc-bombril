@props([
    'prevista' => null,
    'projetada' => null,
    'status' => null,
    'desvio' => null,
])

@php
    

    $cardClass = $status === 'FORA_PREVISTO'
        ? 'op-saida-danger'
        : 'op-saida-ok';
@endphp

<div class="op-saida-card {{ $cardClass }}">
    <div>
        <small>Saída prevista</small>
        <strong>{{ $prevista ? $prevista->format('d/m/Y H:i') : '-' }}</strong>
    </div>

    <div>
        <small>Saída projetada</small>
        <strong>{{ $projetada ? $projetada->format('d/m/Y H:i') : '-' }}</strong>
    </div>

    <div class="mt-2">
        @if ($status === 'FORA_PREVISTO')
            <span class="badge bg-danger">
                Impactada · {{ floor(abs($desvio) / 60) . ':' . str_pad(abs($desvio) % 60, 2, '0', STR_PAD_LEFT) . ':00' }}
            </span>
        @else
            <span class="badge bg-success">
                No prazo
            </span>
        @endif
    </div>
</div>