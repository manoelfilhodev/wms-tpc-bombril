<?php

namespace App\Services\Expedicao;

class CapacidadeAntecipacaoService
{
    public function avaliar(int $folgaOperacionalDt, int $capacidadeRestanteDt, array $risco): array
    {
        $disponivel = max(0, min($folgaOperacionalDt, $capacidadeRestanteDt));
        $nivel = 'SEM_CAPACIDADE';

        if (in_array($risco['nivel'], ['ALTO', 'COLAPSO'], true)) {
            $disponivel = 0;
        } elseif ($disponivel >= 6) {
            $nivel = 'ALTA';
        } elseif ($disponivel >= 3) {
            $nivel = 'MODERADA';
        } elseif ($disponivel >= 1) {
            $nivel = 'LIMITADA';
        }

        return [
            'possui_capacidade' => $disponivel > 0,
            'quantidade_estimada' => $disponivel,
            'nivel' => $nivel,
            'label' => $this->label($nivel),
            'classe' => $this->classe($nivel),
            'recomendacao' => $this->recomendacao($nivel),
        ];
    }

    private function label(string $nivel): string
    {
        return match ($nivel) {
            'ALTA' => 'Alta capacidade para antecipação',
            'MODERADA' => 'Capacidade moderada',
            'LIMITADA' => 'Capacidade limitada',
            default => 'Sem capacidade',
        };
    }

    private function classe(string $nivel): string
    {
        return match ($nivel) {
            'ALTA', 'MODERADA' => 'ok',
            'LIMITADA' => 'warning',
            default => 'danger',
        };
    }

    private function recomendacao(string $nivel): string
    {
        return match ($nivel) {
            'ALTA' => 'Liberar antecipações priorizadas por menor tempo operacional.',
            'MODERADA' => 'Liberar antecipações com acompanhamento de risco.',
            'LIMITADA' => 'Liberar somente oportunidades críticas e rápidas.',
            default => 'Não puxar novas oportunidades até reduzir pendências.',
        };
    }
}
