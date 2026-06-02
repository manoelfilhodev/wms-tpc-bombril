<?php

namespace App\Http\Controllers\Expedicao;

use App\Http\Controllers\Controller;
use App\Models\Demanda;
use App\Models\Expedicao\ExpedicaoProgramacao;
use App\Services\Expedicao\TimelineDtService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TimelineDtExpedicaoController extends Controller
{
    public function index(Request $request)
    {
        $busca = trim((string) $request->input('busca', ''));
        $status = strtoupper((string) $request->input('status', 'TODAS'));
        $status = in_array($status, ['TODAS', 'EM_ANDAMENTO', 'CARREGADAS', 'COM_SAIDA'], true) ? $status : 'TODAS';
        $tipoDemanda = strtoupper((string) $request->input('tipo_demanda', 'TODAS'));
        $tipoDemanda = in_array($tipoDemanda, ExpedicaoProgramacao::tiposDemanda(), true) ? $tipoDemanda : 'TODAS';

        $base = DB::table('_tb_demanda as d')
            ->leftJoin('_tb_expedicao_programacoes as ep', 'ep.fo', '=', 'd.fo')
            ->where('d.tipo', 'EXPEDICAO');

        $this->aplicarFiltroStatus($base, $status);
        $this->aplicarFiltroTipoDemanda($base, $tipoDemanda);

        if ($busca !== '') {
            $base->where(function ($query) use ($busca) {
                $query->where('d.fo', 'like', "%{$busca}%")
                    ->orWhere('ep.dt_sap', 'like', "%{$busca}%")
                    ->orWhere('d.cliente', 'like', "%{$busca}%")
                    ->orWhere('ep.cliente', 'like', "%{$busca}%")
                    ->orWhere('ep.cidade_destino', 'like', "%{$busca}%")
                    ->orWhere('ep.uf_destino', 'like', "%{$busca}%");
            });
        }

        $dts = $base
            ->select([
                'd.id',
                'd.fo',
                'd.cliente',
                'd.transportadora',
                'd.created_at',
                'd.separacao_iniciada_em',
                'd.separacao_finalizada_em',
                'd.conferencia_iniciada_em',
                'd.conferencia_finalizada_em',
                'd.carregamento_iniciado_em',
                'd.carregamento_finalizado_em',
                'd.saida_veiculo_em',
                'ep.dt_sap',
                'ep.cidade_destino',
                'ep.uf_destino',
                'ep.agenda_entrega_em',
            ])
            ->selectRaw("COALESCE(ep.tipo_demanda, ?) as tipo_demanda", [ExpedicaoProgramacao::TIPO_OPORTUNIDADE])
            ->orderByRaw('COALESCE(d.saida_veiculo_em, d.carregamento_finalizado_em, d.updated_at, d.created_at) desc')
            ->paginate(20)
            ->withQueryString();

        $resumo = $this->montarResumo();

        return view('expedicao.timeline-dts.index', [
            'dts' => $dts,
            'busca' => $busca,
            'status' => $status,
            'tipoDemanda' => $tipoDemanda,
            'resumo' => $resumo,
        ]);
    }

    public function show(string $fo, TimelineDtService $timelineService)
    {
        $demanda = Demanda::with(['distribuicoes', 'separador'])->where('fo', $fo)->firstOrFail();
        $programacao = ExpedicaoProgramacao::with('ultimaPrevisao')->where('fo', $fo)->first();

        return view('expedicao.timeline-dts.show', [
            'demanda' => $demanda,
            'programacao' => $programacao,
            'timeline' => $timelineService->montar($demanda, $programacao),
            'statusOperacional' => $this->statusOperacional($demanda),
        ]);
    }

    private function aplicarFiltroStatus($query, string $status): void
    {
        if ($status === 'EM_ANDAMENTO') {
            $query->where(function ($query) {
                $query->whereNull('d.carregamento_finalizado_em')
                    ->orWhere('d.carregamento_finalizado_em', '<', Demanda::DATA_OPERACIONAL_MINIMA);
            });
        }

        if ($status === 'CARREGADAS') {
            $query->whereNotNull('d.carregamento_finalizado_em')
                ->where('d.carregamento_finalizado_em', '>=', Demanda::DATA_OPERACIONAL_MINIMA)
                ->where(function ($query) {
                    $query->whereNull('d.saida_veiculo_em')
                        ->orWhere('d.saida_veiculo_em', '<', Demanda::DATA_OPERACIONAL_MINIMA);
                });
        }

        if ($status === 'COM_SAIDA') {
            $query->whereNotNull('d.saida_veiculo_em')
                ->where('d.saida_veiculo_em', '>=', Demanda::DATA_OPERACIONAL_MINIMA);
        }
    }

    private function aplicarFiltroTipoDemanda($query, string $tipoDemanda): void
    {
        if ($tipoDemanda === 'TODAS') {
            return;
        }

        if ($tipoDemanda === ExpedicaoProgramacao::TIPO_PROGRAMADA) {
            $query->where('ep.tipo_demanda', ExpedicaoProgramacao::TIPO_PROGRAMADA);

            return;
        }

        if ($tipoDemanda === ExpedicaoProgramacao::TIPO_OPORTUNIDADE) {
            $query->where(function ($query) {
                $query->whereNull('ep.tipo_demanda')
                    ->orWhere('ep.tipo_demanda', ExpedicaoProgramacao::TIPO_OPORTUNIDADE);
            });
        }
    }

    private function montarResumo(): array
    {
        $base = DB::table('_tb_demanda as d')->where('d.tipo', 'EXPEDICAO');

        return [
            'total' => (clone $base)->count(),
            'em_andamento' => (clone $base)
                ->where(function ($query) {
                    $query->whereNull('d.carregamento_finalizado_em')
                        ->orWhere('d.carregamento_finalizado_em', '<', Demanda::DATA_OPERACIONAL_MINIMA);
                })
                ->count(),
            'carregadas' => (clone $base)
                ->whereNotNull('d.carregamento_finalizado_em')
                ->where('d.carregamento_finalizado_em', '>=', Demanda::DATA_OPERACIONAL_MINIMA)
                ->where(function ($query) {
                    $query->whereNull('d.saida_veiculo_em')
                        ->orWhere('d.saida_veiculo_em', '<', Demanda::DATA_OPERACIONAL_MINIMA);
                })
                ->count(),
            'com_saida' => (clone $base)
                ->whereNotNull('d.saida_veiculo_em')
                ->where('d.saida_veiculo_em', '>=', Demanda::DATA_OPERACIONAL_MINIMA)
                ->count(),
        ];
    }

    private function statusOperacional($dt): array
    {
        if ($this->dataValida($dt->saida_veiculo_em ?? null)) {
            return ['label' => 'Com saída', 'class' => 'ok'];
        }
        if ($this->dataValida($dt->carregamento_finalizado_em ?? null)) {
            return ['label' => 'Carregada', 'class' => 'loaded'];
        }
        if ($this->dataValida($dt->carregamento_iniciado_em ?? null)) {
            return ['label' => 'Carregando', 'class' => 'active'];
        }
        if ($this->dataValida($dt->conferencia_finalizada_em ?? null)) {
            return ['label' => 'Aguard. carga', 'class' => 'active'];
        }
        if ($this->dataValida($dt->conferencia_iniciada_em ?? null)) {
            return ['label' => 'Conferindo', 'class' => 'active'];
        }
        if ($this->dataValida($dt->separacao_finalizada_em ?? null)) {
            return ['label' => 'Separada', 'class' => 'active'];
        }
        if ($this->dataValida($dt->separacao_iniciada_em ?? null)) {
            return ['label' => 'Separando', 'class' => 'active'];
        }

        return ['label' => 'A separar', 'class' => 'pending'];
    }

    private function dataValida($data): bool
    {
        return $data && \Carbon\Carbon::parse($data)->gte(Demanda::DATA_OPERACIONAL_MINIMA);
    }
}
