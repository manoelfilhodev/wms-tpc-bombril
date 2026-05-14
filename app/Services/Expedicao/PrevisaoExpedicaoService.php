<?php

namespace App\Services\Expedicao;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Expedicao\ExpedicaoProgramacao;
use App\Models\Expedicao\ExpedicaoPrevisao;
use App\Models\Expedicao\ExpedicaoCriterio;
use App\Models\Expedicao\ExpedicaoRota;
use App\Services\Expedicao\ConsultaRotaMapsService;

class PrevisaoExpedicaoService
{
    public function calcular(int $programacaoId): ExpedicaoPrevisao
    {
        $programacao = ExpedicaoProgramacao::findOrFail($programacaoId);

        $demanda = DB::table('_tb_demanda')
            ->where('fo', $programacao->fo)
            ->first();

        if (!$demanda) {
            return $this->registrarErro($programacao, 'Explosão/demanda não encontrada para a FO informada.');
        }

        $totalSkus = DB::table('_tb_demanda_itens')
            ->where('demanda_id', $demanda->id)
            ->count();

        $tipoCarga = $programacao->tipo_carga ?? 'PALETIZADA';
        $possuiPicking = (bool) $programacao->possui_picking;

        $tempoSeparacao = $this->buscarTempo('SEPARACAO', $tipoCarga, $possuiPicking, $totalSkus);
        $tempoConferencia = $this->buscarTempo('CONFERENCIA', $tipoCarga, $possuiPicking, $totalSkus);
        $tempoCarregamento = $this->buscarTempo('CARREGAMENTO', $tipoCarga, $possuiPicking, $totalSkus);
        $tempoViagem = $this->buscarTempoViagem($programacao);

        if ($tempoSeparacao === null || $tempoConferencia === null || $tempoCarregamento === null) {
            $categoriasFaltantes = collect([
                'SEPARACAO' => $tempoSeparacao,
                'CONFERENCIA' => $tempoConferencia,
                'CARREGAMENTO' => $tempoCarregamento,
            ])->filter(fn ($tempo) => $tempo === null)->keys()->implode(', ');

            return $this->registrarErro($programacao, "Critérios não encontrados: {$categoriasFaltantes}.", [
                'tempo_separacao_min' => $tempoSeparacao,
                'tempo_conferencia_min' => $tempoConferencia,
                'tempo_carregamento_min' => $tempoCarregamento,
                'tempo_viagem_min' => $tempoViagem,
            ]);
        }

        if ($tempoViagem === null) {
            return $this->registrarErro($programacao, 'Rota não encontrada para cálculo da saída prevista.', [
                'score_operacional' => $this->calcularScore($totalSkus, $tipoCarga, $possuiPicking),
                'tempo_separacao_min' => $tempoSeparacao,
                'tempo_conferencia_min' => $tempoConferencia,
                'tempo_carregamento_min' => $tempoCarregamento,
                'tempo_total_min' => $tempoSeparacao + $tempoConferencia + $tempoCarregamento,
                'risco_operacional' => 'MEDIO',
            ]);
        }

        $tempoTotal = $tempoSeparacao + $tempoConferencia + $tempoCarregamento + $tempoViagem;

        $agenda = Carbon::parse($programacao->agenda_entrega_em);

        $previsaoSaidaCaminhao = $agenda->copy()->subMinutes($tempoViagem);
        $previsaoInicioCarregamento = $previsaoSaidaCaminhao->copy()->subMinutes($tempoCarregamento);
        $previsaoInicioConferencia = $previsaoInicioCarregamento->copy()->subMinutes($tempoConferencia);
        $previsaoInicioSeparacao = $previsaoInicioConferencia->copy()->subMinutes($tempoSeparacao);
        $previsaoChegadaDoca = $previsaoInicioSeparacao->copy();

        $score = $this->calcularScore($totalSkus, $tipoCarga, $possuiPicking);
        $risco = $this->calcularRisco($score, $tempoTotal);

        $previsao = ExpedicaoPrevisao::create([
            'programacao_id' => $programacao->id,
            'fo' => $programacao->fo,
            'score_operacional' => $score,
            'tempo_separacao_min' => $tempoSeparacao,
            'tempo_conferencia_min' => $tempoConferencia,
            'tempo_carregamento_min' => $tempoCarregamento,
            'tempo_viagem_min' => $tempoViagem,
            'tempo_total_min' => $tempoTotal,
            'previsao_chegada_doca' => $previsaoChegadaDoca,
            'previsao_inicio_separacao' => $previsaoInicioSeparacao,
            'previsao_inicio_conferencia' => $previsaoInicioConferencia,
            'previsao_inicio_carregamento' => $previsaoInicioCarregamento,
            'previsao_saida_caminhao' => $previsaoSaidaCaminhao,
            'risco_operacional' => $risco,
            'status' => 'CALCULADO',
            'observacoes' => "Previsão calculada com {$totalSkus} SKUs.",
        ]);

        $programacao->update([
            'status_previsao' => 'PREVISAO_GERADA',
        ]);

        return $previsao;
    }

