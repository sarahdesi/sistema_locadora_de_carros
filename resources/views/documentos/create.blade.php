<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('documentos.index') }}" class="text-gray-500 hover:text-gray-700 transition">← Voltar</a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                 Lançar Documento 
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow p-6">
                <form method="POST" action="{{ route('documentos.store') }}">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        {{-- Selecionar Veículo pela Placa --}}
                        <div class="col-span-1 md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Veículo Alvo (Placa)</label>
                            <select name="veiculo_placa" required
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Selecione o veículo...</option>
                                @foreach($veiculos as $veiculo)
                                    <option value="{{ $veiculo->placa }}" {{ old('veiculo_placa') == $veiculo->placa ? 'selected' : '' }}>
                                        {{ $veiculo->marca }} {{ $veiculo->modelo }} — Placa: {{ strtoupper($veiculo->placa) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Tipo de Documento --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Taxa / Documento</label>
                            <select name="tipo" required
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Selecione...</option>
                                <option value="IPVA" {{ old('tipo') == 'IPVA' ? 'selected' : '' }}>IPVA</option>
                                <option value="Licenciamento Anual" {{ old('tipo') == 'Licenciamento Anual' ? 'selected' : '' }}>Licenciamento Anual</option>
                                <option value="Seguro Obrigatório" {{ old('tipo') == 'Seguro Obrigatório' ? 'selected' : '' }}>Seguro Obrigatório (DPVAT / Frota)</option>
                                <option value="Vistoria Técnica" {{ old('tipo') == 'Vistoria Técnica' ? 'selected' : '' }}>Vistoria Técnica Cadastral</option>
                            </select>
                        </div>

                        {{-- Valor da Guia --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Valor do Imposto/Guia (R$)</label>
                            <input type="number" name="valor" step="0.01" value="{{ old('valor') }}" placeholder="Opcional"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        {{-- Data de Vencimento --}}
                        <div class="col-span-1 md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Data limite de Vencimento</label>
                            <input type="date" name="data_vencimento" value="{{ old('data_vencimento') }}" required
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                            <span class="text-xs text-gray-400 block mt-1">Se esta data for menor do que hoje, o Centro de Alarmes bloqueará novas locações deste carro.</span>
                        </div>

                    </div>

                    <div class="mt-8 flex gap-3 border-t border-gray-100 pt-6">
                        <button type="submit"
                                class="px-6 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium text-sm shadow-sm">
                            Vincular Documento à Placa
                        </button>
                        <a href="{{ route('documentos.index') }}" class="px-6 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition text-sm font-medium">
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>