<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Usuario;
use Illuminate\Support\Facades\Gate;

new class extends Component {
    use WithPagination;

    public $busca = '';

    public function updatingBusca()
    {
        $this->resetPage();
    }

    public function with(): array
    {
        Gate::authorize('is-staff');

        $usuarios = Usuario::query()
            ->when($this->busca, function ($query) {
                $query->where('name', 'ILIKE', '%' . $this->busca . '%')
                      ->orWhere('cpf', 'ILIKE', '%' . $this->busca . '%')
                      ->orWhere('login', 'ILIKE', '%' . $this->busca . '%');
            })
            ->latest()
            ->paginate(8);

        return [
            'usuarios' => $usuarios
        ];
    }
}; ?>

<div>
    {{-- Barra de Busca e Botão Novo --}}
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="relative w-full md:max-w-md">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </span>
            <input type="text" 
                   wire:model.live.debounce.300ms="busca" 
                   placeholder="Buscar por nome, CPF ou login..." 
                   class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 bg-white text-sm shadow-sm transition">
        </div>

        <a href="{{ route('users.create') }}" 
           class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-xl shadow-sm transition inline-flex items-center gap-2 justify-center">
            <span>+ Novo Usuário</span>
        </a>
    </div>

    {{-- Tabela de Usuários --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100 text-xs font-semibold uppercase text-gray-500 tracking-wider">
                        <th class="p-4">CPF</th>
                        <th class="p-4">Nome</th>
                        <th class="p-4">Data de Nascimento</th>
                        <th class="p-4">CNH</th>
                        <th class="p-4">Validade CNH</th>
                        <th class="p-4">Telefone</th>
                        <th class="p-4 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-gray-700">
                    @forelse($usuarios as $usuario)
                        <tr class="hover:bg-slate-50/80 transition duration-150">
                            <td class="p-4 font-mono text-xs text-gray-500">{{ $usuario->cpf }}</td>
                            <td class="p-4 font-medium text-gray-900">
                                {{ $usuario->name }}
                                <span class="block text-[11px] text-gray-400 font-normal">{{ $usuario->login }}</span>
                            </td>
                            <td class="p-4">
                                {{ $usuario->data_nascimento ? \Carbon\Carbon::parse($usuario->data_nascimento)->format('d/m/Y') : 'N/I' }}
                            </td>
                            <td class="p-4 font-mono text-xs">{{ $usuario->cnh ?? 'N/I' }}</td>
                            <td class="p-4">
                                {{ $usuario->validade_cnh ? \Carbon\Carbon::parse($usuario->validade_cnh)->format('d/m/Y') : 'N/I' }}
                            </td>
                            <td class="p-4 text-gray-500">{{ $usuario->telefone ?? 'N/I' }}</td>
                            <td class="p-4 text-right font-medium text-xs space-x-2">
                                <a href="{{ route('users.show', $usuario->id) }}" class="text-blue-600 hover:underline">Ver</a>
                                <a href="{{ route('users.edit', $usuario->id) }}" class="text-amber-600 hover:underline">Editar</a>
                                <form action="{{ route('users.destroy', $usuario->id) }}" method="POST" onsubmit="return confirm('Excluir este usuário permanentemente?')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">Excluir</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-gray-400 italic">
                                Nenhum usuário encontrado para a pesquisa "{{ $busca }}".
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginação Dinâmica --}}
        @if($usuarios->hasPages())
            <div class="p-4 border-t border-gray-100 bg-gray-50/50">
                {{ $usuarios->links() }}
            </div>
        @endif
    </div>
</div>