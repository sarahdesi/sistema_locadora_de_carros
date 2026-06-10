<?php

namespace App\Http\Controllers;

use App\Models\Contrato;
use App\Models\CheckIn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CheckInController extends Controller
{
    /**
     * Exibe o formulário de Check-In
     */
    public function create($id) // Alterado para receber o $id direto da URL
    {
        Gate::authorize('is-staff');

        // Procura o contrato manualmente no banco de dados pelo ID
        $contrato = Contrato::findOrFail($id);

        // Só permite o check-in se o contrato estiver realmente 'aberto'
        if ($contrato->status_contrato !== 'aberto') {
            return redirect()->route('contratos.index')
                             ->with('error', 'Este contrato não está aguardando liberação de veículo.');
        }

        return view('check_ins.create', compact('contrato'));
    }

    /**
     * Salva a vistoria de saída e ativa a locação
     */
    public function store(Request $request, $id) // Alterado para receber o $id aqui também
    {
        Gate::authorize('is-staff');

        // Garante que o contrato existe antes de salvar a vistoria
        $contrato = Contrato::findOrFail($id);

        $validated = $request->validate([
            'km_inicial'        => 'required|numeric|min:0',
            'nivel_combustivel' => 'required|string',
            'avarias'           => 'nullable|string',
            'conferencia_obj'   => 'nullable|string',
        ]);

        // Vincula os dados automáticos conforme a sua estrutura e Model
        $validated['contrato_id']     = $contrato->id; 
        $validated['data_hora_saida']  = now();
        $validated['previsao_retorno'] = $contrato->data_hora_retorno;
        $validated['status']           = 'ativo';

        // 1. Cria o Check-In passando o contrato_id
        $checkIn = CheckIn::create($validated);

        // 2. Atualiza o status do contrato para 'em_andamento'
        $contrato->update([
            'check_in_id'     => $checkIn->id,
            'status_contrato' => 'em_andamento',
        ]);

        return redirect()->route('contratos.index')
                         ->with('success', 'Check-In realizado com sucesso! Veículo liberado.');
    }
}