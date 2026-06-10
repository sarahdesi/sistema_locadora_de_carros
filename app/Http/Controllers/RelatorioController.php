<?php

namespace App\Http\Controllers;

use App\Models\Contrato;
use App\Models\Veiculo;
use App\Models\Manutencao; // Altere para seu Model real se necessário
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;

class RelatorioController extends Controller
{
    public function index()
    {
        Gate::authorize('is-staff');

        // 💰 1. DESEMPENHO FINANCEIRO
        // Soma o faturamento real dos contratos encerrados
        $faturamentoTotal = Contrato::where('status_contrato', 'encerrado')->sum('valor_total');
        
        // Soma os custos das manutenções concluídas (lembrando que sua tabela é documento_manutencao)
        $custoManutencao = DB::table('documento_manutencao')->where('status', 'concluida')->sum('custo');
        
        // Lucro Líquido do período
        $lucroLiquido = $faturamentoTotal - $custoManutencao;


        // 🚗 2.OCUPAÇÃO E STATUS DA FROTA (Gráfico ou Cards)
        $totalVeiculos = Veiculo::count();
        $veiculosDisponiveis = Veiculo::where('status', 'disponivel')->count();
        $veiculosAlugados = Veiculo::where('status', 'alugado')->count();
        $veiculosOficina = Veiculo::where('status', 'em_manutencao')->count();


        // 📈 3. RANKING DE CARROS QUE MAIS FATURARAM
        // Cruza os contratos com os veículos para ver quais dão mais retorno
        $rankingVeiculos = Contrato::select('veiculo_id', DB::raw('SUM(valor_total) as total_gerado'), DB::raw('COUNT(id) as total_locacoes'))
            ->where('status_contrato', 'encerrado')
            ->groupBy('veiculo_id')
            ->with('veiculo')
            ->orderBy('total_gerado', 'desc')
            ->take(5) // Pega o Top 5
            ->get();


        return view('relatorios.index', compact(
            'faturamentoTotal',
            'custoManutencao',
            'lucroLiquido',
            'totalVeiculos',
            'veiculosDisponiveis',
            'veiculosAlugados',
            'veiculosOficina',
            'rankingVeiculos'
        ));
    }
}