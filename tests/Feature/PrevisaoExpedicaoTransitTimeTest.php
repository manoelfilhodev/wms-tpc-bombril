<?php

namespace Tests\Feature;

use App\Http\Controllers\Expedicao\PrevisibilidadeExpedicaoController;
use App\Models\ClienteTransitTime;
use App\Models\Demanda;
use App\Models\DemandaItem;
use App\Models\Expedicao\ExpedicaoCriterio;
use App\Models\Expedicao\ExpedicaoPrevisao;
use App\Models\Expedicao\ExpedicaoProgramacao;
use App\Models\Expedicao\ExpedicaoRota;
use App\Services\Expedicao\PrevisaoExpedicaoService;
use ReflectionMethod;
use Tests\TestCase;

class PrevisaoExpedicaoTransitTimeTest extends TestCase
{
    public function test_ajusta_tempo_de_api_para_operacao_de_caminhao(): void
    {
        $this->configurarTransitTimeCaminhao();

        $this->assertSame(
            60,
            PrevisaoExpedicaoService::ajustarTempoViagemCaminhao(25, 20)
        );

        $this->assertSame(
            100,
            PrevisaoExpedicaoService::ajustarTempoViagemCaminhao(50, 20)
        );
    }

    public function test_calculo_de_previsao_nao_usa_rota_quando_nao_ha_transit_time(): void
    {
        $this->configurarTransitTimeCaminhao();
        $this->criarCriteriosOperacionais();

        $programacao = ExpedicaoProgramacao::create([
            'fo' => 'DT-TRANSIT-001',
            'dt_sap' => 'DT-TRANSIT-001',
            'agenda_entrega_em' => now()->addDay()->setTime(8, 0),
            'cidade_destino' => 'SAO PAULO',
            'uf_destino' => 'SP',
            'cliente' => 'Cliente Centro SP',
            'transportadora' => 'Transportadora Teste',
            'tipo_carga' => 'PALETIZADA',
            'possui_picking' => false,
            'tipo_demanda' => ExpedicaoProgramacao::TIPO_PROGRAMADA,
            'origem_demanda' => ExpedicaoProgramacao::ORIGEM_PLANILHA_MANHA,
            'status_previsao' => 'PRONTA_PARA_PREVISAO',
        ]);

        $demanda = Demanda::create([
            'fo' => 'DT-TRANSIT-001',
            'cliente' => 'Cliente Centro SP',
            'transportadora' => 'Transportadora Teste',
            'tipo' => 'EXPEDICAO',
            'status' => 'A_SEPARAR',
        ]);

        DemandaItem::create([
            'demanda_id' => $demanda->id,
            'sku' => 'SKU-TRANSIT-001',
            'sku_normalizado' => '1',
            'descricao' => 'Item teste',
            'unidade_medida' => 'CX',
            'sobra' => 1,
            'bloqueado' => false,
        ]);

        $rota = ExpedicaoRota::create([
            'cidade_origem' => 'Sao Bernardo do Campo',
            'uf_origem' => 'SP',
            'cidade_destino' => 'Sao Paulo',
            'uf_destino' => 'SP',
            'distancia_km' => 20,
            'tempo_api_minutos' => 25,
            'tempo_operacional_minutos' => null,
            'ultima_consulta_em' => now(),
            'ativo' => true,
        ]);

        $previsao = app(PrevisaoExpedicaoService::class)->calcular($programacao->id);

        $this->assertSame('ERRO', $previsao->status);
        $this->assertSame('Transit time não encontrado para cálculo da saída prevista.', $previsao->observacoes);
        $this->assertNull($previsao->tempo_viagem_min);
        $this->assertDatabaseHas('_tb_expedicao_rotas', [
            'id' => $rota->id,
            'tempo_api_minutos' => 25,
            'tempo_operacional_minutos' => null,
        ]);
    }

