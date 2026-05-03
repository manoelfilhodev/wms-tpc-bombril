@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    @include('partials.breadcrumb-auto')
    
    <!-- Header -->
    <div class="mb-4">
        <div class="d-flex align-items-center mb-2">
            <div class="icon-wrapper me-3">
                <i class="mdi mdi-file-upload-outline display-6 text-primary"></i>
            </div>
            <div>
                <h3 class="mb-1 fw-bold text-dark">Importar Lista de Inventário</h3>
                <p class="text-muted mb-0 small">Importe SKUs para gerar a listagem de contagem</p>
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

    <div class="row">
        <!-- Card Principal -->
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-semibold">
                        <i class="mdi mdi-table-edit me-2 text-primary"></i>
                        Dados para Importação
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('inventario.gerar') }}" method="POST" id="formImportar">
                        @csrf

                        <div class="mb-4">
                            <label for="lista_skus" class="form-label fw-semibold text-dark mb-2">
                                <i class="mdi mdi-format-list-bulleted me-1"></i>
                                Cole os dados (Material, Centro, Descrição)
                            </label>
                            <textarea 
                                name="lista_skus" 
                                id="lista_skus" 
                                class="form-control font-monospace" 
                                rows="16"
                                placeholder="Cole aqui os dados separados por TAB...&#10;&#10;Exemplo:&#10;3000.AT.003	HY09	ACOPLAMENTO OPT/STAR TURBO C/ TRIAC (AT)&#10;3000.AT.006	HY09	ACOPLAMENTO ND BLINDADA 6500W 220V (AT)&#10;3000.AT.009	HY09	ACOPLAMENTO STAR TURBO 4500W 220V (AT)"
                                required></textarea>
                            
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <small class="text-muted">
                                    <i class="mdi mdi-information-outline me-1"></i>
                                    Use <kbd class="bg-light text-dark border">TAB</kbd> como separador entre as colunas
                                </small>
                                <small class="text-muted" id="lineCount">0 linhas</small>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="mdi mdi-upload me-2"></i>
                                Gerar Listagem de Inventário
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="limparCampo()">
                                <i class="mdi mdi-close me-1"></i>
                                Limpar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Card de Instruções -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header bg-primary bg-opacity-10 border-bottom border-primary border-opacity-25 py-3">
                    <h6 class="mb-0 fw-semibold text-primary">
                        <i class="mdi mdi-help-circle-outline me-2"></i>
                        Como usar
                    </h6>
                </div>
                <div class="card-body">
                    <ol class="ps-3 mb-0 small">
                        <li class="mb-2">
                            <strong>Copie os dados</strong> do Excel ou sistema ERP
                        </li>
                        <li class="mb-2">
                            <strong>Cole no campo</strong> de texto ao lado
                        </li>
                        <li class="mb-2">
                            Certifique-se de que as colunas estão separadas por <kbd class="bg-light text-dark border">TAB</kbd>
                        </li>
                        <li class="mb-0">
                            Clique em <strong>"Gerar Listagem"</strong>
                        </li>
                    </ol>
                </div>
            </div>

            <div class="card shadow-sm border-0 border-start border-4 border-warning">
                <div class="card-body">
                    <h6 class="fw-semibold text-warning mb-2">
                        <i class="mdi mdi-alert-outline me-1"></i>
                        Formato esperado
                    </h6>
                    <div class="bg-light p-3 rounded font-monospace small text-dark">
                        <div class="mb-1">Material<span class="text-muted">[TAB]</span>Centro<span class="text-muted">[TAB]</span>Descrição</div>
                        <hr class="my-2">
                        <div class="text-muted" style="font-size: 0.75rem;">
                            3000.AT.003<span class="text-primary">[TAB]</span>HY09<span class="text-primary">[TAB]</span>ACOPLAMENTO...
                        </div>
                    </div>
                </div>
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

    #lista_skus {
        border: 2px dashed #dee2e6;
        transition: all 0.3s ease;
        font-size: 0.9rem;
        line-height: 1.6;
    }

    #lista_skus:focus {
        border-color: #0d6efd;
        border-style: solid;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.1);
    }

    #lista_skus:not(:placeholder-shown) {
        border-color: #198754;
        border-style: solid;
        background-color: #f8fff9;
    }

    .card {
        border-radius: 0.5rem;
        overflow: hidden;
    }

    kbd {
        padding: 0.2rem 0.4rem;
        font-size: 0.875em;
        border-radius: 0.25rem;
    }

    .alert {
        border-radius: 0.5rem;
    }

    .btn {
        border-radius: 0.375rem;
        font-weight: 500;
    }
</style>

<script>
    // Contador de linhas
    const textarea = document.getElementById('lista_skus');
    const lineCount = document.getElementById('lineCount');

    textarea.addEventListener('input', function() {
        const lines = this.value.split('\n').filter(line => line.trim() !== '').length;
        lineCount.textContent = `${lines} linha${lines !== 1 ? 's' : ''}`;
    });

    function limparCampo() {
        textarea.value = '';
        lineCount.textContent = '0 linhas';
        textarea.focus();
    }

    // Validação antes do submit
    document.getElementById('formImportar').addEventListener('submit', function(e) {
        const valor = textarea.value.trim();
        if (!valor) {
            e.preventDefault();
            alert('Por favor, cole os dados antes de enviar.');
            textarea.focus();
        }
    });
</script>

@endsection