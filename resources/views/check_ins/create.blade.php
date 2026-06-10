<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('contratos.index') }}" class="text-gray-500 hover:text-gray-700 transition">
                ← Voltar
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Realizar Check-In (Saída) — Contrato #{{ $contrato->id }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            
            {{-- Info do Contrato --}}
            <div class="bg-gray-800 text-white p-4 rounded-xl shadow mb-6 flex flex-wrap justify-between items-center text-sm">
                <div>
                    <span class="text-gray-400 block uppercase tracking-wider text-xs font-semibold">Cliente</span>
                    <span class="font-medium text-base">{{ $contrato->cliente->name ?? 'N/A' }}</span>
                </div>
                <div>
                    <span class="text-gray-400 block uppercase tracking-wider text-xs font-semibold">Veículo</span>
                    <span class="font-medium text-base">{{ $contrato->veiculo->marca ?? '' }} {{ $contrato->veiculo->modelo ?? 'N/A' }}</span>
                </div>
                <div>
                    <span class="text-gray-400 block uppercase tracking-wider text-xs font-semibold">Previsão de Retorno</span>
                    <span class="font-medium text-base">{{ \Carbon\Carbon::parse($contrato->data_hora_retorno)->format('d/m/Y H:i') }}</span>
                </div>
            </div>

            {{-- Formulário de Vistoria --}}
            <div class="bg-white rounded-xl shadow p-6">
                <form method="POST" action="{{ route('check-in.store', $contrato) }}">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        {{-- Quilometragem Inicial --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Quilometragem Inicial (KM)</label>
                            <input type="number" name="km_inicial" value="{{ old('km_inicial') }}" 
                                   placeholder="Ex: 45000" required
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                            @error('km_inicial')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Nível de Combustível --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nível do Combustível</label>
                            <select name="nivel_combustivel" required
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Selecione o nível...</option>
                                <option value="Cheio" {{ old('nivel_combustivel') == 'Cheio' ? 'selected' : '' }}>Cheio (1/1)</option>
                                <option value="3/4" {{ old('nivel_combustivel') == '3/4' ? 'selected' : '' }}>Três Quartos (3/4)</option>
                                <option value="1/2" {{ old('nivel_combustivel') == '1/2' ? 'selected' : '' }}>Meio Tanque (1/2)</option>
                                <option value="1/4" {{ old('nivel_combustivel') == '1/4' ? 'selected' : '' }}>Um Quarto (1/4)</option>
                                <option value="Reserva" {{ old('nivel_combustivel') == 'Reserva' ? 'selected' : '' }}>Reserva</option>
                            </select>
                            @error('nivel_combustivel')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Conferência de Objetos / Itens Obrigatórios --}}
                        <div class="col-span-1 md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Conferência de Objetos Obrigatórios</label>
                            <textarea name="conferencia_obj" rows="2" 
                                      placeholder="Ex: Estepe, macaco, triângulo, chave de roda e documento do veículo conferidos e presentes."
                                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">{{ old('conferencia_obj', 'Estepe, macaco, triângulo e chave de roda presentes.') }}</textarea>
                            @error('conferencia_obj')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Registro de Avarias --}}
                        <div class="col-span-1 md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Avarias Identificadas na Saída (Opcional)</label>
                            <textarea name="avarias" rows="3" 
                                      placeholder="Ex: Risco leve no para-choque traseiro, pequeno amassado na porta do motorista..."
                                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">{{ old('avarias') }}</textarea>
                            @error('avarias')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                    </div>

                    {{-- Botões de Envio --}}
                    <div class="mt-8 flex gap-3 border-t border-gray-100 pt-6">
                        <button type="submit"
                                class="px-6 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium text-sm shadow-sm">
                            Liberar Veículo & Iniciar Contrato
                        </button>
                        <a href="{{ route('contratos.index') }}"
                           class="px-6 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition text-sm font-medium">
                            Cancelar
                        </a>
                    </div>

                </form>
            </div>

        </div>
    </div>
</x-app-layout>