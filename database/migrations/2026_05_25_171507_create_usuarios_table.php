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
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->string('cpf',11)->unique();
            $table->string('name');
            $table->date('data_nascimento');
            $table->string('telefone',20);
            $table->string('login')->unique();
            $table->string('password');
            $table->timestamp('criado_em')->useCurrent();
            $table->string('cnh')->nullable();
            $table->date('validade_cnh')->nullable()->unique();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
