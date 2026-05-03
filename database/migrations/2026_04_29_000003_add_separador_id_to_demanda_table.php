<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('_tb_demanda', function (Blueprint $table) {
            $table->unsignedBigInteger('separador_id')->nullable()->after('total_itens_com_sobra');
            $table->index('separador_id');
        });
    }

    public function down(): void
    {
        Schema::table('_tb_demanda', function (Blueprint $table) {
            $table->dropIndex(['separador_id']);
            $table->dropColumn('separador_id');
        });
    }
};