    public function test_painel_recalcula_previsao_antiga_com_tempo_de_rota(): void
    {
        $this->configurarTransitTimeCaminhao();

        $programacao = ExpedicaoProgramacao::create([
            'fo' => 'DT-TRANSIT-002',
            'dt_sap' => 'DT-TRANSIT-002',
            'agenda_entrega_em' => now()->addDay()->setTime(8, 0),
            'cidade_destino' => 'SAO PAULO',
            'uf_destino' => 'SP',
            'cliente' => 'Cliente Centro SP',
            'transportadora' => 'Transportadora Teste',
            'tipo_carga' => 'PALETIZADA',
            'possui_picking' => false,
            'tipo_demanda' => ExpedicaoProgramacao::TIPO_PROGRAMADA,
            'origem_demanda' => ExpedicaoProgramacao::ORIGEM_PLANILHA_MANHA,
            'status_previsao' => 'PREVISAO_GERADA',
        ]);

        ExpedicaoPrevisao::create([
            'programacao_id' => $programacao->id,
            'fo' => $programacao->fo,
            'tempo_separacao_min' => 10,
            'tempo_conferencia_min' => 10,
            'tempo_carregamento_min' => 10,
            'tempo_viagem_min' => 25,
            'tempo_total_min' => 55,
            'risco_operacional' => 'BAIXO',
            'status' => 'CALCULADO',
        ]);

        ExpedicaoRota::create([
            'cidade_origem' => 'São Bernardo do Campo',
            'uf_origem' => 'SP',
            'cidade_destino' => 'Sao Paulo',
            'uf_destino' => 'SP',
            'distancia_km' => 20,
            'tempo_api_minutos' => 25,
            'tempo_operacional_minutos' => null,
            'ultima_consulta_em' => now(),
            'ativo' => true,
        ]);

        $programacao->load('ultimaPrevisao');

        $method = new ReflectionMethod(PrevisibilidadeExpedicaoController::class, 'previsaoPrecisaRecalculo');
        $method->setAccessible(true);

        $this->assertTrue($method->invoke(new PrevisibilidadeExpedicaoController(), $programacao));
    }

    public function test_calculo_de_previsao_prioriza_transit_time_por_codigo_cliente(): void
    {
        $this->criarCriteriosOperacionais();

        ClienteTransitTime::create([
            'codigo_cliente' => '4',
            'zona_partida' => 'SP0541',
            'cidade' => 'SAO PAULO',
            'uf' => 'SP',
            'zona_transporte' => 'SP0559',
            'ciclo_inte' => 3,
            'transit_time_fechada_dias' => 1,
            'transit_time_fracionada_dias' => 2,
            'ativo' => true,
        ]);

        $agenda = now()->addDays(3)->setTime(8, 0);
        $programacao = ExpedicaoProgramacao::create([
            'fo' => 'DT-TRANSIT-CLIENTE-001',
            'dt_sap' => 'DT-TRANSIT-CLIENTE-001',
            'agenda_entrega_em' => $agenda,
            'cidade_destino' => 'SAO PAULO',
            'uf_destino' => 'SP',
            'codigo_cliente' => '4',
            'cliente' => 'Cliente Centro SP',
            'transportadora' => 'Transportadora Teste',
            'tipo_carga' => 'PALETIZADA',
            'possui_picking' => false,
            'tipo_demanda' => ExpedicaoProgramacao::TIPO_PROGRAMADA,
            'origem_demanda' => ExpedicaoProgramacao::ORIGEM_PLANILHA_MANHA,
            'status_previsao' => 'PRONTA_PARA_PREVISAO',
        ]);

        $demanda = Demanda::create([
            'fo' => 'DT-TRANSIT-CLIENTE-001',
            'cliente' => 'Cliente Centro SP',
            'transportadora' => 'Transportadora Teste',
            'tipo' => 'EXPEDICAO',
            'status' => 'A_SEPARAR',
        ]);

        DemandaItem::create([
            'demanda_id' => $demanda->id,
            'sku' => 'SKU-TRANSIT-CLIENTE-001',
            'sku_normalizado' => '1',
            'descricao' => 'Item teste',
            'unidade_medida' => 'CX',
            'sobra' => 1,
            'bloqueado' => false,
        ]);

        $previsao = app(PrevisaoExpedicaoService::class)->calcular($programacao->id);

        $this->assertSame('CALCULADO', $previsao->status);
        $this->assertSame(1440, $previsao->tempo_viagem_min);
        $this->assertSame(
            $agenda->copy()->subDay()->format('Y-m-d H:i:s'),
            $previsao->previsao_saida_caminhao->format('Y-m-d H:i:s')
        );
    }

