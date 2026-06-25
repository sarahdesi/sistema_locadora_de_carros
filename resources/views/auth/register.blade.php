<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - Cadastro</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-slate-50 text-gray-900">
    
    {{-- Container centralizador idêntico ao fluxo de login --}}
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 px-4">
        
        {{-- Renderiza o formulário reativo e limpo --}}
        <livewire:registrar-cliente />
        
    </div>

</body>
</html>