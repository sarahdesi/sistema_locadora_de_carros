<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('contratos.index') }}"
               class="text-gray-500 hover:text-gray-700 transition duration-150">
                ← Voltar
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Nova Locação') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="bg-white rounded-xl shadow p-6 max-w-2xl mx-auto">
                <form method="POST" action="{{ route('contratos.store') }}">
                    @csrf

                    {{-- Rastreador de Erros de Validação --}}
                    @if ($errors->any())
                        <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-600 rounded-lg text-sm">
                            <p class="font-bold mb-2">O Laravel barrou a locação pelos seguintes motivos:</p>
                            <ul class="list-disc pl-5 space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="grid grid-cols-2 gap-4">

                        {{-- Seleção de Cliente: Visível apenas para Gerente/Operador (Staff) --}}
                        @can('is-staff')
                            <div class="col-span-2 md:col-span-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Selecionar Cliente</label>
                                <select name="cliente_id" required
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Selecione o cliente...</option>
                                    @foreach($clientes as $cliente)
                                        <option value="{{ $cliente->id }}" {{ old('cliente_id') == $cliente->id ? 'selected' : '' }}>
                                            {{ $cliente->name }} (CPF: {{ $cliente->cpf }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('cliente_id')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>
                        @endcan

                        {{-- Seleção do Veículo: Ocupa 1 coluna se for staff, ou 2 colunas se for o próprio cliente --}}
                        <div class="{{ auth()->user()->can('is-staff') ? 'col-span-2 md:col-span-1' : 'col-span-2' }}">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Veículo Disponível</label>
                            <select name="veiculo_id" required
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Selecione um veículo...</option>
                                @foreach($veiculosDisponiveis as $veiculo)
                                    <option value="{{ $veiculo->id }}" {{ old('veiculo_id') == $veiculo->id ? 'selected' : '' }}>
                                        {{ $veiculo->marca }} {{ $veiculo->modelo }} — Placa: {{ $veiculo->placa }}
                                    </option>
                                @endforeach
                            </select>
                            @error('veiculo_id')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Data e Hora de Retorno (Devolução) --}}
                        <div class="col-span-2 md:col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Data/Hora Prevista de Devolução</label>
                            <input type="datetime-local" name="data_hora_retorno" 
                                   value="{{ old('data_hora_retorno') }}"
                                   min="{{ now()->format('Y-m-d\TH:i') }}"
                                   required
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2">
                            @error('data_hora_retorno')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Valor da Diária --}}
                        <div class="col-span-2 md:col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Valor da Diária (R$)</label>
                            <input type="number" name="valor_diaria" 
                                   value="{{ old('valor_diaria') }}"
                                   step="0.01"
                                   min="0"
                                   placeholder="0,00"
                                   required
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2">
                            @error('valor_diaria')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                    </div>

                    {{-- Botões de Ação --}}
                    <div class="mt-6 flex gap-3">
                        <button type="submit"
                                class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-150 font-medium text-sm shadow-sm">
                            Confirmar Locação
                        </button>
                        <a href="{{ route('contratos.index') }}"
                           class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition duration-150 text-sm font-medium">
                            Cancelar
                        </a>
                    </div>

                </form>
            </div>

        </div>
    </div>
</x-app-layout>