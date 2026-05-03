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
        Schema::create('_tb_listagem_skus_contagem', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('id_lista', 36)->nullable();
            $table->string('material', 50);
            $table->string('centro', 10)->nullable();
            $table->text('descricao')->nullable();
            $table->integer('quantidade')->nullable()->default(0);
            $table->integer('criado_por')->nullable();
            $table->dateTime('criado_em')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('_tb_listagem_skus_contagem');
    }
};
