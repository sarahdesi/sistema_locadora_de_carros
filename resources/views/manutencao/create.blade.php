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
                
                {{-- INJETA O COMPONENTE DE CRIAÇÃO DO LIVEWIRE --}}
                <livewire:criar-manutencao />

            </div>
        </div>
    </div>
</x-app-layout>