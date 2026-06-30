<?php

use Livewire\Volt\Component;
use Livewire\WithPagination; 
use App\Models\Veiculo;
use Illuminate\Support\Facades\Gate;

new class extends Component {
    use WithPagination; 

    public $busca = '';

    public function updatingBusca()
    {
        $this->resetPage();
    }

    public function with(): array
    {
        $veiculos = Veiculo::query()
            ->when($this->busca, function ($query) {
                // Se for cliente, podemos opcionalmente restringir para não buscar por placa se quiser, 
                // mas mantivemos a busca flexível por enquanto
                $query->where('marca', 'ILIKE', '%' . $this->busca . '%')
                      ->orWhere('modelo', 'ILIKE', '%' . $this->busca . '%')
                      ->orWhere('placa', 'ILIKE', '%' . $this->busca . '%');
            })
            ->latest()
            ->paginate(10);

        return [
            'veiculos' => $veiculos
        ];
    }
}; ?>

<div>
    {{-- Barra de Busca Superior --}}
    <div class="mb-6 flex items-center justify-between gap-4">
        <div class="relative w-full max-w-md">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </span>
            <input type="text" 
                   wire:model.live.debounce.300ms="busca" 
                   placeholder="Buscar por marca, modelo ou placa..." 
                   class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 bg-white text-sm shadow-sm transition">
        </div>

        @can('is-staff')
            <a href="{{ route('veiculos.create') }}" 
               class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-xl shadow-sm transition inline-flex items-center gap-2 justify-center whitespace-nowrap shrink-0">
                <span>+ Novo Veículo</span>
            </a>
        @endcan
    </div>

    {{-- Tabela de Veículos --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="p-4 text-xs font-semibold uppercase text-gray-500 tracking-wider">Veículo</th>
                        
                        {{-- Oculta cabeçalhos do cliente --}}
                        @can('is-staff')
                            <th class="p-4 text-xs font-semibold uppercase text-gray-500 tracking-wider">Placa</th>
                            <th class="p-4 text-xs font-semibold uppercase text-gray-500 tracking-wider">KM Atual</th>
                        @endcan
                        
                        <th class="p-4 text-xs font-semibold uppercase text-gray-500 tracking-wider">Status</th>
                        <th class="p-4 text-xs font-semibold uppercase text-gray-500 tracking-wider text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($veiculos as $veiculo)
                        <tr class="hover:bg-slate-50/80 transition duration-150">
                            <td class="p-4">
                                <div class="font-bold text-gray-800 text-sm">{{ $veiculo->marca }} {{ $veiculo->modelo }}</div>
                                <div class="text-xs text-gray-400 mt-0.5">Ano: {{ $veiculo->ano ?? 'N/A' }}</div>
                            </td>
                            
                            {{-- Oculta dados da Placa e KM do cliente --}}
                            @can('is-staff')
                                <td class="p-4 text-sm font-mono text-gray-600">
                                    <span class="bg-gray-100 px-2 py-0.5 rounded border border-gray-200">{{ $veiculo->placa }}</span>
                                </td>
                                <td class="p-4 text-sm text-gray-600 font-mono">
                                    {{ number_format($veiculo->odometro, 0, ',', '.') }} km
                                </td>
                            @endcan
                            
                            <td class="p-4 text-sm">
                                @php
                                    $statusCores = [
                                        'disponivel' => 'bg-emerald-100 text-emerald-800',
                                        'locado' => 'bg-blue-100 text-blue-800',
                                        'reservado' => 'bg-amber-100 text-amber-800',
                                        'em_manutencao' => 'bg-orange-100 text-orange-800',
                                    ];
                                    $cor = $statusCores[$veiculo->status] ?? 'bg-gray-100 text-gray-800';
                                @endphp
                                <span class="px-2.5 py-0.5 text-xs font-bold rounded-full uppercase tracking-wider {{ $cor }}">
                                    {{ str_replace('_', ' ', $veiculo->status) }}
                                </span>
                            </td>
                            
                            <td class="p-4 text-right">
                                <div class="flex justify-end gap-2">
                                    @can('is-staff')
                                        {{-- Visão do Gerente/Operador: Editar e Deletar --}}
                                        <a href="{{ route('veiculos.edit', $veiculo->id) }}" class="p-1.5 bg-gray-50 text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Editar">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </a>
                                        <form action="{{ route('veiculos.destroy', $veiculo->id) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja remover este veículo?')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 bg-gray-50 text-gray-600 hover:text-red-600 hover:bg-red-50 rounded-lg transition" title="Excluir">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </form>
                                    @else
                                        {{-- Visão do Cliente: Botão de Reservar --}}
                                        @if($veiculo->status === 'disponivel')
                                            <a href="{{ route('contratos.create', ['veiculo_id' => $veiculo->id]) }}" 
                                               class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-xl shadow-sm transition whitespace-nowrap">
                                                Reservar
                                            </a>
                                        @else
                                            <button disabled 
                                                    class="px-3 py-1.5 bg-gray-100 text-gray-400 text-xs font-semibold rounded-xl cursor-not-allowed whitespace-nowrap">
                                                Indisponível
                                            </button>
                                        @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            {{-- Colspan dinâmico: 5 colunas para staff, 3 colunas para cliente --}}
                            <td colspan="{{ Auth::user()?->can('is-staff') ? 5 : 3 }}" class="p-8 text-center text-gray-400 text-sm italic">
                                Nenhum veículo encontrado para a busca "{{ $busca }}".
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($veiculos->hasPages())
            <div class="p-4 border-t border-gray-100 bg-gray-50/50 text-sm">
                {{ $veiculos->links() }}
            </div>
        @endif
    </div>
</div>