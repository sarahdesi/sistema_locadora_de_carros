<?php

use Livewire\Volt\Component;
use App\Models\Contrato;
use App\Models\Veiculo;
use App\Models\Usuario;
use App\Models\DocumentoVeiculo;
use Illuminate\Support\Facades\Gate;
use Carbon\Carbon;

new class extends Component
{
    // Propriedades do formulário
    public $cliente_id;
    public $veiculo_id;
    public $data_hora_retorno;
    public $valor_diaria;
    public $valor_total = 0; // <-- NOVA PROPRIEDADE

    // Regras de validação
    protected function rules()
    {
        $rules = [
            'veiculo_id'        => 'required|exists:veiculos,id',
            'data_hora_retorno' => 'required|date|after:now',
            'valor_diaria'      => 'required|numeric|min:0',
        ];

        if (Gate::allows('is-staff')) {
            $rules['cliente_id'] = 'required|exists:usuarios,id';
        }

        return $rules;
    }

    // Ouvinte do Livewire: Executa automaticamente sempre que qualquer propriedade muda na tela
    public function updated($propertyName)
    {
        // Se mudarem a data de retorno ou o valor da diária, recalculamos o total
        if (in_array($propertyName, ['data_hora_retorno', 'valor_diaria'])) {
            $this->calcularValorTotal();
        }
    }

    // Função que faz a matemática do contrato
    public function calcularValorTotal()
    {
        if ($this->data_hora_retorno && $this->valor_diaria) {
            $inicio = Carbon::now();
            $fim = Carbon::parse($this->data_hora_retorno);

            // Calcula a quantidade de dias (usa ceil para arredondar horas quebradas para cima)
            $dias = max(1, ceil($inicio->diffInHours($fim) / 24));

            // Multiplica os dias pelo valor informado da diária
            $this->valor_total = $dias * (float) $this->valor_diaria;
        } else {
            $this->valor_total = 0;
        }
    }

    // Salva os dados no banco
    public function save()
    {
        $this->validate();

        $user = auth()->user();
        $validatedData = [
            'veiculo_id'        => $this->veiculo_id,
            'data_hora_retorno' => $this->data_hora_retorno,
            'valor_diaria'      => $this->valor_diaria,
            'valor_total'       => $this->valor_total, // <-- SALVA O VALOR CALCULADO
            'status_contrato'   => 'aberto',
        ];

        if (!Gate::allows('is-staff')) {
            $validatedData['cliente_id'] = $user->id;
            $validatedData['servidor_id'] = null;
        } else {
            $validatedData['cliente_id'] = $this->cliente_id;
            $validatedData['servidor_id'] = $user->id;
        }

        $veiculo = Veiculo::findOrFail($this->veiculo_id);

        $temPendeciaDocumento = DocumentoVeiculo::where('veiculo_placa', $veiculo->placa)
            ->where('data_vencimento', '<', now()->format('Y-m-d'))
            ->exists();

        if ($temPendeciaDocumento) {
            session()->flash('error', 'Bloqueio de Segurança: Este veículo possui pendências na tabela de documentos!');
            return;
        }

        $contrato = Contrato::create($validatedData);
        $veiculo->update(['status' => 'reservado']);

        session()->flash('sucesso', 'Contrato aberto com sucesso!');

        return redirect()->route('contratos.show', $contrato);
    }

    public function with(): array
    {
        // NOTA: Voltei a consulta original filtrando por 'disponivel' para manter sua regra de negócio ativa.
        // Garanta que você possui carros com status 'disponivel' no banco para que apareçam.
        $placasComDocumentoVencido = DocumentoVeiculo::where('data_vencimento', '<', now()->format('Y-m-d'))
            ->pluck('veiculo_placa')
            ->toArray();

        $veiculosDisponiveis = Veiculo::where('status', 'disponivel')
            ->whereNotIn('placa', $placasComDocumentoVencido)
            ->get();

        $clientes = collect();
        if (Gate::allows('is-staff')) {
            $clientes = Usuario::whereHas('role', function($query) {
                $query->where('name', 'cliente'); 
            })->get();
        }

        return [
            'veiculosDisponiveis' => $veiculosDisponiveis,
            'clientes' => $clientes
        ];
    }
}; ?>