    private function buscarTempo(string $categoria, string $tipoCarga, bool $possuiPicking, int $totalSkus): ?int
    {
        $criterio = ExpedicaoCriterio::where('categoria', $categoria)
            ->where('ativo', true)
            ->where('tipo_carga', $tipoCarga)
            ->where(function ($query) use ($possuiPicking) {
                $query->whereNull('possui_picking')
                    ->orWhere('possui_picking', $possuiPicking);
            })
            ->where(function ($query) use ($totalSkus) {
                $query->whereNull('sku_min')->orWhere('sku_min', '<=', $totalSkus);
            })
            ->where(function ($query) use ($totalSkus) {
                $query->whereNull('sku_max')->orWhere('sku_max', '>=', $totalSkus);
            })
            ->orderByRaw('possui_picking is null')
            ->orderByDesc('sku_min')
            ->first();

        return $criterio?->tempo_previsto_minutos;
    }

    private function buscarTempoViagem(ExpedicaoProgramacao $programacao): ?int
    {
        $cidadeOrigem = $this->normalizarTexto(config('services.expedicao_rotas.origin_city', 'Sao Bernardo do Campo'));
        $ufOrigem = strtoupper((string) config('services.expedicao_rotas.origin_uf', 'SP'));
        $cidadeDestino = $this->normalizarTexto($programacao->cidade_destino);
        $ufDestino = strtoupper((string) $programacao->uf_destino);

        $rota = ExpedicaoRota::where('ativo', true)
            ->where('uf_origem', $ufOrigem)
            ->where('uf_destino', $ufDestino)
            ->get()
            ->first(function (ExpedicaoRota $rota) use ($cidadeOrigem, $cidadeDestino) {
                return $this->normalizarTexto($rota->cidade_origem) === $cidadeOrigem
                    && $this->normalizarTexto($rota->cidade_destino) === $cidadeDestino;
            });

        if ($rota?->tempo_operacional_minutos) {
            return $rota->tempo_operacional_minutos;
        }

        if ($rota?->tempo_api_minutos && $this->rotaApiAindaValida($rota)) {
            return $rota->tempo_api_minutos;
        }

        $dadosApi = app(ConsultaRotaMapsService::class)->consultar(
            (string) $programacao->cidade_destino,
            $ufDestino
        );

        if (! $dadosApi) {
            return $rota?->tempo_api_minutos;
        }

        $rota = $rota ?: new ExpedicaoRota([
            'cidade_origem' => config('services.expedicao_rotas.origin_city', 'Sao Bernardo do Campo'),
            'uf_origem' => $ufOrigem,
            'cidade_destino' => $programacao->cidade_destino,
            'uf_destino' => $ufDestino,
            'ativo' => true,
        ]);

        $rota->fill([
            'distancia_km' => $dadosApi['distancia_km'],
            'tempo_api_minutos' => $dadosApi['tempo_api_minutos'],
            'ultima_consulta_em' => now(),
            'ativo' => true,
        ]);

        $rota->save();

        return $rota->tempo_operacional_minutos ?? $rota->tempo_api_minutos;
    }

    private function rotaApiAindaValida(ExpedicaoRota $rota): bool
    {
        if (! $rota->ultima_consulta_em) {
            return false;
        }

        $cacheDias = max(1, (int) config('services.expedicao_rotas.cache_days', 30));

        return $rota->ultima_consulta_em->greaterThanOrEqualTo(now()->subDays($cacheDias));
    }

    private function normalizarTexto(?string $valor): string
    {
        $valor = trim((string) $valor);
        $valor = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $valor) ?: $valor;

        return strtoupper($valor);
    }

    private function calcularScore(int $totalSkus, string $tipoCarga, bool $possuiPicking): float
    {
        $score = 1;

        $score += min($totalSkus / 10, 5);

        if ($possuiPicking) {
            $score += 2;
        }

        if ($tipoCarga === 'PICKING') {
            $score += 2;
        }

        return round($score, 2);
    }

    private function calcularRisco(float $score, int $tempoTotal): string
    {
        if ($score >= 8 || $tempoTotal >= 480) {
            return 'CRITICO';
        }

        if ($score >= 6 || $tempoTotal >= 300) {
            return 'ALTO';
        }

        if ($score >= 3 || $tempoTotal >= 180) {
            return 'MEDIO';
        }

        return 'BAIXO';
    }

    private function registrarErro(ExpedicaoProgramacao $programacao, string $mensagem, array $dados = []): ExpedicaoPrevisao
    {
        $programacao->update([
            'status_previsao' => 'ERRO_DADOS',
        ]);

        return ExpedicaoPrevisao::create(array_merge([
            'programacao_id' => $programacao->id,
            'fo' => $programacao->fo,
            'status' => 'ERRO',
            'risco_operacional' => 'CRITICO',
            'observacoes' => $mensagem,
        ], array_filter($dados, fn ($valor) => $valor !== null)));
    }
}
