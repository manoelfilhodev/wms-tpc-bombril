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
        Schema::create('_tb_armazenagem', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('sku', 100)->index();
            $table->integer('quantidade');
            $table->string('endereco', 50)->index();
            $table->text('observacoes')->nullable();
            $table->integer('usuario_id')->index();
            $table->timestamp('data_armazenagem')->useCurrent();
            $table->integer('unidade_id')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('_tb_armazenagem');
    }
};
