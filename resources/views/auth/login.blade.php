<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Locadora</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" rel="stylesheet" />
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">

    <div class="w-full max-w-md bg-white rounded-2xl shadow-lg p-8">

        <!-- Logo -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-blue-600">Locadora</h1>
            <p class="text-gray-500 text-sm mt-1">Sistema de Gestão de Veículos</p>
        </div>

        <!-- Erros -->
        @if($errors->any())
            <div class="p-4 mb-4 text-red-800 bg-red-100 rounded-lg text-sm">
                {{ $errors->first() }}
            </div>
        @endif

        <!-- Formulário -->
        <form method="POST" action="{{ route('login') }}">
            @csrf

            <!-- Campo login -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Login
                </label>
                <input
                    type="text"
                    name="login"
                    value="{{ old('login') }}"
                    required
                    autofocus
                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5
                           focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="seu@login.com"
                />
                @error('login')
                    <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                @enderror
            </div>

            <!-- Campo senha -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Senha
                </label>
                <input
                    type="password"
                    name="password"
                    required
                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5
                           focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="••••••••"
                />
                @error('password')
                    <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                @enderror
            </div>

            <!-- Lembrar -->
            <div class="flex items-center mb-6">
                <input type="checkbox" name="remember" id="remember"
                       class="w-4 h-4 text-blue-600 rounded border-gray-300">
                <label for="remember" class="ml-2 text-sm text-gray-600">
                    Lembrar de mim
                </label>
            </div>

            <!-- Botão -->
            <button type="submit"
                    class="w-full bg-blue-600 text-white py-2.5 rounded-lg
                           hover:bg-blue-700 font-medium transition">
                Entrar
            </button>

        </form>

    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
</body>
</html>