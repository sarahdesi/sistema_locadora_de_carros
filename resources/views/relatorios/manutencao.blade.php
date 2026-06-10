<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                🔧 Relatório de Custos com Manutenção
            </h2>
            <a href="{{ route('relatorios') }}" class="text-sm bg-gray-100 text-gray-700 px-3 py-1.5 rounded-lg hover:bg-gray-200 transition">
                ← Voltar ao Painel
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- BARRA DE FILTRO POR PERÍODO --}}
            <div class="bg-white rounded-xl shadow p-4 border border-gray-100">
                <form method="GET" action="{{ route('relatorios.manutencao') }}" class="flex flex-wrap items-end gap-4">
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

            {{-- HISTÓRICO FINANCEIRO DA OFICINA --}}
            <div class="bg-white rounded-xl shadow overflow-hidden">
                <div class="p-4 bg-gray-50 border-b border-gray-100 flex justify-between items-center">
                    <span class="text-xs font-bold text-gray-500 uppercase">Histórico de Despesas de Oficina</span>
                    <span class="text-sm font-bold text-red-600">Total Pago: R$ {{ number_format($historicoOficina->sum('custo'), 2, ',', '.') }}</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wider border-b border-gray-100">
                                <th class="px-6 py-3">Cód. O.S</th>
                                <th class="px-6 py-3">Veículo / Placa</th>
                                <th class="px-6 py-3">Tipo</th>
                                <th class="px-6 py-3">Descrição Simplificada</th>
                                <th class="px-6 py-3">Data Entrada</th>
                                <th class="px-6 py-3 text-right">Custo Real</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                            @forelse($historicoOficina as $manut)
                                <tr class="hover:bg-gray-50/50 transition">
                                    <td class="px-6 py-4 font-mono text-xs text-gray-500">#{{ $manut->id }}</td>
                                    <td class="px-6 py-4">
                                        <span class="font-semibold text-gray-900 block">{{ $manut->marca }} {{ $manut->modelo }}</span>
                                        <span class="text-xs font-mono uppercase text-gray-400">{{ $manut->placa }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-0.5 text-xs font-medium rounded bg-gray-100 text-gray-700 border border-gray-200">
                                            {{ $manut->tipo_manutencao }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 max-w-xs truncate" title="{{ $manut->descricao }}">{{ $manutencao->descricao ?? $manut->descricao }}</td>
                                    <td class="px-6 py-4 text-xs font-mono">{{ \Carbon\Carbon::parse($manut->data_entrada)->format('d/m/Y') }}</td>
                                    <td class="px-6 py-4 text-right font-bold text-red-600">
                                        R$ {{ number_format($manut->custo, 2, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-400 italic">
                                        Nenhuma despesa de manutenção registada neste intervalo de tempo.
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