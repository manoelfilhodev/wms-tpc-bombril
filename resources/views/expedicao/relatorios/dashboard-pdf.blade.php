<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Relatório - Dashboard Expedição</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h2, h3 { margin: 0 0 10px; }
        .kpis { display: flex; flex-wrap: wrap; margin-bottom: 20px; }
        .card { flex: 1; border: 1px solid #ccc; padding: 10px; margin: 5px; text-align: center; }
        .card h4 { margin: 5px 0; font-size: 14px; }
        .card p { margin: 0; font-size: 13px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table, th, td { border: 1px solid #000; }
        th, td { padding: 5px; text-align: center; }
        th { background: #f0f0f0; }
    </style>
</head>
<body>
    <h2>Relatório - Dashboard Expedição</h2>
    <p>Mês: {{ $mesAtual }}</p>

    <!-- KPIs -->
    <div class="kpis">
        <div class="card">
            <h4>TMSep</h4>
            <p>{{ $temposMensal ? round($temposMensal->tmsep/60,1).' h' : '-' }}</p>
        </div>
        <div class="card">
            <h4>TMConf</h4>
            <p>{{ $temposMensal ? round($temposMensal->tmconf/60,1).' h' : '-' }}</p>
        </div>
        <div class="card">
            <h4>TMCarr</h4>
            <p>{{ $temposMensal ? round($temposMensal->tmcarr/60,1).' h' : '-' }}</p>
        </div>
        <div class="card">
            <h4>TMGP</h4>
            <p>{{ $temposMensal ? round($temposMensal->tmgp/60,1).' h' : '-' }}</p>
        </div>
    </div>

    <div class="kpis">
        <div class="card">
            <h4>Volume Total</h4>
            <p>{{ $volumeMensal ? number_format($volumeMensal->qtd_total) : 0 }}</p>
        </div>
        <div class="card">
            <h4>Peso Total</h4>
            <p>{{ $volumeMensal ? number_format($volumeMensal->peso_total, 0, ',', '.') : 0 }} kg</p>
        </div>
        <div class="card">
            <h4>Demandas</h4>
            <p>{{ $volumeMensal ? $volumeMensal->qtd_demandas : 0 }}</p>
        </div>
    </div>

    <!-- Top 5 Transportadoras -->
    <h3>Top 5 Transportadoras (Peso)</h3>
    <div class="kpis">
        @foreach($volumeTransportadora->sortByDesc('peso_total')->take(5) as $t)
            <div class="card">
                <h4>{{ $t->transportadora }}</h4>
                <p>{{ number_format($t->peso_total, 0, ',', '.') }} kg</p>
            </div>
        @endforeach
    </div>

    <!-- Top 5 Motoristas -->
    <h3>Top 5 Motoristas (Peso)</h3>
    <div class="kpis">
        @foreach($volumeMotorista->sortByDesc('peso_total')->take(5) as $m)
            <div class="card">
                <h4>{{ $m->motorista }}</h4>
                <p>{{ number_format($m->peso_total, 0, ',', '.') }} kg</p>
            </div>
        @endforeach
    </div>

    <!-- Transportadoras (detalhe) -->
    <h3>Volume por Transportadora</h3>
    <table>
        <thead>
            <tr>
                <th>Transportadora</th>
                <th>Quantidade</th>
                <th>Peso (kg)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($volumeTransportadora as $t)
                <tr>
                    <td>{{ $t->transportadora }}</td>
                    <td>{{ number_format($t->qtd_total) }}</td>
                    <td>{{ number_format($t->peso_total, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Motoristas (detalhe) -->
    <h3>Volume por Motorista</h3>
    <table>
        <thead>
            <tr>
                <th>Motorista</th>
                <th>Quantidade</th>
                <th>Peso (kg)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($volumeMotorista as $m)
                <tr>
                    <td>{{ $m->motorista }}</td>
                    <td>{{ number_format($m->qtd_total) }}</td>
                    <td>{{ number_format($m->peso_total, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p style="margin-top:30px; font-size: 11px; text-align: center;">
        Relatório gerado automaticamente pelo sistema WMS - Expedição
    </p>
</body>
</html>
