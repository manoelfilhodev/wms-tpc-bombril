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
        Schema::create('_tb_apontamentos_kits', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('codigo_material', 50);
            $table->integer('quantidade');
            $table->enum('status', ['GERADO', 'APONTADO', 'CANCELADO'])->nullable()->default('GERADO');
            $table->date('data');
            $table->unsignedBigInteger('user_id');
            $table->integer('apontado_por')->nullable()->index();
            $table->string('unidade', 100);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();
            $table->string('palete_uid', 50)->nullable()->unique();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('_tb_apontamentos_kits');
    }
};
