<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use App\Exports\LogsExport;
use App\Http\Controllers\Auth\MicrosoftController;
use Illuminate\Routing\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\KitProgramarController;


use App\Http\Controllers\{
    AuthController,
    UserController,
    DashboardController,
    DashboardTvController,
    LogController,
    ContagemItemController,
    EtiquetaController,
    RelatorioController,
    KitMontagemController,
    MultipackController,
    AutoCadastroController,
    ConviteController,
    SugestoesController,
    EquipamentoController,
    InventarioController,
    Mb52Controller,
    ContagemSkuController,
    InventarioCiclicoController,
    ContagemLivreController,
    ProdutoController,
    RecebimentoEtiquetaController,
    KitEtiquetaController,
    DemandaController,
    RelatorioKitController,
    ExpedicaoController,
    TransferenciaController,
    TransferenciaEtiquetaController
};

use App\Http\Controllers\Setores\{
    RecebimentoController,
    ArmazenagemController,
    SeparacaoController,
    ConferenciaController,
    PedidoController,
    SeparacaoItemController,
    ExecutarSeparacaoController,
    RecebimentoItemController
};


Route::prefix('transferencias')->group(function () {
    
    Route::get('/apontar', [TransferenciaController::class, 'apontarView'])
    ->name('transferencia.apontar');


    // Central
    Route::get('/', [TransferenciaController::class, 'index'])->name('transferencia.index');
    
    Route::get('/relatorio/excel', [TransferenciaController::class, 'exportarRelatorioExcel'])->name('transferencia.relatorio.excel');


    // Programação
    Route::get('/programar', [TransferenciaController::class, 'programar'])->name('transferencia.programar');
    Route::post('/programar', [TransferenciaController::class, 'storeProgramacao'])->name('transferencia.storeProgramacao');
    Route::get('/programar/editar', [TransferenciaController::class, 'editProgramacao'])->name('transferencia.editProgramacao');
    Route::put('/programar/{id}', [TransferenciaController::class, 'updateProgramacao'])->name('transferencia.updateProgramacao');
    Route::delete('/programar/{id}', [TransferenciaController::class, 'destroy'])->name('transferencia.destroy');

    // Apontamento
    Route::get('/apontar', [TransferenciaController::class, 'telaApontamento'])->name('transferencia.apontar');
    Route::post('/apontar', [TransferenciaController::class, 'apontar'])->name('transferencia.apontar.store');

    // Relatórios
    Route::get('/relatorio', [TransferenciaController::class, 'relatorio'])->name('transferencia.relatorio');
    Route::get('/relatorio/pdf', [TransferenciaController::class, 'exportarRelatorioPDF'])->name('transferencia.relatorio.pdf');
    Route::get('/relatorio/excel', [TransferenciaController::class, 'exportarRelatorioExcel'])->name('transferencia.relatorio.excel');

    // Pendências
    Route::get('/pendencias', [TransferenciaController::class, 'pendencias'])->name('transferencia.pendencias');

    // Ajax/autocomplete
    Route::get('/buscar-skus', [TransferenciaController::class, 'buscarSkus'])->name('transferencia.buscarSkus');
    Route::get('/buscar-descricao', [TransferenciaController::class, 'buscarDescricao'])->name('transferencia.buscarDescricao');
    
    // Etiquetas
Route::prefix('etiquetas')->group(function () {
    Route::get('/', [TransferenciaEtiquetaController::class, 'index'])->name('transferencia.etiquetas.index');
    Route::get('/form', [TransferenciaEtiquetaController::class, 'form'])->name('transferencia.etiquetas.form');
    Route::post('/gerar', [TransferenciaEtiquetaController::class, 'gerar'])->name('transferencia.etiquetas.gerar');
    Route::get('/{id}/visualizar', [TransferenciaEtiquetaController::class, 'visualizar'])->name('transferencia.etiquetas.visualizar');
    Route::get('/{id}/preview', [TransferenciaEtiquetaController::class, 'preview'])->name('transferencia.etiquetas.preview');
    Route::get('/imprimir-tudo', [TransferenciaEtiquetaController::class, 'imprimirTudo'])->name('transferencia.etiquetas.imprimirTudo');
});

Route::prefix('transferencias/etiquetas')->group(function () {
    Route::get('/', [TransferenciaEtiquetaController::class, 'index'])->name('transferencia.etiquetas.index');
    Route::get('/{id}/visualizar', [TransferenciaEtiquetaController::class, 'visualizar'])->name('transferencia.etiquetas.visualizar');
    Route::get('/{id}/imprimir-tudo', [TransferenciaEtiquetaController::class, 'imprimirTudo'])->name('transferencia.etiquetas.imprimirTudo');
    Route::get('/{id}/reimprimir', [TransferenciaEtiquetaController::class, 'reimprimir'])->name('transferencia.etiquetas.reimprimir');
});
});


