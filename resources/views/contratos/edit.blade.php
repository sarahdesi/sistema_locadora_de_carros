<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('contratos.show', $contrato) }}"
               class="text-gray-500 hover:text-gray-700 transition duration-150">
                ← Voltar
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Editar Contrato {{ $contrato->id }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="bg-white rounded-xl shadow p-6 max-w-2xl mx-auto">
                <form method="POST" action="{{ route('contratos.update', $contrato) }}">
                    @csrf
                    @method('PUT')

                    {{-- Rastreador de Erros --}}
                    @if ($errors->any())
                        <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-600 rounded-lg text-sm">
                            <p class="font-bold mb-2">O Laravel barrou as alterações pelos seguintes motivos:</p>
                            <ul class="list-disc pl-5 space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="grid grid-cols-2 gap-4">

                        {{-- Cliente (Apenas Leitura) --}}
                        <div class="col-span-2 md:col-span-1">
                            <label class="block text-sm font-medium text-gray-400 mb-1">Cliente</label>
                            <input type="text" value="{{ $contrato->cliente->name ?? 'N/A' }}" readonly
                                   class="w-full border border-gray-200 rounded-lg px-3 py-2 bg-gray-50 text-gray-500 cursor-not-allowed text-sm">
                        </div>

                        {{-- Veículo (Apenas Leitura) --}}
                        <div class="col-span-2 md:col-span-1">
                            <label class="block text-sm font-medium text-gray-400 mb-1">Veículo (Placa)</label>
                            <input type="text" value="{{ $contrato->veiculo->marca ?? '' }} — {{ $contrato->veiculo->placa ?? '' }}" readonly
                                   class="w-full border border-gray-200 rounded-lg px-3 py-2 bg-gray-50 text-gray-500 cursor-not-allowed text-sm">
                        </div>

                        {{-- Status do Contrato --}}
                        <div class="col-span-2 md:col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status do Contrato</label>
                            <select name="status_contrato" required
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="aberto" {{ old('status_contrato', $contrato->status_contrato) == 'aberto' ? 'selected' : '' }}>Aberto (Solicitado)</option>
                                <option value="em_andamento" {{ old('status_contrato', $contrato->status_contrato) == 'em_andamento' ? 'selected' : '' }}>Em Andamento (Carro Liberado)</option>
                                <option value="encerrado" {{ old('status_contrato', $contrato->status_contrato) == 'encerrado' ? 'selected' : '' }}>Encerrado (Carro Devolvido)</option>
                                <option value="cancelado" {{ old('status_contrato', $contrato->status_contrato) == 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                            </select>
                            @error('status_contrato')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Nova Data/Hora de Retorno --}}
                        <div class="col-span-2 md:col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nova Data/Hora de Devolução</label>
                            <input type="datetime-local" name="data_hora_retorno" 
                                   value="{{ old('data_hora_retorno', \Carbon\Carbon::parse($contrato->data_hora_retorno)->format('Y-m-d\TH:i')) }}"
                                   required
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                            @error('data_hora_retorno')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Valor da Diária --}}
                        <div class="col-span-2 md:col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Valor da Diária (R$)</label>
                            <input type="number" name="valor_diaria" 
                                   value="{{ old('valor_diaria', $contrato->valor_diaria) }}"
                                   step="0.01" min="0" required
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                            @error('valor_diaria')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Valor Total --}}
                        <div class="col-span-2 md:col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Valor Total do Contrato (R$)</label>
                            <input type="number" name="valor_total" 
                                   value="{{ old('valor_total', $contrato->valor_total) }}"
                                   step="0.01" min="0"
                                   placeholder="Deixe vazio para cálculo automático"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                            @error('valor_total')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                    </div>

                    {{-- Botões de Ação --}}
                    <div class="mt-6 flex gap-3">
                        <button type="submit"
                                class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-150 text-sm font-medium shadow-sm">
                            Salvar Alterações
                        </button>
                        <a href="{{ route('contratos.show', $contrato) }}"
                           class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition duration-150 text-sm font-medium">
                            Cancelar
                        </a>
                    </div>

                </form>
            </div>

        </div>
    </div>
</x-app-layout>