    public function test_calculo_de_previsao_usa_transit_time_por_destino_quando_codigo_cliente_nao_veio(): void
    {
        $this->criarCriteriosOperacionais();

        ClienteTransitTime::create([
            'codigo_cliente' => '20305',
            'zona_partida' => 'SP0541',
            'cidade' => 'GOIANIA',
            'uf' => 'GO',
            'zona_transporte' => 'GO0301',
            'ciclo_inte' => 3,
            'transit_time_fechada_dias' => 3,
            'transit_time_fracionada_dias' => 6,
            'ativo' => true,
        ]);

        $programacao = ExpedicaoProgramacao::create([
            'fo' => 'DT-TRANSIT-DESTINO-001',
            'dt_sap' => 'DT-TRANSIT-DESTINO-001',
            'agenda_entrega_em' => now()->addDays(5)->setTime(9, 0),
            'cidade_destino' => 'Goiania',
            'uf_destino' => 'GO',
            'cliente' => 'Cliente sem codigo',
            'transportadora' => 'Transportadora Teste',
            'tipo_carga' => 'PALETIZADA',
            'possui_picking' => false,
            'tipo_demanda' => ExpedicaoProgramacao::TIPO_PROGRAMADA,
            'origem_demanda' => ExpedicaoProgramacao::ORIGEM_PLANILHA_MANHA,
            'status_previsao' => 'PRONTA_PARA_PREVISAO',
        ]);

        $demanda = Demanda::create([
            'fo' => 'DT-TRANSIT-DESTINO-001',
            'cliente' => 'Cliente sem codigo',
            'transportadora' => 'Transportadora Teste',
            'tipo' => 'EXPEDICAO',
            'status' => 'A_SEPARAR',
        ]);

        DemandaItem::create([
            'demanda_id' => $demanda->id,
            'sku' => 'SKU-TRANSIT-DESTINO-001',
            'sku_normalizado' => '1',
            'descricao' => 'Item teste',
            'unidade_medida' => 'CX',
            'sobra' => 1,
            'bloqueado' => false,
        ]);

        $previsao = app(PrevisaoExpedicaoService::class)->calcular($programacao->id);

        $this->assertSame('CALCULADO', $previsao->status);
        $this->assertSame(4320, $previsao->tempo_viagem_min);
    }

    public function test_calculo_de_previsao_usa_maior_transit_time_da_uf_para_diversos(): void
    {
        $this->criarCriteriosOperacionais();

        ClienteTransitTime::create([
            'codigo_cliente' => '100',
            'cidade' => 'CURITIBA',
            'uf' => 'PR',
            'transit_time_fechada_dias' => 2,
            'transit_time_fracionada_dias' => 4,
            'ativo' => true,
        ]);

        ClienteTransitTime::create([
            'codigo_cliente' => '200',
            'cidade' => 'LONDRINA',
            'uf' => 'PR',
            'transit_time_fechada_dias' => 5,
            'transit_time_fracionada_dias' => 8,
            'ativo' => true,
        ]);

        ClienteTransitTime::create([
            'codigo_cliente' => '300',
            'cidade' => 'JOINVILLE',
            'uf' => 'SC',
            'transit_time_fechada_dias' => 9,
            'transit_time_fracionada_dias' => 10,
            'ativo' => true,
        ]);

        $programacao = ExpedicaoProgramacao::create([
            'fo' => 'DT-TRANSIT-DIVERSOS-001',
            'dt_sap' => 'DT-TRANSIT-DIVERSOS-001',
            'agenda_entrega_em' => now()->addDays(10)->setTime(9, 0),
            'cidade_destino' => 'Diversos',
            'uf_destino' => 'PR',
            'codigo_cliente' => 'DIV',
            'cliente' => 'Diversos',
            'transportadora' => 'Transportadora Teste',
            'tipo_carga' => 'PALETIZADA',
            'possui_picking' => false,
            'tipo_demanda' => ExpedicaoProgramacao::TIPO_PROGRAMADA,
            'origem_demanda' => ExpedicaoProgramacao::ORIGEM_PLANILHA_MANHA,
            'status_previsao' => 'PRONTA_PARA_PREVISAO',
        ]);

        $previsao = app(PrevisaoExpedicaoService::class)->calcular($programacao->id);

        $this->assertSame('CALCULADO', $previsao->status);
        $this->assertSame(7200, $previsao->tempo_viagem_min);
    }

