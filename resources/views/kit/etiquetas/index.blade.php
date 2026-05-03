@extends($layout)

@section('content')

<div class="container-fluid px-4 py-3">
    @include('partials.breadcrumb-auto')
    
    <!-- Header com ícone roxo -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
            <div class="icon-wrapper me-3">
                <i class="mdi mdi-tag-multiple display-6 text-primary"></i>
            </div>
            <div>
                <h3 class="mb-1 fw-bold text-dark">Lista de Produções</h3>
                <p class="text-muted mb-0 small">Gerencie etiquetas de todas as produções programadas</p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('kit.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="mdi mdi-arrow-left me-1"></i> Voltar
            </a>
            <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="tooltip" title="Atualizar">
                <i class="mdi mdi-refresh"></i>
            </button>
        </div>
    </div>

    <!-- Card da Tabela -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="px-4 py-3 text-muted small fw-semibold">
                                <i class="mdi mdi-pound me-1"></i> ID
                            </th>
                            <th class="px-4 py-3 text-muted small fw-semibold">
                                <i class="mdi mdi-barcode me-1"></i> Código Material
                            </th>
                            <th class="px-4 py-3 text-muted small fw-semibold text-end">
                                <i class="mdi mdi-counter me-1"></i> Qtd Programada
                            </th>
                            <th class="px-4 py-3 text-muted small fw-semibold">
                                <i class="mdi mdi-calendar me-1"></i> Data Produção
                            </th>
                            <th class="px-4 py-3 text-muted small fw-semibold text-center">
                                <i class="mdi mdi-cog me-1"></i> Ações
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($kits as $kit)
                            <tr class="border-bottom">
                                <td class="px-4 py-3">
                                    <span class="badge bg-light text-dark border">{{ $kit->id }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-dark fw-semibold">{{ $kit->codigo_material }}</span>
                                </td>
                                <td class="px-4 py-3 text-end">
                                    <span class="badge bg-primary bg-opacity-10 text-primary">
                                        {{ number_format($kit->quantidade_programada, 0, ',', '.') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-dark">
                                        <i class="mdi mdi-calendar-outline me-1 text-muted"></i>
                                        {{ \Carbon\Carbon::parse($kit->data_montagem)->format('d/m/Y') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <button type="button" 
                                            class="btn btn-success btn-sm"
                                            onclick="abrirModalEtiquetas({{ $kit->id }})"
                                            data-bs-toggle="tooltip" 
                                            title="Gerenciar etiquetas">
                                        <i class="mdi mdi-tag-multiple me-1"></i> Etiquetas
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="mdi mdi-package-variant-closed display-4 d-block mb-3 opacity-25"></i>
                                        <p class="mb-0">Nenhuma produção encontrada</p>
                                        <small>Crie uma nova programação para começar</small>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Etiquetas -->
<div class="modal fade" id="modalEtiquetas" tabindex="-1" aria-labelledby="modalEtiquetasLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light border-bottom">
                <h5 class="modal-title fw-semibold text-dark" id="modalEtiquetasLabel">
                    <i class="mdi mdi-printer-outline me-2 text-primary"></i>Impressão de Etiquetas
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted mb-4">
                    <i class="mdi mdi-information-outline me-1"></i>
                    Selecione uma das opções abaixo para imprimir as etiquetas:
                </p>
                <div class="d-grid gap-2">
                    <a href="#" id="btnImprimirTudo" class="btn btn-primary btn-lg" target="_blank">
                        <i class="mdi mdi-printer me-2"></i> Imprimir Todas as Etiquetas
                    </a>
                    <a href="#" id="btnReimprimir" class="btn btn-warning btn-lg" target="_blank" style="display:none;">
                        <i class="mdi mdi-refresh me-2"></i> Reimprimir Etiqueta
                    </a>
                </div>
            </div>
            <div class="modal-footer bg-light border-top">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="mdi mdi-close me-1"></i> Fechar
                </button>
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

    .table tbody tr:hover { 
        background-color: #f8f9fa; 
        transition: background-color 0.2s ease; 
    }
    .card { border-radius: 0.5rem; }
    .badge { font-weight: 500; padding: 0.35em 0.65em; }
    .modal-content { border-radius: 0.5rem; }
</style>

@endsection

@push('scripts')
<script>
function abrirModalEtiquetas(kitId, etiquetaId = null) {
    // Imprimir tudo
    let urlImprimirTudo = "{{ route('kits.etiquetas.imprimirTudo', ':id') }}".replace(':id', kitId);
    document.getElementById('btnImprimirTudo').href = urlImprimirTudo;

    // Reimprimir uma etiqueta específica
    if (etiquetaId) {
        let urlReimprimir = "{{ route('kits.etiquetas.reimprimir', ':id') }}".replace(':id', etiquetaId);
        document.getElementById('btnReimprimir').href = urlReimprimir;
        document.getElementById('btnReimprimir').style.display = 'block';
    } else {
        document.getElementById('btnReimprimir').style.display = 'none';
    }

    // Usar Bootstrap 5 modal
    const modal = new bootstrap.Modal(document.getElementById('modalEtiquetas'));
    modal.show();
}

// Inicializar tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endpush