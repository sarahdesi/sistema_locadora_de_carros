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

        $inicio = Carbon::parse($dataInicio)->startOfDay();
        $fim = Carbon::parse($dataFim)->endOfDay();

        return [$inicio, $fim, $dataInicio, $dataFim];
    }

    /**
     * PAINEL EXECUTIVO COMPLETO
     */
    public function index(Request $request)
    {
        Gate::authorize('is-staff');
        [$inicio, $fim, $dataInicio, $dataFim] = $this->obterPeriodo($request);

        // ==========================================
        // FINANÇAS E FATURAMENTO DETALHADO
        // ==========================================
        $faturamentoTotal = Contrato::where('status_contrato', 'encerrado')
            ->whereBetween('updated_at', [$inicio, $fim])
            ->sum('valor_total');
        
        $custoManutencao = DB::table('documento_manutencao')
            ->where('status', 'concluida')
            ->whereBetween('data_saida', [$dataInicio, $dataFim])
            ->sum('custo');
        
        $lucroLiquido = $faturamentoTotal - $custoManutencao;

        // Faturamento diário para o gráfico de linha (Evolução no Período)
        $faturamentoPorPeriodo = Contrato::select(
                DB::raw('DATE(updated_at) as data'), 
                DB::raw('SUM(valor_total) as total')
            )
            ->where('status_contrato', 'encerrado')
            ->whereBetween('updated_at', [$inicio, $fim])
            ->groupBy('data')
            ->orderBy('data', 'asc')
            ->get();

        // Detalhamento de Receitas Adicionais
        $receitasAdicionais = DB::table('valor_extra')
            ->select('tipo_de_cobrança', DB::raw('SUM(valor) as total_faturado'))
            ->whereBetween('created_at', [$inicio, $fim])
            ->groupBy('tipo_de_cobrança')
            ->get();

        // Contas a Receber / Inadimplência
        $contasAReceber = Contrato::whereIn('status_contrato', ['ativo', 'pendente'])
            ->with(['usuario'])
            ->select('id', 'cliente_id', 'valor_total', 'data_hora_retorno', 'status_contrato')
            ->get();

        // ==========================================
        // STATUS E TAXA DE OCUPAÇÃO DA FROTA
        // ==========================================
        $totalVeiculos = Veiculo::count();
        $veiculosDisponiveis = Veiculo::where('status', 'disponivel')->count();
        $veiculosAlugados = Veiculo::where('status', 'alugado')->count();
        $veiculosOficina = Veiculo::where('status', 'em_manutencao')->count();
        $veiculosReservados = Veiculo::where('status', 'reservado')->count();

        $taxaOcupacao = $totalVeiculos > 0 ? round(($veiculosAlugados / $totalVeiculos) * 100, 1) : 0;

        $frota = [
            'total'         => $totalVeiculos,
            'disponiveis'   => $veiculosDisponiveis,
            'alugados'      => $veiculosAlugados,
            'oficina'       => $veiculosOficina,
            'reservados'    => $veiculosReservados,
            'taxa_ocupacao' => $taxaOcupacao
        ];

        // ==========================================
        // CUSTOS ACUMULADOS POR VEÍCULO
        // ==========================================
        $custoPorVeiculo = DB::table('documento_manutencao')
            ->join('veiculos', 'documento_manutencao.veiculo_id', '=', 'veiculos.id')
            ->select('veiculos.placa', 'veiculos.marca', 'veiculos.modelo', DB::raw('SUM(documento_manutencao.custo) as total_gasto'))
            ->where('documento_manutencao.status', 'concluida')
            ->groupBy('veiculos.placa', 'veiculos.marca', 'veiculos.modelo')
            ->orderBy('total_gasto', 'desc')
            ->get();

        // ==========================================
        // QUILOMETRAGEM E RANKING DE DESGASTE
        // ==========================================
        // CORREÇÃO: check_out.km_final (onde o carro sai) e check_out.km_inicial (ajuste conforme suas colunas reais se necessário)
        $rankingRodagem = Contrato::join('check_out', 'contratos.id', '=', 'check_out.contrato_id') 
            ->join('check_in', 'contratos.id', '=', 'check_in.contrato_id')   
            ->select(
                'contratos.veiculo_id', 
                DB::raw('SUM(check_out.km_final - check_out.km_inicial) as total_km_rodados'), 
                DB::raw('COUNT(contratos.id) as total_locacoes')
            )
            ->where('contratos.status_contrato', 'encerrado')
            ->whereBetween('contratos.updated_at', [$inicio, $fim])
            ->groupBy('contratos.veiculo_id')
            ->with('veiculo')
            ->orderBy('total_km_rodados', 'desc')
            ->get(); 

        return view('relatorios.index', compact(
            'faturamentoTotal', 'custoManutencao', 'lucroLiquido', 'frota',
            'faturamentoPorPeriodo', 'receitasAdicionais', 'contasAReceber',
            'custoPorVeiculo', 'rankingRodagem', 'dataInicio', 'dataFim'
        ));
    }
    
   

    public function index(Request $request)
    {
        Gate::authorize('is-staff');
        [$inicio, $fim, $dataInicio, $dataFim] = $this->obterPeriodo($request);

        // ==========================================
        // FINANÇAS E FATURAMENTO DETALHADO
        // ==========================================
        $faturamentoTotal = Contrato::where('status_contrato', 'encerrado')
            ->whereBetween('updated_at', [$inicio, $fim])
            ->sum('valor_total');
        
        $custoManutencao = DB::table('documento_manutencao')
            ->where('status', 'concluida')
            ->whereBetween('data_saida', [$dataInicio, $dataFim])
            ->sum('custo');
        
        $lucroLiquido = $faturamentoTotal - $custoManutencao;

        // Faturamento diário para o gráfico de linha (Evolução no Período)
        $faturamentoPorPeriodo = Contrato::select(
                DB::raw('DATE(updated_at) as data'), 
                DB::raw('SUM(valor_total) as total')
            )
            ->where('status_contrato', 'encerrado')
            ->whereBetween('updated_at', [$inicio, $fim])
            ->groupBy('data')
            ->orderBy('data', 'asc')
            ->get();

        // Detalhamento de Receitas Adicionais (Valores Extras cobrados)
        // Mude os nomes das colunas de acordo com o que definiu na sua tabela 'valor_extra' ou 'contratos'
        $receitasAdicionais = DB::table('valor_extra')
            ->select('tipo_de_cobrança', DB::raw('SUM(valor) as total_faturado'))
            ->whereBetween('created_at', [$inicio, $fim])
            ->groupBy('tipo_de_cobrança')
            ->get();

        // Contas a Receber / Inadimplência (Contratos em aberto ou com atraso de pagamento)
        $contasAReceber = Contrato::whereIn('status_contrato', ['ativo', 'pendente'])
            ->with(['usuario'])
            ->select('id', 'cliente_id', 'valor_total', 'data_hora_retorno')
            ->get();


        // ==========================================
        // STATUS E TAXA DE OCUPAÇÃO DA FROTA
        // ==========================================
        $totalVeiculos = Veiculo::count();
        $veiculosDisponiveis = Veiculo::where('status', 'disponivel')->count();
        $veiculosAlugados = Veiculo::where('status', 'alugado')->count();
        $veiculosOficina = Veiculo::where('status', 'em_manutencao')->count();
        $veiculosReservados = Veiculo::where('status', 'reservado')->count();

        // Cálculo da Taxa de Ocupação da Frota em Percentual
        $taxaOcupacao = $totalVeiculos > 0 ? round(($veiculosAlugados / $totalVeiculos) * 100, 1) : 0;

        $frota = [
            'total'       => $totalVeiculos,
            'disponiveis' => $veiculosDisponiveis,
            'alugados'    => $veiculosAlugados,
            'oficina'     => $veiculosOficina,
            'reservados'  => $veiculosReservados,
            'taxa_ocupacao' => $taxaOcupacao
        ];


        // ==========================================
        // CUSTOS ACUMULADOS POR VEÍCULO
        // ==========================================
        $custoPorVeiculo = DB::table('documento_manutencao')
            ->join('veiculos', 'documento_manutencao.veiculo_id', '=', 'veiculos.id')
            ->select('veiculos.placa', 'veiculos.marca', 'veiculos.modelo', DB::raw('SUM(documento_manutencao.custo) as total_gasto'))
            ->where('documento_manutencao.status', 'concluida')
            ->groupBy('veiculos.placa', 'veiculos.marca', 'veiculos.modelo')
            ->orderBy('total_gasto', 'desc')
            ->get();


        // ==========================================
        // QUILOMETRAGEM E RANKING DE DESGASTE
        // ==========================================
        // Calcula a rodagem extraindo a diferença entre o KM Inicial e o KM Final dos contratos encerrados
        $rankingRodagem = $veiculosMaisRodados = Contrato::join('check_out', 'contratos.id', '=', 'check_out.contrato_id') 
        ->join('check_in', 'contratos.id', '=', 'check_in.contrato_id')   
        ->select(
            'contratos.veiculo_id', 
            DB::raw('SUM(check_in.km_inicial - check_out.km_final) as total_km_rodados'), 
            DB::raw('COUNT(contratos.id) as total_locacoes')
        )
        ->where('contratos.status_contrato', 'encerrado')
        ->whereBetween('contratos.updated_at', [$inicio, $fim])
        ->groupBy('contratos.veiculo_id')
        ->with('veiculo')
        ->orderBy('total_km_rodados', 'desc')
        ->get(); 

        return view('relatorios.index', compact(
            'faturamentoTotal', 'custoManutencao', 'lucroLiquido', 'frota',
            'faturamentoPorPeriodo', 'receitasAdicionais', 'contasAReceber',
            'custoPorVeiculo', 'rankingRodagem', 'dataInicio', 'dataFim'
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