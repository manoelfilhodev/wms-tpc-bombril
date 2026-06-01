@extends('layouts.app')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h4 class="mb-0">Security Operations</h4>
            <small class="text-muted">Monitoramento de eventos, alertas e acessos sensiveis.</small>
        </div>
    </div>

    <div class="row g-3 mb-3">
        @foreach([
            'Logins 24h' => $summary['login_success_24h'],
            'Falhas login 24h' => $summary['login_failure_24h'],
            'Acessos negados 24h' => $summary['denied_24h'],
            'Uploads bloqueados 24h' => $summary['blocked_uploads_24h'],
            'Alertas 24h' => $summary['alerts_24h'],
        ] as $label => $value)
            <div class="col-6 col-md">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <small class="text-muted">{{ $label }}</small>
                        <div class="fs-4 fw-bold">{{ $value }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-3">
        <div class="col-lg-6">
            @include('admin.security.partials.audit-list', ['title' => 'Ultimos logins', 'items' => $recentLogins])
        </div>
        <div class="col-lg-6">
            @include('admin.security.partials.audit-list', ['title' => 'Falhas de login', 'items' => $loginFailures])
        </div>
        <div class="col-lg-6">
            @include('admin.security.partials.audit-list', ['title' => 'Acessos negados', 'items' => $deniedAccess])
        </div>
        <div class="col-lg-6">
            @include('admin.security.partials.audit-list', ['title' => 'Uploads bloqueados', 'items' => $blockedUploads])
        </div>
        <div class="col-lg-6">
            @include('admin.security.partials.audit-list', ['title' => 'Eventos criticos', 'items' => $criticalEvents])
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent fw-semibold">Alertas</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th>Quando</th>
                                    <th>Severidade</th>
                                    <th>Tipo</th>
                                    <th>Correlacao</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($alerts as $alert)
                                    <tr>
                                        <td>{{ $alert->created_at?->format('d/m H:i') }}</td>
                                        <td>{{ $alert->severity }}</td>
                                        <td>{{ $alert->type }}</td>
                                        <td><code>{{ $alert->correlation_id }}</code></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-muted text-center py-3">Sem alertas.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
