<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('_tb_separacao_itens', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('pedido_id')->index();
            $table->integer('separacao_id')->nullable()->index();
            $table->string('sku', 100);
            $table->integer('quantidade');
            $table->integer('quantidade_separada')->nullable();
            $table->text('observacoes')->nullable();
            $table->string('centro', 20)->nullable();
            $table->string('fo', 20)->nullable();
            $table->integer('usuario_id')->index();
            $table->integer('coletado_por')->nullable();
            $table->integer('unidade_id')->index();
            $table->boolean('conferido')->nullable()->default(false);
            $table->enum('status', ['ABERTA', 'FINALIZADA'])->nullable()->default('ABERTA');
            $table->timestamp('data_separacao')->nullable();
            $table->timestamp('data_conferencia')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('_tb_separacao_itens');
    }
};
