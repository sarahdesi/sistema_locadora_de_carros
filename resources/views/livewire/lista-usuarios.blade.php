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
    <div class="mb-6 flex items-center justify-between gap-4">
    <div class="relative w-full max-w-md">
        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
        </span>
            <input type="text" 
                   wire:model.live.debounce.300ms="busca" 
                   placeholder="Buscar por nome, CPF ou login..." 
                   class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 bg-white text-sm shadow-sm transition">
                </div>

        <a href="{{ route('users.create') }}" 
          class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-xl shadow-sm transition inline-flex items-center gap-2 justify-center whitespace-nowrap shrink-0">
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
                            <td class="p-4 text-right">
                            <div class="flex justify-end gap-2">
                                {{-- Botão Ver (Ícone de Olho) --}}
                                <a href="{{ route('users.show', $usuario->id) }}" 
                                class="p-1.5 bg-gray-50 text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition" 
                                title="Ver Detalhes">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </a>

                                {{-- Botão Editar (Ícone de Lápis) --}}
                                <a href="{{ route('users.edit', $usuario->id) }}" 
                                class="p-1.5 bg-gray-50 text-gray-600 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition" 
                                title="Editar">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </a>

                                {{-- Botão Excluir (Ícone de Lixeira) --}}
                                <form action="{{ route('users.destroy', $usuario->id) }}" method="POST" onsubmit="return confirm('Excluir este usuário permanentemente?')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="p-1.5 bg-gray-50 text-gray-600 hover:text-red-600 hover:bg-red-50 rounded-lg transition" 
                                            title="Excluir">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </form>
                            </div>
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