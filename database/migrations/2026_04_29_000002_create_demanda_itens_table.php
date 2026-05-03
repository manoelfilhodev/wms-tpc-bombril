<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('_tb_demanda_itens', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('demanda_id');
            $table->string('sku', 30);
            $table->string('sku_normalizado', 30);
            $table->string('descricao', 255)->nullable();
            $table->string('unidade_medida', 20)->nullable();
            $table->decimal('sobra', 12, 3)->default(0);
            $table->boolean('bloqueado')->default(false);
            $table->timestamps();

            $table->foreign('demanda_id')->references('id')->on('_tb_demanda')->onDelete('cascade');
            $table->index(['sku_normalizado', 'bloqueado']);
            $table->index(['demanda_id', 'sobra']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('_tb_demanda_itens');
    }
};

