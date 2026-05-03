<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('_tb_demanda_status_history', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('demanda_id')->nullable()->index();
            $table->string('status', 50)->nullable();
            $table->integer('changed_by')->nullable()->index();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('_tb_demanda_status_history');
    }
};
