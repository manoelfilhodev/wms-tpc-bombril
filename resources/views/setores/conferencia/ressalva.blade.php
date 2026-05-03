@extends('layouts.app')

@section('content')

<div class="container-fluid px-4 py-3">
    @include('partials.breadcrumb-auto')
    
    <!-- Header com ícone -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
            <div class="icon-wrapper me-3">
                <i class="mdi mdi-alert-circle-outline display-6 text-primary"></i>
            </div>
            <div>
                <h3 class="mb-1 fw-bold text-dark">Ressalva Pós-Conferência</h3>
                <p class="text-muted mb-0 small">Nota Fiscal {{ $recebimento->nota_fiscal }}</p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('setores.recebimento.painel') }}" class="btn btn-outline-secondary btn-sm" data-bs-toggle="tooltip" title="Voltar ao painel">
                <i class="mdi mdi-arrow-left"></i>
            </a>
        </div>
    </div>

    <!-- Card de Informações do Recebimento -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card info-card shadow-sm border-0">
                <div class="card-body p-3">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="info-item">
                                <i class="mdi mdi-file-document text-primary me-2"></i>
                                <div>
                                    <small class="text-muted d-block">Nota Fiscal</small>
                                    <strong class="text-dark">{{ $recebimento->nota_fiscal }}</strong>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-item">
                                <i class="mdi mdi-domain text-primary me-2"></i>
                                <div>
                                    <small class="text-muted d-block">Fornecedor</small>
                                    <strong class="text-dark">{{ $recebimento->fornecedor }}</strong>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-item">
                                <i class="mdi mdi-truck text-primary me-2"></i>
                                <div>
                                    <small class="text-muted d-block">Transportadora</small>
                                    <strong class="text-dark">{{ $recebimento->transportadora }}</strong>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-item">
                                <i class="mdi mdi-calendar text-primary me-2"></i>
                                <div>
                                    <small class="text-muted d-block">Data Recebimento</small>
                                    <strong class="text-dark">{{ \Carbon\Carbon::parse($recebimento->data_recebimento)->format('d/m/Y H:i') }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Card Principal do Formulário -->
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0 pt-4 pb-3">
                    <h5 class="fw-semibold mb-0">
                        <i class="mdi mdi-text-box-edit-outline text-primary me-2"></i>
                        Registrar Ressalva do Assistente
                    </h5>
                    <p class="text-muted small mb-0 mt-2">
                        Descreva detalhadamente qualquer divergência, avaria ou observação identificada durante a conferência
                    </p>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('setores.conferencia.salvarRessalva', $recebimento->id) }}" id="formRessalva">
                        @csrf
                        
                        <!-- Textarea de Ressalva -->
                        <div class="mb-4">
                            <label for="ressalva_assistente" class="form-label fw-semibold">
                                <i class="mdi mdi-comment-text-outline me-1"></i>
                                Descrição da Ressalva
                            </label>
                            <textarea 
                                name="ressalva_assistente" 
                                id="ressalva_assistente"
                                class="form-control form-control-lg" 
                                rows="8" 
                                placeholder="Ex: Produto X apresentou avaria na embalagem. Quantidade divergente no item Y (recebido 10, nota fiscal 12). Lote Z com validade próxima ao vencimento..."
                                required>{{ $recebimento->ressalva_assistente }}</textarea>
                            <div class="form-text">
                                <i class="mdi mdi-information-outline me-1"></i>
                                Seja específico: mencione itens, quantidades, lotes, condições e fotos (se aplicável)
                            </div>
                            <div class="d-flex justify-content-between mt-2">
                                <small class="text-muted">
                                    <span id="charCount">0</span> caracteres
                                </small>
                                <small class="text-muted">Mínimo recomendado: 20 caracteres</small>
                            </div>
                        </div>

                        <!-- Sugestões Rápidas -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold mb-3">
                                <i class="mdi mdi-lightbulb-outline me-1"></i>
                                Sugestões Rápidas
                            </label>
                            <div class="quick-suggestions">
                                <button type="button" class="btn btn-outline-secondary btn-sm suggestion-btn" data-text="Divergência de quantidade: ">
                                    <i class="mdi mdi-numeric me-1"></i>Divergência Quantidade
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm suggestion-btn" data-text="Avaria identificada: ">
                                    <i class="mdi mdi-package-variant-closed-remove me-1"></i>Avaria
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm suggestion-btn" data-text="Produto com validade próxima: ">
                                    <i class="mdi mdi-calendar-alert me-1"></i>Validade
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm suggestion-btn" data-text="Falta de documentação: ">
                                    <i class="mdi mdi-file-document-alert me-1"></i>Documentação
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm suggestion-btn" data-text="Produto não conforme: ">
                                    <i class="mdi mdi-close-circle-outline me-1"></i>Não Conforme
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm suggestion-btn" data-text="Embalagem danificada: ">
                                    <i class="mdi mdi-package-variant me-1"></i>Embalagem
                                </button>
                            </div>
                        </div>

                        <!-- Botões de Ação -->
                        <div class="d-flex gap-2 justify-content-end pt-3 border-top">
                            <a href="{{ route('setores.recebimento.painel') }}" class="btn btn-outline-secondary btn-lg">
                                <i class="mdi mdi-close me-1"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-success btn-lg" id="btnSalvar">
                                <i class="mdi mdi-check me-1"></i>Salvar Ressalva
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Card de Orientações -->
            <div class="card border-0 bg-light mt-3">
                <div class="card-body p-3">
                    <h6 class="fw-semibold mb-3">
                        <i class="mdi mdi-help-circle text-primary me-2"></i>
                        Orientações para Preenchimento
                    </h6>
                    <ul class="mb-0 small text-muted">
                        <li class="mb-2">
                            <strong>Seja específico:</strong> Identifique claramente os produtos, códigos e quantidades envolvidas
                        </li>
                        <li class="mb-2">
                            <strong>Documente evidências:</strong> Mencione se há fotos, vídeos ou outros registros da ocorrência
                        </li>
                        <li class="mb-2">
                            <strong>Indique ações:</strong> Descreva se o produto foi aceito com ressalva, recusado ou segregado
                        </li>
                        <li class="mb-0">
                            <strong>Responsabilidade:</strong> Informe se a divergência é de responsabilidade do fornecedor ou transportadora
                        </li>
                    </ul>
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

    .card {
        border-radius: 0.75rem;
        transition: all 0.3s ease;
    }

    .info-card {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    }

    .info-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .info-item i {
        font-size: 1.5rem;
    }

    .form-control-lg {
        border-radius: 0.5rem;
        border: 2px solid #e9ecef;
        transition: all 0.3s ease;
        font-size: 1rem;
        padding: 0.75rem 1rem;
    }

    .form-control-lg:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
    }

    .form-label {
        color: #495057;
        margin-bottom: 0.75rem;
    }

    .quick-suggestions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .suggestion-btn {
        border-radius: 2rem;
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
        transition: all 0.3s ease;
        border: 1.5px solid #dee2e6;
    }

    .suggestion-btn:hover {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: #fff;
        border-color: transparent;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102,126,234,0.3);
    }

    .btn {
        border-radius: 0.5rem;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-success {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        border: none;
    }

    .btn-success:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(40,167,69,0.4);
    }

    .btn-outline-secondary:hover {
        background: #6c757d;
        color: #fff;
    }

    .card-header {
        border-radius: 0.75rem 0.75rem 0 0;
    }

    @media (max-width: 768px) {
        .info-item {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .quick-suggestions {
            flex-direction: column;
        }
        
        .suggestion-btn {
            width: 100%;
        }
    }
</style>

<script>
// Contador de caracteres
const textarea = document.getElementById('ressalva_assistente');
const charCount = document.getElementById('charCount');

function updateCharCount() {
    const count = textarea.value.length;
    charCount.textContent = count;
    
    if (count < 20) {
        charCount.classList.add('text-danger');
        charCount.classList.remove('text-success');
    } else {
        charCount.classList.add('text-success');
        charCount.classList.remove('text-danger');
    }
}

textarea.addEventListener('input', updateCharCount);
updateCharCount(); // Inicializar

// Sugestões rápidas
document.querySelectorAll('.suggestion-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const text = this.getAttribute('data-text');
        const currentValue = textarea.value;
        
        // Adiciona o texto no final ou no cursor
        const cursorPos = textarea.selectionStart;
        const textBefore = currentValue.substring(0, cursorPos);
        const textAfter = currentValue.substring(cursorPos);
        
        textarea.value = textBefore + text + textAfter;
        textarea.focus();
        
        // Posiciona o cursor após o texto inserido
        const newCursorPos = cursorPos + text.length;
        textarea.setSelectionRange(newCursorPos, newCursorPos);
        
        updateCharCount();
        
        // Feedback visual
        this.classList.add('btn-success');
        setTimeout(() => {
            this.classList.remove('btn-success');
        }, 500);
    });
});

