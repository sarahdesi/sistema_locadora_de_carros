<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CheckIn extends Model
{
    // Força o Laravel a usar o nome correto da tabela no singular do banco
    protected $table = 'check_in';

    protected $fillable = [
        'contrato_id', 
        'data_hora_saida', 
        'previsao_retorno',
        'km_inicial', 
        'nivel_combustivel',
        'avarias', 
        'conferencia_obj', 
        'status'
    ];

    public function contrato()
    {
        return $this->belongsTo(Contrato::class);
    }
}