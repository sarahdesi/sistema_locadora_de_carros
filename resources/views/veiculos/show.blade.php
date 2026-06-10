<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('veiculos.index') }}"
               class="text-gray-500 hover:text-gray-700 transition duration-150">
                ← Voltar
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $veiculo->marca }} {{ $veiculo->modelo }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                {{-- Bloco 1: Dados do veículo --}}
                <div class="bg-white rounded-xl shadow p-6">
                    <h2 class="text-lg font-semibold text-gray-700 mb-4">Ficha Técnica</h2>
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between border-b border-gray-50 pb-2">
                            <dt class="text-gray-500">Placa</dt>
                            <dd class="font-medium text-gray-900">{{ $veiculo->placa }}</dd>
                        </div>
                        <div class="flex justify-between border-b border-gray-50 pb-2">
                            <dt class="text-gray-500">RENAVAM</dt>
                            <dd class="font-medium text-gray-900">{{ $veiculo->renavam }}</dd>
                        </div>
                        <div class="flex justify-between border-b border-gray-50 pb-2">
                            <dt class="text-gray-500">Cor</dt>
                            <dd class="font-medium text-gray-900">{{ $veiculo->cor }}</dd>
                        </div>
                        <div class="flex justify-between border-b border-gray-50 pb-2">
                            <dt class="text-gray-500">Ano</dt>
                            <dd class="font-medium text-gray-900">{{ $veiculo->ano }}</dd>
                        </div>
                        <div class="flex justify-between border-b border-gray-50 pb-2">
                            <dt class="text-gray-500">Combustível</dt>
                            <dd class="font-medium text-gray-900">{{ ucfirst($veiculo->combustivel) }}</dd>
                        </div>
                        <div class="flex justify-between border-b border-gray-50 pb-2">
                            <dt class="text-gray-500">Odômetro</dt>
                            <dd class="font-medium text-gray-900">{{ number_format($veiculo->odometro, 0, ',', '.') }} km</dd>
                        </div>
                        <div class="flex justify-between pt-1">
                            <dt class="text-gray-500">Status</dt>
                            <dd class="font-medium text-gray-900">{{ ucfirst($veiculo->status) }}</dd>
                        </div>
                    </dl>
                </div>

                {{-- Bloco 2: Histórico de manutenções --}}
                <div class="bg-white rounded-xl shadow p-6">
                    <h2 class="text-lg font-semibold text-gray-700 mb-4">Manutenções</h2>
                    @forelse($veiculo->manutencoes as $manutencao)
                        <div class="border-b border-gray-100 pb-3 mb-3 text-sm last:border-0 last:pb-0 last:mb-0">
                            <p class="font-medium text-gray-800">{{ $manutencao->descricao }}</p>
                            <p class="text-gray-500 mt-1">
                                {{ \Carbon\Carbon::parse($manutencao->data_realizada)->format('d/m/Y') }}
                                <span class="mx-1.5">·</span> 
                                <span class="text-emerald-600 font-medium">R$ {{ number_format($manutencao->custo, 2, ',', '.') }}</span>
                            </p>
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center py-8 text-gray-400">
                            <p class="text-sm">Nenhuma manutenção registrada para este veículo.</p>
                        </div>
                    @endforelse
                </div>

            </div>

        </div>
    </div>
</x-app-layout>