Route::get('/expedicao/relatorio/pdf', [ExpedicaoController::class, 'exportarPdf'])
    ->name('expedicao.relatorio.pdf');

Route::middleware(['auth'])->group(function () {
    Route::get('/expedicao/dashboard', [ExpedicaoController::class, 'dashboard'])
        ->name('expedicao.dashboard');
});

Route::post('/itens/store-multiple', [ContagemItemController::class, 'storeMultiple'])->name('contagem.itens.storeMultiple');


Route::get('/relatorios/producao', [RelatorioKitController::class, 'gerarRelatorio'])
    ->name('relatorios.producao');


Route::get('/kits/pendencias', [App\Http\Controllers\KitMontagemController::class, 'pendencias'])->name('kit.pendencias');


Route::post('/demandas/import', [DemandaController::class, 'import'])->name('demandas.import');

Route::get('/demandas/import', function () {
    return view('demandas.import');
})->name('demandas.import.view');



Route::prefix('demandas')->group(function () {
    Route::get('/', [DemandaController::class, 'index'])->name('demandas.index');
    Route::get('/create', [DemandaController::class, 'create'])->name('demandas.create');
    Route::post('/store', [DemandaController::class, 'store'])->name('demandas.store');
});

Route::resource('demandas', DemandaController::class)->except(['show']);
Route::get('/demandas/export', [DemandaController::class, 'export'])->name('demandas.export');
Route::patch('/demandas/{id}/status', [DemandaController::class, 'updateStatus'])->name('demandas.updateStatus');
Route::patch('/demandas/update-multiple', [DemandaController::class, 'updateMultiple'])->name('demandas.updateMultiple');




// routes/web.php
Route::prefix('kits')->middleware('auth')->group(function () {
    Route::get('/apontar', [App\Http\Controllers\KitMontagemController::class, 'telaApontamento'])
        ->name('kit.apontar');   // esta é a que abre a tela
    Route::post('/apontar', [App\Http\Controllers\KitMontagemController::class, 'apontar'])
        ->name('kit.apontar.store');  // esta é só para salvar
});




Route::post('/kits/apontar', [App\Http\Controllers\KitMontagemController::class, 'apontar'])
    ->name('kits.apontar')
    ->middleware('auth');

Route::prefix('kits')->group(function () {
    // Programação
    Route::get('/', [KitProgramarController::class, 'index'])->name('kits.index');
    Route::get('/create', [KitProgramarController::class, 'create'])->name('kits.create');
    Route::post('/store', [KitProgramarController::class, 'store'])->name('kits.store');
    Route::get('/{id}/confirmar', [KitProgramarController::class, 'confirmar'])->name('kits.confirmar');

    // Etiquetas
    Route::get('/etiquetas', [KitEtiquetaController::class, 'index'])->name('kits.etiquetas.index');
    Route::get('/{id}/etiquetas', [KitEtiquetaController::class, 'visualizar'])->name('kits.etiquetas.visualizar');
    Route::post('/{id}/etiquetas/gerar', [KitEtiquetaController::class, 'gerar'])->name('kits.etiquetas.gerar');
    Route::get('/{id}/etiquetas/imprimir-tudo', [KitEtiquetaController::class, 'imprimirTudo'])->name('kits.etiquetas.imprimirTudo');
    Route::get('/etiquetas/{id}/reimprimir', [KitEtiquetaController::class, 'reimprimir'])->name('kits.etiquetas.reimprimir');
     Route::get('/etiquetas', [KitEtiquetaController::class, 'index'])
        ->name('kit.etiquetas');
});


Route::prefix('setores/recebimento')
    ->as('recebimento.')
    ->group(function () {
        Route::get('/imprimir/{recebimento_id}', [App\Http\Controllers\RecebimentoEtiquetaController::class, 'imprimirTudo'])
            ->name('imprimirTudo');

        Route::get('/reimprimir/{item_id}', [App\Http\Controllers\RecebimentoEtiquetaController::class, 'reimprimir'])
            ->name('reimprimir');
    });


Route::prefix('produtos')->middleware(['auth'])->group(function () {
    Route::get('/', [ProdutoController::class, 'index'])->name('produtos.index');
    Route::get('/create', [ProdutoController::class, 'create'])->name('produtos.create');
    Route::post('/', [ProdutoController::class, 'store'])->name('produtos.store');
    Route::get('/{id}/edit', [ProdutoController::class, 'edit'])->name('produtos.edit');
    Route::put('/{id}', [ProdutoController::class, 'update'])->name('produtos.update');
    Route::delete('/{id}', [ProdutoController::class, 'destroy'])->name('produtos.destroy');
});


