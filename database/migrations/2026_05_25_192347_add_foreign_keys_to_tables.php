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
        Schema::table('motoristas_autorizados', function (Blueprint $table) {
            // Cria a coluna contrato_id e já faz a amarração com a tabela contratos
            $table->foreignId('contrato_id')->constrained('contratos');
        });

        Schema::table('check_in', function (Blueprint $table) {
            $table->foreignId('contrato_id')->constrained('contratos');
        });


        Schema::table('valor_extra', function (Blueprint $table) {
            $table->foreignId('contrato_id')->constrained('contratos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No método down, nós precisamos desfazer a amarração E apagar a coluna que criamos
        Schema::table('motoristas_autorizados', function (Blueprint $table) {
            $table->dropForeign(['contrato_id']); // 1. Derruba a regra
            $table->dropColumn('contrato_id');    // 2. Apaga a coluna
        });

        Schema::table('check_in', function (Blueprint $table) {
            $table->dropForeign(['contrato_id']);
            $table->dropColumn('contrato_id');
        });

        

        Schema::table('valor_extra', function (Blueprint $table) {
            $table->dropForeign(['contrato_id']);
            $table->dropColumn('contrato_id');
        });
    }
};