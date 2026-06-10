<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Veiculo extends Model
{
    protected $fillable = [
        'placa', 'modelo', 'marca', 'renavam', 'cor', 'ano', 'combustivel', 'odometro', 'status'
    ];

    public function contratos(){
        return $this->hasMany(Contrato::class);
    }

    public function manutencoes(){
        return $this->hasMany(Manutencao::class, 'veiculo_id', 'id'); //mudei aqui o id
    }

    public function documentos(){
        return $this->hasMany(DocumentoVeiculo::class, 'veiculo_placa', 'placa');
    }






}