Route::middleware(['auth'])->group(function () {
    Route::get('/contagem-lista', [ContagemLivreController::class, 'listar'])->name('contagem.livre.lista');
});


Route::middleware(['auth'])->group(function () {
    Route::get('/contagem-livre', [ContagemLivreController::class, 'form'])->name('contagem.livre.form');
    Route::post('/contagem-livre', [ContagemLivreController::class, 'salvar'])->name('contagem.livre.salvar');
});


Route::get('/estoque/fichas', [InventarioCiclicoController::class, 'formImportarFichas'])->name('inventario.fichas.form');

Route::get('/estoque/fichas', function () {
    return view('inventario.fichas_importar');
})->name('inventario.fichas.form');
Route::get('/estoque/fichas', [InventarioCiclicoController::class, 'formImportarFichas'])->name('inventario.fichas.form');


Route::post('/estoque/fichas/gerar', [App\Http\Controllers\InventarioCiclicoController::class, 'gerarFichasDiretas'])->name('inventario.fichas.gerar');
Route::get('/estoque/fichas/reimprimir/{cod}', [InventarioCiclicoController::class, 'reimprimirFichas'])->name('inventario.fichas.reimprimir');
Route::get('/estoque/fichas/historico', [InventarioCiclicoController::class, 'historicoFichas'])->name('inventario.fichas.historico');


Route::get('/inventario/contar/{inventarioId}/{itemId}', [ContagemSkuController::class, 'formContagem'])->name('contar_item.form');
Route::post('/inventario/contar/{inventarioId}/{itemId}', [ContagemSkuController::class, 'salvarContagem'])->name('contar_item.salvar');


Route::get('/inventario/contar/{idInventario}', [ContagemSkuController::class, 'listarItensInventario'])->name('contar.inventario');

Route::get('/posicoes', [InventarioCiclicoController::class, 'posicoes'])->name('inventario.posicoes');
Route::post('/posicoes', [InventarioCiclicoController::class, 'salvarPosicao'])->name('inventario.posicoes.salvar');

Route::get('/saldos', [InventarioCiclicoController::class, 'saldos'])->name('inventario.saldos');

Route::get('/inventario/exportar/excel/{id}', [InventarioCiclicoController::class, 'exportarExcel'])->name('inventario.exportar.excel');
Route::get('/inventario/exportar/pdf/{id}', [InventarioCiclicoController::class, 'exportarPdf'])->name('inventario.exportar.pdf');

Route::get('/inventario/resumo/{id}', [InventarioCiclicoController::class, 'resumo'])->name('inventario.resumo');
Route::post('/inventario/efetivar/{id}', [InventarioCiclicoController::class, 'efetivar'])->name('inventario.efetivar');


Route::get('/inventario/pular/{id_inventario}/{item}', [InventarioCiclicoController::class, 'pular'])->name('inventario.pular');


Route::get('/inventario/importar', [InventarioCiclicoController::class, 'importar'])->name('inventario.importar'); // FORMULÁRIO
Route::post('/inventario/gerar', [InventarioCiclicoController::class, 'gerarInventario'])->name('inventario.gerar'); // PROCESSA LISTA

Route::post('/inventario/gerar', [InventarioCiclicoController::class, 'gerarInventario'])->name('inventario.gerar');


Route::get('/inventario/requisicoes', [InventarioCiclicoController::class, 'listarRequisicoes'])->name('inventario.requisicoes');

Route::get('/inventario/iniciar/{id}', [InventarioCiclicoController::class, 'iniciarContagem'])->name('inventario.iniciar');

Route::get('/inventario/contar/{id_inventario}/{item}', [InventarioCiclicoController::class, 'contar'])->name('inventario.contar');
Route::post('/inventario/contar/{id_inventario}/{item}', [InventarioCiclicoController::class, 'salvarContagem'])->name('inventario.contar.salvar');
Route::get('/inventario/validacao/{id_inventario}', [InventarioCiclicoController::class, 'validacao'])->name('inventario.validacao');

Route::get('/contagem/importar', [ContagemSkuController::class, 'formulario'])->name('contagem.formulario');
Route::post('/contagem/salvar', [ContagemSkuController::class, 'salvar'])->name('contagem.salvar');
Route::get('/contagem/lista/{id_lista}', [ContagemSkuController::class, 'exibir'])->name('contagem.lista');
Route::post('/contagem/atualizar', [ContagemSkuController::class, 'salvarContagem'])->name('contagem.atualizar');

