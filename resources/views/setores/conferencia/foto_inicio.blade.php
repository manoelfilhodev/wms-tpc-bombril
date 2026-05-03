@extends('layouts.app')

@section('content')

<div class="container-fluid px-4 py-3">
    @include('partials.breadcrumb-auto')
    
    <!-- Header com ícone -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
            <div class="icon-wrapper me-3">
                <i class="mdi mdi-camera display-6 text-primary"></i>
            </div>
            <div>
                <h3 class="mb-1 fw-bold text-dark">Foto Inicial do Veículo</h3>
                <p class="text-muted mb-0 small">Recebimento NF {{ $recebimento->nota_fiscal }}</p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('setores.conferencia.index') }}" class="btn btn-outline-secondary btn-sm" data-bs-toggle="tooltip" title="Voltar">
                <i class="mdi mdi-arrow-left"></i>
            </a>
        </div>
    </div>

    {{-- Exibir erros de validação --}}
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="mdi mdi-alert-circle me-2"></i>
            <strong>Erro ao salvar:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Mensagem de sucesso --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="mdi mdi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Mensagem de erro genérico --}}
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="mdi mdi-alert-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Card Principal -->
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <form action="{{ route('setores.conferencia.salvarFotoInicio', $recebimento->id) }}" method="POST" enctype="multipart/form-data" id="formFotoInicio">
                        @csrf
                        
                        <!-- Área de Upload -->
                        <div class="upload-area text-center mb-4" id="uploadArea">
                            <div class="upload-icon mb-3">
                                <i class="mdi mdi-cloud-upload mdi-48px text-primary"></i>
                            </div>
                            <h5 class="fw-semibold mb-2">Selecione a Foto do Veículo</h5>
                            <p class="text-muted small mb-3">Clique ou arraste a imagem para fazer upload</p>
                            <label for="foto_inicio_veiculo" class="btn btn-primary btn-lg">
                                <i class="mdi mdi-camera me-2"></i>Escolher Foto
                            </label>
                            <input class="d-none" type="file" name="foto" id="foto_inicio_veiculo" accept="image/*" required>
                            <p class="text-muted small mt-2 mb-0">Formatos aceitos: JPG, PNG, JPEG (máx. 5MB)</p>
                        </div>

                        <!-- Preview da Foto -->
                        <div class="preview-container mb-4" id="previewContainer" style="display: none;">
                            <div class="preview-header d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-semibold mb-0">
                                    <i class="mdi mdi-image-check text-success me-2"></i>Prévia da Imagem
                                </h6>
                                <button type="button" class="btn btn-sm btn-outline-danger" id="btnRemoverFoto">
                                    <i class="mdi mdi-close"></i> Remover
                                </button>
                            </div>
                            <div class="preview-image-wrapper">
                                <img id="previewFoto" src="#" alt="Prévia da Foto" class="preview-image">
                            </div>
                        </div>

                        <!-- Botões de Ação -->
                        <div class="d-flex gap-2 justify-content-end">
                            <a href="{{ route('setores.conferencia.index') }}" class="btn btn-outline-secondary">
                                <i class="mdi mdi-close me-1"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-success" id="btnSalvar" disabled>
                                <i class="mdi mdi-check me-1"></i>Salvar Foto
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Info Card -->
            <div class="card border-0 bg-light mt-3">
                <div class="card-body p-3">
                    <div class="d-flex align-items-start">
                        <i class="mdi mdi-information text-primary me-2 mt-1"></i>
                        <small class="text-muted">
                            <strong>Dica:</strong> Certifique-se de que a foto está nítida e mostra claramente o início do veículo para facilitar a conferência.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Overlay loader --}}
