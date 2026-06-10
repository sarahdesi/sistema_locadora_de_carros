<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentoVeiculo extends Model
{
    protected $table = 'documento_veiculos';
    protected $fillable = [
        'veiculo_placa', 'tipo', 'data_vencimento', 'valor'
    ];


    public function veiculo()
    {
        return $this-> belongsTo(Veiculo::class, 'veiculo_placa', 'placa');
    }

    
}
