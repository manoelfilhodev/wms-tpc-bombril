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
        Schema::create('_tb_inventario_fichas', function (Blueprint $table) {
            $table->increments('id');
            $table->string('cod_referencia', 50);
            $table->string('sku', 100);
            $table->string('descricao');
            $table->string('posicao', 100)->nullable();
            $table->string('deposito', 50)->nullable();
            $table->integer('ordem')->nullable()->default(0);
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('_tb_inventario_fichas');
    }
};