// Validação antes de enviar
document.getElementById('formRessalva').addEventListener('submit', function(e) {
    const text = textarea.value.trim();
    
    if (text.length < 20) {
        e.preventDefault();
        alert('Por favor, descreva a ressalva com mais detalhes (mínimo 20 caracteres).');
        textarea.focus();
        return false;
    }
    
    // Confirmação
    if (!confirm('Confirma o registro desta ressalva?')) {
        e.preventDefault();
        return false;
    }
});

// Inicializar tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

// Auto-save no localStorage (opcional - recuperação em caso de erro)
textarea.addEventListener('input', function() {
    localStorage.setItem('ressalva_temp_' + {{ $recebimento->id }}, this.value);
});

// Recuperar texto salvo ao carregar
window.addEventListener('load', function() {
    const savedText = localStorage.getItem('ressalva_temp_' + {{ $recebimento->id }});
    if (savedText && !textarea.value) {
        if (confirm('Encontramos um rascunho salvo. Deseja recuperá-lo?')) {
            textarea.value = savedText;
            updateCharCount();
        }
    }
});

// Limpar localStorage após salvar com sucesso
document.getElementById('formRessalva').addEventListener('submit', function() {
    localStorage.removeItem('ressalva_temp_' + {{ $recebimento->id }});
});
</script>

@endsection