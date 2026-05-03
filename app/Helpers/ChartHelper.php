<?php

namespace App\Helpers;

class ChartHelper
{
    public static function gerarGrafico($chartConfig, $width = 800, $height = 400)
{
    $url = "https://quickchart.io/chart?chart.jsVersion=3&c=" 
         . urlencode(json_encode($chartConfig)) 
         . "&w=$width&h=$height";
    return $url; // agora sempre usa Chart.js v3
}

public static function gerarGraficoBase64($chartConfig, $width = 800, $height = 400)
{
    $url = "https://quickchart.io/chart?chart.jsVersion=3&c=" 
         . urlencode(json_encode($chartConfig)) 
         . "&w=$width&h=$height";

    $imageData = @file_get_contents($url);
    if ($imageData === false) {
        return '';
    }

    return 'data:image/png;base64,' . base64_encode($imageData);
}

}
