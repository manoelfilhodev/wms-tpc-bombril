<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>{{ $titulo_principal }}</title>
    <style>
        @page { margin: 0cm 0cm; }

        body {
            margin: 5cm 2cm 2cm 2cm; /* espaço para cabeçalho e rodapé */
            font-family: Arial, Helvetica, sans-serif;
        }

        header {
            position: fixed;
            top: 0cm; left: 0cm; right: 0cm;
            height: 4cm;
            text-align: center;
            color: rgb(0, 71, 186);
        }

        header img {
            width: 100%; height: 100%; object-fit: cover;
        }

        footer {
            position: fixed;
            bottom: 0cm; left: 0cm; right: 0cm;
            height: 1.5cm;
            font-size: 10px;
            color: #555;
            text-align: center;
            line-height: 1.5cm;
        }

        .grafico-container { text-align: center; margin-top: 20px; }
        .grafico-container h2 {
            margin-bottom: 15px;
            font-size: 16px;
            color: #333;
        }

        .grafico-container img {
            max-width: 100%; height: auto;
            border: 1px solid #ddd;
            padding: 5px; background: #fff;
        }

        .media-mensal {
            margin-top: 15px;
            padding: 10px;
            border: 1px solid #ccc;
            background: #f8f8f8;
            font-size: 14px;
            color: #333;
            display: inline-block;
        }

        .descricao {
            margin-top: 12px;
            font-size: 12px;
            color: #555;
            text-align: center;
            line-height: 1.4;
        }

        .page-break { page-break-after: always; }

        table {
            margin:20px auto; width:80%;
            border-collapse: collapse;
            font-size:12px;
        }
        th, td {
            border:1px solid #ccc; padding:6px;
        }
        thead tr { background:#f0f0f0; }
        td { text-align:right; }
        td:first-child { text-align:left; }
    </style>
</head>
<body>
    <!-- TOPO -->
    <header>
        <img src="{{ $topo }}" alt="Topo da Empresa">
        <h3>OPERAÇÃO TPC - EXPEDIÇÃO - DEXCO CAJAMAR</h3>
    </header>

    <!-- RODAPÉ -->
    <footer>
        {{ $titulo_principal }} — Referente a {{ $data_referencia }} — Gerado em {{ $data_hoje }}
    </footer>

    <!-- CONTEÚDO -->
    @foreach($graficos as $index => $grafico)
        <div class="grafico-container">
            <h2>{{ $grafico['titulo'] }}</h2>

            {{-- Bloco especial para KPIs --}}
            @if(isset($grafico['tipo']) && $grafico['tipo'] === 'kpis')
 

        <table style="width:100%; border-collapse:separate; border-spacing:15px; font-size:14px; margin:0 auto;">
            <tr>
                <td style="padding:15px; text-align:center; background:#f8f8f8; border-radius:8px; box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                    <strong>TMSep (média)</strong><br>
                    <span style="font-size:18px; font-weight:bold;">{{ $grafico['dados']['tmsep'] }}</span><br>
                    <small>Tempo Médio de Separação</small>
                </td>
                <td style="padding:15px; text-align:center; background:#f8f8f8; border-radius:8px; box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                    <strong>TMConf (média)</strong><br>
                    <span style="font-size:18px; font-weight:bold;">{{ $grafico['dados']['tmconf'] }}</span><br>
                    <small>Tempo Médio de Conferência</small>
                </td>
                <td style="padding:15px; text-align:center; background:#f8f8f8; border-radius:8px; box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                    <strong>TMCarr (média)</strong><br>
                    <span style="font-size:18px; font-weight:bold;">{{ $grafico['dados']['tmcarr'] }}</span><br>
                    <small>Tempo Médio de Carregamento</small>
                </td>
                <td style="padding:15px; text-align:center; background:#f8f8f8; border-radius:8px; box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                    <strong>TMGP (média)</strong><br>
                    <span style="font-size:18px; font-weight:bold;">{{ $grafico['dados']['tmgp'] }}</span><br>
                    <small>Tempo Médio Geral do Processo</small>
                </td>
            </tr>
            <tr>
                <td style="padding:15px; text-align:center; background:#f8f8f8; border-radius:8px; box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                    <strong>Volume (Qtd)</strong><br>
                    <span style="font-size:18px; font-weight:bold;">{{ $grafico['dados']['volume'] }}</span><br>
                    <small>Total de peças expedidas</small>
                </td>
                <td style="padding:15px; text-align:center; background:#f8f8f8; border-radius:8px; box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                    <strong>Peso Total</strong><br>
                    <span style="font-size:18px; font-weight:bold;">{{ $grafico['dados']['peso'] }}</span><br>
                    <small>Peso consolidado das cargas</small>
                </td>
                <td style="padding:15px; text-align:center; background:#f8f8f8; border-radius:8px; box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                    <strong>Demandas</strong><br>
                    <span style="font-size:18px; font-weight:bold;">{{ $grafico['dados']['demandas'] }}</span><br>
                    <small>Total de ordens processadas</small>
                </td>
                <td style="padding:15px; text-align:center; background:#f8f8f8; border-radius:8px; box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                    &nbsp;
                </td>
            </tr>
        </table>
    </div>
@endif


            {{-- Imagem do gráfico --}}
            @if(isset($grafico['url']))
                <img src="{{ $grafico['url'] }}" alt="Gráfico: {{ $grafico['titulo'] }}">
            @endif

            {{-- KPI numérico simples --}}
            @if(isset($grafico['media_mensal']))
                <div class="media-mensal">
                    <strong>Média Mensal:</strong> {{ $grafico['media_mensal'] }}
                </div>
            @endif

            {{-- Tabela de detalhes --}}
            @if(isset($grafico['detalhes']))
                <table>
                    <thead>
                        <tr>
                            <th>Transportadora / Motorista</th>
                            <th>Peças</th>
                            <th>Peso (kg)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($grafico['detalhes'] as $detalhe)
                        <tr>
                            <td>{{ $detalhe['nome'] }}</td>
                            <td>{{ $detalhe['qtd'] }}</td>
                            <td>{{ number_format($detalhe['peso'], 1, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            {{-- Descrição explicativa --}}
            @if(isset($grafico['descricao']))
                <p class="descricao">{{ $grafico['descricao'] }}</p>
            @endif
        </div>

        @if($index < count($graficos) - 1)
            <div class="page-break"></div>
        @endif
    @endforeach

    <!-- Paginação -->
    <script type="text/php">
        if ( isset($pdf) ) { 
            $pdf->page_script('
                if ($PAGE_COUNT > 0) {
                    $font = $fontMetrics->get_font("Arial", "normal");
                    $size = 8;
                    $pageText = "Página " . $PAGE_NUM . " de " . $PAGE_COUNT;
                    $y = 820; 
                    $x = 520;
                    $pdf->text($x, $y, $pageText, $font, $size);
                } 
            ');
        }
    </script>
</body>
</html>
