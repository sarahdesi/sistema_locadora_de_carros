<?php

use Livewire\Volt\Component;
use App\Models\Usuario;
use App\Models\Role;
use App\Models\LogAtividade;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    public $name;
    public $cpf;
    public $cnh;
    public $validade_cnh;
    public $data_nascimento;
    public $telefone;
    public $login;
    public $password;
    public $password_confirmation;

    protected function rules()
    {
        return [
            'name'            => 'required|string|max:255',
            'cpf'             => 'required|string|max:11|unique:usuarios,cpf',
            'cnh'             => 'required|string|max:11|unique:usuarios,cnh',
            'validade_cnh'    => 'required|date',
            'data_nascimento' => 'required|date',
            'telefone'        => 'required|string|max:20',
            'login'           => 'required|string|email|max:100|unique:usuarios,login',
            'password'        => 'required|string|min:6|confirmed',
        ];
    }

    public function salvarRegistro()
    {
        $this->validate();

        // 1. PASSO CORRIGIDO: Busca o nível de acesso "cliente" ANTES de criar o usuário
        $roleCliente = Role::where('name', 'cliente')->first();
        
        // Se por acaso não achar a role no banco, define um ID padrão ou null 
        // (Certifique-se de que você rodou os seeders das Roles no seu banco!)
        $roleId = $roleCliente ? $roleCliente->id : null;

        // 2. Cria o usuário já enviando o role_id no primeiro instante
        $usuario = Usuario::create([
            'name'            => $this->name,
            'cpf'             => $this->cpf,
            'cnh'             => $this->cnh,
            'validade_cnh'    => $this->validade_cnh,
            'data_nascimento' => $this->data_nascimento,
            'telefone'        => $this->telefone,
            'login'           => $this->login,
            'password'        => Hash::make($this->password),
            'role_id'         => $roleId, // <-- INJETADO AQUI DE FORMA OBRIGATÓRIA!
        ]);

        // 3. Autentica o usuário automaticamente na sessão
        Auth::login($usuario);

        // 4. Registra o Log de Atividade
        LogAtividade::create([
            'usuario_id' => $usuario->id,
            'acao'       => 'Cadastro de Usuário',
            'descricao'  => 'Cadastrou o usuário ' . $usuario->name . ' (CPF: ' . $usuario->cpf . ')',
        ]);

        // 5. Envia o aviso de sucesso para a sessão
        session()->flash('success', 'Sua conta foi criada com sucesso! Seja bem-vindo à nossa plataforma.');

        // 6. Redireciona para o painel de veículos
        return redirect()->route('veiculos.index');
    }
}; ?>

