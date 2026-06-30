<?php

namespace Tests\Feature;

use App\Models\Expedicao\ExpedicaoProgramacao;
use App\Models\Demanda;
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

    public function test_importacao_salva_codigo_cliente_para_cruzar_transit_time(): void
    {
        $resultado = $this->salvarLinha(
            'DT-PROG-CLIENTE-001',
            [
                'dataagendamento' => '2026-05-22',
                'horaagendamento' => '04:00',
                'cidadedestino' => 'SANTOS',
                'ufdestino' => 'SP',
                'codcliente' => '4.0',
                'desccliente' => 'Cliente Bombril',
            ],
            ExpedicaoProgramacao::TIPO_PROGRAMADA,
            ExpedicaoProgramacao::ORIGEM_PLANILHA_MANHA
        );

        $this->assertSame('criadas', $resultado);
        $this->assertDatabaseHas('_tb_expedicao_programacoes', [
            'fo' => 'DT-PROG-CLIENTE-001',
            'codigo_cliente' => '4',
            'cliente' => 'Cliente Bombril',
        ]);
    }

    public function test_importacao_salva_data_expedicao_da_programacao(): void
    {
        $resultado = $this->salvarLinha(
            'DT-PROG-DATA-EXPEDICAO-001',
            [
                'dataexpedicao' => '2026-06-19',
                'dataagendamento' => '2026-06-23',
                'horaagendamento' => '07:00',
                'cidadedestino' => 'SANTOS',
                'ufdestino' => 'SP',
                'cliente' => 'Cliente Bombril',
            ],
            ExpedicaoProgramacao::TIPO_PROGRAMADA,
            ExpedicaoProgramacao::ORIGEM_PLANILHA_MANHA
        );

        $programacao = ExpedicaoProgramacao::where('fo', 'DT-PROG-DATA-EXPEDICAO-001')->firstOrFail();

        $this->assertSame('criadas', $resultado);
        $this->assertSame('2026-06-19 00:00:00', $programacao->data_expedicao_em->format('Y-m-d H:i:s'));
        $this->assertSame('2026-06-23 07:00:00', $programacao->agenda_entrega_em->format('Y-m-d H:i:s'));
    }

    public function test_importacao_usa_coluna_cliente_como_codigo_quando_existe_desc_cliente(): void
    {
        $resultado = $this->salvarLinha(
            'DT-PROG-CLIENTE-002',
            [
                'dataagendamento' => '2026-05-22',
                'horaagendamento' => '04:00',
                'cidadedestino' => 'SANTOS',
                'ufdestino' => 'SP',
                'cliente' => '20305',
                'desccliente' => 'Cliente Bombril',
            ],
            ExpedicaoProgramacao::TIPO_PROGRAMADA,
            ExpedicaoProgramacao::ORIGEM_PLANILHA_MANHA
        );

        $this->assertSame('criadas', $resultado);
        $this->assertDatabaseHas('_tb_expedicao_programacoes', [
            'fo' => 'DT-PROG-CLIENTE-002',
            'codigo_cliente' => '20305',
            'cliente' => 'Cliente Bombril',
        ]);
    }

    public function test_importacao_programacao_nao_finaliza_operacao_com_saida_meia_noite(): void
    {
        Demanda::create([
            'fo' => 'DT-PROG-SEM-FINALIZACAO-001',
            'cliente' => 'Cliente Teste',
            'transportadora' => 'Transportadora Teste',
            'tipo' => 'EXPEDICAO',
            'status' => 'A_SEPARAR',
        ]);

        $resultado = $this->salvarLinha(
            'DT-PROG-SEM-FINALIZACAO-001',
            [
                'dataagendamento' => '2026-06-23',
                'horaagendamento' => '06:00',
                'cidadedestino' => 'CAMBE',
                'ufdestino' => 'PR',
                'cliente' => '85413',
                'desccliente' => 'IRMAOS MUFFATO S.A',
                'datasaida' => '2026-06-19',
                'saida' => '00:00',
                'datavalidacao' => '2026-06-19',
                'validacao' => '00:00',
            ],
            ExpedicaoProgramacao::TIPO_PROGRAMADA,
            ExpedicaoProgramacao::ORIGEM_PLANILHA_MANHA
        );

        $demanda = Demanda::where('fo', 'DT-PROG-SEM-FINALIZACAO-001')->firstOrFail();

        $this->assertSame('criadas', $resultado);
        $this->assertNull($demanda->conferencia_finalizada_em);
        $this->assertNull($demanda->carregamento_finalizado_em);
    }

    private function salvarLinha(string $fo, array $dados, string $tipoDemanda, string $origemDemanda): string
    {
        $service = app(ImportacaoProgramacaoExpedicaoService::class);
        $method = new ReflectionMethod($service, 'salvarLinha');
        $method->setAccessible(true);

        return $method->invoke($service, $fo, $dados, $tipoDemanda, $origemDemanda);
    }
}
