<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alarme extends Model
{
    protected $fillable= ['tipo', 'entidade','entidade_id','mensagem', 'data_disparo', 'visualizacao'];

    
}
