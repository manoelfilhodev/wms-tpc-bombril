<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>{{ $titulo_principal }}</title>
    <style>
        @page { margin: 0cm 0cm; }
        body { margin: 5cm 2cm 2cm 2cm; font-family: Arial, Helvetica, sans-serif; }

        header { position: fixed; top: 0cm; left: 0cm; right: 0cm; height: 4cm; text-align: center; }
        header img { width: 100%; height: 100%; object-fit: cover; }

        footer { position: fixed; bottom: 0cm; left: 0cm; right: 0cm; height: 1.5cm; font-size: 10px; color: #555; text-align: center; line-height: 1.5cm; }

        .grafico-container { text-align: center; margin-top: 20px; }
        .grafico-container h2 { margin-bottom: 15px; font-size: 16px; color: #333; }
        .grafico-container img { max-width: 100%; height: auto; border: 1px solid #ddd; padding: 5px; background: #fff; }

        .descricao { margin-top: 12px; font-size: 12px; color: #555; text-align: center; line-height: 1.4; }

        .page-break { page-break-after: always; }
    </style>
</head>
<body>
    <header>
        <img src="{{ $topo }}" alt="Topo da Empresa">
        <h3>OPERAÇÃO TPC - DEXCO CAJAMAR</h3>
    </header>

    <footer>
        {{ $titulo_principal }} — Referente a {{ $data_referencia }} — Gerado em {{ $data_hoje }}
    </footer>

    @foreach($graficos as $index => $grafico)
        @if(isset($grafico['tipo']) && $grafico['tipo'] === 'kpis')
    <div class="grafico-container">
        <h2>{{ $grafico['titulo'] }}</h2>

        <table style="width:100%; border-collapse:collapse; font-size:14px; margin:0 auto;">
            <tr>
                <td style="border:1px solid #ccc; padding:15px; text-align:center;">
                    <strong>TMSep (média)</strong><br>
                    {{ $grafico['dados']['tmsep'] }}
                </td>
                <td style="border:1px solid #ccc; padding:15px; text-align:center;">
                    <strong>TMConf (média)</strong><br>
                    {{ $grafico['dados']['tmconf'] }}
                </td>
                <td style="border:1px solid #ccc; padding:15px; text-align:center;">
                    <strong>TMCarr (média)</strong><br>
                    {{ $grafico['dados']['tmcarr'] }}
                </td>
                <td style="border:1px solid #ccc; padding:15px; text-align:center;">
                    <strong>TMGP (média)</strong><br>
                    {{ $grafico['dados']['tmgp'] }}
                </td>
            </tr>
            <tr>
                <td style="border:1px solid #ccc; padding:15px; text-align:center;">
                    <strong>Volume (Qtd)</strong><br>
                    {{ $grafico['dados']['volume'] }}
                </td>
                <td style="border:1px solid #ccc; padding:15px; text-align:center;">
                    <strong>Peso Total</strong><br>
                    {{ $grafico['dados']['peso'] }}
                </td>
                <td style="border:1px solid #ccc; padding:15px; text-align:center;">
                    <strong>Demandas</strong><br>
                    {{ $grafico['dados']['demandas'] }}
                </td>
                <td style="border:1px solid #ccc; padding:15px; text-align:center;">
                    &nbsp; <!-- espaço vazio para manter layout -->
                </td>
            </tr>
        </table>
    </div>
@endif

        @if($index < count($graficos) - 1)
            <div class="page-break"></div>
        @endif
    @endforeach

    <script type="text/php">
        if ( isset($pdf) ) {
            $pdf->page_script('
                if ($PAGE_COUNT > 0) {
                    $font = $fontMetrics->get_font("Arial", "normal");
                    $size = 8;
                    $pageText = "Página " . $PAGE_NUM . " de " . $PAGE_COUNT;
                    $y = 820; $x = 520;
                    $pdf->text($x, $y, $pageText, $font, $size);
                }
            ');
        }
    </script>
</body>
</html>
