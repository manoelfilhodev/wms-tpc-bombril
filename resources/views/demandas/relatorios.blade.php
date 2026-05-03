@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    @include('partials.breadcrumb-auto')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
            <div class="icon-wrapper me-3">
                <i class="mdi mdi-file-chart-outline display-6"></i>
            </div>
            <div>
                <h3 class="mb-1 fw-bold text-dark">Relatórios Operacionais</h3>
                <p class="text-muted mb-0 small">Resumo executivo da operação de picking</p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('demandas.reportTurno') }}" class="btn btn-primary btn-sm">
                <i class="mdi mdi-whatsapp me-1"></i> Report de turno
            </a>
            <a href="{{ route('demandas.identificacaoA4') }}" class="btn btn-outline-secondary btn-sm">
                <i class="mdi mdi-printer-outline me-1"></i> Identificação A4
            </a>
            <a href="{{ route('demandas.dashboardOperacional') }}" class="btn btn-outline-secondary btn-sm">Voltar dashboard</a>
            <a href="{{ route('demandas.relatorios') }}" class="btn btn-outline-secondary btn-sm">Atualizar</a>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body"><small class="text-muted">DTs Picking</small><h4 class="mb-0">{{ $total }}</h4></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body"><small class="text-muted">Separação parcial</small><h4 class="mb-0">{{ $parcial }}</h4></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body"><small class="text-muted">Separação completa</small><h4 class="mb-0">{{ $completa }}</h4></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body"><small class="text-muted">Em aberto</small><h4 class="mb-0">{{ $abertas }}</h4></div></div></div>
    </div>

    <div class="card border-0 shadow-sm mt-4">
        <div class="card-body">
            <small class="text-muted d-block">Tempo médio geral da separação</small>
            <h4 class="mb-0">{{ $tempoMedioMin !== null ? $tempoMedioMin.' min' : '-' }}</h4>
        </div>
    </div>

    <div class="card border-0 shadow-sm mt-4">
        <div class="card-body">
            <h5 class="mb-3">Modelos de Relatórios Disponíveis (Roadmap)</h5>
            <p class="text-muted small mb-3">Base principal: DT, SKU e Quantidade (incluindo itens com sobra/picking).</p>

            <div class="row g-3">
                <div class="col-md-6">
                    <a href="{{ route('demandas.reportTurno') }}" class="text-decoration-none text-reset">
                        <div class="border rounded p-3 h-100">
                            <h6 class="mb-2">Report de Turno para WhatsApp</h6>
                            <p class="small text-muted mb-2">Resumo por separador com peças, SKUs, BOX e DT para print ao fim do turno.</p>
                            <small class="text-primary">Abrir report printável</small>
                        </div>
                    </a>
                </div>
                <div class="col-md-6">
                    <a href="{{ route('demandas.identificacaoA4') }}" class="text-decoration-none text-reset">
                        <div class="border rounded p-3 h-100">
                            <h6 class="mb-2">Identificação A4 para DT</h6>
                            <p class="small text-muted mb-2">Folha A4 com duas vias iguais para cortar no meio: DT, pallets, data e conferente.</p>
                            <small class="text-primary">Abrir impressão</small>
                        </div>
                    </a>
                </div>
                <div class="col-md-6">
                    <div class="border rounded p-3 h-100">
                        <h6 class="mb-2">1) Curva ABC de SKUs</h6>
                        <p class="small text-muted mb-2">Classificação A/B/C por volume separado no período.</p>
                        <small class="text-muted">Saída esperada: SKU, Qtd total, % participação, % acumulado, Classe.</small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="border rounded p-3 h-100">
                        <h6 class="mb-2">2) Produtividade por Separador</h6>
                        <p class="small text-muted mb-2">Peças, DTs e tempo médio por operador.</p>
                        <small class="text-muted">Saída esperada: Separador, Peças separadas, DTs, Tempo médio, % parcial.</small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="border rounded p-3 h-100">
                        <h6 class="mb-2">3) Relatório de DTs Picking</h6>
                        <p class="small text-muted mb-2">Rastreabilidade completa da execução por DT.</p>
                        <small class="text-muted">Saída esperada: DT, início/fim, duração, status final, saldo e distribuído.</small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="border rounded p-3 h-100">
                        <h6 class="mb-2">4) Gargalos Operacionais</h6>
                        <p class="small text-muted mb-2">Identificação das maiores perdas de tempo.</p>
                        <small class="text-muted">Saída esperada: Top DTs demoradas, pendências por turno, fila de “a separar”.</small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="border rounded p-3 h-100">
                        <h6 class="mb-2">5) SLA de Separação</h6>
                        <p class="small text-muted mb-2">Nível de serviço operacional por período.</p>
                        <small class="text-muted">Saída esperada: % finalizada no dia, tempo médio por turno/faixa horária.</small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="border rounded p-3 h-100">
                        <h6 class="mb-2">6) Cobertura por SKU</h6>
                        <p class="small text-muted mb-2">Frequência e volume por item no picking.</p>
                        <small class="text-muted">Saída esperada: SKU, Nº de DTs atendidas, Qtd total separada, média por DT.</small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="border rounded p-3 h-100">
                        <h6 class="mb-2">7) DTs terminadas após a data de criação</h6>
                        <p class="small text-muted mb-2">Acompanhamento das DTs concluídas em data posterior à data de criação.</p>
                        <small class="text-muted">Saída esperada: DT, data de criação, data de conclusão, duração, status final e responsável.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="alert alert-info mt-4 mb-0">
        Próximo passo sugerido: implementar primeiro o relatório de Curva ABC com filtro por período e exportação CSV.
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
</style>
@endsection
