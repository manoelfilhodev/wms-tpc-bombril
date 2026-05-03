@extends($layout)

@section('content')
<div class="container py-3">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h4 class="mb-0">Recebimento de Materiais</h4>
            <small class="text-muted">Etapa 1 — Recebimento documental (pré-recebimento)</small>
        </div>
        <a href="{{ route('setores.recebimento.painel') }}" class="btn btn-outline-secondary">
            <i class="fa fa-arrow-left me-1"></i> Voltar
        </a>
    </div>

    <form id="form-recebimento" action="{{ route('setores.recebimento.store') }}" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
        @csrf

        {{-- Card: Cabeçalho --}}
        <div class="card mb-3 shadow-sm">
            <div class="card-header">
                <strong>Dados do Recebimento</strong>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-lg-6">
                        <label class="form-label">Motorista</label>
                        <input type="text" name="motorista" class="form-control" placeholder="Nome do motorista" required>
                        <div class="invalid-feedback">Informe o nome do motorista.</div>
                    </div>
                    <div class="col-lg-3">
                        <label class="form-label">Placa</label>
                        <input type="text" name="placa" class="form-control" placeholder="AAA0A00" maxlength="8" required>
                        <div class="invalid-feedback">Informe a placa.</div>
                    </div>
                    <div class="col-lg-3">
                        <label class="form-label">Tipo da Carga</label>
                        <select name="tipo" class="form-select" required>
                            <option value="">Selecione...</option>
                            <option value="LOUÇAS">Louças</option>
                            <option value="METAIS">Metais</option>
                            <option value="KIT">Kit</option>
                        </select>
                        <div class="invalid-feedback">Selecione o tipo da carga.</div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Horário Janela</label>
                        <input type="time" name="horario_janela" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Horário Chegada</label>
                        <input type="time" name="horario_chegada" class="form-control" value="{{ now()->format('H:i') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Doca</label>
                        <select name="doca" class="form-select" required>
                            <option value="">Selecione uma doca...</option>
                            @for($i=1; $i<=15; $i++)
                                <option value="{{ $i }}">Doca {{ $i }}</option>
                            @endfor
                        </select>
                        <div class="invalid-feedback">Selecione a doca.</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card: Ações (Manual vs XML) --}}
        <div class="card mb-3 shadow-sm">
            <div class="card-header d-flex align-items-center justify-content-between">
                <strong>Adicionar Itens</strong>
                <small class="text-muted">Escolha uma das opções</small>
            </div>
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-6">
                        <button type="button" class="btn btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#modalItemManual">
                            <i class="fa fa-plus me-1"></i> Inserir itens manualmente
                        </button>
                        <small class="text-muted d-block mt-1">Adição rápida por SKU e quantidade.</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Importar XML da NF-e</label>
                        <div class="input-group">
                            <input type="file" id="xml_nfe" name="xml_nfe" class="form-control" accept=".xml,application/xml,text/xml">
                            <button type="button" id="btnImportarXml" class="btn btn-secondary">
                                <i class="fa fa-file-import me-1"></i> Processar XML
                            </button>
                        </div>
                        <small class="text-muted d-block mt-1" id="xmlHelper">Selecione o arquivo XML e clique em Processar.</small>
                    </div>
                </div>

                {{-- Feedback inline de processamento --}}
                <div class="mt-3" id="area-feedback" style="display:none;">
                    <div class="alert alert-info d-flex align-items-center py-2 mb-0">
                        <i class="fa fa-circle-notch fa-spin me-2"></i>
                        <span id="msg-feedback">Processando XML...</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card: Grid --}}
        <div class="card mb-4 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Itens do Pré-Recebimento</strong>
                <div class="d-flex align-items-center gap-3 small">
                    <span id="indicadores-itens" class="text-muted">0 itens</span>
                    <span id="indicadores-pend" class="badge bg-warning text-dark" style="display:none;">Pendências</span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0" id="grid-itens">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 160px;">SKU</th>
                                <th>Descrição</th>
                                <th style="width: 100px;">UOM</th>
                                <th style="width: 160px;" class="text-end">Qtd. Esperada</th>
                                <th style="width: 140px;">Status</th>
                                <th style="width: 90px;" class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- linhas via JS --}}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Rodapé fixo simples --}}
        <div class="d-flex justify-content-end gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="fa fa-save me-1"></i> Salvar Pré-Recebimento
            </button>
        </div>

        <input type="hidden" name="itens_json" id="itens_json">
    </form>