Route::post('/inventario/mb51/salvar-temp', [InventarioController::class, 'importarMB51SalvarTemporario'])->name('inventario.mb51.salvar_temp');

Route::get('/inventario/upload-mb51', [InventarioController::class, 'uploadForm'])->name('inventario.upload.mb51');



Route::middleware(['auth'])->group(function () {
    Route::get('/mb52/upload', [Mb52Controller::class, 'uploadForm'])->name('mb52.upload');
    Route::post('/mb52/importar', [Mb52Controller::class, 'importar'])->name('mb52.importar');
    Route::post('/mb52/excluir-hoje', [Mb52Controller::class, 'excluirHoje'])->name('mb52.excluir');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/inventario/upload', [InventarioController::class, 'uploadForm'])->name('inventario.upload');
    Route::post('/inventario/importar', [InventarioController::class, 'importarMB51'])->name('inventario.importar');
});

Route::put('/kit/programar/{id}', [KitMontagemController::class, 'updateProgramacao'])->name('kit.atualizar');
Route::get('/kit/programar/editar', [KitMontagemController::class, 'editProgramacao'])->name('kit.editar');
Route::delete('/kit/programar/{id}', [KitMontagemController::class, 'destroy'])->name('kit.deletar');

Route::get('/kit/relatorio', [KitMontagemController::class, 'relatorio'])->name('kit.relatorio');
Route::get('/kit/relatorio/exportar-pdf', [KitMontagemController::class, 'exportarRelatorioPDF'])->name('kit.relatorio.pdf');
Route::get('/kit/relatorio/exportar-excel', [KitMontagemController::class, 'exportarRelatorioExcel'])->name('kit.relatorio.excel');


Route::get('/container', function () {
    return view('container');
})->name('container.descarga');

Route::get('/formulario', function () {
    return view('formulario');
})->name('formulario');

Route::post('/formulario', function (Request $request) {
    \Log::info('[SYNC] Recebido:', $request->all());
    return response()->json(['status' => 'ok']);
})->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);


Route::get('/offline', function () {
    return view('offline');
})->name('offline');

Route::middleware(['auth'])->prefix('equipamentos')->group(function () {
    Route::get('/', [EquipamentoController::class, 'index'])->name('equipamentos.index');
    Route::get('/create', [EquipamentoController::class, 'create'])->name('equipamentos.create');
    Route::post('/', [EquipamentoController::class, 'store'])->name('equipamentos.store');
    Route::get('/{equipamento}/edit', [EquipamentoController::class, 'edit'])->name('equipamentos.edit');
    Route::put('/{equipamento}', [EquipamentoController::class, 'update'])->name('equipamentos.update');
    Route::get('/export/excel', [EquipamentoController::class, 'exportExcel'])->name('equipamentos.export.excel');
    Route::get('/export/pdf', [EquipamentoController::class, 'exportPDF'])->name('equipamentos.export.pdf');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/atualizacoes', [SugestoesController::class, 'index'])->name('sugestoes.index');
    Route::post('/atualizacoes', [SugestoesController::class, 'store'])->name('sugestoes.store');
    Route::put('/atualizacoes/{id}', [SugestoesController::class, 'update'])->name('sugestoes.update');
});

Route::get('/cadastro/sucesso', function () {
    return view('cadastro.sucesso');
})->name('cadastro.sucesso');

Route::middleware(['auth'])->prefix('convites')->group(function () {
    Route::get('/', [ConviteController::class, 'index'])->name('convites.index');
    Route::post('/gerar', [ConviteController::class, 'gerar'])->name('convites.gerar');
});

Route::get('/cadastro', [AutoCadastroController::class, 'form'])->name('cadastro.form');
Route::post('/cadastro', [AutoCadastroController::class, 'salvar'])->name('cadastro.salvar');


Route::prefix('multipack')->group(function () {
    Route::get('/create', [MultipackController::class, 'create'])->name('multipack.create');
    Route::post('/store', [MultipackController::class, 'store'])->name('multipack.store');
});



Route::get('/kit/programar', [KitMontagemController::class, 'programar'])->name('kit.programar');
Route::post('/kit/programar', [KitMontagemController::class, 'storeProgramacao'])->name('kit.programar.store');


Route::get('/kit/buscar-skus', [KitMontagemController::class, 'buscarSkus'])->name('kit.buscarSkus');
Route::get('/kit/buscar-descricao', [KitMontagemController::class, 'buscarDescricao'])->name('kit.buscarDescricao');


