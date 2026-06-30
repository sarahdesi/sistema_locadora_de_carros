<?php

use Livewire\Volt\Component;
use App\Models\Contrato;
use App\Models\Veiculo;
use App\Models\Usuario;
use App\Models\DocumentoVeiculo;
use App\Models\Manutencao; 
use Illuminate\Support\Facades\Gate;
use Carbon\Carbon;

new class extends Component {
    
    public function enviarParaManutencao($veiculoId)
    {
        Gate::authorize('is-staff');

        $veiculo = Veiculo::findOrFail($veiculoId);
        
        // 2. Atualiza o status do veículo para ele ser bloqueado no sistema
        $veiculo->update(['status' => 'em_manutencao']);
        
        // 3. CRIA O REGISTRO DE MANUTENÇÃO (O que estava faltando!)
        // Como o alerta vem do card de Preventiva, já setamos os dados padrões correspondentes
        Manutencao::create([
            'veiculo_id'      => $veiculo->id,
            'tipo_manutencao' => 'Preventiva', 
            'descricao'       => 'Manutenção preventiva iniciada via Central de Alertas (revisão de odômetro).',
            'data_entrada'    => Carbon::today()->format('Y-m-d'),
            'custo'           => 0.00, // Começa zerado, podendo ser editado depois na tela de manutenção
            'status'          => 'em_andamento'
        ]);
        
        session()->flash('message', "Veículo {$veiculo->placa} enviado para manutenção com sucesso!");
    }

    public function with(): array
    {
        Gate::authorize('is-staff');

        $hoje = Carbon::today();
        $alertaPrazoCnh = Carbon::today()->addDays(30);

        $alertasManutencao = Veiculo::where('status', '!=', 'em_manutencao')
            ->withCount(['manutencoes' => function ($query) {
                $query->where('tipo_manutencao', 'Preventiva')->where('status', 'concluida');
            }])->get()->filter(function ($veiculo) {
                $proximoMultiplo = ceil($veiculo->odometro / 10000) * 10000;
                $kmLimite = ($veiculo->manutencoes_count == 0) ? ($proximoMultiplo ?: 10000) : ($veiculo->manutencoes_count + 1) * 10000;
                $veiculo->km_limite = $kmLimite;
                return $veiculo->odometro >= ($kmLimite - 500);
            });

        $devolucoesAtrasadas = Contrato::with(['veiculo', 'cliente'])
            ->where('status_contrato', 'em_andamento')
            ->where('data_hora_retorno', '<', Carbon::now())
            ->get();

        $alertasCnh = Usuario::whereNotNull('validade_cnh')
            ->where('validade_cnh', '<=', $alertaPrazoCnh)
            ->get();

        $documentosVencidos = DocumentoVeiculo::where('data_vencimento', '<', $hoje->format('Y-m-d'))
            ->get();

        $totalAlertas = $alertasManutencao->count() + 
                        $devolucoesAtrasadas->count() + 
                        $alertasCnh->count() + 
                        $documentosVencidos->count();

        return [
            'alertasManutencao'   => $alertasManutencao,
            'devolucoesAtrasadas' => $devolucoesAtrasadas,
            'alertasCnh'          => $alertasCnh,
            'documentosVencidos'  => $documentosVencidos,
            'totalAlertas'        => $totalAlertas
        ];
    }
}; ?>

