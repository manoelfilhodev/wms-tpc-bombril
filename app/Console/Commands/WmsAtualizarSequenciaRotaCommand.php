<?php

namespace App\Console\Commands;

use App\Services\Wms\WmsRotaPickingService;
use Illuminate\Console\Command;

class WmsAtualizarSequenciaRotaCommand extends Command
{
    protected $signature = 'wms:atualizar-sequencia-rota';

    protected $description = 'Atualiza a sequência de rota das posições WMS pela rota física PA-PF.';

    public function handle(WmsRotaPickingService $service): int
    {
        $resumo = $service->atualizarSequenciasPosicoes();

        $this->info("Posições lidas: {$resumo['total']}");
        $this->info("Posições atualizadas: {$resumo['atualizadas']}");
        $this->info("Posições ignoradas: {$resumo['ignoradas']}");

        return self::SUCCESS;
    }
}
