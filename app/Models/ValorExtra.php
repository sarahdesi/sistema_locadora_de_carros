<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ValorExtra extends Model
{
    protected $fillable = [
        'contrato_id', 'tipo_de_cobranca', 'valor'    
        ];

    public function contrato(){
        return $this->belongsTo(Contrato::class);
    }
}
