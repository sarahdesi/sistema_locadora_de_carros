<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogAtividade extends Model
{
    // Vincula o nome exato da tabela que está no seu banco
    protected $table = 'log_atividade';

    // Permite a gravação em massa desses campos (Apenas UMA vez)
    protected $fillable = ['usuario_id', 'acao', 'descricao'];

    // Relacionamento com o Usuário
    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }

    public static function registrar($acao, $descricao)
    {
        self::create([
            'usuario_id' => auth()->id(), // Pega automaticamente o ID de quem está logado
            'acao'       => $acao,
            'descricao'  => $descricao,
        ]);
    }
}