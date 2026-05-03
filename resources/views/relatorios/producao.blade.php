<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>{{ $titulo_principal }}</title>
    <style>
        @page {
            margin: 0cm 0cm;
        }

        body {
            margin: 5cm 2cm 2cm 2cm; /* espaço para cabeçalho e rodapé */
            font-family: Arial, Helvetica, sans-serif;
        }

        header {
            position: fixed;
            top: 0cm;
            left: 0cm;
            right: 0cm;
            height: 4cm;
            text-align: center;
            color: rgb(0, 71, 186);
        }

        header img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        footer {
            position: fixed;
            bottom: 0cm;
            left: 0cm;
            right: 0cm;
            height: 1.5cm;
            font-size: 10px;
            color: #555;
            text-align: center;
            line-height: 1.5cm;
        }

        .grafico-container {
            text-align: center;
            margin-top: 20px;
        }

        .grafico-container h2 {
            margin-bottom: 15px;
            font-size: 16px;
            color: #333;
        }

        .grafico-container img {
            max-width: 100%;
            height: auto;
            border: 1px solid #ddd;
            padding: 5px;
            background: #fff;
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

        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <!-- TOPO -->
    <header>
        <img src="{{ $topo }}" alt="Topo da Empresa">
        <h3>OPERAÇÃO TPC - DEXCO CAJAMAR</h3>
    </header>

    <!-- RODAPÉ -->
    <footer>
        {{ $titulo_principal }} — Referente a {{ $data_referencia }} — Gerado em {{ $data_hoje }}
    </footer>

    <!-- CONTEÚDO -->
    @foreach($graficos as $index => $grafico)
        <div class="grafico-container">
            <h2>{{ $grafico['titulo'] }}</h2>
            <img src="{{ $grafico['url'] }}" alt="Gráfico: {{ $grafico['titulo'] }}">

            {{-- Média mensal (quando existir) --}}
            @if(isset($grafico['media_mensal']))
                <div class="media-mensal">
                    <strong>Média Mensal:</strong> {{ $grafico['media_mensal'] }} minutos
                </div>
            @endif
            @if(isset($grafico['detalhes']))
    <table style="margin:20px auto; width:80%; border-collapse: collapse; font-size:12px;">
        <thead>
            <tr style="background:#f0f0f0;">
                <th style="border:1px solid #ccc; padding:6px;">SKU</th>
                <th style="border:1px solid #ccc; padding:6px;">Planejado</th>
                <th style="border:1px solid #ccc; padding:6px;">Realizado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($grafico['detalhes'] as $detalhe)
            <tr>
                <td style="border:1px solid #ccc; padding:6px;">{{ $detalhe['sku'] }}</td>
                <td style="border:1px solid #ccc; padding:6px; text-align:right;">{{ $detalhe['planejado'] }}</td>
                <td style="border:1px solid #ccc; padding:6px; text-align:right;">{{ $detalhe['realizado'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endif

            {{-- Descrição explicativa --}}
            @if(isset($grafico['descricao']))
                <p class="descricao">
                    {{ $grafico['descricao'] }}
                </p>
            @endif
        </div>

        @if($index < count($graficos) - 1)
            <div class="page-break"></div>
        @endif
    @endforeach

    <!-- Script para paginação -->
    <script type="text/php">
        if ( isset($pdf) ) { 
            $pdf->page_script('
                if ($PAGE_COUNT > 0) {
                    $font = $fontMetrics->get_font("Arial, Helvetica, sans-serif", "normal");
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
