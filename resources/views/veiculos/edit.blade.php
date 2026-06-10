<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('veiculos.index') }}"
               class="text-gray-500 hover:text-gray-700 transition duration-150">
                ← Voltar
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Editar Veículo') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="bg-white rounded-xl shadow p-6 max-w-2xl mx-auto">
                <form method="POST" action="{{ route('veiculos.update', $veiculo) }}">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-2 gap-4">

                        {{-- Placa (Desabilitada) --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Placa</label>
                            <input type="text" value="{{ $veiculo->placa }}" disabled
                                   class="w-full border border-gray-200 rounded-lg px-3 py-2 bg-gray-50 text-gray-400 uppercase">
                        </div>

                        {{-- Modelo --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Modelo</label>
                            <input type="text" name="modelo" value="{{ old('modelo', $veiculo->modelo) }}"
                                   maxlength="100"
                                   required
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2">
                            @error('modelo')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Marca --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Marca</label>
                            <input type="text" name="marca" value="{{ old('marca', $veiculo->marca) }}"
                                   maxlength="100"
                                   required
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2">
                            @error('marca')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Cor --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Cor</label>
                            <input type="text" name="cor" value="{{ old('cor', $veiculo->cor) }}"
                                   maxlength="50"
                                   required
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2">
                            @error('cor')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Ano --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ano</label>
                            <input type="number" name="ano" value="{{ old('ano', $veiculo->ano) }}"
                                   min="1990"
                                   max="2026"
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
                                @foreach(['flex','gasolina','diesel','eletrico'] as $tipo)
                                    <option value="{{ $tipo }}"
                                        {{ old('combustivel', $veiculo->combustivel) == $tipo ? 'selected' : '' }}>
                                        {{ ucfirst($tipo) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('combustivel')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Status --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select name="status" required
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2">
                                @foreach(['disponivel','locado','manutencao','reservado'] as $s)
                                    <option value="{{ $s }}"
                                        {{ old('status', $veiculo->status) == $s ? 'selected' : '' }}>
                                        {{ ucfirst($s) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Odômetro --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Odômetro (km)</label>
                            <input type="number" name="odometro" value="{{ old('odometro', $veiculo->odometro) }}"
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
                            Salvar Alterações
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