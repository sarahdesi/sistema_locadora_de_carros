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
        Schema::create('alarmes', function (Blueprint $table) {
            $table->id();
            $table->string('tipo');
            $table->string('entidade');
            $table->string('entidade_id');
            $table->text('mensagem');
            $table->date('data_disparo');
            $table->boolean('visualizacao')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alarmes');
    }
};
