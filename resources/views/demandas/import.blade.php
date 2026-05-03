@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    @include('partials.breadcrumb-auto')

    <!-- Header com ícone gradiente (padrão gestão de estoque) -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
            <div class="icon-wrapper me-3">
                <i class="mdi mdi-file-excel-box display-6"></i>
            </div>
            <div>
                <h3 class="mb-1 fw-bold text-dark">Importar DTs SAP</h3>
                <p class="text-muted mb-0 small">Cole a exportação SAP (com Transporte, Material e Sobra)</p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('demandas.index') }}" class="btn btn-outline-secondary btn-sm" data-bs-toggle="tooltip" title="Voltar para a lista">
                <i class="mdi mdi-arrow-left"></i> Voltar
            </a>
        </div>
    </div>

    <!-- Alertas -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="mdi mdi-check-circle-outline me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="mdi mdi-alert-circle-outline me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Card de Instruções -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small mb-3">
                        <i class="mdi mdi-information-outline me-1"></i> Como Importar
                    </h6>
                    
                    <div class="mb-3">
                        <div class="d-flex align-items-start mb-2">
                            <span class="badge bg-primary me-2">1</span>
                            <small class="text-muted">Abra sua planilha Excel com as demandas</small>
                        </div>
                        <div class="d-flex align-items-start mb-2">
                            <span class="badge bg-primary me-2">2</span>
                            <small class="text-muted">Selecione todas as células (incluindo cabeçalho)</small>
                        </div>
                        <div class="d-flex align-items-start mb-2">
                            <span class="badge bg-primary me-2">3</span>
                            <small class="text-muted">Copie os dados (Ctrl+C ou Cmd+C)</small>
                        </div>
                        <div class="d-flex align-items-start mb-2">
                            <span class="badge bg-primary me-2">4</span>
                            <small class="text-muted">Cole no campo ao lado (Ctrl+V ou Cmd+V)</small>
                        </div>
                        <div class="d-flex align-items-start">
                            <span class="badge bg-primary me-2">5</span>
                            <small class="text-muted">Clique em "Importar Demandas"</small>
                        </div>
                    </div>

                    <hr class="my-3">

                    <h6 class="text-muted text-uppercase small mb-3">
                        <i class="mdi mdi-table me-1"></i> Formato Esperado
                    </h6>
                    <div class="bg-light p-3 rounded">
                        <small class="text-muted d-block mb-1"><strong>Colunas obrigatórias:</strong></small>
                        <ul class="small text-muted mb-0 ps-3">
                            <li>Transporte (DT)</li>
                            <li>Transportadora</li>
                            <li>Material (SKU)</li>
                            <li>Sobra</li>
                            <li>Texto breve material</li>
                        </ul>
                    </div>

                    <div class="alert alert-info mt-3 mb-0" role="alert">
                        <i class="mdi mdi-lightbulb-outline me-1"></i>
                        <small><strong>Dica:</strong> Mantenha o cabeçalho da planilha ao colar os dados.</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card do Formulário -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <form action="{{ route('demandas.import') }}" method="POST">
                        @csrf
                        
                        <h6 class="text-muted text-uppercase small mb-3">
                            <i class="mdi mdi-clipboard-text-outline me-1"></i> Dados da Planilha
                        </h6>

                        <div class="mb-3">
                            <label for="planilha" class="form-label small text-muted mb-2">
                                Cole aqui os dados copiados do Excel
                            </label>
                            <textarea 
                                name="planilha" 
                                id="planilha" 
                                class="form-control font-monospace" 
                                rows="16" 
                                placeholder="Cole aqui os dados exportados do SAP (Ctrl+V ou Cmd+V)"
                                required
                            ></textarea>
                            <small class="text-muted">
                                <i class="mdi mdi-information-outline"></i> 
                                Os dados devem estar separados por tabulação (TAB)
                            </small>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="document.getElementById('planilha').value = ''">
                                <i class="mdi mdi-eraser"></i> Limpar
                            </button>
                            <button type="submit" class="btn btn-success">
                                <i class="mdi mdi-upload me-1"></i> Importar Demandas
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Card de Preview (opcional - mostra quando há dados) -->
            <div class="card shadow-sm border-0 mt-3" id="previewCard" style="display: none;">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small mb-3">
                        <i class="mdi mdi-eye-outline me-1"></i> Preview dos Dados
                    </h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0" id="previewTable">
                            <thead class="bg-light">
                                <tr id="previewHeader"></tr>
                            </thead>
                            <tbody id="previewBody"></tbody>
                        </table>
                    </div>
                    <small class="text-muted mt-2 d-block">
                        <i class="mdi mdi-information-outline"></i> 
                        Mostrando apenas as primeiras 5 linhas
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .icon-wrapper {
        width: 60px; height: 60px;
        display: flex; align-items: center; justify-content: center;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(102,126,234,0.3);
    }
    .icon-wrapper i { color: #fff !important; }

    .form-control:focus { 
        border-color: #0d6efd; 
        box-shadow: 0 0 0 0.2rem rgba(13,110,253,0.1); 
    }
    .card { border-radius: 0.5rem; }
    .font-monospace { font-family: 'Courier New', monospace; font-size: 0.85rem; }
    
    .badge { font-weight: 500; }
    
    #previewTable { font-size: 0.8rem; }
    #previewTable th { white-space: nowrap; }
</style>

<script>
// Preview automático dos dados colados
document.getElementById('planilha')?.addEventListener('input', function() {
    const text = this.value.trim();
    const previewCard = document.getElementById('previewCard');
    const previewHeader = document.getElementById('previewHeader');
    const previewBody = document.getElementById('previewBody');
    
    if (!text) {
        previewCard.style.display = 'none';
        return;
    }
    
    const lines = text.split('\n').filter(l => l.trim());
    if (lines.length < 2) {
        previewCard.style.display = 'none';
        return;
    }
    
    // Cabeçalho
    const headers = lines[0].split('\t');
    previewHeader.innerHTML = headers.map(h => `<th class="px-3 py-2 text-muted small">${h}</th>`).join('');
    
    // Corpo (máximo 5 linhas)
    const rows = lines.slice(1, 6);
    previewBody.innerHTML = rows.map(row => {
        const cols = row.split('\t');
        return `<tr>${cols.map(c => `<td class="px-3 py-2">${c}</td>`).join('')}</tr>`;
    }).join('');
    
    previewCard.style.display = 'block';
});
</script>
@endsection
