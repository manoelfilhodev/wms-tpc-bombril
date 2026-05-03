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
        Schema::create('_tb_kit_montagem', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('codigo_material', 100);
            $table->integer('quantidade_programada');
            $table->integer('quantidade_produzida')->nullable();
            $table->integer('apontado_por')->nullable();
            $table->dateTime('apontado_em')->nullable();
            $table->integer('usuario_id')->nullable()->index();
            $table->integer('unidade_id')->nullable()->index();
            $table->integer('programado_por')->nullable();
            $table->dateTime('programado_em')->nullable();
            $table->date('data_montagem');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('_tb_kit_montagem');
    }
};
