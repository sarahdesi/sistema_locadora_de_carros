<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                🔧 Relatório de Custos e Manutenções
            </h2>

            @can('is-gerente')
                <form method="POST" action="{{ route('relatorios.exportar') }}" class="inline-block">
                    @csrf
                    <input type="hidden" name="data_inicio" value="{{ $dataInicio }}">
                    <input type="hidden" name="data_fim" value="{{ $dataFim }}">
                    {{-- 🎯 CORREÇÃO: Contexto atualizado para puxar o PDF de oficinas --}}
                    <input type="hidden" name="contexto" value="manutencao"> 
                    
                    <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition text-sm font-semibold shadow-sm">
                        🖨️ Exportar Relatório de Manutenções (PDF)
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

            {{-- 📊 GRID DE MANUTENÇÃO: ACUMULADO VS HISTÓRICO --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                {{-- TABELA 1: CUSTOS ACUMULADOS POR VEÍCULO (1/3 do espaço) --}}
                <div class="bg-white rounded-xl shadow overflow-hidden h-fit border border-gray-100">
                    <div class="p-4 bg-gray-50 border-b border-gray-100">
                        <span class="text-xs font-bold text-gray-500 uppercase">Top Gastos por Veículo</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-sm">
                            <thead>
                                <tr class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase border-b border-gray-100">
                                    <th class="px-4 py-3">Carro / Placa</th>
                                    <th class="px-4 py-3 text-right">Total Gasto</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-gray-700">
                                @forelse($custoPorVeiculo as $carroGasto)
                                    <tr class="hover:bg-gray-50/50 transition">
                                        <td class="px-4 py-3">
                                            <span class="font-semibold text-gray-900 block text-xs md:text-sm">{{ $carroGasto->marca }} {{ $carroGasto->modelo }}</span>
                                            <span class="text-[10px] bg-gray-100 text-gray-500 font-mono px-1.5 py-0.5 rounded uppercase tracking-wider">{{ $carroGasto->placa }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-right font-bold text-red-600">
                                            R$ {{ number_format($carroGasto->total_gasto, 2, ',', '.') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="px-4 py-6 text-center text-xs text-gray-400 italic">Sem registros no período.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- TABELA 2: HISTÓRICO CRONOLÓGICO DA OFICINA (2/3 do espaço) --}}
                <div class="bg-white rounded-xl shadow overflow-hidden lg:col-span-2 border border-gray-100">
                    <div class="p-4 bg-gray-50 border-b border-gray-100 flex justify-between items-center">
                        <span class="text-xs font-bold text-gray-500 uppercase">Histórico de Despesas de Oficina</span>
                        <span class="text-sm font-bold text-red-600">Total Pago: R$ {{ number_format($historicoOficina->sum('custo'), 2, ',', '.') }}</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-sm">
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
                            <tbody class="divide-y divide-gray-100 text-gray-700">
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
                                        {{-- 🎯 CORREÇÃO: Variável interna do loop ajustada de $manutencao para $manut --}}
                                        <td class="px-6 py-4 max-w-xs truncate" title="{{ $manut->descricao }}">{{ $manut->descricao }}</td>
                                        <td class="px-6 py-4 text-xs font-mono">{{ \Carbon\Carbon::parse($manut->data_entrada)->format('d/m/Y') }}</td>
                                        <td class="px-6 py-4 text-right font-bold text-red-600">
                                            R$ {{ number_format($manut->custo, 2, ',', '.') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-12 text-center text-gray-400 italic">
                                            Nenhuma despesa de manutenção registrada neste intervalo de tempo.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

        </div>
    </div>
</x-app-layout>