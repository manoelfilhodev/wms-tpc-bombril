<?php

namespace Tests\Feature;

use App\Models\Demanda;
use App\Models\DemandaItem;
use App\Models\Expedicao\ExpedicaoProgramacao;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DemandaRelatoriosExportTest extends TestCase
{
    public function test_pagina_de_relatorios_exibe_exportacoes_excel_e_pdf(): void
    {
        $this->actingAs($this->createUser())
            ->withSession(['tipo' => 'admin', 'nivel' => 'Admin']);

        $this->get(route('demandas.relatorios'))
            ->assertOk()
            ->assertSee('Exportações disponíveis')
            ->assertSee('Ciclo completo da DT')
            ->assertSee('Curva ABC de SKUs')
            ->assertSee('Excel')
            ->assertSee('PDF');
    }

    public function test_export_excel_de_ciclo_da_dt_retorna_arquivo(): void
    {
        $this->actingAs($this->createUser())
            ->withSession(['tipo' => 'admin', 'nivel' => 'Admin']);

        $demanda = Demanda::create([
            'fo' => 'DT-REL-EXPORT-001',
            'cliente' => 'Cliente Relatório',
            'transportadora' => 'Transportadora Relatório',
            'tipo' => 'EXPEDICAO',
            'status' => 'CARREGADO',
            'quantidade' => 10,
            'possui_sobra' => true,
            'separacao_iniciada_em' => '2026-05-20 08:00:00',
            'separacao_finalizada_em' => '2026-05-20 09:00:00',
            'separacao_resultado' => 'COMPLETA',
            'conferencia_iniciada_em' => '2026-05-20 09:10:00',
            'conferencia_finalizada_em' => '2026-05-20 09:30:00',
            'carregamento_iniciado_em' => '2026-05-20 09:40:00',
            'carregamento_finalizado_em' => '2026-05-20 10:00:00',
            'created_at' => '2026-05-20 07:00:00',
        ]);

        DemandaItem::create([
            'demanda_id' => $demanda->id,
            'sku' => 'SKU-001',
            'sku_normalizado' => 'SKU001',
            'descricao' => 'Item teste',
            'unidade_medida' => 'CX',
            'sobra' => 10,
            'bloqueado' => false,
        ]);

        DB::table('_tb_demanda_distribuicoes')->insert([
            'demanda_id' => $demanda->id,
            'separador_nome' => 'Operador Relatório',
            'quantidade_pecas' => 10,
            'quantidade_skus' => 1,
            'finalizado_em' => '2026-05-20 09:00:00',
            'resultado' => 'COMPLETA',
            'created_at' => '2026-05-20 08:00:00',
            'updated_at' => '2026-05-20 09:00:00',
        ]);

        ExpedicaoProgramacao::create([
            'fo' => $demanda->fo,
            'dt_sap' => $demanda->fo,
            'agenda_entrega_em' => '2026-05-20 12:00:00',
            'cidade_destino' => 'SAO PAULO',
            'uf_destino' => 'SP',
            'tipo_demanda' => ExpedicaoProgramacao::TIPO_PROGRAMADA,
            'origem_demanda' => ExpedicaoProgramacao::ORIGEM_PLANILHA_MANHA,
            'status_previsao' => 'AGUARDANDO_EXPLOSAO',
        ]);

        $response = $this->get(route('demandas.relatorios.export', [
            'tipo' => 'ciclo-expedicao',
            'formato' => 'excel',
            'data_inicio' => '2026-05-20',
            'data_fim' => '2026-05-20',
            'tipo_demanda' => 'PROGRAMADA',
        ]));

        $response->assertOk();
        $this->assertStringContainsString(
            'ciclo-expedicao_',
            $response->headers->get('content-disposition', '')
        );
    }

    public function test_export_pdf_de_curva_abc_retorna_arquivo(): void
    {
        $this->actingAs($this->createUser())
            ->withSession(['tipo' => 'admin', 'nivel' => 'Admin']);

        $demanda = Demanda::create([
            'fo' => 'DT-REL-PDF-001',
            'cliente' => 'Cliente PDF',
            'tipo' => 'EXPEDICAO',
            'status' => 'A_SEPARAR',
            'quantidade' => 3,
            'possui_sobra' => true,
            'created_at' => '2026-05-20 07:00:00',
        ]);

        DemandaItem::create([
            'demanda_id' => $demanda->id,
            'sku' => 'SKU-ABC',
            'sku_normalizado' => 'SKUABC',
            'descricao' => 'Item ABC',
            'unidade_medida' => 'CX',
            'sobra' => 3,
            'bloqueado' => false,
        ]);

        $response = $this->get(route('demandas.relatorios.export', [
            'tipo' => 'curva-abc-skus',
            'formato' => 'pdf',
            'data_inicio' => '2026-05-20',
            'data_fim' => '2026-05-20',
        ]));

        $response->assertOk();
        $this->assertStringContainsString(
            'curva-abc-skus_',
            $response->headers->get('content-disposition', '')
        );
    }

    private function createUser(): User
    {
        $unidadeId = DB::table('_tb_unidades')->insertGetId([
            'nome' => 'Unidade Teste Relatórios',
            'status' => 'ativo',
            'created_at' => now(),
        ]);

        return User::create([
            'nome' => 'Usuario Teste Relatórios',
            'email' => 'relatorios.' . uniqid() . '@example.com',
            'password' => Hash::make('Secret123!'),
            'unidade_id' => $unidadeId,
            'tipo' => 'admin',
            'status' => 'ativo',
            'nivel' => 'Admin',
        ]);
    }
}
