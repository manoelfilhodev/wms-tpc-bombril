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
        Schema::create('_tb_contagens_drone', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('sku');
            $table->string('posicao');
            $table->integer('quantidade');
            $table->string('usuario');
            $table->string('data_hora')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('_tb_contagens_drone');
    }
};
