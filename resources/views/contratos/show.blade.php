<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('contratos.index') }}" class="text-gray-500 hover:text-gray-700">← Voltar</a>
            <h2 class="font-semibold text-xl text-gray-800">Contrato {{ $contrato->id }}</h2>
        </div>
    </x-slot>

    <div class="space-y-6">

        <x-alerta />

        {{-- Status do contrato --}}
        @php
            $cores = [
                'aberto'       => 'bg-yellow-100 text-yellow-800',
                'em_andamento' => 'bg-blue-100 text-blue-800',
                'encerrado'    => 'bg-green-100 text-green-800',
                'cancelado'    => 'bg-red-100 text-red-800',
            ];
            $cor = $cores[$contrato->status_contrato] ?? 'bg-gray-100 text-gray-800';
        @endphp

        <div class="flex items-center gap-3">
            <span class="px-3 py-1 rounded-full text-sm font-medium {{ $cor }}">
                {{ ucfirst(str_replace('_', ' ', $contrato->status_contrato)) }}
            </span>
        </div>

        {{-- Dados do contrato --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            {{-- Coluna da Esquerda: Informações --}}
            <div class="bg-white rounded-xl shadow p-6">
                <h3 class="font-semibold text-gray-700 mb-4">Dados da Locação</h3>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Cliente</dt>
                        <dd class="font-medium">{{ $contrato->cliente->name }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Veículo</dt>
                        <dd class="font-medium">{{ $contrato->veiculo->marca }} {{ $contrato->veiculo->modelo }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Placa</dt>
                        <dd class="font-medium font-mono bg-gray-50 px-1.5 py-0.5 rounded text-xs border border-gray-100 uppercase">{{ $contrato->veiculo->placa }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Previsão de retorno</dt>
                        <dd class="font-medium">
                            {{ \Carbon\Carbon::parse($contrato->data_hora_retorno)->format('d/m/Y H:i') }}
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Valor da diária</dt>
                        <dd class="font-medium text-gray-900">R$ {{ number_format($contrato->valor_diaria, 2, ',', '.') }}</dd>
                    </div>
                    @if($contrato->servidor)
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Atendido por</dt>
                            <dd class="font-medium">{{ $contrato->servidor->name }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            {{-- Coluna da Direita: Painel Reativo do Livewire --}}
            <div class="bg-white rounded-xl shadow p-6">
                <h3 class="font-semibold text-gray-700 mb-2">Ações  </h3>
            
                {{-- Alertas de feedback do Livewire --}}
                @if(session('sucesso_checkin'))
                    <div class="mb-4 p-3 bg-blue-100 text-blue-700 text-sm font-medium rounded-lg border border-blue-200">
                        ✅ {{ session('sucesso_checkin') }}
                    </div>
                @endif

                @if(session('sucesso_checkout'))
                    <div class="mb-4 p-3 bg-emerald-100 text-emerald-700 text-sm font-medium rounded-lg border border-emerald-200">
                        ✅ {{ session('sucesso_checkout') }}
                    </div>
                @endif

                {{-- Renderização dos Componentes baseados nas suas regras de negócio --}}
                @if(Gate::allows('is-staff'))
                    
                    @if($contrato->status_contrato === 'aberto')
                        {{-- O carro está saindo: Realiza o Check-in --}}
                        <livewire:realizar-checkin :contrato="$contrato" />
                    
                    @elseif($contrato->status_contrato === 'em_andamento')
                        {{-- O carro está voltando/encerrando: Realiza o Check-out com cálculo de multas --}}
                        <livewire:realizar-checkout :contrato="$contrato" />
                    
                    @endif

                @endif

                {{-- Status Finais (Estáticos) --}}
                @if($contrato->status_contrato === 'encerrado')
                    <div class="p-4 bg-emerald-50 border border-emerald-100 text-emerald-800 rounded-lg text-sm flex items-center gap-2">
                        <span>✅ Contrato encerrado com sucesso. Veículo devolvido à frota.</span>
                    </div>
                @endif

                @if($contrato->status_contrato === 'cancelado')
                    <div class="p-4 bg-red-50 border border-red-100 text-red-800 rounded-lg text-sm flex items-center gap-2">
                        <span>❌ Este contrato foi cancelado e o veículo liberado.</span>
                    </div>
                @endif

            </div>
        </div>

        {{-- Seção inferior fixa: Histórico da Vistoria de Saída --}}
        @if($contrato->checkIn)
            <div class="bg-white rounded-xl shadow p-6 border border-gray-100">
                <div class="flex items-center justify-between mb-4 border-b border-gray-100 pb-3">
                    <h3 class="font-semibold text-gray-700 text-base flex items-center gap-2">
                        📋 Vistoria de Saída (Check-In)
                    </h3>
                    <span class="text-xs bg-gray-100 font-mono text-gray-600 px-2.5 py-1 rounded">
                        KM Inicial: {{ number_format($contrato->checkIn->km_inicial, 0, ',', '.') }}
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                    <div class="bg-gray-50 p-3 rounded-lg">
                        <span class="text-xs text-gray-400 block font-medium uppercase">Combustível</span>
                        <span class="font-semibold text-gray-800">{{ $contrato->checkIn->nivel_combustivel }}</span>
                    </div>
                    <div class="bg-gray-50 p-3 rounded-lg">
                        <span class="text-xs text-gray-400 block font-medium uppercase">Data/Hora da Saída</span>
                        <span class="font-semibold text-gray-800">
                            {{ \Carbon\Carbon::parse($contrato->checkIn->data_hora_saida)->format('d/m/Y H:i') }}
                        </span>
                    </div>
                    <div class="bg-gray-50 p-3 rounded-lg">
                        <span class="text-xs text-gray-400 block font-medium uppercase">Status da Vistoria</span>
                        <span class="inline-block px-2 py-0.5 mt-0.5 rounded text-xs font-medium bg-blue-50 text-blue-700 border border-blue-100">
                            {{ ucfirst($contrato->checkIn->status) }}
                        </span>
                    </div>

                    <div class="md:col-span-3 bg-gray-50 p-3 rounded-lg">
                        <span class="text-xs text-gray-400 block font-medium uppercase mb-1">Objetos de Conferência</span>
                        <p class="text-gray-700 italic text-xs">{{ $contrato->checkIn->conferencia_obj ?? 'Nenhum objeto listado.' }}</p>
                    </div>

                    @if($contrato->checkIn->avarias)
                        <div class="md:col-span-3 bg-amber-50/60 border border-amber-100/70 p-3 rounded-lg">
                            <span class="text-xs text-amber-700 block font-medium uppercase mb-1">Avarias Observadas</span>
                            <p class="text-gray-700 text-xs">{{ $contrato->checkIn->avarias }}</p>
                        </div>
                    @endif
                </div>
            </div>
        @endif

    </div>
</x-app-layout>