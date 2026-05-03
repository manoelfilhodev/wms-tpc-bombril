<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContagemLivreController;
use App\Http\Controllers\ContagemSkuController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\InventarioCiclicoController;
use App\Http\Controllers\Mb52Controller;

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
    Route::post('/inventario/importar', [InventarioController::class, 'importarMB51'])->name('inventario.importar.mb51');
});


