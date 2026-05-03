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
        Schema::table('_tb_apontamentos_kits', function (Blueprint $table) {
            $table->foreign(['apontado_por'], 'fk_apontado_por')->references(['id_user'])->on('_tb_usuarios')->onUpdate('restrict')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('_tb_apontamentos_kits', function (Blueprint $table) {
            $table->dropForeign('fk_apontado_por');
        });
    }
};
