<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('_tb_wms_sku_posicoes', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('sku_id');
            $table->unsignedBigInteger('posicao_id');
            $table->string('sku', 80)->nullable();
            $table->string('endereco', 160)->nullable();
            $table->decimal('qtd_padrao', 12, 3)->nullable();
            $table->unsignedInteger('prioridade')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            $table->foreign('sku_id', 'wms_sku_posicoes_sku_fk')
                ->references('id')
                ->on('_tb_wms_skus')
                ->cascadeOnDelete();
            $table->foreign('posicao_id', 'wms_sku_posicoes_posicao_fk')
                ->references('id')
                ->on('_tb_wms_posicoes')
                ->cascadeOnDelete();
            $table->unique(['sku_id', 'posicao_id'], 'wms_sku_posicoes_unica');
            $table->index(['sku', 'endereco'], 'wms_sku_posicoes_busca_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('_tb_wms_sku_posicoes');
    }
};
