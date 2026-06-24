<?php

namespace App\Http\Controllers;

use App\Models\Contrato;
use App\Models\Veiculo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon; 
// use Barryvdh\DomPDF\Facade\Pdf; // Se utilizar laravel-dompdf

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

    // 1. ABA: VISÃO GERAL
    public function index(Request $request)
    {
        Gate::authorize('is-staff');
        [$inicio, $fim, $dataInicio, $dataFim] = $this->obterPeriodo($request);

        $faturamentoTotal = Contrato::where('status_contrato', 'encerrado')->whereBetween('updated_at', [$inicio, $fim])->sum('valor_total');
        $custoManutencao = DB::table('documento_manutencao')->where('status', 'concluida')->whereBetween('data_saida', [$dataInicio, $dataFim])->sum('custo');
        $lucroLiquido = $faturamentoTotal - $custoManutencao;

        $totalVeiculos = Veiculo::count();
        $frota = [
            'total' => $totalVeiculos,
            'disponiveis' => Veiculo::where('status', 'disponivel')->count(),
            'alugados' => Veiculo::where('status', 'alugado')->count(),
            'oficina' => Veiculo::where('status', 'em_manutencao')->count(),
            'reservados' => Veiculo::where('status', 'reservado')->count(),
            'taxa_ocupacao' => $totalVeiculos > 0 ? round((Veiculo::where('status', 'alugado')->count() / $totalVeiculos) * 100, 1) : 0
        ];

        return view('relatorios.index', compact('faturamentoTotal', 'custoManutencao', 'lucroLiquido', 'frota', 'dataInicio', 'dataFim'));
    }

    // 2. ABA: FINANCEIRO
    // 2. ABA: FINANCEIRO
    public function faturamento(Request $request)
    {
        Gate::authorize('is-staff');
        [$inicio, $fim, $dataInicio, $dataFim] = $this->obterPeriodo($request);

        // 1. Busca os contratos encerrados (O que estava faltando!)
        $contratosFechados = Contrato::with(['veiculo', 'cliente']) //em vez de usuarios, vou tentar usar clientes
            ->where('status_contrato', 'encerrado')
            ->whereBetween('updated_at', [$inicio, $fim])
            ->latest()
            ->get();

        // 2. Detalhamento de Receitas Adicionais
        $receitasAdicionais = DB::table('valor_extra')
            ->select('tipo_de_cobrança', DB::raw('SUM(valor) as total_faturado'))
            ->whereBetween('created_at', [$inicio, $fim])
            ->groupBy('tipo_de_cobrança')
            ->get();

        // 3. Contas a Receber / Inadimplência
        $contasAReceber = Contrato::whereIn('status_contrato', ['ativo', 'pendente'])
            ->with(['cliente']) //aqui vou trocar usuario por cliente
            ->select('id', 'cliente_id', 'valor_total', 'data_hora_retorno', 'status_contrato')
            ->get();

        // Retorna a view enviando TODAS as variáveis necessárias
        return view('relatorios.faturamento', compact(
            'contratosFechados', 
            'receitasAdicionais', 
            'contasAReceber', 
            'dataInicio', 
            'dataFim'
        ));
    }

    // 3. ABA: GESTÃO DE FROTA
    public function frota(Request $request)
    {
        Gate::authorize('is-staff');
        [$inicio, $fim, $dataInicio, $dataFim] = $this->obterPeriodo($request);

        $rankingVeiculos = Contrato::select(
                'veiculo_id', 
                DB::raw('SUM(valor_total) as total_gerado'), 
                DB::raw('COUNT(id) as total_locacoes')
            )
            ->where('status_contrato', 'encerrado')
            ->whereBetween('updated_at', [$inicio, $fim])
            ->groupBy('veiculo_id')
            ->with('veiculo')
            ->orderBy('total_gerado', 'desc')
            ->get();

        $rankingRodagem = Contrato::join('check_out', 'contratos.id', '=', 'check_out.contrato_id') 
            ->join('check_in', 'contratos.id', '=', 'check_in.contrato_id')   
            ->select(
                'contratos.veiculo_id', 
                DB::raw('SUM(check_out.km_final - check_in.km_inicial) as total_km_rodados'), 
                DB::raw('COUNT(contratos.id) as total_locacoes')
            )
            ->where('contratos.status_contrato', 'encerrado')
            ->whereBetween('contratos.updated_at', [$inicio, $fim])
            ->groupBy('contratos.veiculo_id')
            ->with('veiculo')
            ->orderBy('total_km_rodados', 'desc')
            ->get();

        
        return view('relatorios.frota', compact('rankingVeiculos', 'rankingRodagem', 'dataInicio', 'dataFim'));
    }

    // 4. ABA: HISTÓRICO DE MANUTENÇÃO
   // 4. ABA: HISTÓRICO DE MANUTENÇÃO
    public function manutencao(Request $request)
    {
        Gate::authorize('is-staff');
        [$inicio, $fim, $dataInicio, $dataFim] = $this->obterPeriodo($request);

        // 1. Ranking acumulado: Soma de custos agrupado por veículo
        $custoPorVeiculo = DB::table('documento_manutencao')
            ->join('veiculos', 'documento_manutencao.veiculo_id', '=', 'veiculos.id')
            ->select('veiculos.placa', 'veiculos.marca', 'veiculos.modelo', DB::raw('SUM(documento_manutencao.custo) as total_gasto'))
            ->where('documento_manutencao.status', 'concluida')
            ->groupBy('veiculos.placa', 'veiculos.marca', 'veiculos.modelo')
            ->orderBy('total_gasto', 'desc')
            ->get();

        // 2. Histórico detalhado: Lista cronológica de registros (O que estava faltando!)
        $historicoOficina = DB::table('documento_manutencao')
            ->join('veiculos', 'documento_manutencao.veiculo_id', '=', 'veiculos.id')
            ->select('documento_manutencao.*', 'veiculos.marca', 'veiculos.modelo', 'veiculos.placa')
            ->whereBetween('documento_manutencao.data_entrada', [$dataInicio, $dataFim]) // Filtra pelo período selecionado
            ->orderBy('documento_manutencao.data_entrada', 'desc')
            ->get();

        // Retorna a view enviando o custo resumido E o histórico detalhado
        return view('relatorios.manutencao', compact('custoPorVeiculo', 'historicoOficina', 'dataInicio', 'dataFim'));
    }

    // ENGINE DE EXPORTAÇÃO DINÂMICA VIA CONTEXTO
    public function exportar(Request $request)
    {
        Gate::authorize('is-gerente');
        $contexto = $request->input('contexto', 'visao_geral');
        [$inicio, $fim, $dataInicio, $dataFim] = $this->obterPeriodo($request);

        $dados = ['dataInicio' => $dataInicio, 'dataFim' => $dataFim];

        // Carrega somente os dados específicos da aba solicitada para renderizar o PDF rápido
        if ($contexto === 'financeiro') {
            $dados['receitasAdicionais'] = DB::table('valor_extra')->select('tipo_de_cobrança', DB::raw('SUM(valor) as total_faturado'))->whereBetween('created_at', [$inicio, $fim])->groupBy('tipo_de_cobrança')->get();
            $dados['contasAReceber'] = Contrato::whereIn('status_contrato', ['ativo', 'pendente'])->get();
        } elseif ($contexto === 'frota') {
            // Queries do ranking de rodagem...
        }

        // Exemplo de retorno usando uma biblioteca como DomPDF/Snappy:
        // $pdf = Pdf::loadView('relatorios.pdf.' . $contexto, $dados);
        // return $pdf->download('relatorio_' . $contexto . '_' . $dataInicio . '.pdf');

        return response()->json(['success' => "PDF do contexto [{$contexto}] gerado com sucesso!"]);
    }
}