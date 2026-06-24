<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                🚗 Relatório de Produtividade da Frota
            </h2>

            @can('is-gerente')
                <form method="POST" action="{{ route('relatorios.exportar') }}" class="inline-block">
                    @csrf
                    <input type="hidden" name="data_inicio" value="{{ $dataInicio }}">
                    <input type="hidden" name="data_fim" value="{{ $dataFim }}">
                    <input type="hidden" name="contexto" value="frota"> 
                    
                    <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition text-sm font-semibold shadow-sm">
                        🖨️ Exportar Relatório de Frota (PDF)
                    </button>
                </form>
            @endcan
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- 🎯 NAVEGAÇÃO DE ABAS COMPARTILHADAS --}}
            <x-relatorios-nav :dataInicio="$dataInicio" :dataFim="$dataFim" />

            {{-- 📅 BARRA DE FILTRO POR PERÍODO --}}
            <div class="bg-white rounded-xl shadow p-4 border border-gray-100">
                <form method="GET" action="{{ route('relatorios.frota') }}" class="flex flex-wrap items-end gap-4">
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

            {{-- 📊 RANKING DE PRODUTIVIDADE --}}
            <div class="bg-white rounded-xl shadow overflow-hidden">
                <div class="p-4 bg-gray-50 border-b border-gray-100">
                    <span class="text-xs font-bold text-gray-500 uppercase">Desempenho Comercial por Veículo (Top Rentabilidade)</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wider border-b border-gray-100">
                                <th class="px-6 py-3">Posição</th>
                                <th class="px-6 py-3">Veículo</th>
                                <th class="px-6 py-3">Placa</th>
                                <th class="px-6 py-3 text-center">Qtd. Locações</th>
                                <th class="px-6 py-3 text-right">Faturamento Acumulado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                            @forelse($rankingVeiculos as $index => $item)
                                <tr class="hover:bg-gray-50/50 transition">
                                    <td class="px-6 py-4 font-bold text-gray-400">#{{ $index + 1 }}</td>
                                    <td class="px-6 py-4 font-semibold text-gray-900">
                                        {{ $item->veiculo->marca ?? 'N/A' }} {{ $item->veiculo->modelo ?? '' }}
                                    </td>
                                    <td class="px-6 py-4 font-mono text-xs uppercase">{{ $item->veiculo->placa ?? '---' }}</td>
                                    <td class="px-6 py-4 text-center font-medium text-blue-600">{{ $item->total_locacoes }}x</td>
                                    <td class="px-6 py-4 text-right font-black text-gray-900">
                                        R$ {{ number_format($item->total_gerado, 2, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-400 italic">
                                        Nenhuma movimentação de frota no período indicado.
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