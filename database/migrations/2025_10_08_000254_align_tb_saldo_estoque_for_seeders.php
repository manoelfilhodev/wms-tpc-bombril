<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('_tb_saldo_estoque', function (Blueprint $table) {
            if (!Schema::hasColumn('_tb_saldo_estoque', 'unidade_id')) {
                $table->unsignedBigInteger('unidade_id')->nullable()->after('id');
            }
            if (!Schema::hasColumn('_tb_saldo_estoque', 'material_id')) {
                $table->unsignedBigInteger('material_id')->nullable()->after('unidade_id');
            }
            if (!Schema::hasColumn('_tb_saldo_estoque', 'posicao_id')) {
                $table->unsignedBigInteger('posicao_id')->nullable()->after('material_id');
            }
            if (!Schema::hasColumn('_tb_saldo_estoque', 'quantidade')) {
                $table->decimal('quantidade', 15, 3)->default(0)->after('posicao_id');
            }
            if (!Schema::hasColumn('_tb_saldo_estoque', 'data_entrada')) {
                $table->timestamp('data_entrada')->nullable()->after('quantidade');
            }
        });
    }

    public function down(): void
    {
        Schema::table('_tb_saldo_estoque', function (Blueprint $table) {
            foreach (['data_entrada','quantidade','posicao_id','material_id','unidade_id'] as $col) {
                if (Schema::hasColumn('_tb_saldo_estoque', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
