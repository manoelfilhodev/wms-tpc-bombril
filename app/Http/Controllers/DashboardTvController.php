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
        $inicioDia = $hoje->copy()->startOfDay();
        $fimDia = $hoje->copy()->endOfDay();
        $inicioMes = $hoje->copy()->startOfMonth();

        $base = Demanda::query()->where('possui_sobra', true);
        $baseHoje = (clone $base)->whereDate('created_at', $hoje->toDateString());
        $baseBacklog = (clone $base)->whereDate('created_at', '<>', $hoje->toDateString());

        $status = [
            'a_separar' => (clone $baseHoje)->whereNull('separacao_iniciada_em')->count(),
            'separando' => (clone $baseHoje)->whereNotNull('separacao_iniciada_em')->whereNull('separacao_finalizada_em')->count(),
            'separado_parcial' => (clone $baseHoje)->where('separacao_resultado', 'PARCIAL')->count(),
            'separado_completo' => (clone $baseHoje)->where('separacao_resultado', 'COMPLETA')->count(),
            'backlog_a_separar' => (clone $baseBacklog)->whereNull('separacao_iniciada_em')->count(),
            'backlog_separando' => (clone $baseBacklog)->whereNotNull('separacao_iniciada_em')->whereNull('separacao_finalizada_em')->count(),
            'backlog_finalizado_parcial_hoje' => (clone $baseBacklog)
                ->where('separacao_resultado', 'PARCIAL')
                ->whereBetween('separacao_finalizada_em', [$inicioDia, $fimDia])
                ->count(),
            'backlog_finalizado_completo_hoje' => (clone $baseBacklog)
                ->where('separacao_resultado', 'COMPLETA')
                ->whereBetween('separacao_finalizada_em', [$inicioDia, $fimDia])
                ->count(),
        ];

        $tempoMedioMin = (clone $base)
            ->whereNotNull('separacao_iniciada_em')
            ->whereNotNull('separacao_finalizada_em')
            ->whereBetween('separacao_finalizada_em', [$inicioDia, $fimDia])
            ->selectRaw('AVG('.$this->tempoDiffMinExpr('separacao_iniciada_em', 'separacao_finalizada_em').') as media')
            ->value('media');

        $ranking = DB::table('_tb_demanda_distribuicoes as dd')
            ->join('_tb_demanda as d', 'd.id', '=', 'dd.demanda_id')
            ->where('d.possui_sobra', true)
            ->whereNotNull('dd.finalizado_em')
            ->whereBetween('dd.finalizado_em', [$inicioDia, $fimDia])
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
            ->whereNotNull('dd.finalizado_em')
            ->whereBetween('dd.finalizado_em', [$inicioDia, $fimDia])
            ->whereNotNull('dd.separador_nome')
            ->whereRaw("TRIM(dd.separador_nome) <> ''")
            ->selectRaw('dd.separador_nome as nome, SUM(COALESCE(dd.quantidade_skus, 0)) as total')
            ->groupBy('dd.separador_nome')
            ->havingRaw('SUM(COALESCE(dd.quantidade_skus, 0)) > 0')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $pecasPorColaboradorAcumulado = DB::table('_tb_demanda_distribuicoes as dd')
            ->join('_tb_demanda as d', 'd.id', '=', 'dd.demanda_id')
            ->where('d.possui_sobra', true)
            ->whereNotNull('dd.finalizado_em')
            ->whereNotNull('dd.separador_nome')
            ->whereRaw("TRIM(dd.separador_nome) <> ''")
            ->selectRaw('dd.separador_nome as nome, SUM(COALESCE(dd.quantidade_pecas, 0)) as total')
            ->groupBy('dd.separador_nome')
            ->havingRaw('SUM(COALESCE(dd.quantidade_pecas, 0)) > 0')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        $pecasPorColaboradorDia = DB::table('_tb_demanda_distribuicoes as dd')
            ->join('_tb_demanda as d', 'd.id', '=', 'dd.demanda_id')
            ->where('d.possui_sobra', true)
            ->whereNotNull('dd.finalizado_em')
            ->whereBetween('dd.finalizado_em', [$inicioDia, $fimDia])
            ->whereNotNull('dd.separador_nome')
            ->whereRaw("TRIM(dd.separador_nome) <> ''")
            ->selectRaw('dd.separador_nome as nome, SUM(COALESCE(dd.quantidade_pecas, 0)) as total')
            ->groupBy('dd.separador_nome')
            ->havingRaw('SUM(COALESCE(dd.quantidade_pecas, 0)) > 0')
            ->orderByDesc('total')
            ->limit(8)
            ->get()
            ->values();

        $pecasPorColaborador = [
            'dia' => [
                'labels' => $pecasPorColaboradorDia->pluck('nome')->values(),
                'values' => $pecasPorColaboradorDia->map(fn($item) => (int) $item->total)->values(),
            ],
            'acumulado' => [
                'labels' => $pecasPorColaboradorAcumulado->pluck('nome')->values(),
                'values' => $pecasPorColaboradorAcumulado->map(fn($item) => (int) $item->total)->values(),
            ],
        ];

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

        $turnos = collect($this->turnosOperacionais())->map(function ($turno, $codigo) use ($hoje) {
            [$inicioTurno, $fimTurno] = $this->intervaloTurno($hoje->toDateString(), $codigo);

            $q = Demanda::query()
                ->where('possui_sobra', true)
                ->whereNotNull('separacao_finalizada_em')
                ->whereBetween('separacao_finalizada_em', [$inicioTurno, $fimTurno]);

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
            'pecasPorColaborador' => $pecasPorColaborador,
            'diasMes' => $diasMes,
            'separacoesDia' => $separacoesDia,
            'parciaisDia' => $parciaisDia,
            'turnoLabels' => $turnos->pluck('turno')->values(),
            'turnoValues' => $turnos->pluck('total')->values(),
        ];
    }

    private function intervaloTurno(string $data, string $turno): array
    {
        $dia = Carbon::parse($data);

        return match ($turno) {
            'T2' => [
                $dia->copy()->setTime(14, 11),
                $dia->copy()->setTime(22, 10, 59),
            ],
            'T3' => [
                $dia->copy()->setTime(22, 11),
                $dia->copy()->addDay()->setTime(6, 10, 59),
            ],
            default => [
                $dia->copy()->setTime(6, 11),
                $dia->copy()->setTime(14, 10, 59),
            ],
        };
    }

    private function turnosOperacionais(): array
    {
        return [
            'T1' => ['label' => 'Turno A (T1)', 'periodo' => '06:11 as 14:10'],
            'T2' => ['label' => 'Turno B (T2)', 'periodo' => '14:11 as 22:10'],
            'T3' => ['label' => 'Turno C (T3)', 'periodo' => '22:11 as 06:10'],
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
