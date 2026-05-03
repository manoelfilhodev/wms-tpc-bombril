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
        Schema::create('_tb_recebimento_itens', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('recebimento_id')->index();
            $table->string('sku', 100)->nullable();
            $table->string('descricao')->nullable();
            $table->integer('quantidade')->nullable();
            $table->enum('status', ['pendente', 'conferido', 'armazenado'])->nullable()->default('pendente');
            $table->integer('usuario_id')->index();
            $table->integer('unidade_id')->index();
            $table->timestamp('created_at')->useCurrent();
            $table->string('ean', 50)->nullable();
            $table->decimal('valor_unitario', 10)->nullable();
            $table->decimal('valor_total', 10)->nullable();
            $table->integer('qtd_conferida')->nullable();
            $table->text('observacao')->nullable();
            $table->boolean('divergente')->nullable()->default(false);
            $table->boolean('avariado')->nullable()->default(false);
            $table->string('foto_avaria')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('_tb_recebimento_itens');
    }
};
