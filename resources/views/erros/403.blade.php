<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Acesso Negado</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" rel="stylesheet" />
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="text-center">
        <h1 class="text-6xl font-bold text-red-500 mb-4">403</h1>
        <p class="text-xl text-gray-700 mb-2">Acesso Negado</p>
        <p class="text-gray-500 mb-6">Você não tem permissão para acessar essa página.</p>
        <a href="{{ url()->previous() }}"
           class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            Voltar
        </a>
    </div>
</body>
</html>