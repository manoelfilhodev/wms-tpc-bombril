@extends('layouts.app')

@section('title', 'Apontar Palete com Stretch')

@section('content')
<div class="container-fluid px-4 py-3 stretch-page">
    @include('partials.breadcrumb-auto')

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div class="d-flex align-items-center">
            <div class="stretch-icon me-3">
                <i class="mdi mdi-barcode-scan display-6"></i>
            </div>
            <div>
                <h3 class="mb-1 fw-bold text-dark">Apontar palete com Stretch</h3>
                <p class="text-muted mb-0 small">Registro web de validacao antes da integracao mobile</p>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center shadow-sm border-0" role="alert">
            <i class="mdi mdi-check-circle-outline fs-4 me-2"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0" role="alert">
            <div class="d-flex align-items-center">
                <i class="mdi mdi-alert-circle-outline fs-4 me-2"></i>
                <div>{{ $errors->first() }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show d-flex align-items-center shadow-sm border-0" role="alert">
            <i class="mdi mdi-alert-outline fs-4 me-2"></i>
            <div>{{ session('warning') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="mb-0 fw-semibold text-dark">
                <i class="mdi mdi-barcode-scan me-2 text-primary"></i>Novo apontamento
            </h5>
        </div>
        <div class="card-body p-4">
            <form method="POST" action="{{ route('stretch.apontar.store') }}" id="stretchForm">
                @csrf

                <div class="row g-3 align-items-end">
                    <div class="col-lg-7">
                        <label for="palete_codigo" class="form-label fw-semibold text-dark mb-1">
                            Codigo do palete
                        </label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="mdi mdi-barcode text-muted"></i>
                            </span>
                            <input
                                type="text"
                                name="palete_codigo"
                                id="palete_codigo"
                                value="{{ old('palete_codigo') }}"
                                class="form-control form-control-lg border-start-0 text-uppercase"
                                placeholder="Leia ou digite o codigo"
                                autocomplete="off"
                                autofocus
                                required
                            >
                        </div>
                    </div>

                    <div class="col-lg-3">
                        <label for="observacao" class="form-label fw-semibold text-dark mb-1">
                            Observacao
                        </label>
                        <input
                            type="text"
                            name="observacao"
                            id="observacao"
                            value="{{ old('observacao') }}"
                            class="form-control form-control-lg"
                            placeholder="Opcional"
                            maxlength="1000"
                        >
                    </div>

                    <div class="col-lg-2">
                        <button type="submit" class="btn btn-danger btn-lg w-100 stretch-submit" @disabled(! $tabelaDisponivel)>
                            <i class="mdi mdi-check-bold me-2"></i>APONTAR
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-semibold text-dark">
                <i class="mdi mdi-history me-2 text-primary"></i>Ultimos apontamentos
            </h5>
            <span class="badge bg-light text-dark border">Stretch</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="px-4 py-3 text-muted small fw-semibold">Palete</th>
                            <th class="px-4 py-3 text-muted small fw-semibold">Status</th>
                            <th class="px-4 py-3 text-muted small fw-semibold">Origem</th>
                            <th class="px-4 py-3 text-muted small fw-semibold">Usuario</th>
                            <th class="px-4 py-3 text-muted small fw-semibold">Unidade</th>
                            <th class="px-4 py-3 text-muted small fw-semibold">Data/Hora</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($apontamentos as $apontamento)
                            <tr>
                                <td class="px-4 py-3">
                                    <span class="badge bg-light text-dark border font-monospace">
                                        {{ $apontamento->palete_codigo }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="badge bg-success">{{ $apontamento->status }}</span>
                                </td>
                                <td class="px-4 py-3">{{ $apontamento->origem }}</td>
                                <td class="px-4 py-3">{{ $apontamento->usuario->nome ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $apontamento->unidade_id ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    {{ optional($apontamento->apontado_em_servidor)->format('d/m/Y H:i') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <i class="mdi mdi-clipboard-text-outline display-4 d-block mb-3 text-muted opacity-25"></i>
                                    <p class="mb-0 text-muted">Nenhum palete apontado ainda.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($apontamentos->hasPages())
            <div class="card-footer bg-white border-top">
                {{ $apontamentos->links() }}
            </div>
        @endif
    </div>
</div>

<style>
    .stretch-icon {
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #111827;
        color: #fff;
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 8px;
        box-shadow: 0 8px 20px rgba(17, 24, 39, 0.18);
    }

    .stretch-page .card {
        border-radius: 8px;
        overflow: hidden;
    }

    .stretch-page .form-control:focus {
        border-color: #dc3545;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.12);
    }

    .stretch-submit {
        min-height: 48px;
        letter-spacing: 0;
    }

    .font-monospace {
        font-family: "Courier New", monospace;
    }
</style>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const input = document.getElementById('palete_codigo');
        const form = document.getElementById('stretchForm');

        if (input) {
            input.focus();
            input.addEventListener('input', function () {
                input.value = input.value.toUpperCase();
            });
        }

        if (form && input) {
            form.addEventListener('submit', function () {
                input.value = input.value.trim().toUpperCase();
            });
        }
    });
</script>
@endpush