<div class="w-full max-w-md mx-auto bg-white rounded-2xl shadow-xl p-8 border border-gray-100">
    
    <div class="text-center mb-6">
        <h1 class="text-3xl font-bold text-blue-600">Locadora</h1>
        <p class="text-sm text-gray-500 mt-1">Sistema de Gestão de Veículos</p>
        <div class="mt-4 border-b border-gray-100 pb-2">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Criar Nova Conta</p>
        </div>
    </div>

    <form wire:submit.prevent="salvarRegistro" class="space-y-4">
        
        {{-- Nome Completo --}}
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nome Completo</label>
            <input id="name" type="text" wire:model="name" required autofocus placeholder="Digite seu nome..."
                   class="w-full text-sm border border-gray-300 rounded-xl px-4 py-2.5 bg-white text-gray-800 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition duration-150">
            @error('name') <span class="text-red-500 text-xs mt-1 block">⚠️ {{ $message }}</span> @enderror
        </div>

        {{-- E-mail / Login --}}
        <div>
            <label for="login" class="block text-sm font-medium text-gray-700 mb-1">E-mail (Login)</label>
            <input id="login" type="email" wire:model="login" required placeholder="seu@email.com"
                   class="w-full text-sm border border-gray-300 rounded-xl px-4 py-2.5 bg-white text-gray-800 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition duration-150">
            @error('login') <span class="text-red-500 text-xs mt-1 block">⚠️ {{ $message }}</span> @enderror
        </div>

        {{-- Grid: CPF e Telefone --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="cpf" class="block text-sm font-medium text-gray-700 mb-1">CPF</label>
                <input id="cpf" type="text" wire:model="cpf" required placeholder="Apenas números"
                       maxlength="11" class="w-full text-sm border border-gray-300 rounded-xl px-4 py-2.5 bg-white text-gray-800 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition duration-150">
                @error('cpf') <span class="text-red-500 text-xs mt-1 block">⚠️ {{ $message }}</span> @enderror
            </div>

            <div>
                <label for="telefone" class="block text-sm font-medium text-gray-700 mb-1">Telefone</label>
                <input id="telefone" type="text" wire:model="telefone" required placeholder="(00) 00000-0000"
                       class="w-full text-sm border border-gray-300 rounded-xl px-4 py-2.5 bg-white text-gray-800 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition duration-150">
                @error('telefone') <span class="text-red-500 text-xs mt-1 block">⚠️ {{ $message }}</span> @enderror
            </div>
        </div>

        {{-- Grid: CNH e Validade --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="cnh" class="block text-sm font-medium text-gray-700 mb-1">CNH</label>
                <input id="cnh" type="text" wire:model="cnh" required placeholder="Nº do documento"
                       maxlength="11" class="w-full text-sm border border-gray-300 rounded-xl px-4 py-2.5 bg-white text-gray-800 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition duration-150">
                @error('cnh') <span class="text-red-500 text-xs mt-1 block">⚠️ {{ $message }}</span> @enderror
            </div>

            <div>
                <label for="validade_cnh" class="block text-sm font-medium text-gray-700 mb-1">Validade da CNH</label>
                <input id="validade_cnh" type="date" wire:model="validade_cnh" required
                       class="w-full text-sm border border-gray-300 rounded-xl px-4 py-2.5 bg-white text-gray-800 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition duration-150">
                @error('validade_cnh') <span class="text-red-500 text-xs mt-1 block">⚠️ {{ $message }}</span> @enderror
            </div>
        </div>

        {{-- Data de Nascimento --}}
        <div>
            <label for="data_nascimento" class="block text-sm font-medium text-gray-700 mb-1">Data de Nascimento</label>
            <input id="data_nascimento" type="date" wire:model="data_nascimento" required
                   class="w-full text-sm border border-gray-300 rounded-xl px-4 py-2.5 bg-white text-gray-800 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition duration-150">
            @error('data_nascimento') <span class="text-red-500 text-xs mt-1 block">⚠️ {{ $message }}</span> @enderror
        </div>

        {{-- Grid Senhas --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Senha</label>
                <input id="password" type="password" wire:model="password" required placeholder="••••••••"
                       class="w-full text-sm border border-gray-300 rounded-xl px-4 py-2.5 bg-white text-gray-800 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition duration-150">
                @error('password') <span class="text-red-500 text-xs mt-1 block">⚠️ {{ $message }}</span> @enderror
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirmar</label>
                <input id="password_confirmation" type="password" wire:model="password_confirmation" required placeholder="••••••••"
                       class="w-full text-sm border border-gray-300 rounded-xl px-4 py-2.5 bg-white text-gray-800 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition duration-150">
            </div>
        </div>

        {{-- Botão de Ação --}}
        <div class="pt-2">
            <button type="submit" 
                    class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl shadow-md transition duration-150">
                Confirmar Cadastro
            </button>
        </div>

        {{-- Link para Retorno --}}
        <div class="text-center mt-4 border-t border-gray-100 pt-4">
            <p class="text-sm text-gray-500">
                Já tem uma conta? 
                <a class="text-blue-600 hover:text-blue-800 font-semibold underline transition duration-150 ml-1" href="{{ route('login') }}">
                    Fazer Login
                </a>
            </p>
        </div>
    </form>
</div>