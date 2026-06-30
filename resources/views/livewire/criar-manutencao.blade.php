<?php

use Livewire\Volt\Component;
use App\Models\Veiculo;
use App\Models\Manutencao;
use Illuminate\Support\Facades\Gate;

new class extends Component {
    
    public $veiculo_id = '';
    public $busca_veiculo = '';
    public $tipo_manutencao = '';
    public $custo = '0.00';
    public $data_entrada;
    public $descricao = '';

    public function mount()
    {
        Gate::authorize('is-staff');
        $this->data_entrada = date('Y-m-d');
    }

    
    public function with(): array
    {
        $veiculos = [];
        
        if (strlen($this->busca_veiculo) >= 2) {
            $veiculos = Veiculo::where('status', '!=', 'em_manutencao')
                ->where(function($query) {
                    $query->where('marca', 'ILIKE', '%' . $this->busca_veiculo . '%')
                          ->orWhere('modelo', 'ILIKE', '%' . $this->busca_veiculo . '%')
                          ->orWhere('placa', 'ILIKE', '%' . $this->busca_veiculo . '%');
                })
                ->get();
        } else if (empty($this->veiculo_id)) {
           
            $veiculos = Veiculo::where('status', '!=', 'em_manutencao')->limit(5)->get();
        }

        return [
            'veiculos' => $veiculos
        ];
    }

    public function selecionarVeiculo($id)
    {
        $this->veiculo_id = $id;
        $veiculo = Veiculo::find($id);
        $this->busca_veiculo = $veiculo->marca . ' ' . $veiculo->modelo . ' — Placa: ' . strtoupper($veiculo->placa);
    }

    public function limparSelecao()
    {
        $this->veiculo_id = '';
        $this->busca_veiculo = '';
    }

    public function salvar()
    {
        Gate::authorize('is-staff');

        $validated = $this->validate([
            'veiculo_id'      => 'required|exists:veiculos,id',
            'tipo_manutencao' => 'required|string',
            'descricao'       => 'required|string',
            'data_entrada'    => 'required|date',
            'custo'           => 'required|numeric|min:0',
        ]);

        $validated['status'] = 'em_andamento';

        // 1. Cria o registro da manutenção
        Manutencao::create($validated);

        // 2. Atualiza o status do veículo bloqueando-o
        $veiculo = Veiculo::findOrFail($this->veiculo_id);
        $veiculo->update(['status' => 'em_manutencao']);

        session()->flash('success', 'Manutenção registrada! O veículo foi bloqueado para locações.');

        return redirect()->route('manutencao.index');
    }
}; ?>

<form wire:submit.prevent="salvar" class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        {{-- Auto-complete / Filtro de veículos premium --}}
        {{-- Auto-complete / Filtro de veículos premium --}}
<div class="col-span-1 md:col-span-2 relative">
    <label class="block text-sm font-medium text-gray-700 mb-1">Veículo para Manutenção</label>
    
    <div class="relative">
        @if($veiculo_id)
            {{-- ESTADO: Veículo já selecionado (Apenas Leitura) --}}
            <input type="text" 
                   wire:key="input-veiculo-selecionado"
                   value="{{ $busca_veiculo }}" 
                   readonly
                   class="w-full border border-blue-200 bg-blue-50 rounded-lg px-4 py-2.5 text-sm font-medium text-blue-800">
            
            <button type="button" wire:click="limparSelecao" class="absolute inset-y-0 right-0 pr-3 flex items-center text-sm text-red-500 hover:text-red-700 font-semibold">
                ✕ Alterar Carro
            </button>
        @else
            {{-- ESTADO: Campo de busca ativo --}}
            <input type="text" 
                   wire:key="input-veiculo-busca"
                   wire:model.live.debounce.300ms="busca_veiculo" 
                   placeholder="Digite a marca, modelo ou placa..."
                   class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-blue-500 focus:border-blue-500">
        @endif
    </div>
    
    @error('veiculo_id') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror

    {{-- Resultados flutuantes da pesquisa --}}
    @if(!empty($busca_veiculo) && !$veiculo_id)
        <div class="absolute z-10 w-full bg-white border border-gray-200 rounded-lg shadow-lg mt-1 max-h-60 overflow-y-auto divide-y divide-gray-50">
            @forelse($veiculos as $v)
                <button type="button" wire:click="selecionarVeiculo({{ $v->id }})" class="w-full text-left px-4 py-3 hover:bg-slate-50 transition text-sm flex justify-between items-center">
                    <div>
                        <span class="font-semibold text-gray-800">{{ $v->marca }} {{ $v->modelo }}</span>
                        <span class="text-gray-400 text-xs font-mono ml-2 uppercase bg-gray-100 px-1.5 py-0.5 rounded border border-gray-200">{{ $v->placa }}</span>
                    </div>
                    <span class="text-xs font-medium text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full uppercase tracking-wider">{{ $v->status }}</span>
                </button>
            @empty
                <div class="px-4 py-3 text-sm text-gray-400 italic">Nenhum veículo disponível encontrado...</div>
            @endforelse
        </div>
    @endif
</div>

        {{-- Tipo de Manutenção --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Manutenção</label>
            <select wire:model="tipo_manutencao" required
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                <option value="">Selecione...</option>
                <option value="Preventiva">Preventiva (Revisão, Óleo, Filtros)</option>
                <option value="Corretiva">Corretiva (Conserto de Falhas/Avarias)</option>
                <option value="Estética">Estética (Pintura, Detailing, Higienização)</option>
            </select>
            @error('tipo_manutencao') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        {{-- Custo Estimado --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Custo Estimado Inicial (R$)</label>
            <input type="number" wire:model="custo" step="0.01" required
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
            @error('custo') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        {{-- Data de Entrada --}}
        <div class="col-span-1 md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Data de Entrada</label>
            <input type="date" wire:model="data_entrada" required
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
            @error('data_entrada') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        {{-- Descrição do Serviço --}}
        <div class="col-span-1 md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Descrição Detalhada do Serviço</label>
            <textarea wire:model="descricao" rows="4" required 
                      placeholder="Ex: Substituição das pastilhas de travão dianteiras..."
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500"></textarea>
            @error('descricao') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

    </div>

    {{-- Botões de Envio --}}
    <div class="mt-8 flex gap-3 border-t border-gray-100 pt-6">
        <button type="submit"
                class="px-6 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium text-sm shadow-sm">
            Bloquear Veículo & Registrar Entrada
        </button>
        <a href="{{ route('manutencao.index') }}"
           class="px-6 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition text-sm font-medium">
            Cancelar
        </a>
    </div>
</form>