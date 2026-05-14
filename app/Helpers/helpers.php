<?php

if (! function_exists('formatarMinutosHora')) {
    function formatarMinutosHora(?int $minutos): string
    {
        if ($minutos === null) {
            return '-';
        }

        $horas = floor($minutos / 60);
        $mins = $minutos % 60;

        return $horas . ':' . str_pad($mins, 2, '0', STR_PAD_LEFT) . ':00';
    }
}

?>
