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
        Schema::create('veiculos', function (Blueprint $table) {
            $table->id();
            $table->string('placa', 7)->unique();
            $table->string('modelo');
            $table->string('marca');
            $table->string('renavam',11)->unique;
            $table->string('cor');
            $table->integer('ano');
            $table->string('combustivel');
            $table->float('odometro')->default(0);
            $table->enum('status',[
                'disponivel',
                'locado',
                'em_manutencao',
                'reservado'
            ])->default('disponivel');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('veiculos');
    }
};