Route::get('/kit', [KitMontagemController::class, 'index'])->name('kit.index');


Route::get('/kit/create', [KitMontagemController::class, 'create'])->name('kit.create');

Route::get('/kit/apontamento', [KitMontagemController::class, 'apontamento'])->name('kit.apontamento');

Route::post('/kit/store', [KitMontagemController::class, 'store'])->name('kit.store');

Route::get('/teste', function () {
    return 'Laravel funcionando';
});


Route::get('/painel-tv', [DashboardTvController::class, 'index'])->name('painel.tv');

Route::get('/painel-tv/dados', [DashboardTvController::class, 'dados'])->name('painel.tv.dados');


Route::get('/teste', function () {
    return 'Laravel funcionando';
});

Route::get('/login/microsoft', [MicrosoftController::class, 'redirectToProvider'])->name('login.microsoft');
Route::get('/login/microsoft/callback', [MicrosoftController::class, 'handleProviderCallback']);

Route::get('/etiquetas/html', [EtiquetaController::class, 'viewHtml']);


Route::get('/separacoes/itens/{id}/pular', [SeparacaoController::class, 'pular'])->name('separacoes.pular');


Route::get('/separacoes/pendencias', [SeparacaoController::class, 'pendencias'])->name('separacoes.pendencias');


Route::get('/liberar-posicao/{id}', [SeparacaoController::class, 'liberarPosicao']);


Route::get('/dashboard/resumo/pdf', [DashboardController::class, 'exportarResumoDiaPDF'])->name('dashboard.resumo.pdf');

Route::put('/notificacoes/{id}/ler', [DashboardController::class, 'marcarNotificacaoLida'])->name('notificacoes.ler');

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/etiquetas/hydra/reimprimir/{fo}', [EtiquetaController::class, 'reimprimirSelecionar'])->name('etiquetas.hydra.reimprimir');
Route::post('/etiquetas/hydra/imprimir', [EtiquetaController::class, 'imprimirSelecionadas'])->name('etiquetas.hydra.imprimir');


Route::get('/etiquetas/hydra/historico', [EtiquetaController::class, 'historico'])->name('etiquetas.hydra.historico');
Route::get('/etiquetas/hydra', [\App\Http\Controllers\EtiquetaController::class, 'viewHtml'])->name('etiquetas.hydra');
Route::get('/etiquetas/hydra/reimprimir/{fo}', [EtiquetaController::class, 'reimprimir'])->name('etiquetas.hydra.reimprimir');



Route::get('/etiquetas/html', [EtiquetaController::class, 'viewHtml'])->name('etiquetas.html');

Route::get('/etiquetas/gerar', [EtiquetaController::class, 'gerar'])->name('etiquetas.gerar');


Route::get('/painel-operador', function () {
    return view('painel_operador');
})->name('painel.operador')->middleware(['auth']);


Route::get('/separacoes/linha/manual', function () {
    return view('setores.separacao.linha.separar_manual');
})->name('separacoes.linha.manual');

Route::post('/separacoes/linha/manual/salvar', [\App\Http\Controllers\Setores\SeparacaoController::class, 'salvarLinhaManual'])->name('separacoes.salvarLinhaManual');


Route::get('/separacoes/linha/{id}', [SeparacaoController::class, 'linha'])->name('separacoes.linha');


Route::get('/separacoes/linha/{id}', [SeparacaoController::class, 'linha'])->name('separacoes.linha');
Route::post('/separacoes/store', [SeparacaoController::class, 'store'])->name('separacoes.store');

Route::get('/setores/separacao/separacoes', [SeparacaoController::class, 'listarEmAndamento'])->name('separacoes.andamento');


Route::get('/separacoes/separar/{id}', [ExecutarSeparacaoController::class, 'separarItem'])->name('separacoes.separar_item');
Route::post('/separacoes/separar/{id}', [ExecutarSeparacaoController::class, 'executar'])->name('separacoes.executar');
Route::get('/separacoes/andamento', [SeparacaoController::class, 'index'])->name('separacoes.andamento');


Route::get('/separacoes/separar/{item_id}', [ExecutarSeparacaoController::class, 'mostrarFormSeparar'])->name('separacoes.form');
Route::get('/separacoes/separar/{item_id}', [ExecutarSeparacaoController::class, 'mostrarFormSeparar'])->name('separacoes.form');
Route::post('/separacoes/separar/{item_id}', [ExecutarSeparacaoController::class, 'executar'])->name('separacoes.executar');


Route::get('/separacoes/itens/{pedido_id}', [SeparacaoController::class, 'verItensSeparacao'])->name('separacoes.itens');


