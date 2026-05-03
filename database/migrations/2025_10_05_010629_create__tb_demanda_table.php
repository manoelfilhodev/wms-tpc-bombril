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
        Schema::create('_tb_demanda', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('fo', 50);
            $table->string('cliente', 150)->nullable();
            $table->string('transportadora', 150)->nullable();
            $table->string('veiculo', 50)->nullable();
            $table->string('modelo_veicular', 150)->nullable();
            $table->string('doca', 10)->nullable();
            $table->enum('tipo', ['RECEBIMENTO', 'EXPEDICAO']);
            $table->integer('quantidade')->nullable();
            $table->string('motorista', 150)->nullable();
            $table->decimal('peso', 10)->nullable();
            $table->decimal('valor_carga', 12)->nullable();
            $table->time('hora_agendada')->nullable();
            $table->time('entrada')->nullable();
            $table->time('saida')->nullable();
            $table->enum('status', ['GERAR', 'A_SEPARAR', 'SEPARANDO', 'A_CONFERIR', 'CONFERINDO', 'CONFERIDO', 'A_CARREGAR', 'CARREGANDO', 'CARREGADO', 'FATURANDO', 'LIBERADO'])->nullable()->default('GERAR');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('_tb_demanda');
    }
};
