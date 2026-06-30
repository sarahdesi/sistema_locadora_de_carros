<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                 Controle de Manutenções da Frota
            </h2>
            {{-- Botão para registrar nova manutenção --}}
            <a href="{{ route('manutencao.create') }}" 
               class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-medium shadow-sm">
                + Enviar Carro para Oficina
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <x-alerta />

            {{-- Tabela de Ordens de Serviço --}}
            <div class="bg-white rounded-xl shadow overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                <th class="px-6 py-4">Veículo / Placa</th>
                                <th class="px-6 py-4">Tipo</th>
                                <th class="px-6 py-4">Descrição do Serviço</th>
                                <th class="px-6 py-4">Período</th>
                                <th class="px-6 py-4">Custo</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4 text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                            @forelse($manutencoes as $manutencao)
                                @php
                                    // Configuração dinâmica das cores do status
                                    $badges = [
                                        'em_andamento' => 'bg-amber-50 text-amber-700 border-amber-100',
                                        'concluida'    => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                                        'cancelada'    => 'bg-red-50 text-red-700 border-red-100',
                                    ];
                                    $labels = [
                                        'em_andamento' => 'Na Oficina',
                                        'concluida'    => 'Concluída',
                                        'cancelada'    => 'Cancelada',
                                    ];
                                    $badgeCor = $badges[$manutencao->status] ?? 'bg-gray-50 text-gray-700 border-gray-100';
                                    $statusLabel = $labels[$manutencao->status] ?? $manutencao->status;
                                @endphp

                                <tr class="hover:bg-gray-50/50 transition">
                                    {{-- Veículo --}}
                                    <td class="px-6 py-4">
                                        <span class="font-semibold text-gray-900 block">
                                            {{ $manutencao->veiculo->marca ?? 'N/A' }} {{ $manutencao->veiculo->modelo ?? '' }}
                                        </span>
                                        <span class="text-xs font-mono bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded uppercase border border-gray-200">
                                            {{ $manutencao->veiculo->placa ?? '---' }}
                                        </span>
                                    </td>

                                    {{-- Tipo --}}
                                    <td class="px-6 py-4 font-medium">
                                        {{ $manutencao->tipo_manutencao }}
                                    </td>

                                    {{-- Descrição --}}
                                    <td class="px-6 py-4 max-w-xs truncate" title="{{ $manutencao->descricao }}">
                                        {{ $manutencao->descricao }}
                                    </td>

                                    {{-- Período --}}
                                    <td class="px-6 py-4 text-xs space-y-1">
                                        <div><span class="text-gray-400">Entrada:</span> {{ \Carbon\Carbon::parse($manutencao->data_entrada)->format('d/m/Y') }}</div>
                                        <div>
                                            <span class="text-gray-400">Saída:</span> 
                                            {{ $manutencao->data_saida ? \Carbon\Carbon::parse($manutencao->data_saida)->format('d/m/Y') : '---' }}
                                        </div>
                                    </td>

                                    {{-- Custo --}}
                                    <td class="px-6 py-4 font-semibold text-gray-900">
                                        R$ {{ number_format($manutencao->custo, 2, ',', '.') }}
                                    </td>

                                    {{-- Status --}}
                                    <td class="px-6 py-4">
                                        <span class="px-2.5 py-1 text-xs font-semibold rounded-full border {{ $badgeCor }}">
                                            {{ $statusLabel }}
                                        </span>
                                    </td>

                                    {{-- Ações --}}
                                    <td class="px-6 py-4 text-right whitespace-nowrap font-medium text-sm">
                                            @if($manutencao->status === 'em_andamento')
                                                
                                                <a href="{{ route('manutencao.edit', $manutencao->id) }}" 
                                                class="inline-block bg-blue-600 text-white text-xs px-3 py-1.5 rounded-lg hover:bg-blue-700 transition shadow-sm font-semibold">
                                                    Atualizar / Dar Baixa
                                                </a>
                                            @else
                                                <span class="text-xs text-gray-400 italic">Arquivado</span>
                                            @endif
                                        </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-gray-400 italic">
                                         Nenhuma ordem de manutenção cadastrada no momento.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Paginação do Laravel --}}
                @if($manutencoes->hasPages())
                    <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
                        {{ $manutencoes->links() }}
                    </div>
                @endif

            </div>

        </div>
    </div>
</x-app-layout>