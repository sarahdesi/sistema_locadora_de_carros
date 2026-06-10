<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('contratos.show', $contrato->id) }}" class="text-gray-500 hover:text-gray-700">← Voltar</a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Realizar Check-Out (Devolução) — Contrato #{{ $contrato->id }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            
            {{-- LEMBRETE DE COMO O CARRO SAIU (CHECK-IN) --}}
            @if($contrato->checkIn)
                <div class="bg-amber-50 border border-amber-200 text-amber-900 p-4 rounded-xl shadow mb-6 text-sm">
                    <span class="font-bold block text-base mb-2">📌 Dados da Vistoria de Saída (Para Comparação):</span>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        <div><strong>KM Inicial:</strong> {{ number_format($contrato->checkIn->km_inicial, 0, ',', '.') }} KM</div>
                        <div><strong>Combustível Saída:</strong> {{ $contrato->checkIn->nivel_combustivel }}</div>
                        <div class="col-span-2 md:col-span-3"><strong>Objetos Entregues:</strong> <span class="italic text-gray-700">{{ $contrato->checkIn->conferencia_obj }}</span></div>
                    </div>
                </div>
            @endif

            {{-- FORMULÁRIO DE DEVOLUÇÃO --}}
            <div class="bg-white rounded-xl shadow p-6">
                <form method="POST" action="{{ route('check-out.store', $contrato->id) }}">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        {{-- KM Final --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Quilometragem Final (KM)</label>
                            <input type="number" name="km_final" value="{{ old('km_final') }}" 
                                   placeholder="Deve ser maior que {{ $contrato->checkIn->km_inicial ?? 0 }}" required
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-green-500 focus:border-green-500">
                            @error('km_final') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        {{-- Combustível Retorno --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Combustível no Retorno</label>
                            <select name="nivel_combustivel_retorno" required
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-green-500 focus:border-green-500">
                                <option value="">Selecione...</option>
                                <option value="Cheio">Cheio (1/1)</option>
                                <option value="3/4">Três Quartos (3/4)</option>
                                <option value="1/2">Meio Tanque (1/2)</option>
                                <option value="1/4">Um Quarto (1/4)</option>
                                <option value="Reserva">Reserva</option>
                            </select>
                        </div>

                        {{-- Avaliação de Limpeza --}}
                      <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Estado de Limpeza do Veículo</label>
                        <select name="avaliacao_limpeza" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-green-500 focus:border-green-500">
                            <option value="">Selecione o estado...</option>
                            <option value="Limpo (Padrão)" {{ old('avaliacao_limpeza') == 'Limpo (Padrão)' ? 'selected' : '' }}>Limpo</option>
                            <option value="Sujo Leve (Necessita Lavagem Simples)" {{ old('avaliacao_limpeza') == 'Sujo Leve (Necessita Lavagem Simples)' ? 'selected' : '' }}>Necessita Lavagem Simples</option>
                            <option value="Sujo Pesado (Necessita Lavagem Completa)" {{ old('avaliacao_limpeza') == 'Sujo Pesado (Necessita Lavagem Completa)' ? 'selected' : '' }}>Necessita Lavagem Completa</option>
                        </select>
                        @error('avaliacao_limpeza')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>

                        {{-- Custo Adicional --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Custos Adicionais / Taxas (R$)</label>
                            <input type="number" name="custo_adicional" step="0.01" value="{{ old('custo_adicional', '0.00') }}" required
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-green-500 focus:border-green-500">
                            <span class="text-xs text-gray-400 block mt-1">Preencha se houver cobrança de lavagem ou combustível faltando.</span>
                        </div>

                        {{-- Conferência de Objetos Retorno --}}
                        <div class="col-span-1 md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Conferência de Objetos na Devolução</label>
                            <textarea name="conferencia_obj_retorno" rows="2" 
                                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-green-500 focus:border-green-500">{{ old('conferencia_obj_retorno', 'Todos os objetos (estepe, macaco, triângulo) devolvidos em perfeito estado.') }}</textarea>
                        </div>

                        {{-- Novas Avarias --}}
                        <div class="col-span-1 md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Avarias Identificadas no Retorno (Se houver)</label>
                            <textarea name="avarias_retorno" rows="2" placeholder="Ex: Novo risco na porta direita..."
                                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-green-500 focus:border-green-500">{{ old('avarias_retorno') }}</textarea>
                        </div>

                        {{-- Observações --}}
                        <div class="col-span-1 md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Observações Gerais</label>
                            <textarea name="observacoes" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-green-500 focus:border-green-500">{{ old('observacoes') }}</textarea>
                        </div>

                    </div>

                    <div class="mt-8 flex gap-3 border-t border-gray-100 pt-6">
                        <button type="submit"
                                class="px-6 py-2.5 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-medium text-sm shadow-sm">
                            Receber Veículo & Encerrar Contrato
                        </button>
                        <a href="{{ route('contratos.show', $contrato->id) }}"
                           class="px-6 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition text-sm font-medium">
                            Voltar
                        </a>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>