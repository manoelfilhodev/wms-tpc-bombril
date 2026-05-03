@extends($layout)

@section('content')
<div class="container">
    <h4>Painel de Kits Programados</h4>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Ações</th>
                <th>ID</th>
                <th>Código Material</th>
                <th>Quantidade Programada</th>
                <th>Data Montagem</th>
                <th>Status</th>
                <th>Etiquetas</th>
            </tr>
        </thead>
        <tbody>
        @foreach($kits as $kit)
            @php
                $apontamentos = DB::table('_tb_apontamentos_kits')
                    ->where('codigo_material', $kit->codigo_material)
                    ->count();
            @endphp
            <tr>
                <td>
                    <a href="{{ route('kit.editar', $kit->id) }}" class="btn btn-primary btn-sm">
                        Editar
                    </a>
                </td>
                <td>{{ $kit->id }}</td>
                <td>{{ $kit->codigo_material }}</td>
                <td>{{ $kit->quantidade_programada }}</td>
                <td>{{ \Carbon\Carbon::parse($kit->data_montagem)->format('d/m/Y') }}</td>
                <td>
                    <span class="badge bg-info">Programado</span>
                </td>
                <td>
                    <button class="btn btn-success btn-sm" onclick="abrirModalEtiquetas({{ $kit->id }})">
                        <i class="fa fa-tags"></i> Etiquetas ({{ $apontamentos }})
                    </button>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    {{-- Modal Etiquetas --}}
    <div class="modal fade" id="modalEtiquetas" tabindex="-1" role="dialog" aria-labelledby="modalEtiquetasLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEtiquetasLabel">Impressão de Etiquetas</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Selecione uma das opções abaixo para imprimir as etiquetas do kit:
                </div>
                <div class="modal-footer">
                    <a href="#" id="btnImprimirTudo" class="btn btn-primary" target="_blank">
                        <i class="fa fa-print"></i> Imprimir Tudo
                    </a>
                    <a href="#" id="btnReimprimir" class="btn btn-warning" target="_blank" style="display:none;">
                        <i class="fa fa-refresh"></i> Reimprimir
                    </a>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
function abrirModalEtiquetas(kitId, apontamentoId = null) {
    let urlImprimirTudo = "{{ route('kit.imprimirTudo', ':id') }}".replace(':id', kitId);
    document.getElementById('btnImprimirTudo').href = urlImprimirTudo;

    if (apontamentoId) {
        let urlReimprimir = "{{ route('kit.reimprimir', ':apontamentoId') }}".replace(':apontamentoId', apontamentoId);
        document.getElementById('btnReimprimir').href = urlReimprimir;
        document.getElementById('btnReimprimir').style.display = 'inline-block';
    } else {
        document.getElementById('btnReimprimir').style.display = 'none';
    }

    $('#modalEtiquetas').modal('show');
}
</script>
@endsection
