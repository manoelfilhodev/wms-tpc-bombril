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
        Schema::create('_tb_etiquetas_sep_hydra_metais', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('usuario_id')->nullable()->index();
            $table->integer('pedido_id')->nullable()->index();
            $table->string('fo', 50)->nullable();
            $table->string('remessa', 100)->nullable();
            $table->string('recebedor', 100)->nullable();
            $table->string('cliente')->nullable();
            $table->string('produto', 100)->nullable();
            $table->integer('qtd')->nullable()->default(1);
            $table->string('cidade', 100)->nullable();
            $table->char('uf', 2)->nullable();
            $table->string('doca', 10)->nullable();
            $table->string('ip', 45)->nullable();
            $table->dateTime('data_gerada')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('_tb_etiquetas_sep_hydra_metais');
    }
};
