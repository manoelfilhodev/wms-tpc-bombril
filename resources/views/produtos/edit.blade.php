@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    @include('partials.breadcrumb-auto')

    <!-- Header com ícone roxo -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
            <div class="icon-wrapper me-3">
                <i class="mdi mdi-package-variant display-6 text-primary"></i>
            </div>
            <div>
                <h3 class="mb-1 fw-bold text-dark">Editar Produto</h3>
                <p class="text-muted mb-0 small">Atualize as informações do produto</p>
            </div>
        </div>
    </div>

    <!-- Card do Formulário -->
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form action="{{ route('produtos.update', $produto->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label small text-muted mb-1">SKU <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="mdi mdi-barcode-scan text-muted"></i>
                        </span>
                        <input type="text" name="sku" class="form-control border-start-0" value="{{ old('sku', $produto->sku) }}" placeholder="Digite o SKU" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small text-muted mb-1">EAN</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="mdi mdi-barcode text-muted"></i>
                        </span>
                        <input type="text" name="ean" class="form-control border-start-0" value="{{ old('ean', $produto->ean) }}" placeholder="Código de barras (EAN)">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small text-muted mb-1">Descrição <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="mdi mdi-text-short text-muted"></i>
                        </span>
                        <input type="text" name="descricao" class="form-control border-start-0" value="{{ old('descricao', $produto->descricao) }}" placeholder="Descrição do produto" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small text-muted mb-1">Quantidade Estoque <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="mdi mdi-counter text-muted"></i>
                        </span>
                        <input type="number" name="quantidade_estoque" class="form-control border-start-0" value="{{ old('quantidade_estoque', $produto->quantidade_estoque) }}" placeholder="0" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label small text-muted mb-1">Lastro <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="mdi mdi-view-grid text-muted"></i>
                            </span>
                            <input type="number" name="lastro" class="form-control border-start-0" value="{{ old('lastro', $produto->lastro) }}" placeholder="0" required>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label small text-muted mb-1">Camada <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="mdi mdi-layers text-muted"></i>
                            </span>
                            <input type="number" name="camada" class="form-control border-start-0" value="{{ old('camada', $produto->camada) }}" placeholder="0" required>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label small text-muted mb-1">Paletização <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="mdi mdi-package-variant-closed text-muted"></i>
                            </span>
                            <input type="number" name="paletizacao" class="form-control border-start-0" value="{{ old('paletizacao', $produto->paletizacao) }}" placeholder="0" required>
                        </div>
                    </div>
                </div>

                <div class="card-footer bg-white border-top mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="mdi mdi-content-save me-1"></i> Atualizar
                    </button>
                    <a href="{{ route('produtos.index') }}" class="btn btn-outline-secondary">
                        <i class="mdi mdi-arrow-left me-1"></i> Voltar
                    </a>
                </div>
            </form>
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

    .input-group-text { background-color: #f8f9fa; }
    .form-control:focus { border-color: #0d6efd; box-shadow: 0 0 0 0.2rem rgba(13,110,253,0.1); }
    .card { border-radius: 0.5rem; }
</style>

@endsection