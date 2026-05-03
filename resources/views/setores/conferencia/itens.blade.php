@extends('layouts.app')

@section('content')
<div class="container py-4">
    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1">Conferência de Recebimento</h4>
            <small class="text-muted">NF {{ $recebimento->nota_fiscal }} • Rec. Doc. #{{ $recebimento->id }}</small>
        </div>
        <a href="{{ route('setores.recebimento.painel') }}" class="btn btn-outline-secondary">
            <i class="fa fa-arrow-left me-1"></i> Voltar
        </a>
    </div>

    {{-- Card: Informações do Recebimento --}}
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-light">
            <strong><i class="fa fa-info-circle me-2"></i>Dados do Recebimento</strong>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="text-muted small mb-1">Fornecedor</label>
                    <div class="fw-semibold">{{ $recebimento->fornecedor }}</div>
                </div>
                <div class="col-md-3">
                    <label class="text-muted small mb-1">Transportadora</label>
                    <div class="fw-semibold">{{ $recebimento->transportadora }}</div>
                </div>
                <div class="col-md-2">
                    <label class="text-muted small mb-1">Motorista</label>
                    <div class="fw-semibold">{{ $recebimento->motorista }}</div>
                </div>
                <div class="col-md-2">
                    <label class="text-muted small mb-1">Placa</label>
                    <div class="fw-semibold text-uppercase">{{ $recebimento->placa }}</div>
                </div>
                <div class="col-md-2">
                    <label class="text-muted small mb-1">Doca</label>
                    <div class="fw-semibold">Doca {{ $recebimento->doca }}</div>
                </div>
            </div>
            <div class="row g-3 mt-2">
                <div class="col-md-2">
                    <label class="text-muted small mb-1">Tipo Carga</label>
                    <div><span class="badge bg-info">{{ $recebimento->tipo }}</span></div>
                </div>
                <div class="col-md-2">
                    <label class="text-muted small mb-1">Hr. Janela</label>
                    <div class="fw-semibold">{{ $recebimento->horario_janela ?? '—' }}</div>
                </div>
                <div class="col-md-2">
                    <label class="text-muted small mb-1">Hr. Chegada</label>
                    <div class="fw-semibold">{{ $recebimento->horario_chegada ?? '—' }}</div>
                </div>
                <div class="col-md-3">
                    <label class="text-muted small mb-1">Data Recebimento</label>
                    <div class="fw-semibold">{{ \Carbon\Carbon::parse($recebimento->data_recebimento)->format('d/m/Y H:i') }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Card: Itens para Conferência --}}
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <strong><i class="fa fa-clipboard-check me-2"></i>Itens para Conferência</strong>
            <div class="d-flex gap-2">
                <span class="badge bg-secondary">{{ $itens->count() }} itens</span>
                @php
                    $conferidos = $itens->where('status', 'conferido')->count();
                    $pendentes = $itens->count() - $conferidos;
                @endphp
                @if($conferidos > 0)
                    <span class="badge bg-success">{{ $conferidos }} conferidos</span>
                @endif
                @if($pendentes > 0)
                    <span class="badge bg-warning text-dark">{{ $pendentes }} pendentes</span>
                @endif
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;" class="text-center">#</th>
                            <th style="width: 160px;">SKU</th>
                            <th>Descrição</th>
                            <th style="width: 120px;" class="text-center">Qtd. Esperada</th>
                            <th style="width: 120px;" class="text-center">Qtd. Conferida</th>
                            <th style="width: 120px;" class="text-center">Status</th>
                            <th style="width: 140px;" class="text-center">Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($itens as $index => $item)
                        <tr>
                            <td class="text-center text-muted">{{ $index + 1 }}</td>
                            <td><span class="fw-semibold font-monospace">{{ $item->sku }}</span></td>
                            <td class="text-truncate" style="max-width: 300px;" title="{{ $item->descricao }}">
                                {{ $item->descricao }}
                            </td>
                            <td class="text-center">{{ $item->quantidade ?? 0 }}</td>
                            <td class="text-center">
                                @if($item->status == 'conferido')
                                    <span class="fw-semibold text-success">{{ $item->qtd_conferida ?? 0 }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($item->status == 'conferido')
                                    @if($item->qtd_conferida == $item->quantidade)
                                        <span class="badge bg-success"><i class="fa fa-check me-1"></i>OK</span>
                                    @else
                                        <span class="badge bg-warning text-dark"><i class="fa fa-exclamation-triangle me-1"></i>Divergência</span>
                                    @endif
                                @else
                                    <span class="badge bg-secondary">Pendente</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($item->status != 'conferido')
                                    <a href="{{ url("setores/conferencia/item/{$recebimento->id}/{$item->id}/conferir") }}" 
                                       class="btn btn-sm btn-primary">
                                        <i class="fa fa-check me-1"></i>Conferir
                                    </a>
                                @else
                                    <span class="badge bg-success"><i class="fa fa-check-circle me-1"></i>Conferido</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Botão Fechar Conferência --}}
    <div class="d-flex justify-content-end gap-2">
        @php
            $todoConferido = $itens->where('status', '!=', 'conferido')->count() === 0;
        @endphp
        <button type="button" 
                class="btn btn-success btn-lg" 
                data-bs-toggle="modal" 
                data-bs-target="#modalFecharConferencia"
                @if(!$todoConferido) disabled title="Confira todos os itens antes de fechar" @endif>
            <i class="fa fa-check-circle me-2"></i>Fechar Conferência
        </button>
    </div>
