<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

Route::get('/teste-imagem', function () {
    $origem = storage_path('app/public/exemplo.jpg'); // Substitua pelo caminho da imagem
    $destino = storage_path('app/public/teste_redimensionada.jpg');

    if (!file_exists($origem)) {
        return 'Imagem de origem não encontrada. Envie "exemplo.jpg" para storage/app/public/';
    }

    $img = Image::make($origem)
        ->resize(1024, null, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        })
        ->save($destino, 80);

    return 'Imagem redimensionada com sucesso! Verifique: storage/app/public/teste_redimensionada.jpg';
});
