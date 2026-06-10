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
    Schema::create('contratos', function (Blueprint $table) {
        $table->id();
        $table->foreignId('cliente_id')->constrained('usuarios');
        $table->foreignId('veiculo_id')->constrained('veiculos');
        $table->foreignId('servidor_id')->nullable()->constrained('usuarios');
        $table->dateTime('data_hora_retorno')->nullable();
        $table->decimal('valor_diaria', 10, 2)->default(0);
        $table->decimal('valor_total', 10, 2)->default(0);
        $table->enum('status_contrato', [
            'aberto',
            'em_andamento',
            'encerrado',
            'cancelado',
        ])->default('aberto');
        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contratos');
    }
};
