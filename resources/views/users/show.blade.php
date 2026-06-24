<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('users.index') }}"
               class="text-gray-500 hover:text-gray-700 transition duration-150">
                ← Voltar
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $usuario->name }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                {{-- Bloco 1: Dados do Usuário --}}
                <div class="bg-white rounded-xl shadow p-6">
                    <h2 class="text-lg font-semibold text-gray-700 mb-4">Informações Pessoais</h2>
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between border-b border-gray-50 pb-2">
                            <button class="text-gray-500">CPF</button>
                            <dd class="font-medium text-gray-900">{{ $usuario->cpf }}</dd>
                        </div>
                        <div class="flex justify-between border-b border-gray-50 pb-2">
                            <dt class="text-gray-500">E-mail (Login)</dt>
                            <dd class="font-medium text-gray-900">{{ $usuario->login }}</dd>
                        </div>
                        <div class="flex justify-between border-b border-gray-50 pb-2">
                            <dt class="text-gray-500">Telefone</dt>
                            <dd class="font-medium text-gray-900">{{ $usuario->telefone ?? 'Não informado' }}</dd>
                        </div>
                        <div class="flex justify-between border-b border-gray-50 pb-2">
                            <dt class="text-gray-500">Data de Nascimento</dt>
                            <dd class="font-medium text-gray-900">
                                {{ $usuario->data_nascimento ? \Carbon\Carbon::parse($usuario->data_nascimento)->format('d/m/Y') : 'Não informada' }}
                            </dd>
                        </div>
                        <div class="flex justify-between border-b border-gray-50 pb-2">
                            <dt class="text-gray-500">CNH</dt>
                            <dd class="font-medium text-gray-900">{{ $usuario->cnh ?? 'Não possui' }}</dd>
                        </div>
                        <div class="flex justify-between border-b border-gray-50 pb-2">
                            <dt class="text-gray-500">Validade da CNH</dt>
                            <dd class="font-medium text-gray-900">
                                {{ $usuario->validade_cnh ? \Carbon\Carbon::parse($usuario->validade_cnh)->format('d/m/Y') : 'N/A' }}
                            </dd>
                        </div>
                        <div class="flex justify-between pt-1">
                            <dt class="text-gray-500">Nível de Acesso</dt>
                            <dd class="font-medium text-blue-600 font-semibold">
                                {{ ucfirst($usuario->role->name ?? 'Cliente') }}
                            </dd>
                        </div>
                    </dl>
                </div>

                {{-- Bloco 2: Histórico de Contratos / Locações --}}
                <div class="bg-white rounded-xl shadow p-6">
                <h2 class="text-lg font-semibold text-gray-700 mb-4">Histórico de Locações</h2>
                @forelse($usuario->contratos as $contrato)
                    <div class="border-b border-gray-100 pb-3 mb-3 text-sm last:border-0 last:pb-0 last:mb-0">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="font-medium text-gray-800">Contrato #{{ $contrato->id }}</p>
                                <p class="text-gray-500 text-xs mt-0.5">
                                    Período: {{ \Carbon\Carbon::parse($contrato->created_at)->format('d/m/Y') }} até {{ \Carbon\Carbon::parse($contrato->data_hora_retorno)->format('d/m/Y') }}
                                </p>
                            </div>
                            <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-gray-100 text-gray-700">
                                {{ ucfirst($contrato->status_contrato ?? 'Pendente') }}
                            </span>
                        </div>
                        <p class="text-emerald-600 font-medium mt-1">
                            R$ {{ number_format($contrato->valor_total ?? 0, 2, ',', '.') }}
                        </p>
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center py-12 text-gray-400">
                        <p class="text-sm">Este usuário ainda não realizou nenhuma locação.</p>
                    </div>
                @endforelse
            </div>

            </div>

        </div>
    </div>
</x-app-layout>