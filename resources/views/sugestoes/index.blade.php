@extends('layouts.app')

@section('content')

<div class="container-fluid px-4 py-3">
    @include('partials.breadcrumb-auto')
    
    <!-- Header com ícone roxo -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
            <div class="icon-wrapper me-3">
                <i class="mdi mdi-lightbulb-on-outline display-6 text-primary"></i>
            </div>
            <div>
                <h3 class="mb-1 fw-bold text-dark">Sugestões e Melhorias</h3>
                <p class="text-muted mb-0 small">Envie suas ideias e acompanhe o status das solicitações</p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="tooltip" title="Atualizar">
                <i class="mdi mdi-refresh"></i>
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert">
            <i class="mdi mdi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Card de Nova Sugestão -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="mb-0 fw-semibold text-dark">
                <i class="mdi mdi-plus-circle-outline me-2 text-primary"></i>Nova Sugestão
            </h5>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('sugestoes.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label small text-muted mb-1">Título da Sugestão</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="mdi mdi-format-title text-muted"></i>
                        </span>
                        <input type="text" name="titulo" class="form-control border-start-0" 
                               placeholder="Ex: Melhorar filtro de relatórios" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label small text-muted mb-1">Descrição Detalhada</label>
                    <textarea name="descricao" rows="4" class="form-control" 
                              placeholder="Descreva sua sugestão de forma clara e objetiva..." required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="mdi mdi-send me-1"></i>Enviar Sugestão
                </button>
            </form>
        </div>
    </div>

    <!-- Card da Tabela -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="mb-0 fw-semibold text-dark">
                <i class="mdi mdi-history me-2 text-primary"></i>Histórico de Sugestões
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="px-4 py-3 text-muted small fw-semibold" style="width: 25%;">
                                <i class="mdi mdi-format-title me-1"></i>Título
                            </th>
                            <th class="px-4 py-3 text-muted small fw-semibold" style="width: 12%;">
                                <i class="mdi mdi-flag me-1"></i>Status
                            </th>
                            <th class="px-4 py-3 text-muted small fw-semibold" style="width: 15%;">
                                <i class="mdi mdi-account me-1"></i>Usuário
                            </th>
                            <th class="px-4 py-3 text-muted small fw-semibold" style="width: 13%;">
                                <i class="mdi mdi-calendar me-1"></i>Data
                            </th>
                            <th class="px-4 py-3 text-muted small fw-semibold" style="width: 35%;">
                                <i class="mdi mdi-comment-text me-1"></i>Resposta
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sugestoes as $s)
                            <tr class="border-bottom">
                                <td class="px-4 py-3">
                                    <div class="fw-semibold text-dark mb-1">{{ $s->titulo }}</div>
                                    <small class="text-muted">{{ Str::limit($s->descricao, 60) }}</small>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="badge status-badge status-{{ $s->status }}">
                                        @if($s->status === 'concluida')
                                            <i class="mdi mdi-check-circle me-1"></i>Concluída
                                        @elseif($s->status === 'recusada')
                                            <i class="mdi mdi-close-circle me-1"></i>Recusada
                                        @elseif($s->status === 'em_andamento')
                                            <i class="mdi mdi-progress-clock me-1"></i>Em andamento
                                        @else
                                            <i class="mdi mdi-clock-outline me-1"></i>Pendente
                                        @endif
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle me-2">
                                            {{ strtoupper(substr($s->usuario->nome ?? 'U', 0, 1)) }}
                                        </div>
                                        <span class="text-dark">{{ $s->usuario->nome ?? '-' }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <small class="text-dark">{{ \Carbon\Carbon::parse($s->created_at)->format('d/m/Y') }}</small><br>
                                    <small class="text-muted">{{ \Carbon\Carbon::parse($s->created_at)->format('H:i') }}</small>
                                </td>
                                <td class="px-4 py-3">
                                    @if(Auth::user()->tipo === 'admin' || Auth::user()->tipo === 'gestor')
                                        <form action="{{ route('sugestoes.update', $s->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <select name="status" class="form-select form-select-sm mb-2" required>
                                                <option value="pendente" {{ $s->status === 'pendente' ? 'selected' : '' }}>Pendente</option>
                                                <option value="em_andamento" {{ $s->status === 'em_andamento' ? 'selected' : '' }}>Em andamento</option>
                                                <option value="concluida" {{ $s->status === 'concluida' ? 'selected' : '' }}>Concluída</option>
                                                <option value="recusada" {{ $s->status === 'recusada' ? 'selected' : '' }}>Recusada</option>
                                            </select>
                                            <textarea name="resposta" class="form-control form-control-sm mb-2" rows="2" 
                                                      placeholder="Retorno ao usuário...">{{ $s->resposta }}</textarea>
                                            <button type="submit" class="btn btn-sm btn-success">
                                                <i class="mdi mdi-check me-1"></i>Atualizar
                                            </button>
                                        </form>
                                    @else
                                        @if($s->resposta)
                                            <div class="response-box">
                                                <i class="mdi mdi-message-reply-text text-primary me-1"></i>
                                                {{ $s->resposta }}
                                            </div>
                                        @else
                                            <span class="text-muted small">Aguardando resposta...</span>
                                        @endif
                                    @endif
                                    
                                    @if($s->respostas->count())
                                        <div class="mt-3 pt-3 border-top">
                                            <small class="text-muted fw-semibold d-block mb-2">
                                                <i class="mdi mdi-history me-1"></i>Histórico de Atualizações
                                            </small>
                                            @foreach($s->respostas as $r)
                                                <div class="history-item mb-2">
                                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                                        <span class="badge status-badge status-{{ $r->status }} badge-sm">
                                                            {{ ucfirst(str_replace('_', ' ', $r->status)) }}
                                                        </span>
                                                        <small class="text-muted">{{ \Carbon\Carbon::parse($r->created_at)->format('d/m/Y H:i') }}</small>
                                                    </div>
                                                    <p class="mb-1 small">{{ $r->resposta }}</p>
                                                    <small class="text-muted">
                                                        <i class="mdi mdi-account-circle me-1"></i>{{ $r->autor->nome ?? 'Sistema' }}
                                                    </small>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="mdi mdi-lightbulb-outline display-4 d-block mb-3 opacity-25"></i>
                                        <p class="mb-0">Nenhuma sugestão enviada ainda</p>
                                        <small>Seja o primeiro a contribuir com melhorias!</small>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        @if(method_exists($sugestoes, 'links'))
            <div class="card-footer bg-white border-top">
                {{ $sugestoes->links() }}
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
    .form-control:focus { border-color: #0d6efd; box-shadow: 0 0 0 0.2rem rgba(13,110,253,0.1); }
    
    .table tbody tr:hover { background-color: #f8f9fa; transition: background-color 0.2s ease; }
    .card { border-radius: 0.5rem; }
    
    .status-badge {
        font-weight: 500;
        padding: 0.4em 0.75em;
        font-size: 0.75rem;
        border-radius: 0.375rem;
    }
    
    .status-concluida {
        background-color: #d1f4e0;
        color: #0f5132;
    }
    
    .status-recusada {
        background-color: #f8d7da;
        color: #842029;
    }
    
    .status-em_andamento {
        background-color: #cfe2ff;
        color: #084298;
    }
    
    .status-pendente {
        background-color: #fff3cd;
        color: #997404;
    }
    
    .avatar-circle {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.75rem;
    }
    
    .response-box {
        background-color: #f8f9fa;
        border-left: 3px solid #0d6efd;
        padding: 0.75rem;
        border-radius: 0.375rem;
        font-size: 0.875rem;
    }
    
    .history-item {
        background-color: #f8f9fa;
        padding: 0.75rem;
        border-radius: 0.375rem;
        border-left: 2px solid #dee2e6;
    }
    
    .badge-sm {
        font-size: 0.7rem;
        padding: 0.25em 0.5em;
    }
    
    .alert {
        border-radius: 0.5rem;
    }
</style>

@endsection