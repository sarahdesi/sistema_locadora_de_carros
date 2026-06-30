<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Contratos de Locação') }}
            </h2>
            {{-- Botão para abrir nova locação disponível para todos --}}
            <a href="{{ route('contratos.create') }}"
               class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-150 text-sm font-medium shadow-sm">
                + Nova Locação
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Alerta de Sucesso --}}
            @if(session('success'))
                <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl flex items-center gap-2 text-sm shadow-sm">
                    <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            {{-- Tabela de Contratos --}}
            <div class="bg-white rounded-xl shadow overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-600">
                        <thead class="bg-gray-50 text-gray-700 uppercase text-xs font-semibold tracking-wider border-b border-gray-100">
                            <tr>
                                <th class="px-6 py-4">Cód / Contrato</th>
                                <th class="px-6 py-4">Cliente</th>
                                <th class="px-6 py-4">Veículo</th>
                                <th class="px-6 py-4">Período de Locação</th>
                                <th class="px-6 py-4">Valores</th>
                                <th class="px-6 py-4 text-center">Status</th>
                                <th class="px-6 py-4 text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($contratos as $contrato)
                                <tr class="hover:bg-gray-50 transition duration-100">
                                    {{-- ID do Contrato --}}
                                    <td class="px-6 py-4 font-semibold text-gray-900">
                                        #{{ $contrato->id }}
                                    </td>

                                    {{-- Dados do Cliente --}}
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-900">{{ $contrato->cliente->name ?? 'N/A' }}</div>
                                        <div class="text-gray-400 text-xs mt-0.5">CPF: {{ $contrato->cliente->cpf ?? 'N/A' }}</div>
                                    </td>

                                    {{-- Dados do Veículo --}}
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-800">
                                            {{ $contrato->veiculo->marca ?? 'N/A' }} {{ $contrato->veiculo->modelo ?? '' }}
                                        </div>
                                        <span class="inline-block bg-gray-100 text-gray-600 text-xs px-2 py-0.5 rounded font-mono mt-0.5 uppercase tracking-wider">
                                            {{ $contrato->veiculo->placa ?? 'N/A' }}
                                        </span>
                                    </td>

                                    {{-- Período (Início ao Fim) --}}
                                    <td class="px-6 py-4 text-gray-500">
                                        <div class="text-xs"><span class="font-medium text-gray-700">Retirada:</span> {{ $contrato->created_at->format('d/m/Y H:i') }}</div>
                                        <div class="text-xs mt-1"><span class="font-medium text-gray-700">Devolução:</span> {{ \Carbon\Carbon::parse($contrato->data_hora_retorno)->format('d/m/Y H:i') }}</div>
                                    </td>

                                    {{-- Valores --}}
                                    <td class="px-6 py-4">
                                        <div class="text-xs text-gray-400">Diária: R$ {{ number_format($contrato->valor_diaria, 2, ',', '.') }}</div>
                                        <div class="font-semibold text-emerald-600 mt-0.5">
                                            Total: R$ {{ $contrato->valor_total ? number_format($contrato->valor_total, 2, ',', '.') : 'Calculando...' }}
                                        </div>
                                    </td>

                                    {{-- Status do Contrato --}}
                                    <td class="px-6 py-4 text-center">
                                       @if($contrato->status_contrato === 'aberto') {{-- mudei aqui --}}
                                        <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-amber-50 text-amber-700 border border-amber-100">
                                            Aberto
                                        </span>
                                    @elseif($contrato->status_contrato === 'em_andamento')
                                        <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-blue-50 text-blue-700 border border-blue-100">
                                            Em Andamento
                                        </span>
                                    @elseif($contrato->status_contrato === 'encerrado')
                                        <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-emerald-50 text-emerald-700 border border-emerald-100">
                                            Encerrado
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-red-50 text-red-700 border border-red-100">
                                            Cancelado
                                        </span>
                                    @endif 
                                    {{-- ate aqui --}}
                                    </td>

                                    {{-- Ações Condicionais --}}
                                    <td class="p-4 text-right">
                            <div class="flex justify-end gap-2">
                                {{-- Botão Ver Detalhes (Ícone de Olho) --}}
                                <a href="{{ route('contratos.show', $contrato) }}" 
                                class="p-1.5 bg-gray-50 text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition" 
                                title="Ver Detalhes">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </a>

                                {{-- Apenas Gerente/Operador podem Editar --}}
                                                    @can('is-staff')
                                                        <a href="{{ route('contratos.edit', $contrato) }}" 
                                                        class="p-1.5 bg-gray-50 text-gray-600 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition" 
                                                        title="Editar">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                            </svg>
                                                        </a>
                                                    @endcan
                                                </div>
                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                                        <div class="flex flex-col items-center justify-center">
                                            <svg class="w-8 h-8 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            <p class="text-sm">Nenhum contrato de locação encontrado.</p>
                                        </div>
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