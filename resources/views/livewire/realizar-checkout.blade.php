<?php

use Livewire\Volt\Component;
use App\Models\Contrato;
use Carbon\Carbon;

new class extends Component {
    public Contrato $contrato;

    public $odometro_final;
    public $data_real_entrega;
    public $notas_retorno = 'Veículo devolvido em boas condições, sem avarias novas.'; // <-- NOVO CAMPO
    
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
        if (in_array($propertyName, ['data_real_entrega', 'odometro_final'])) {
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
        $this->valor_final = $valorBase + $this->valor_multa_atraso;
    }

    public function fecharContrato()
    {
        $this->recalcularCheckout();

        $minKm = $this->contrato->veiculo ? $this->contrato->veiculo->odometro : 0;

        $this->validate([
            'odometro_final' => 'required|numeric|min:' . $minKm,
            'data_real_entrega' => 'required|date',
            'notas_retorno' => 'required|string', // <-- VALIDAÇÃO
        ], [
            'odometro_final.min' => 'O odômetro final não pode ser menor que o KM inicial (' . $minKm . ' km).'
        ]);

        // Finaliza o contrato salvando as notas de devolução
        $this->contrato->update([
            'status_contrato' => 'encerrado',
            'data_hora_retorno_real' => $this->data_real_entrega,
            'valor_total' => $this->valor_final,
            // Caso queira salvar a nota de retorno, pode usar um campo descritivo do contrato:
            // 'observacoes' => $this->notas_retorno 
        ]);

        if ($this->contrato->veiculo) {
            $this->contrato->veiculo->update([
                'odometro' => $this->odometro_final,
                'status' => 'disponivel'
            ]);
        }

        session()->flash('sucesso_checkout', 'Check-out realizado com sucesso! Contrato finalizado.');
        
        return redirect()->route('contratos.show', $this->contrato->id);
    }
}; ?>

<div class="border-t border-gray-100 pt-4 mt-4">
    <h4 class="text-sm font-bold text-gray-800 mb-3 uppercase tracking-wider">🔍 Vistoria de Retorno (Check-out)</h4>

    <form wire:submit.prevent="fecharContrato" class="space-y-4">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- KM Final --}}
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Odômetro Final (KM Atual)</label>
                <div class="relative">
                    <input type="number" wire:model.live.debounce.500ms="odometro_final" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm font-mono">
                    <span class="absolute inset-y-0 right-3 flex items-center text-xs text-gray-400 pointer-events-none">km</span>
                </div>
                @error('odometro_final') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            {{-- Data de Devolução Real --}}
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Data/Hora Real da Devolução</label>
                <input type="datetime-local" wire:model.live="data_real_entrega" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm">
            </div>

            {{-- Observações do Retorno --}}
            <div class="col-span-2">
                <label class="block text-xs font-medium text-gray-500 mb-1">Estado de Devolução / Avarias Novas</label>
                <textarea wire:model="notas_retorno" rows="2" required
                          class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2"></textarea>
            </div>
        </div>

        {{-- Painel Resumo --}}
        <div class="p-3 bg-slate-50 border border-gray-100 rounded-xl space-y-1.5 text-xs">
            <div class="flex justify-between text-gray-600">
                <span>Valor Base Original:</span>
                <span class="font-mono">R$ {{ number_format($contrato->valor_total ?? 0, 2, ',', '.') }}</span>
            </div>
            
            @if($dias_atraso > 0)
                <div class="flex justify-between text-red-600 font-semibold animate-pulse">
                    <span>Multa Atraso ({{ $dias_atraso }} d):</span>
                    <span class="font-mono">+ R$ {{ number_format($valor_multa_atraso, 2, ',', '.') }}</span>
                </div>
            @endif

            <div class="flex justify-between text-sm font-bold text-gray-800 border-t border-gray-200 pt-1.5 mt-1">
                <span>Total Final do Fechamento:</span>
                <span class="text-blue-600 font-mono">R$ {{ number_format($valor_final, 2, ',', '.') }}</span>
            </div>
        </div>

        <button type="submit" 
                class="w-full py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg shadow-sm transition">
            Concluir Check-out e Fechar Conta
        </button>
    </form>
</div>