Route::prefix('separacoes')->middleware('auth')->group(function () {
    Route::post('/iniciar/{pedido_id}', [SeparacaoController::class, 'iniciar'])->name('separacoes.iniciar');
    Route::get('/{id}', [SeparacaoController::class, 'show'])->name('separacoes.show'); // se ainda não tiver
});


Route::prefix('pedidos')->middleware('auth')->group(function () {
    Route::get('/', [PedidoController::class, 'index'])->name('pedidos.index');
});


Route::prefix('separacoes')->middleware('auth')->group(function () {
    Route::get('/', [SeparacaoController::class, 'listarEmAndamento'])->name('separacoes.index');
});



Route::prefix('pedidos')->middleware('auth')->group(function () {
    Route::get('/criar', [PedidoController::class, 'create'])->name('pedidos.create');
    Route::post('/store', [PedidoController::class, 'store'])->name('pedidos.store');
});


Route::prefix('pedidos')->middleware('auth')->group(function () {
    Route::get('/', [PedidoController::class, 'index'])->name('pedidos.index');
    Route::get('/criar', [PedidoController::class, 'create'])->name('pedidos.create');
    Route::post('/store', [PedidoController::class, 'store'])->name('pedidos.store');
    Route::get('/{id}', [PedidoController::class, 'show'])->name('pedidos.show');
});


Route::prefix('separacoes')->middleware('auth')->group(function () {
    Route::get('/novo-pedido', [SeparacaoController::class, 'criarPedido'])->name('separacoes.novoPedido');
    Route::post('/salvar-pedido', [SeparacaoController::class, 'salvarPedido'])->name('separacoes.salvarPedido');
    Route::get('/{pedido_id}/nova', [SeparacaoController::class, 'novaSeparacao'])->name('separacoes.nova');
    Route::post('/{pedido_id}/salvar', [SeparacaoController::class, 'salvarSeparacao'])->name('separacoes.salvarSeparacao');
    Route::get('/{id}/detalhes', [SeparacaoController::class, 'mostrarSeparacao'])->name('separacoes.show');
});


Route::get('/setores/conferencia/{id}/ressalva', [ConferenciaController::class, 'formRessalva'])->name('setores.conferencia.formRessalva');
Route::post('/setores/conferencia/{id}/ressalva', [ConferenciaController::class, 'salvarRessalva'])->name('setores.conferencia.salvarRessalva');


Route::post('/setores/conferencia/{id}/ressalva', [ConferenciaController::class, 'salvarRessalva'])->name('setores.conferencia.salvarRessalva');

Route::get('/setores/conferencia/{id}/foto-inicio', [ConferenciaController::class, 'telaFotoInicio'])->name('setores.conferencia.telaFotoInicio');


Route::post('/setores/conferencia/{id}/reabrir', [ConferenciaController::class, 'reabrir'])->name('setores.conferencia.reabrir');

Route::get('/setores/recebimento/painel', [RecebimentoController::class, 'painel'])->name('setores.recebimento.painel');
Route::get('/setores/recebimento/painel/{id}', [RecebimentoController::class, 'detalharPainel'])->name('setores.recebimento.painel.detalhado');



Route::get('setores/conferencia/item/{recebimento_id}/{item_id}/conferir', [ConferenciaController::class, 'formConferirItem'])->name('setores.conferencia.formConferirItem');

// Exibir o formulário de conferência do item (GET)
Route::get('setores/conferencia/{recebimento_id}/item/{item_id}/conferir', [ConferenciaController::class, 'formConferirItem'])
    ->name('setores.conferencia.formConferirItem');

// Salvar os dados da conferência do item (POST)
Route::post('setores/conferencia/{recebimento_id}/item/{item_id}/conferir', [ConferenciaController::class, 'salvarConferenciaItem'])
    ->name('setores.conferencia.salvarConferenciaItem');

// Exibe o formulário de conferência manual
Route::get('setores/conferencia/item/{id}/enviar-manual', [ConferenciaController::class, 'formItemManual'])->name('setores.conferencia.formItemManual');

// Salva os dados da conferência
Route::post('setores/conferencia/item/{id}/enviar-manual', [ConferenciaController::class, 'enviarItemManual'])->name('setores.conferencia.enviarItemManual');



Route::post('/setores/conferencia/item/{id}/enviar-manual', [ConferenciaController::class, 'enviarItemManual'])
    ->name('setores.conferencia.enviarItemManual');


Route::get('/teste-conferencia', function () {
    return view('teste_post_conferencia');
});

Route::post('/setores/conferencia/item/{id}/conferir', [ConferenciaController::class, 'salvarItem'])
    ->name('setores.conferencia.salvarItem');

