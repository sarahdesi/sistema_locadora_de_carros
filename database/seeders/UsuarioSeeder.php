<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

class UsuarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // CORRIGIDO: Agora enviando o 'guard_name' obrigatório exigido pelo banco de dados
        Role::firstOrCreate(['name' => 'gerente', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'operador', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'cliente', 'guard_name' => 'web']);

        // O restante do código permanece igual buscando pelo 'name'
        $roleGerente = Role::where('name', 'gerente')->first();
        Usuario::create([
            'cpf'             => '12345678901',
            'name'            => 'Gerente Teste',
            'data_nascimento' => '1990-01-01',
            'telefone'        => '38999999999',
            'login'           => 'gerente@locadora.com',
            'password'        => bcrypt('123456'),
            'role_id'         => $roleGerente->id,
        ]);

        $roleOperador = Role::where('name', 'operador')->first();
        Usuario::create([
            'cpf'             => '98765432100',
            'name'            => 'Operador Teste',
            'data_nascimento' => '1995-05-15',
            'telefone'        => '38988888888',
            'login'           => 'operador@locadora.com',
            'password'        => bcrypt('123456'),
            'role_id'         => $roleOperador->id,
        ]);

        $roleCliente = Role::where('name', 'cliente')->first();
        Usuario::create([
            'cpf'             => '11122233344',
            'name'            => 'Cliente Teste',
            'data_nascimento' => '2000-10-20',
            'telefone'        => '38977777777',
            'login'           => 'cliente@locadora.com',
            'password'        => bcrypt('123456'),
            'role_id'         => $roleCliente->id,
        ]);
    }
}