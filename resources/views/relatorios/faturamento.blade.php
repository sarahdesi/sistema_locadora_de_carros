<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                💵 Relatórios Financeiros e Faturamento
            </h2>

            @can('is-gerente')
                <form method="POST" action="{{ route('relatorios.exportar') }}" class="inline-block">
                    @csrf
                    <input type="hidden" name="data_inicio" value="{{ $dataInicio }}">
                    <input type="hidden" name="data_fim" value="{{ $dataFim }}">
                    <input type="hidden" name="contexto" value="financeiro"> 
                    
                    <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition text-sm font-semibold shadow-sm">
                        🖨️ Exportar Relatório Financeiro (PDF)
                    </button>
                </form>
            @endcan
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            {{-- NAVEGAÇÃO DE ABAS --}}
            <x-relatorios-nav :dataInicio="$dataInicio" :dataFim="$dataFim" />

            {{-- FILTROS --}}
            <div class="bg-white rounded-xl shadow p-4 border border-gray-100">
                <form method="GET" action="{{ route('relatorios.faturamento') }}" class="flex flex-wrap items-end gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Data de Início</label>
                        <input type="date" name="data_inicio" value="{{ $dataInicio }}"
                               class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Data Final</label>
                        <input type="date" name="data_fim" value="{{ $dataFim }}"
                               class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <button type="submit" class="px-5 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-medium shadow-sm">
                        🔍 Filtrar Período
                    </button>
                </form>
            </div>

            {{-- TABELA DE CONTRATOS --}}
            <div class="bg-white rounded-xl shadow overflow-hidden">
                <div class="p-4 bg-gray-50 border-b border-gray-100 flex justify-between items-center">
                    <span class="text-xs font-bold text-gray-500 uppercase">Receitas de Locação</span>
                    <span class="text-sm font-semibold text-gray-800">Total do Período: R$ {{ number_format($contratosFechados->sum('valor_total'), 2, ',', '.') }}</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wider border-b border-gray-100">
                                <th class="px-6 py-3">Contrato</th>
                                <th class="px-6 py-3">Cliente</th>
                                <th class="px-6 py-3">Veículo / Placa</th>
                                <th class="px-6 py-3">Data Encerramento</th>
                                <th class="px-6 py-3 text-right">Valor Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                            @forelse($contratosFechados as $contrato)
                                <tr class="hover:bg-gray-50/50 transition">
                                    <td class="px-6 py-4 font-bold text-gray-900">#{{ $contrato->id }}</td>
                                    <td class="px-6 py-4">{{ $contrato->usuario->name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4">
                                        {{ $contrato->veiculo->marca ?? '' }} {{ $contrato->veiculo->modelo ?? 'N/A' }}
                                        <span class="text-xs font-mono text-gray-400 block uppercase">{{ $contrato->veiculo->placa ?? '' }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-xs font-mono">
                                        {{ $contrato->updated_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-6 py-4 text-right font-bold text-emerald-600">
                                        R$ {{ number_format($contrato->valor_total, 2, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-400 italic">Nenhum faturamento registrado no período.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>