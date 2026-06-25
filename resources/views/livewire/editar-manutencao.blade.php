<?php

use Livewire\Volt\Component;
use App\Models\Manutencao;
use App\Models\Veiculo;
use Illuminate\Support\Facades\Gate;

new class extends Component {
    public Manutencao $manutencao;

    // Propriedades espelhadas
    public $tipo_manutencao;
    public $custo;
    public $data_entrada;
    public $data_saida;
    public $status;
    public $descricao;

    public function mount(Manutencao $manutencao)
    {
        Gate::authorize('is-staff');
        
        $this->manutencao = $manutencao;
        $this->tipo_manutencao = $manutencao->tipo_manutencao;
        $this->custo = $manutencao->custo;
        $this->data_entrada = $manutencao->data_entrada;
        $this->data_saida = $manutencao->data_saida ?? date('Y-m-d');
        $this->status = $manutencao->status;
        $this->descricao = $manutencao->descricao;
    }

    public function atualizar()
    {
        Gate::authorize('is-staff');

        // Validação dinâmica condicional
        $regras = [
            'tipo_manutencao' => 'required|string',
            'descricao'       => 'required|string',
            'data_entrada'    => 'required|date',
            'status'          => 'required|in:em_andamento,concluida,cancelada',
            'custo'           => 'required|numeric|min:0',
        ];

        if ($this->status === 'concluida') {
            $regras['data_saida'] = 'required|date|after_or_equal:data_entrada';
        } else {
            $regras['data_saida'] = 'nullable|date';
        }

        $validated = $this->validate($regras);

        // Regra de negócios para devolução do veículo à frota
        if (in_array($this->status, ['concluida', 'cancelada'])) {
            if ($this->status === 'concluida' && empty($this->data_saida)) {
                $validated['data_saida'] = now()->format('Y-m-d');
            }

            if ($this->manutencao->veiculo) {
                $this->manutencao->veiculo->update(['status' => 'disponivel']);
            }
        } else {
            if ($this->manutencao->veiculo) {
                $this->manutencao->veiculo->update(['status' => 'em_manutencao']);
            }
            $validated['data_saida'] = null; // Reseta se voltou para a oficina
        }

        $this->manutencao->update($validated);

        session()->flash('success', 'Histórico de manutenção atualizado com sucesso!');

        return redirect()->route('manutencao.index');
    }
}; ?>

<form wire:submit.prevent="atualizar" class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        {{-- Tipo de Manutenção --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Manutenção</label>
            <select wire:model="tipo_manutencao" required
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                <option value="Preventiva">Preventiva</option>
                <option value="Corretiva">Corretiva</option>
                <option value="Estética">Estética</option>
            </select>
            @error('tipo_manutencao') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        {{-- Data de Entrada --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Data de Entrada</label>
            <input type="date" wire:model="data_entrada" required
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
            @error('data_entrada') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        {{-- Status da Ordem de Serviço --}}
        <div class="col-span-1 md:col-span-2 bg-slate-50 p-4 rounded-xl border border-gray-100">
            <label class="block text-sm font-semibold text-gray-800 mb-1">Situação / Status Atual</label>
            <select wire:model.live="status" required
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500 bg-white font-medium">
                <option value="em_andamento">Na Oficina (Em andamento)</option>
                <option value="concluida">Concluída (Liberar Carro de Volta à Frota)</option>
                <option value="cancelada">Cancelada</option>
            </select>
            @error('status') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        {{-- BLOCO CONDICIONAL: APARECE E DESAPARECE EM TEMPO REAL --}}
        @if($status === 'concluida')
            <div class="col-span-1 md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6 p-4 bg-emerald-50/60 border border-emerald-100 rounded-xl animate-fadeIn">
                <div>
                    <label class="block text-sm font-medium text-emerald-900 mb-1">Custo Final Fechado (R$)</label>
                    <input type="number" wire:model="custo" step="0.01" required
                           class="w-full border border-emerald-300 rounded-lg px-3 py-2 text-sm focus:ring-emerald-500 focus:border-emerald-500 bg-white font-mono">
                    @error('custo') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-emerald-900 mb-1">Data de Saída (Fim do Serviço)</label>
                    <input type="date" wire:model="data_saida" required
                           class="w-full border border-emerald-300 rounded-lg px-3 py-2 text-sm focus:ring-emerald-500 focus:border-emerald-500 bg-white">
                    @error('data_saida') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>
        @else
            {{-- Se estiver em andamento ou cancelada, mostramos apenas o custo estimado base, mas sem exigir data de fechamento --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Custo Estimado Base (R$)</label>
                <input type="number" wire:model="custo" step="0.01" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                @error('custo') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
        @endif

        {{-- Descrição do Serviço --}}
        <div class="col-span-1 md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Descrição do Serviço / Notas Adicionais da Oficina</label>
            <textarea wire:model="descricao" rows="4" required
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500"></textarea>
            @error('descricao') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

    </div>

    {{-- Botões de Envio --}}
    <div class="mt-8 flex gap-3 border-t border-gray-100 pt-6">
        <button type="submit"
                class="px-6 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium text-sm shadow-sm">
            Salvar Histórico Técnico
        </button>
        <a href="{{ route('manutencao.index') }}"
           class="px-6 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition text-sm font-medium">
            Voltar
        </a>
    </div>
</form>