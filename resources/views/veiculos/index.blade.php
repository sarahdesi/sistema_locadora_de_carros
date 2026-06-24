<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Veículos') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- Mensagens de feedback do sistema (Exclusão / Erros de Chave Estrangeira) --}}
            @if(session('success'))
                <div class="mb-4 p-4 text-sm text-emerald-700 bg-emerald-50 rounded-xl border border-emerald-100 flex items-center shadow-sm">
                    <span>✅ {{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 p-4 text-sm text-red-700 bg-red-50 rounded-xl border border-red-100 flex items-center shadow-sm">
                    <span>⚠️ {{ session('error') }}</span>
                </div>
            @endif

            <x-alerta />

            {{-- O componente Livewire entra aqui e gerencia o título, busca, botão e tabela sozinho --}}
            <livewire:lista-veiculos />

        </div>
    </div>
</x-app-layout>