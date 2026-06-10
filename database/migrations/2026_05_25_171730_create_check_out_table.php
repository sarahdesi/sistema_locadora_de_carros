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
        Schema::create('check_out', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contrato_id')->constrained('contratos')->onDelete('cascade');
            $table->timestamp('data_hora_devolucao')->useCurrent();
            $table->float('km_final');
            $table->string('nivel_combustivel_retorno');
            $table->string('avaliacao_limpeza'); 
            $table->text('conferencia_obj_retorno')->nullable(); 
            $table->text('avarias_retorno')->nullable();
            $table->decimal('custo_adicional', 8, 2)->default(0.00); // Para cobrar lavagem, combustível faltando, etc.
            $table->text('observacoes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('check_out');
    }
};
