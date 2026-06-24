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
        return view('alarmes.index');
    }
}