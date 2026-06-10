<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                📊 Painel Executivo e Relatórios
            </h2>

            {{-- 🛡️ REGRA: Apenas o Gerente visualiza o botão de Gerar/Exportar arquivo --}}
            @can('is-gerente')
                <form method="POST" action="{{ route('relatorios.exportar') }}" class="inline-block">
                    @csrf
                    <input type="hidden" name="data_inicio" value="{{ $dataInicio }}">
                    <input type="hidden" name="data_fim" value="{{ $dataFim }}">
                    <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition text-sm font-semibold shadow-sm">
                        🖨️ Gerar PDF / Exportar Relatório
                    </button>
                </form>
            @else
                <span class="text-xs font-medium text-gray-400 bg-gray-100 px-2.5 py-1 rounded-md italic">
                    Modo de Visualização (Operador)
                </span>
            @endcan
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- 📅 BARRA DE FILTRO POR PERÍODO --}}
            <div class="bg-white rounded-xl shadow p-4 border border-gray-100">
                <form method="GET" action="{{ url()->current() }}" class="flex flex-wrap items-end gap-4">
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

            {{-- CARDS FINANCEIROS DO PERÍODO --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white rounded-xl shadow p-6 border-l-4 border-blue-500">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block mb-1">Faturamento Bruto</span>
                    <h4 class="text-2xl font-black text-gray-900">R$ {{ number_format($faturamentoTotal, 2, ',', '.') }}</h4>
                </div>
                <div class="bg-white rounded-xl shadow p-6 border-l-4 border-red-500">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block mb-1">Gastos com Oficina</span>
                    <h4 class="text-2xl font-black text-gray-900">R$ {{ number_format($custoManutencao, 2, ',', '.') }}</h4>
                </div>
                <div class="bg-white rounded-xl shadow p-6 border-l-4 border-emerald-500">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block mb-1">Resultado Líquido</span>
                    <h4 class="text-2xl font-black {{ $lucroLiquido >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                        R$ {{ number_format($lucroLiquido, 2, ',', '.') }}
                    </h4>
                </div>
            </div>

            {{-- CARDS DA SITUAÇÃO ATUAL DA FROTA --}}
            <div class="bg-white rounded-xl shadow p-6 border border-gray-100">
                <h3 class="text-sm font-bold text-gray-700 mb-4 uppercase tracking-wider">📦 Estado Atual da Frota</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                    <div class="bg-gray-50 p-4 rounded-xl">
                        <span class="text-2xl font-bold text-gray-900 block">{{ $frota['total'] }}</span>
                        <span class="text-xs text-gray-400 font-medium">Veículos Cadastrados</span>
                    </div>
                    <div class="bg-emerald-50 p-4 rounded-xl">
                        <span class="text-2xl font-bold text-emerald-700 block">{{ $frota['disponiveis'] }}</span>
                        <span class="text-xs text-emerald-600 font-medium">Disponíveis</span>
                    </div>
                    <div class="bg-blue-50 p-4 rounded-xl">
                        <span class="text-2xl font-bold text-blue-700 block">{{ $frota['alugados'] }}</span>
                        <span class="text-xs text-blue-600 font-medium">Alugados (Na rua)</span>
                    </div>
                    <div class="bg-amber-50 p-4 rounded-xl">
                        <span class="text-2xl font-bold text-amber-700 block">{{ $frota['oficina'] }}</span>
                        <span class="text-xs text-amber-600 font-medium">Em Manutenção</span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>