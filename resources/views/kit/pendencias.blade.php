@extends('layouts.app')

@section('content')

<div class="container-fluid px-4 py-3">
    @include('partials.breadcrumb-auto')
    
    <!-- Header com ícone roxo -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
            <div class="icon-wrapper me-3">
                <i class="mdi mdi-alert-circle display-6 text-primary"></i>
            </div>
            <div>
                <h3 class="mb-1 fw-bold text-dark">Pendências de Apontamento</h3>
                <p class="text-muted mb-0 small">Acompanhe etiquetas de produção ainda não apontadas</p>
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

    @if($pendencias->isEmpty())
        <!-- Card de Sucesso (sem pendências) -->
        <div class="card shadow-sm border-0">
            <div class="card-body text-center py-5">
                <div class="mb-4">
                    <div class="success-icon-wrapper mx-auto">
                        <i class="mdi mdi-check-circle display-1 text-success"></i>
                    </div>
                </div>
                <h4 class="text-dark fw-semibold mb-2">Tudo em dia!</h4>
                <p class="text-muted mb-0">
                    Nenhuma pendência de apontamento encontrada. 🎉
                </p>
            </div>
        </div>
    @else
        <!-- Card de Resumo -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body p-3">
                <div class="d-flex align-items-center">
                    <i class="mdi mdi-alert-outline text-danger me-2 fs-4"></i>
                    <span class="text-muted">
                        <strong class="text-danger">{{ $pendencias->count() }}</strong> 
                        {{ $pendencias->count() === 1 ? 'etiqueta pendente' : 'etiquetas pendentes' }} de apontamento
                    </span>
                </div>
            </div>
        </div>

        <!-- Card da Tabela -->
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="px-4 py-3 text-muted small fw-semibold text-center">
                                    <i class="mdi mdi-tag-outline me-1"></i> Etiqueta
                                </th>
                                <th class="px-4 py-3 text-muted small fw-semibold">
                                    <i class="mdi mdi-barcode me-1"></i> SKU
                                </th>
                                <th class="px-4 py-3 text-muted small fw-semibold text-end">
                                    <i class="mdi mdi-counter me-1"></i> Quantidade
                                </th>
                                <th class="px-4 py-3 text-muted small fw-semibold text-center">
                                    <i class="mdi mdi-information-outline me-1"></i> Status
                                </th>
                                <th class="px-4 py-3 text-muted small fw-semibold text-center">
                                    <i class="mdi mdi-clock-outline me-1"></i> Gerado em
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendencias as $p)
                                <tr class="border-bottom">
                                    <td class="px-4 py-3 text-center">
                                        <span class="badge bg-light text-dark border font-monospace">
                                            {{ $p->palete_uid }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="text-dark fw-semibold">
                                            {{ $p->codigo_material ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-end">
                                        <span class="badge bg-primary bg-opacity-10 text-primary">
                                            {{ number_format($p->quantidade ?? 0, 0, ',', '.') }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="badge bg-danger">
                                            {{ $p->status ?? 'GERADO' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="text-dark">
                                            <i class="mdi mdi-calendar-clock me-1 text-muted"></i>
                                            {{ \Carbon\Carbon::parse($p->created_at)->format('d/m/Y H:i') }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            
            @if(method_exists($pendencias, 'links'))
                <div class="card-footer bg-white border-top">
                    {{ $pendencias->links() }}
                </div>
            @endif
        </div>
    @endif
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

    .success-icon-wrapper {
        width: 100px; height: 100px;
        display: flex; align-items: center; justify-content: center;
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        border-radius: 50%;
        box-shadow: 0 8px 24px rgba(16,185,129,0.3);
    }
    .success-icon-wrapper i { color: #fff !important; }

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