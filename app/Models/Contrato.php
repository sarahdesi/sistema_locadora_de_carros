<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contrato extends Model
{
    protected $fillable =[
        'cliente_id', 'veiculo_id', 'servidor_id', 
        'data_hora_retorno', 'valor_diaria', 'valor_total',
        'status_contrato'
    ];
//belongsTo chave estrangeira no banco de dados e "pertence" a outra entidade.
    public function cliente(){
        return $this->belongsTo(Usuario::class, 'cliente_id');
    }

    public function servidor(){
        return $this->belongsTo(Usuario::class, 'servidor_id');
    }

    public function veiculo(){
        return $this->belongsTo(Veiculo::class);
    }

    public function checkIn(){
        return $this->hasOne(CheckIn::class);
    }

    public function checkOut(){
        return $this->hasOne(CheckOut::class);
    }

    public function valoresExtras(){
        return $this->hasMany(ValorExtra::class);
    }

    public function motoristasAutorizados(){
        return $this->hasMany(MotoristaAutorizado::class);
    }


}
