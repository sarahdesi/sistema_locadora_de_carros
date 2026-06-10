<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('manutencao.index') }}" class="text-gray-500 hover:text-gray-700 transition">
                ← Voltar
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                🔧 Registrar Entrada em Manutenção
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            
            <div class="bg-white rounded-xl shadow p-6">
                <form method="POST" action="{{ route('manutencao.store') }}">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        {{-- Selecionar Veículo --}}
                        <div class="col-span-1 md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Veículo para Manutenção</label>
                            <select name="veiculo_id" required
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Selecione o veículo...</option>
                                @foreach($veiculos as $veiculo)
                                    <option value="{{ $veiculo->id }}" {{ old('veiculo_id') == $veiculo->id ? 'selected' : '' }}>
                                        {{ $veiculo->marca }} {{ $veiculo->modelo }} — Placa: {{ strtoupper($veiculo->placa) }} (Status Atual: {{ ucfirst($veiculo->status) }})
                                    </option>
                                @endforeach
                            </select>
                            @error('veiculo_id')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                            <p class="text-xs text-gray-400 mt-1">Nota: Apenas os veículos que não estão atualmente na oficina são listados.</p>
                        </div>

                        {{-- Tipo de Manutenção --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Manutenção</label>
                            <select name="tipo_manutencao" required
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Selecione...</option>
                                <option value="Preventiva" {{ old('tipo_manutencao') == 'Preventiva' ? 'selected' : '' }}>Preventiva (Revisão, Óleo, Filtros)</option>
                                <option value="Corretiva" {{ old('tipo_manutencao') == 'Corretiva' ? 'selected' : '' }}>Corretiva (Conserto de Falhas/Avarias)</option>
                                <option value="Estética" {{ old('tipo_manutencao') == 'Estética' ? 'selected' : '' }}>Estética (Pintura, Detailing, Higienização)</option>
                            </select>
                            @error('tipo_manutencao')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Custo Estimado --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Custo Estimado Inicial (R$)</label>
                            <input type="number" name="custo" step="0.01" value="{{ old('custo', '0.00') }}" required
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                            {{-- CORRIGIDO AQUI: De </error> para @enderror --}}
                            @error('custo')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Data de Entrada --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Data de Entrada</label>
                            <input type="date" name="data_entrada" value="{{ old('data_entrada', date('Y-m-d')) }}" required
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                            @error('data_entrada')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Descrição do Serviço --}}
                        <div class="col-span-1 md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Descrição Detalhada do Serviço</label>
                            <textarea name="descricao" rows="4" required 
                                      placeholder="Ex: Substituição das pastilhas de travão dianteiras, mudança de óleo do motor e alinhamento de direção."
                                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">{{ old('descricao') }}</textarea>
                            @error('descricao')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
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
            </div>

        </div>
    </div>
</x-app-layout>