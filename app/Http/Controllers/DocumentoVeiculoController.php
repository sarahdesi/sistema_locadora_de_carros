<?php

namespace App\Http\Controllers;

use App\Models\DocumentoVeiculo;
use App\Models\Veiculo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class DocumentoVeiculoController extends Controller
{
    /**
     * Lista todos os documentos cadastrados no sistema
     */
    public function index()
    {
        Gate::authorize('is-staff');

        // Carrega os documentos trazendo os dados do veículo associado pela placa
        $documentos = DocumentoVeiculo::with('veiculo')->latest()->paginate(10);

        return view('documentos.index', compact('documentos'));
    }

    /**
     * Exibe o formulário para cadastrar um novo documento
     */
    public function create()
    {
        Gate::authorize('is-staff');

        // Busca todos os veículos para o operador escolher a qual placa o documento pertence
        $veiculos = Veiculo::orderBy('marca')->get();

        return view('documentos.create', compact('veiculos'));
    }

    /**
     * Salva o documento no banco de dados
     */
    public function store(Request $request)
    {
        Gate::authorize('is-staff');

        $validated = $request->validate([
            'veiculo_placa'   => 'required|exists:veiculos,placa',
            'tipo'            => 'required|string',
            'data_vencimento' => 'required|date',
            'valor'           => 'nullable|numeric|min:0',
        ]);

        DocumentoVeiculo::create($validated);

        return redirect()->route('documentos.index')
                         ->with('success', 'Documento cadastrado com sucesso e associado à placa!');
    }

    /**
     * Exibe o formulário de edição do documento
     */
    public function edit($id)
    {
        Gate::authorize('is-staff');

        $documento = DocumentoVeiculo::findOrFail($id);
        $veiculos = Veiculo::orderBy('marca')->get();

        return view('documentos.edit', compact('documento', 'veiculos'));
    }

    /**
     * Atualiza os dados do documento (útil para quando o imposto for renovado)
     */
    public function update(Request $request, $id)
    {
        Gate::authorize('is-staff');

        $documento = DocumentoVeiculo::findOrFail($id);

        $validated = $request->validate([
            'veiculo_placa'   => 'required|exists:veiculos,placa',
            'tipo'            => 'required|string',
            'data_vencimento' => 'required|date',
            'valor'           => 'nullable|numeric|min:0',
        ]);

        $documento->update($validated);

        return redirect()->route('documentos.index')
                         ->with('success', 'Documento atualizado com sucesso!');
    }

    /**
     * Remove um documento do sistema
     */
    public function destroy($id)
    {
        Gate::authorize('is-staff');

        $documento = DocumentoVeiculo::findOrFail($id);
        $documento->delete();

        return redirect()->route('documentos.index')
                         ->with('success', 'Documento removido do histórico.');
    }
}