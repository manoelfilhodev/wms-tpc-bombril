<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('_tb_demanda', function (Blueprint $table) {
            if (! Schema::hasColumn('_tb_demanda', 'saida_veiculo_em')) {
                $table->timestamp('saida_veiculo_em')->nullable()->after('carregamento_finalizado_em');
            }

            if (! Schema::hasColumn('_tb_demanda', 'saida_veiculo_usuario_id')) {
                $table->unsignedBigInteger('saida_veiculo_usuario_id')->nullable()->after('saida_veiculo_em');
            }

            if (! Schema::hasColumn('_tb_demanda', 'saida_veiculo_observacao')) {
                $table->string('saida_veiculo_observacao', 255)->nullable()->after('saida_veiculo_usuario_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('_tb_demanda', function (Blueprint $table) {
            $columns = array_values(array_filter([
                Schema::hasColumn('_tb_demanda', 'saida_veiculo_observacao') ? 'saida_veiculo_observacao' : null,
                Schema::hasColumn('_tb_demanda', 'saida_veiculo_usuario_id') ? 'saida_veiculo_usuario_id' : null,
                Schema::hasColumn('_tb_demanda', 'saida_veiculo_em') ? 'saida_veiculo_em' : null,
            ]));

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
