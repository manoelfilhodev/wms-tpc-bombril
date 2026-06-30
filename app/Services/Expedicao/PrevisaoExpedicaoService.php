<?php

namespace App\Services\Expedicao;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\ClienteTransitTime;
use App\Models\Expedicao\ExpedicaoProgramacao;
use App\Models\Expedicao\ExpedicaoPrevisao;
use App\Models\Expedicao\ExpedicaoCriterio;
use App\Models\Expedicao\ExpedicaoRota;

class PrevisaoExpedicaoService
{
    private const TEMPOS_OPERACIONAIS_PADRAO = [
        'SEPARACAO' => 90,
        'CONFERENCIA' => 60,
        'CARREGAMENTO' => 120,
    ];

    public function calcular(int $programacaoId): ExpedicaoPrevisao
    {
        $programacao = ExpedicaoProgramacao::findOrFail($programacaoId);

        $demanda = DB::table('_tb_demanda')
            ->where('fo', $programacao->fo)
            ->first();

        $totalSkus = $demanda
            ? DB::table('_tb_demanda_itens')
                ->where('demanda_id', $demanda->id)
                ->count()
            : 0;

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
            return $this->registrarErro($programacao, 'Transit time não encontrado para cálculo da saída prevista.', [
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
            'observacoes' => $demanda
                ? "Previsão calculada com {$totalSkus} SKUs."
                : 'Previsão planejada sem explosão/demanda vinculada.',
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

        return $criterio?->tempo_previsto_minutos
            ?? self::TEMPOS_OPERACIONAIS_PADRAO[$categoria]
            ?? null;
    }

    private function buscarTempoViagem(ExpedicaoProgramacao $programacao): ?int
    {
        return $this->tempoViagemTransitTimeCliente($programacao);
    }

    public function tempoViagemTransitTimeCliente(ExpedicaoProgramacao $programacao): ?int
    {
        $tempoDiversos = $this->tempoViagemDiversosPorUf($programacao);

        if ($tempoDiversos !== null) {
            return $tempoDiversos;
        }

        $transitTime = $this->transitTimeCliente($programacao);

        if (! $transitTime) {
            return null;
        }

        $dias = $this->usaCargaFechada($programacao)
            ? $transitTime->transit_time_fechada_dias
            : $transitTime->transit_time_fracionada_dias;

        return max(0, (int) $dias) * 1440;
    }

    private function tempoViagemDiversosPorUf(ExpedicaoProgramacao $programacao): ?int
    {
        if (! $this->isDestinoDiversos($programacao)) {
            return null;
        }

        $ufDestino = strtoupper(trim((string) $programacao->uf_destino));

        if ($ufDestino === '') {
            return null;
        }

        $campoDias = $this->usaCargaFechada($programacao)
            ? 'transit_time_fechada_dias'
            : 'transit_time_fracionada_dias';

        $maiorDias = ClienteTransitTime::query()
            ->where('ativo', true)
            ->where('uf', $ufDestino)
            ->max($campoDias);

        if ($maiorDias === null) {
            return null;
        }

        return max(0, (int) $maiorDias) * 1440;
    }

    private function transitTimeCliente(ExpedicaoProgramacao $programacao): ?ClienteTransitTime
    {
        $codigoCliente = trim((string) $programacao->codigo_cliente);

        if ($codigoCliente !== '') {
            $transitTime = ClienteTransitTime::query()
                ->where('ativo', true)
                ->where('codigo_cliente', $codigoCliente)
                ->first();

            if ($transitTime) {
                return $transitTime;
            }
        }

        $cidadeDestino = $this->normalizarTexto($programacao->cidade_destino);
        $ufDestino = strtoupper(trim((string) $programacao->uf_destino));

        if ($cidadeDestino === '' || $ufDestino === '') {
            return null;
        }

        $transitTime = ClienteTransitTime::query()
            ->where('ativo', true)
            ->where('uf', $ufDestino)
            ->where('cidade', strtoupper(trim((string) $programacao->cidade_destino)))
            ->first();

        if ($transitTime) {
            return $transitTime;
        }

        return ClienteTransitTime::query()
            ->where('ativo', true)
            ->where('uf', $ufDestino)
            ->where('cidade', 'like', substr($cidadeDestino, 0, 3) . '%')
            ->get()
            ->first(fn (ClienteTransitTime $transitTime) => $this->normalizarTexto($transitTime->cidade) === $cidadeDestino);
    }

    public function possuiTransitTimeBase(ExpedicaoProgramacao $programacao): bool
    {
        return $this->transitTimeCliente($programacao) !== null;
    }

    public function preencherCodigoClientePorTransitTime(ExpedicaoProgramacao $programacao): bool
    {
        if (filled($programacao->codigo_cliente)) {
            return false;
        }

        $transitTime = $this->transitTimeCliente($programacao);

        if (! $transitTime) {
            return false;
        }

        $programacao->offsetUnset('demanda');
        $programacao->forceFill(['codigo_cliente' => $transitTime->codigo_cliente])->save();

        return true;
    }

    private function usaCargaFechada(ExpedicaoProgramacao $programacao): bool
    {
        $tipoCarga = $this->normalizarTexto($programacao->tipo_carga);

        return str_contains($tipoCarga, 'PALET')
            || str_contains($tipoCarga, 'FECHAD');
    }

    private function isDestinoDiversos(ExpedicaoProgramacao $programacao): bool
    {
        $codigoCliente = $this->normalizarTexto($programacao->codigo_cliente);
        $cidadeDestino = $this->normalizarTexto($programacao->cidade_destino);
        $cliente = $this->normalizarTexto($programacao->cliente);

        return $codigoCliente === 'DIV'
            || $cidadeDestino === 'DIVERSOS'
            || $cliente === 'DIVERSOS';
    }

    public static function ajustarTempoViagemCaminhao(?int $tempoApiMinutos, $distanciaKm = null): ?int
    {
        if ($tempoApiMinutos === null && $distanciaKm === null) {
            return null;
        }

        $multiplicador = max(1.0, (float) config('services.expedicao_rotas.truck_time_multiplier', 1.6));
        $bufferMinutos = max(0, (int) config('services.expedicao_rotas.truck_fixed_buffer_minutes', 20));
        $pisoMinutos = max(0, (int) config('services.expedicao_rotas.truck_min_minutes', 60));
        $velocidadeMedia = max(1.0, (float) config('services.expedicao_rotas.truck_average_speed_kmh', 45));

        $candidatos = [];

        if ($tempoApiMinutos !== null && $tempoApiMinutos > 0) {
            $candidatos[] = (int) ceil($tempoApiMinutos * $multiplicador) + $bufferMinutos;
        }

        if ($distanciaKm !== null && (float) $distanciaKm > 0) {
            $candidatos[] = (int) ceil(((float) $distanciaKm / $velocidadeMedia) * 60) + $bufferMinutos;
        }

        if ($pisoMinutos > 0) {
            $candidatos[] = $pisoMinutos;
        }

        return $candidatos ? max($candidatos) : null;
    }

    public function tempoViagemOperacionalCaminhao(?ExpedicaoRota $rota): ?int
    {
        if (! $rota) {
            return null;
        }

        if ($rota->tempo_operacional_minutos) {
            return (int) $rota->tempo_operacional_minutos;
        }

        return self::ajustarTempoViagemCaminhao(
            $rota->tempo_api_minutos ? (int) $rota->tempo_api_minutos : null,
            $rota->distancia_km
        );
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
