@extends('layouts.app')

@section('content')

<div class="container-fluid px-4 py-3">
    @include('partials.breadcrumb-auto')
    
    <!-- Header com ícone roxo -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
            <div class="icon-wrapper me-3">
                <i class="mdi mdi-pencil-box-outline display-6 text-primary"></i>
            </div>
            <div>
                <h3 class="mb-1 fw-bold text-dark">Editar Programação de Produção</h3>
                <p class="text-muted mb-0 small">Altere ou exclua programações existentes</p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('kit.programar') }}" class="btn btn-outline-secondary btn-sm">
                <i class="mdi mdi-arrow-left me-1"></i> Voltar
            </a>
        </div>
    </div>

    <!-- Alertas -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
            <i class="mdi mdi-check-circle me-2"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Card do Formulário -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="mb-0 fw-semibold text-dark">
                <i class="mdi mdi-cog-outline me-2 text-primary"></i>Selecionar Produção
            </h5>
        </div>
        <div class="card-body p-4">
            <form method="POST" action="{{ route('kit.atualizar', '') }}" id="formEditar">
                @csrf
                @method('PUT')

                <!-- Seleção do SKU -->
                <div class="mb-4">
                    <label for="kit_id" class="form-label small text-muted mb-1">
                        <i class="mdi mdi-barcode-scan me-1"></i>Selecione o SKU do Dia
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="mdi mdi-package-variant text-muted"></i>
                        </span>
                        <select class="form-select border-start-0" name="kit_id" id="kit_id" required>
                            <option value="">-- Selecione uma produção --</option>
                            @foreach ($kitsHoje as $kit)
                                <option value="{{ $kit->id }}"
                                    data-qtd="{{ $kit->quantidade_programada }}"
                                    data-date="{{ \Carbon\Carbon::parse($kit->data_montagem)->format('Y-m-d') }}">
                                    {{ $kit->codigo_material }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Campos de Edição (ocultos inicialmente) -->
                <div id="infoCampos" style="display: none;">
                    <hr class="my-4">
                    
                    <h6 class="mb-3 text-dark fw-semibold">
                        <i class="mdi mdi-information-outline me-2 text-info"></i>Dados da Produção
                    </h6>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small text-muted mb-1">
                                <i class="mdi mdi-counter me-1"></i>Quantidade Atual Programada
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="mdi mdi-numeric text-muted"></i>
                                </span>
                                <input type="number" class="form-control border-start-0 bg-light" 
                                       id="quantidade_atual" readonly>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label for="quantidade_programada" class="form-label small text-muted mb-1">
                                <i class="mdi mdi-pencil me-1"></i>Nova Quantidade
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="mdi mdi-numeric text-muted"></i>
                                </span>
                                <input type="number" class="form-control border-start-0" 
                                       name="quantidade_programada" min="1" required>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label for="data_montagem" class="form-label small text-muted mb-1">
                                <i class="mdi mdi-calendar me-1"></i>Nova Data da Produção
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="mdi mdi-calendar-clock text-muted"></i>
                                </span>
                                <input type="date" class="form-control border-start-0" 
                                       name="data_montagem" required>
                            </div>
                        </div>
                    </div>

                    <!-- Botões de Ação -->
                    <div class="mt-4 d-flex gap-2 align-items-center">
                        <button type="submit" class="btn btn-primary">
                            <i class="mdi mdi-check me-1"></i> Salvar Alterações
                        </button>
                        <a href="{{ route('kit.programar') }}" class="btn btn-outline-secondary">
                            <i class="mdi mdi-close me-1"></i> Cancelar
                        </a>
                        <div class="ms-auto">
                            <button type="button" class="btn btn-danger" id="btnExcluir"
                                onclick="confirmarExclusao()">
                                <i class="mdi mdi-delete me-1"></i> Excluir Produção
                            </button>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Form de Delete (oculto) -->
            <form method="POST" action="{{ route('kit.deletar', '') }}" id="formDelete" style="display: none;">
                @csrf
                @method('DELETE')
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
    .form-control:focus, .form-select:focus { 
        border-color: #0d6efd; 
        box-shadow: 0 0 0 0.2rem rgba(13,110,253,0.1); 
    }
    .card { border-radius: 0.5rem; }
</style>

@endsection

@push('scripts')
<script>
    document.getElementById('kit_id').addEventListener('change', function () {
        const select = this;
        const id = select.value;
        const qtd = select.options[select.selectedIndex].dataset.qtd;
        const data = select.options[select.selectedIndex].dataset.date;

        if (id) {
            document.getElementById('infoCampos').style.display = 'block';
            document.getElementById('quantidade_atual').value = qtd;
            document.querySelector('input[name=quantidade_programada]').value = qtd;
            document.querySelector('input[name=data_montagem]').value = data;
            document.getElementById('formEditar').action = `{{ url('/kit/programar') }}/${id}`;
            document.getElementById('formDelete').action = `{{ url('/kit/programar') }}/${id}`;
        } else {
            document.getElementById('infoCampos').style.display = 'none';
        }
    });

    function confirmarExclusao() {
        if (confirm('Tem certeza que deseja excluir esta programação de produção?')) {
            document.getElementById('formDelete').submit();
        }
    }
</script>
@endpush