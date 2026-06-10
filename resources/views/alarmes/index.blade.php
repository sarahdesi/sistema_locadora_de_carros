<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center gap-2">
                🔔 Centro de Alarmes e Notificações
            </h2>
            <span class="px-3 py-1 bg-red-100 text-red-700 font-bold text-sm rounded-full">
                {{ $totalAlertas }} Alertas Ativos
            </span>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            {{-- SEÇÃO 1: CONTROLE DE DEVOLUÇÕES (CRÍTICO) --}}
            <div class="bg-white rounded-xl shadow p-6 border border-gray-100">
                <h3 class="text-base font-bold text-gray-800 mb-4 flex items-center gap-2">
                     1. Controle de Devoluções (Contratos)
                </h3>
                
                @if($devolucoesAtrasadas->isEmpty() && $devolucoesProximas->isEmpty())
                    <p class="text-gray-400 text-sm italic">Nenhum veículo com devolução atrasada ou próxima.</p>
                @else
                    <div class="space-y-3">
                        {{-- Devoluções Já Atrasadas --}}
                        @foreach($devolucoesAtrasadas as $contrato)
                            <div class="flex justify-between items-center bg-red-50 border border-red-100 p-3 rounded-lg text-sm">
                                <div>
                                    <span class="font-bold text-red-700 uppercase text-xs px-2 py-0.5 bg-red-100 rounded mr-2">ATRASADO</span>
                                    <strong>Contrato #{{ $contrato->id }}</strong> — {{ $contrato->veiculo->marca ?? 'Carro' }} (Placa: {{ $contrato->veiculo->placa ?? '---' }})
                                    <span class="text-gray-500 block text-xs mt-0.5">Cliente: {{ $contrato->user->name ?? 'Não Informado' }}</span>
                                </div>
                                <div class="text-right text-xs">
                                    <span class="text-red-600 font-semibold block">Deveria ter voltado em:</span>
                                    {{ \Carbon\Carbon::parse($contrato->previsao_retorno)->format('d/m/Y H:i') }}
                                </div>
                            </div>
                        @endforeach

                        {{-- Devoluções para Hoje --}}
                        @foreach($devolucoesProximas as $contrato)
                            <div class="flex justify-between items-center bg-amber-50 border border-amber-100 p-3 rounded-lg text-sm">
                                <div>
                                    <span class="font-bold text-amber-700 uppercase text-xs px-2 py-0.5 bg-amber-100 rounded mr-2">VENCE HOJE</span>
                                    <strong>Contrato #{{ $contrato->id }}</strong> — {{ $contrato->veiculo->marca ?? 'Carro' }}
                                    <span class="text-gray-500 block text-xs mt-0.5">Cliente: {{ $contrato->user->name ?? 'Não Informado' }}</span>
                                </div>
                                <div class="text-right text-xs">
                                    <span class="text-amber-600 font-semibold block">Horário Limite:</span>
                                    {{ \Carbon\Carbon::parse($contrato->previsao_retorno)->format('H:i') }} hs
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- SEÇÃO 2: ALERTAS DE MANUTENÇÃO (QUILOMETRAGEM) --}}
            <div class="bg-white rounded-xl shadow p-6 border border-gray-100">
                <h3 class="text-base font-bold text-gray-800 mb-4 flex items-center gap-2">
                     2. Alertas de Manutenção Preventiva
                </h3>
                @if($alertasManutencao->isEmpty())
                    <p class="text-gray-400 text-sm italic">Toda a frota está com as revisões de quilometragem em dia.</p>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($alertasManutencao as $veiculo)
                            <div class="border border-amber-200 bg-amber-50/50 p-4 rounded-lg flex justify-between items-center text-sm">
                                <div>
                                    <span class="font-bold text-gray-900 block">{{ $veiculo->marca }} {{ $veiculo->modelo }}</span>
                                    <span class="text-xs text-gray-500 font-mono uppercase bg-white px-1.5 py-0.5 rounded border border-gray-200">{{ $veiculo->placa }}</span>
                                </div>
                                <div class="text-right">
                                    <span class="text-xs text-amber-700 font-bold block">🚨 Troca de Óleo / Revisão</span>
                                    <span class="text-xs text-gray-600">KM Atual: <strong>{{ number_format($veiculo->km_atual, 0, ',', '.') }}</strong> / Limite: {{ number_format($veiculo->km_proxima_revisao ?? 0, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- SEÇÃO 3: DOCUMENTAÇÃO VENCIDA --}}
            <div class="bg-white rounded-xl shadow p-6 border border-gray-100">
                <h3 class="text-base font-bold text-gray-800 mb-4 flex items-center gap-2">
                     3. Vencimento de Documentação (IPVA / Licenciamento / Seguro)
                </h3>
                @if(empty($alertasDocumentos) || count($alertasDocumentos) === 0)
                    <p class="text-gray-400 text-sm italic">Nenhum imposto ou licenciamento vencido ou próximo do vencimento.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr class="text-gray-400 border-b border-gray-100 text-xs uppercase font-medium">
                                    <th class="pb-2">Veículo</th>
                                    <th class="pb-2">Documento</th>
                                    <th class="pb-2">Vencimento</th>
                                    <th class="pb-2 text-right">Situação</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                            @foreach($alertasDocumentos as $doc)
                                @php 
                                    $vencido = \Carbon\Carbon::parse($doc->data_vencimento)->isPast();
                                @endphp
                                <tr class="hover:bg-gray-50/50">
                                    {{-- Mostra Marca, Modelo e Placa do veículo associado --}}
                                    <td class="py-3 font-semibold text-gray-900">
                                        {{ $doc->veiculo->marca ?? 'Veículo' }} {{ $doc->veiculo->modelo ?? '' }} 
                                        <span class="text-xs font-mono text-gray-500 uppercase">({{ $doc->veiculo_placa }})</span>
                                    </td>
                                    {{-- Mostra o tipo (IPVA, Licenciamento, Seguro, etc) --}}
                                    <td class="py-3 text-gray-600 font-medium">
                                        {{ $doc->tipo }}
                                    </td>
                                    {{-- Data de Vencimento formatada --}}
                                    <td class="py-3 font-mono text-xs">
                                        {{ \Carbon\Carbon::parse($doc->data_vencimento)->format('d/m/Y') }}
                                    </td>
                                    {{-- Badge colorido dinâmico --}}
                                    <td class="py-3 text-right">
                                        <span class="px-2 py-0.5 text-xs font-bold rounded {{ $vencido ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700' }}">
                                            {{ $vencido ? 'VENCIDO' : 'VENCE EM BREVE' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{-- SEÇÃO 4: MANUTENÇÃO DE CADASTRO (CNH) --}}
            <div class="bg-white rounded-xl shadow p-6 border border-gray-100">
                <h3 class="text-base font-bold text-gray-800 mb-4 flex items-center gap-2">
                     4. Manutenção de Cadastro (Vencimento de CNH de Clientes)
                </h3>
                @if($alertasCnh->isEmpty())
                    <p class="text-gray-400 text-sm italic">Todos os clientes ativos possuem CNH regularizada e dentro do prazo.</p>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @foreach($alertasCnh as $cliente)
                            @php 
                                $vencida = \Carbon\Carbon::parse($cliente->vencimento_cnh)->isPast();
                            @endphp
                            <div class="p-3 rounded-lg border {{ $vencida ? 'bg-red-50/50 border-red-200' : 'bg-gray-50 border-gray-200' }} text-sm">
                                <span class="font-bold text-gray-900 block">{{ $cliente->name }}</span>
                                <span class="text-xs text-gray-500 block mb-2">CPF: {{ $cliente->cpf ?? '---' }}</span>
                                <div class="flex justify-between items-center text-xs border-t border-gray-100 pt-2">
                                    <span class="text-gray-400">Vencimento CNH:</span>
                                    <span class="font-semibold {{ $vencida ? 'text-red-600 font-bold' : 'text-gray-700' }}">
                                        {{ \Carbon\Carbon::parse($cliente->vencimento_cnh)->format('d/m/Y') }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>