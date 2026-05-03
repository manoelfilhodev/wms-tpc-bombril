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
                <h3 class="mb-1 fw-bold text-dark">Etiquetas da Produção</h3>
                <p class="text-muted mb-0 small">
                    <span class="badge bg-light text-dark border me-2">#{{ $kit->id }}</span>
                    <span class="badge bg-primary bg-opacity-10 text-primary">{{ $kit->codigo_material }}</span>
                </p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('kit.etiquetas') }}" class="btn btn-outline-secondary btn-sm">
                <i class="mdi mdi-arrow-left me-1"></i> Voltar
            </a>
            <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="tooltip" title="Atualizar">
                <i class="mdi mdi-refresh"></i>
            </button>
        </div>
    </div>

    <!-- Card de Resumo -->
    @if($etiquetas->count() > 0)
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <i class="mdi mdi-information-outline text-primary me-2"></i>
                        <span class="text-muted small">
                            Total de <strong class="text-dark">{{ $etiquetas->count() }}</strong> etiqueta(s) gerada(s)
                        </span>
                    </div>
                    <a href="{{ route('kits.etiquetas.imprimirTudo', $kit->id) }}" 
                       target="_blank" 
                       class="btn btn-primary btn-sm">
                        <i class="mdi mdi-printer me-1"></i> Imprimir Todas
                    </a>
                </div>
            </div>
        </div>
    @endif

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
                            <th class="px-4 py-3 text-muted small fw-semibold text-end">
                                <i class="mdi mdi-counter me-1"></i> Quantidade
                            </th>
                            <th class="px-4 py-3 text-muted small fw-semibold">
                                <i class="mdi mdi-identifier me-1"></i> UID Palete
                            </th>
                            <th class="px-4 py-3 text-muted small fw-semibold text-center">
                                <i class="mdi mdi-clock-outline me-1"></i> Data Geração
                            </th>
                            <th class="px-4 py-3 text-muted small fw-semibold text-center">
                                <i class="mdi mdi-cog me-1"></i> Ações
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($etiquetas as $etiqueta)
                            <tr class="border-bottom">
                                <td class="px-4 py-3">
                                    <span class="badge bg-light text-dark border">{{ $etiqueta->id }}</span>
                                </td>
                                <td class="px-4 py-3 text-end">
                                    <span class="badge bg-primary bg-opacity-10 text-primary">
                                        {{ number_format($etiqueta->quantidade, 0, ',', '.') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="font-monospace text-muted small">{{ $etiqueta->palete_uid }}</span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="text-dark">
                                        <i class="mdi mdi-calendar-clock me-1 text-muted"></i>
                                        {{ \Carbon\Carbon::parse($etiqueta->created_at)->format('d/m/Y H:i') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <a href="{{ route('kits.etiquetas.reimprimir', $etiqueta->id) }}" 
                                       target="_blank" 
                                       class="btn btn-warning btn-sm"
                                       data-bs-toggle="tooltip" 
                                       title="Reimprimir etiqueta">
                                        <i class="mdi mdi-printer-outline me-1"></i> Reimprimir
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="mdi mdi-tag-off-outline display-4 d-block mb-3 opacity-25"></i>
                                        <p class="mb-0">Nenhuma etiqueta gerada</p>
                                        <small>As etiquetas serão geradas automaticamente após a programação</small>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        @if(method_exists($etiquetas, 'links'))
            <div class="card-footer bg-white border-top">
                {{ $etiquetas->links() }}
            </div>
        @endif
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
    .font-monospace { font-family: 'Courier New', monospace; }
</style>

@endsection

@push('scripts')
<script>
// Inicializar tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endpush