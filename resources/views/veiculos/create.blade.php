<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('veiculos.index') }}"
               class="text-gray-500 hover:text-gray-700 transition duration-150">
                ← Voltar
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Novo Veículo') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="bg-white rounded-xl shadow p-6 max-w-2xl mx-auto">
                <form method="POST" action="{{ route('veiculos.store') }}">
                    @csrf

                    <div class="grid grid-cols-2 gap-4">

                        {{-- Placa --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Placa</label>
                            <input type="text" name="placa" value="{{ old('placa') }}"
                                   maxlength="7"
                                   placeholder="ABC1D23"
                                   required
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500 uppercase">
                            @error('placa')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Modelo --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Modelo</label>
                            <input type="text" name="modelo" value="{{ old('modelo') }}"
                                   maxlength="100"
                                   placeholder="Ex: Onix"
                                   required
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2">
                            @error('modelo')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Marca --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Marca</label>
                            <input type="text" name="marca" value="{{ old('marca') }}"
                                   maxlength="100"
                                   placeholder="Ex: Chevrolet"
                                   required
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2">
                            @error('marca')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- RENAVAM --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">RENAVAM</label>
                            <input type="text" name="renavam" value="{{ old('renavam') }}"
                                   maxlength="11"
                                   pattern="\d{11}"
                                   placeholder="12345678901"
                                   required
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2">
                            @error('renavam')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Cor --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Cor</label>
                            <input type="text" name="cor" value="{{ old('cor') }}"
                                   maxlength="50"
                                   placeholder="Ex: Preto"
                                   required
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2">
                            @error('cor')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Ano --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ano</label>
                            <input type="number" name="ano" value="{{ old('ano') }}"
                                   min="1990"
                                   max="2026"
                                   placeholder="Ex: 2024"
                                   required
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2">
                            @error('ano')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Combustível --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Combustível</label>
                            <select name="combustivel" required
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2">
                                <option value="">Selecione...</option>
                                <option value="flex"      {{ old('combustivel') == 'flex'      ? 'selected' : '' }}>Flex</option>
                                <option value="gasolina"  {{ old('combustivel') == 'gasolina'  ? 'selected' : '' }}>Gasolina</option>
                                <option value="diesel"    {{ old('combustivel') == 'diesel'    ? 'selected' : '' }}>Diesel</option>
                                <option value="eletrico"  {{ old('combustivel') == 'eletrico'  ? 'selected' : '' }}>Elétrico</option>
                            </select>
                            @error('combustivel')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Odômetro --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Odômetro (km)</label>
                            <input type="number" name="odometro" value="{{ old('odometro', 0) }}"
                                   min="0"
                                   required
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2">
                            @error('odometro')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                    </div>

                    {{-- Botões de Ação --}}
                    <div class="mt-6 flex gap-3">
                        <button type="submit"
                                class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-150">
                            Salvar
                        </button>
                        <a href="{{ route('veiculos.index') }}"
                           class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition duration-150">
                            Cancelar
                        </a>
                    </div>

                </form>
            </div>

        </div>
    </div>
</x-app-layout>