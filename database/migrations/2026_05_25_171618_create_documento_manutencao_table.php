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
    Schema::create('documento_manutencao', function (Blueprint $table) {
        $table->id();
        
        // Chave estrangeira ligando a manutenção ao veículo
        $table->foreignId('veiculo_id')->constrained('veiculos')->onDelete('cascade');
        
        $table->string('tipo_manutencao'); 
        $table->text('descricao');         
        $table->date('data_entrada');
        $table->date('data_saida')->nullable(); 
        $table->decimal('custo', 10, 2)->default(0.00);
        
        $table->enum('status', [
            'em_andamento',
            'concluida',
            'cancelada'
        ])->default('em_andamento');
        
        $table->timestamps();
    });
}

        public function down(): void
        {
            Schema::dropIfExists('manutencoes');
        }
};
