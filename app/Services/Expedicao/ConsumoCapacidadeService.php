<?php

namespace App\Services\Expedicao;

use App\Models\Expedicao\ExpedicaoProgramacao;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ConsumoCapacidadeService
{
    private const DATA_OPERACIONAL_MINIMA = '2000-01-01 00:00:00';

    public function calcular(Carbon $referencia): array
    {
        $inicio = $referencia->copy()->startOfDay();
        $fim = $referencia->copy()->endOfDay();
        $agora = $referencia->copy();

        if ($agora->lt($inicio)) {
            $agora = $inicio->copy();
        } elseif ($agora->gt($fim)) {
            $agora = $fim->copy();
        }

        $horasDecorridas = max(0.25, $inicio->diffInMinutes($agora) / 60);
        $horasRestantes = max(0, $agora->diffInMinutes($fim, false) / 60);
        $programacoes = $this->programacoesDoDia($inicio, $fim);
        $backlog = $this->backlogAntesDoDia($inicio, $agora);
        $etapas = $this->consumoPorEtapa($inicio, $agora);
        $consumo = $this->consumoPorContexto($inicio, $agora);
        $produtividade = $this->produtividade($inicio, $agora, $horasDecorridas);

        $programadas = $programacoes->where('tipo_demanda', ExpedicaoProgramacao::TIPO_PROGRAMADA);
        $oportunidades = $programacoes->where('tipo_demanda', ExpedicaoProgramacao::TIPO_OPORTUNIDADE);
        $executadas = $programacoes->filter(fn ($item) => $this->concluidaAteReferencia($item, $agora));
        $programadasExecutadas = $programadas->filter(fn ($item) => $this->concluidaAteReferencia($item, $agora))->count();
        $oportunidadesExecutadas = $oportunidades->filter(fn ($item) => $this->concluidaAteReferencia($item, $agora))->count();

        return [
            'data_operacao' => $referencia->toDateString(),
            'inicio' => $inicio,
            'fim' => $fim,
            'agora' => $agora,
            'horas_decorridas' => round($horasDecorridas, 2),
            'horas_restantes' => round($horasRestantes, 2),
            'headcount_estimado' => $produtividade['headcount_estimado'],
            'demanda' => [
                'programadas_total' => $programadas->count(),
                'programadas_executadas' => $programadasExecutadas,
                'programadas_pendentes' => max(0, $programadas->count() - $programadasExecutadas),
                'oportunidades_total' => $oportunidades->count(),
                'oportunidades_executadas' => $oportunidadesExecutadas,
                'oportunidades_pendentes' => max(0, $oportunidades->count() - $oportunidadesExecutadas),
                'backlog' => $backlog,
                'total_dia' => $programacoes->count(),
                'total_executado' => $executadas->count(),
                'total_pendente' => max(0, $programacoes->count() - $executadas->count()),
            ],
            'consumo' => $consumo,
            'etapas' => $etapas,
            'produtividade' => $produtividade,
        ];
    }

    private function programacoesDoDia(Carbon $inicio, Carbon $fim): Collection
    {
        return DB::table('_tb_expedicao_programacoes as ep')
            ->leftJoin('_tb_demanda as d', 'd.fo', '=', 'ep.fo')
            ->whereBetween('ep.agenda_entrega_em', [$inicio, $fim])
            ->select([
                'ep.id',
                'ep.fo',
                'ep.tipo_demanda',
                'ep.agenda_entrega_em',
                'd.separacao_finalizada_em',
                'd.conferencia_finalizada_em',
                'd.carregamento_finalizado_em',
            ])
            ->get();
    }

    private function backlogAntesDoDia(Carbon $inicio, Carbon $referencia): int
    {
        return DB::table('_tb_expedicao_programacoes as ep')
            ->leftJoin('_tb_demanda as d', 'd.fo', '=', 'ep.fo')
            ->where('ep.tipo_demanda', ExpedicaoProgramacao::TIPO_PROGRAMADA)
            ->where('ep.agenda_entrega_em', '<', $inicio)
            ->where(function ($query) use ($referencia) {
                $query->whereNull('d.carregamento_finalizado_em')
                    ->orWhere('d.carregamento_finalizado_em', '<', self::DATA_OPERACIONAL_MINIMA)
                    ->orWhere('d.carregamento_finalizado_em', '>', $referencia);
            })
            ->count();
    }

    private function consumoPorEtapa(Carbon $inicio, Carbon $referencia): array
    {
        $etapas = [
            'separacao' => 'separacao_finalizada_em',
            'conferencia' => 'conferencia_finalizada_em',
            'carregamento' => 'carregamento_finalizado_em',
        ];

        $resultado = [];

        foreach ($etapas as $etapa => $coluna) {
            $executadasHoje = DB::table('_tb_demanda')
                ->whereBetween($coluna, [$inicio, $referencia])
                ->count();
            $executadasCarteira = DB::table('_tb_demanda as d')
                ->whereBetween("d.{$coluna}", [$inicio, $referencia])
                ->whereExists(function ($subquery) {
                    $subquery->selectRaw('1')
                        ->from('_tb_expedicao_programacoes as ep')
                        ->whereColumn('ep.fo', 'd.fo');
                })
                ->count();

            $resultado[$etapa] = [
                'executadas' => $executadasHoje,
                'carteira' => $executadasCarteira,
                'paralela' => max(0, $executadasHoje - $executadasCarteira),
                'taxa_hora' => $this->taxaHistoricaPorColuna($coluna, $inicio),
            ];
        }

        return $resultado;
    }

    private function consumoPorContexto(Carbon $inicio, Carbon $referencia): array
    {
        $geral = DB::table('_tb_demanda')
            ->whereBetween('carregamento_finalizado_em', [$inicio, $referencia])
            ->count();
        $carteira = DB::table('_tb_demanda as d')
            ->whereBetween('d.carregamento_finalizado_em', [$inicio, $referencia])
            ->whereExists(function ($subquery) {
                $subquery->selectRaw('1')
                    ->from('_tb_expedicao_programacoes as ep')
                    ->whereColumn('ep.fo', 'd.fo');
            })
            ->count();
        $programadas = DB::table('_tb_demanda as d')
            ->whereBetween('d.carregamento_finalizado_em', [$inicio, $referencia])
            ->whereExists(function ($subquery) {
                $subquery->selectRaw('1')
                    ->from('_tb_expedicao_programacoes as ep')
                    ->whereColumn('ep.fo', 'd.fo')
                    ->where('ep.tipo_demanda', ExpedicaoProgramacao::TIPO_PROGRAMADA);
            })
            ->count();
        $oportunidades = DB::table('_tb_demanda as d')
            ->whereBetween('d.carregamento_finalizado_em', [$inicio, $referencia])
            ->whereExists(function ($subquery) {
                $subquery->selectRaw('1')
                    ->from('_tb_expedicao_programacoes as ep')
                    ->whereColumn('ep.fo', 'd.fo')
                    ->where('ep.tipo_demanda', ExpedicaoProgramacao::TIPO_OPORTUNIDADE);
            })
            ->count();

        return [
            'geral' => $geral,
            'carteira' => $carteira,
            'programadas' => $programadas,
            'oportunidades' => $oportunidades,
            'paralela' => max(0, $geral - $carteira),
        ];
    }

    private function produtividade(Carbon $inicio, Carbon $referencia, float $horasDecorridas): array
    {
        $finalizadasHoje = DB::table('_tb_demanda')
            ->whereBetween('carregamento_finalizado_em', [$inicio, $referencia])
            ->count();

        $taxaAtual = round($finalizadasHoje / max(0.25, $horasDecorridas), 2);
        $historico = $this->historicoFinalizadas($inicio);
        $taxaHistorica = $historico['taxa_hora'];
        $taxaBase = $finalizadasHoje >= 3
            ? (($taxaAtual * 0.6) + ($taxaHistorica * 0.4))
            : $taxaHistorica;

        if ($taxaBase <= 0) {
            $taxaBase = $this->taxaPorPrevisao();
        }

        return [
            'finalizadas_hoje' => $finalizadasHoje,
            'taxa_atual_hora' => round($taxaAtual, 2),
            'taxa_historica_hora' => round($taxaHistorica, 2),
            'taxa_efetiva_hora' => round(max(0.1, $taxaBase), 2),
            'headcount_estimado' => max(1, $historico['headcount_estimado']),
            'base_historica_dias' => $historico['dias'],
        ];
    }

    private function historicoFinalizadas(Carbon $inicio): array
    {
        $linhas = DB::table('_tb_demanda')
            ->whereBetween('carregamento_finalizado_em', [$inicio->copy()->subDays(14), $inicio->copy()->subSecond()])
            ->whereNotNull('carregamento_finalizado_em')
            ->select(['created_at', 'carregamento_finalizado_em'])
            ->get()
            ->groupBy(fn ($item) => Carbon::parse($item->carregamento_finalizado_em)->toDateString());

        if ($linhas->isEmpty()) {
            return ['taxa_hora' => 0, 'headcount_estimado' => 1, 'dias' => 0];
        }

        $taxas = $linhas->map(function (Collection $itens) {
            $primeiro = $itens->min(fn ($item) => Carbon::parse($item->created_at)->timestamp);
            $ultimo = $itens->max(fn ($item) => Carbon::parse($item->carregamento_finalizado_em)->timestamp);
            $horas = max(1, ($ultimo - $primeiro) / 3600);

            return $itens->count() / $horas;
        });

        $headcount = DB::table('_tb_demanda_distribuicoes')
            ->whereBetween('finalizado_em', [$inicio->copy()->subDays(14), $inicio->copy()->subSecond()])
            ->whereNotNull('separador_nome')
            ->whereRaw("TRIM(separador_nome) <> ''")
            ->distinct()
            ->count('separador_nome');

        return [
            'taxa_hora' => round((float) $taxas->avg(), 2),
            'headcount_estimado' => (int) $headcount,
            'dias' => $linhas->count(),
        ];
    }

    private function taxaHistoricaPorColuna(string $coluna, Carbon $inicio): float
    {
        $total = DB::table('_tb_demanda')
            ->whereBetween($coluna, [$inicio->copy()->subDays(14), $inicio->copy()->subSecond()])
            ->count();

        return round($total / max(1, 14 * 8), 2);
    }

    private function taxaPorPrevisao(): float
    {
        $mediaMin = DB::table('_tb_expedicao_previsoes')
            ->where('status', 'CALCULADO')
            ->whereNotNull('tempo_total_min')
            ->where('created_at', '>=', now()->subDays(14))
            ->avg('tempo_total_min');

        if (! $mediaMin || $mediaMin <= 0) {
            return 0.25;
        }

        return min(0.5, round(60 / (float) $mediaMin, 2));
    }

    private function concluidaAteReferencia($item, Carbon $referencia): bool
    {
        if (empty($item->carregamento_finalizado_em)) {
            return false;
        }

        $finalizadoEm = Carbon::parse($item->carregamento_finalizado_em);

        return $finalizadoEm->gte(self::DATA_OPERACIONAL_MINIMA)
            && $finalizadoEm->lessThanOrEqualTo($referencia);
    }
}
