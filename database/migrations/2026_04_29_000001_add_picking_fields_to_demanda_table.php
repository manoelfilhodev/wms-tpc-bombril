<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('_tb_demanda', function (Blueprint $table) {
            $table->boolean('possui_sobra')->default(false)->after('status');
            $table->timestamp('separacao_iniciada_em')->nullable()->after('possui_sobra');
            $table->timestamp('separacao_finalizada_em')->nullable()->after('separacao_iniciada_em');
            $table->enum('separacao_resultado', ['PARCIAL', 'COMPLETA'])->nullable()->after('separacao_finalizada_em');
            $table->unsignedInteger('total_itens')->default(0)->after('separacao_resultado');
            $table->unsignedInteger('total_itens_com_sobra')->default(0)->after('total_itens');
        });
    }

    public function down(): void
    {
        Schema::table('_tb_demanda', function (Blueprint $table) {
            $table->dropColumn([
                'possui_sobra',
                'separacao_iniciada_em',
                'separacao_finalizada_em',
                'separacao_resultado',
                'total_itens',
                'total_itens_com_sobra',
            ]);
        });
    }
};

