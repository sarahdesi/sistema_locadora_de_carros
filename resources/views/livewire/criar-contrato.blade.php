<?php

use Livewire\Volt\Component;
use App\Models\Usuario;
use App\Models\Veiculo;
use App\Models\Contrato;
use Carbon\Carbon;

new class extends Component {
    // Dados de controle do Cliente
    public $cliente_id = '';
    public $busca_cliente = '';

    // Dados de controle do Veículo
    public $veiculo_id = '';
    public $busca_veiculo = '';

    // Restante dos dados do contrato
    public $data_hora_retorno = '';

    public function mount()
    {
        $this->data_hora_retorno = Carbon::now()->addDays(3)->format('Y-m-d\TH:i');
    }

    public function with(): array
    {
        $clientesFiltrados = [];
        $veiculosFiltrados = [];

        if (strlen($this->busca_cliente) >= 2 && empty($this->cliente_id)) {
            $clientesFiltrados = Usuario::where('name', 'ILIKE', '%' . $this->busca_cliente . '%')
                ->orWhere('cpf', 'ILIKE', '%' . $this->busca_cliente . '%')
                ->limit(5)->get();
        }

        if (strlen($this->busca_veiculo) >= 2 && empty($this->veiculo_id)) {
            $veiculosFiltrados = Veiculo::where('status', 'disponivel')
                ->where(function($query) {
                    $query->where('marca', 'ILIKE', '%' . $this->busca_veiculo . '%')
                          ->orWhere('modelo', 'ILIKE', '%' . $this->busca_veiculo . '%')
                          ->orWhere('placa', 'ILIKE', '%' . $this->busca_veiculo . '%');
                })->limit(5)->get();
        }

        return [
            'clientes' => $clientesFiltrados,
            'veiculos' => $veiculosFiltrados
        ];
    }

    public function selecionarCliente($id, $nome)
    {
        $this->cliente_id = $id;
        $this->busca_cliente = $nome;
    }

    public function limparCliente()
    {
        $this->cliente_id = '';
        $this->busca_cliente = '';
    }

    public function selecionarVeiculo($id, $nome)
    {
        $this->veiculo_id = $id;
        $this->busca_veiculo = $nome;
    }

    public function limparVeiculo()
    {
        $this->veiculo_id = '';
        $this->busca_veiculo = '';
    }

    public function confirmarLocacao()
    {
        $this->validate([
            'cliente_id'        => 'required|exists:usuarios,id',
            'veiculo_id'        => 'required|exists:veiculos,id',
            'data_hora_retorno' => 'required|date|after:now',
        ], [
            'cliente_id.required' => 'Você precisa selecionar um cliente válido na busca.',
            'veiculo_id.required' => 'Você precisa selecionar um veículo disponível na busca.',
        ]);

        $veiculo = Veiculo::findOrFail($this->veiculo_id);

        Contrato::create([
            'cliente_id'         => $this->cliente_id,
            'veiculo_id'         => $this->veiculo_id,
            'data_hora_retorno'  => $this->data_hora_retorno,
            'status_contrato'    => 'aberto',
            'valor_diaria'       => 150.00,
            'servidor_id'        => auth()->id()
        ]);

        $veiculo->update(['status' => 'reservado']);

        session()->flash('success', 'Locação aberta com sucesso! Realize o Check-in na retirada do veículo.');

        return redirect()->route('contratos.index');
    }
}; ?>

<div class="bg-white rounded-xl shadow p-6">
    <form wire:submit.prevent="confirmarLocacao" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            {{-- Autocomplete de Clientes --}}
            <div class="relative col-span-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">Selecionar Cliente</label>
                <div class="relative">
                    <input type="text" wire:model.live.debounce.200ms="busca_cliente" 
                           placeholder="Digite o nome ou CPF do cliente..."
                           {{ $cliente_id ? 'readonly' : '' }}
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500 {{ $cliente_id ? 'bg-blue-50 border-blue-200 font-medium text-blue-800' : '' }}">
                    
                    @if($cliente_id)
                        <button type="button" wire:click="limparCliente" class="absolute inset-y-0 right-3 flex items-center text-xs text-red-500 hover:underline font-semibold">✕ Limpar</button>
                    @endif
                </div>
                @error('cliente_id') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror

                @if(!empty($busca_cliente) && !$cliente_id)
                    <div class="absolute z-20 w-full bg-white border border-gray-200 rounded-lg shadow-lg mt-1 divide-y divide-gray-50 max-h-48 overflow-y-auto">
                        @forelse($clientes as $c)
                            <button type="button" wire:click="selecionarCliente({{ $c->id }}, '{{ $c->name }}')" class="w-full text-left px-4 py-2 hover:bg-slate-50 text-sm flex justify-between">
                                <span class="font-medium text-gray-800">{{ $c->name }}</span>
                                <span class="text-gray-400 font-mono text-xs">CPF: {{ $c->cpf }}</span>
                            </button>
                        @empty
                            <div class="px-4 py-2 text-xs text-gray-400 italic">Nenhum cliente correspondente...</div>
                        @endforelse
                    </div>
                @endif
            </div>

            {{-- Autocomplete de Veículos --}}
            <div class="relative col-span-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">Veículo Disponível</label>
                <div class="relative">
                    <input type="text" wire:model.live.debounce.200ms="busca_veiculo" 
                           placeholder="Busque por marca, modelo ou placa..."
                           {{ $veiculo_id ? 'readonly' : '' }}
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500 {{ $veiculo_id ? 'bg-blue-50 border-blue-200 font-medium text-blue-800' : '' }}">
                    
                    @if($veiculo_id)
                        <button type="button" wire:click="limparVeiculo" class="absolute inset-y-0 right-3 flex items-center text-xs text-red-500 hover:underline font-semibold">✕ Limpar</button>
                    @endif
                </div>
                @error('veiculo_id') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror

                @if(!empty($busca_veiculo) && !$veiculo_id)
                    <div class="absolute z-20 w-full bg-white border border-gray-200 rounded-lg shadow-lg mt-1 divide-y divide-gray-50 max-h-48 overflow-y-auto">
                        @forelse($veiculos as $v)
                            <button type="button" wire:click="selecionarVeiculo({{ $v->id }}, '{{ $v->marca }} {{ $v->modelo }} — {{ $v->placa }}')" class="w-full text-left px-4 py-2 hover:bg-slate-50 text-sm flex justify-between">
                                <span class="font-medium text-gray-800">{{ $v->marca }} {{ $v->modelo }}</span>
                                <span class="bg-gray-100 border border-gray-200 px-1.5 py-0.5 font-mono text-xs uppercase text-gray-600 rounded">{{ $v->placa }}</span>
                            </button>
                        @empty
                            <div class="px-4 py-2 text-xs text-gray-400 italic">Nenhum veículo disponível encontrado...</div>
                        @endforelse
                    </div>
                @endif
            </div>

            {{-- Data Prevista de Retorno (AJUSTADO PARA COL-SPAN-1) --}}
            <div class="col-span-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">Data/Hora Prevista de Devolução</label>
                <input type="datetime-local" wire:model="data_hora_retorno" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                @error('data_hora_retorno') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

        </div>

        {{-- Botões de Envio --}}
        <div class="mt-8 flex gap-3 border-t border-gray-100 pt-6 justify-start">
            <button type="submit"
                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium text-sm shadow-sm">
                Confirmar Locação
            </button>
            <a href="{{ route('contratos.index') }}"
               class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition text-sm font-medium">
                Cancelar
            </a>
        </div>
    </form>
</div>