<div wire:poll.15s>
    {{-- CORREÇÃO AQUI: Injeta o título diretamente na barra branca oficial do topo do seu layout --}}
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.02 6.02 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                </svg>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Centro de Notificações') }}
                </h2>
            </div>
            
            <span class="px-4 py-1.5 {{ $totalAlertas > 0 ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700' }} rounded-full text-xs font-bold shadow-sm transition duration-300">
                {{ $totalAlertas }} {{ $totalAlertas == 1 ? 'Alerta Ativo' : 'Alertas Ativos' }}
            </span>
        </div>
    </x-slot>

    {{-- Área de Conteúdo com as 4 Caixas Brancas Separadas --}}
    <div class="space-y-6">
        @if (session()->has('message'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm flex items-center gap-2 shadow-sm">
                ✅ {{ session('message') }}
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            {{-- CARD 1: MANUTENÇÃO PREVENTIVA --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
                <div class="p-4 bg-gray-50 border-b border-gray-100 text-xs font-semibold uppercase text-gray-500 tracking-wider">
                    🔧 Manutenção Preventiva
                </div>
                <div class="overflow-x-auto flex-1 max-h-64 overflow-y-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <tbody class="divide-y divide-gray-100 text-gray-700">
                            @forelse($alertasManutencao as $v)
                                <tr class="hover:bg-slate-50/80 transition duration-150">
                                    <td class="p-4 font-medium text-gray-900">
                                        {{ $v->marca }} {{ $v->modelo }}
                                        <span class="block text-[11px] text-gray-400 font-mono font-normal mt-0.5">Placa: {{ $v->placa }}</span>
                                    </td>
                                    <td class="p-4 text-xs text-gray-500 font-mono">
                                        {{ number_format($v->odometro, 0, ',', '.') }} km
                                    </td>
                                    <td class="p-4 text-right">
                                        <div class="flex justify-end">
                                            <button wire:click="enviarParaManutencao({{ $v->id }})" 
                                                    class="p-1.5 bg-gray-50 text-gray-600 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition" 
                                                    title="Enviar para Oficina">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="p-4 text-center text-gray-400 italic text-xs">Toda a frota está com a revisão em dia.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- CARD 2: DEVOLUÇÕES ATRASADAS --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
                <div class="p-4 bg-gray-50 border-b border-gray-100 text-xs font-semibold uppercase text-gray-500 tracking-wider">
                    ⏰ Devoluções Atrasadas
                </div>
                <div class="overflow-x-auto flex-1 max-h-64 overflow-y-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <tbody class="divide-y divide-gray-100 text-gray-700">
                            @forelse($devolucoesAtrasadas as $c)
                                <tr class="hover:bg-slate-50/80 transition duration-150">
                                    <td class="p-4 font-medium text-gray-900">
                                        {{ $c->cliente->name }}
                                        <span class="block text-[11px] text-red-500 font-normal mt-0.5">Carro: {{ $c->veiculo->modelo }} ({{ $c->veiculo->placa }})</span>
                                    </td>
                                    <td class="p-4 text-xs font-mono text-gray-500">
                                        {{ \Carbon\Carbon::parse($c->data_hora_retorno)->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="p-4 text-right">
                                        <div class="flex justify-end">
                                            <a href="{{ route('contratos.show', $c->id) }}" 
                                               class="p-1.5 bg-gray-50 text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition" 
                                               title="Ver Detalhes">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="p-4 text-center text-gray-400 italic text-xs">Nenhum veículo em atraso no momento.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- CARD 3: VALIDADE DE CNH --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
                <div class="p-4 bg-gray-50 border-b border-gray-100 text-xs font-semibold uppercase text-gray-500 tracking-wider">
                    🪪 Validade de CNH
                </div>
                <div class="overflow-x-auto flex-1 max-h-64 overflow-y-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <tbody class="divide-y divide-gray-100 text-gray-700">
                            @forelse($alertasCnh as $u)
                                @php 
                                    $isVencido = \Carbon\Carbon::parse($u->validade_cnh)->isPast();
                                @endphp
                                <tr class="hover:bg-slate-50/80 transition duration-150">
                                    <td class="p-4 font-medium text-gray-900">
                                        {{ $u->name }}
                                        <span class="block text-[11px] {{ $isVencido ? 'text-red-500 font-semibold' : 'text-gray-400' }} font-normal mt-0.5">
                                            {{ $isVencido ? '🚨 Vencida em: ' : 'Vence em: ' }} {{ \Carbon\Carbon::parse($u->validade_cnh)->format('d/m/Y') }}
                                        </span>
                                    </td>
                                    <td class="p-4 text-right">
                                        <div class="flex justify-end">
                                            <span class="px-2.5 py-0.5 text-[10px] font-bold uppercase rounded-full {{ $isVencido ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800' }}">
                                                {{ $isVencido ? 'Bloqueado' : 'Atenção' }}
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="p-4 text-center text-gray-400 italic text-xs">Nenhum cliente com CNH vencendo ou irregular.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- CARD 4: DOCUMENTAÇÕES VENCIDAS --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
                <div class="p-4 bg-gray-50 border-b border-gray-100 text-xs font-semibold uppercase text-gray-500 tracking-wider">
                    📁 Documentações Vencidas
                </div>
                <div class="overflow-x-auto flex-1 max-h-64 overflow-y-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <tbody class="divide-y divide-gray-100 text-gray-700">
                            @forelse($documentosVencidos as $doc)
                                <tr class="hover:bg-slate-50/80 transition duration-150">
                                    <td class="p-4 font-medium text-gray-900">
                                        Placa: <span class="font-mono text-xs">{{ $doc->veiculo_placa }}</span>
                                        <span class="block text-[11px] text-red-500 font-normal mt-0.5">Documento vencido desde {{ \Carbon\Carbon::parse($doc->data_vencimento)->format('d/m/Y') }}</span>
                                    </td>
                                    <td class="p-4 text-right">
                                        <div class="flex justify-end">
                                            <span class="px-2.5 py-0.5 bg-red-600 text-white font-bold text-[10px] rounded-full uppercase tracking-wider shadow-sm">
                                                Irregular
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="p-4 text-center text-gray-400 italic text-xs">Todos os impostos e licenciamentos em dia.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>