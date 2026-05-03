<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('_tb_materiais', function (Blueprint $table) {
            if (!Schema::hasColumn('_tb_materiais', 'nome')) {
                $table->string('nome', 150)->after('id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('_tb_materiais', function (Blueprint $table) {
            if (Schema::hasColumn('_tb_materiais', 'nome')) {
                $table->dropColumn('nome');
            }
        });
    }
};
