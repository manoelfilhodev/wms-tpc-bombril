<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('_tb_demanda_separadores', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('demanda_id');
            $table->unsignedBigInteger('usuario_id');
            $table->timestamps();

            $table->foreign('demanda_id')->references('id')->on('_tb_demanda')->onDelete('cascade');
            $table->index(['demanda_id', 'usuario_id']);
            $table->unique(['demanda_id', 'usuario_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('_tb_demanda_separadores');
    }
};

