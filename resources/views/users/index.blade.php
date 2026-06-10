<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Usuários') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Controle de Usuários</h1>
                @if(auth()->user()->isGerente() || auth()->user()->isOperador())
                    <a href="{{ route('users.create') }}"
                       class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-150">
                        + Novo Usuário
                    </a>
                @endif
            </div>

            <x-alerta />

            <div class="bg-white rounded-xl shadow overflow-hidden">
                <table class="w-full text-sm text-left text-gray-600">
                    <thead class="bg-gray-50 text-gray-700 uppercase text-xs">
                        <tr>
                            <th class="px-6 py-3">CPF</th>
                            <th class="px-6 py-3">Nome</th>
                            <th class="px-6 py-3">Data de Nascimento</th>
                            <th class="px-6 py-3">CNH</th>
                            <th class="px-6 py-3">Validade CNH</th>
                            <th class="px-6 py-3">Telefone</th>
                            <th class="px-6 py-3 text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($usuarios as $usuario)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 font-medium text-gray-900">{{ $usuario->cpf }}</td>
                                <td class="px-6 py-4">{{ $usuario->name }}</td>
                                <td class="px-6 py-4">
                                    {{ $usuario->data_nascimento ? \Carbon\Carbon::parse($usuario->data_nascimento)->format('d/m/Y') : 'N/I' }}
                                </td>
                                <td class="px-6 py-4">{{ $usuario->cnh ?? 'N/I' }}</td>
                                <td class="px-6 py-4">
                                    {{ $usuario->validade_cnh ? \Carbon\Carbon::parse($usuario->validade_cnh)->format('d/m/Y') : 'N/I' }}
                                </td>
                                <td class="px-6 py-4">{{ $usuario->telefone ?? 'N/I' }}</td>
                                
                                <td class="px-6 py-4 flex gap-3 items-center justify-center">
                                    <a href="{{ route('users.show', $usuario) }}"
                                       class="text-blue-600 hover:underline font-medium">Ver</a>

                                    @if(auth()->user()->isGerente() || auth()->user()->isOperador())
                                        <a href="{{ route('users.edit', $usuario) }}"
                                           class="text-yellow-600 hover:underline font-medium">Editar</a>

                                        <form method="POST"
                                              action="{{ route('users.destroy', $usuario) }}"
                                              onsubmit="return confirm('Tem certeza que deseja excluir este usuário?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="text-red-600 hover:underline font-medium">
                                                Excluir
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-gray-400">
                                    Nenhum usuário cadastrado ainda.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</x-app-layout>