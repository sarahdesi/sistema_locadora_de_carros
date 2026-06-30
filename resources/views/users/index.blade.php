<x-app-layout>
<div class="flex justify-between items-center">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Usuários') }}
        </h2>
    </x-slot>
</div>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-alerta />
      
            <livewire:lista-usuarios />
            
        </div>
    </div>
</x-app-layout>


