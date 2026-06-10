<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('manutencao.index') }}" class="text-gray-500 hover:text-gray-700 transition">
                ← Voltar
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                🔧 Atualizar Ordem de Serviço — O.S. #{{ $manutencao->id }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            
            {{-- Detalhes Fixos do Carro na Oficina --}}
            <div class="bg-gray-800 text-white p-4 rounded-xl shadow mb-6 flex justify-between items-center text-sm">
                <div>
                    <span class="text-gray-400 block uppercase tracking-wider text-xs font-semibold">Veículo</span>
                    <span class="font-medium text-base">{{ $manutencao->veiculo->marca ?? 'N/A' }} {{ $manutencao->veiculo->modelo ?? '' }}</span>
                </div>
                <div>
                    <span class="text-gray-400 block uppercase tracking-wider text-xs font-semibold">Placa</span>
                    <span class="font-mono bg-gray-700 px-2 py-0.5 rounded text-sm uppercase font-semibold border border-gray-600">{{ $manutencao->veiculo->placa ?? '---' }}</span>
                </div>
                <div>
                    <span class="text-gray-400 block uppercase tracking-wider text-xs font-semibold">Data de Entrada</span>
                    <span class="font-medium text-base">{{ \Carbon\Carbon::parse($manutencao->data_entrada)->format('d/m/Y') }}</span>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow p-6">
                <form method="POST" action="{{ route('manutencao.update', $manutencao->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        {{-- Tipo de Manutenção --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Manutenção</label>
                            <select name="tipo_manutencao" required
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="Preventiva" {{ old('tipo_manutencao', $manutencao->tipo_manutencao) == 'Preventiva' ? 'selected' : '' }}>Preventiva</option>
                                <option value="Corretiva" {{ old('tipo_manutencao', $manutencao->tipo_manutencao) == 'Corretiva' ? 'selected' : '' }}>Corretiva</option>
                                <option value="Estética" {{ old('tipo_manutencao', $manutencao->tipo_manutencao) == 'Estética' ? 'selected' : '' }}>Estética</option>
                            </select>
                        </div>

                        {{-- Custo Atualizado --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Custo Final Fechado (R$)</label>
                            <input type="number" name="custo" step="0.01" value="{{ old('custo', $manutencao->custo) }}" required
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        {{-- Data de Entrada --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Data de Entrada</label>
                            <input type="date" name="data_entrada" value="{{ old('data_entrada', $manutencao->data_entrada) }}" required
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        {{-- Data de Saída --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Data de Saída (Fim do Serviço)</label>
                            <input type="date" name="data_saida" value="{{ old('data_saida', $manutencao->data_saida ?? date('Y-m-d')) }}"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                            {{-- FIX: De </error> para @enderror --}}
                            @error('data_saida')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Status da Ordem de Serviço --}}
                        <div class="col-span-1 md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Situação / Status Atual</label>
                            <select name="status" required
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="em_andamento" {{ old('status', $manutencao->status) == 'em_andamento' ? 'selected' : '' }}>Na Oficina (Em andamento)</option>
                                <option value="concluida" {{ old('status', $manutencao->status) == 'concluida' ? 'selected' : '' }}>Concluída (Liberar Carro de Volta à Frota)</option>
                                <option value="cancelada" {{ old('status', $manutencao->status) == 'cancelada' ? 'selected' : '' }}>Cancelada</option>
                            </select>
                        </div>

                        {{-- Descrição do Serviço --}}
                        <div class="col-span-1 md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Descrição do Serviço / Notas Adicionais da Oficina</label>
                            <textarea name="descricao" rows="4" required
                                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">{{ old('descricao', $manutencao->descricao) }}</textarea>
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
            </div>

        </div>
    </div>
</x-app-layout>