// Grupo de rotas protegidas para conferência
Route::middleware(['auth'])->prefix('setores/conferencia')->group(function () {

    // Página inicial para enviar a foto do início do veículo
    Route::get('/{id}/foto', [ConferenciaController::class, 'telaFotoInicio'])->name('setores.conferencia.foto');
    Route::post('/{id}/salvar-foto', [ConferenciaController::class, 'salvarFotoInicio'])->name('setores.conferencia.salvarFotoInicio');

    // Página de itens para conferência
    Route::get('/{id}/itens', [ConferenciaController::class, 'itens'])->name('setores.conferencia.itens');

    // Página de conferência individual de item
    Route::get('item/{id}/conferir', [ConferenciaController::class, 'formConferirItem'])->name('setores.conferencia.formConferirItem');

    // Salvamento de conferência de item
    Route::post('item/{id}/conferir', [ConferenciaController::class, 'salvarItem'])->name('setores.conferencia.salvarItem');

    // Fechamento da conferência
    Route::post('/{id}/finalizar', [ConferenciaController::class, 'finalizar'])->name('setores.conferencia.finalizar');

    // Relatório em PDF
    Route::get('/{id}/relatorio', [ConferenciaController::class, 'gerarRelatorio'])->name('setores.conferencia.relatorio');
});


Route::middleware(['auth'])->prefix('setores/conferencia')->group(function () {
    Route::get('item/{id}/conferir', [ConferenciaController::class, 'formConferirItem'])->name('setores.conferencia.formConferirItem');
});


Route::middleware('auth')->group(function () {
    Route::post('/setores/conferencia/item/{id}/conferir', [ConferenciaController::class, 'contar'])->name('setores.conferencia.contar');
});



Route::post('/setores/conferencia/{id}/salvar-foto', [ConferenciaController::class, 'salvarFotoInicio'])->name('setores.conferencia.salvarFotoInicio');


Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Apenas admins

    Route::get('/usuarios', [UserController::class, 'index'])->name('usuarios.index');
    Route::get('/usuarios/novo', [UserController::class, 'create'])->name('usuarios.create');
    Route::post('/usuarios', [UserController::class, 'store'])->name('usuarios.store');
    Route::get('/usuarios/{id}/editar', [UserController::class, 'edit'])->name('usuarios.edit');
    Route::put('/usuarios/{id}', [UserController::class, 'update'])->name('usuarios.update');
    Route::delete('/usuarios/{id}', [UserController::class, 'destroy'])->name('usuarios.destroy');
});

Route::prefix('setores')->middleware('auth')->group(function () {
    Route::prefix('recebimento')->group(function () {
        Route::get('/conferencia', [RecebimentoController::class, 'index'])->name('recebimento.index');
        Route::post('/conferir', [RecebimentoController::class, 'conferir'])->name('recebimento.conferir');
    });

    Route::prefix('armazenagem')->group(function () {
        Route::get('/', [ArmazenagemController::class, 'index'])->name('armazenagem.index');
        Route::post('/', [ArmazenagemController::class, 'store'])->name('armazenagem.store');
    });

    Route::prefix('separacao')->group(function () {
        Route::get('/', [SeparacaoController::class, 'index'])->name('separacao.index');
        Route::post('/', [SeparacaoController::class, 'store'])->name('separacao.store');
    });
});

Route::get('/armazenagem/buscar-skus', [ArmazenagemController::class, 'buscarSkus'])->name('armazenagem.buscarSkus');
Route::get('/armazenagem/buscar-descricao', [ArmazenagemController::class, 'buscarDescricao'])->name('armazenagem.buscarDescricao');
Route::get('/armazenagem/buscar-posicoes', [ArmazenagemController::class, 'buscarPosicoes'])->name('armazenagem.buscarPosicoes');

Route::prefix('logs')->middleware(['auth'])->group(function () {
    Route::get('/', [App\Http\Controllers\LogController::class, 'index'])->name('logs.index');
});

Route::prefix('relatorios')->middleware(['auth'])->group(function () {
    Route::get('/', [App\Http\Controllers\RelatorioController::class, 'index'])->name('relatorios.index');
});

Route::middleware(['auth'])->prefix('logs')->group(function () {
    Route::get('/', [LogController::class, 'index'])->name('logs.index');
    Route::get('/export/excel', [LogController::class, 'exportExcel'])->name('logs.export.excel');
    Route::get('/export/pdf', [LogController::class, 'exportPDF'])->name('logs.export.pdf');
});



