@props(['dataInicio', 'dataFim'])

<div class="border-b border-gray-200 mb-6">
    <ul class="flex flex-wrap -mb-px text-sm font-medium text-center text-gray-500">
        <li class="me-2">
            <a href="{{ route('relatorios.index', ['data_inicio' => $dataInicio, 'data_fim' => $dataFim]) }}" 
               class="inline-flex items-center p-4 border-b-2 rounded-t-lg transition {{ request()->routeIs('relatorios.index') ? 'text-blue-600 border-blue-600 font-bold active' : 'border-transparent hover:text-gray-600 hover:border-gray-300' }}">
                📊 Visão Geral
            </a>
        </li>
        <li class="me-2">
            <a href="{{ route('relatorios.faturamento', ['data_inicio' => $dataInicio, 'data_fim' => $dataFim]) }}" 
               class="inline-flex items-center p-4 border-b-2 rounded-t-lg transition {{ request()->routeIs('relatorios.faturamento') ? 'text-blue-600 border-blue-600 font-bold active' : 'border-transparent hover:text-gray-600 hover:border-gray-300' }}">
                💵 Faturamento & Financeiro
            </a>
        </li>
        <li class="me-2">
            <a href="{{ route('relatorios.frota', ['data_inicio' => $dataInicio, 'data_fim' => $dataFim]) }}" 
               class="inline-flex items-center p-4 border-b-2 rounded-t-lg transition {{ request()->routeIs('relatorios.frota') ? 'text-blue-600 border-blue-600 font-bold active' : 'border-transparent hover:text-gray-600 hover:border-gray-300' }}">
                🚗 Gestão de Frota
            </a>
        </li>
        <li class="me-2">
            <a href="{{ route('relatorios.manutencao', ['data_inicio' => $dataInicio, 'data_fim' => $dataFim]) }}" 
               class="inline-flex items-center p-4 border-b-2 rounded-t-lg transition {{ request()->routeIs('relatorios.manutencao') ? 'text-blue-600 border-blue-600 font-bold active' : 'border-transparent hover:text-gray-600 hover:border-gray-300' }}">
                🔧 Histórico de Oficinas
            </a>
        </li>
    </ul>
</div>