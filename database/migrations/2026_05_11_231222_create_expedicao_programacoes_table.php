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
        Schema::create('_tb_expedicao_programacoes', function (Blueprint $table) {
            $table->id();

            // Identificação operacional
            $table->string('fo', 50)->index();
            $table->string('dt_sap', 50)->nullable()->index();

            // Dados da entrega
            $table->dateTime('agenda_entrega_em')->nullable();
            $table->string('cidade_destino', 150)->nullable();
            $table->string('uf_destino', 2)->nullable();

            // Dados operacionais
            $table->string('cliente', 150)->nullable();
            $table->string('transportadora', 150)->nullable();
            $table->string('tipo_veiculo', 100)->nullable();
            $table->string('tipo_carga', 100)->nullable();

            // Inteligência operacional
            $table->boolean('possui_picking')->default(false);

            $table->enum('status_previsao', [
                'AGUARDANDO_EXPLOSAO',
                'AGUARDANDO_CRITERIOS',
                'AGUARDANDO_ROTA',
                'PRONTA_PARA_PREVISAO',
                'PREVISAO_GERADA',
                'ERRO_DADOS'
            ])->default('AGUARDANDO_EXPLOSAO');

            $table->text('observacoes')->nullable();

            $table->timestamps();

            // Índices importantes
            $table->index(['cidade_destino', 'uf_destino']);
            $table->index(['agenda_entrega_em']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('_tb_expedicao_programacoes');
    }
};
