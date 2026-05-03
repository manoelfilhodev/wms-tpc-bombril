<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('_tb_demanda_distribuicoes', function (Blueprint $table) {
            if (!Schema::hasColumn('_tb_demanda_distribuicoes', 'quantidade_skus')) {
                $table->unsignedInteger('quantidade_skus')->default(0)->after('quantidade_pecas');
            }
        });
    }

    public function down(): void
    {
        Schema::table('_tb_demanda_distribuicoes', function (Blueprint $table) {
            if (Schema::hasColumn('_tb_demanda_distribuicoes', 'quantidade_skus')) {
                $table->dropColumn('quantidade_skus');
            }
        });
    }
};
