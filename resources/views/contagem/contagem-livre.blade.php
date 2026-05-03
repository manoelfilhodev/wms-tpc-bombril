@extends($layout)

@section('title', 'Contagem Livre de Itens')

@section('content')
<div class="container-fluid px-4 py-3">
    @include('partials.breadcrumb-auto')

    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div class="d-flex align-items-center">
            <div class="icon-wrapper me-3">
                <i class="mdi mdi-barcode-scan display-6 text-primary"></i>
            </div>
            <div>
                <h3 class="mb-1 fw-bold text-dark">Contagem Livre de Itens</h3>
                <p class="text-muted mb-0 small">Registre contagens avulsas de SKUs</p>
            </div>
        </div>
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
            <i class="mdi mdi-arrow-left"></i> Voltar
        </a>
    </div>

    <!-- Mensagens de feedback -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center shadow-sm border-0" role="alert">
            <i class="mdi mdi-check-circle-outline fs-4 me-2"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @elseif(session('error'))
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center shadow-sm border-0" role="alert">
            <i class="mdi mdi-alert-circle-outline fs-4 me-2"></i>
            <div>{{ session('error') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-semibold">
                        <i class="mdi mdi-form-select me-2 text-primary"></i>
                        Registrar Contagem
                    </h6>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('contagem.livre.salvar') }}" method="POST" id="formContagemLivre">
                        @csrf

                        <div class="mb-3">
                            <label for="ficha" class="form-label fw-semibold text-dark mb-1">
                                <i class="mdi mdi-file-document-outline me-1"></i> Número da Ficha
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="mdi mdi-numeric text-muted"></i>
                                </span>
                                <input type="text" class="form-control border-start-0" id="ficha" name="ficha" 
                                       placeholder="Ex: 001, A-123" required autofocus>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="sku" class="form-label fw-semibold text-dark mb-1">
                                <i class="mdi mdi-barcode-scan me-1"></i> SKU
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="mdi mdi-barcode text-muted"></i>
                                </span>
                                <input type="text" class="form-control border-start-0" id="sku" name="sku" 
                                       placeholder="Digite o SKU" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="quantidade" class="form-label fw-semibold text-dark mb-1">
                                <i class="mdi mdi-counter me-1"></i> Quantidade
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="mdi mdi-numeric-1-box-multiple-outline text-muted"></i>
                                </span>
                                <input type="number" class="form-control border-start-0 text-center" id="quantidade" name="quantidade" 
                                       placeholder="0" required min="1" inputmode="numeric" pattern="[0-9]*">
                            </div>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="mdi mdi-content-save-outline me-2"></i> Salvar Contagem
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .icon-wrapper {
        width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 12px; box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }
    .icon-wrapper i { color: white !important; }
    .card { border-radius: 0.5rem; overflow: hidden; }
    .form-control:focus { border-color: #0d6efd; box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.1); }
    .input-group-text { background-color: #f8f9fa; }
    .alert { border-radius: 0.5rem; }
    .btn-lg { font-size: 1.1rem; padding: 0.75rem 1.5rem; }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Foca no campo Ficha ao carregar a página
        document.getElementById('ficha').focus();

        // Adiciona evento para focar no próximo campo ao pressionar Enter
        document.getElementById('ficha').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('sku').focus();
            }
        });

        document.getElementById('sku').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('quantidade').focus();
            }
        });

        document.getElementById('quantidade').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('formContagemLivre').submit();
            }
        });
    });
</script>
@endsection