@extends('layouts.app')

@php
    $badgeClass = function (?string $module): string {
        return match ($module) {
            'login', 'autenticacao' => 'audit-badge audit-badge-login',
            'separacao' => 'audit-badge audit-badge-separacao',
            'expedicao' => 'audit-badge audit-badge-expedicao',
            'importacao' => 'audit-badge audit-badge-importacao',
            'relatorios' => 'audit-badge audit-badge-relatorios',
            'administracao' => 'audit-badge audit-badge-administracao',
            default => 'audit-badge audit-badge-sistema',
        };
    };
@endphp

@section('content')
<div class="container-fluid px-4 py-3 audit-page">
    @include('partials.breadcrumb-auto')

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div class="d-flex align-items-center">
            <div class="audit-icon me-3">
                <i class="mdi mdi-shield-search"></i>
            </div>
            <div>
                <h3 class="mb-1 fw-bold text-dark">Logs do Sistema</h3>
                <p class="text-muted mb-0 small">Auditoria operacional e administrativa das ações críticas</p>
            </div>
        </div>
        <a href="{{ route('admin.logs.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="mdi mdi-refresh me-1"></i> Atualizar
        </a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-md-6 col-xl-3">
            <div class="audit-summary">
                <span>Total de logs</span>
                <strong>{{ number_format($resumo['total'], 0, ',', '.') }}</strong>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="audit-summary">
                <span>Logs de hoje</span>
                <strong>{{ number_format($resumo['hoje'], 0, ',', '.') }}</strong>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="audit-summary">
                <span>Usuários ativos hoje</span>
                <strong>{{ number_format($resumo['usuarios_ativos_hoje'], 0, ',', '.') }}</strong>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="audit-summary">
                <span>Ações críticas</span>
                <strong>{{ number_format($resumo['criticas'], 0, ',', '.') }}</strong>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4 audit-card">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.logs.index') }}" class="row g-3 align-items-end">
                <div class="col-12 col-md-6 col-xl-2">
                    <label class="form-label small text-muted mb-1">Data início</label>
                    <input type="date" name="data_inicio" class="form-control" value="{{ request('data_inicio') }}">
                </div>
                <div class="col-12 col-md-6 col-xl-2">
                    <label class="form-label small text-muted mb-1">Data fim</label>
                    <input type="date" name="data_fim" class="form-control" value="{{ request('data_fim') }}">
                </div>
                <div class="col-12 col-md-6 col-xl-2">
                    <label class="form-label small text-muted mb-1">Usuário</label>
                    <select name="usuario" class="form-select">
                        <option value="">Todos</option>
                        @foreach ($usuarios as $usuario)
                            <option value="{{ $usuario->id_user }}" @selected((string) request('usuario') === (string) $usuario->id_user)>
                                {{ $usuario->nome }} - {{ $usuario->email }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-6 col-xl-2">
                    <label class="form-label small text-muted mb-1">Módulo</label>
                    <select name="module" class="form-select">
                        <option value="">Todos</option>
                        @foreach ($modulos as $modulo)
                            <option value="{{ $modulo }}" @selected(request('module') === $modulo)>{{ ucfirst($modulo) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-6 col-xl-2">
                    <label class="form-label small text-muted mb-1">Ação</label>
                    <select name="action" class="form-select">
                        <option value="">Todas</option>
                        @foreach ($acoes as $acao)
                            <option value="{{ $acao }}" @selected(request('action') === $acao)>{{ str_replace('_', ' ', $acao) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-6 col-xl-2">
                    <label class="form-label small text-muted mb-1">Pesquisa</label>
                    <input type="search" name="q" class="form-control" placeholder="Texto livre" value="{{ request('q') }}">
                </div>
                <div class="col-12 col-md-6 col-xl-2">
                    <label class="form-label small text-muted mb-1">Ordenação</label>
                    <select name="ordem" class="form-select">
                        <option value="desc" @selected(request('ordem', 'desc') === 'desc')>Mais recentes</option>
                        <option value="asc" @selected(request('ordem') === 'asc')>Mais antigos</option>
                    </select>
                </div>
                <div class="col-12 col-xl-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="mdi mdi-magnify me-1"></i> Filtrar
                    </button>
                    <a href="{{ route('admin.logs.index') }}" class="btn btn-outline-secondary">
                        <i class="mdi mdi-filter-remove-outline me-1"></i> Limpar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0 audit-card">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-semibold text-dark">
                <i class="mdi mdi-table-search me-2 text-danger"></i>Registros de auditoria
            </h6>
            <span class="badge bg-dark">{{ $logs->total() }}</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Data/Hora</th>
                            <th>Usuário</th>
                            <th>Módulo</th>
                            <th>Ação</th>
                            <th>Descrição</th>
                            <th>IP</th>
                            <th class="text-end">Detalhes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $log)
                            <tr>
                                <td class="text-nowrap">
                                    <small>{{ $log->created_at?->format('d/m/Y H:i:s') }}</small>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $log->user_name ?? 'Sistema' }}</div>
                                    <small class="text-muted">{{ $log->user_email ?? '---' }}</small>
                                </td>
                                <td>
                                    <span class="{{ $badgeClass($log->module) }}">{{ ucfirst($log->module) }}</span>
                                </td>
                                <td>
                                    <code>{{ $log->action }}</code>
                                </td>
                                <td style="min-width: 280px;">
                                    <span>{{ $log->description }}</span>
                                    @if ($log->entity_type || $log->entity_id)
                                        <small class="text-muted d-block">{{ $log->entity_type }} #{{ $log->entity_id }}</small>
                                    @endif
                                </td>
                                <td><code>{{ $log->ip_address ?? '---' }}</code></td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-outline-dark" data-bs-toggle="modal" data-bs-target="#logModal{{ $log->id }}">
                                        <i class="mdi mdi-eye-outline me-1"></i> Detalhes
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="mdi mdi-shield-alert-outline display-5 d-block mb-2"></i>
                                    Nenhum log encontrado.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($logs->hasPages())
            <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    Mostrando {{ $logs->firstItem() }} a {{ $logs->lastItem() }} de {{ $logs->total() }}
                </small>
                {{ $logs->links() }}
            </div>
        @endif
    </div>

    @foreach ($logs as $log)
        <div class="modal fade" id="logModal{{ $log->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content audit-modal">
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title">Log #{{ $log->id }}</h5>
                            <small class="text-muted">{{ $log->created_at?->format('d/m/Y H:i:s') }}</small>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3 mb-3">
                            <div class="col-md-4"><strong>Usuário:</strong><br>{{ $log->user_name ?? 'Sistema' }}</div>
                            <div class="col-md-4"><strong>E-mail:</strong><br>{{ $log->user_email ?? '---' }}</div>
                            <div class="col-md-4"><strong>Perfil:</strong><br>{{ $log->user_role ?? '---' }}</div>
                            <div class="col-md-4"><strong>Módulo:</strong><br>{{ $log->module }}</div>
                            <div class="col-md-4"><strong>Ação:</strong><br>{{ $log->action }}</div>
                            <div class="col-md-4"><strong>Registro:</strong><br>{{ $log->entity_type ?? '---' }} #{{ $log->entity_id ?? '---' }}</div>
                            <div class="col-md-4"><strong>IP:</strong><br>{{ $log->ip_address ?? '---' }}</div>
                            <div class="col-md-4"><strong>Rota:</strong><br>{{ $log->route ?? '---' }}</div>
                            <div class="col-md-4"><strong>Método:</strong><br>{{ $log->method ?? '---' }}</div>
                            <div class="col-md-12"><strong>Device ID:</strong><br>{{ $log->device_id ?? '---' }}</div>
                            <div class="col-md-12"><strong>Navegador:</strong><br><small>{{ $log->user_agent ?? '---' }}</small></div>
                            <div class="col-md-12"><strong>Descrição:</strong><br>{{ $log->description }}</div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <h6>Dados antigos</h6>
                                <pre class="audit-json">{{ json_encode($log->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: 'null' }}</pre>
                            </div>
                            <div class="col-md-6">
                                <h6>Dados novos</h6>
                                <pre class="audit-json">{{ json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: 'null' }}</pre>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<style>
    .audit-icon {
        width: 58px;
        height: 58px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #161b22;
        border: 1px solid rgba(220, 53, 69, 0.35);
        color: #dc3545;
        font-size: 30px;
    }

    .audit-summary {
        min-height: 96px;
        border-radius: 8px;
        border: 1px solid #252b33;
        background: #121820;
        color: #fff;
        padding: 18px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .audit-summary span {
        color: #aab4c0;
        font-size: 13px;
    }

    .audit-summary strong {
        font-size: 28px;
        line-height: 1;
    }

    .audit-card {
        border-radius: 8px;
    }

    .audit-badge {
        display: inline-flex;
        align-items: center;
        border-radius: 6px;
        padding: 5px 8px;
        font-size: 12px;
        font-weight: 700;
        border: 1px solid transparent;
    }

    .audit-badge-login { background: #e8f1ff; color: #0d4f9e; border-color: #b9d5ff; }
    .audit-badge-separacao { background: #edf7ed; color: #1f6b2a; border-color: #b9dfbd; }
    .audit-badge-expedicao { background: #fff4e5; color: #8a5200; border-color: #ffd79a; }
    .audit-badge-importacao { background: #eef2ff; color: #3730a3; border-color: #c7d2fe; }
    .audit-badge-relatorios { background: #f4ecff; color: #6f42c1; border-color: #d8bfff; }
    .audit-badge-administracao { background: #ffe8ec; color: #a31428; border-color: #ffb7c1; }
    .audit-badge-sistema { background: #f1f3f5; color: #495057; border-color: #d8dee4; }

    .audit-json {
        min-height: 220px;
        max-height: 420px;
        overflow: auto;
        padding: 12px;
        border-radius: 8px;
        background: #0d1117;
        color: #d6e2ef;
        font-size: 12px;
    }

    code {
        color: #b42335;
        background: #f8f9fa;
        border-radius: 4px;
        padding: 2px 5px;
    }
</style>
@endsection
