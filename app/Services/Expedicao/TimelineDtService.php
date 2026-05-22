<?php

namespace App\Services\Expedicao;

use App\Models\Demanda;
use App\Models\Expedicao\ExpedicaoProgramacao;
use App\Models\SystemLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TimelineDtService
{
    public function montar(Demanda $demanda, ?ExpedicaoProgramacao $programacao = null): array
    {
        $logs = SystemLog::where('entity_type', 'demanda')
            ->where('entity_id', (string) $demanda->id)
            ->orderBy('created_at')
            ->get();

        return [
            $this->timelineItem('Importação', $programacao?->created_at ?? $demanda->created_at, null, 'DT registrada no sistema.', $programacao?->origem_demanda),
            $this->timelineItem('Início da separação', $demanda->separacao_iniciada_em, null, 'Separação iniciada.', $this->responsavelSeparacao($demanda)),
            $this->timelineItem('Fim da separação', $demanda->separacao_finalizada_em, $demanda->separacao_iniciada_em, 'Separação finalizada.', $this->responsavelSeparacao($demanda)),
            $this->timelineItem('Início da conferência', $demanda->conferencia_iniciada_em, null, 'Conferência iniciada.', $this->responsavelLog($logs, 'conferencia', 'iniciar_agora')),
            $this->timelineItem('Fim da conferência', $demanda->conferencia_finalizada_em, $demanda->conferencia_iniciada_em, 'Conferência finalizada.', $this->responsavelLog($logs, 'conferencia', 'finalizar_agora')),
            $this->timelineItem('Início do carregamento', $demanda->carregamento_iniciado_em, null, 'Carregamento iniciado.', $this->responsavelLog($logs, 'carregamento', 'iniciar_agora')),
            $this->timelineItem('Fim do carregamento', $demanda->carregamento_finalizado_em, $demanda->carregamento_iniciado_em, 'Carregamento finalizado.', $this->responsavelLog($logs, 'carregamento', 'finalizar_agora')),
            $this->timelineItem('Saída do veículo', $demanda->saida_veiculo_em, $demanda->carregamento_finalizado_em, 'Ciclo da DT fechado.', $this->responsavelSaida($demanda)),
        ];
    }

    private function timelineItem(string $titulo, $data, $inicio, string $descricao, ?string $responsavel): array
    {
        $data = $this->dataValida($data);
        $inicio = $this->dataValida($inicio);

        return [
            'titulo' => $titulo,
            'data' => $data,
            'descricao' => $descricao,
            'responsavel' => $responsavel ?: '-',
            'duracao' => ($data && $inicio) ? $this->formatarDuracao((int) $inicio->diffInMinutes($data)) : null,
            'concluida' => (bool) $data,
        ];
    }

    private function dataValida($data): ?Carbon
    {
        if (! $data) {
            return null;
        }

        $data = Carbon::parse($data);

        return $data->gte(Demanda::DATA_OPERACIONAL_MINIMA) ? $data : null;
    }

    private function responsavelSeparacao(Demanda $demanda): ?string
    {
        return $demanda->distribuicoes
            ->pluck('separador_nome')
            ->filter()
            ->unique()
            ->join(', ') ?: $demanda->separador?->nome;
    }

    private function responsavelSaida(Demanda $demanda): ?string
    {
        if (! $demanda->saida_veiculo_usuario_id) {
            return null;
        }

        return DB::table('_tb_usuarios')->where('id_user', $demanda->saida_veiculo_usuario_id)->value('nome');
    }

    private function responsavelLog($logs, string $etapa, string $acao): ?string
    {
        $log = $logs->first(function (SystemLog $log) use ($etapa, $acao) {
            return ($log->new_values['etapa'] ?? null) === $etapa
                && ($log->new_values['acao'] ?? null) === $acao;
        });

        return $log?->user_name;
    }

    private function formatarDuracao(int $minutos): string
    {
        return floor($minutos / 60) . 'h ' . str_pad((string) ($minutos % 60), 2, '0', STR_PAD_LEFT) . 'min';
    }
}
