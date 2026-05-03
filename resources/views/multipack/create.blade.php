@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    @include('partials.breadcrumb-auto')
    
    <!-- Header com ícone roxo -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
            <div class="icon-wrapper me-3">
                <i class="mdi mdi-package-variant-closed display-6 text-primary"></i>
            </div>
            <div>
                <h3 class="mb-1 fw-bold text-dark">Cadastro de Multipack</h3>
                <p class="text-muted mb-0 small">Configure os fatores de embalagem por SKU</p>
            </div>
        </div>
    </div>

    <!-- Card da Tabela -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <form action="{{ route('multipack.store') }}" method="POST">
                @csrf
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="multipack-table">
                        <thead class="bg-light">
                            <tr>
                                <th class="px-4 py-3 text-muted small fw-semibold">
                                    <i class="mdi mdi-barcode me-1"></i> SKU
                                </th>
                                <th class="px-4 py-3 text-muted small fw-semibold">
                                    <i class="mdi mdi-package-variant me-1"></i> Descrição
                                </th>
                                <th class="px-4 py-3 text-muted small fw-semibold">
                                    <i class="mdi mdi-numeric me-1"></i> Fator Embalagem
                                </th>
                                <th class="px-4 py-3 text-muted small fw-semibold text-center">
                                    <button type="button" class="btn btn-success btn-sm" onclick="addRow()">
                                        <i class="mdi mdi-plus"></i>
                                    </button>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="border-bottom">
                                <td class="px-4 py-3">
                                    <input type="text" name="sku[]" class="form-control" required>
                                </td>
                                <td class="px-4 py-3">
                                    <input type="text" name="descricao[]" class="form-control" required>
                                </td>
                                <td class="px-4 py-3">
                                    <input type="number" name="fator_embalagem[]" class="form-control" required>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">
                                        <i class="mdi mdi-minus"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-white border-top">
                    <button type="submit" class="btn btn-primary">
                        <i class="mdi mdi-content-save me-1"></i> Salvar
                    </button>
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
    .table tbody tr:hover { background-color: #f8f9fa; transition: background-color 0.2s ease; }
    .card { border-radius: 0.5rem; }
    .badge { font-weight: 500; padding: 0.35em 0.65em; }
</style>

<script>
    function addRow() {
        const table = document.getElementById("multipack-table").getElementsByTagName('tbody')[0];
        const newRow = table.rows[0].cloneNode(true);
        newRow.querySelectorAll('input').forEach(input => input.value = '');
        table.appendChild(newRow);
    }

    function removeRow(button) {
        const row = button.closest('tr');
        const table = document.getElementById("multipack-table").getElementsByTagName('tbody')[0];
        if (table.rows.length > 1) {
            row.remove();
        }
    }
</script>
@endsection