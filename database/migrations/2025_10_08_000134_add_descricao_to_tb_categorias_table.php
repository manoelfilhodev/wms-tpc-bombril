<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('_tb_categorias', function (Blueprint $table) {
            if (!Schema::hasColumn('_tb_categorias', 'descricao')) {
                $table->string('descricao', 255)->nullable()->after('nome');
            }
        });
    }

    public function down(): void
    {
        Schema::table('_tb_categorias', function (Blueprint $table) {
            if (Schema::hasColumn('_tb_categorias', 'descricao')) {
                $table->dropColumn('descricao');
            }
        });
    }
};
