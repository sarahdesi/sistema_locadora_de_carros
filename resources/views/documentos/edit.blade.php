<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('documentos.index') }}" class="text-gray-500 hover:text-gray-700 transition">← Voltar</a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                📄 Editar/Renovar Documento
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow p-6">
                <form method="POST" action="{{ route('documentos.update', $documento->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        <div class="col-span-1 md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Veículo Associado</label>
                            <select name="veiculo_placa" required
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                                @foreach($veiculos as $veiculo)
                                    <option value="{{ $veiculo->placa }}" {{ old('veiculo_placa', $documento->veiculo_placa) == $veiculo->placa ? 'selected' : '' }}>
                                        {{ $veiculo->marca }} {{ $veiculo->modelo }} — Placa: {{ strtoupper($veiculo->placa) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Documento</label>
                            <select name="tipo" required
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="IPVA" {{ old('tipo', $documento->tipo) == 'IPVA' ? 'selected' : '' }}>IPVA</option>
                                <option value="Licenciamento Anual" {{ old('tipo', $documento->tipo) == 'Licenciamento Anual' ? 'selected' : '' }}>Licenciamento Anual</option>
                                <option value="Seguro Obrigatório" {{ old('tipo', $documento->tipo) == 'Seguro Obrigatório' ? 'selected' : '' }}>Seguro Obrigatório</option>
                                <option value="Vistoria Técnica" {{ old('tipo', $documento->tipo) == 'Vistoria Técnica' ? 'selected' : '' }}>Vistoria Técnica</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Valor Pago (R$)</label>
                            <input type="number" name="valor" step="0.01" value="{{ old('valor', $documento->valor) }}"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div class="col-span-1 md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nova Data de Vencimento</label>
                            <input type="date" name="data_vencimento" value="{{ old('data_vencimento', $documento->data_vencimento) }}" required
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>

                    </div>

                    <div class="mt-8 flex gap-3 border-t border-gray-100 pt-6">
                        <button type="submit" class="px-6 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium text-sm shadow-sm">
                            Salvar Alterações
                        </button>
                        <a href="{{ route('documentos.index') }}" class="px-6 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition text-sm font-medium">
                            Voltar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>