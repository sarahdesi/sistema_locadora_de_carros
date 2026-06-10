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
        Schema::create('documento_veiculos', function (Blueprint $table) {
            $table->id();
            $table->string('veiculo_placa');
            $table->foreign('veiculo_placa')->references('placa')->on('veiculos');
            $table->string('tipo');
            $table->date('data_vencimento');
            $table->float('valor')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documento_veiculos');
    }
};
