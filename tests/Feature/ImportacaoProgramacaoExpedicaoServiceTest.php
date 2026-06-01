<?php

namespace Tests\Feature;

use App\Models\Expedicao\ExpedicaoProgramacao;
use App\Services\Expedicao\ImportacaoProgramacaoExpedicaoService;
use ReflectionMethod;
use Tests\TestCase;

class ImportacaoProgramacaoExpedicaoServiceTest extends TestCase
{
    public function test_importacao_de_oportunidade_nao_reclassifica_fo_ja_programada(): void
    {
        $programacao = ExpedicaoProgramacao::create([
            'fo' => 'DT-PROG-OPP-001',
            'dt_sap' => 'DT-PROG-OPP-001',
            'agenda_entrega_em' => '2026-05-21 02:00:00',
            'cidade_destino' => 'ARUJA',
            'uf_destino' => 'SP',
            'cliente' => 'Cliente Programado',
            'transportadora' => 'Transportadora Programada',
            'tipo_demanda' => ExpedicaoProgramacao::TIPO_PROGRAMADA,
            'origem_demanda' => ExpedicaoProgramacao::ORIGEM_PLANILHA_MANHA,
            'status_previsao' => 'AGUARDANDO_EXPLOSAO',
        ]);

        $resultado = $this->salvarLinha(
            'DT-PROG-OPP-001',
            [
                'dataagendamento' => '2026-05-22',
                'horaagendamento' => '04:00',
                'cidadedestino' => 'SANTOS',
                'ufdestino' => 'SP',
                'cliente' => 'Cliente Oportunidade',
            ],
            ExpedicaoProgramacao::TIPO_OPORTUNIDADE,
            ExpedicaoProgramacao::ORIGEM_IMPORTACAO_OPORTUNIDADE
        );

        $programacao->refresh();

        $this->assertSame('bloqueadas_programadas', $resultado);
        $this->assertSame(ExpedicaoProgramacao::TIPO_PROGRAMADA, $programacao->tipo_demanda);
        $this->assertSame(ExpedicaoProgramacao::ORIGEM_PLANILHA_MANHA, $programacao->origem_demanda);
        $this->assertSame('2026-05-21 02:00:00', $programacao->agenda_entrega_em->format('Y-m-d H:i:s'));
        $this->assertSame('ARUJA', $programacao->cidade_destino);
        $this->assertSame('Cliente Programado', $programacao->cliente);
    }

    public function test_importacao_de_oportunidade_cria_fo_nova_como_oportunidade(): void
    {
        $resultado = $this->salvarLinha(
            'DT-OPP-NOVA-001',
            [
                'dataagendamento' => '2026-05-22',
                'horaagendamento' => '04:00',
                'cidadedestino' => 'SANTOS',
                'ufdestino' => 'SP',
                'cliente' => 'Cliente Oportunidade',
            ],
            ExpedicaoProgramacao::TIPO_OPORTUNIDADE,
            ExpedicaoProgramacao::ORIGEM_IMPORTACAO_OPORTUNIDADE
        );

        $this->assertSame('criadas', $resultado);
        $this->assertDatabaseHas('_tb_expedicao_programacoes', [
            'fo' => 'DT-OPP-NOVA-001',
            'tipo_demanda' => ExpedicaoProgramacao::TIPO_OPORTUNIDADE,
            'origem_demanda' => ExpedicaoProgramacao::ORIGEM_IMPORTACAO_OPORTUNIDADE,
        ]);
    }

    private function salvarLinha(string $fo, array $dados, string $tipoDemanda, string $origemDemanda): string
    {
        $service = app(ImportacaoProgramacaoExpedicaoService::class);
        $method = new ReflectionMethod($service, 'salvarLinha');
        $method->setAccessible(true);

        return $method->invoke($service, $fo, $dados, $tipoDemanda, $origemDemanda);
    }
}
