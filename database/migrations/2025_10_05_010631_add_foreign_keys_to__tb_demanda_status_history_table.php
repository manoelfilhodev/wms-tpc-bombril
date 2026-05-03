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
        Schema::table('_tb_demanda_status_history', function (Blueprint $table) {
            $table->foreign(['demanda_id'], 'fk_demanda')->references(['id'])->on('_tb_demanda')->onUpdate('restrict')->onDelete('cascade');
            $table->foreign(['changed_by'], 'fk_user')->references(['id_user'])->on('_tb_usuarios')->onUpdate('restrict')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('_tb_demanda_status_history', function (Blueprint $table) {
            $table->dropForeign('fk_demanda');
            $table->dropForeign('fk_user');
        });
    }
};
