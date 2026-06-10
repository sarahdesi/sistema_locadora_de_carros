<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Manutencao extends Model
{
    protected $table = 'documento_manutencao';

    protected $fillable = [
        'veiculo_id',
        'tipo_manutencao',
        'descricao',
        'data_entrada',
        'data_saida',
        'custo',
        'status'
    ];

    public function veiculo(){
        return $this->belongsTo(Veiculo::class);
    }
}
