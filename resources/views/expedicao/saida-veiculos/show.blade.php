@extends('layouts.app')

@section('title', 'Timeline da DT')

@section('content')
    <style>
        .timeline-page { color: #f8fafc; }
        .timeline-panel {
            background: rgba(12, 16, 24, .94);
            border: 1px solid rgba(255, 255, 255, .10);
            border-radius: 8px;
            box-shadow: 0 18px 45px rgba(0, 0, 0, .24);
        }
        .timeline-muted { color: #a8b3c7; }
        .timeline-page .form-control {
            background: rgba(255, 255, 255, .06);
            border-color: rgba(255, 255, 255, .16);
            color: #fff;
        }
        .timeline {
            position: relative;
            display: grid;
            gap: 14px;
            padding-left: 20px;
        }
        .timeline::before {
            content: "";
            position: absolute;
            left: 8px;
            top: 8px;
            bottom: 8px;
            width: 2px;
            background: rgba(148, 163, 184, .22);
        }
        .timeline-item {
            position: relative;
            padding: 14px;
            border: 1px solid rgba(148, 163, 184, .16);
            border-radius: 8px;
            background: rgba(15, 23, 42, .70);
        }
        .timeline-item::before {
            content: "";
            position: absolute;
            left: -18px;
            top: 18px;
            width: 12px;
            height: 12px;
            border-radius: 999px;
            background: #64748b;
            box-shadow: 0 0 0 4px rgba(100, 116, 139, .16);
        }
        .timeline-item.done {
            border-color: rgba(34, 197, 94, .28);
            background: linear-gradient(135deg, rgba(21, 128, 61, .14), rgba(15, 23, 42, .72));
        }
        .timeline-item.done::before {
            background: #22c55e;
            box-shadow: 0 0 0 4px rgba(34, 197, 94, .16);
        }
        .timeline-title {
            color: #fff;
            font-size: 16px;
            font-weight: 900;
            text-transform: uppercase;
        }
        .timeline-date {
            color: #93c5fd;
            font-size: 14px;
            font-weight: 800;
        }
        .timeline-meta {
            color: #a8b3c7;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .timeline-dt {
            color: #fff;
            font-size: 30px;
            font-weight: 900;
            line-height: 1;
        }
        .icon-wrapper {
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0ea5e9 0%, #2563eb 100%);
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(14, 165, 233, .26);
        }
        .icon-wrapper i { color: #fff !important; }
    </style>

    <div class="timeline-page container-fluid px-4 py-3">
        @include('partials.breadcrumb-auto')

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div class="d-flex align-items-center">
                <div class="icon-wrapper me-3">
                    <i class="mdi mdi-timeline-clock-outline display-6"></i>
                </div>
                <div>
                    <h3 class="mb-1 fw-bold text-dark">Timeline da DT</h3>
                    <p class="text-muted mb-0 small">Histórico operacional completo até a saída do veículo</p>
                </div>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('expedicao.saida-veiculos.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="mdi mdi-arrow-left me-1"></i> Voltar
                </a>
                <a href="{{ route('expedicao.previsibilidade.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="mdi mdi-monitor-dashboard me-1"></i> Painel
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row g-3">
            <div class="col-xl-4">
                <div class="timeline-panel p-3 mb-3">
                    <div class="timeline-muted small text-uppercase fw-bold">DT</div>
                    <div class="timeline-dt">{{ $programacao->dt_sap ?: $programacao->fo }}</div>
                    <div class="timeline-muted mt-2">FO {{ $programacao->fo }}</div>
                    <hr class="border-secondary">
                    <div class="text-white fw-bold">{{ $programacao->cidade_destino }}/{{ $programacao->uf_destino }}</div>
                    <div class="timeline-muted small">{{ $programacao->cliente ?? '-' }}</div>
                    <div class="timeline-muted small mt-2">Tipo: {{ $programacao->tipo_demanda_label }}</div>
                    <div class="timeline-muted small">Agenda: {{ optional($programacao->agenda_entrega_em)->format('d/m/Y H:i') ?? '-' }}</div>
                </div>

                <div class="timeline-panel p-3" id="editar-saida">
                    <h5 class="text-white fw-bold mb-3">Saída do veículo</h5>

                    @if (! $demanda->saida_veiculo_em)
                        <form method="POST" action="{{ route('expedicao.programacoes.saida-veiculo.store', $programacao->fo) }}">
                            @csrf
                            <label class="form-label timeline-muted">Observação</label>
                            <input type="text" name="observacao" class="form-control mb-3" maxlength="255" placeholder="Opcional">
                            <button type="submit" class="btn btn-success w-100">
                                <i class="mdi mdi-check-decagram-outline me-1"></i> Registrar saída agora
                            </button>
                        </form>
                    @else
                        <div class="alert alert-success">
                            Saída registrada em {{ $demanda->saida_veiculo_em->format('d/m/Y H:i') }}.
                        </div>

                        @if ($podeEditar)
                            <form method="POST" action="{{ route('expedicao.programacoes.saida-veiculo.update', $programacao->fo) }}">
                                @csrf
                                @method('PATCH')
                                <label class="form-label timeline-muted">Data/hora da saída</label>
                                <input type="datetime-local" name="saida_veiculo_em" class="form-control mb-3" value="{{ $demanda->saida_veiculo_em->format('Y-m-d\TH:i') }}" required>
                                <label class="form-label timeline-muted">Observação</label>
                                <input type="text" name="observacao" class="form-control mb-3" maxlength="255" value="{{ $demanda->saida_veiculo_observacao }}">
                                <button type="submit" class="btn btn-warning w-100">
                                    <i class="mdi mdi-pencil-outline me-1"></i> Salvar edição
                                </button>
                            </form>
                        @else
                            <div class="timeline-muted small">
                                Edição disponível somente para usuário admin ou gestor.
                            </div>
                        @endif
                    @endif
                </div>
            </div>

            <div class="col-xl-8">
                <div class="timeline-panel p-3">
                    <div class="timeline">
                        @foreach ($timeline as $item)
                            <div class="timeline-item {{ $item['concluida'] ? 'done' : '' }}">
                                <div class="d-flex flex-wrap justify-content-between gap-2">
                                    <div class="timeline-title">{{ $item['titulo'] }}</div>
                                    <div class="timeline-date">
                                        {{ $item['data'] ? $item['data']->format('d/m/Y H:i') : 'Pendente' }}
                                    </div>
                                </div>
                                <div class="timeline-muted mt-2">{{ $item['descricao'] }}</div>
                                <div class="timeline-meta mt-2">
                                    Responsável: {{ $item['responsavel'] }}
                                    @if ($item['duracao'])
                                        | Tempo: {{ $item['duracao'] }}
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
