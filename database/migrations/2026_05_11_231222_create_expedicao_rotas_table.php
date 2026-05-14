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
        Schema::create('_tb_expedicao_rotas', function (Blueprint $table) {
            $table->id();

            // Origem
            $table->string('cidade_origem', 150);
            $table->string('uf_origem', 2);

            // Destino
            $table->string('cidade_destino', 150);
            $table->string('uf_destino', 2);

            // Distância e tempo
            $table->decimal('distancia_km', 10, 2)->nullable();

            // Tempo bruto vindo da API
            $table->integer('tempo_api_minutos')->nullable();

            // Tempo ajustado operacionalmente
            $table->integer('tempo_operacional_minutos')->nullable();

            // Controle
            $table->timestamp('ultima_consulta_em')->nullable();

            $table->boolean('ativo')->default(true);

            $table->timestamps();

            // Índices importantes
            $table->index([
                'cidade_origem',
                'uf_origem',
                'cidade_destino',
                'uf_destino'
            ], 'idx_rota_origem_destino');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('_tb_expedicao_rotas');
    }
};
