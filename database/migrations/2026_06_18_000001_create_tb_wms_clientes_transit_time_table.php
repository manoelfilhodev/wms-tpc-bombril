<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('_tb_wms_clientes_transit_time', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_cliente', 50)->unique();
            $table->string('nome_cliente', 150)->nullable();
            $table->string('zona_partida', 50)->nullable();
            $table->string('regiao', 100)->nullable();
            $table->string('uf', 2)->nullable();
            $table->string('cidade', 120)->nullable();
            $table->string('zona_transporte', 50)->nullable();
            $table->integer('ciclo_inte')->nullable();
            $table->integer('transit_time_fechada_dias');
            $table->integer('transit_time_fracionada_dias');
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            $table->index(['ativo', 'uf']);
            $table->index('zona_partida');
            $table->index('zona_transporte');
            $table->index(['regiao', 'cidade']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('_tb_wms_clientes_transit_time');
    }
};
