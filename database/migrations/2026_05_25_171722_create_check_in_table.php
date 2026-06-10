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
        Schema::create('check_in', function (Blueprint $table) {
            $table->id();
            $table->timestamp('data_hora_saida')->useCurrent();
            $table->timestamp('previsao_retorno');
            $table->float('km_inicial');
            $table->string('nivel_combustivel');
            $table->text('avarias')->nullable();
            $table->text('conferencia_obj')->nullable();
            $table->enum('status',[
                'ativo',
                'encerrado'
            ])->default('ativo');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('check_in');
    }
};
