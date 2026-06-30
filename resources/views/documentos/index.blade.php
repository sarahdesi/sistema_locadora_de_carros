<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
             Gestão de Documentos de Veículos
            </h2>
            <a href="{{ route('documentos.create') }}" 
               class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-medium shadow-sm">
                + Novo Documento 
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <x-alerta />

            <div class="bg-white rounded-xl shadow overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                <th class="px-6 py-4">Veículo / Placa</th>
                                <th class="px-6 py-4">Tipo de Documento</th>
                                <th class="px-6 py-4">Vencimento</th>
                                <th class="px-6 py-4">Valor da Taxa</th>
                                <th class="px-6 py-4">Situação</th>
                                <th class="px-6 py-4 text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                            @forelse($documentos as $doc)
                                @php
                                    $vencido = \Carbon\Carbon::parse($doc->data_vencimento)->isPast();
                                @endphp
                                <tr class="hover:bg-gray-50/50 transition">
                                    {{-- Veículo --}}
                                    <td class="px-6 py-4">
                                        <span class="font-semibold text-gray-900 block">
                                            {{ $doc->veiculo->marca ?? 'N/A' }} {{ $doc->veiculo->modelo ?? '' }}
                                        </span>
                                        <span class="text-xs font-mono bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded uppercase border border-gray-200">
                                            {{ $doc->veiculo_placa }}
                                        </span>
                                    </td>

                                    {{-- Tipo --}}
                                    <td class="px-6 py-4 font-medium text-gray-800">
                                        {{ $doc->tipo }}
                                    </td>

                                    {{-- Vencimento --}}
                                    <td class="px-6 py-4 font-mono text-xs">
                                        {{ \Carbon\Carbon::parse($doc->data_vencimento)->format('d/m/Y') }}
                                    </td>

                                    {{-- Valor --}}
                                    <td class="px-6 py-4 font-semibold">
                                        {{ $doc->valor ? 'R$ ' . number_format($doc->valor, 2, ',', '.') : '---' }}
                                    </td>

                                    {{-- Situação --}}
                                    <td class="px-6 py-4">
                                        <span class="px-2.5 py-1 text-xs font-bold rounded-full {{ $vencido ? 'bg-red-50 text-red-700 border border-red-100' : 'bg-emerald-50 text-emerald-700 border border-emerald-100' }}">
                                            {{ $vencido ? '⚠️ Vencido' : '✅ Regularizado' }}
                                        </span>
                                    </td>

                                    {{-- Ações --}}
                                    <td class="p-4 text-right">
                <div class="flex justify-end gap-2">
                    {{-- Botão Editar (Ícone de Lápis) --}}
                    <a href="{{ route('documentos.edit', $doc->id) }}" 
                    class="p-1.5 bg-gray-50 text-gray-600 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition" 
                    title="Editar">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </a>

                    {{-- Botão Excluir (Ícone de Lixeira) --}}
                    <form method="POST" action="{{ route('documentos.destroy', $doc->id) }}" class="inline" onsubmit="return confirm('Tem certeza que deseja remover este documento?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="p-1.5 bg-gray-50 text-gray-600 hover:text-red-600 hover:bg-red-50 rounded-lg transition" 
                                title="Excluir">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </form>
                </div>
            </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-400 italic">
                                         Nenhum documento fiscal registrado na frota até o momento.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>