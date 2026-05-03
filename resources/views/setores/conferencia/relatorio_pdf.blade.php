<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Relatório de Conferência</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        h1, h2, h3, h4 { margin-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 5px; text-align: left; }
        .section { margin-top: 20px; }
        .status-ok { color: green; font-weight: bold; }
        .status-divergente { color: red; font-weight: bold; }
        .status-avariado { color: orange; font-weight: bold; }
        img { margin-top: 10px; max-width: 100%; height: auto; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .footer { position: fixed; bottom: 10px; left: 0; right: 0; text-align: center; font-size: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ public_path('logo.png') }}" height="50" alt="Logo">
        <h2>Relatório de Conferência Cega</h2>
        <small>Gerado em: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</small>
    </div>

    <div class="section">
        <h4>Dados do Recebimento</h4>
        <p><strong>Nota Fiscal:</strong> {{ $recebimento->nota_fiscal }}</p>
        <p><strong>Fornecedor:</strong> {{ $recebimento->fornecedor }}</p>
        <p><strong>Data Recebimento:</strong> {{ \Carbon\Carbon::parse($recebimento->data_recebimento)->format('d/m/Y') }}</p>
        <p><strong>Transportadora:</strong> {{ $recebimento->transportadora }}</p>
        <p><strong>Motorista:</strong> {{ $recebimento->motorista }}</p>
        <p><strong>Placa:</strong> {{ $recebimento->placa }}</p>
        <p><strong>Doca:</strong> {{ $recebimento->doca }}</p>
        <p><strong>Tipo de Carga:</strong> {{ $recebimento->tipo }}</p>
    </div>

    <div class="section">
        <h4>Itens Conferidos</h4>
        <table>
            <thead>
                <tr>
                    <th>Data Conf</th>
                    <th>SKU</th>
                    <th>Qtd Esperada</th>
                    <th>Qtd Conf</th>
                    <th>Status</th>
                    <th>Observação</th>
                </tr>
            </thead>
            <tbody>
                @foreach($itens as $item)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y') }}</td>
                        <td>{{ $item->sku }}</td>
                        <td>{{ $item->quantidade }}</td>
                        <td>{{ $item->qtd_conferida }}</td>
                        <td>
                            @if($item->divergente)
                                <span class="status-divergente">Divergente</span>
                            @elseif($item->avariado)
                                <span class="status-avariado">Avariado</span>
                            @else
                                <span class="status-ok">OK</span>
                            @endif
                        </td>
                        <td>{{ $item->observacao }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($recebimento->foto_inicio_veiculo)
        <div class="section">
            <h4>Foto Inicial do Veículo</h4>
            <img src="{{ storage_path('app/public/' . $recebimento->foto_inicio_veiculo) }}">
        </div>
    @endif

    @if($recebimento->foto_fim_veiculo)
        <div class="section">
            <h4>Foto Final do Veículo</h4>
            <img src="{{ storage_path('app/public/' . $recebimento->foto_fim_veiculo) }}">
        </div>
    @endif

    @if($recebimento->ressalva_assistente)
        <div class="section">
            <h4>Ressalva do Assistente</h4>
            <p>{{ $recebimento->ressalva_assistente }}</p>
        </div>
    @endif

    @if($recebimento->assinatura_conferente)
        <div class="section">
            <h4>Assinatura do Conferente</h4>
            <p>{{ $recebimento->assinatura_conferente }}</p>
        </div>
    @endif

    <div class="footer">
        SYSTEX Sistemas Inteligentes &copy; {{ date('Y') }} - www.systex.com.br
    </div>
</body>
</html>
