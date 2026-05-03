@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    @include('partials.breadcrumb-auto')

    <!-- Header -->
    <div class="mb-4">
        <div class="d-flex align-items-center mb-2">
            <div class="icon-wrapper me-3">
                <i class="mdi mdi-truck-delivery-outline display-6"></i>
            </div>
            <div class="d-flex flex-column">
                <h3 class="mb-1 fw-bold text-dark">Painel de Recebimento — Notas Fiscais</h3>
                <p class="text-muted mb-0 small">Acompanhe o status dos recebimentos e prossiga para as próximas etapas</p>
            </div>
            <div class="ms-auto">
                <a href="{{ route('setores.recebimento.create') }}" class="btn btn-primary">
                    <i class="mdi mdi-plus me-2"></i> Iniciar Recebimento
                </a>
            </div>
        </div>
    </div>

    <!-- Alertas -->
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center shadow-sm border-0" role="alert">
            <i class="mdi mdi-alert-circle me-2 fs-5"></i>
            <div>{{ session('error') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @elseif (session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center shadow-sm border-0" role="alert">
            <i class="mdi mdi-check-circle me-2 fs-5"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 160px;">Ações</th>
                            <th style="width: 90px;">Início</th>
                            <th style="width: 140px;">NF</th>
                            {{-- <th>Fornecedor</th> --}}
                            <th style="width: 140px;">Data</th>
                            <th style="width: 260px;">Progresso</th>
                            <th style="width: 200px;">Alertas</th>
                            <th style="width: 130px;" class="text-center">Etiquetas</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($recebimentos as $recebimento)
                        @php
                            $totalItens = $recebimento->itens->count();
                            $armazenados = $recebimento->itens->where('status', 'armazenado')->count();
                            $percentual = $totalItens > 0 ? round(($armazenados / $totalItens) * 100) : 0;

                            $temDivergencia = DB::table('_tb_recebimento_itens')
                                ->where('recebimento_id', $recebimento->id)
                                ->where('divergente', 1)
                                ->exists();

                            $temAvaria = DB::table('_tb_recebimento_itens')
                                ->where('recebimento_id', $recebimento->id)
                                ->where('avariado', 1)
                                ->exists();

                            $nivel = strtolower(Auth::user()->nivel);
                        @endphp
                        <tr>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('setores.conferencia.telaFotoInicio', $recebimento->id) }}"
                                       class="btn btn-sm btn-outline-primary" title="Iniciar Conferência">
                                        <i class="mdi mdi-clipboard-check-outline"></i>
                                        <span class="d-none d-xl-inline ms-1">Conferir</span>
                                    </a>

                                    @if($recebimento->status === 'conferido' && in_array($nivel, ['admin', 'documental', 'recebimento']))
                                        <a href="{{ route('setores.conferencia.formRessalva', $recebimento->id) }}"
                                           class="btn btn-sm btn-outline-secondary" title="Adicionar Ressalva">
                                            <i class="mdi mdi-comment-text-outline"></i>
                                        </a>
                                        <a href="{{ route('setores.conferencia.relatorio', $recebimento->id) }}" target="_blank"
                                           class="btn btn-sm btn-outline-dark" title="Relatório PDF">
                                            <i class="mdi mdi-file-pdf-box"></i>
                                        </a>
                                    @endif
                                </div>
                            </td>

                            <td>
                                <span class="text-success" title="Pré-recebimento iniciado">
                                    <i class="mdi mdi-check-circle-outline fs-5"></i>
                                </span>
                            </td>

                            <td><span class="fw-semibold">{{ $recebimento->nota_fiscal }}</span></td>

                            {{-- <td class="text-truncate" style="max-width: 360px;" title="{{ $recebimento->fornecedor }}">
                                {{ $recebimento->fornecedor }}
                            </td> --}}

                            <td>{{ \Carbon\Carbon::parse($recebimento->data_recebimento)->format('d/m/Y') }}</td>

                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height: 10px;">
                                        <div class="progress-bar bg-{{ $percentual == 100 ? 'success' : ($percentual > 0 ? 'info' : 'secondary') }}"
                                             role="progressbar"
                                             style="width: {{ $percentual }}%;"
                                             aria-valuenow="{{ $percentual }}" aria-valuemin="0" aria-valuemax="100">
                                        </div>
                                    </div>
                                    <small class="text-muted" style="min-width: 44px; text-align:right;">
                                        {{ $percentual }}%
                                    </small>
                                </div>
                                <small class="text-muted">{{ $armazenados }}/{{ $totalItens }} itens armazenados</small>
                            </td>

                            <td>
                                @if ($temDivergencia)
                                    <span class="badge bg-danger me-1 d-inline-flex align-items-center">
                                        <i class="mdi mdi-alert-octagon-outline me-1"></i> Divergência
                                    </span>
                                @endif
                                @if ($temAvaria)
                                    <span class="badge bg-warning text-dark d-inline-flex align-items-center">
                                        <i class="mdi mdi-alert-outline me-1"></i> Avaria
                                    </span>
                                @endif
                                @if (!$temDivergencia && !$temAvaria)
                                    <span class="text-muted">Sem alertas</span>
                                @endif
                            </td>

                            <td class="text-center">
                                <button class="btn btn-sm btn-primary"
                                        onclick="abrirModalEtiquetas({{ $recebimento->id }})"
                                        title="Imprimir etiquetas">
                                    <i class="mdi mdi-tag-multiple-outline"></i>
                                    <span class="d-none d-xl-inline ms-1">Etiquetas</span>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    .icon-wrapper {
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }

    .icon-wrapper i {
        color: white !important;
    }

    .card {
        border-radius: 0.5rem;
        overflow: hidden;
    }

    .btn {
        border-radius: 0.375rem;
        font-weight: 500;
    }

    .badge {
        border-radius: 0.35rem;
    }
</style>

{{-- Mantendo exatamente as MESMAS rotas do seu código --}}
<script>
function abrirModalEtiquetas(recebimentoId, itemId = null) {
    let urlImprimirTudo = "{{ route('recebimento.imprimirTudo', ':id') }}".replace(':id', recebimentoId);
    let urlReimprimir = itemId
        ? "{{ route('recebimento.reimprimir', ':itemId') }}".replace(':itemId', itemId)
        : null;

    // Cria modal dinamicamente no padrão que você já usa
    const existing = document.getElementById('modalEtiquetas');
    if (existing) existing.remove();

    const modalHtml = `
    <div class="modal fade" id="modalEtiquetas" tabindex="-1" aria-labelledby="modalEtiquetasLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content shadow border-0">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEtiquetasLabel">
                        <i class="mdi mdi-tag-text-outline me-2 text-primary"></i>
                        Impressão de Etiquetas
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">Selecione uma das opções abaixo:</p>
                    <ul class="small mb-0">
                        <li>Imprimir todas as etiquetas deste recebimento</li>
                        <li>Reimprimir etiquetas específicas (se disponível)</li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <a href="${urlImprimirTudo}" class="btn btn-primary" target="_blank">
                        <i class="mdi mdi-printer me-2"></i> Imprimir Tudo
                    </a>
                    ${urlReimprimir ? `
                        <a href="${urlReimprimir}" class="btn btn-outline-secondary" target="_blank">
                            <i class="mdi mdi-refresh me-2"></i> Reimprimir
                        </a>` : ''}
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>`;

    document.body.insertAdjacentHTML('beforeend', modalHtml);
    const modal = new bootstrap.Modal(document.getElementById('modalEtiquetas'));
    modal.show();
}
</script>
@endsection