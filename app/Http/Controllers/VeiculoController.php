<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Veiculo;
use App\Models\LogAtividade; // Importação que estava faltando
use Illuminate\Support\Facades\Gate; // Importação do Gate

class VeiculoController extends Controller
{
    /**
     * Lista todos os veículos
     * GET /veiculos
     */
    public function index()
    {
        $veiculos = Veiculo::with('manutencoes')
                            ->orderBy('status')
                            ->get();

        return view('veiculos.index', compact('veiculos'));
    }

    /**
     * Exibe o formulário de cadastro de novo veículo
     * GET /veiculos/create
     */
    public function create()
    {
        Gate::authorize('is-staff');

        return view('veiculos.create');
    }

    /**
     * Salva o veículo novo no banco
     * POST /veiculos
     */
    public function store(Request $request)
    {
        Gate::authorize('is-staff');

        $dados = $request->validate([
            'placa'        => 'required|string|max:7|unique:veiculos',
            'modelo'       => 'required|string|max:100',
            'marca'        => 'required|string|max:100',
            'renavam'      => 'required|string|size:11|unique:veiculos',
            'cor'          => 'required|string|max:50',
            'ano'          => 'required|integer|min:1990|max:2026',
            'combustivel'  => 'required|in:flex,gasolina,diesel,eletrico', 
            'odometro'     => 'required|numeric|min:0',
        ]);

        Veiculo::create($dados);

        // Registra no log de atividades
        LogAtividade::create([
            'usuario_id'  => auth()->id(),
            'acao'        => 'Cadastro de veículo',
            'descricao'  => 'Cadastrou o veículo de placa ' . $dados['placa'],
        ]);

        return redirect()->route('veiculos.index')
                         ->with('sucesso', 'Veículo cadastrado com sucesso!');
    }

    /**
     * Exibe um veículo específico
     * GET /veiculos/{veiculo}
     */
    public function show(Veiculo $veiculo)
    {
        $veiculo->load('manutencoes', 'documentos', 'contratos');
        
        // Corrigido de 'veiculos' para 'veiculo' no compact (singular)
        return view('veiculos.show', compact('veiculo')); 
    }

    /**
     * Exibe o formulário de edição
     * GET /veiculos/{veiculo}/edit
     */
    public function edit(Veiculo $veiculo)
    {
        Gate::authorize('is-staff');

        // Corrigido de 'veiculos' para 'veiculo' no compact (singular)
        return view('veiculos.edit', compact('veiculo'));
    }

    /**
     * Salva as alterações do veículo
     * PUT /veiculos/{veiculo}
     */
    public function update(Request $request, Veiculo $veiculo)
    {
        Gate::authorize('is-staff');

        $dados = $request->validate([
            'modelo'      => 'required|string|max:100',
            'marca'       => 'required|string|max:100',
            'cor'         => 'required|string|max:50',
            'ano'         => 'required|integer|min:1990|max:2026',
            'combustivel' => 'required|in:flex,gasolina,diesel,eletrico',
            'status'      => 'required|in:disponivel,locado,manutencao,reservado',
            'odometro'    => 'required|numeric|min:0',
        ]);

        $veiculo->update($dados);

        // Registra no log de atividades
        LogAtividade::create([
            'usuario_id' => auth()->id(),
            'acao'       => 'Atualização de veículo',
            'descricao'  => 'Atualizou o veículo de placa ' . $veiculo->placa,
        ]);

        return redirect()->route('veiculos.index')
                         ->with('sucesso', 'Veículo atualizado com sucesso!');
    }

    /**
     * Deleta o veículo
     * DELETE /veiculos/{veiculo}
     */
    public function destroy(Veiculo $veiculo)
    {
        Gate::authorize('is-staff');

        if ($veiculo->contratos()->exists()) {
        return redirect()->back()->with('error', 'Não é possível excluir este veículo porque ele possui contratos vinculados no sistema.');
    }

        $placa = $veiculo->placa;
        $veiculo->delete();

        // Registra no log de atividades
        LogAtividade::create([
            'usuario_id' => auth()->id(),
            'acao'       => 'Remoção de veículo',
            'descricao'  => 'Removeu o veículo de placa ' . $placa,
        ]);

        return redirect()->route('veiculos.index')
                         ->with('sucesso', 'Veículo removido com sucesso!');
    }
}