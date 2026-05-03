<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('_tb_posicoes', function (Blueprint $table) {
            if (!Schema::hasColumn('_tb_posicoes', 'codigo_posicao')) {
                $table->string('codigo_posicao', 100)->nullable(); // cria como nullable por enquanto
            }
        });

        // Popular codigo_posicao para registros existentes
        $posicoes = DB::table('_tb_posicoes')->select('id','corredor','prateleira','nivel')->get();

        foreach ($posicoes as $p) {
            $codigo = null;
            if (!is_null($p->corredor) && !is_null($p->prateleira) && !is_null($p->nivel)) {
                $codigo = sprintf('%s-%s-%s', $p->corredor, $p->prateleira, $p->nivel);
            } else {
                $codigo = 'POS-' . str_pad((string)$p->id, 6, '0', STR_PAD_LEFT);
            }
            DB::table('_tb_posicoes')->where('id', $p->id)->update(['codigo_posicao' => $codigo]);
        }

        // Agora tornar NOT NULL e opcionalmente unique
        Schema::table('_tb_posicoes', function (Blueprint $table) {
            $table->string('codigo_posicao', 100)->nullable(false)->change();
            // $table->unique('codigo_posicao'); // descomente se quiser garantir unicidade
        });
    }

    public function down(): void
    {
        Schema::table('_tb_posicoes', function (Blueprint $table) {
            if (Schema::hasColumn('_tb_posicoes', 'codigo_posicao')) {
                // $table->dropUnique(['codigo_posicao']); // se criou unique acima
                $table->dropColumn('codigo_posicao');
            }
        });
    }
};
