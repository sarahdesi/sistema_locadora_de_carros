<?php

namespace App\Http\Controllers;

use App\Models\Contrato;
use App\Models\Veiculo;
use App\Models\Usuario; 
use App\Models\DocumentoVeiculo; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Carbon\Carbon;

class AlarmeController extends Controller
{
    /**
     * Exibe o Centro de Alarmes e Notificações unificado
     */
    public function index()
    {
        Gate::authorize('is-staff');

        $hoje = Carbon::today();
        $alertaPrazoDias = Carbon::today()->addDays(10); 

        // ---------------------------------------------------------------------
        // REQUISITO 1: Alertas de Manutenção (Baseado em KM)
        // ---------------------------------------------------------------------
        // Busca veículos onde a quilometragem atual atingiu ou passou da revisão prevista
        // Nota: Ajuste os nomes das colunas de acordo com a sua tabela de 'veiculos'
        $alertasManutencao = Veiculo::withCount(['manutencoes' => function ($query) {
            $query->where('tipo_manutencao', 'Preventiva')
                  ->where('status', 'concluida');
        }])->get()->filter(function ($veiculo) {
            
            // Se o carro já está fisicamente na oficina, mantém o alerta ativo
            if ($veiculo->status === 'em_manutencao') {
                return true;
            }

            //  Lógica dos 10.000 km fixos: (Revisões feitas + 1) * 10000
            $kmLimiteProximaRevisao = ($veiculo->manutencoes_count + 1) * 10000;

            // Se o odômetro atual passou ou igualou o limite do ciclo, dispara o alarme!
            return $veiculo->odometro >= $kmLimiteProximaRevisao;
        });

        // ---------------------------------------------------------------------
        // REQUISITO 2: Documentação Vencida (IPVA, Licenciamento, Seguro)
        // ---------------------------------------------------------------------
        // Busca documentos vencidos ou que vencem nos próximos 30 dias
        // Nota: Ajuste para o seu Model e tabelas reais de documentos se necessário
        $alertasDocumentos = \App\Models\DocumentoVeiculo::with('veiculo')
            ->where('data_vencimento', '<=', $alertaPrazoDias)
            ->orderBy('data_vencimento', 'asc')
            ->get();


        // ---------------------------------------------------------------------
        // REQUISITO 3: Controle de Devoluções (Atrasadas ou Próximas)
        // ---------------------------------------------------------------------
        // Busca contratos 'em_andamento' onde a previsão de retorno já passou do horário atual
        $devolucoesAtrasadas = Contrato::with(['veiculo', 'usuario'])
            ->where('status_contrato', 'em_andamento')
            ->where('data_hora_retorno', '<', Carbon::now())
            ->get();

        // Contratos que vencem hoje (próximos do vencimento)
        $devolucoesProximas = Contrato::with(['veiculo', 'usuario'])
            ->where('status_contrato', 'em_andamento')
            ->whereDate('data_hora_retorno', $hoje)
            ->where('data_hora_retorno', '>=', Carbon::now())
            ->get();

        // ---------------------------------------------------------------------
        // REQUISITO 4: Manutenção de Cadastro (Vencimento de CNH)
        // ---------------------------------------------------------------------
        // Busca motoristas/clientes com CNH vencida ou vencendo nos próximos 30 dias
        // Nota: Altere para a tabela onde você guarda a CNH (ex: no model User ou Cliente)
        $alertasCnh = Usuario::whereNotNull('validade_cnh')
            ->where('validade_cnh', '<=', $alertaPrazoDias)
            ->orderBy('validade_cnh', 'asc')
            ->get();

        // Contagem total de alertas ativos para exibir um contador global
        $totalAlertas = $alertasManutencao->count() + 
                        (is_countable($alertasDocumentos) ? count($alertasDocumentos) : 0) + 
                        $devolucoesAtrasadas->count() + 
                        $devolucoesProximas->count() + 
                        $alertasCnh->count();

        return view('alarmes.index', compact(
            'alertasManutencao',
            'alertasDocumentos',
            'devolucoesAtrasadas',
            'devolucoesProximas',
            'alertasCnh',
            'totalAlertas'
        ));
    }
}