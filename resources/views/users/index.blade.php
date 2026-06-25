<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Usuários') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-alerta />
            
            <h3 class="text-xl font-bold text-gray-700 mb-4">Controle de Usuários</h3>
            
            <livewire:lista-usuarios />
            
        </div>
    </div>
</x-app-layout>