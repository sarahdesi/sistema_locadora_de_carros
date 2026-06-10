<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CheckOut extends Model
{
    protected $table = 'check_out';
    protected $fillable = [
        'contrato_id',
        'data_hora_devolucao',
        'km_final',
        'nivel_combustivel_retorno',
        'avaliacao_limpeza',
        'conferencia_obj_retorno',
        'avarias_retorno',
        'custo_adicional',
        'observacoes'
    ];




    
    public function contrato(){
        return $this->belongsTo(Contrato::class);
    }
}
