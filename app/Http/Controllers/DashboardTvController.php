<?php

namespace App\Http\Controllers;

use App\Models\Demanda;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardTvController extends Controller
{
    public function index()
    {
        $dados = $this->dadosPicking();
        return view('dashboard.tv', $dados);
    }

    public function dados()
    {
        return response()->json($this->dadosPicking());
    }

    private function dadosPicking(): array
    {
        $hoje = Carbon::today();
        $inicioSemana = $hoje->copy()->subDays(6);
        $inicioMes = $hoje->copy()->startOfMonth();

        $base = Demanda::query()->where('possui_sobra', true);

        $status = [
            'a_separar' => (clone $base)->whereNull('separacao_iniciada_em')->count(),
            'separando' => (clone $base)->whereNotNull('separacao_iniciada_em')->whereNull('separacao_finalizada_em')->count(),
            'separado_parcial' => (clone $base)->where('separacao_resultado', 'PARCIAL')->count(),
            'separado_completo' => (clone $base)->where('separacao_resultado', 'COMPLETA')->count(),
        ];

        $tempoMedioMin = (clone $base)
            ->whereNotNull('separacao_iniciada_em')
            ->whereNotNull('separacao_finalizada_em')
            ->selectRaw('AVG('.$this->tempoDiffMinExpr('separacao_iniciada_em', 'separacao_finalizada_em').') as media')
            ->value('media');

        $ranking = DB::table('_tb_demanda_distribuicoes as dd')
            ->join('_tb_demanda as d', 'd.id', '=', 'dd.demanda_id')
            ->where('d.possui_sobra', true)
            ->whereDate('d.separacao_iniciada_em', '>=', $inicioSemana->toDateString())
            ->whereNotNull('dd.separador_nome')
            ->whereRaw("TRIM(dd.separador_nome) <> ''")
            ->selectRaw('dd.separador_nome as nome, SUM(dd.quantidade_pecas) as total')
            ->groupBy('dd.separador_nome')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $rankingSkus = DB::table('_tb_demanda_distribuicoes as dd')
            ->join('_tb_demanda as d', 'd.id', '=', 'dd.demanda_id')
            ->where('d.possui_sobra', true)
            ->whereDate('dd.finalizado_em', '>=', $inicioSemana->toDateString())
            ->whereNotNull('dd.finalizado_em')
            ->whereNotNull('dd.separador_nome')
            ->whereRaw("TRIM(dd.separador_nome) <> ''")
            ->selectRaw('dd.separador_nome as nome, SUM(COALESCE(dd.quantidade_skus, 0)) as total')
            ->groupBy('dd.separador_nome')
            ->havingRaw('SUM(COALESCE(dd.quantidade_skus, 0)) > 0')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $diasMes = [];
        $separacoesDia = [];
        $parciaisDia = [];
        for ($d = $inicioMes->copy(); $d->lte($hoje); $d->addDay()) {
            $diasMes[] = $d->format('d');
            $separacoesDia[] = Demanda::query()
                ->where('possui_sobra', true)
                ->whereDate('separacao_finalizada_em', $d->toDateString())
                ->count();
            $parciaisDia[] = Demanda::query()
                ->where('possui_sobra', true)
                ->where('separacao_resultado', 'PARCIAL')
                ->whereDate('separacao_finalizada_em', $d->toDateString())
                ->count();
        }

        $turnos = collect($this->turnosOperacionais())->map(function ($turno, $codigo) {
            $q = Demanda::query()
                ->where('possui_sobra', true)
                ->whereNotNull('separacao_iniciada_em');

            if ($codigo === 'T1') {
                $q->whereRaw("TIME(separacao_iniciada_em) BETWEEN '06:00:00' AND '13:59:59'");
            } elseif ($codigo === 'T2') {
                $q->whereRaw("TIME(separacao_iniciada_em) BETWEEN '14:00:00' AND '21:59:59'");
            } else {
                $q->where(function ($qq) {
                    $qq->whereRaw("TIME(separacao_iniciada_em) BETWEEN '22:00:00' AND '23:59:59'")
                        ->orWhereRaw("TIME(separacao_iniciada_em) BETWEEN '00:00:00' AND '05:59:59'");
                });
            }

            return [
                'turno' => $turno['label'],
                'total' => (clone $q)->count(),
            ];
        });

        return [
            'status' => $status,
            'tempoMedioMin' => $tempoMedioMin ? round((float) $tempoMedioMin, 1) : 0,
            'ranking' => $ranking,
            'rankingSkus' => $rankingSkus,
            'diasMes' => $diasMes,
            'separacoesDia' => $separacoesDia,
            'parciaisDia' => $parciaisDia,
            'turnoLabels' => $turnos->pluck('turno')->values(),
            'turnoValues' => $turnos->pluck('total')->values(),
        ];
    }

    private function turnosOperacionais(): array
    {
        return [
            'T1' => ['label' => 'Turno A (T1)', 'periodo' => '06h as 14h'],
            'T2' => ['label' => 'Turno B (T2)', 'periodo' => '14h as 22h'],
            'T3' => ['label' => 'Turno C (T3)', 'periodo' => '22h as 06h'],
        ];
    }

    private function tempoDiffMinExpr(string $colInicio, string $colFim): string
    {
        $driver = DB::connection()->getDriverName();
        if ($driver === 'sqlite') {
            return "(julianday({$colFim}) - julianday({$colInicio})) * 1440";
        }

        return "TIMESTAMPDIFF(MINUTE, {$colInicio}, {$colFim})";
    }
}
