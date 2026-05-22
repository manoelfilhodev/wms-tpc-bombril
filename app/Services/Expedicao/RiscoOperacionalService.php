<?php

namespace App\Services\Expedicao;

class RiscoOperacionalService
{
    public function calcular(array $consumo, int $capacidadeRestanteDt): array
    {
        $demanda = $consumo['demanda'];
        $produtividade = $consumo['produtividade'];
        $horasRestantes = (float) $consumo['horas_restantes'];
        $taxaEfetiva = max(0.1, (float) $produtividade['taxa_efetiva_hora']);
        $pendenciaObrigatoria = (int) $demanda['programadas_pendentes'] + (int) $demanda['backlog'];
        $backlogProjetado = max(0, $pendenciaObrigatoria - $capacidadeRestanteDt);
        $pressaoHora = $horasRestantes > 0 ? $pendenciaObrigatoria / $horasRestantes : $pendenciaObrigatoria;
        $indicePressao = $pressaoHora / $taxaEfetiva;
        $nivel = 'BAIXO';

        if ($backlogProjetado > 0 && ($horasRestantes < 2 || $indicePressao >= 1.6)) {
            $nivel = 'COLAPSO';
        } elseif ($backlogProjetado > 0 || $indicePressao >= 1.25) {
            $nivel = 'ALTO';
        } elseif ($demanda['backlog'] > 0 || $indicePressao >= 0.85) {
            $nivel = 'MEDIO';
        }

        return [
            'nivel' => $nivel,
            'label' => $this->label($nivel),
            'classe' => $this->classe($nivel),
            'indice_pressao' => round($indicePressao, 2),
            'backlog_projetado' => $backlogProjetado,
            'impacto_proximo_turno' => min($backlogProjetado, max(0, $pendenciaObrigatoria)),
            'impacto_proximo_dia' => max(0, $backlogProjetado - max(0, $capacidadeRestanteDt)),
            'tendencia' => $this->tendencia($indicePressao, $backlogProjetado),
            'impacto_estimado' => $this->impacto($nivel, $backlogProjetado),
        ];
    }

    private function label(string $nivel): string
    {
        return match ($nivel) {
            'COLAPSO' => 'Colapso operacional',
            'ALTO' => 'Alto risco',
            'MEDIO' => 'Médio risco',
            default => 'Baixo risco',
        };
    }

    private function classe(string $nivel): string
    {
        return match ($nivel) {
            'COLAPSO', 'ALTO' => 'danger',
            'MEDIO' => 'warning',
            default => 'ok',
        };
    }

    private function tendencia(float $indicePressao, int $backlogProjetado): string
    {
        if ($backlogProjetado > 0 || $indicePressao >= 1.1) {
            return 'SUBINDO';
        }

        if ($indicePressao <= 0.7) {
            return 'CAINDO';
        }

        return 'ESTAVEL';
    }

    private function impacto(string $nivel, int $backlogProjetado): string
    {
        if ($nivel === 'COLAPSO') {
            return "Backlog projetado de {$backlogProjetado} DTs e risco de ruptura no turno.";
        }

        if ($nivel === 'ALTO') {
            return "Backlog projetado de {$backlogProjetado} DTs se o ritmo atual continuar.";
        }

        if ($nivel === 'MEDIO') {
            return 'Operação exige acompanhamento antes de liberar novas antecipações.';
        }

        return 'Ritmo atual suporta a demanda oficial estimada.';
    }
}
