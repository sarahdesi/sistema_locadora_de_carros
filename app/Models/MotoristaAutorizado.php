<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MotoristaAutorizado extends Model
{
    protected $fillable = [
        'contrato_id', 'cpf', 'nome', 'cnh'
    ];

    public function contrato(){
        return $this->belongsTo(Contrato::class);
    }
}
