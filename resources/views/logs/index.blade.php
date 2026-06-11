<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            📋 Histórico de Logs e Auditoria do Sistema
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="bg-white rounded-xl shadow overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                <th class="px-6 py-4">Data / Hora</th>
                                <th class="px-6 py-4">Funcionário</th>
                                <th class="px-6 py-4">Ação</th>
                                <th class="px-6 py-4">Descrição do Evento</th>
                            
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                            @forelse($logs as $log)
                                <tr class="hover:bg-gray-50/50 transition">
                                    {{-- Data e Hora --}}
                                    <td class="px-6 py-4 font-mono text-xs text-gray-500 whitespace-nowrap">
                                        {{ $log->created_at->format('d/m/Y H:i:s') }}
                                    </td>
                                    {{-- Usuário --}}
                                    <td class="px-6 py-4 font-semibold text-gray-900">
                                        {{ $log->usuario->name ?? 'Sistema / Automático' }}
                                    </td>
                                    {{-- Categoria da Ação --}}
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-0.5 text-xs font-bold rounded bg-blue-50 text-blue-700 border border-blue-100 uppercase">
                                            {{ $log->acao }}
                                        </span>
                                    </td>
                                    {{-- Detalhes --}}
                                    <td class="px-6 py-4 text-gray-600">
                                        {{ $log->descricao }}
                                    </td>
                                    </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-400 italic">
                                        📭 Nenhuma atividade registrada no sistema ainda.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Paginação do Laravel no rodapé --}}
                <div class="p-4 bg-gray-50 border-t border-gray-100">
                    {{ $logs->links() }}
                </div>
            </div>

        </div>
    </div>
</x-app-layout>