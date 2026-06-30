<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
             Painel Executivo e Relatórios
            </h2>

            @can('is-gerente')
                <form method="POST" action="{{ route('relatorios.exportar') }}" class="inline-block">
                    @csrf
                    <input type="hidden" name="data_inicio" value="{{ $dataInicio }}">
                    <input type="hidden" name="data_fim" value="{{ $dataFim }}">
                    <input type="hidden" name="contexto" value="visao_geral"> 
                    
                    <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition text-sm font-semibold shadow-sm">
                        🖨️ Gerar PDF Resumo
                    </button>
                </form>
            @endcan
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{--  MENU DE ABAS CLICÁVEIS NO TOPO --}}
            <x-relatorios-nav :dataInicio="$dataInicio" :dataFim="$dataFim" />

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

            {{-- 💰 SEÇÃO: CARDS FINANCEIROS BÁSICOS --}}
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

            {{-- 📦 SEÇÃO: SITUAÇÃO ATUAL DA FROTA (RESUMO) --}}
            <div class="bg-white rounded-xl shadow p-6 border border-gray-100">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 gap-2">
                    <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider">📦 Estado Atual da Frota</h3>
                    <div class="bg-blue-50 text-blue-700 text-xs font-bold px-3 py-1 rounded-lg border border-blue-100">
                        🎯 Taxa de Ocupação Operacional: <span class="text-sm font-black">{{ $frota['taxa_ocupacao'] }}%</span>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 md:grid-cols-5 gap-4 text-center">
                    <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                        <span class="text-2xl font-black text-gray-900 block">{{ $frota['total'] }}</span>
                        <span class="text-xs text-gray-400 font-semibold">Frota Total</span>
                    </div>
                    <div class="bg-emerald-50 p-4 rounded-xl border border-emerald-100">
                        <span class="text-2xl font-black text-emerald-700 block">{{ $frota['disponiveis'] }}</span>
                        <span class="text-xs text-emerald-600 font-semibold">Disponíveis</span>
                    </div>
                    <div class="bg-blue-50 p-4 rounded-xl border border-blue-100">
                        <span class="text-2xl font-black text-blue-700 block">{{ $frota['alugados'] }}</span>
                        <span class="text-xs text-blue-600 font-semibold">Locados (Na rua)</span>
                    </div>
                    <div class="bg-amber-50 p-4 rounded-xl border border-amber-100">
                        <span class="text-2xl font-black text-amber-700 block">{{ $frota['oficina'] }}</span>
                        <span class="text-xs text-amber-600 font-semibold">Em Oficina</span>
                    </div>
                    <div class="bg-purple-50 p-4 rounded-xl border border-purple-100">
                        <span class="text-2xl font-black text-purple-700 block">{{ $frota['reservados'] }}</span>
                        <span class="text-xs text-purple-600 font-semibold">Reservados</span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>