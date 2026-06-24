<?php

use Livewire\Volt\Component;
use App\Models\Contrato;
use App\Models\Veiculo;
use App\Models\Usuario;
use App\Models\DocumentoVeiculo;
use Illuminate\Support\Facades\Gate;
use Carbon\Carbon;

new class extends Component {
    
    // Ação rápida: Envia o veículo para manutenção direto pelo clique
    public function enviarParaManutencao($veiculoId)
    {
        $veiculo = Veiculo::find($veiculoId);
        $veiculo->update(['status' => 'em_manutencao']);
        
        session()->flash('message', "Veículo {$veiculo->placa} enviado para manutenção com sucesso!");
    }

    public function with(): array
    {
        Gate::authorize('is-staff');

        $hoje = Carbon::today();
        $alertaPrazoCnh = Carbon::today()->addDays(30); // Alerta Cnh faltando 30 dias

        // 1. REQUISITO: Manutenção Preventiva (Oculta quem já está em manutenção)
        $alertasManutencao = Veiculo::where('status', '!=', 'em_manutencao')
            ->withCount(['manutencoes' => function ($query) {
                $query->where('tipo_manutencao', 'Preventiva')->where('status', 'concluida');
            }])->get()->filter(function ($veiculo) {
                $proximoMultiplo = ceil($veiculo->odometro / 10000) * 10000;
                $kmLimite = ($veiculo->manutencoes_count == 0) ? ($proximoMultiplo ?: 10000) : ($veiculo->manutencoes_count + 1) * 10000;
                $veiculo->km_limite = $kmLimite;
                return $veiculo->odometro >= ($kmLimite - 500);
            });

        // 2. REQUISITO: Devoluções Atrasadas
        $devolucoesAtrasadas = Contrato::with(['veiculo', 'cliente'])
            ->where('status_contrato', 'em_andamento')
            ->where('data_hora_retorno', '<', Carbon::now())
            ->get();

        // 3. REQUISITO: CNH Vencendo ou Vencida (Próximos 30 dias ou no passado)
        $alertasCnh = Usuario::whereNotNull('validade_cnh')
            ->where('validade_cnh', '<=', $alertaPrazoCnh)
            ->get();

        // 4. REQUISITO: Documentos de Veículos Vencidos (IPVA, Licenciamento, etc)
        $documentosVencidos = DocumentoVeiculo::where('data_vencimento', '<', $hoje->format('Y-m-d'))
            ->get();

        // Soma total de todos os problemas ativos no sistema
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

<div wire:poll.15s> {{-- O painel se auto-atualiza discretamente a cada 15 segundos --}}
    
    {{-- Cabeçalho Dinâmico --}}
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Centro de Notificações</h1>
        
        </div>
        <span class="px-4 py-1.5 {{ $totalAlertas > 0 ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700' }} rounded-full text-sm font-bold shadow-sm transition duration-300">
            {{ $totalAlertas }} {{ $totalAlertas == 1 ? 'Alerta Ativo' : 'Alertas Ativos' }}
        </span>
    </div>

    {{-- Feedback de Ações do Livewire --}}
    @if (session()->has('message'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm flex items-center gap-2 shadow-sm">
            ✅ {{ session('message') }}
        </div>
    @endif

    {{-- Grid Principal de Painéis --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        {{-- CARD 1: MANUTENÇÃO PREVENTIVA --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h2 class="flex items-center gap-2 font-bold text-gray-700 mb-4 text-sm uppercase tracking-wider">
                🔧 Manutenção Preventiva
            </h2>
            <div class="space-y-3 max-h-64 overflow-y-auto pr-1">
                @forelse($alertasManutencao as $v)
                    <div class="p-3.5 bg-slate-50 rounded-xl border border-gray-100 flex justify-between items-center">
                        <div>
                            <p class="font-bold text-gray-800 text-sm">{{ $v->marca }} {{ $v->modelo }}</p>
                            <p class="text-xs text-gray-500 font-mono mt-0.5">Placa: {{ $v->placa }} | KM: {{ number_format($v->odometro,0,',','.') }}</p>
                        </div>
                        <button wire:click="enviarParaManutencao({{ $v->id }})" 
                                class="px-3 py-1.5 bg-orange-500 hover:bg-orange-600 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                            Oficina
                        </button>
                    </div>
                @empty
                    <p class="text-gray-400 text-sm italic py-2">Toda a frota está com a revisão em dia.</p>
                @endforelse
            </div>
        </div>

        {{-- CARD 2: DEVOLUÇÕES ATRASADAS --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h2 class="flex items-center gap-2 font-bold text-gray-700 mb-4 text-sm uppercase tracking-wider">
                ⏰ Devoluções Atrasadas
            </h2>
            <div class="space-y-3 max-h-64 overflow-y-auto pr-1">
                @forelse($devolucoesAtrasadas as $c)
                    <div class="p-3.5 bg-red-50 rounded-xl border border-red-100 flex justify-between items-center">
                        <div>
                            <p class="font-bold text-red-900 text-sm">{{ $c->cliente->name }}</p>
                            <p class="text-xs text-red-600 mt-0.5">Carro: {{ $c->veiculo->modelo }} ({{ $c->veiculo->placa }})</p>
                        </div>
                        <a href="{{ route('contratos.show', $c->id) }}" 
                           class="p-2 bg-white text-red-600 hover:bg-red-100 rounded-lg shadow-sm border border-red-200 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"></path></svg>
                        </a>
                    </div>
                @empty
                    <p class="text-gray-400 text-sm italic py-2">Nenhum veículo em atraso no momento.</p>
                @endforelse
            </div>
        </div>

        {{-- CARD 3: CONTROLE DE CNH (Condutores) --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h2 class="flex items-center gap-2 font-bold text-gray-700 mb-4 text-sm uppercase tracking-wider">
                🪪 Validade de CNH
            </h2>
            <div class="space-y-3 max-h-64 overflow-y-auto pr-1">
                @forelse($alertasCnh as $u)
                    @php 
                        $isVencido = \Carbon\Carbon::parse($u->validade_cnh)->isPast();
                    @endphp
                    <div class="p-3.5 {{ $isVencido ? 'bg-amber-50 border-amber-200' : 'bg-slate-50 border-gray-100' }} rounded-xl border flex justify-between items-center">
                        <div>
                            <p class="font-bold text-gray-800 text-sm">{{ $u->name }}</p>
                            <p class="text-xs {{ $isVencido ? 'text-amber-700 font-semibold' : 'text-gray-500' }} mt-0.5">
                                {{ $isVencido ? '🚨 Vencida em: ' : 'Vence em: ' }} {{ \Carbon\Carbon::parse($u->validade_cnh)->format('d/M/Y') }}
                            </p>
                        </div>
                        <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded {{ $isVencido ? 'bg-amber-200 text-amber-800' : 'bg-blue-100 text-blue-800' }}">
                            {{ $isVencido ? 'Bloqueado' : 'Atenção' }}
                        </span>
                    </div>
                @empty
                    <p class="text-gray-400 text-sm italic py-2">Nenhum cliente com CNH vencendo ou irregular.</p>
                @endforelse
            </div>
        </div>

        {{-- CARD 4: DOCUMENTOS DOS VEÍCULOS (IPVA/Licenciamento) --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h2 class="flex items-center gap-2 font-bold text-gray-700 mb-4 text-sm uppercase tracking-wider">
                📁 Documentações Vencidas
            </h2>
            <div class="space-y-3 max-h-64 overflow-y-auto pr-1">
                @forelse($documentosVencidos as $doc)
                    <div class="p-3.5 bg-rose-50 rounded-xl border border-rose-100 flex justify-between items-center">
                        <div>
                            <p class="font-bold text-rose-950 text-sm">Placa: {{ $doc->veiculo_placa }}</p>
                            <p class="text-xs text-rose-700 mt-0.5">Documento vencido desde {{ \Carbon\Carbon::parse($doc->data_vencimento)->format('d/m/Y') }}</p>
                        </div>
                        <span class="px-2 py-1 bg-rose-600 text-white font-bold text-[10px] rounded shadow-sm uppercase tracking-wider">
                            Irregular
                        </span>
                    </div>
                @empty
                    <p class="text-gray-400 text-sm italic py-2">Todos os impostos e licenciamentos em dia.</p>
                @endforelse
            </div>
        </div>

    </div>
</div>