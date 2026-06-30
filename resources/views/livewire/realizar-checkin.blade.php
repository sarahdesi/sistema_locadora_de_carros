<?php

use Livewire\Volt\Component;
use App\Models\Contrato;
use Carbon\Carbon;

new class extends Component {
    public Contrato $contrato;

    // Campos do formulário
    public $odometro_final;
    public $data_real_entrega;
    public $nivel_combustivel_retorno = 'Cheio';
    public $avaliacao_limpeza = 'Bom';
    public $conferencia_obj_retorno = '';
    public $avarias_retorno = 'Sem avarias novas.';
    public $custo_adicional = 0.00;
    public $observacoes = '';
    
    // Propriedades calculadas
    public $dias_atraso = 0;
    public $valor_multa_atraso = 0;
    public $valor_final = 0;

    public function mount(Contrato $contrato)
    {
        $this->contrato = $contrato;
        $this->data_real_entrega = Carbon::now()->format('Y-m-d\TH:i');
        $this->odometro_final = $contrato->veiculo ? $contrato->veiculo->odometro : 0;
        
        $this->recalcularCheckout();
    }

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['data_real_entrega', 'odometro_final', 'custo_adicional'])) {
            $this->recalcularCheckout();
        }
    }

    public function recalcularCheckout()
    {
        $previsto = Carbon::parse($this->contrato->data_hora_retorno);
        $real = Carbon::parse($this->data_real_entrega);
        
        if ($real->gt($previsto)) {
            $this->dias_atraso = max(1, ceil($previsto->diffInHours($real) / 24));
            $this->valor_multa_atraso = $this->dias_atraso * ($this->contrato->valor_diaria * 1.2);
        } else {
            $this->dias_atraso = 0;
            $this->valor_multa_atraso = 0;
        }

        $valorBase = $this->contrato->valor_total ?? ($this->contrato->valor_diaria * 1); 
        $this->valor_final = $valorBase + $this->valor_multa_atraso + (float) ($this->custo_adicional ?? 0);
    }

    public function fecharContrato()
    {
        $this->recalcularCheckout();
        $minKm = $this->contrato->veiculo ? $this->contrato->veiculo->odometro : 0;

        $this->validate([
            'odometro_final' => 'required|numeric|min:' . $minKm,
            'data_real_entrega' => 'required|date',
            'nivel_combustivel_retorno' => 'required|string',
            'avaliacao_limpeza' => 'required|string',
            'conferencia_obj_retorno' => 'nullable|string',
            'avarias_retorno' => 'nullable|string',
            'custo_adicional' => 'required|numeric|min:0',
            'observacoes' => 'nullable|string',
        ], [
            'odometro_final.min' => 'O odômetro final não pode ser menor que o KM inicial (' . $minKm . ' km).'
        ]);

        $this->contrato->update([
            'status_contrato' => 'encerrado',
            'data_hora_devolucao' => $this->data_real_entrega,
            'km_final' => $this->odometro_final,
            'nivel_combustivel_retorno' => $this->nivel_combustivel_retorno,
            'avaliacao_limpeza' => $this->avaliacao_limpeza,
            'conferencia_obj_retorno' => $this->conferencia_obj_retorno,
            'avarias_retorno' => $this->avarias_retorno,
            'custo_adicional' => $this->custo_adicional,
            'observacoes' => $this->observacoes,
            'valor_total' => $this->valor_final,
        ]);

        if ($this->contrato->veiculo) {
            $this->contrato->veiculo->update([
                'odometro' => $this->odometro_final,
                'status' => 'disponivel'
            ]);
        }

        session()->flash('sucesso_checkout', 'Check-out realizado com sucesso!');
        return redirect()->route('contratos.show', $this->contrato->id);
    }
}; ?>

