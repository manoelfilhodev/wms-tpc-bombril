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
        Schema::create('_tb_expedicao_criterios', function (Blueprint $table) {
            $table->id();

            // Categoria da regra
            $table->string('categoria', 100)->index();
            // Ex:
            // SEPARACAO
            // CONFERENCIA
            // CARREGAMENTO
            // ROTA
            // TIPO_CARGA

            // Nome da regra
            $table->string('nome', 150);

            // Condições operacionais
            $table->integer('sku_min')->nullable();
            $table->integer('sku_max')->nullable();

            $table->integer('volume_min')->nullable();
            $table->integer('volume_max')->nullable();

            $table->decimal('peso_min', 12, 3)->nullable();
            $table->decimal('peso_max', 12, 3)->nullable();

            // Tipo de carga
            $table->string('tipo_carga', 100)->nullable();

            // Picking
            $table->boolean('possui_picking')->nullable();

            // Tempo operacional previsto
            $table->integer('tempo_previsto_minutos')->default(0);

            // Multiplicador operacional
            $table->decimal('multiplicador', 8, 2)->default(1);

            // Ativo
            $table->boolean('ativo')->default(true);

            // Observações
            $table->text('observacoes')->nullable();

            $table->timestamps();

            // Índices
            $table->index(['categoria', 'tipo_carga']);
            $table->index(['possui_picking']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('_tb_expedicao_criterios');
    }
};
