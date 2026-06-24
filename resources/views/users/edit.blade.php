<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('users.index') }}"
               class="text-gray-500 hover:text-gray-700 transition duration-150">
                ← Voltar
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Editar Usuário') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="bg-white rounded-xl shadow p-6 max-w-2xl mx-auto">
                <form method="POST" action="{{ route('users.update', $usuario) }}">
                    @csrf
                    @method('PUT')

                    @if ($errors->any())
            <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-600 rounded-lg text-sm">
                <p class="font-bold mb-2">O Laravel barrou a edição pelos seguintes motivos:</p>
                <ul class="list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

                    <div class="grid grid-cols-2 gap-4">

                        {{-- CPF (Desabilitado para evitar alteração de documento chave) --}}
                        <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">CPF</label>
                <input type="text" name="cpf" value="{{ $usuario->cpf }}" readonly
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 bg-gray-100 text-gray-500 cursor-not-allowed">
            </div>
                        {{-- Nome --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nome Completo</label>
                            <input type="text" name="name" value="{{ old('name', $usuario->name) }}"
                                   maxlength="100"
                                   required
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('name')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Data de Nascimento --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Data de Nascimento</label>
                            <input type="date" name="data_nascimento" value="{{ old('data_nascimento', $usuario->data_nascimento) }}"
                                   required
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2">
                            @error('data_nascimento')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Telefone --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Telefone</label>
                            <input type="text" name="telefone" value="{{ old('telefone', $usuario->telefone) }}"
                                   maxlength="20"
                                   placeholder="Ex: (38) 99999-9999"
                                   required
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2">
                            @error('telefone')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- CNH --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">CNH</label>
                            <input type="text" name="cnh" value="{{ old('cnh', $usuario->cnh) }}"
                                   maxlength="11"
                                   placeholder="Apenas números"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2">
                            @error('cnh')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Validade da CNH --}}
                        <div class="col-span-2 md:col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Validade da CNH</label>
                            <input type="date" name="validade_cnh" 
                                value="{{ old('validade_cnh', $usuario->validade_cnh ? \Carbon\Carbon::parse($usuario->validade_cnh)->format('Y-m-d') : '') }}"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('validade_cnh')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Login / E-mail --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">E-mail (Login)</label>
                            <input type="email" name="login" value="{{ old('login', $usuario->login) }}"
                                   required
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2">
                            @error('login')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Nível de Acesso (Role) --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nível de Acesso</label>
                            <select name="role_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2">
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}" 
                                        {{ old('role_id', $usuario->role_id) == $role->id ? 'selected' : '' }}>
                                        {{ ucfirst($role->name) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('role_id')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Senha (Opcional na Edição) --}}
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nova Senha (Deixe em branco para manter a atual)</label>
                            <input type="password" name="password" placeholder="Mínimo 6 caracteres"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2">
                            @error('password')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                    </div>

                    {{-- Botões de Ação --}}
                    <div class="mt-6 flex gap-3">
                        <button type="submit"
                                class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-150">
                            Salvar Alterações
                        </button>
                        <a href="{{ route('users.index') }}"
                           class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition duration-150">
                            Cancelar
                        </a>
                    </div>

                </form>
            </div>

        </div>
    </div>
</x-app-layout>