<div>
    <div class="flex items-center gap-2 mb-4 pb-2 border-b border-gray-100">
        <span class="text-lg">🔍</span>
        <h4 class="text-sm font-bold text-gray-800 uppercase tracking-wider">Vistoria de Retorno (Check-out)</h4>
    </div>

    <form wire:submit.prevent="fecharContrato" class="space-y-5">
        
        {{-- SEÇÃO 1: Dados Cronológicos e Km --}}
        <div class="bg-gray-50/60 p-3 rounded-xl border border-gray-100">
            <span class="text-[10px] font-bold text-blue-600 uppercase tracking-wide block mb-2">1. Coleta de Dados Básicos</span>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Odômetro Final (KM Atual)</label>
                    <div class="relative">
                        <input type="number" wire:model.live.debounce.500ms="odometro_final" required
                               class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm font-mono focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                        <span class="absolute inset-y-0 right-3 flex items-center text-xs text-gray-400 pointer-events-none">km</span>
                    </div>
                    @error('odometro_final') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Data/Hora Real da Devolução</label>
                    <input type="datetime-local" wire:model.live="data_real_entrega" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>
        </div>

        {{-- SEÇÃO 2: Condições do Veículo --}}
        <div class="bg-gray-50/60 p-3 rounded-xl border border-gray-100">
            <span class="text-[10px] font-bold text-blue-600 uppercase tracking-wide block mb-2">2. Estado do Veículo</span>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Nível do Combustível</label>
                    <select wire:model="nivel_combustivel_retorno" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm bg-white focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                        <option value="Cheio">Cheio (1/1)</option>
                        <option value="3/4">3/4</option>
                        <option value="Meio">Meio (1/2)</option>
                        <option value="1/4">1/4</option>
                        <option value="Reserva">Reserva</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Avaliação de Limpeza</label>
                    <select wire:model="avaliacao_limpeza" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm bg-white focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                        <option value="Excelente">Excelente</option>
                        <option value="Bom">Bom</option>
                        <option value="Regular">Regular (Sujeira leve)</option>
                        <option value="Ruim">Ruim (Necessita Lavagem Pesada)</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- SEÇÃO 3: Detalhes & Observações --}}
        <div class="space-y-3">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Conferência de Objetos Retorno (Estepe, Macaco, Chave de Roda...)</label>
                <textarea wire:model="conferencia_obj_retorno" rows="2" placeholder="Ex: Todos os pertences e ferramentas devolvidos corretamente."
                          class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-1 focus:ring-blue-500 focus:border-blue-500"></textarea>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Avarias Identificadas no Retorno</label>
                <textarea wire:model="avarias_retorno" rows="2" required
                          class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-1 focus:ring-blue-500 focus:border-blue-500"></textarea>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Observações Gerais</label>
                <textarea wire:model="observacoes" rows="2" placeholder="Notas internas ou observações extras adicionais."
                          class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-1 focus:ring-blue-500 focus:border-blue-500"></textarea>
            </div>
        </div>

        {{-- SEÇÃO 4: Financeiro --}}
        <div class="bg-gray-50 p-3 rounded-xl border border-gray-100 space-y-3">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Custo Adicional (Multas por sujeira, combustível faltando, etc.)</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-3 flex items-center text-xs text-gray-400 pointer-events-none">R$</span>
                    <input type="number" step="0.01" min="0" wire:model.live.debounce.500ms="custo_adicional" required
                           class="w-full border border-gray-300 rounded-lg pl-8 pr-3 py-1.5 text-sm font-mono focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>

            <div class="border-t border-gray-200 pt-2 space-y-1 text-xs">
                <div class="flex justify-between text-gray-500">
                    <span>Valor Base Original:</span>
                    <span class="font-mono">R$ {{ number_format($contrato->valor_total ?? 0, 2, ',', '.') }}</span>
                </div>
                
                @if($dias_atraso > 0)
                    <div class="flex justify-between text-red-600 font-semibold">
                        <span>Multa Atraso ({{ $dias_atraso }} d):</span>
                        <span class="font-mono">+ R$ {{ number_format($valor_multa_atraso, 2, ',', '.') }}</span>
                    </div>
                @endif

                @if((float)$custo_adicional > 0)
                    <div class="flex justify-between text-amber-600 font-semibold">
                        <span>Custos Adicionais Informados:</span>
                        <span class="font-mono">+ R$ {{ number_format($custo_adicional, 2, ',', '.') }}</span>
                    </div>
                @endif

                <div class="flex justify-between text-sm font-bold text-gray-800 border-t border-gray-200 pt-2 mt-1">
                    <span>Total Final do Fechamento:</span>
                    <span class="text-blue-600 font-mono">R$ {{ number_format($valor_final, 2, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <button type="submit" 
                class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg shadow-sm transition">
            Concluir Check-out e Fechar Conta
        </button>
    </form>
</div>