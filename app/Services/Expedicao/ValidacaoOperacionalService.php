<?php

namespace App\Services\Expedicao;

use Carbon\Carbon;

class ValidacaoOperacionalService
{
    private const DATA_OPERACIONAL_MINIMA = '2000-01-01 00:00:00';

    public function validarEtapa(?string $inicio, ?string $fim, int $limiteMinutos): array
    {
        if (!$inicio || !$fim) {
            return [
                'valido' => false,
                'anomalia' => false,
                'status' => 'SEM_REALIZADO',
                'motivo' => null,
            ];
        }

        $inicioCarbon = Carbon::parse($inicio);
        $fimCarbon = Carbon::parse($fim);

        if ($inicioCarbon->lt(self::DATA_OPERACIONAL_MINIMA) || $fimCarbon->lt(self::DATA_OPERACIONAL_MINIMA)) {
            return [
                'valido' => false,
                'anomalia' => false,
                'status' => 'SEM_REALIZADO',
                'motivo' => null,
            ];
        }

        if ($fimCarbon->lessThan($inicioCarbon)) {
            return [
                'valido' => false,
                'anomalia' => true,
                'status' => 'ANOMALIA_OPERACIONAL',
                'motivo' => 'Horário final menor que horário inicial',
            ];
        }

        $realizadoMin = $inicioCarbon->diffInMinutes($fimCarbon);

        if ($realizadoMin < 1) {
            return [
                'valido' => false,
                'anomalia' => true,
                'status' => 'ANOMALIA_OPERACIONAL',
                'motivo' => 'Tempo realizado inferior a 1 minuto',
            ];
        }

        if ($realizadoMin > $limiteMinutos) {
            return [
                'valido' => false,
                'anomalia' => true,
                'status' => 'ANOMALIA_OPERACIONAL',
                'motivo' => 'Tempo realizado acima do limite operacional',
            ];
        }

        return [
            'valido' => true,
            'anomalia' => false,
            'status' => 'VALIDO',
            'motivo' => null,
            'realizado_min' => $realizadoMin,
        ];
    }
}
