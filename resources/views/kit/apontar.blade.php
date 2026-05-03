@extends('layouts.app')

@section('content')

<div class="container-fluid px-4 py-3">
    @include('partials.breadcrumb-auto')
    
    <!-- Header com ícone roxo -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
            <div class="icon-wrapper me-3">
                <i class="mdi mdi-format-list-bulleted display-6 text-primary"></i>
            </div>
            <div>
                <h3 class="mb-1 fw-bold text-dark">Apontamento de Produção</h3>
                <p class="text-muted mb-0 small">Registre apontamentos escaneando QR Code ou digitando o código</p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('kit.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="mdi mdi-arrow-left me-1"></i> Voltar
            </a>
        </div>
    </div>

    <!-- Alertas -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center shadow-sm" role="alert">
            <i class="mdi mdi-check-circle me-2 fs-4"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show d-flex align-items-center shadow-sm" role="alert">
            <i class="mdi mdi-alert me-2 fs-4"></i>
            <div>{{ session('warning') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center shadow-sm" role="alert">
            <i class="mdi mdi-alert-circle me-2 fs-4"></i>
            <div>{{ session('error') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Card do Formulário -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="mb-0 fw-semibold text-dark">
                <i class="mdi mdi-qrcode-scan me-2 text-primary"></i>Novo Apontamento
            </h5>
        </div>
        <div class="card-body p-4">
            <form method="POST" action="{{ route('kits.apontar') }}">
                @csrf
                <div class="row">
                    <div class="col-md-8">
                        <label for="palete_uid" class="form-label small text-muted mb-1">
                            <i class="mdi mdi-barcode-scan me-1"></i>Escaneie o QR Code ou digite o código
                        </label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="mdi mdi-qrcode text-muted"></i>
                            </span>
                            <input type="text" name="palete_uid" id="palete_uid"
                                   class="form-control form-control-lg border-start-0" 
                                   placeholder="Digite ou escaneie o código"
                                   autofocus required>
                        </div>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            <i class="mdi mdi-check-circle me-2"></i> Apontar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Card da Tabela -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="mb-0 fw-semibold text-dark">
                <i class="mdi mdi-history me-2 text-primary"></i>Últimos Apontamentos
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="px-4 py-3 text-muted small fw-semibold">
                                <i class="mdi mdi-tag-outline me-1"></i> Palete UID
                            </th>
                            <th class="px-4 py-3 text-muted small fw-semibold">
                                <i class="mdi mdi-barcode me-1"></i> Cód. Material
                            </th>
                            <th class="px-4 py-3 text-muted small fw-semibold text-end">
                                <i class="mdi mdi-counter me-1"></i> Quantidade
                            </th>
                            <th class="px-4 py-3 text-muted small fw-semibold text-center">
                                <i class="mdi mdi-information-outline me-1"></i> Status
                            </th>
                            <th class="px-4 py-3 text-muted small fw-semibold">
                                <i class="mdi mdi-account-outline me-1"></i> Apontado Por
                            </th>
                            <th class="px-4 py-3 text-muted small fw-semibold text-center">
                                <i class="mdi mdi-clock-outline me-1"></i> Atualizado em
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($apontamentos as $a)
                            <tr class="border-bottom">
                                <td class="px-4 py-3">
                                    <span class="badge bg-light text-dark border font-monospace">
                                        {{ $a->palete_uid }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-dark fw-semibold">{{ $a->codigo_material }}</span>
                                </td>
                                <td class="px-4 py-3 text-end">
                                    <span class="badge bg-primary bg-opacity-10 text-primary">
                                        {{ number_format($a->quantidade, 0, ',', '.') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="badge bg-{{ $a->status == 'APONTADO' ? 'success' : 'secondary' }}">
                                        {{ $a->status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-dark">
                                        <i class="mdi mdi-account-circle me-1 text-muted"></i>
                                        {{ mb_strtoupper($a->apontadoPor->nome ?? '-') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="text-dark">
                                        <i class="mdi mdi-calendar-clock me-1 text-muted"></i>
                                        {{ $a->updated_at->format('d/m/Y H:i') }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="mdi mdi-clipboard-text-outline display-4 d-block mb-3 opacity-25"></i>
                                        <p class="mb-0">Nenhum apontamento realizado ainda</p>
                                        <small>Use o formulário acima para registrar o primeiro apontamento</small>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        @if(method_exists($apontamentos, 'links'))
            <div class="card-footer bg-white border-top">
                {{ $apontamentos->links() }}
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

    .input-group-text { background-color: #f8f9fa; }
    .form-control:focus { 
        border-color: #0d6efd; 
        box-shadow: 0 0 0 0.2rem rgba(13,110,253,0.1); 
    }
    .table tbody tr:hover { 
        background-color: #f8f9fa; 
        transition: background-color 0.2s ease; 
    }
    .card { border-radius: 0.5rem; }
    .badge { font-weight: 500; padding: 0.35em 0.65em; }
    .font-monospace { font-family: 'Courier New', monospace; }
    .alert { border-radius: 0.5rem; border: none; }
</style>

@endsection

@push('scripts')
<script>
// Auto-focus no campo após submit
document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('palete_uid');
    if (input) {
        input.focus();
        
        // Limpar campo após 2 segundos se houver sucesso
        @if(session('success'))
            setTimeout(() => {
                input.value = '';
                input.focus();
            }, 2000);
        @endif
    }
});
</script>
@endpush