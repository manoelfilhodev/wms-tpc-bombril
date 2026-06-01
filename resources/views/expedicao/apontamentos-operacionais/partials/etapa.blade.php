@php
    $inicioValor = $inicio ? \Carbon\Carbon::parse($inicio)->format('Y-m-d\TH:i') : '';
    $fimValor = $fim ? \Carbon\Carbon::parse($fim)->format('Y-m-d\TH:i') : '';
    $emAndamento = $inicio && ! $fim;
    $editId = 'edit-' . $etapa . '-' . preg_replace('/[^A-Za-z0-9_-]/', '-', (string) $programacao->fo);
    $statusTexto = $finalizado
        ? 'Finalizada'
        : ($emAndamento ? ($etapa === 'conferencia' ? 'Conferindo' : 'Carregando') : 'Pendente');
    $statusClasse = $finalizado ? 'ok' : ($emAndamento ? 'working' : '');
    $conferenciaFinalizada = $demanda?->conferencia_finalizada_em
        && \Carbon\Carbon::parse($demanda->conferencia_finalizada_em)->gte(\App\Models\Demanda::DATA_OPERACIONAL_MINIMA);
    $carregamentoBloqueado = $etapa === 'carregamento' && ! $conferenciaFinalizada;
@endphp

<div>
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
        <span class="exp-ops-pill {{ $statusClasse }}">
            {{ $statusTexto }}
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
                <button type="submit" class="btn btn-outline-light btn-sm w-100" @disabled($inicio || $carregamentoBloqueado)>
                    {{ $inicio ? 'Início lançado' : ($carregamentoBloqueado ? 'Aguard. conferência' : 'Início agora') }}
                </button>
            </form>

            <form method="POST" action="{{ route('expedicao.programacoes.apontamento-operacional.store', $programacao->fo) }}">
                @csrf
                <input type="hidden" name="etapa" value="{{ $etapa }}">
                <input type="hidden" name="acao" value="finalizar_agora">
                <button type="submit" class="btn btn-outline-success btn-sm w-100" @disabled($fim || $carregamentoBloqueado)>
                    {{ $fim ? 'Fim lançado' : ($carregamentoBloqueado ? 'Aguard. conferência' : 'Fim agora') }}
                </button>
            </form>
        </div>

        @if ($carregamentoBloqueado)
            <div class="exp-ops-muted small mb-2">
                Carregamento liberado somente após finalizar a conferência.
            </div>
        @endif

        <div class="d-flex justify-content-end mb-2">
            <button class="btn btn-outline-warning btn-sm exp-ops-edit-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $editId }}" aria-expanded="false" aria-controls="{{ $editId }}">
                <i class="mdi mdi-pencil-outline me-1"></i> Editar horários
            </button>
        </div>

        <form method="POST" action="{{ route('expedicao.programacoes.apontamento-operacional.store', $programacao->fo) }}" class="collapse exp-ops-edit-panel" id="{{ $editId }}">
            @csrf
            <input type="hidden" name="etapa" value="{{ $etapa }}">
            <input type="hidden" name="acao" value="salvar_manual">

            <div class="exp-ops-action-row">
                <input type="datetime-local" name="inicio" class="form-control form-control-sm" value="{{ $inicioValor }}" aria-label="Início {{ $label }}">
                <input type="datetime-local" name="fim" class="form-control form-control-sm" value="{{ $fimValor }}" aria-label="Fim {{ $label }}">
            </div>

            <button type="submit" class="btn btn-danger btn-sm w-100">
                Salvar edição de {{ $label }}
            </button>
        </form>
    @else
        <div class="exp-ops-muted small">
            Aguardando importação da explosão operacional.
        </div>
    @endif
</div>
