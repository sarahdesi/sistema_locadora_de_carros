<?php

namespace App\Http\Controllers;

use App\Models\Contrato;
use App\Models\CheckOut;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CheckOutController extends Controller
{
    /**
     * Exibe o formulário de Check-Out
     */
    public function create($id)
    {
        Gate::authorize('is-staff');

        $contrato = Contrato::findOrFail($id);

        // Só faz sentido fazer check-out se o carro estiver na rua ('em_andamento')
        if ($contrato->status_contrato !== 'em_andamento') {
            return redirect()->route('contratos.index')
                             ->with('error', 'Este contrato não está em andamento para receber devolução.');
        }

        return view('check_outs.create', compact('contrato'));
    }

    /**
     * Salva a devolução, encerra o contrato e libera o veículo
     */
    /**
     * Salva a devolução, calcula o valor total, encerra o contrato e libera o veículo
     */
    public function store(Request $request, $id)
    {
        Gate::authorize('is-staff');

        $contrato = Contrato::findOrFail($id);

        // Validação dos dados de retorno
        $validated = $request->validate([
            'km_final'                  => 'required|numeric|min:' . ($contrato->checkIn->km_inicial ?? 0),
            'nivel_combustivel_retorno' => 'required|string',
            'avaliacao_limpeza'         => 'required|string',
            'conferencia_obj_retorno'   => 'nullable|string',
            'avarias_retorno'           => 'nullable|string',
            'custo_adicional'           => 'required|numeric|min:0',
            'observacoes'               => 'nullable|string',
        ]);

        $validated['contrato_id'] = $contrato->id;
        $validated['data_hora_devolucao'] = now();

        // 1. Cria o registro de Check-Out no banco
        $checkOut = CheckOut::create($validated);

        // ========================================================
        // CÁLCULO AUTOMÁTICO DO VALOR TOTAL DA LOCAÇÃO
        // ========================================================
        $dataSaida = \Carbon\Carbon::parse($contrato->checkIn->data_hora_saida);
        $dataDevolucao = \Carbon\Carbon::parse($checkOut->data_hora_devolucao);

        // Calcula a diferença de dias entre a saída e a devolução
        $dias = $dataSaida->diffInDays($dataDevolucao);

        // Regra de negócio: se o cliente devolver no mesmo dia, cobra-se no mínimo 1 diária
        if ($dias == 0) {
            $dias = 1;
        }

        // Valor Total = (Quantidade de Dias * Valor da Diária) + Custo Adicional da Devolução
        $valorTotalFinal = ($dias * $contrato->valor_diaria) + $request->custo_adicional;
        // ========================================================

        // 2. Atualiza o Contrato para 'encerrado' e grava o valor total real
        $contrato->update([
            'check_out_id'    => $checkOut->id,
            'status_contrato' => 'encerrado',
            'valor_total'     => $valorTotalFinal, 
        ]);

        // 3. Libera o veículo para a frota
        if ($contrato->veiculo) {
         $contrato->veiculo->update([
        'status'   => 'disponivel',
        'odometro' => $request->km_final 
    ]);
}

        return redirect()->route('contratos.index')
                         ->with('success', 'Check-Out realizado com sucesso! Contrato finalizado.');
    }
}