    public function test_calculo_de_previsao_usa_tempos_padrao_quando_criterios_nao_estao_cadastrados(): void
    {
        ClienteTransitTime::create([
            'codigo_cliente' => '20305',
            'cidade' => 'GOIANIA',
            'uf' => 'GO',
            'transit_time_fechada_dias' => 3,
            'transit_time_fracionada_dias' => 6,
            'ativo' => true,
        ]);

        $programacao = ExpedicaoProgramacao::create([
            'fo' => 'DT-TRANSIT-SEM-CRITERIO-001',
            'dt_sap' => 'DT-TRANSIT-SEM-CRITERIO-001',
            'agenda_entrega_em' => now()->addDays(5)->setTime(9, 0),
            'cidade_destino' => 'GOIANIA',
            'uf_destino' => 'GO',
            'cliente' => 'Cliente sem criterio',
            'transportadora' => 'Transportadora Teste',
            'tipo_carga' => 'PALETIZADA',
            'possui_picking' => false,
            'tipo_demanda' => ExpedicaoProgramacao::TIPO_PROGRAMADA,
            'origem_demanda' => ExpedicaoProgramacao::ORIGEM_PLANILHA_MANHA,
            'status_previsao' => 'PRONTA_PARA_PREVISAO',
        ]);

        $demanda = Demanda::create([
            'fo' => 'DT-TRANSIT-SEM-CRITERIO-001',
            'cliente' => 'Cliente sem criterio',
            'transportadora' => 'Transportadora Teste',
            'tipo' => 'EXPEDICAO',
            'status' => 'A_SEPARAR',
        ]);

        DemandaItem::create([
            'demanda_id' => $demanda->id,
            'sku' => 'SKU-SEM-CRITERIO-001',
            'sku_normalizado' => '1',
            'descricao' => 'Item teste',
            'unidade_medida' => 'CX',
            'sobra' => 1,
            'bloqueado' => false,
        ]);

        $previsao = app(PrevisaoExpedicaoService::class)->calcular($programacao->id);

        $this->assertSame('CALCULADO', $previsao->status);
        $this->assertSame(90, $previsao->tempo_separacao_min);
        $this->assertSame(60, $previsao->tempo_conferencia_min);
        $this->assertSame(120, $previsao->tempo_carregamento_min);
        $this->assertSame(4320, $previsao->tempo_viagem_min);
    }

    public function test_calculo_de_previsao_planejada_nao_exige_demanda_explodida(): void
    {
        ClienteTransitTime::create([
            'codigo_cliente' => '21108',
            'cidade' => 'CHAPECO',
            'uf' => 'SC',
            'transit_time_fechada_dias' => 2,
            'transit_time_fracionada_dias' => 7,
            'ativo' => true,
        ]);

        $agenda = now()->addDays(4)->setTime(7, 0);
        $programacao = ExpedicaoProgramacao::create([
            'fo' => '251312531',
            'dt_sap' => '251312531',
            'agenda_entrega_em' => $agenda,
            'cidade_destino' => 'CHAPECO',
            'uf_destino' => 'SC',
            'codigo_cliente' => '21108',
            'cliente' => 'TOZZO ALIMENTOS LTDA',
            'transportadora' => 'Transportadora Teste',
            'tipo_carga' => 'PALETIZADA',
            'possui_picking' => false,
            'tipo_demanda' => ExpedicaoProgramacao::TIPO_PROGRAMADA,
            'origem_demanda' => ExpedicaoProgramacao::ORIGEM_PLANILHA_MANHA,
            'status_previsao' => 'PRONTA_PARA_PREVISAO',
        ]);

        $previsao = app(PrevisaoExpedicaoService::class)->calcular($programacao->id);

        $this->assertSame('CALCULADO', $previsao->status);
        $this->assertSame(2880, $previsao->tempo_viagem_min);
        $this->assertSame(
            $agenda->copy()->subDays(2)->format('Y-m-d H:i:s'),
            $previsao->previsao_saida_caminhao->format('Y-m-d H:i:s')
        );
        $this->assertSame('Previsão planejada sem explosão/demanda vinculada.', $previsao->observacoes);
    }

    private function configurarTransitTimeCaminhao(): void
    {
        config([
            'services.expedicao_rotas.truck_time_multiplier' => 1.6,
            'services.expedicao_rotas.truck_fixed_buffer_minutes' => 20,
            'services.expedicao_rotas.truck_min_minutes' => 60,
            'services.expedicao_rotas.truck_average_speed_kmh' => 45,
        ]);
    }

    private function criarCriteriosOperacionais(): void
    {
        foreach (['SEPARACAO', 'CONFERENCIA', 'CARREGAMENTO'] as $categoria) {
            ExpedicaoCriterio::create([
                'categoria' => $categoria,
                'nome' => "Criterio {$categoria}",
                'sku_min' => 0,
                'sku_max' => 999,
                'tipo_carga' => 'PALETIZADA',
                'possui_picking' => false,
                'tempo_previsto_minutos' => 10,
                'multiplicador' => 1,
                'ativo' => true,
            ]);
        }
    }
}
