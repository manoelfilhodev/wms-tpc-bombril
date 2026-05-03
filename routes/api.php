<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Setores\RecebimentoController;
use App\Http\Controllers\Api\RecebimentoApiController;
use App\Http\Controllers\Api\ConferenciaApiController;
use App\Http\Controllers\Auth\MicrosoftApiController;
use App\Http\Controllers\KitMontagemController;
use App\Http\Controllers\Api\DemandaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Setores\ArmazenagemController;
use App\Http\Controllers\ContagemLivreController;
use App\Http\Controllers\Api\V1\SaldoEstoqueController;
use App\Http\Controllers\Api\V1\MeController;
use App\Http\Controllers\Api\V1\ApontamentoPaleteStretchApiController;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::get('/', function () {
        return response()->json([
            'success' => true,
            'message' => 'API v1',
            'data' => (object) [],
            'meta' => (object) [],
        ]);
    });

    Route::get('/me', MeController::class);

    Route::get('/saldo-estoque', [SaldoEstoqueController::class, 'index']);
    Route::get('/saldo-estoque/{id}', [SaldoEstoqueController::class, 'show'])->whereNumber('id');
    Route::match(['put', 'patch'], '/saldo-estoque/{id}', [SaldoEstoqueController::class, 'update'])->whereNumber('id');

    Route::post('/apontamentos-paletes-stretch', [ApontamentoPaleteStretchApiController::class, 'store']);
});

Route::post('/v1/auth/login', [AuthController::class, 'apiLogin']);

Route::post('/contagem-livre', [ContagemLivreController::class, 'store'])->middleware('auth:sanctum');

Route::prefix('armazenagem')->group(function () {
    Route::get('/buscarDescricaoApi', [ArmazenagemController::class, 'buscarDescricaoApi']);
    Route::get('/buscarPosicoes', [ArmazenagemController::class, 'buscarPosicoes']);
    Route::post('/store', [ArmazenagemController::class, 'store'])->middleware('auth:sanctum');
    Route::post('/store-api', [ArmazenagemController::class, 'storeApi'])->middleware('auth:sanctum');
});



Route::prefix('contagem-livre')->group(function () {
    // Buscar SKU pelo EAN (ex: GET /api/contagem-livre/buscarDescricaoApi?ean=123...)
    Route::get('/buscarDescricaoApi', [ContagemLivreController::class, 'buscarDescricaoApi']);

    // Salvar contagem livre (POST /api/contagem-livre/store)
    Route::post('/store', [ContagemLivreController::class, 'store'])->middleware('auth:sanctum');
});

Route::get('/apontamentos/hoje', function () {
    $hoje = Carbon::today();

    // Meta = total de etiquetas/paletes gerados no dia
    $meta = DB::table('_tb_apontamentos_kits')
        ->whereDate('data', $hoje)
        ->count();

    // Buscar apontamentos de hoje
    $apontamentos = DB::table('_tb_apontamentos_kits')
        ->whereDate('data', $hoje)
        ->orderBy('updated_at')
        ->get(['updated_at']);

    // Montar acumulado
    $acumulado = [];
    $count = 0;
    foreach ($apontamentos as $a) {
        $count++;
        $acumulado[] = [
            'hora' => Carbon::parse($a->updated_at)->format('H:i'),
            'acumulado' => $count,
        ];
    }

    return response()->json([
        'meta' => $meta,
        'produzido' => $count,
        'apontamentos' => $acumulado,
    ]);
});

Route::get('/apontamentos/ultimos', [KitMontagemController::class, 'apiUltimosApontamentos']);
Route::post('/apontamento', [KitMontagemController::class, 'apiApontarPorEtiqueta'])->middleware('auth:sanctum');

Route::post('/login-microsoft', [MicrosoftApiController::class, 'loginFromApp']);
Route::post('/auth/microsoft', [MicrosoftApiController::class, 'loginFromApp']);


// Login API
Route::post('/login', [AuthController::class, 'apiLogin']);

// Rotas protegidas por Sanctum
Route::middleware('auth:sanctum')->group(function () {

    // Rotas de Recebimento (API)
    Route::get('/recebimentos', [RecebimentoApiController::class, 'listar']);
    Route::get('/recebimentos/{id}', [RecebimentoApiController::class, 'detalhes']);
    Route::post('/recebimentos/{id}/foto-inicio', [RecebimentoApiController::class, 'uploadFotoInicio']);

    Route::post('/recebimentos/{id}/foto-fim', [RecebimentoApiController::class, 'uploadFotoFim']);

    Route::post('/recebimentos/{id}/assinatura-fim', [RecebimentoApiController::class, 'uploadAssinaturaFim']);

    Route::post('/recebimentos/{id}/finalizar', [RecebimentoApiController::class, 'finalizarConferencia']);




    // Rotas de Conferencia (API)
    Route::get('/recebimentos/{id}/itens', [ConferenciaApiController::class, 'listarItens']);
    Route::get('/conferencia/item/{id}', [ConferenciaApiController::class, 'detalheItem']);
    Route::post('/conferencia/item/{id}', [ConferenciaApiController::class, 'salvarItem']);
    Route::post('/recebimentos/{id}/fechar', [ConferenciaApiController::class, 'fecharConferencia']);
});

// Rotas para uso no painel web (nao precisam do Sanctum)
Route::get('/painel/recebimentos', [RecebimentoController::class, 'listar']);


// Rotas sem autenticacao
Route::get('/demandas', [DemandaController::class, 'index']);
Route::get('/demandas/{id}', [DemandaController::class, 'show']);
Route::get('/demandas/{id}/historico', [DemandaController::class, 'historico']);

Route::put('/demandas/{id}', [DemandaController::class, 'update'])->middleware('auth:sanctum');
Route::post('/demandas/{id}/status', [DemandaController::class, 'atualizarStatus'])->middleware('auth:sanctum');
