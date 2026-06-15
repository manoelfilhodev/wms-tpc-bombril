<?php

namespace App\Traits;

trait ExpedicaoBuscaTrait
{
    protected function aplicarBuscaExpedicao($query, string $busca, array $colunasDt, array $colunasTexto = []): void
    {
        if ($busca === '') {
            return;
        }

        $termosDt = $this->termosBuscaDt($busca);

        $query->where(function ($query) use ($busca, $colunasDt, $colunasTexto, $termosDt) {
            foreach ($colunasTexto as $coluna) {
                $query->orWhere($coluna, 'like', "%{$busca}%");
            }

            foreach ($termosDt as $termo) {
                foreach ($colunasDt as $coluna) {
                    $query->orWhere($coluna, 'like', "%{$termo}%");
                }
            }
        });
    }

    protected function termosBuscaDt(string $busca): array
    {
        $termos = preg_split('/[\s,;|]+/', $busca, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return collect($termos)
            ->map(fn (string $termo) => trim($termo))
            ->filter()
            ->unique()
            ->take(50)
            ->values()
            ->all();
    }
}
