@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header compacto no padrão roxo -->
    <div class="d-flex align-items-center mb-3">
        <div class="icon-wrapper me-3">
            <i class="mdi mdi-clipboard-check-multiple-outline display-6 text-primary"></i>
        </div>
        <div>
            <h3 class="mb-0 fw-bold text-dark">Contagens Realizadas</h3>
            @if(!empty($contagens) && count($contagens))
                <small class="text-muted">Total exibido:
                    <strong>{{ number_format(count($contagens), 0, ',', '.') }}</strong>
                </small>
            @endif
        </div>
    </div>

    <!-- Tabela (mesmos dados/colunas) -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="bg-light">
                        <tr class="text-muted small">
                            <th class="px-4 py-3">Ficha</th>
                            <th class="px-4 py-3">SKU</th>
                            <th class="px-4 py-3 text-center">Quantidade</th>
                            <th class="px-4 py-3">Contado por</th>
                            <th class="px-4 py-3 text-center">Data/Hora</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($contagens as $contagem)
                            <tr class="border-bottom">
                                <td class="px-4 py-3">
                                    <span class="badge bg-light text-dark border">{{ $contagem->ficha }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="badge bg-primary bg-opacity-10 text-primary">
                                        <i class="mdi mdi-barcode-scan me-1"></i>{{ $contagem->sku }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="fw-semibold text-dark">
                                        {{ number_format($contagem->quantidade, 0, ',', '.') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle me-2">
                                            <i class="mdi mdi-account"></i>
                                        </div>
                                        <span class="text-dark">{{ $contagem->usuario }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="d-flex flex-column">
                                        <span class="text-dark fw-semibold">
                                            {{ \Carbon\Carbon::parse($contagem->data_hora)->format('d/m/Y') }}
                                        </span>
                                        <small class="text-muted">
                                            {{ \Carbon\Carbon::parse($contagem->data_hora)->format('H:i') }}
                                        </small>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    Nenhuma contagem registrada ainda.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    /* Padrão visual roxo que estamos usando */
    .icon-wrapper {
        width: 60px; height: 60px;
        display: flex; align-items: center; justify-content: center;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(102,126,234,0.3);
    }
    .icon-wrapper i { color: #fff !important; }

    .avatar-circle {
        width: 32px; height: 32px; border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex; align-items: center; justify-content: center;
        color: #fff; font-size: 1rem;
    }

    .card { border-radius: 0.5rem; overflow: hidden; }
    .table tbody tr:hover {
        background-color: #f8f9fa;
        transition: background-color 0.2s ease;
    }
    .badge { font-weight: 500; padding: 0.35em 0.65em; }
</style>
@endsection