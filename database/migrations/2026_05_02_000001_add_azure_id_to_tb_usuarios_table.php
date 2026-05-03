<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('_tb_usuarios', function (Blueprint $table) {
            if (! Schema::hasColumn('_tb_usuarios', 'azure_id')) {
                $table->string('azure_id', 100)->nullable()->unique()->after('email');
            }
        });
    }

    public function down(): void
    {
        Schema::table('_tb_usuarios', function (Blueprint $table) {
            if (Schema::hasColumn('_tb_usuarios', 'azure_id')) {
                $table->dropUnique('_tb_usuarios_azure_id_unique');
                $table->dropColumn('azure_id');
            }
        });
    }
};
