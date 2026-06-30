<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('manutencao.index') }}" class="text-gray-500 hover:text-gray-700 transition">
                ← Voltar
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                🔧 Atualizar Ordem de Serviço {{ $manutencao->id }}
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
                
                {{-- INJETA O COMPONENTE DE EDIÇÃO DO LIVEWIRE PASSANDO O REGISTRO ATUAL --}}
                <livewire:editar-manutencao :manutencao="$manutencao" />

            </div>
        </div>
    </div>
</x-app-layout>