</div>

{{-- Modal: Fechar Conferência --}}
<div class="modal fade" id="modalFecharConferencia" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <form method="POST" action="{{ route('setores.conferencia.finalizar', $recebimento->id) }}" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fa fa-file-alt me-2"></i>Relatório de Conferência Cega</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    {{-- Dados do Recebimento --}}
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <strong>Dados do Recebimento</strong>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-2">
                                    <label class="form-label small text-muted">Rec. Doc</label>
                                    <input type="text" class="form-control form-control-sm" value="{{ $recebimento->id }}" readonly>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small text-muted">Data Recebimento</label>
                                    <input type="text" class="form-control form-control-sm" value="{{ $recebimento->data_recebimento }}" readonly>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small text-muted">NF</label>
                                    <input type="text" class="form-control form-control-sm" value="{{ $recebimento->nota_fiscal }}" readonly>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small text-muted">Tipo Carga</label>
                                    <input type="text" class="form-control form-control-sm" value="{{ $recebimento->tipo }}" readonly>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small text-muted">Transportadora</label>
                                    <input type="text" class="form-control form-control-sm" value="{{ $recebimento->transportadora }}" readonly>
                                </div>
                            </div>
                            <div class="row g-3 mt-1">
                                <div class="col-md-3">
                                    <label class="form-label small text-muted">Motorista</label>
                                    <input type="text" class="form-control form-control-sm" value="{{ $recebimento->motorista }}" readonly>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small text-muted">Placa</label>
                                    <input type="text" class="form-control form-control-sm text-uppercase" value="{{ $recebimento->placa }}" readonly>
                                </div>
                                <div class="col-md-1">
                                    <label class="form-label small text-muted">Doca</label>
                                    <input type="text" class="form-control form-control-sm" value="{{ $recebimento->doca }}" readonly>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small text-muted">Hr. Janela</label>
                                    <input type="text" class="form-control form-control-sm" value="{{ $recebimento->horario_janela }}" readonly>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small text-muted">Hr. Chegada</label>
                                    <input type="text" class="form-control form-control-sm" value="{{ $recebimento->horario_chegada }}" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Tabela de Itens Conferidos --}}
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <strong>SKUs Conferidos</strong>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 100px;">Data Conf</th>
                                            <th style="width: 140px;">SKU</th>
                                            <th>Descrição</th>
                                            <th style="width: 100px;" class="text-center">Qtd. Esp.</th>
                                            <th style="width: 100px;" class="text-center">Qtd. Conf.</th>
                                            <th style="width: 100px;" class="text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($itens as $item)
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y') }}</td>
                                            <td class="font-monospace fw-semibold">{{ $item->sku }}</td>
                                            <td class="text-truncate" style="max-width: 250px;" title="{{ $item->descricao }}">{{ $item->descricao }}</td>
                                            <td class="text-center">{{ $item->quantidade ?? 0 }}</td>
                                            <td class="text-center fw-semibold">{{ $item->qtd_conferida ?? 0 }}</td>
                                            <td class="text-center">
                                                @if($item->qtd_conferida == $item->quantidade)
                                                    <span class="badge bg-success">OK</span>
                                                @else
                                                    <span class="badge bg-warning text-dark">DIVERGÊNCIA</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- Foto Final --}}
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <strong><i class="fa fa-camera me-2"></i>Foto Final - Veículo Descarregado</strong>
                        </div>
                        <div class="card-body">
                            <input type="file" name="foto_fim_veiculo" id="foto_fim_veiculo" class="form-control" accept="image/*" required>
                            <small class="text-muted d-block mt-1">Anexe foto do veículo vazio após descarga completa</small>
                        </div>
                    </div>

                    {{-- Assinatura/Confirmação --}}
                    <div class="card border-success">
                        <div class="card-header bg-light">
                            <strong><i class="fa fa-signature me-2"></i>Assinatura Conferente TPC</strong>
                        </div>
                        <div class="card-body">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="confirmacao" name="confirmacao" required>
                                <label class="form-check-label" for="confirmacao">
                                    <strong>Declaro que conferi a carga conforme recebido fisicamente em doca, de acordo com as especificações e padrões da empresa.</strong>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fa fa-times me-1"></i>Cancelar
                    </button>
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="fa fa-check-circle me-2"></i>Fechar Conferência
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Modal: Conferir Item (mantido do original, mas não usado pois você usa rota direta) --}}
<div class="modal fade" id="modalConferirItem" tabindex="-1">
    <div class="modal-dialog">
        <form id="formConferirItem" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Conferir Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="item_id" id="item_id">
                    <p><strong>SKU:</strong> <span id="skuModal"></span></p>
                    <p><strong>Descrição:</strong> <span id="descModal"></span></p>

                    <div class="mb-3">
                        <label>Quantidade Conferida</label>
                        <input type="number" name="qtd_conferida" class="form-control" required min="0">
                    </div>

                    <div class="mb-3">
                        <label>Observação</label>
                        <textarea name="observacao" class="form-control" rows="2"></textarea>
                    </div>

                    <div class="mb-3 form-check">
                        <input class="form-check-input" type="checkbox" name="avariado" id="avariadoCheck">
                        <label class="form-check-label" for="avariadoCheck">Item Avariado</label>
                    </div>

                    <div class="mb-3">
                        <label>Foto da Avaria (opcional)</label>
                        <input type="file" name="foto_avaria" accept="image/*" class="form-control">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Salvar Conferência</button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.querySelectorAll('.btnConferir').forEach(btn => {
    btn.addEventListener('click', function () {
        const id = this.dataset.id;
        document.getElementById('item_id').value = id;
        document.getElementById('skuModal').innerText = this.dataset.sku;
        document.getElementById('descModal').innerText = this.dataset.desc;

        const modal = new bootstrap.Modal(document.getElementById('modalConferirItem'));
        modal.show();
    });
});

document.getElementById('formConferirItem').addEventListener('submit', function (e) {
    e.preventDefault();

    const formData = new FormData(this);
    const itemId = formData.get('item_id');

    fetch(`/setores/conferencia/item/${itemId}/conferir`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': formData.get('_token'),
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'ok') {
            alert(data.mensagem);
            location.reload();
        } else {
            alert('Erro: ' + (data.mensagem ?? 'Falha ao salvar.'));
        }
    })
    .catch(err => {
        console.error('Erro detalhado:', err);
        alert('Erro ao enviar conferência. Veja o console (F12) para detalhes.');
    });
});
</script>
@endpush
@endsection