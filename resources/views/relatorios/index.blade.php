<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                📊 Painel Executivo e Relatórios
            </h2>

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

            {{-- 💰 SEÇÃO: CARDS FINANCEIROS --}}
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

            {{-- 📈 SEÇÃO: DETALHAMENTOS ADICIONAIS FINANCEIROS E INADIMPLÊNCIA --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white rounded-xl shadow p-6 border border-gray-100">
                    <h3 class="text-sm font-bold text-gray-700 mb-4 uppercase tracking-wider">➕ Receitas Adicionais do Período</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-gray-600">
                            <thead>
                                <tr class="border-b border-gray-100 text-gray-400 text-xs uppercase">
                                    <th class="pb-3">Tipo de Cobrança</th>
                                    <th class="pb-3 text-right">Total Faturado</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @forelse($receitasAdicionais as $extra)
                                    <tr>
                                        <td class="py-3 font-medium text-gray-800 capitalize">{{ str_replace('_', ' ', $extra->tipo_de_cobrança) }}</td>
                                        <td class="py-3 text-right font-semibold text-emerald-600">R$ {{ number_format($extra->total_faturado, 2, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="py-4 text-center text-xs text-gray-400 italic">Nenhuma receita adicional computada.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow p-6 border border-gray-100">
                    <h3 class="text-sm font-bold text-gray-700 mb-4 uppercase tracking-wider">⚠️ Contas a Receber / Pendências</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-gray-600">
                            <thead>
                                <tr class="border-b border-gray-100 text-gray-400 text-xs uppercase">
                                    <th class="pb-3">Contrato</th>
                                    <th class="pb-3">Previsão Retorno</th>
                                    <th class="pb-3">Status</th>
                                    <th class="pb-3 text-right">Valor Aberto</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @forelse($contasAReceber as $conta)
                                    <tr>
                                        <td class="py-3 font-medium text-gray-900">#{{ $conta->id }}</td>
                                        <td class="py-3 text-xs {{ \Carbon\Carbon::parse($conta->data_hora_retorno)->isPast() ? 'text-red-500 font-semibold' : 'text-gray-500' }}">
                                            {{ \Carbon\Carbon::parse($conta->data_hora_retorno)->format('d/m/Y H:i') }}
                                        </td>
                                        <td class="py-3">
                                            <span class="text-[10px] px-2 py-0.5 rounded-full font-bold uppercase {{ $conta->status_contrato === 'ativo' ? 'bg-blue-50 text-blue-600' : 'bg-amber-50 text-amber-600' }}">
                                                {{ $conta->status_contrato }}
                                            </span>
                                        </td>
                                        <td class="py-3 text-right font-semibold text-gray-900">R$ {{ number_format($conta->valor_total, 2, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-4 text-center text-xs text-gray-400 italic">Sem faturamento pendente no momento.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- 📦 SEÇÃO: SITUAÇÃO ATUAL DA FROTA --}}
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

            {{-- 🔧 & 🏎️ SEÇÃO SEGUNDA CAMADA: CUSTO DE MANUTENÇÃO VS RANKING DE RODAGEM --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                
                <div class="bg-white rounded-xl shadow p-6 border border-gray-100">
                    <h3 class="text-sm font-bold text-gray-700 mb-4 uppercase tracking-wider">🔧 Top Custos de Oficina por Veículo</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-gray-600">
                            <thead>
                                <tr class="border-b border-gray-100 text-gray-400 text-xs uppercase">
                                    <th class="pb-3">Veículo / Placa</th>
                                    <th class="pb-3 text-right">Histórico de Gastos</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @forelse($custoPorVeiculo as $carroGasto)
                                    <tr class="hover:bg-gray-50/50 transition">
                                        <td class="py-2.5">
                                            <span class="font-semibold text-gray-800 block text-xs md:text-sm">{{ $carroGasto->marca }} {{ $carroGasto->modelo }}</span>
                                            <span class="text-[10px] bg-gray-100 text-gray-500 font-mono px-1.5 py-0.5 rounded uppercase tracking-wider">{{ $carroGasto->placa }}</span>
                                        </td>
                                        <td class="py-2.5 text-right font-bold text-red-600">
                                            R$ {{ number_format($carroGasto->total_gasto, 2, ',', '.') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="py-4 text-center text-xs text-gray-400 italic">Nenhum custo registrado no intervalo selecionado.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow p-6 border border-gray-100">
                    <h3 class="text-sm font-bold text-gray-700 mb-4 uppercase tracking-wider">🏎️ Ranking de Rodagem (Desgaste da Frota)</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-gray-600">
                            <thead>
                                <tr class="border-b border-gray-100 text-gray-400 text-xs uppercase">
                                    <th class="pb-3">Veículo</th>
                                    <th class="pb-3 text-center">Locações</th>
                                    <th class="pb-3 text-right">KM Rodado no Período</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @forelse($rankingRodagem as $rodado)
                                    <tr class="hover:bg-gray-50/50 transition">
                                        <td class="py-2.5">
                                            <span class="font-semibold text-gray-800 block text-xs md:text-sm">{{ $rodado->veiculo->modelo ?? 'N/A' }}</span>
                                            <span class="text-[10px] bg-indigo-50 text-indigo-600 font-mono px-1.5 py-0.5 rounded uppercase font-bold tracking-wider">{{ $rodado->veiculo->placa ?? 'N/A' }}</span>
                                        </td>
                                        <td class="py-2.5 text-center text-gray-500 font-medium text-xs md:text-sm">
                                            {{ $rodado->total_locacoes }}x
                                        </td>
                                        <td class="py-2.5 text-right font-black text-indigo-600">
                                            {{ number_format($rodado->total_km_rodados, 0, ',', '.') }} KM
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="py-4 text-center text-xs text-gray-400 italic">Sem registros de movimentações finalizadas no período.</td>
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