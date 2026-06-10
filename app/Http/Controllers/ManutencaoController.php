<?php

namespace App\Http\Controllers;

use App\Models\Manutencao;
use App\Models\Veiculo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ManutencaoController extends Controller
{
    /**
     * Lista todas as manutenções cadastradas
     */
    public function index()
    {
        Gate::authorize('is-staff');

        // Carrega as manutenções trazendo junto os dados do veículo (Eager Loading)
        $manutencoes = Manutencao::with('veiculo')->latest()->paginate(10);

        return view('manutencao.index', compact('manutencoes'));
    }

    /**
     * Exibe o formulário de entrada na oficina
     */
    public function create()
    {
        Gate::authorize('is-staff');

        // Só lista veículos que NÃO estão em manutenção no momento
        $veiculos = Veiculo::where('status', '!=', 'em_manutencao')->get();

        return view('manutencao.create', compact('veiculos'));
    }

    /**
     * Registra a entrada do carro na oficina e bloqueia o veículo
     */
    public function store(Request $request)
    {
        Gate::authorize('is-staff');

        $validated = $request->validate([
            'veiculo_id'      => 'required|exists:veiculos,id',
            'tipo_manutencao' => 'required|string',
            'descricao'       => 'required|string',
            'data_entrada'    => 'required|date',
            'custo'           => 'required|numeric|min:0',
        ]);

        $validated['status'] = 'em_andamento';

        // 1. Salva o registro da manutenção
        $manutencao = Manutencao::create($validated);

        // 2. REGRA DE OURO: Atualiza o status do veículo para 'em_manutencao'
        $veiculo = Veiculo::findOrFail($request->veiculo_id);
        $veiculo->update(['status' => 'em_manutencao']);

        return redirect()->route('manutencao.index')
                         ->with('success', 'Manutenção registrada! O veículo foi bloqueado para locações.');
    }

    /**
     * Exibe o formulário para editar ou encerrar a manutenção
     */
    public function edit($id)
    {
        Gate::authorize('is-staff');

        $manutencao = Manutencao::findOrFail($id);

        return view('manutencao.edit', compact('manutencao'));
    }

    /**
     * Atualiza a manutenção e libera o carro se concluída/cancelada
     */
    public function update(Request $request, $id)
    {
        Gate::authorize('is-staff');

        $manutencao = Manutencao::findOrFail($id);

        $validated = $request->validate([
            'tipo_manutencao' => 'required|string',
            'descricao'       => 'required|string',
            'data_entrada'    => 'required|date',
            'data_saida'      => 'nullable|date|after_or_equal:data_entrada',
            'custo'           => 'required|numeric|min:0',
            'status'          => 'required|in:em_andamento,concluida,cancelada',
        ]);

        // Se o operador mudar o status para concluída ou cancelada, libertamos o carro
        if (in_array($request->status, ['concluida', 'cancelada'])) {
            
            // Se foi concluída e não preencheram a data de saída, coloca a data de hoje
            if ($request->status === 'concluida' && empty($validated['data_saida'])) {
                $validated['data_saida'] = now()->format('Y-m-d');
            }

            // Libera o veículo de volta para a frota
            if ($manutencao->veiculo) {
                $manutencao->veiculo->update(['status' => 'disponivel']);
            }
        } else {
            // Se continuar em andamento, garante que o carro permanece bloqueado
            if ($manutencao->veiculo) {
                $manutencao->veiculo->update(['status' => 'em_manutencao']);
            }
        }

        $manutencao->update($validated);

        return redirect()->route('manutencao.index')
                         ->with('success', 'Histórico de manutenção atualizado com sucesso!');
    }
}