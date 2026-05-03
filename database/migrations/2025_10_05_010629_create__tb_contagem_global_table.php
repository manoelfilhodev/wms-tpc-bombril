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
        Schema::create('_tb_contagem_global', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('unidade_id')->index();
            $table->integer('material_id')->index();
            $table->enum('tipo_contagem', ['1contagem', '2contagem', '3contagem']);
            $table->integer('quantidade_contada')->nullable();
            $table->integer('usuario_id')->nullable()->index();
            $table->timestamp('data_contagem')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('_tb_contagem_global');
    }
};
