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
        Schema::create('_tb_movimentos_ativos', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('codigo_movimento', 10)->unique();
            $table->string('descricao_movimento')->nullable();
            $table->boolean('ativo')->nullable()->default(true);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('_tb_movimentos_ativos');
    }
};