</div>

{{-- Modal: Inserir item manual --}}
<div class="modal fade" id="modalItemManual" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Adicionar item</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">SKU</label>
                <input type="text" id="m_sku" class="form-control" placeholder="Código/EAN">
            </div>
            <div class="col-md-5">
                <label class="form-label">Descrição</label>
                <input type="text" id="m_descricao" class="form-control" placeholder="Opcional">
            </div>
            <div class="col-md-2">
                <label class="form-label">UOM</label>
                <input type="text" id="m_uom" class="form-control" value="UN">
            </div>
            <div class="col-md-2">
                <label class="form-label">Qtd. Esperada</label>
                <input type="number" id="m_qtd" class="form-control text-end" min="1" step="1" value="1">
            </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" id="btnAddItemManual" class="btn btn-primary">
            <i class="fa fa-plus me-1"></i> Adicionar
        </button>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
(function() {
    // Estado
    const itens = []; // {sku, descricao, uom, qtd, status}
    const tbody = document.querySelector('#grid-itens tbody');
    const indicadores = document.getElementById('indicadores-itens');
    const badgePend = document.getElementById('indicadores-pend');
    const itensJson = document.getElementById('itens_json');
    const areaFeedback = document.getElementById('area-feedback');
    const msgFeedback = document.getElementById('msg-feedback');
    const btnImportarXml = document.getElementById('btnImportarXml');
    const xmlInput = document.getElementById('xml_nfe');

    // Helpers
    function escapeHtml(s){return (''+s).replace(/[&<>"']/g,m=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;' }[m]));}

    function refreshGrid() {
        tbody.innerHTML = '';
        let pendencias = 0;

        itens.forEach((it, idx) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><span class="fw-semibold">${escapeHtml(it.sku || '')}</span></td>
                <td class="text-truncate" style="max-width: 420px;" title="${escapeHtml(it.descricao || '')}">
                    ${escapeHtml(it.descricao || '')}
                </td>
                <td>${escapeHtml(it.uom || '')}</td>
                <td class="text-end">${Number(it.qtd || 0)}</td>
                <td>
                    <span class="badge ${it.status === 'OK' ? 'bg-success' : 'bg-warning text-dark'}">
                        ${it.status || 'Pendente'}
                    </span>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-light border" data-action="del" data-idx="${idx}" title="Remover">
                        <i class="fa fa-trash"></i>
                    </button>
                </td>
            `;
            if (it.status !== 'OK') pendencias++;
            tbody.appendChild(tr);
        });

        indicadores.textContent = `${itens.length} item${itens.length === 1 ? '' : 's'}`;
        badgePend.style.display = pendencias > 0 ? '' : 'none';
        itensJson.value = JSON.stringify(itens);
    }

    // Validação de formulário Bootstrap
    (function formValidation(){
        const form = document.getElementById('form-recebimento');
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');

            if (itens.length === 0) {
                event.preventDefault();
                event.stopPropagation();
                alert('Adicione ao menos um item manualmente ou importe via XML antes de salvar.');
            }
        }, false);
    })();

    // Adição manual
    document.getElementById('btnAddItemManual').addEventListener('click', () => {
        const sku = document.getElementById('m_sku').value.trim();
        const descricao = document.getElementById('m_descricao').value.trim();
        const uom = document.getElementById('m_uom').value.trim() || 'UN';
        const qtd = parseFloat(document.getElementById('m_qtd').value || '0');

        if (!sku) { document.getElementById('m_sku').focus(); return; }
        if (!(qtd > 0)) { document.getElementById('m_qtd').focus(); return; }

        itens.push({ sku, descricao, uom, qtd, status: 'OK' });
        refreshGrid();

        // Limpa e fecha modal
        document.getElementById('m_sku').value = '';
        document.getElementById('m_descricao').value = '';
        document.getElementById('m_uom').value = 'UN';
        document.getElementById('m_qtd').value = '1';

        const modalEl = document.getElementById('modalItemManual');
        const modal = bootstrap.Modal.getInstance(modalEl);
        modal.hide();
    });

    // Remover item
    tbody.addEventListener('click', (e) => {
        const btn = e.target.closest('button[data-action="del"]');
        if (!btn) return;
        const idx = Number(btn.dataset.idx);
        itens.splice(idx,1);
        refreshGrid();
    });

    // Importar XML (FormData com campo 'xml')
    btnImportarXml.addEventListener('click', async () => {
        if (!xmlInput.files || xmlInput.files.length === 0) {
            xmlInput.focus();
            document.getElementById('xmlHelper').classList.add('text-danger');
            setTimeout(()=>document.getElementById('xmlHelper').classList.remove('text-danger'), 1500);
            return;
        }

        const file = xmlInput.files[0];
        // Validações opcionais
        if (!file.name.toLowerCase().endsWith('.xml')) {
            document.getElementById('xmlHelper').textContent = 'Arquivo inválido. Selecione um .xml.';
            document.getElementById('xmlHelper').classList.add('text-danger');
            setTimeout(()=>{
                document.getElementById('xmlHelper').textContent = 'Selecione o arquivo XML e clique em Processar.';
                document.getElementById('xmlHelper').classList.remove('text-danger');
            }, 2500);
            return;
        }
        if (file.size > 5_000_000) {
            document.getElementById('xmlHelper').textContent = 'Arquivo muito grande (máx. 5MB).';
            document.getElementById('xmlHelper').classList.add('text-danger');
            setTimeout(()=>{
                document.getElementById('xmlHelper').textContent = 'Selecione o arquivo XML e clique em Processar.';
                document.getElementById('xmlHelper').classList.remove('text-danger');
            }, 2500);
            return;
        }

        const formData = new FormData();
        // IMPORTANTE: o nome do campo tem que ser 'xml' para casar com $request->hasFile('xml')
        formData.append('xml', file);
        // CSRF se rota estiver em web.php
        formData.append('_token', '{{ csrf_token() }}');

        // UI: loading
        btnImportarXml.disabled = true;
        areaFeedback.style.display = '';
        msgFeedback.textContent = 'Processando XML...';

        try {
            const resp = await fetch('{{ route('setores.recebimento.parseXml') }}', {
                method: 'POST',
                body: formData
            });

            const text = await resp.text();
            let data;
            try {
                data = JSON.parse(text);
            } catch {
                throw new Error('Falha ao processar XML. Resposta não-JSON. ' + text.slice(0, 300) + '...');
            }

            if (!resp.ok || data.ok === false) {
                throw new Error('Falha ao processar XML. ' + (data.message || ''));
            }

            // Esperado: data.itens = [{sku, descricao, uom, qtd, status?}], data.mensagens = []
            if (Array.isArray(data.itens) && data.itens.length > 0) {
                itens.splice(0, itens.length, ...data.itens.map(i => ({
                    sku: i.sku || '',
                    descricao: i.descricao || '',
                    uom: i.uom || 'UN',
                    qtd: Number(i.qtd || 0),
                    status: i.status || (i.sku ? 'OK' : 'Pendente')
                })));
                refreshGrid();
            }

            msgFeedback.textContent = (data.mensagens?.join(' • ')) || (data.message || 'XML processado com sucesso.');
            setTimeout(()=> areaFeedback.style.display='none', 2500);

        } catch (err) {
            console.error('[XML] Erro:', err);
            msgFeedback.textContent = err.message || 'Erro ao importar XML.';
            areaFeedback.querySelector('.alert').classList.remove('alert-info');
            areaFeedback.querySelector('.alert').classList.add('alert-danger');
            setTimeout(()=>{
                areaFeedback.style.display='none';
                areaFeedback.querySelector('.alert').classList.remove('alert-danger');
                areaFeedback.querySelector('.alert').classList.add('alert-info');
            }, 3500);
        } finally {
            btnImportarXml.disabled = false;
        }
    });
})();
</script>
@endpush
@endsection