<div>
    <div class="bg-white rounded-xl shadow p-6 max-w-2xl mx-auto">
        
        @if (session()->has('error'))
            <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-600 rounded-lg text-sm">
                {{ session('error') }}
            </div>
        @endif

        <form wire:submit.prevent="save">
            <div class="grid grid-cols-2 gap-4">

                {{-- Seleção de Cliente ou Alerta Informativo --}}
                @can('is-staff')
                    <div class="col-span-2 md:col-span-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Selecionar Cliente</label>
                        <select wire:model.live="cliente_id" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Selecione o cliente...</option>
                            @foreach($clientes as $cliente)
                                <option value="{{ $cliente->id }}">
                                    {{ $cliente->name }} (CPF: {{ $cliente->cpf }})
                                </option>
                            @endforeach
                        </select>
                        @error('cliente_id')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>
                @else
                    <div class="col-span-2 bg-blue-50 border border-blue-100 rounded-xl p-4 text-sm text-blue-700 flex items-center gap-3 shadow-sm mb-2">
                        <svg class="w-5 h-5 text-blue-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        <span>Você está solicitando uma reserva de veículo em seu nome: <strong class="font-semibold">{{ auth()->user()->name }}</strong>.</span>
                    </div>
                @endcan

                {{-- Seleção do Veículo --}}
                <div class="{{ auth()->user()->can('is-staff') ? 'col-span-2 md:col-span-1' : 'col-span-2' }}">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Veículo Disponível</label>
                    <select wire:model.live="veiculo_id" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Selecione um veículo...</option>
                        @foreach($veiculosDisponiveis as $veiculo)
                            <option value="{{ $veiculo->id }}">
                                {{ $veiculo->marca }} {{ $veiculo->modelo }} — Placa: {{ $veiculo->placa }}
                            </option>
                        @endforeach
                    </select>
                    @error('veiculo_id')
                        <span class="text-red-500 text-xs">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Data e Hora de Retorno (Devolução) --}}
                <div class="col-span-2 md:col-span-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Data/Hora Prevista de Devolução</label>
                    <input type="datetime-local" wire:model.live="data_hora_retorno" 
                           min="{{ now()->format('Y-m-d\TH:i') }}"
                           required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    @error('data_hora_retorno')
                        <span class="text-red-500 text-xs">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Valor da Diária --}}
                <div class="col-span-2 md:col-span-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Valor da Diária (R$)</label>
                    <input type="number" wire:model.live="valor_diaria" 
                           step="0.01"
                           min="0"
                           placeholder="0,00"
                           required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    @error('valor_diaria')
                        <span class="text-red-500 text-xs">{{ $message }}</span>
                    @enderror
                </div>

                {{-- NOVO BLOCO: Exibição Dinâmica do Valor Total do Contrato --}}
                @if($valor_total > 0)
                    <div class="col-span-2 mt-2 p-4 bg-emerald-50 border border-emerald-100 rounded-xl flex justify-between items-center shadow-sm animate-fade-in">
                        <div>
                            <h4 class="text-sm font-medium text-emerald-800">Resumo do Investimento</h4>
                            <p class="text-xs text-emerald-600 mt-0.5">Calculado dinamicamente pelo sistema baseado nas datas informadas.</p>
                        </div>
                        <div class="text-right">
                            <span class="text-xs font-semibold text-emerald-700 block uppercase tracking-wider">Valor Total Estimado</span>
                            <span class="text-xl font-bold text-emerald-600 font-mono">
                                R$ {{ number_format($valor_total, 2, ',', '.') }}
                            </span>
                        </div>
                    </div>
                @endif

            </div>

            <div class="mt-6 flex gap-3">
                <button type="submit"
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-150 font-medium text-sm shadow-sm">
                    Confirmar Locação
                </button>
                <a href="{{ route('contratos.index') }}"
                   class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition duration-150 text-sm font-medium">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>