Route::middleware(['auth'])->prefix('relatorios')->group(function () {
    Route::get('/separacoes', [RelatorioController::class, 'separacoes'])->name('relatorios.separacoes');
});




Route::middleware(['auth'])->prefix('relatorios')->group(function () {
    Route::get('/', [RelatorioController::class, 'index'])->name('relatorios.index');
    Route::get('/separacoes', [RelatorioController::class, 'separacoes'])->name('relatorios.separacoes');
    Route::get('/separacoes/export/excel', [RelatorioController::class, 'exportSeparacoesExcel'])->name('relatorios.separacoes.excel');
    Route::get('/separacoes/export/pdf', [RelatorioController::class, 'exportSeparacoesPDF'])->name('relatorios.separacoes.pdf');


    Route::get('/armazenagem', [RelatorioController::class, 'armazenagem'])->name('relatorios.armazenagem');
    Route::get('/armazenagem/export/excel', [RelatorioController::class, 'exportArmazenagemExcel'])->name('relatorios.armazenagem.excel');
    Route::get('/armazenagem/export/pdf', [RelatorioController::class, 'exportArmazenagemPDF'])->name('relatorios.armazenagem.pdf');
});



Route::prefix('contagem')->middleware('auth')->group(function () {
    Route::get('/itens', [ContagemItemController::class, 'index'])->name('contagem.itens.index');
    Route::get('/itens/novo', [ContagemItemController::class, 'create'])->name('contagem.itens.create');
    Route::post('/itens', [ContagemItemController::class, 'store'])->name('contagem.itens.store');
    
    Route::get('/itens/export/excel', [ContagemItemController::class, 'exportExcel'])->name('contagem.itens.excel');
    Route::get('/itens/export/pdf', [ContagemItemController::class, 'exportPDF'])->name('contagem.itens.pdf');
});



Route::prefix('recebimento/itens')->middleware('auth')->group(function () {
    Route::get('/{recebimento_id}', [RecebimentoItemController::class, 'index'])->name('recebimento.itens.index');
    Route::get('/{recebimento_id}/novo', [RecebimentoItemController::class, 'create'])->name('recebimento.itens.create');
    Route::post('/{recebimento_id}/store', [RecebimentoItemController::class, 'store'])->name('recebimento.itens.store');
});

Route::prefix('setores')->middleware('auth')->group(function () {
    Route::prefix('recebimento')->group(function () {
        Route::get('/painel', [RecebimentoController::class, 'painel'])->name('setores.recebimento.painel');
    });
});


Route::prefix('setores')->middleware('auth')->group(function () {
    Route::prefix('recebimento')->group(function () {
        Route::get('/painel', [RecebimentoController::class, 'painel'])->name('setores.recebimento.painel');
        Route::get('/novo', [RecebimentoController::class, 'create'])->name('setores.recebimento.create');
        Route::post('/store', [RecebimentoController::class, 'store'])->name('setores.recebimento.store'); // 👈 ESSA LINHA
    });
});


Route::prefix('setores')->middleware(['auth'])->group(function () {
    Route::prefix('conferencia')->group(function () {
        Route::get('/', [ConferenciaController::class, 'index'])->name('setores.conferencia.index');
        Route::get('/{id}/itens', [ConferenciaController::class, 'itens'])->name('setores.conferencia.itens');
        Route::post('/{id}/contar', [ConferenciaController::class, 'contar'])->name('setores.conferencia.contar');
    });
});

Route::get('/setores/conferencia/{id}/relatorio', [ConferenciaController::class, 'gerarRelatorioPDF'])->name('setores.conferencia.relatorio');
Route::post('/setores/conferencia/{id}/finalizar', [ConferenciaController::class, 'finalizar'])->name('setores.conferencia.finalizar');
Route::post('/setores/conferencia/item/{id}/conferir', [ConferenciaController::class, 'conferirItem'])->name('setores.conferencia.item.conferir');
Route::post('/setores/conferencia/{id}/iniciar', [ConferenciaController::class, 'salvarFotoInicio'])->name('setores.conferencia.iniciar');
Route::get('/setores/conferencia/{id}/foto', [ConferenciaController::class, 'telaFotoInicio'])->name('setores.conferencia.foto');
Route::post('/setores/conferencia/{id}/contar', [ConferenciaController::class, 'contar'])->name('setores.conferencia.contar');


Route::post('/setores/conferencia/item/{id}/conferir', [ConferenciaController::class, 'contar'])->name('setores.conferencia.contar');



Route::middleware(['auth'])->group(function () {
    Route::post('/setores/conferencia/{id}/contar', [ConferenciaController::class, 'contar'])->name('setores.conferencia.contar');
});
