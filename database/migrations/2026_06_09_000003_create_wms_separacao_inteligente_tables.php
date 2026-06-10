<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('_tb_wms_separacao_geracoes', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('demanda_id');
            $table->string('fo', 80);
            $table->string('criterio_agrupamento', 40);
            $table->string('criterio_ordenacao', 40);
            $table->string('criterio_equalizacao', 40)->nullable();
            $table->unsignedInteger('quantidade_separadores')->default(1);
            $table->unsignedInteger('total_itens')->default(0);
            $table->unsignedInteger('total_skus')->default(0);
            $table->unsignedInteger('total_ruas')->default(0);
            $table->unsignedInteger('itens_sem_endereco')->default(0);
            $table->string('status', 40)->default('GERADA');
            $table->unsignedBigInteger('gerado_por')->nullable();
            $table->timestamps();

            $table->foreign('demanda_id', 'wms_sep_geracoes_demanda_fk')
                ->references('id')
                ->on('_tb_demanda')
                ->cascadeOnDelete();
            $table->index(['fo', 'status'], 'wms_sep_geracoes_fo_status_idx');
        });

        Schema::create('_tb_wms_separacao_folhas', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('geracao_id');
            $table->unsignedInteger('numero_folha');
            $table->unsignedInteger('separador_numero')->nullable();
            $table->string('titulo', 160);
            $table->string('rua', 80)->nullable();
            $table->string('curva_abc', 20)->nullable();
            $table->unsignedInteger('total_skus')->default(0);
            $table->decimal('total_quantidade', 12, 3)->default(0);
            $table->decimal('peso_estimado', 12, 3)->nullable();
            $table->string('status', 40)->default('GERADA');
            $table->timestamps();

            $table->foreign('geracao_id', 'wms_sep_folhas_geracao_fk')
                ->references('id')
                ->on('_tb_wms_separacao_geracoes')
                ->cascadeOnDelete();
            $table->unique(['geracao_id', 'numero_folha'], 'wms_sep_folhas_numero_unico');
        });

        Schema::create('_tb_wms_separacao_itens', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('geracao_id');
            $table->unsignedBigInteger('folha_id');
            $table->unsignedBigInteger('demanda_id');
            $table->string('fo', 80);
            $table->unsignedBigInteger('sku_id')->nullable();
            $table->unsignedBigInteger('posicao_id')->nullable();
            $table->string('sku', 80);
            $table->string('descricao')->nullable();
            $table->string('curva_abc', 20)->nullable();
            $table->string('rua', 80)->nullable();
            $table->string('posicao', 80)->nullable();
            $table->string('endereco', 160)->nullable();
            $table->string('lado', 40)->nullable();
            $table->decimal('quantidade', 12, 3)->default(0);
            $table->unsignedInteger('sequencia_rota')->nullable();
            $table->unsignedInteger('ordem_separacao')->nullable();
            $table->string('status', 40)->default('PENDENTE');
            $table->string('observacao')->nullable();
            $table->timestamps();

            $table->foreign('geracao_id', 'wms_sep_itens_geracao_fk')
                ->references('id')
                ->on('_tb_wms_separacao_geracoes')
                ->cascadeOnDelete();
            $table->foreign('folha_id', 'wms_sep_itens_folha_fk')
                ->references('id')
                ->on('_tb_wms_separacao_folhas')
                ->cascadeOnDelete();
            $table->foreign('demanda_id', 'wms_sep_itens_demanda_fk')
                ->references('id')
                ->on('_tb_demanda')
                ->cascadeOnDelete();
            $table->foreign('sku_id', 'wms_sep_itens_sku_fk')
                ->references('id')
                ->on('_tb_wms_skus')
                ->nullOnDelete();
            $table->foreign('posicao_id', 'wms_sep_itens_posicao_fk')
                ->references('id')
                ->on('_tb_wms_posicoes')
                ->nullOnDelete();
            $table->index(['fo', 'sku'], 'wms_sep_itens_fo_sku_idx');
            $table->index(['geracao_id', 'folha_id', 'ordem_separacao'], 'wms_sep_itens_ordem_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('_tb_wms_separacao_itens');
        Schema::dropIfExists('_tb_wms_separacao_folhas');
        Schema::dropIfExists('_tb_wms_separacao_geracoes');
    }
};
