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
        Schema::create('_tb_expedicao_previsoes', function (Blueprint $table) {
            $table->id();

            // Relacionamento
            $table->unsignedBigInteger('programacao_id')->index();

            // Identificação
            $table->string('fo', 50)->index();

            // Score operacional
            $table->decimal('score_operacional', 8, 2)->nullable();

            // Tempos previstos
            $table->integer('tempo_separacao_min')->nullable();
            $table->integer('tempo_conferencia_min')->nullable();
            $table->integer('tempo_carregamento_min')->nullable();
            $table->integer('tempo_viagem_min')->nullable();

            // Tempo total previsto
            $table->integer('tempo_total_min')->nullable();

            // Timeline operacional prevista
            $table->dateTime('previsao_chegada_doca')->nullable();
            $table->dateTime('previsao_inicio_separacao')->nullable();
            $table->dateTime('previsao_inicio_conferencia')->nullable();
            $table->dateTime('previsao_inicio_carregamento')->nullable();
            $table->dateTime('previsao_saida_caminhao')->nullable();

            // Risco operacional
            $table->enum('risco_operacional', [
                'BAIXO',
                'MEDIO',
                'ALTO',
                'CRITICO'
            ])->default('BAIXO');

            // Situação
            $table->enum('status', [
                'PROCESSANDO',
                'CALCULADO',
                'ERRO'
            ])->default('PROCESSANDO');

            // Logs / observações
            $table->text('observacoes')->nullable();

            $table->timestamps();

            // Foreign key
            $table->foreign('programacao_id')
                ->references('id')
                ->on('_tb_expedicao_programacoes')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('_tb_expedicao_previsoes');
    }
};
