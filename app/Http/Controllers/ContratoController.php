<?php

namespace App\Http\Controllers;

use App\Models\Contrato;
use App\Models\Veiculo;
use App\Models\Usuario; // Importante para listar os clientes no formulário
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ContratoController extends Controller
{
    /**
     * Exibe os contratos (Filtra se for cliente, mostra todos se for staff)
     */
    public function index()
    {
        $user = auth()->user();

        if (Gate::allows('is-staff')) {
           
            $contratos = Contrato::with(['cliente', 'veiculo', 'servidor'])->latest()->get();
        } else {
            // Se for Cliente, só vê os contratos vinculados ao ID dele
            $contratos = Contrato::where('cliente_id', $user->id)->with(['veiculo', 'servidor'])->latest()->get();
        }

        return view('contratos.index', compact('contratos'));
    }

    /**
     * Formulário de nova locação
     */
public function create()
{
    
    $placasComDocumentoVencido = \App\Models\DocumentoVeiculo::where('data_vencimento', '<', now()->format('Y-m-d'))
        ->pluck('veiculo_placa')
        ->toArray();

    $veiculosDisponiveis = \App\Models\Veiculo::where('status', 'disponivel')
        ->whereNotIn('placa', $placasComDocumentoVencido)
        ->get();

    
    $clientes = collect(); 
    
    if (Gate::allows('is-staff')) {
        $clientes = \App\Models\Usuario::whereHas('role', function($query) {
            $query->where('name', 'cliente'); 
        })->get();
    }

    return view('contratos.create', compact('veiculosDisponiveis', 'clientes'));
}

    /**
     * Salva o contrato / Solicita locação
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'veiculo_id'        => 'required|exists:veiculos,id',
            'data_hora_retorno' => 'required|date|after:now',
            'valor_diaria'      => 'required|numeric|min:0',
        ]);

        // Força o cliente_id a ser o ID do usuário logado se ele for um cliente
        if (!Gate::allows('is-staff')) {
            $validated['cliente_id'] = $user->id;
            $validated['servidor_id'] = null; // Cliente solicitou sozinho pelo site/app
        } else {
            // Se for staff criando para um cliente, valida o cliente selecionado e grava quem atendeu (servidor)
            $request->validate(['cliente_id' => 'required|exists:usuarios,id']);
            $validated['cliente_id'] = $request->cliente_id;
            $validated['servidor_id'] = $user->id; 
        }
        

        $veiculo = \App\Models\Veiculo::findOrFail($request->veiculo_id);

        // 2. Verifica se este carro específico tem algum imposto/licenciamento vencido
        $temPendeciaDocumento = \App\Models\DocumentoVeiculo::where('veiculo_placa', $veiculo->placa)
            ->where('data_vencimento', '<', now()->format('Y-m-d'))
            ->exists();

        // 3. Se encontrar alguma irregularidade, barra o contrato na hora!
        if ($temPendeciaDocumento) {
            return redirect()->back()
                             ->withInput() // Mantém o que o operador já digitou na tela
                             ->with('error', 'Bloqueio de Segurança: Este veículo possui pendências na tabela de documentos e não pode ser alugado até ser regularizado!');
        }


        $validated['status_contrato'] = 'aberto';

        // 1. Salva o contrato no banco
        $contrato = Contrato::create($validated);

        // 2. REGRA DE NEGÓCIO: Atualiza o status do veículo para 'locado'
        $veiculo = Veiculo::find($request->veiculo_id);
        $veiculo->update(['status' => 'reservado']);

        return redirect()
        ->route('contratos.show', $contrato)
        ->with('sucesso', 'Contrato aberto com sucesso!');
    }

    /**
     * Exibe detalhes de um contrato
     */
    public function show(Contrato $contrato)
    {
        $user = auth()->user();

        if (!Gate::allows('is-staff') && $contrato->cliente_id !== $user->id) {
            abort(403, 'Você não tem permissão para visualizar este contrato.');
        }

        // Carrega as relações para exibir na tela de detalhes
        $contrato->load(['cliente', 'veiculo', 'servidor']);

        return view('contratos.show', compact('contrato'));
    }

    /**
     * Formulário de edição de contrato (Apenas Gerente e Operador)
     */
    public function edit(Contrato $contrato)
    {
        Gate::authorize('is-staff');

        return view('contratos.edit', compact('contrato'));
    }

    /**
     * Atualiza o contrato (Apenas Gerente e Operador)
     */
    /**
     * Atualiza o contrato (Apenas Gerente e Operador)
     */
    public function update(Request $request, Contrato $contrato)
    {
        Gate::authorize('is-staff');

        // 1. Valida os dados vindo do formulário de edição
        $dados = $request->validate([
            'status_contrato'   => 'required|string|in:aberto,em_andamento,encerrado,cancelado',
            'data_hora_retorno' => 'required|date',
            'valor_diaria'      => 'required|numeric|min:0',
            'valor_total'       => 'nullable|numeric|min:0',
        ]);

        //  Se o contrato for Finalizado ou Cancelado, 
        // o carro correspondente precisa voltar a ficar 'disponivel' automaticamente!
        if (in_array($dados['status_contrato'], ['encerrado', 'cancelado'])) {
            if ($contrato->veiculo) {
                $contrato->veiculo->update(['status' => 'disponivel']);
            }
        } 
        
        elseif (in_array($dados['status_contrato'], ['aberto', 'em_andamento'])) {
            if ($contrato->veiculo) {
                $contrato->veiculo->update(['status' => 'locado']);
            }
        }

        // 3. Salva as alterações de verdade no banco de dados
        $contrato->update($dados);

        return redirect()->route('contratos.index')
                         ->with('success', 'Contrato atualizado com sucesso no banco de dados!');
    }

    

}