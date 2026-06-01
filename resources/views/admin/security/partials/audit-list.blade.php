<div class="card border-0 shadow-sm h-100">
    <div class="card-header bg-transparent fw-semibold">{{ $title }}</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Quando</th>
                        <th>Acao</th>
                        <th>IP</th>
                        <th>Status</th>
                        <th>Request</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        <tr>
                            <td>{{ $item->created_at?->format('d/m H:i') }}</td>
                            <td>{{ $item->action }}</td>
                            <td>{{ $item->ip }}</td>
                            <td>{{ $item->response_status }}</td>
                            <td><code>{{ $item->request_id }}</code></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-muted text-center py-3">Sem registros.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
