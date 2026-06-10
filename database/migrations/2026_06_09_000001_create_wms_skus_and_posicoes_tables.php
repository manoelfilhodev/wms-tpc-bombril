<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('_tb_wms_skus', function (Blueprint $table): void {
            $table->id();
            $table->string('sku', 80)->unique();
            $table->string('descricao')->nullable();
            $table->decimal('peso_kg', 12, 3)->nullable();
            $table->string('classe_peso', 80)->nullable();
            $table->string('classe_cubagem', 80)->nullable();
            $table->string('curva_abc', 20)->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });

        Schema::create('_tb_wms_posicoes', function (Blueprint $table): void {
            $table->id();
            $table->string('bloco', 40)->nullable();
            $table->string('rua', 80);
            $table->string('posicao', 80);
            $table->string('endereco', 160);
            $table->string('lado', 40)->nullable();
            $table->unsignedInteger('sequencia_rota')->nullable();
            $table->string('status', 40)->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            $table->unique(['bloco', 'rua', 'posicao', 'endereco'], 'wms_posicoes_unica');
            $table->index(['rua', 'endereco'], 'wms_posicoes_busca_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('_tb_wms_posicoes');
        Schema::dropIfExists('_tb_wms_skus');
    }
};
