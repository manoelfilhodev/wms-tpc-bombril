<?php

namespace Tests\Feature;

use App\Http\Controllers\Expedicao\PrevisibilidadeExpedicaoController;
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

    public function test_calculo_de_previsao_usa_tempo_operacional_de_caminhao_da_rota(): void
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

        $this->assertSame('CALCULADO', $previsao->status);
        $this->assertSame(60, $previsao->tempo_viagem_min);
        $this->assertDatabaseHas('_tb_expedicao_rotas', [
            'id' => $rota->id,
            'tempo_api_minutos' => 25,
            'tempo_operacional_minutos' => 60,
        ]);
    }

    public function test_painel_recalcula_previsao_antiga_com_tempo_de_carro(): void
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
