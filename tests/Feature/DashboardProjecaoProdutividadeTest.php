<?php

namespace Tests\Feature;

use App\Models\Demanda;
use App\Models\User;
use App\Services\DashboardService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DashboardProjecaoProdutividadeTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_projecao_de_produtividade_usa_caixas_separadas_apos_meio_dia(): void
    {
        Carbon::setTestNow('2026-05-06 15:00:00');

        $this->criarDemandaSeparada('DT-ANTES-DO-TURNO', 999, '2026-05-06 11:30:00');
        $this->criarDemandaSeparada('DT-1230', 500, '2026-05-06 12:30:00');
        $this->criarDemandaSeparada('DT-1345', 700, '2026-05-06 13:45:00');
        $this->criarDemandaSeparada('DT-1420', 800, '2026-05-06 14:20:00');
        Demanda::create($this->demandaData('DT-ABERTA', 1500));

        $dados = app(DashboardService::class)->getProjecaoProdutividade();

        $this->assertSame(11000, $dados['meta']);
        $this->assertSame(1000, $dados['metaPorHora']);
        $this->assertSame(2000, $dados['produzido']);
        $this->assertSame(666.67, $dados['velocidadeAtual']);
        $this->assertSame('baixo', $dados['statusProdutividade']);
        $this->assertNull($dados['previsaoConclusao']);

        $this->assertSame(['12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00', '19:00', '20:00', '21:00', '22:00', '23:00'], array_column($dados['curvaIdeal'], 'hora'));
        $this->assertSame([0, 1000, 2000, 3000, 4000, 5000, 6000, 7000, 8000, 9000, 10000, 11000], array_column($dados['curvaIdeal'], 'valor'));
        $this->assertSame([0, 500, 1200, 2000, null, null, null, null, null, null, null, null], array_column($dados['apontamentos'], 'acumulado'));
        $this->assertSame([null, null, null, null, 2667, 3333, 4000, 4667, 5333, 6000, 6667, 7333], array_column($dados['projecaoCorrigida'], 'valor'));
    }

    public function test_projecao_corrigida_mostra_meta_atingida_antes_das_vinte_e_tres(): void
    {
        Carbon::setTestNow('2026-05-06 14:00:00');

        $this->criarDemandaSeparada('DT-RITMO-ALTO-1', 2000, '2026-05-06 12:30:00');
        $this->criarDemandaSeparada('DT-RITMO-ALTO-2', 2000, '2026-05-06 13:30:00');

        $dados = app(DashboardService::class)->getProjecaoProdutividade();

        $this->assertSame(4000, $dados['produzido']);
        $this->assertSame(2000.0, $dados['velocidadeAtual']);
        $this->assertSame('17:30', $dados['previsaoConclusao']);
        $this->assertSame('ok', $dados['statusProdutividade']);
        $this->assertSame(11000, $dados['projecaoCorrigida'][6]['valor']);
        $this->assertSame('18:00', $dados['projecaoCorrigida'][6]['hora']);
    }

    public function test_projecao_respeita_data_operacional_selecionada(): void
    {
        Carbon::setTestNow('2026-05-06 14:00:00');

        $this->criarDemandaSeparada('DT-DATA-SELECIONADA', 1500, '2026-05-05 13:00:00');
        $this->criarDemandaSeparada('DT-DATA-ATUAL', 9000, '2026-05-06 13:00:00');

        $dados = app(DashboardService::class)->getProjecaoProdutividade('2026-05-05');

        $this->assertSame(1500, $dados['produzido']);
        $this->assertSame([0, 1500, 1500, 1500, 1500, 1500, 1500, 1500, 1500, 1500, 1500, 1500], array_column($dados['apontamentos'], 'acumulado'));
    }

    public function test_dashboard_operacional_ancora_grafico_acumulativo_na_data_selecionada(): void
    {
        Carbon::setTestNow('2026-05-06 14:00:00');

        $this->actingAs($this->createUser())
            ->withSession(['tipo' => 'admin', 'nivel' => 'Admin'])
            ->get(route('demandas.dashboardOperacional', ['data' => '2026-05-05']))
            ->assertOk()
            ->assertSee('29\/04', false)
            ->assertSee('05\/05', false)
            ->assertDontSee('06\/05', false);
    }

    private function criarDemandaSeparada(string $fo, int $quantidade, string $finalizadaEm): Demanda
    {
        return Demanda::create($this->demandaData($fo, $quantidade, [
            'status' => 'CONFERIDO',
            'separacao_finalizada_em' => $finalizadaEm,
            'separacao_resultado' => 'COMPLETA',
        ]));
    }

    private function demandaData(string $fo, int $quantidade, array $extra = []): array
    {
        return array_merge([
            'fo' => $fo,
            'cliente' => 'Cliente Dashboard',
            'transportadora' => 'Transportadora Dashboard',
            'tipo' => 'EXPEDICAO',
            'status' => 'A_SEPARAR',
            'quantidade' => $quantidade,
            'possui_sobra' => false,
        ], $extra);
    }

    private function createUser(): User
    {
        $unidadeId = DB::table('_tb_unidades')->insertGetId([
            'nome' => 'Unidade Teste Dashboard',
            'status' => 'ativo',
            'created_at' => now(),
        ]);

        return User::create([
            'nome' => 'Usuario Teste Dashboard',
            'email' => 'dashboard.' . uniqid() . '@example.com',
            'password' => Hash::make('Secret123!'),
            'unidade_id' => $unidadeId,
            'tipo' => 'admin',
            'status' => 'ativo',
            'nivel' => 'Admin',
        ]);
    }
}
