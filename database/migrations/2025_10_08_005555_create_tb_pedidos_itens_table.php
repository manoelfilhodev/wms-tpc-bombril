<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('_tb_pedidos_itens')) {
            Schema::create('_tb_pedidos_itens', function (Blueprint $table) {
                $table->increments('id');             // PK
                $table->integer('pedido_id')->index(); // FK para _tb_pedidos(id)
                $table->integer('material_id')->index(); // FK para _tb_materiais (qualquer PK int)
                $table->integer('quantidade')->default(0);
                $table->decimal('preco_unitario', 12, 2)->nullable(); // opcional
                $table->timestamps(); // created_at / updated_at
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('_tb_pedidos_itens');
    }
};
