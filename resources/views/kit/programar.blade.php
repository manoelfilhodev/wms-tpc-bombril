@extends($layout)

@section('content')

<div class="container-fluid px-4 py-3">
    @include('partials.breadcrumb-auto')
    
    <!-- Header com ícone roxo -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
            <div class="icon-wrapper me-3">
                <i class="mdi mdi-playlist-plus display-6 text-primary"></i>
            </div>
            <div>
                <h3 class="mb-1 fw-bold text-dark">Programar Produção</h3>
                <p class="text-muted mb-0 small">Crie novas programações e gerencie as existentes</p>
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
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
            <i class="mdi mdi-check-circle me-2"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
            <i class="mdi mdi-alert-circle me-2"></i>
            <div>{{ session('error') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Card do Formulário -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="mb-0 fw-semibold text-dark">
                <i class="mdi mdi-plus-circle-outline me-2 text-primary"></i>Nova Programação
            </h5>
        </div>
        <div class="card-body p-4">
            <form method="POST" action="{{ route('kit.programar.store') }}">
                @csrf
                
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="codigo_material" class="form-label small text-muted mb-1">
                            <i class="mdi mdi-barcode-scan me-1"></i>Código do Material
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="mdi mdi-package-variant text-muted"></i>
                            </span>
                            <input type="text" id="codigo_material" name="codigo_material" 
                                   class="form-control border-start-0" 
                                   placeholder="Digite o código do kit" 
                                   value="{{ old('codigo_material') }}" required>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label for="quantidade_programada" class="form-label small text-muted mb-1">
                            <i class="mdi mdi-counter me-1"></i>Quantidade a Produzir
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="mdi mdi-numeric text-muted"></i>
                            </span>
                            <input type="number" id="quantidade_programada" name="quantidade_programada" 
                                   class="form-control border-start-0" 
                                   placeholder="0" min="1"
                                   value="{{ old('quantidade_programada') }}" required>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label for="data_montagem" class="form-label small text-muted mb-1">
                            <i class="mdi mdi-calendar me-1"></i>Data da Montagem
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="mdi mdi-calendar-clock text-muted"></i>
                            </span>
                            <input type="date" id="data_montagem" name="data_montagem" 
                                   class="form-control border-start-0"
                                   value="{{ old('data_montagem') }}" required>
                        </div>
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="mdi mdi-check me-1"></i> Programar
                    </button>
                    <button type="reset" class="btn btn-outline-secondary">
                        <i class="mdi mdi-refresh me-1"></i> Limpar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Card da Tabela -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="mb-0 fw-semibold text-dark">
                <i class="mdi mdi-format-list-bulleted me-2 text-primary"></i>Programações Existentes
            </h5>
        </div>
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
                                <i class="mdi mdi-calendar me-1"></i> Data Montagem
                            </th>
                            <th class="px-4 py-3 text-muted small fw-semibold text-center">
                                <i class="mdi mdi-cog me-1"></i> Ações
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(($kits ?? []) as $kit)
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
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button type="button" class="btn btn-outline-primary" data-bs-toggle="tooltip" title="Editar">
                                            <i class="mdi mdi-pencil"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="tooltip" title="Excluir">
                                            <i class="mdi mdi-delete"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="mdi mdi-playlist-remove display-4 d-block mb-3 opacity-25"></i>
                                        <p class="mb-0">Nenhuma programação encontrada</p>
                                        <small>Crie uma nova programação usando o formulário acima</small>
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
    .btn-group-sm .btn { padding: 0.25rem 0.5rem; }
</style>

@endsection