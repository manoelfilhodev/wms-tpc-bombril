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
        Schema::table('_tb_kit_etiquetas', function (Blueprint $table) {
            $table->foreign(['id_kit'], 'fk_kit')->references(['id'])->on('_tb_kit_montagem')->onUpdate('restrict')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('_tb_kit_etiquetas', function (Blueprint $table) {
            $table->dropForeign('fk_kit');
        });
    }
};
