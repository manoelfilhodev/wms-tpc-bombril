<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $adicionarZonaPartida = ! Schema::hasColumn('_tb_wms_clientes_transit_time', 'zona_partida');
        $adicionarZonaTransporte = ! Schema::hasColumn('_tb_wms_clientes_transit_time', 'zona_transporte');
        $adicionarCicloInte = ! Schema::hasColumn('_tb_wms_clientes_transit_time', 'ciclo_inte');

        Schema::table('_tb_wms_clientes_transit_time', function (Blueprint $table) use ($adicionarZonaPartida, $adicionarZonaTransporte, $adicionarCicloInte) {
            $table->string('nome_cliente', 150)->nullable()->change();

            if ($adicionarZonaPartida) {
                $table->string('zona_partida', 50)->nullable()->after('nome_cliente');
            }

            if ($adicionarZonaTransporte) {
                $table->string('zona_transporte', 50)->nullable()->after('cidade');
            }

            if ($adicionarCicloInte) {
                $table->integer('ciclo_inte')->nullable()->after('zona_transporte');
            }
        });

        Schema::table('_tb_wms_clientes_transit_time', function (Blueprint $table) use ($adicionarZonaPartida, $adicionarZonaTransporte) {
            if ($adicionarZonaPartida) {
                $table->index('zona_partida', '_tb_wms_clientes_transit_time_zona_partida_index');
            }

            if ($adicionarZonaTransporte) {
                $table->index('zona_transporte', '_tb_wms_clientes_transit_time_zona_transporte_index');
            }
        });
    }

    public function down(): void
    {
        Schema::table('_tb_wms_clientes_transit_time', function (Blueprint $table) {
            $table->dropIndex('_tb_wms_clientes_transit_time_zona_partida_index');
            $table->dropIndex('_tb_wms_clientes_transit_time_zona_transporte_index');
            $table->dropColumn(['zona_partida', 'zona_transporte', 'ciclo_inte']);
            $table->string('nome_cliente', 150)->nullable(false)->change();
        });
    }
};
