<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Locadora') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="font-sans antialiased bg-gray-100">

    <div class="flex min-h-screen">

        <!-- Menu lateral -->
        <aside class="fixed top-0 left-0 w-64 h-screen bg-white border-r border-gray-200 flex flex-col z-10">

            <!-- Logo -->
            <div class="px-6 py-5 border-b border-gray-200">
                <span class="text-xl font-bold text-blue-600">🚗 Locadora</span>
            </div>

            <!-- Links -->
            <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto">

                <a href="{{ route('veiculos.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition">
                    🚗 Veículos
                </a>

                <a href="{{ route('documentos.index') }}" 
                class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('documentos.*') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-gray-600 hover:bg-gray-50' }}">
                    📄 <span class="sidebar-text">Documentos</span>
                </a>

                <a href="{{ route('contratos.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition">
                    📋 Contratos
                </a>

                @if(auth()->user()->isGerente() || auth()->user()->isOperador())
                    <a href="{{ route('users.index') }}"
                       class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition">
                        👥 Usuários
                    </a>

                    <a href="{{ route('manutencao.index') }}"
                       class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition">
                        🔧 Manutenção
                    </a>

                    <a href="{{ route('alarmes.index') }}"
                       class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition">
                        🔔 Alarmes
                    </a>
                @endif

                @if(auth()->user()->isGerente())
                    <a href="{{ route('relatorios') }}"
                       class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition">
                        📊 Relatórios
                    </a>

                    <a href="{{ route('logs.index') }}"
                       class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition">
                        📝 Logs
                    </a>
                @endif

            </nav>

            <!-- Usuário logado -->
            <div class="px-4 py-4 border-t border-gray-200">
                <p class="text-sm font-medium text-gray-800">{{ auth()->user()->name }}</p>
                <p class="text-xs text-gray-500 mb-3 capitalize">{{ auth()->user()->role->name }}</p>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-sm text-red-500 hover:text-red-700">
                        Sair
                    </button>
                </form>
            </div>

        </aside>

        <!-- Conteúdo principal -->
        <div class="ml-64 flex-1 flex flex-col">

            <!-- Cabeçalho -->
            @isset($header)
                <header class="bg-white shadow">
                    <div class="px-8 py-4">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Conteúdo -->
            <main class="p-8">
                {{ $slot }}
            </main>

        </div>

    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
    @livewireScripts
</body>
</html>