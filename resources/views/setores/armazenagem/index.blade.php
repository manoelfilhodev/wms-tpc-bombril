@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    @include('partials.breadcrumb-auto')
    
    <!-- Header -->
    <div class="mb-4">
        <div class="d-flex align-items-center mb-2">
            <div class="icon-wrapper me-3">
                <i class="mdi mdi-warehouse display-6 text-primary"></i>
            </div>
            <div>
                <h3 class="mb-1 fw-bold text-dark">Armazenagem de Produto</h3>
                <p class="text-muted mb-0 small">Enderece itens com validação de SKU e posição em tempo real</p>
            </div>
        </div>
        @auth
            @if(Auth::user()->tipo === 'admin')
                <a href="{{ route('relatorios.armazenagem') }}" class="btn btn-outline-primary btn-sm">
                    <i class="mdi mdi-chart-bar"></i> Relatório
                </a>
            @endif
        @endauth
    </div>

    <!-- Alertas -->
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center shadow-sm border-0" role="alert">
            <i class="mdi mdi-alert-circle me-2 fs-5"></i>
            <div>{{ session('error') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @elseif (session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center shadow-sm border-0" role="alert">
            <i class="mdi mdi-check-circle me-2 fs-5"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Card Principal -->
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-semibold">
                        <i class="mdi mdi-package-variant-closed me-2 text-primary"></i>
                        Dados de Armazenagem
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('armazenagem.store') }}" id="form-armazenagem" novalidate>
                        @csrf

                        <div class="row g-4">
                            <div class="col-12">
                                <label for="sku" class="form-label fw-semibold text-dark mb-2">
                                    <i class="mdi mdi-barcode me-1"></i>
                                    Código do Produto (SKU)
                                </label>
                                <div class="input-group has-validation">
                                    <span class="input-group-text"><i class="mdi mdi-barcode"></i></span>
                                    <input
                                        type="text"
                                        inputmode="latin"
                                        autocapitalize="off"
                                        autocomplete="off"
                                        class="form-control text-transform-upper"
                                        name="sku"
                                        id="sku"
                                        placeholder="Ex.: ABC12345"
                                        required
                                        aria-describedby="skuHelp skuFeedback"
                                    >
                                    <div id="skuFeedback" class="invalid-feedback">Produto não encontrado no sistema.</div>
                                </div>
                                <div class="d-flex justify-content-between mt-1">
                                    <small id="skuHelp" class="text-muted">
                                        <i class="mdi mdi-information-outline me-1"></i>
                                        Digite ao menos 2 caracteres para buscar.
                                    </small>
                                    <small id="descricao" class="fw-semibold text-muted text-transform-upper" aria-live="polite"></small>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="quantidade" class="form-label fw-semibold text-dark mb-2">
                                    <i class="mdi mdi-counter me-1"></i>
                                    Quantidade a Armazenar
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="mdi mdi-counter"></i></span>
                                    <input
                                        type="tel"
                                        inputmode="numeric"
                                        pattern="[0-9]*"
                                        name="quantidade"
                                        id="quantidade"
                                        class="form-control"
                                        placeholder="Ex.: 100"
                                        required
                                        aria-describedby="qtdHelp"
                                    >
                                </div>
                                <small id="qtdHelp" class="text-muted d-block mt-1">Somente números.</small>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="endereco" class="form-label fw-semibold text-dark mb-2">
                                    <i class="mdi mdi-map-marker-outline me-1"></i>
                                    Endereço de Destino
                                </label>
                                <div class="input-group has-validation">
                                    <span class="input-group-text"><i class="mdi mdi-map-marker-outline"></i></span>
                                    <input
                                        type="text"
                                        inputmode="latin"
                                        autocapitalize="off"
                                        autocomplete="off"
                                        class="form-control text-transform-upper"
                                        name="endereco"
                                        id="endereco"
                                        placeholder="Ex.: R01-C02-N03-P01"
                                        required
                                        aria-describedby="posicaoInfo posicaoFeedback"
                                    >
                                    <div id="posicaoFeedback" class="invalid-feedback">Posição não encontrada no sistema.</div>
                                </div>
                                <small id="posicaoInfo" class="d-block mt-1" aria-live="polite"></small>
                            </div>

                            <div class="col-12">
                                <label for="observacoes" class="form-label fw-semibold text-dark mb-2">
                                    <i class="mdi mdi-note-text-outline me-1"></i>
                                    Observações
                                </label>
                                <textarea
                                    name="observacoes"
                                    id="observacoes"
                                    class="form-control text-transform-upper"
                                    placeholder="Informações adicionais (opcional)"
                                    rows="3"
                                ></textarea>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-primary px-4" id="btn-submit" disabled>
                                <span class="btn-label"><i class="mdi mdi-tray-arrow-down me-2"></i>Armazenar</span>
                                <span class="spinner-border spinner-border-sm ms-2 d-none" role="status" aria-hidden="true"></span>
                            </button>
                            <button type="button" class="btn btn-outline-secondary" id="btn-limpar">
                                <i class="mdi mdi-close me-1"></i>
                                Limpar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Card de Instruções -->
        {{-- <div class="col-lg-4">
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header bg-primary bg-opacity-10 border-bottom border-primary border-opacity-25 py-3">
                    <h6 class="mb-0 fw-semibold text-primary">
                        <i class="mdi mdi-help-circle-outline me-2"></i>
                        Como usar
                    </h6>
                </div>
                <div class="card-body">
                    <ol class="ps-3 mb-0 small">
                        <li class="mb-2">Informe o <strong>SKU</strong> e aguarde a validação.</li>
                        <li class="mb-2">Digite a <strong>quantidade</strong> a armazenar.</li>
                        <li class="mb-2">Preencha o <strong>endereço</strong> conforme o padrão do CD.</li>
                        <li class="mb-0">Clique em <strong>Armazenar</strong> para confirmar.</li>
                    </ol>
                </div>
            </div>

            <div class="card shadow-sm border-0 border-start border-4 border-warning">
                <div class="card-body">
                    <h6 class="fw-semibold text-warning mb-2">
                        <i class="mdi mdi-alert-outline me-1"></i>
                        Dicas de preenchimento
                    </h6>
                    <div class="bg-light p-3 rounded small text-dark">
                        <div class="mb-2">
                            Endereço no formato:
                            <span class="font-monospace">RXX-CXX-NXX-PXX</span>
                        </div>
                        <div class="text-muted" style="font-size: 0.85rem;">
                            Ex.: <span class="font-monospace">R01-C02-N03-P01</span>
                        </div>
                    </div>
                </div>
            </div>
        </div> --}}
    </div>
</div>

{{-- jQuery (se já não existir no layout) --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
// Utils
const debounce = (fn, wait = 300) => {
  let t;
  return (...args) => {
    clearTimeout(t);
    t = setTimeout(() => fn.apply(this, args), wait);
  };
};

const setValid = ($el) => { $el.removeClass('is-invalid').addClass('is-valid'); };
const setInvalid = ($el) => { $el.removeClass('is-valid').addClass('is-invalid'); };
const clearValidation = ($el) => { $el.removeClass('is-valid is-invalid'); };

// Estado
let skuValido = false;
let posicaoValida = false;

// Habilita o submit quando todos os mínimos estão ok
const toggleSubmit = () => {
  const qtdOk = $('#quantidade').val().trim().length > 0;
  $('#btn-submit').prop('disabled', !(skuValido && posicaoValida && qtdOk));
};

// Busca SKU
const buscarSku = debounce(function() {
  const input = $('#sku').val().trim();
  const $sku = $('#sku');
  const $desc = $('#descricao');

  if (input.length < 2) {
    skuValido = false;
    clearValidation($sku);
    $desc.text('');
    toggleSubmit();
    return;
  }

  $.get("{{ route('armazenagem.buscarSkus') }}", { term: input })
    .done(function(data) {
      if (Array.isArray(data) && data.includes(input)) {
        skuValido = true; setValid($sku);
      } else {
        skuValido = false; setInvalid($sku);
      }
      toggleSubmit();
    })
    .fail(function() {
      skuValido = false; setInvalid($sku); toggleSubmit();
    });

  $.get("{{ route('armazenagem.buscarDescricao') }}", { sku: input })
    .done(function(data) {
      if (data && data.descricao) $desc.text(String(data.descricao).toUpperCase());
      else $desc.text('');
    })
    .fail(function() { $desc.text(''); });
}, 350);

// Busca Posição
const buscarPosicao = debounce(function() {
  const input = $('#endereco').val().trim();
  const $pos = $('#endereco');
  const $info = $('#posicaoInfo');

  if (input.length < 2) {
    posicaoValida = false;
    clearValidation($pos);
    $info.text('').removeClass('text-success text-danger');
    toggleSubmit();
    return;
  }

  $.get("{{ route('armazenagem.buscarPosicoes') }}", { term: input })
    .done(function(data) {
      if (Array.isArray(data) && data.includes(input)) {
        posicaoValida = true; setValid($pos);
        $info.text('Posição válida.').removeClass('text-danger').addClass('text-success');
      } else {
        posicaoValida = false; setInvalid($pos);
        $info.text('Posição não encontrada.').removeClass('text-success').addClass('text-danger');
      }
      toggleSubmit();
    })
    .fail(function() {
      posicaoValida = false; setInvalid($pos);
      $info.text('Erro ao validar posição.').removeClass('text-success').addClass('text-danger');
      toggleSubmit();
    });
}, 350);

// Máscara: somente dígitos
const onlyDigits = (e) => {
  const v = e.target.value.replace(/\D+/g, '');
  if (e.target.value !== v) e.target.value = v;
  toggleSubmit();
};

$(function () {
  $('#sku').on('input', buscarSku);
  $('#endereco').on('input', buscarPosicao);
  $('#quantidade').on('input', onlyDigits);

  // Limpar campos
  $('#btn-limpar').on('click', function() {
    $('#form-armazenagem')[0].reset();
    skuValido = false; posicaoValida = false;
    clearValidation($('#sku')); clearValidation($('#endereco'));
    $('#descricao').text(''); $('#posicaoInfo').text('').removeClass('text-success text-danger');
    toggleSubmit();
    $('#sku').focus();
  });

  // Submissão com loading
  $('#form-armazenagem').on('submit', function() {
    const $btn = $('#btn-submit');
    $btn.prop('disabled', true);
    $btn.find('.btn-label').html('<i class="mdi mdi-loading mdi-spin me-2"></i>Armazenando...');
    $btn.find('.spinner-border').removeClass('d-none');
  });

  // Auto close alert de sucesso
  const $alertSuccess = $('.alert-success');
  if ($alertSuccess.length) {
    setTimeout(() => { $alertSuccess.alert('close'); }, 3000);
  }
});
</script>

<style>
    .icon-wrapper {
        width: 60px; height: 60px;
        display: flex; align-items: center; justify-content: center;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }
    .icon-wrapper i { color: white !important; }

    .card { border-radius: 0.5rem; overflow: hidden; }
    .btn { border-radius: 0.375rem; font-weight: 500; }
    .alert { border-radius: 0.5rem; }

    .text-transform-upper { text-transform: uppercase; }

    /* Feedback visual */
    .is-valid ~ .invalid-feedback { display: none !important; }
</style>
@endsection