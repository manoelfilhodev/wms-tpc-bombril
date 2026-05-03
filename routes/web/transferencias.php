<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TransferenciaController;
use App\Http\Controllers\TransferenciaEtiquetaController;

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
    Route::get('/', [TransferenciaEtiquetaController::class, 'index'])->name('transferencia.etiquetas.index.legacy');
    Route::get('/{id}/visualizar', [TransferenciaEtiquetaController::class, 'visualizar'])->name('transferencia.etiquetas.visualizar.legacy');
    Route::get('/{id}/imprimir-tudo', [TransferenciaEtiquetaController::class, 'imprimirTudo'])->name('transferencia.etiquetas.imprimirTudo.legacy');
    Route::get('/{id}/reimprimir', [TransferenciaEtiquetaController::class, 'reimprimir'])->name('transferencia.etiquetas.reimprimir');
});
});



