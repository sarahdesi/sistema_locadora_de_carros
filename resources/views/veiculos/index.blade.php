<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Veículos') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Frota de Veículos</h1>
                @if(auth()->user()->isGerente() || auth()->user()->isOperador())
                    <a href="{{ route('veiculos.create') }}"
                       class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-150">
                        + Novo Veículo
                    </a>
                @endif
            </div>

            <x-alerta />

            <div class="bg-white rounded-xl shadow overflow-hidden">
                <table class="w-full text-sm text-left text-gray-600">
                    <thead class="bg-gray-50 text-gray-700 uppercase text-xs">
                        <tr>
                            <th class="px-6 py-3">Placa</th>
                            <th class="px-6 py-3">Modelo</th>
                            <th class="px-6 py-3">Marca</th>
                            <th class="px-6 py-3">Ano</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($veiculos as $veiculo)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 font-medium text-gray-900">{{ $veiculo->placa }}</td>
                                <td class="px-6 py-4">{{ $veiculo->modelo }}</td>
                                <td class="px-6 py-4">{{ $veiculo->marca }}</td>
                                <td class="px-6 py-4">{{ $veiculo->ano }}</td>
                                <td class="px-6 py-4">
                                    @php
                                        // Ajustado com classes completas para o Tailwind não falhar na compilação
                                        $cores = [
                                            'disponivel' => 'bg-green-100 text-green-700',
                                            'locado'     => 'bg-blue-100 text-blue-700',
                                            'manutencao' => 'bg-yellow-100 text-yellow-700',
                                            'reservado'  => 'bg-purple-100 text-purple-700',
                                        ];
                                        $classeBadge = $cores[$veiculo->status] ?? 'bg-gray-100 text-gray-700';
                                    @endphp
                                    <span class="px-2 py-1 rounded-full text-xs font-medium {{ $classeBadge }}">
                                        {{ ucfirst($veiculo->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 flex gap-3 items-center">
                                    <a href="{{ route('veiculos.show', $veiculo) }}"
                                       class="text-blue-600 hover:underline font-medium">Ver</a>

                                    @if(auth()->user()->isGerente() || auth()->user()->isOperador())
                                        <a href="{{ route('veiculos.edit', $veiculo) }}"
                                           class="text-yellow-600 hover:underline font-medium">Editar</a>

                                        <form method="POST"
                                              action="{{ route('veiculos.destroy', $veiculo) }}"
                                              onsubmit="return confirm('Tem certeza que deseja excluir este veículo?')">
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
                                <td colspan="6" class="px-6 py-8 text-center text-gray-400">
                                    Nenhum veículo cadastrado ainda.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</x-app-layout>