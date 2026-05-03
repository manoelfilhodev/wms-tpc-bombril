@extends($layout)

@section('title', 'Nova Contagem de Itens')

@section('content')
<div class="container-fluid px-4 py-3">
    @include('partials.breadcrumb-auto')

    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div class="d-flex align-items-center">
            <div class="icon-wrapper me-3">
                <i class="mdi mdi-clipboard-plus-outline display-6 text-primary"></i>
            </div>
            <div>
                <h3 class="mb-1 fw-bold text-dark">Nova Contagem de Itens</h3>
                <p class="text-muted mb-0 small">Obrigatório contar todos os 6 itens listados</p>
            </div>
        </div>
        <a href="{{ route('contagem.itens.index') }}" class="btn btn-outline-secondary">
            <i class="mdi mdi-arrow-left"></i> Voltar
        </a>
    </div>

    <!-- Alertas de validação -->
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0" role="alert">
            <div class="d-flex">
                <i class="mdi mdi-alert-circle-outline fs-4 me-2"></i>
                <div>
                    <strong>Corrija os erros para continuar:</strong>
                    <ul class="mb-0 mt-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('contagem.itens.storeMultiple') }}" method="POST" id="formContagem">
        @csrf

        <!-- Instruções -->
        <div class="row g-3 mb-3">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="mb-0 fw-semibold">
                            <i class="mdi mdi-information-outline me-2 text-primary"></i>
                            Instruções
                        </h6>
                    </div>
                    <div class="card-body">
                        <ul class="mb-0 small text-muted">
                            <li>Preencha a quantidade contada para cada material.</li>
                            <li>Todos os <strong>6 itens</strong> devem ser informados para habilitar o envio.</li>
                            <li>Use apenas números inteiros (sem vírgula).</li>
                            <li>Aperte <kbd>Tab</kbd> para ir para o próximo campo rapidamente.</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <!-- Status de preenchimento -->
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body d-flex flex-column justify-content-center">
                        @php $totalEsperado = 6; @endphp
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small">Itens preenchidos</span>
                            <span class="badge bg-primary bg-opacity-10 text-primary" id="badgePreenchidos">
                                0 / {{ $totalEsperado }}
                            </span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div id="progressPreenchidos" class="progress-bar bg-primary" role="progressbar" style="width: 0%;"></div>
                        </div>
                        <small class="text-muted mt-2">Preencha todos os campos para habilitar o envio.</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabela de contagem -->
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="px-4 py-3 text-muted small fw-semibold">Material</th>
                                <th class="px-4 py-3 text-muted small fw-semibold text-center" style="width: 200px;">Quantidade</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($materiais as $m)
                                <tr class="border-bottom">
                                    <td class="px-4 py-3">
                                        <div class="d-flex flex-column">
                                            <span class="fw-semibold text-dark">{{ $m->codigo_material }}</span>
                                            <small class="text-muted">{{ $m->descricao }}</small>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <div class="input-group input-group-sm justify-content-center" style="max-width: 180px; margin: 0 auto;">
                                            <span class="input-group-text bg-light">
                                                <i class="mdi mdi-counter"></i>
                                            </span>
                                            <input
                                                type="number"
                                                name="quantidades[{{ $m->codigo_material }}]"
                                                class="form-control text-center campo-quantidade"
                                                min="0"
                                                step="1"
                                                inputmode="numeric"
                                                pattern="[0-9]*"
                                                placeholder="0"
                                                required
                                            >
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Rodapé com ações -->
                <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center">
                    <a href="{{ route('contagem.itens.index') }}" class="btn btn-outline-secondary">
                        <i class="mdi mdi-arrow-left"></i> Voltar
                    </a>
                    <button type="submit" id="btnSalvar" class="btn btn-success" disabled>
                        <i class="mdi mdi-content-save-outline me-1"></i> Salvar Contagem
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
    .icon-wrapper {
        width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 12px; box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }
    .icon-wrapper i { color: white !important; }
    .card { border-radius: 0.5rem; overflow: hidden; }
    .table tbody tr:hover { background-color: #f8f9fa; transition: background-color 0.2s ease; }
    kbd { padding: 0.2rem 0.45rem; border-radius: 0.25rem; background: #f8f9fa; border: 1px solid #e9ecef; }
    .form-control:focus { border-color: #0d6efd; box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.1); }
    .input-group-text { background-color: #f8f9fa; }
    .campo-quantidade.is-valid { border-color: #198754 !important; background-color: #f8fff9; }
    .campo-quantidade.is-invalid { border-color: #dc3545 !important; background-color: #fff8f8; }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function(){
        const campos = Array.from(document.querySelectorAll('.campo-quantidade'));
        const btnSalvar = document.getElementById('btnSalvar');
        const badge = document.getElementById('badgePreenchidos');
        const progress = document.getElementById('progressPreenchidos');
        const totalEsperado = {{ isset($totalEsperado) ? $totalEsperado : 6 }};

        function atualizarStatus() {
            let preenchidos = 0;

            campos.forEach(campo => {
                const val = campo.value.trim();
                // válido: número inteiro >= 0
                const valido = val !== '' && /^[0-9]+$/.test(val);
                if (valido) preenchidos++;
                campo.classList.toggle('is-valid', valido);
                campo.classList.toggle('is-invalid', !valido && val !== '');
            });

            const pct = Math.round((preenchidos / totalEsperado) * 100);
            badge.textContent = `${preenchidos} / ${totalEsperado}`;
            progress.style.width = pct + '%';

            btnSalvar.disabled = preenchidos !== totalEsperado;
        }

        campos.forEach(c => {
            c.addEventListener('input', atualizarStatus);
            c.addEventListener('blur', atualizarStatus);
        });

        // Inicializa status
        atualizarStatus();

        // Evita submit se não estiver completo
        document.getElementById('formContagem').addEventListener('submit', function(e){
            const bloqueado = btnSalvar.disabled;
            if (bloqueado) {
                e.preventDefault();
                alert('Preencha todos os 6 itens com um valor válido (inteiro ≥ 0) para salvar.');
            }
        });
    });
</script>
@endsection