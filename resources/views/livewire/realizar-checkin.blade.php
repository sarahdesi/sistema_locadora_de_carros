<?php

use Livewire\Volt\Component;
use App\Models\Contrato;
use Carbon\Carbon;

new class extends Component {
    public Contrato $contrato;

    // Campos da Vistoria de Saída
    public $km_inicial;
    public $nivel_combustivel = 'Cheio';
    public $conferencia_obj = 'Estepe, Macaco, Chave de Roda, Triângulo, Documento do Veículo.';
    public $avarias = 'Nenhuma avaria aparente.';

    public function mount(Contrato $contrato)
    {
        $this->contrato = $contrato;
        // Puxa o KM atual do carro de forma automática
        $this->km_inicial = $contrato->veiculo ? $contrato->veiculo->odometro : 0;
    }

    public function confirmarCheckin()
    {
        $this->validate([
            'km_inicial' => 'required|numeric|min:0',
            'nivel_combustivel' => 'required|string',
            'conferencia_obj' => 'required|string',
            'avarias' => 'nullable|string',
        ]);

        // 1. Cria o registro de Vistoria (Check-in) vinculado ao contrato
        // Ajuste os nomes das colunas se forem ligeiramente diferentes no seu banco
        $this->contrato->checkIn()->create([
            'km_inicial' => $this->km_inicial,
            'nivel_combustivel' => $this->nivel_combustivel,
            'data_hora_saida' => Carbon::now(),
            'status' => 'finalizado',
            'conferencia_obj' => $this->conferencia_obj,
            'avarias' => $this->avarias,
        ]);

        // 2. Altera o contrato para 'em_andamento'
        $this->contrato->update([
            'status_contrato' => 'em_andamento',
        ]);

        // 3. Altera o veículo para 'locado'
        if ($this->contrato->veiculo) {
            $this->contrato->veiculo->update([
                'status' => 'locado'
            ]);
        }

        session()->flash('sucesso_checkin', 'Check-in concluído! Vistoria salva e veículo liberado.');

        return redirect()->route('contratos.show', $this->contrato->id);
    }
}; ?>

<div class="bg-slate-50 border border-gray-200 rounded-xl p-5 shadow-sm">
    <h4 class="text-sm font-bold text-gray-800 mb-1 uppercase tracking-wider">📋 Vistoria de Saída (Check-in)</h4>
    <p class="text-xs text-gray-500 mb-4">Preencha os dados do veículo antes de liberar a saída com o cliente.</p>
    
    <form wire:submit.prevent="confirmarCheckin" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            
            {{-- KM Inicial --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Quilometragem Inicial</label>
                <input type="number" wire:model="km_inicial" required
                       class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 bg-gray-100" readonly>
                <p class="text-[10px] text-gray-400 mt-0.5">Capturado automaticamente do cadastro do veículo.</p>
            </div>

            {{-- Nível de Combustível --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Nível do Tanque</label>
                <select wire:model="nivel_combustivel" required
                        class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2">
                    <option value="Cheio">Cheio (1/1)</option>
                    <option value="3/4">3/4</option>
                    <option value="Meio">Meio (1/2)</option>
                    <option value="1/4">1/4</option>
                    <option value="Reserva">Reserva</option>
                </select>
            </div>

            {{-- Objetos de Conferência --}}
            <div class="col-span-2">
                <label class="block text-xs font-medium text-gray-600 mb-1">Itens Obrigatórios de Conferência</label>
                <textarea wire:model="conferencia_obj" rows="2" required
                          class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2"></textarea>
            </div>

            {{-- Avarias do Veículo --}}
            <div class="col-span-2">
                <label class="block text-xs font-medium text-gray-600 mb-1">Avarias / Riscos / Amassados (Saída)</label>
                <textarea wire:model="avarias" rows="2" placeholder="Ex: Risco leve na porta do motorista..."
                          class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2"></textarea>
            </div>

        </div>

        <button type="submit" 
                class="w-full mt-2 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg shadow-sm transition flex items-center justify-center gap-2">
            Confirmar Vistoria e Liberar Carro
        </button>
    </form>
</div>