<div id="overlayLoader" class="d-none">
    <div class="overlay-background"></div>
    <div class="overlay-content text-center">
        <div class="logo-loader">
            <img src="{{ asset('images/logo-sem-nome.png') }}" alt="Carregando..." class="systex-seta-gif">
        </div>
        <div class="spinner-border text-light mt-3" role="status">
            <span class="visually-hidden">Carregando...</span>
        </div>
        <p class="text-light mt-3 fw-semibold">Processando imagem...</p>
        <p class="text-light small">Aguarde enquanto salvamos a foto</p>
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

    .upload-area {
        border: 2px dashed #dee2e6;
        border-radius: 0.75rem;
        padding: 3rem 2rem;
        background: #f8f9fa;
        transition: all 0.3s ease;
    }

    .upload-area:hover {
        border-color: #667eea;
        background: #f0f2ff;
    }

    .upload-icon i {
        color: #667eea;
    }

    .preview-container {
        border: 1px solid #dee2e6;
        border-radius: 0.75rem;
        padding: 1.5rem;
        background: #f8f9fa;
    }

    .preview-image-wrapper {
        border-radius: 0.5rem;
        overflow: hidden;
        background: #fff;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .preview-image {
        width: 100%;
        height: auto;
        display: block;
        max-height: 500px;
        object-fit: contain;
    }

    #overlayLoader {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1055;
    }

    .overlay-background {
        position: absolute;
        background: rgba(0, 0, 0, 0.85);
        width: 100%;
        height: 100%;
        backdrop-filter: blur(4px);
    }

    .overlay-content {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }

    .logo-loader {
        position: relative;
        display: inline-block;
    }

    .systex-seta-gif {
        width: 120px;
        display: block;
        margin: 0 auto;
        filter: drop-shadow(0 4px 12px rgba(255,255,255,0.2));
    }

    .btn {
        border-radius: 0.5rem;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102,126,234,0.4);
    }

    .btn-success:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .alert {
        border-radius: 0.75rem;
    }

    .alert ul {
        padding-left: 1.25rem;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const inputFoto = document.getElementById('foto_inicio_veiculo');
    const previewFoto = document.getElementById('previewFoto');
    const previewContainer = document.getElementById('previewContainer');
    const uploadArea = document.getElementById('uploadArea');
    const btnSalvar = document.getElementById('btnSalvar');
    const btnRemoverFoto = document.getElementById('btnRemoverFoto');
    const formFotoInicio = document.getElementById('formFotoInicio');

    if (!inputFoto || !formFotoInicio) {
        console.error('Elementos do formulário não encontrados');
        return;
    }

    // Preview da imagem ao selecionar
    inputFoto.addEventListener('change', function (e) {
        if (e.target.files && e.target.files[0]) {
            const file = e.target.files[0];
            
            // Validação de tamanho (5MB)
            if (file.size > 5 * 1024 * 1024) {
                alert('Arquivo muito grande! Tamanho máximo: 5MB');
                inputFoto.value = '';
                return;
            }

            // Validação de tipo
            if (!file.type.match('image/(jpeg|jpg|png)')) {
                alert('Formato inválido! Use apenas JPG, JPEG ou PNG');
                inputFoto.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = function (ev) {
                previewFoto.src = ev.target.result;
                previewContainer.style.display = 'block';
                uploadArea.style.display = 'none';
                btnSalvar.disabled = false;
            };
            reader.readAsDataURL(file);
        }
    });

    // Remover foto
    if (btnRemoverFoto) {
        btnRemoverFoto.addEventListener('click', function() {
            inputFoto.value = '';
            previewFoto.src = '#';
            previewContainer.style.display = 'none';
            uploadArea.style.display = 'block';
            btnSalvar.disabled = true;
        });
    }

    // Drag and drop
    ['dragover', 'dragleave', 'drop'].forEach(eventName => {
        uploadArea.addEventListener(eventName, function(e) {
            e.preventDefault();
            e.stopPropagation();
        });
    });

    uploadArea.addEventListener('dragover', function() {
        uploadArea.style.borderColor = '#667eea';
        uploadArea.style.background = '#f0f2ff';
    });

    uploadArea.addEventListener('dragleave', function() {
        uploadArea.style.borderColor = '#dee2e6';
        uploadArea.style.background = '#f8f9fa';
    });

    uploadArea.addEventListener('drop', function(e) {
        uploadArea.style.borderColor = '#dee2e6';
        uploadArea.style.background = '#f8f9fa';
        
        if (e.dataTransfer.files && e.dataTransfer.files[0]) {
            inputFoto.files = e.dataTransfer.files;
            inputFoto.dispatchEvent(new Event('change', { bubbles: true }));
        }
    });

    // Validação e loader ao submeter
    formFotoInicio.addEventListener('submit', function (e) {
        // Validação extra antes de enviar
        if (!inputFoto.files || !inputFoto.files[0]) {
            e.preventDefault();
            alert('Por favor, selecione uma imagem antes de salvar.');
            return false;
        }

        // Desabilita botão para evitar double-click
        btnSalvar.disabled = true;
        btnSalvar.innerHTML = '<i class="mdi mdi-loading mdi-spin me-1"></i>Salvando...';
        
        // Mostra loader
        document.getElementById('overlayLoader').classList.remove('d-none');
    });

    // Inicializar tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>

@endsection