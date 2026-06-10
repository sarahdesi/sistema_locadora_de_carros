<?php

namespace App\Http\Controllers;

use App\Models\Contrato;
use App\Models\Veiculo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon; 

class RelatorioController extends Controller
{
    
    private function obterPeriodo(Request $request)
    {
        $dataInicio = $request->input('data_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dataFim = $request->input('data_fim', Carbon::now()->format('Y-m-d'));

        // Converte para o início e fim exato do dia
        $inicio = Carbon::parse($dataInicio)->startOfDay();
        $fim = Carbon::parse($dataFim)->endOfDay();

        return [$inicio, $fim, $dataInicio, $dataFim];
    }

  
    public function index(Request $request) 
    {
        Gate::authorize('is-staff');

      
        [$inicio, $fim, $dataInicio, $dataFim] = $this->obterPeriodo($request);

        
        $faturamentoTotal = Contrato::where('status_contrato', 'encerrado')
            ->whereBetween('updated_at', [$inicio, $fim])
            ->sum('valor_total');
        
        $custoManutencao = DB::table('documento_manutencao')
            ->where('status', 'concluida')
            ->whereBetween('data_saida', [$dataInicio, $dataFim])
            ->sum('custo');
        
        $lucroLiquido = $faturamentoTotal - $custoManutencao;
        

        
        $totalVeiculos = Veiculo::count();
        $veiculosDisponiveis = Veiculo::where('status', 'disponivel')->count();
        $veiculosAlugados = Veiculo::where('status', 'alugado')->count();
        $veiculosOficina = Veiculo::where('status', 'em_manutencao')->count();

        
        $frota = [
            'total'       => $totalVeiculos,
            'disponiveis' => $veiculosDisponiveis,
            'alugados'    => $veiculosAlugados,
            'oficina'     => $veiculosOficina,
        ];


        
        $rankingVeiculos = Contrato::select('veiculo_id', DB::raw('SUM(valor_total) as total_gerado'), DB::raw('COUNT(id) as total_locacoes'))
            ->where('status_contrato', 'encerrado')
            ->whereBetween('updated_at', [$inicio, $fim])
            ->groupBy('veiculo_id')
            ->with('veiculo')
            ->orderBy('total_gerado', 'desc')
            ->take(5)
            ->get();


        
        return view('relatorios.index', compact(
            'faturamentoTotal',
            'custoManutencao',
            'lucroLiquido',
            'totalVeiculos',
            'veiculosDisponiveis',
            'veiculosAlugados',
            'veiculosOficina',
            'frota',
            'rankingVeiculos',
            'dataInicio',
            'dataFim'
        ));
    }

    
    public function faturamento(Request $request)
    {
        Gate::authorize('is-staff');
        [$inicio, $fim, $dataInicio, $dataFim] = $this->obterPeriodo($request);

        $contratosFechados = Contrato::with(['veiculo', 'usuario'])
            ->where('status_contrato', 'encerrado')
            ->whereBetween('updated_at', [$inicio, $fim])
            ->latest()
            ->get();

        return view('relatorios.faturamento', compact('contratosFechados', 'dataInicio', 'dataFim'));
    }

   
    public function frota(Request $request)
    {
        Gate::authorize('is-staff');
        [$inicio, $fim, $dataInicio, $dataFim] = $this->obterPeriodo($request);

        $rankingVeiculos = Contrato::select('veiculo_id', DB::raw('SUM(valor_total) as total_gerado'), DB::raw('COUNT(id) as total_locacoes'))
            ->where('status_contrato', 'encerrado')
            ->whereBetween('updated_at', [$inicio, $fim])
            ->groupBy('veiculo_id')
            ->with('veiculo')
            ->orderBy('total_gerado', 'desc')
            ->get();

        return view('relatorios.frota', compact('rankingVeiculos', 'dataInicio', 'dataFim'));
    }

    
    public function manutencao(Request $request)
    {
        Gate::authorize('is-staff');
        [$inicio, $fim, $dataInicio, $dataFim] = $this->obterPeriodo($request);

        $historicoOficina = DB::table('documento_manutencao')
            ->join('veiculos', 'documento_manutencao.veiculo_id', '=', 'veiculos.id')
            ->select('documento_manutencao.*', 'veiculos.marca', 'veiculos.modelo', 'veiculos.placa')
            ->whereBetween('data_entrada', [$dataInicio, $dataFim])
            ->orderBy('data_entrada', 'desc')
            ->get();

        return view('relatorios.manutencao', compact('historicoOficina', 'dataInicio', 'dataFim'));
    }

    
    public function exportar(Request $request)
    {
        Gate::authorize('is-gerente');
        return response()->json(['success' => 'Recurso integrado! Período processado com sucesso.']);
    }
}