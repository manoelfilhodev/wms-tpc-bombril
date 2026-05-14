@php
    $inicioValor = $inicio ? \Carbon\Carbon::parse($inicio)->format('Y-m-d\TH:i') : '';
    $fimValor = $fim ? \Carbon\Carbon::parse($fim)->format('Y-m-d\TH:i') : '';
@endphp

<div>
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
        <span class="exp-ops-pill {{ $finalizado ? 'ok' : '' }}">
            {{ $finalizado ? 'Finalizada' : 'Pendente' }}
        </span>
        <span class="exp-ops-muted small">
            {{ $inicio ? \Carbon\Carbon::parse($inicio)->format('d/m H:i') : '--/-- --:--' }}
            →
            {{ $fim ? \Carbon\Carbon::parse($fim)->format('d/m H:i') : '--/-- --:--' }}
        </span>
    </div>

    @if ($demanda)
        <div class="exp-ops-action-row">
            <form method="POST" action="{{ route('expedicao.programacoes.apontamento-operacional.store', $programacao->fo) }}">
                @csrf
                <input type="hidden" name="etapa" value="{{ $etapa }}">
                <input type="hidden" name="acao" value="iniciar_agora">
                <button type="submit" class="btn btn-outline-light btn-sm w-100">Início agora</button>
            </form>

            <form method="POST" action="{{ route('expedicao.programacoes.apontamento-operacional.store', $programacao->fo) }}">
                @csrf
                <input type="hidden" name="etapa" value="{{ $etapa }}">
                <input type="hidden" name="acao" value="finalizar_agora">
                <button type="submit" class="btn btn-outline-success btn-sm w-100">Fim agora</button>
            </form>
        </div>

        <form method="POST" action="{{ route('expedicao.programacoes.apontamento-operacional.store', $programacao->fo) }}">
            @csrf
            <input type="hidden" name="etapa" value="{{ $etapa }}">
            <input type="hidden" name="acao" value="salvar_manual">

            <div class="exp-ops-action-row">
                <input type="datetime-local" name="inicio" class="form-control form-control-sm" value="{{ $inicioValor }}" aria-label="Início {{ $label }}">
                <input type="datetime-local" name="fim" class="form-control form-control-sm" value="{{ $fimValor }}" aria-label="Fim {{ $label }}">
            </div>

            <button type="submit" class="btn btn-danger btn-sm w-100">Salvar {{ $label }}</button>
        </form>
    @else
        <div class="exp-ops-muted small">
            Aguardando importação da explosão operacional.
        </div>
    @endif
</div>
