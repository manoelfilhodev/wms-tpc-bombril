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
        Schema::create('_tb_sugestoes_atualizacoes', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('titulo');
            $table->text('descricao');
            $table->enum('status', ['pendente', 'em_andamento', 'concluida', 'recusada'])->nullable()->default('pendente');
            $table->text('resposta')->nullable();
            $table->integer('criado_por')->index();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('_tb_sugestoes_atualizacoes');
    }
};
